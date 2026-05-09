<?php

namespace App\Services\AccountFees;

/**
 * CS415 Assignment 3 SimpleAccess fee rule.
 *
 * Strapi `SimpleAccess` maps to the assignment Access account type and always
 * incurs a flat FJD 0.90 monthly maintenance fee.
 */
class SimpleAccessAccountFee implements AccountFeeCalculator, ProvidesAccountFeeSummary
{
    public const MONTHLY_FEE = 0.90;

    public function calculate(array $account, array $transactions, string $month): float
    {
        return self::MONTHLY_FEE;
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>
     */
    public function summary(array $account, array $transactions, string $month): array
    {
        return [
            'explanation' => 'SimpleAccess maps to Access account and has a flat FJD 0.90 monthly fee.',
            'withdrawal_count' => null,
            'monthly_input' => null,
        ];
    }
}
