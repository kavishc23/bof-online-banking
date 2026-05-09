<?php

namespace App\Services\AccountFees;

use App\Events\BankingActivityOccurred;
use Carbon\Carbon;

/**
 * CS415 Assignment 3 business-layer service.
 *
 * This service consumes Strapi account/transaction arrays, applies monthly
 * filtering, delegates fee rules to calculators, and returns view-ready
 * summaries without placing business logic in controllers or Blade files.
 */
class MonthlyAccountFeeService
{
    public function __construct(
        private readonly AccountFeeFactory $factory,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $accounts
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, array<string, mixed>>
     */
    public function summaries(array $accounts, array $transactions, string $month): array
    {
        return collect($accounts)
            ->mapWithKeys(fn (array $account): array => [
                (string) ($account['accountNumber'] ?? $account['id'] ?? '') => $this->summary($account, $transactions, $month),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>
     */
    public function summary(array $account, array $transactions, string $month): array
    {
        $monthlyTransactions = $this->filterCompletedTransactionsByMonth($transactions, $month);
        $calculator = $this->factory->make($account);
        $fee = round($calculator->calculate($account, $monthlyTransactions, $month), 2);
        $accountType = (string) ($account['accountType'] ?? 'Unknown');
        $calculatorSummary = $calculator instanceof ProvidesAccountFeeSummary
            ? $calculator->summary($account, $monthlyTransactions, $month)
            : $this->defaultSummary($fee);

        $summary = [
            'account_number' => $account['accountNumber'] ?? null,
            'account_type' => $accountType,
            'calculated_monthly_fee' => $fee,
            'explanation' => $calculatorSummary['explanation'],
            'withdrawal_count' => $calculatorSummary['withdrawal_count'],
            'monthly_input' => $calculatorSummary['monthly_input'],
        ];

        event(new BankingActivityOccurred('account_fee.calculated', 'Monthly account fee calculated.', [
            'account_number' => $summary['account_number'],
            'account_type' => $accountType,
            'month' => $month,
            'fee' => $fee,
        ]));

        return $summary;
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<int, array<string, mixed>>
     */
    private function filterCompletedTransactionsByMonth(array $transactions, string $month): array
    {
        return collect($transactions)
            ->filter(function (array $transaction) use ($month): bool {
                if (! $this->isCompleted($transaction)) {
                    return false;
                }

                if (empty($transaction['transactionDate'])) {
                    return false;
                }

                return Carbon::parse((string) $transaction['transactionDate'])->format('Y-m') === $month;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function isCompleted(array $transaction): bool
    {
        if (! array_key_exists('transactionStatus', $transaction) || empty($transaction['transactionStatus'])) {
            return true;
        }

        return strtolower((string) $transaction['transactionStatus']) === 'completed';
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSummary(float $fee): array
    {
        return [
            'explanation' => $fee > 0 ? 'Monthly fee applied by account policy.' : 'No monthly fee applies for this account type.',
            'withdrawal_count' => null,
            'monthly_input' => null,
        ];
    }
}
