<?php

namespace App\Services;

use App\Events\BankingActivityOccurred;
use App\Services\Logging\BankingLogger;
use App\Services\Notifications\NotificationSettingsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class BofService
{
    public function __construct(
        private readonly BankingLogger $logger,
        private readonly AuditService $audit,
        private readonly NotificationSettingsService $notificationSettings,
    ) {}

    public function reportApiFailure(string $step, Response $response): void
    {
        $this->logger->apiFailure($step, $response);
        report(new RuntimeException($step.' failed with status '.$response->status().': '.$response->body()));
    }

    public function handleException(Throwable $exception, string $message): RedirectResponse
    {
        $this->logger->exception($exception, ['friendly_message' => $message]);
        report($exception);

        return back()->withInput()->with('error', $message);
    }

    public function fetchCustomerAndTransactions(string $jwt, array $user): array
    {
        try {
            $customerResponse = Http::withToken($jwt)->get(
                'http://localhost:1337/api/customers?populate[accounts][populate]=*'
            );

            $customerRows = $customerResponse->json()['data'] ?? [];
            $matchedCustomer = null;

            foreach ($customerRows as $customer) {
                if (
                    isset($customer['email']) &&
                    isset($user['email']) &&
                    strtolower(trim((string) $customer['email'])) === strtolower(trim((string) $user['email']))
                ) {
                    $matchedCustomer = $customer;
                    break;
                }
            }

            $transactions = [];

            if ($matchedCustomer && ! empty($matchedCustomer['accounts'])) {
                $transactionResponse = Http::withToken($jwt)->get(
                    'http://localhost:1337/api/transactions?populate=*'
                );

                $transactionRows = $transactionResponse->json()['data'] ?? [];

                foreach ($transactionRows as $transaction) {
                    foreach ($matchedCustomer['accounts'] as $account) {
                        if ($this->transactionBelongsToCustomerAccount($transaction, $account)) {
                            $transactions[] = $this->normalizeTransactionForAccount($transaction, $account);
                            break;
                        }
                    }
                }
            }

            usort($transactions, fn ($a, $b) => strcmp(
                (string) ($b['transactionDate'] ?? ''),
                (string) ($a['transactionDate'] ?? '')
            ));

            return [
                'customer' => $matchedCustomer,
                'transactions' => $transactions,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'customer' => null,
                'transactions' => [],
            ];
        }
    }

    public function transactionBelongsToAccount(array $transaction, int $accountId): bool
    {
        return $this->transactionBelongsToCustomerAccount($transaction, ['id' => $accountId]);
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @param  array<string, mixed>  $account
     */
    public function transactionBelongsToCustomerAccount(array $transaction, array $account): bool
    {
        $transactionType = strtolower($transaction['transactionType'] ?? '');
        $transferType = strtolower($transaction['transferType'] ?? '');

        $isDeposit = $transactionType === 'deposit' || $transferType === 'deposit';

        if ($isDeposit) {
            return
                $this->relationMatchesAccount($transaction['account'] ?? null, $account) ||
                $this->relationMatchesAccount($transaction['destinationAccount'] ?? null, $account);
        }

        return
            $this->relationMatchesAccount($transaction['account'] ?? null, $account) ||
            $this->relationMatchesAccount($transaction['sourceAccount'] ?? null, $account);
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function relationMatchesAccount(mixed $relation, array $account): bool
    {
        if (is_numeric($relation)) {
            return (int) $relation === (int) ($account['id'] ?? 0);
        }

        if (! is_array($relation)) {
            return false;
        }

        $relationData = $relation['data'] ?? $relation;
        $relationAttributes = is_array($relationData) ? ($relationData['attributes'] ?? []) : [];
        $relationAccount = is_array($relationData) ? array_merge($relationData, is_array($relationAttributes) ? $relationAttributes : []) : [];

        return ((int) ($relationAccount['id'] ?? 0) > 0 && (int) ($relationAccount['id'] ?? 0) === (int) ($account['id'] ?? 0))
            || (! empty($relationAccount['documentId']) && (string) $relationAccount['documentId'] === (string) ($account['documentId'] ?? ''))
            || (! empty($relationAccount['accountNumber']) && (string) $relationAccount['accountNumber'] === (string) ($account['accountNumber'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>
     */
    private function normalizeTransactionForAccount(array $transaction, array $account): array
    {
        $transactionType = strtolower((string) ($transaction['transactionType'] ?? ''));

        if (empty($transaction['transferType'])) {
            $transaction['transferType'] = match ($transactionType) {
                'deposit' => 'Deposit',
                'withdrawal' => 'Withdrawal',
                'billpayment' => 'BillPayment',
                'fee' => 'Fee',
                'transfer' => 'Transfer',
                default => $transaction['transactionType'] ?? 'Transaction',
            };
        }

        if ($this->relationMatchesAccount($transaction['account'] ?? null, $account)) {
            $transaction['account'] = $this->normalizedAccountRelation($transaction['account'] ?? null, $account);
        }

        if ($this->relationMatchesAccount($transaction['sourceAccount'] ?? null, $account)) {
            $transaction['sourceAccount'] = $this->normalizedAccountRelation($transaction['sourceAccount'] ?? null, $account);
        }

        if ($this->relationMatchesAccount($transaction['destinationAccount'] ?? null, $account)) {
            $transaction['destinationAccount'] = $this->normalizedAccountRelation($transaction['destinationAccount'] ?? null, $account);
        }

        if ($transactionType === 'withdrawal') {
            $transaction['account'] = is_array($transaction['account'] ?? null) ? $transaction['account'] : $account;
            $transaction['sourceAccount'] = is_array($transaction['sourceAccount'] ?? null) ? $transaction['sourceAccount'] : $account;
        }

        return $transaction;
    }

    /**
     * @param  array<string, mixed>  $fallbackAccount
     * @return array<string, mixed>
     */
    private function normalizedAccountRelation(mixed $relation, array $fallbackAccount): array
    {
        if (! is_array($relation)) {
            return $fallbackAccount;
        }

        $relationData = $relation['data'] ?? $relation;
        $relationAttributes = is_array($relationData) ? ($relationData['attributes'] ?? []) : [];
        $normalized = is_array($relationData) ? array_merge($relationData, is_array($relationAttributes) ? $relationAttributes : []) : [];

        return array_filter($normalized) ?: $fallbackAccount;
    }

    public function fetchArray(string $url, ?string $jwt = null): array
    {
        try {
            $request = $jwt ? Http::withToken($jwt) : Http::asJson();
            $response = $request->get($url);

            return $response->json()['data'] ?? [];
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    public function fetchFirst(string $url, string $jwt): ?array
    {
        $rows = $this->fetchArray($url, $jwt);

        return $rows[0] ?? null;
    }

    public function fetchLoanApplications(string $jwt, string $email): array
    {
        return $this->fetchArray('http://localhost:1337/api/loan-applications?filters[customerEmail][$eq]='.urlencode($email).'&sort[0]=submittedAt:desc', $jwt);
    }

    public function fetchActiveBillers(string $jwt): array
    {
        return $this->fetchArray('http://localhost:1337/api/billers?filters[isActive][$eq]=true&sort[0]=name:asc', $jwt);
    }

    public function fetchActiveOtherLocalBanks(string $jwt): array
    {
        return $this->fetchArray('http://localhost:1337/api/other-local-banks?filters[isActive][$eq]=true&sort[0]=name:asc', $jwt);
    }

    public function fetchBeneficiaries(string $jwt, string $email): array
    {
        return $this->fetchArray('http://localhost:1337/api/beneficiaries?filters[customerEmail][$eq]='.urlencode($email).'&sort[0]=nickname:asc', $jwt);
    }

    public function fetchScheduledTransfers(string $jwt, string $email): array
    {
        return $this->fetchArray('http://localhost:1337/api/scheduled-transfers?filters[customerEmail][$eq]='.urlencode($email).'&sort[0]=scheduledDate:asc&populate=*', $jwt);
    }

    public function fetchCustomerByEmail(string $jwt, string $email): ?array
    {
        return $this->fetchFirst('http://localhost:1337/api/customers?filters[email][$eq]='.urlencode($email).'&populate=*', $jwt);
    }

    public function fetchInvestmentsByEmail(string $jwt, string $email): array
    {
        return $this->fetchArray('http://localhost:1337/api/investments?filters[customerEmail][$eq]='.urlencode($email).'&sort[0]=submittedAt:desc', $jwt);
    }

    public function fetchTaxReportsByEmail(string $jwt, string $email): array
    {
        return $this->fetchArray('http://localhost:1337/api/tax-reports?filters[customerEmail][$eq]='.urlencode($email).'&sort[0]=reportingYear:desc', $jwt);
    }

    public function fetchLoanProducts(): array
    {
        return $this->fetchArray('http://localhost:1337/api/loan-products?filters[isActive][$eq]=true&sort[0]=advertisedRate:asc');
    }

    public function fetchScheduledBillPayments(string $jwt, string $email): array
    {
        return $this->fetchArray('http://localhost:1337/api/scheduled-bill-payments?filters[customerEmail][$eq]='.urlencode($email).'&sort[0]=scheduledDate:asc&populate=*', $jwt);
    }

    public function fetchInvestments(string $jwt, string $email): array
    {
        return $this->fetchInvestmentsByEmail($jwt, $email);
    }

    public function calculateTaxProfileStatus(?string $tin, string $residencyStatus): string
    {
        if ($residencyStatus === 'NonResident') {
            return 'NonResident';
        }

        if (empty(trim((string) $tin))) {
            return 'MissingTIN';
        }

        return 'ValidTIN';
    }

    public function calculateWithholdingTaxRate(?string $tin, string $residencyStatus): float
    {
        if ($residencyStatus === 'NonResident') {
            return 15.0;
        }

        if (empty(trim((string) $tin))) {
            return 15.0;
        }

        return 0.0;
    }

    public function calculateGrossInterestForYear(array $investments, int $year): float
    {
        $grossInterest = 0.0;

        foreach ($investments as $investment) {
            $dateToCheck = $investment['submittedAt'] ?? $investment['startDate'] ?? null;

            if (! $dateToCheck) {
                continue;
            }

            try {
                $investmentYear = Carbon::parse($dateToCheck)->year;
            } catch (Exception) {
                continue;
            }

            if ($investmentYear === $year) {
                $grossInterest += (float) ($investment['estimatedReturn'] ?? 0);
            }
        }

        return round($grossInterest, 2);
    }

    public function requiresOtp(float $amount): bool
    {
        return $amount >= 1000;
    }

    public function generateOtpCode(): string
    {
        if (app()->environment('local')) {
            return '123456';
        }

        return (string) random_int(100000, 999999);
    }

    public function findCustomerAccount(array $customer, int $accountId): ?array
    {
        foreach (($customer['accounts'] ?? []) as $account) {
            if ((int) $account['id'] === $accountId) {
                return $account;
            }
        }

        return null;
    }

    public function createBeneficiary(Request $request, string $jwt, string $email): RedirectResponse
    {
        try {
            $response = Http::withToken($jwt)->post('http://localhost:1337/api/beneficiaries', [
                'data' => [
                    'nickname' => $request->nickname,
                    'beneficiaryName' => $request->beneficiary_name,
                    'transferMode' => $request->transfer_mode,
                    'institutionName' => $request->institution_name,
                    'accountNumber' => $request->account_number,
                    'customerEmail' => $email,
                    'isFavorite' => false,
                ],
            ]);

            if (! $response->successful()) {
                $this->reportApiFailure('beneficiary_create', $response);

                return back()->withInput()->with('error', 'Beneficiary could not be saved. Please try again.');
            }

            event(new BankingActivityOccurred('beneficiary.created', 'Beneficiary saved successfully.', [
                'beneficiary_name' => $request->beneficiary_name,
                'transfer_mode' => $request->transfer_mode,
                'customer_email' => $email,
            ]));

            return redirect()->route('beneficiaries')->with('success', 'Beneficiary saved successfully.');
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Beneficiary could not be saved. Please try again.');
        }
    }

    public function processImmediateTransfer(Request $request, string $jwt, array $customer, array $user): RedirectResponse
    {
        try {
            $fromAccount = $this->findCustomerAccount($customer, (int) $request->from_account_id);

            if (! $fromAccount) {
                return back()->with('error', 'Selected source account not found.');
            }

            $amount = (float) $request->amount;
            $fromBalance = (float) ($fromAccount['balance'] ?? 0);

            if ($amount > $fromBalance) {
                return back()->withInput()->with('error', 'Insufficient balance.');
            }

            if ($request->transfer_mode === 'internal') {
                return $this->processInternalTransfer($request, $jwt, $fromAccount, $amount, $fromBalance, $user);
            }

            return $this->processLocalBankTransfer($request, $jwt, $fromAccount, $amount, $fromBalance, $user);
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Transfer could not be completed. Please try again.');
        }
    }

    public function processInternalTransfer(Request $request, string $jwt, array $fromAccount, float $amount, float $fromBalance, array $user): RedirectResponse
    {
        $accountsResponse = Http::withToken($jwt)->get('http://localhost:1337/api/accounts?populate=*');
        $allAccounts = $accountsResponse->json()['data'] ?? [];
        $toAccount = null;

        foreach ($allAccounts as $account) {
            if (
                isset($account['accountNumber']) &&
                trim((string) $account['accountNumber']) === trim((string) $request->to_account_number)
            ) {
                $toAccount = $account;
                break;
            }
        }

        if (! $toAccount) {
            return back()->withInput()->with('error', 'Destination BoF account not found.');
        }

        if ((int) $toAccount['id'] === (int) $fromAccount['id']) {
            return back()->withInput()->with('error', 'Cannot transfer to the same account.');
        }

        $updateFromResponse = Http::withToken($jwt)->put("http://localhost:1337/api/accounts/{$fromAccount['documentId']}", [
            'data' => ['balance' => $fromBalance - $amount],
        ]);

        $updateToResponse = Http::withToken($jwt)->put("http://localhost:1337/api/accounts/{$toAccount['documentId']}", [
            'data' => ['balance' => (float) ($toAccount['balance'] ?? 0) + $amount],
        ]);

        if (! $updateFromResponse->successful() || ! $updateToResponse->successful()) {
            return back()->withInput()->with('error', 'Transfer failed while updating balances.');
        }

        $referenceOut = 'TXN-OUT-'.time();
        $referenceIn = 'TXN-IN-'.time();

        $outgoingTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
            'data' => [
                'referenceNumber' => $referenceOut,
                'transactionType' => 'Transfer',
                'transferType' => 'Internal',
                'amount' => $amount,
                'transactionDate' => now()->toISOString(),
                'description' => $request->description ?: 'Transfer to account '.$toAccount['accountNumber'],
                'destinationInstitution' => 'BoF',
                'destinationAccountNumber' => $toAccount['accountNumber'] ?? '',
                'beneficiaryName' => $toAccount['accountHolderName'] ?? '',
                'remarks' => $request->description,
                'transactionStatus' => 'Completed',
                'sourceAccount' => $fromAccount['id'],
                'destinationAccount' => $toAccount['id'],
                'account' => $fromAccount['id'],
            ],
        ]);

        if (! $outgoingTransactionResponse->successful()) {
            $this->reportApiFailure('internal_outgoing_transaction_create', $outgoingTransactionResponse);

            return back()->withInput()->with('error', 'Transfer failed while recording the outgoing transaction.');
        }

        $incomingTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
            'data' => [
                'referenceNumber' => $referenceIn,
                'transactionType' => 'Deposit',
                'transferType' => 'Deposit',
                'amount' => $amount,
                'transactionDate' => now()->toISOString(),
                'description' => 'Transfer received from account '.$fromAccount['accountNumber'],
                'destinationInstitution' => 'BoF',
                'destinationAccountNumber' => $toAccount['accountNumber'] ?? '',
                'beneficiaryName' => $toAccount['accountHolderName'] ?? '',
                'remarks' => 'Incoming internal transfer',
                'transactionStatus' => 'Completed',
                'sourceAccount' => $fromAccount['id'],
                'destinationAccount' => $toAccount['id'],
                'account' => $toAccount['id'],
            ],
        ]);

        if (! $incomingTransactionResponse->successful()) {
            $this->reportApiFailure('internal_incoming_transaction_create', $incomingTransactionResponse);

            return back()->withInput()->with('error', 'Transfer failed while recording the incoming transaction.');
        }

        $this->audit->record('transfer.internal.completed', [
            'source_balance' => $fromBalance,
            'destination_balance' => (float) ($toAccount['balance'] ?? 0),
        ], [
            'source_balance' => $fromBalance - $amount,
            'destination_balance' => (float) ($toAccount['balance'] ?? 0) + $amount,
        ], [
            'reference_number' => $referenceOut,
            'amount' => $amount,
            'source_account' => $fromAccount['id'],
            'destination_account' => $toAccount['id'],
        ]);

        event(new BankingActivityOccurred('transfer.completed', 'Internal transfer completed.', [
            'reference_number' => $referenceOut,
            'amount' => $amount,
            'transfer_type' => 'Internal',
            'source_account' => $fromAccount['id'],
            'destination_account' => $toAccount['id'],
        ]));

        event(new BankingActivityOccurred('transfer.received', 'Internal transfer received.', [
            'reference_number' => $referenceIn,
            'amount' => $amount,
            'transfer_type' => 'Internal',
            'source_account' => $fromAccount['id'],
            'destination_account' => $toAccount['id'],
        ]));

        $this->refreshCustomerSession($jwt, $user);

        return $this->redirectWithOptionalSuccess(
            $this->transferSuccessMessage($referenceOut, internalTransfer: true)
        );
    }

    public function processLocalBankTransfer(Request $request, string $jwt, array $fromAccount, float $amount, float $fromBalance, array $user): RedirectResponse
    {
        $selectedInstitution = $this->findSelectedInstitution($jwt, (int) $request->destination_institution_id);

        if (! $selectedInstitution) {
            return back()->withInput()->with('error', 'Selected destination institution not found.');
        }

        $updateFromResponse = Http::withToken($jwt)->put("http://localhost:1337/api/accounts/{$fromAccount['documentId']}", [
            'data' => ['balance' => $fromBalance - $amount],
        ]);

        if (! $updateFromResponse->successful()) {
            return back()->withInput()->with('error', 'Local bank transfer failed while updating balance.');
        }

        $referenceNumber = 'LBT-'.time();

        $localTransferTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
            'data' => [
                'referenceNumber' => $referenceNumber,
                'transactionType' => 'Transfer',
                'transferType' => 'LocalBank',
                'amount' => $amount,
                'transactionDate' => now()->toISOString(),
                'description' => $request->description ?: 'Transfer to '.($selectedInstitution['name'] ?? 'External Institution'),
                'destinationInstitution' => $selectedInstitution['name'] ?? '',
                'destinationAccountNumber' => $request->destination_account_number,
                'beneficiaryName' => $request->beneficiary_name,
                'remarks' => $request->description,
                'transactionStatus' => 'Completed',
                'sourceAccount' => $fromAccount['id'],
                'account' => $fromAccount['id'],
            ],
        ]);

        if (! $localTransferTransactionResponse->successful()) {
            $this->reportApiFailure('local_bank_transaction_create', $localTransferTransactionResponse);

            return back()->withInput()->with('error', 'Local bank transfer failed while recording the transaction.');
        }

        $this->audit->record('transfer.local_bank.completed', [
            'source_balance' => $fromBalance,
        ], [
            'source_balance' => $fromBalance - $amount,
        ], [
            'reference_number' => $referenceNumber,
            'amount' => $amount,
            'source_account' => $fromAccount['id'],
            'destination_institution' => $selectedInstitution['name'] ?? '',
        ]);

        event(new BankingActivityOccurred('transfer.completed', 'Local bank transfer completed.', [
            'reference_number' => $referenceNumber,
            'amount' => $amount,
            'transfer_type' => 'LocalBank',
            'source_account' => $fromAccount['id'],
            'destination_institution' => $selectedInstitution['name'] ?? '',
        ]));

        $this->refreshCustomerSession($jwt, $user);

        return $this->redirectWithOptionalSuccess(
            $this->transferSuccessMessage($referenceNumber, internalTransfer: false)
        );
    }

    public function findSelectedInstitution(string $jwt, int $institutionId): ?array
    {
        foreach ($this->fetchActiveOtherLocalBanks($jwt) as $bank) {
            if ((int) ($bank['id'] ?? 0) === $institutionId) {
                return $bank;
            }
        }

        return null;
    }

    public function processImmediateBillPayment(Request $request, string $jwt, array $customer, array $user): RedirectResponse
    {
        try {
            $fromAccount = $this->findCustomerAccount($customer, (int) $request->from_account_id);

            if (! $fromAccount) {
                return back()->with('error', 'Selected account not found.');
            }

            $selectedBiller = $this->findSelectedBiller($jwt, (int) $request->biller_id);

            if (! $selectedBiller) {
                return back()->withInput()->with('error', 'Selected biller not found.');
            }

            $amount = (float) $request->amount;
            $fromBalance = (float) ($fromAccount['balance'] ?? 0);

            if ($amount > $fromBalance) {
                return back()->withInput()->with('error', 'Insufficient balance.');
            }

            $updateFromResponse = Http::withToken($jwt)->put("http://localhost:1337/api/accounts/{$fromAccount['documentId']}", [
                'data' => ['balance' => $fromBalance - $amount],
            ]);

            if (! $updateFromResponse->successful()) {
                return back()->withInput()->with('error', 'Bill payment failed while updating account balance.');
            }

            $billPaymentResponse = Http::withToken($jwt)->post('http://localhost:1337/api/bill-payments', [
                'data' => [
                    'billerName' => $selectedBiller['name'] ?? '',
                    'billReference' => $request->bill_reference,
                    'amount' => $amount,
                    'paymentDate' => now()->toISOString(),
                    'isScheduled' => false,
                    'notes' => $request->notes,
                    'account' => $fromAccount['id'],
                ],
            ]);

            if (! $billPaymentResponse->successful()) {
                $this->reportApiFailure('bill_payment_record_create', $billPaymentResponse);

                return back()->withInput()->with('error', 'Bill payment failed while creating the payment record.');
            }

            $referenceNumber = 'BILL-'.time();

            $transactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
                'data' => [
                    'referenceNumber' => $referenceNumber,
                    'transactionType' => 'BillPayment',
                    'transferType' => 'BillPayment',
                    'amount' => $amount,
                    'transactionDate' => now()->toISOString(),
                    'description' => 'Bill payment to '.($selectedBiller['name'] ?? 'Biller'),
                    'destinationInstitution' => $selectedBiller['name'] ?? '',
                    'destinationAccountNumber' => $request->bill_reference,
                    'remarks' => $request->notes,
                    'transactionStatus' => 'Completed',
                    'sourceAccount' => $fromAccount['id'],
                    'account' => $fromAccount['id'],
                ],
            ]);

            if (! $transactionResponse->successful()) {
                $this->reportApiFailure('bill_payment_transaction_create', $transactionResponse);

                return back()->withInput()->with('error', 'Bill payment failed while recording the transaction.');
            }

            $this->audit->record('bill_payment.completed', [
                'source_balance' => $fromBalance,
            ], [
                'source_balance' => $fromBalance - $amount,
            ], [
                'reference_number' => $referenceNumber,
                'amount' => $amount,
                'biller_name' => $selectedBiller['name'] ?? '',
                'source_account' => $fromAccount['id'],
            ]);

            event(new BankingActivityOccurred('bill-payment.completed', 'Bill payment completed.', [
                'reference_number' => $referenceNumber,
                'amount' => $amount,
                'biller_name' => $selectedBiller['name'] ?? '',
                'source_account' => $fromAccount['id'],
            ]));

            $this->refreshCustomerSession($jwt, $user);

            return $this->redirectWithOptionalSuccess(
                $this->billPaymentSuccessMessage($referenceNumber)
            );
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Bill payment could not be completed. Please try again.');
        }
    }

    public function findSelectedBiller(string $jwt, int $billerId): ?array
    {
        foreach ($this->fetchActiveBillers($jwt) as $biller) {
            if ((int) ($biller['id'] ?? 0) === $billerId) {
                return $biller;
            }
        }

        return null;
    }

    public function refreshCustomerSession(string $jwt, array $user): void
    {
        $result = $this->fetchCustomerAndTransactions($jwt, $user);

        session([
            'customer' => $result['customer'],
            'transactions' => $result['transactions'],
        ]);
    }

    private function transferSuccessMessage(string $referenceNumber, bool $internalTransfer): ?string
    {
        if (! $this->notificationSettings->isEnabled('money_sent')) {
            return null;
        }

        $message = 'Transaction successful. SMS confirmation sent.';

        if ($internalTransfer && $this->notificationSettings->isEnabled('money_received')) {
            $message .= ' Funds received notification sent to destination account holder.';
        }

        return $message.' Reference: '.$referenceNumber;
    }

    private function billPaymentSuccessMessage(string $referenceNumber): ?string
    {
        if (! $this->notificationSettings->isEnabled('bill_payments')) {
            return null;
        }

        return 'Bill payment successful. SMS confirmation sent. Reference: '.$referenceNumber;
    }

    private function redirectWithOptionalSuccess(?string $message): RedirectResponse
    {
        $redirect = redirect('/dashboard');

        if ($message === null) {
            return $redirect;
        }

        return $redirect->with('success', $message);
    }
}
