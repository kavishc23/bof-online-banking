<?php

namespace App\Services\AccountFees;

/**
 * CS415 Assignment 3 Business account fee rule.
 *
 * Business accounts pay FJD 20.00 monthly maintenance only when monthly input
 * is less than FJD 2,000. Inputs include Deposit and incoming Transfer
 * transactions where this account is the destination account.
 */
class BusinessAccountFee implements AccountFeeCalculator, ProvidesAccountFeeSummary
{
    public const MONTHLY_INPUT_THRESHOLD = 2000.00;

    public const LOW_INPUT_MONTHLY_FEE = 20.00;

    public function calculate(array $account, array $transactions, string $month): float
    {
        return self::feeForMonthlyInput($this->monthlyInput($account, $transactions));
    }

    public static function feeForMonthlyInput(float $monthlyInput): float
    {
        return $monthlyInput < self::MONTHLY_INPUT_THRESHOLD ? self::LOW_INPUT_MONTHLY_FEE : 0.00;
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     */
    public function monthlyInput(array $account, array $transactions): float
    {
        return round(collect($transactions)
            ->filter(fn (array $transaction): bool => $this->isInputForAccount($transaction, $account))
            ->sum(fn (array $transaction): float => (float) ($transaction['amount'] ?? 0)), 2);
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>
     */
    public function summary(array $account, array $transactions, string $month): array
    {
        $monthlyInput = $this->monthlyInput($account, $transactions);

        return [
            'explanation' => $monthlyInput < self::MONTHLY_INPUT_THRESHOLD
                ? 'Business monthly input is below FJD 2,000, so the FJD 20.00 maintenance fee applies.'
                : 'Business monthly input meets the FJD 2,000 threshold, so no maintenance fee applies.',
            'withdrawal_count' => null,
            'monthly_input' => $monthlyInput,
        ];
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @param  array<string, mixed>  $account
     */
    private function isInputForAccount(array $transaction, array $account): bool
    {
        $transactionType = strtolower((string) ($transaction['transactionType'] ?? ''));

        if ($transactionType === 'deposit') {
            return AccountTransactionMatcher::matchesAccount($transaction['account'] ?? null, $account)
                || AccountTransactionMatcher::matchesAccount($transaction['destinationAccount'] ?? null, $account);
        }

        return $transactionType === 'transfer'
            && AccountTransactionMatcher::matchesAccount($transaction['destinationAccount'] ?? null, $account);
    }
}
