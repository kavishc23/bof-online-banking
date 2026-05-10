<?php

use App\Services\AccountFees\MonthlyAccountFeeService;
use Tests\TestCase;

uses(TestCase::class);

function cs415FeeAccount(string $type): array
{
    return [
        'id' => 901,
        'documentId' => 'account-901',
        'accountNumber' => '901001',
        'accountType' => $type,
        'balance' => 5000,
    ];
}

function cs415FeeTransaction(string $type, float $amount, array $relations = []): array
{
    return [
        'referenceNumber' => 'CS415-'.$type.'-'.$amount,
        'transactionType' => $type,
        'amount' => $amount,
        'transactionDate' => '2026-05-10T00:00:00.000Z',
        'transactionStatus' => 'Completed',
    ] + $relations;
}

function cs415FeeSummary(array $account, array $transactions = []): array
{
    return app(MonthlyAccountFeeService::class)->summary($account, $transactions, '2026-05');
}

test('CS415 fee rule: Access account monthly fee should be 0.90', function () {
    $summary = cs415FeeSummary(cs415FeeAccount('SimpleAccess'));

    expect($summary['calculated_monthly_fee'])->toBe(0.90);
});

test('CS415 fee rule: Savings first monthly withdrawal should not charge fee', function () {
    $account = cs415FeeAccount('Savings');
    $summary = cs415FeeSummary($account, [
        cs415FeeTransaction('Withdrawal', 100, ['account' => $account]),
    ]);

    expect($summary['withdrawal_count'])->toBe(1)
        ->and($summary['calculated_monthly_fee'])->toBe(0.00);
});

test('CS415 fee rule: Savings second withdrawal in same month should charge 5.00', function () {
    $account = cs415FeeAccount('Savings');
    $summary = cs415FeeSummary($account, [
        cs415FeeTransaction('Withdrawal', 100, ['account' => $account]),
        cs415FeeTransaction('Withdrawal', 75, ['account' => $account]),
    ]);

    expect($summary['withdrawal_count'])->toBe(2)
        ->and($summary['calculated_monthly_fee'])->toBe(5.00);
});

test('CS415 fee rule: Business account below 2000 monthly input should charge 20.00', function () {
    $account = cs415FeeAccount('Business');
    $summary = cs415FeeSummary($account, [
        cs415FeeTransaction('Deposit', 1500, ['account' => $account]),
    ]);

    expect($summary['monthly_input'])->toBe(1500.00)
        ->and($summary['calculated_monthly_fee'])->toBe(20.00);
});

test('CS415 fee rule: Business account with monthly input equal or greater than 2000 should charge 0.00', function () {
    $account = cs415FeeAccount('Business');
    $equalThreshold = cs415FeeSummary($account, [
        cs415FeeTransaction('Deposit', 2000, ['account' => $account]),
    ]);
    $aboveThreshold = cs415FeeSummary($account, [
        cs415FeeTransaction('Deposit', 1500, ['account' => $account]),
        cs415FeeTransaction('Transfer', 500, ['destinationAccount' => $account]),
    ]);

    expect($equalThreshold['calculated_monthly_fee'])->toBe(0.00)
        ->and($aboveThreshold['calculated_monthly_fee'])->toBe(0.00);
});
