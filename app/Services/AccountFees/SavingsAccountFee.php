<?php

namespace App\Services\AccountFees;

/**
 * CS415 Assignment 3 Savings account fee rule.
 *
 * The first monthly withdrawal is free; each later qualifying withdrawal costs
 * FJD 5.00. Qualifying withdrawals include Withdrawal, BillPayment, and
 * outgoing Transfer transactions where this account is the source account.
 */
class SavingsAccountFee implements AccountFeeCalculator, ProvidesAccountFeeSummary
{
    public const FREE_MONTHLY_WITHDRAWALS = 1;

    public const WITHDRAWAL_FEE = 5.00;

    public function calculate(array $account, array $transactions, string $month): float
    {
        return self::chargeForWithdrawalCount($this->monthlyWithdrawalCount($account, $transactions));
    }

    public static function chargeForWithdrawalCount(int $monthlyWithdrawalCount): float
    {
        $chargeableWithdrawals = max(0, $monthlyWithdrawalCount - self::FREE_MONTHLY_WITHDRAWALS);

        return (float) ($chargeableWithdrawals * self::WITHDRAWAL_FEE);
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     */
    public function monthlyWithdrawalCount(array $account, array $transactions): int
    {
        return collect($transactions)
            ->filter(fn (array $transaction): bool => $this->isWithdrawalForAccount($transaction, $account))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $account
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>
     */
    public function summary(array $account, array $transactions, string $month): array
    {
        $withdrawalCount = $this->monthlyWithdrawalCount($account, $transactions);

        return [
            'explanation' => $withdrawalCount <= 1
                ? 'Savings account includes one free withdrawal this month.'
                : 'Savings account charged FJD 5.00 for each withdrawal after the first free withdrawal.',
            'withdrawal_count' => $withdrawalCount,
            'monthly_input' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @param  array<string, mixed>  $account
     */
    private function isWithdrawalForAccount(array $transaction, array $account): bool
    {
        $transactionType = strtolower((string) ($transaction['transactionType'] ?? ''));

        if (in_array($transactionType, ['withdrawal', 'billpayment'], true)) {
            return AccountTransactionMatcher::matchesAccount($transaction['account'] ?? null, $account);
        }

        return $transactionType === 'transfer'
            && AccountTransactionMatcher::matchesAccount($transaction['sourceAccount'] ?? null, $account);
    }
}
