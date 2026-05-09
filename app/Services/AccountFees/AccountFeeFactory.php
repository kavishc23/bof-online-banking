<?php

namespace App\Services\AccountFees;

/**
 * CS415 Assignment 3 factory for selecting the fee strategy by Strapi accountType.
 */
class AccountFeeFactory
{
    public function __construct(
        private readonly SimpleAccessAccountFee $simpleAccess,
        private readonly SavingsAccountFee $savings,
        private readonly BusinessAccountFee $business,
    ) {}

    /**
     * @param  array<string, mixed>  $account
     */
    public function make(array $account): AccountFeeCalculator
    {
        return match ($account['accountType'] ?? null) {
            'SimpleAccess' => $this->simpleAccess,
            'Savings' => $this->savings,
            'Business' => $this->business,
            default => new class implements AccountFeeCalculator
            {
                public function calculate(array $account, array $transactions, string $month): float
                {
                    return 0.00;
                }
            },
        };
    }
}
