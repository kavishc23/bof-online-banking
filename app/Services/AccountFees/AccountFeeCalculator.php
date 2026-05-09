<?php

namespace App\Services\AccountFees;

/**
 * CS415 Assignment 3 account fee calculator contract.
 *
 * Implementations keep account-fee business rules outside controllers and
 * Blade views while still using Strapi account and transaction arrays.
 */
interface AccountFeeCalculator
{
    /**
     * Calculate the monthly account fee for one account.
     *
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     */
    public function calculate(array $account, array $transactions, string $month): float;
}
