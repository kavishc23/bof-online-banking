<?php

namespace App\Services\Transactions;

use App\Events\BankingActivityOccurred;
use App\Services\AccountFees\MonthlyAccountFeeService;
use App\Services\AccountFees\SavingsAccountFee;
use App\Services\BofService;
use App\Services\Logging\BankingLogger;
use App\Services\Strapi\StrapiApiService;
use Throwable;

class WithdrawalService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly BofService $bofService,
        private readonly BankingLogger $logger,
        private readonly MonthlyAccountFeeService $monthlyFees,
    ) {}

    /**
     * Creates a Strapi Withdrawal transaction and updates the selected account balance.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $user
     * @return array{successful: bool, message: string}
     */
    public function withdraw(array $validated, array $customer, array $user): array
    {
        try {
            $account = $this->bofService->findCustomerAccount($customer, (int) $validated['account_id']);

            if (! $account) {
                $this->logger->activity('withdrawal.denied', 'Withdrawal rejected because account does not belong to customer.', [
                    'account_id' => $validated['account_id'],
                    'customer_email' => $user['email'] ?? null,
                ]);

                return [
                    'successful' => false,
                    'message' => 'Selected account was not found for this customer.',
                ];
            }

            $amount = (float) $validated['amount'];
            $balance = (float) ($account['balance'] ?? 0);
            $feeAmount = $this->feeForThisWithdrawal($account);
            $totalDebit = $amount + $feeAmount;

            if ($totalDebit > $balance) {
                $this->logger->activity('withdrawal.insufficient_balance', 'Withdrawal rejected because account balance is insufficient.', [
                    'account_id' => $account['id'] ?? null,
                    'amount' => $amount,
                    'fee_amount' => $feeAmount,
                    'balance' => $balance,
                ]);

                return [
                    'successful' => false,
                    'message' => $feeAmount > 0
                        ? 'Insufficient balance for this withdrawal and the Savings withdrawal fee.'
                        : 'Insufficient balance for this withdrawal.',
                ];
            }

            $newBalance = round($balance - $totalDebit, 2);
            $referenceNumber = $this->referenceNumber();
            $transactionDate = now()->toISOString();
            $withdrawalTransaction = [
                'referenceNumber' => $referenceNumber,
                'transactionType' => 'Withdrawal',
                'transferType' => 'Withdrawal',
                'amount' => $amount,
                'transactionDate' => $transactionDate,
                'description' => 'Cash withdrawal',
                'remarks' => $validated['remarks'] ?? null,
                'transactionStatus' => 'Completed',
                'account' => $account['id'],
                'sourceAccount' => $account['id'],
            ];

            $transactionResponse = $this->strapi->post('/api/transactions', [
                'data' => $withdrawalTransaction,
            ]);

            if (! $transactionResponse->successful()) {
                $this->bofService->reportApiFailure('withdrawal_transaction_create', $transactionResponse);

                return [
                    'successful' => false,
                    'message' => 'Withdrawal failed while recording the transaction.',
                ];
            }

            $feeTransaction = null;

            if ($feeAmount > 0) {
                $feeTransaction = $this->createSavingsWithdrawalFeeTransaction($account, $feeAmount);

                if ($feeTransaction === null) {
                    return [
                        'successful' => false,
                        'message' => 'Withdrawal failed while recording the Savings withdrawal fee.',
                    ];
                }
            }

            $accountIdentifier = (string) ($account['documentId'] ?? $account['id']);
            $updateResponse = $this->strapi->put('/api/accounts/'.$accountIdentifier, [
                'data' => [
                    'balance' => $newBalance,
                ],
            ]);

            if (! $updateResponse->successful()) {
                $this->bofService->reportApiFailure('withdrawal_account_balance_update', $updateResponse);

                return [
                    'successful' => false,
                    'message' => 'Withdrawal failed while updating account balance.',
                ];
            }

            $previousTransactions = session('transactions', []);

            $this->bofService->refreshCustomerSession((string) session('jwt'), $user);
            $this->mergeTransactionsIntoSession($account, array_merge($previousTransactions, array_filter([$withdrawalTransaction, $feeTransaction])));
            $this->logger->activity('withdrawal.completed', 'Customer withdrawal completed.', [
                'reference_number' => $referenceNumber,
                'account_id' => $account['id'] ?? null,
                'amount' => $amount,
                'fee_amount' => $feeAmount,
                'new_balance' => $newBalance,
            ]);

            event(new BankingActivityOccurred('withdrawal.completed', 'Customer withdrawal completed.', [
                'reference_number' => $referenceNumber,
                'account_id' => $account['id'] ?? null,
                'amount' => $amount,
                'fee_amount' => $feeAmount,
            ]));

            return [
                'successful' => true,
                'message' => 'Withdrawal successful.',
            ];
        } catch (Throwable $exception) {
            $this->logger->exception($exception, ['operation' => 'withdrawal']);
            report($exception);

            return [
                'successful' => false,
                'message' => 'Withdrawal could not be processed. Please try again.',
            ];
        }
    }

    private function referenceNumber(): string
    {
        return 'WDL-'.now()->year.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function feeReferenceNumber(): string
    {
        return 'WDL-FEE-'.now()->year.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function feeForThisWithdrawal(array $account): float
    {
        if (($account['accountType'] ?? null) !== 'Savings') {
            return 0.0;
        }

        $summary = $this->monthlyFees->summary($account, $this->currentTransactions(), now()->format('Y-m'));
        $withdrawalCountBeforeThisWithdrawal = (int) ($summary['withdrawal_count'] ?? 0);

        return $withdrawalCountBeforeThisWithdrawal >= SavingsAccountFee::FREE_MONTHLY_WITHDRAWALS
            ? SavingsAccountFee::WITHDRAWAL_FEE
            : 0.0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function currentTransactions(): array
    {
        return $this->strapi->data($this->strapi->get('/api/transactions', [
            'populate' => '*',
            'sort' => 'transactionDate:desc',
        ]));
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function createSavingsWithdrawalFeeTransaction(array $account, float $feeAmount): ?array
    {
        $feeTransaction = [
            'referenceNumber' => $this->feeReferenceNumber(),
            'transactionType' => 'Withdrawal',
            'transferType' => 'Withdrawal',
            'amount' => $feeAmount,
            'transactionDate' => now()->toISOString(),
            'description' => 'Savings withdrawal fee',
            'remarks' => 'FJD 5.00 fee for Savings withdrawal after the first free monthly withdrawal.',
            'transactionStatus' => 'Completed',
            'account' => $account['id'],
            'sourceAccount' => $account['id'],
        ];

        $response = $this->strapi->post('/api/transactions', [
            'data' => $feeTransaction,
        ]);

        if (! $response->successful()) {
            $this->bofService->reportApiFailure('savings_withdrawal_fee_transaction_create', $response);

            return null;
        }

        $this->logger->activity('withdrawal.savings_fee_applied', 'Savings withdrawal fee transaction created.', [
            'account_id' => $account['id'] ?? null,
            'fee_amount' => $feeAmount,
        ]);

        return $feeTransaction;
    }

    /**
     * Strapi may not immediately return freshly-created relations in the shape
     * expected by the transaction register, so the service also stores a
     * display-ready copy in the session after the authoritative API writes.
     *
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     */
    private function mergeTransactionsIntoSession(array $account, array $transactions): void
    {
        $existingTransactions = collect(session('transactions', []))
            ->keyBy(fn (array $transaction): string => (string) ($transaction['referenceNumber'] ?? uniqid('tx-', true)));

        foreach ($transactions as $transaction) {
            if ($this->shouldUseSelectedAccountRelation($transaction['account'] ?? null, $account)) {
                $transaction['account'] = $account;
            }

            if ($this->shouldUseSelectedAccountRelation($transaction['sourceAccount'] ?? null, $account)) {
                $transaction['sourceAccount'] = $account;
            }

            $existingTransactions->put((string) $transaction['referenceNumber'], $transaction);
        }

        session([
            'transactions' => $existingTransactions
                ->sortByDesc(fn (array $transaction): string => (string) ($transaction['transactionDate'] ?? ''))
                ->values()
                ->all(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function shouldUseSelectedAccountRelation(mixed $relation, array $account): bool
    {
        return $relation === null
            || $relation === ''
            || (is_numeric($relation) && (int) $relation === (int) ($account['id'] ?? 0));
    }
}
