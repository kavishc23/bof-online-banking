<?php

namespace App\Services\AccountFees;

/**
 * CS415 Assignment 3 summary contract for dashboard-ready fee metadata.
 *
 * Implementations keep fee explanations and account-type metrics beside the
 * fee rule that produced them, avoiding controller or Blade business logic.
 */
interface ProvidesAccountFeeSummary
{
    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>
     */
    public function summary(array $account, array $transactions, string $month): array;
}
