<?php

namespace App\Services;

use App\Contracts\FeeCalculator;
use App\Services\AccountFees\BusinessAccountFee;
use App\Services\AccountFees\SavingsAccountFee;
use App\Services\AccountFees\SimpleAccessAccountFee;

class FeeCalculationService implements FeeCalculator
{
    /**
     * Compatibility wrapper for the CS415 SimpleAccess account rule.
     */
    public function accessAccountMonthlyFee(array $account): float
    {
        return SimpleAccessAccountFee::MONTHLY_FEE;
    }

    /**
     * Compatibility wrapper for the CS415 Savings account rule.
     */
    public function savingsWithdrawalCharge(float $amount, int $monthlyWithdrawalCount): float
    {
        return SavingsAccountFee::chargeForWithdrawalCount($monthlyWithdrawalCount);
    }

    /**
     * Compatibility wrapper for the CS415 Business account rule.
     *
     * @param  array{monthlyInput?: float|int|string}  $account
     */
    public function businessMonthlyMaintenanceFee(array $account): float
    {
        $isBusinessAccount = strtolower((string) ($account['accountType'] ?? '')) === 'business';

        if (! $isBusinessAccount) {
            return 0.0;
        }

        return BusinessAccountFee::feeForMonthlyInput((float) ($account['monthlyInput'] ?? 0));
    }
}
