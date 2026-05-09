<?php

namespace App\Services;

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
    public function reportApiFailure(string $step, Response $response): void
    {
        report(new RuntimeException($step.' failed with status '.$response->status().': '.$response->body()));
    }

    public function handleException(Throwable $exception, string $message): RedirectResponse
    {
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
                $accountIds = array_map(fn ($account) => $account['id'], $matchedCustomer['accounts']);

                $transactionResponse = Http::withToken($jwt)->get(
                    'http://localhost:1337/api/transactions?populate=*'
                );

                $transactionRows = $transactionResponse->json()['data'] ?? [];

                foreach ($transactionRows as $transaction) {
                    foreach ($accountIds as $accountId) {
                        if ($this->transactionBelongsToAccount($transaction, (int) $accountId)) {
                            $transactions[] = $transaction;
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
        $sourceAccountId = $transaction['sourceAccount']['id'] ?? null;
        $legacyAccountId = $transaction['account']['id'] ?? null;
        $destinationAccountId = $transaction['destinationAccount']['id'] ?? null;

        $transactionType = strtolower($transaction['transactionType'] ?? '');
        $transferType = strtolower($transaction['transferType'] ?? '');

        $isDeposit = $transactionType === 'deposit' || $transferType === 'deposit';

        if ($isDeposit) {
            return
                (int) $legacyAccountId === $accountId ||
                (int) $destinationAccountId === $accountId;
        }

        return
            (int) $legacyAccountId === $accountId ||
            (int) $sourceAccountId === $accountId;
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

        $this->refreshCustomerSession($jwt, $user);

        return redirect('/dashboard')->with(
            'success',
            'Transaction successful. SMS confirmation sent to sender and funds received notification sent to destination account holder. Reference: '.$referenceOut
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

        $this->refreshCustomerSession($jwt, $user);

        return redirect('/dashboard')->with(
            'success',
            'Transaction successful. SMS confirmation sent. Reference: '.$referenceNumber
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

            $this->refreshCustomerSession($jwt, $user);

            return redirect('/dashboard')->with(
                'success',
                'Bill payment successful. SMS confirmation sent. Reference: '.$referenceNumber
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
}
