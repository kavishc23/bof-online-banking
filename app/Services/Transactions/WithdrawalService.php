<?php

namespace App\Services\Transactions;

use App\Events\BankingActivityOccurred;
use App\Services\AccountFees\MonthlyAccountFeeService;
use App\Services\AccountFees\SavingsAccountFee;
use App\Services\BofService;
use App\Services\Logging\BankingLogger;
use App\Services\Strapi\StrapiApiService;
use Throwable;

/**
 * Handles the customer withdrawal use case for CS415 Assignment 3.
 *
 * The controller only passes validated input into this service. This class owns
 * the business rules: account ownership, balance checks, Strapi transaction
 * creation, Savings withdrawal fee creation, balance update, and session refresh.
 */
class WithdrawalService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly BofService $bofService,
        private readonly BankingLogger $logger,
        private readonly MonthlyAccountFeeService $monthlyFees,
    ) {}

    /**
     * Creates a real Strapi Withdrawal transaction, deducts the account balance,
     * and refreshes the customer session only after both API writes succeed.
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

            $strapiAccountId = $this->strapiAccountId($account);

            if ($strapiAccountId === null) {
                return [
                    'successful' => false,
                    'message' => 'Selected account is missing its Strapi account id.',
                ];
            }

            $accountUpdateIdentifier = $this->strapiAccountUpdateIdentifier($account);
            $amount = (float) $validated['amount'];
            $balance = (float) ($account['balance'] ?? 0);
            $feeAmount = $this->feeForThisWithdrawal($account);
            $totalDebit = $amount + $feeAmount;

            if ($totalDebit > $balance) {
                return [
                    'successful' => false,
                    'message' => $feeAmount > 0
                        ? 'Insufficient balance for this withdrawal and the Savings withdrawal fee.'
                        : 'Insufficient balance for this withdrawal.',
                ];
            }

            $newBalance = round($balance - $totalDebit, 2);
            $referenceNumber = $this->referenceNumber();

            $withdrawalTransaction = [
                'referenceNumber' => $referenceNumber,
                'transactionType' => 'Withdrawal',
                'transferType' => 'Withdrawal',
                'amount' => $amount,
                'transactionDate' => now()->toISOString(),
                'description' => 'Cash withdrawal',
                'remarks' => $validated['remarks'] ?? null,
                'transactionStatus' => 'Completed',
                'account' => $strapiAccountId,
            ];

            $transactionPayload = ['data' => $withdrawalTransaction];
            $transactionResponse = $this->strapi->post('/api/transactions', $transactionPayload);

            if (! $transactionResponse->successful()) {
                $this->logger->apiFailureWithPayload('withdrawal_transaction_create', $transactionResponse, $transactionPayload);

                return [
                    'successful' => false,
                    'message' => 'Withdrawal failed while recording the transaction.',
                ];
            }

            if ($feeAmount > 0) {
                $feeTransaction = $this->createSavingsWithdrawalFeeTransaction($account, $feeAmount, $strapiAccountId);

                if ($feeTransaction === null) {
                    return [
                        'successful' => false,
                        'message' => 'Withdrawal failed while recording the Savings withdrawal fee.',
                    ];
                }
            }

            $accountUpdateUrl = '/api/accounts/'.$accountUpdateIdentifier;
            $accountUpdatePayload = [
                'data' => [
                    'balance' => $newBalance,
                ],
            ];

            $updateResponse = $this->strapi->put($accountUpdateUrl, $accountUpdatePayload);

            if (! $updateResponse->successful()) {
                $this->logger->apiFailureWithPayload('withdrawal_account_balance_update', $updateResponse, [
                    'url' => $accountUpdateUrl,
                    'payload' => $accountUpdatePayload,
                    'account' => [
                        'id' => $account['id'] ?? null,
                        'documentId' => $account['documentId'] ?? null,
                        'accountNumber' => $account['accountNumber'] ?? null,
                    ],
                ]);

                return [
                    'successful' => false,
                    'message' => 'Withdrawal failed while updating account balance.',
                ];
            }

            $this->bofService->refreshCustomerSession((string) session('jwt'), $user);

            $this->logger->activity('withdrawal.completed', 'Customer withdrawal completed.', [
                'reference_number' => $referenceNumber,
                'account_id' => $account['id'] ?? null,
                'account_type' => $account['accountType'] ?? null,
                'amount' => $amount,
                'fee_amount' => $feeAmount,
                'total_debit' => $totalDebit,
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
                'message' => $feeAmount > 0
                    ? 'Withdrawal successful. Savings withdrawal fee was applied.'
                    : 'Withdrawal successful.',
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

    /**
     * Creates a readable unique reference for the main withdrawal transaction.
     */
    private function referenceNumber(): string
    {
        return 'WDL-'.now()->year.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Creates a readable unique reference for the Savings withdrawal fee.
     */
    private function feeReferenceNumber(): string
    {
        return 'WDL-FEE-'.now()->year.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Checks the existing monthly withdrawal count and returns the extra Savings fee.
     *
     * @param  array<string, mixed>  $account
     */
    private function feeForThisWithdrawal(array $account): float
    {
        if (($account['accountType'] ?? null) !== 'Savings') {
            return 0.0;
        }

        $summary = $this->monthlyFees->summary($account, $this->currentTransactions(), now()->format('Y-m'));
        $withdrawalCountBeforeThisWithdrawal = (int) ($summary['withdrawal_count'] ?? 0);

        $feeAmount = $withdrawalCountBeforeThisWithdrawal >= SavingsAccountFee::FREE_MONTHLY_WITHDRAWALS
            ? SavingsAccountFee::WITHDRAWAL_FEE
            : 0.0;

        $this->logger->activity('withdrawal.savings_fee_check', 'Savings withdrawal fee check completed.', [
            'account_id' => $account['id'] ?? null,
            'account_number' => $account['accountNumber'] ?? null,
            'account_type' => $account['accountType'] ?? null,
            'month' => now()->format('Y-m'),
            'withdrawal_count_before_this_withdrawal' => $withdrawalCountBeforeThisWithdrawal,
            'free_monthly_withdrawals' => SavingsAccountFee::FREE_MONTHLY_WITHDRAWALS,
            'fee_amount' => $feeAmount,
        ]);

        return $feeAmount;
    }

    /**
     * Loads current Strapi transactions so the existing account-fee service can
     * count withdrawals for the selected month.
     *
     * @return array<int, array<string, mixed>>
     */
    private function currentTransactions(): array
    {
        return $this->strapi->data($this->strapi->get('/api/transactions', [
            'populate' => '*',
            'sort' => 'transactionDate:desc',
            'pagination[pageSize]' => 100,
        ]));
    }

    /**
     * Stores the extra Savings withdrawal charge as a Fee transaction in Strapi.
     *
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>|null
     */
    private function createSavingsWithdrawalFeeTransaction(array $account, float $feeAmount, int $strapiAccountId): ?array
    {
        $feeTransaction = [
            'referenceNumber' => $this->feeReferenceNumber(),
            'transactionType' => 'Fee',
            'transferType' => 'Fee',

            'amount' => $feeAmount,
            'transactionDate' => now()->toISOString(),
            'description' => 'Savings withdrawal fee',
            'remarks' => 'FJD 5.00 fee for Savings withdrawal after the first free monthly withdrawal.',
            'transactionStatus' => 'Completed',
            'account' => $strapiAccountId,
        ];

        $payload = ['data' => $feeTransaction];
        $response = $this->strapi->post('/api/transactions', $payload);

        if (! $response->successful()) {
            $this->logger->apiFailureWithPayload('savings_withdrawal_fee_transaction_create', $response, $payload);

            return null;
        }

        $this->logger->activity('withdrawal.savings_fee_applied', 'Savings withdrawal fee transaction created.', [
            'account_id' => $account['id'] ?? null,
            'account_number' => $account['accountNumber'] ?? null,
            'fee_amount' => $feeAmount,
        ]);

        return $feeTransaction;
    }

    /**
     * Strapi relations use the numeric account id, not the visible account number.
     *
     * @param  array<string, mixed>  $account
     */
    private function strapiAccountId(array $account): ?int
    {
        if (! isset($account['id']) || ! is_numeric($account['id'])) {
            return null;
        }

        return (int) $account['id'];
    }

    /**
     * This Strapi v5 project updates account records through documentId URLs.
     *
     * @param  array<string, mixed>  $account
     */
    private function strapiAccountUpdateIdentifier(array $account): string
    {
        return (string) ($account['documentId'] ?? $account['id']);
    }
}
