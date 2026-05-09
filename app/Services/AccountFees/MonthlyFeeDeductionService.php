<?php

namespace App\Services\AccountFees;

use App\Services\Logging\BankingLogger;
use App\Services\Strapi\StrapiApiService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

class MonthlyFeeDeductionService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly MonthlyAccountFeeService $monthlyFees,
        private readonly BankingLogger $logger,
    ) {}

    /**
     * @return array{accounts_checked: int, fees_charged: int, skipped: int, duplicates_prevented: int, errors: int, insufficient_balance: int}
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
        ];

        $this->logger->activity('monthly_fee.command_started', 'Monthly account fee deduction started.', [
            'date' => $date->toDateString(),
        ]);

        try {
            $accounts = $this->accounts();
            $transactions = $this->transactions();
        } catch (Throwable $exception) {
            $summary['errors']++;
            $this->logger->exception($exception, [
                'operation' => 'monthly_fee_fetch_strapi_data',
            ]);

            return $summary;
        }

        $month = $date->format('Y-m');

        foreach ($accounts as $account) {
            $summary['accounts_checked']++;

            if (! $this->isBillingDay($account, $date)) {
                $summary['skipped']++;
                $this->logger->activity('monthly_fee.skipped', 'Account skipped because today is not billing day.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'date' => $date->toDateString(),
                ]);

                continue;
            }

            if ($this->hasFeeAlreadyBeenCharged($account, $transactions, $month)) {
                $summary['duplicates_prevented']++;
                $this->logger->activity('monthly_fee.duplicate_prevented', 'Monthly account fee duplicate prevented.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'month' => $month,
                ]);

                continue;
            }

            $feeSummary = $this->monthlyFees->summary($account, $transactions, $month);
            $fee = round((float) ($feeSummary['calculated_monthly_fee'] ?? 0), 2);

            if ($fee <= 0) {
                $summary['skipped']++;
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
                $this->logger->activity('monthly_fee.insufficient_balance', 'Monthly account fee skipped because balance is insufficient.', [
                    'account_number' => $account['accountNumber'] ?? null,
                    'fee' => $fee,
                    'balance' => $balance,
                ]);

                continue;
            }

            if (! $this->createFeeTransaction($account, $fee, $date)) {
                $summary['errors']++;

                continue;
            }

            if (! $this->updateAccountBalance($account, round($balance - $fee, 2))) {
                $summary['errors']++;

                continue;
            }

            $summary['fees_charged']++;
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
        if (empty($account['openedAt'])) {
            return false;
        }

        $openedAt = Carbon::parse((string) $account['openedAt']);

        if ($date->lt($openedAt->copy()->addMonthNoOverflow()->startOfDay())) {
            return false;
        }

        $billingDay = min($openedAt->day, $date->copy()->endOfMonth()->day);

        return $date->day === $billingDay;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function accounts(): array
    {
        return $this->strapi->data($this->strapi->get('/api/accounts', [
            'populate' => '*',
        ]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function transactions(): array
    {
        return $this->strapi->data($this->strapi->get('/api/transactions', [
            'populate' => '*',
        ]));
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     */
    private function hasFeeAlreadyBeenCharged(array $account, array $transactions, string $month): bool
    {
        return collect($transactions)
            ->contains(function (array $transaction) use ($account, $month): bool {
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
    private function createFeeTransaction(array $account, float $fee, Carbon $date): bool
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

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function updateAccountBalance(array $account, float $newBalance): bool
    {
        $response = $this->strapi->put('/api/accounts/'.($account['documentId'] ?? $account['id']), [
            'data' => [
                'balance' => $newBalance,
            ],
        ]);

        if (! $response->successful()) {
            $this->logger->apiFailure('monthly_fee_account_balance_update', $response);
        }

        return $response->successful();
    }

    private function referenceNumber(Carbon $date): string
    {
        return 'FEE-'.$date->year.'-'.Str::upper(Str::random(6));
    }
}
