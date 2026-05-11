<?php

namespace App\Services\AccountFees;

use App\Services\Logging\BankingLogger;
use App\Services\Strapi\StrapiApiService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MonthlyFeeDeductionService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly MonthlyAccountFeeService $monthlyFees,
        private readonly BankingLogger $logger,
    ) {}

    /**
     * @return array{accounts_checked: int, fees_charged: int, skipped: int, duplicates_prevented: int, errors: int, insufficient_balance: int, details: array<int, array<string, mixed>>}
     */
    public function chargeDueFees(?Carbon $date = null): array
    {
        $date ??= now();
        $summary = [
            'accounts_checked' => 0,
            'fees_charged' => 0,
            'skipped' => 0,
            'duplicates_prevented' => 0,
            'errors' => 0,
            'insufficient_balance' => 0,
            'details' => [],
        ];

        $this->logger->activity('monthly_fee.command_started', 'Monthly account fee deduction started.', [
            'date' => $date->toDateString(),
        ]);

        try {
            $accounts = $this->accounts();
            $transactions = $this->transactions();
        } catch (Throwable $exception) {
            $summary['errors']++;
            $summary['details'][] = [
                'accountNumber' => '-',
                'id' => '-',
                'documentId' => '-',
                'accountType' => '-',
                'balance' => '-',
                'monthlyMaintenanceFee' => '-',
                'openedAt' => '-',
                'createdAt' => '-',
                'lastMonthlyFeeChargedAt' => '-',
                'lastFeeChargedAt' => '-',
                'result' => 'Error',
                'reason' => 'Could not fetch Strapi accounts or transactions.',
                'response_body' => $exception->getMessage(),
            ];
            $this->logger->exception($exception, [
                'operation' => 'monthly_fee_fetch_strapi_data',
            ]);

            return $summary;
        }

        $month = $date->format('Y-m');

        foreach ($accounts as $account) {
            $summary['accounts_checked']++;
            $detail = $this->accountDetail($account);

            $billingSkipReason = $this->billingSkipReason($account, $date);

            if ($billingSkipReason !== null) {
                $summary['skipped']++;
                $summary['details'][] = $detail + [
                    'result' => 'Skipped',
                    'reason' => $billingSkipReason,
                ];
                $this->logger->activity('monthly_fee.skipped', 'Account skipped because today is not billing day.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'date' => $date->toDateString(),
                    'reason' => $billingSkipReason,
                ]);

                continue;
            }

            $duplicateTransaction = $this->duplicateFeeTransaction($account, $transactions, $month);

            if ($duplicateTransaction !== null) {
                $summary['duplicates_prevented']++;
                $summary['details'][] = $detail + [
                    'result' => 'Duplicate prevented',
                    'reason' => 'Monthly fee already charged this month.',
                    'duplicate_reference' => $duplicateTransaction['referenceNumber'] ?? null,
                    'duplicate_date' => $duplicateTransaction['transactionDate'] ?? null,
                    'duplicate_description' => $duplicateTransaction['description'] ?? null,
                ];
                $this->logger->activity('monthly_fee.duplicate_prevented', 'Monthly account fee duplicate prevented.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'month' => $month,
                    'duplicate_reference' => $duplicateTransaction['referenceNumber'] ?? null,
                ]);

                continue;
            }

            $feeSummary = $this->monthlyFees->summary($account, $transactions, $month);
            $fee = round((float) ($feeSummary['calculated_monthly_fee'] ?? 0), 2);

            if ($fee <= 0) {
                $summary['skipped']++;
                $summary['details'][] = $detail + [
                    'result' => 'Skipped',
                    'reason' => 'Calculated monthly fee is zero.',
                    'calculated_fee' => $fee,
                    'fee_explanation' => $feeSummary['explanation'] ?? null,
                    'withdrawal_count' => $feeSummary['withdrawal_count'] ?? null,
                    'monthly_input' => $feeSummary['monthly_input'] ?? null,
                ];
                $this->logger->activity('monthly_fee.zero_fee_skipped', 'Monthly account fee calculated as zero.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'month' => $month,
                ]);

                continue;
            }

            $balance = (float) ($account['balance'] ?? 0);

            if ($fee > $balance) {
                $summary['insufficient_balance']++;
                $summary['skipped']++;
                $summary['details'][] = $detail + [
                    'result' => 'Skipped',
                    'reason' => 'Insufficient balance for calculated monthly fee.',
                    'calculated_fee' => $fee,
                    'balance' => $balance,
                ];
                $this->logger->activity('monthly_fee.insufficient_balance', 'Monthly account fee skipped because balance is insufficient.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'fee' => $fee,
                    'balance' => $balance,
                ]);

                continue;
            }

            $transactionResult = $this->createFeeTransaction($account, $fee, $date);

            if (! $transactionResult['successful']) {
                $summary['errors']++;
                $summary['details'][] = $detail + [
                    'result' => 'Error',
                    'reason' => 'Fee transaction creation failed.',
                    'calculated_fee' => $fee,
                    'http_status' => $transactionResult['status'],
                    'response_body' => $transactionResult['body'],
                ];

                continue;
            }

            $newBalance = round($balance - $fee, 2);
            $updateResult = $this->updateAccountBalance($account, $newBalance);

            if (! $updateResult['successful']) {
                $summary['errors']++;
                $summary['details'][] = $detail + [
                    'result' => 'Error',
                    'reason' => 'Account balance update failed.',
                    'calculated_fee' => $fee,
                    'new_balance' => $newBalance,
                    'update_identifier' => $account['documentId'] ?? $account['id'] ?? null,
                    'http_status' => $updateResult['status'],
                    'response_body' => $updateResult['body'],
                ];

                continue;
            }

            $summary['fees_charged']++;
            $summary['details'][] = $detail + [
                'result' => 'Charged',
                'reason' => 'Monthly fee deducted successfully.',
                'calculated_fee' => $fee,
                'new_balance' => $newBalance,
                'transaction_status' => $transactionResult['status'],
                'update_status' => $updateResult['status'],
            ];
            $this->logger->activity('monthly_fee.charged', 'Monthly account fee charged.', [
                'account_number' => $account['accountNumber'] ?? null,
                'fee' => $fee,
                'month' => $month,
            ]);
        }

        $this->logger->activity('monthly_fee.command_completed', 'Monthly account fee deduction completed.', $summary);

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $account
     */
    public function isBillingDay(array $account, Carbon $date): bool
    {
        return $this->billingSkipReason($account, $date) === null;
    }

    /**
     * Returns null when the account is due for billing; otherwise returns the exact skip reason.
     *
     * @param  array<string, mixed>  $account
     */
    private function billingSkipReason(array $account, Carbon $date): ?string
    {
        if (empty($account['openedAt'])) {
            return 'Missing openedAt. The command uses openedAt, not created_at.';
        }

        $openedAt = Carbon::parse((string) $account['openedAt']);

        if ($date->lt($openedAt->copy()->addMonthNoOverflow()->startOfDay())) {
            return 'Account is not at least one month old. First eligible date is '.$openedAt->copy()->addMonthNoOverflow()->toDateString().'.';
        }

        $billingDay = min($openedAt->day, $date->copy()->endOfMonth()->day);

        if ($date->day !== $billingDay) {
            return 'Today is day '.$date->day.', but billing day is '.$billingDay.' based on openedAt.';
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function accounts(): array
    {
        $response = $this->strapi->get('/api/accounts', [
            'populate' => '*',
        ]);

        if (! $response->successful()) {
            $this->logger->apiFailure('monthly_fee_accounts_fetch', $response);

            throw new RuntimeException('Strapi accounts fetch failed with status '.$response->status().': '.$response->body());
        }

        return $this->strapi->data($response);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function transactions(): array
    {
        $response = $this->strapi->get('/api/transactions', [
            'populate' => '*',
        ]);

        if (! $response->successful()) {
            $this->logger->apiFailure('monthly_fee_transactions_fetch', $response);

            throw new RuntimeException('Strapi transactions fetch failed with status '.$response->status().': '.$response->body());
        }

        return $this->strapi->data($response);
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     */
    private function duplicateFeeTransaction(array $account, array $transactions, string $month): ?array
    {
        return collect($transactions)
            ->first(function (array $transaction) use ($account, $month): bool {
                $transactionType = strtolower((string) ($transaction['transactionType'] ?? ''));
                $transferType = strtolower((string) ($transaction['transferType'] ?? ''));
                $referenceNumber = strtoupper((string) ($transaction['referenceNumber'] ?? ''));
                $description = strtolower((string) ($transaction['description'] ?? ''));
                $status = strtolower((string) ($transaction['transactionStatus'] ?? 'completed'));

                if ($status !== 'completed') {
                    return false;
                }

                if (! AccountTransactionMatcher::matchesAccount($transaction['account'] ?? null, $account)) {
                    return false;
                }

                if (empty($transaction['transactionDate']) || Carbon::parse((string) $transaction['transactionDate'])->format('Y-m') !== $month) {
                    return false;
                }

                return ($transactionType === 'fee' || $transferType === 'fee')
                    && (str_starts_with($referenceNumber, 'FEE-') || str_contains($description, 'monthly account fee'));
            });
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function createFeeTransaction(array $account, float $fee, Carbon $date): array
    {
        $response = $this->strapi->post('/api/transactions', [
            'data' => [
                'referenceNumber' => $this->referenceNumber($date),
                'transactionType' => 'Fee',
                'transferType' => 'Fee',
                'amount' => $fee,
                'transactionDate' => $date->toISOString(),
                'description' => 'Monthly account fee',
                'remarks' => 'Automatically deducted monthly account charge',
                'transactionStatus' => 'Completed',
                'account' => $account['id'],
            ],
        ]);

        if (! $response->successful()) {
            $this->logger->apiFailure('monthly_fee_transaction_create', $response);
        }

        return [
            'successful' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function updateAccountBalance(array $account, float $newBalance): array
    {
        $response = $this->strapi->put('/api/accounts/'.($account['documentId'] ?? $account['id']), [
            'data' => [
                'balance' => $newBalance,
            ],
        ]);

        if (! $response->successful()) {
            $this->logger->apiFailure('monthly_fee_account_balance_update', $response);
        }

        return [
            'successful' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>
     */
    private function accountDetail(array $account): array
    {
        return [
            'accountNumber' => $account['accountNumber'] ?? null,
            'id' => $account['id'] ?? null,
            'documentId' => $account['documentId'] ?? null,
            'accountType' => $account['accountType'] ?? null,
            'balance' => $account['balance'] ?? null,
            'monthlyMaintenanceFee' => $account['monthlyMaintenanceFee'] ?? null,
            'openedAt' => $account['openedAt'] ?? null,
            'createdAt' => $account['createdAt'] ?? null,
            'lastMonthlyFeeChargedAt' => $account['lastMonthlyFeeChargedAt'] ?? null,
            'lastFeeChargedAt' => $account['lastFeeChargedAt'] ?? null,
        ];
    }

    private function referenceNumber(Carbon $date): string
    {
        return 'FEE-'.$date->year.'-'.Str::upper(Str::random(6));
    }
}
