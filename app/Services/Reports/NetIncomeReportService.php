<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Str;

class NetIncomeReportService
{
    /**
     * Builds CS415 Assignment 3 net income report data from Strapi banking records.
     *
     * @param  array<int, array<string, mixed>>  $transactions
     * @param  array<int, array<string, mixed>>  $accounts
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function generate(array $transactions, array $accounts, Carbon $startDate, Carbon $endDate, ?int $interestDays, array $options = []): array
    {
        $periodDays = $interestDays ?? $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;
        $transactionsInRange = $this->filterTransactionsByDateRange($transactions, $startDate, $endDate);
        $feeRows = $this->categorizeFees($transactionsInRange);
        $interestRows = $this->calculateInterestRows($accounts, $periodDays);

        $monthlyFees = $this->sumAmount($feeRows['monthly_account_fee']);
        $withdrawalFees = $this->sumAmount($feeRows['withdrawal_fee']);
        $otherFees = $this->sumAmount($feeRows['other_fee']);
        $totalFees = round($monthlyFees + $withdrawalFees + $otherFees, 2);
        $totalInterest = round(collect($interestRows)->sum('estimatedInterestPaid'), 2);
        $netIncome = round($totalFees - $totalInterest, 2);

        $feeTotals = [
            'monthly_account_fee' => $monthlyFees,
            'withdrawal_fee' => $withdrawalFees,
            'other_fee' => $otherFees,
        ];

        return [
            'report_start_date' => $startDate->toDateString(),
            'report_end_date' => $endDate->toDateString(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'interest_days' => $periodDays,
            'notes' => $options['notes'] ?? null,
            'include_transaction_details' => (bool) ($options['include_transaction_details'] ?? false),
            'include_account_interest_breakdown' => (bool) ($options['include_account_interest_breakdown'] ?? false),
            'fee_rows' => $feeRows,
            'fee_totals' => $feeTotals,
            'interest_rows' => $interestRows,
            'total_monthly_account_fees' => $monthlyFees,
            'total_withdrawal_fees' => $withdrawalFees,
            'total_other_fees' => $otherFees,
            'total_fees_collected' => $totalFees,
            'total_interest_paid' => $totalInterest,
            'net_income' => $netIncome,
            'number_of_fee_transactions' => collect($feeRows)->flatten(1)->count(),
            'number_of_accounts' => count($accounts),
            'highest_fee_category' => $this->highestFeeCategory($feeTotals),
            'net_income_status' => $netIncome >= 0 ? 'Positive Net Income' : 'Negative Net Income',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<int, array<string, mixed>>
     */
    public function filterTransactionsByDateRange(array $transactions, Carbon $startDate, Carbon $endDate): array
    {
        return collect($transactions)
            ->filter(function (array $transaction) use ($startDate, $endDate): bool {
                if (empty($transaction['transactionDate'])) {
                    return false;
                }

                $transactionDate = Carbon::parse($transaction['transactionDate'])->startOfDay();

                return $transactionDate->betweenIncluded($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array{monthly_account_fee: array<int, array<string, mixed>>, withdrawal_fee: array<int, array<string, mixed>>, other_fee: array<int, array<string, mixed>>}
     */
    public function categorizeFees(array $transactions): array
    {
        $categories = [
            'monthly_account_fee' => [],
            'withdrawal_fee' => [],
            'other_fee' => [],
        ];

        foreach ($transactions as $transaction) {
            if (! $this->isCompleted($transaction)) {
                continue;
            }

            $category = $this->detectFeeCategory($transaction);

            if ($category !== 'not_fee') {
                $categories[$category][] = $transaction;
            }
        }

        return $categories;
    }

    /**
     * @param  array<int, array<string, mixed>>  $accounts
     * @return array<int, array<string, mixed>>
     */
    public function calculateInterestRows(array $accounts, int $interestDays): array
    {
        return collect($accounts)
            ->map(function (array $account) use ($interestDays): array {
                $balance = (float) ($account['balance'] ?? 0);
                $interestRate = (float) ($account['interestRate'] ?? 0);
                $interestPaid = round($balance * ($interestRate / 100) * ($interestDays / 365), 2);

                return [
                    'accountNumber' => $account['accountNumber'] ?? '-',
                    'accountType' => $account['accountType'] ?? '-',
                    'balance' => $balance,
                    'interestRate' => $interestRate,
                    'interestDays' => $interestDays,
                    'estimatedInterestPaid' => $interestPaid,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public function detectFeeCategory(array $transaction): string
    {
        $transactionType = Str::lower((string) ($transaction['transactionType'] ?? ''));
        $transferType = Str::lower((string) ($transaction['transferType'] ?? ''));
        $description = Str::lower((string) ($transaction['description'] ?? ''));
        $referenceNumber = Str::upper((string) ($transaction['referenceNumber'] ?? ''));
        $isFeeType = $transactionType === 'fee' || $transferType === 'fee';

        if (Str::startsWith($referenceNumber, 'WDL-FEE') || str_contains($description, 'savings withdrawal fee')) {
            return 'withdrawal_fee';
        }

        if (
            Str::startsWith($referenceNumber, 'FEE-') ||
            str_contains($description, 'monthly account fee') ||
            str_contains($description, 'monthly account charge')
        ) {
            return 'monthly_account_fee';
        }

        return $isFeeType ? 'other_fee' : 'not_fee';
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     */
    private function sumAmount(array $transactions): float
    {
        return round((float) collect($transactions)->sum(fn (array $transaction): float => (float) ($transaction['amount'] ?? 0)), 2);
    }

    /**
     * @param  array<string, float>  $feeTotals
     */
    private function highestFeeCategory(array $feeTotals): string
    {
        if (array_sum($feeTotals) <= 0) {
            return 'No Fees Collected';
        }

        arsort($feeTotals);

        return match (array_key_first($feeTotals)) {
            'monthly_account_fee' => 'Monthly Account Fees',
            'withdrawal_fee' => 'Savings Withdrawal Fees',
            default => 'Other Fees',
        };
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function isCompleted(array $transaction): bool
    {
        if (! isset($transaction['transactionStatus']) || $transaction['transactionStatus'] === null || $transaction['transactionStatus'] === '') {
            return true;
        }

        return in_array(Str::lower((string) $transaction['transactionStatus']), ['completed', 'successful', 'success'], true);
    }
}
