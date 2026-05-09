<?php

namespace App\Contracts;

interface FeeCalculator
{
    /**
     * CS415 compatibility method for the SimpleAccess/Access monthly fee.
     *
     * @param  array<string, mixed>  $account
     */
    public function accessAccountMonthlyFee(array $account): float;

    /**
     * CS415 compatibility method for Savings withdrawal charges.
     */
    public function savingsWithdrawalCharge(float $amount, int $monthlyWithdrawalCount): float;

    /**
     * CS415 compatibility method for Business account maintenance fees.
     *
     * @param  array<string, mixed>  $account
     */
    public function businessMonthlyMaintenanceFee(array $account): float;
}
