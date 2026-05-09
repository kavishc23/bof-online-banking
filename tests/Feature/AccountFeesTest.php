<?php

use App\Services\AccountFees\MonthlyAccountFeeService;

function feeSummary(array $account, array $transactions, string $month = '2026-02'): array
{
    return app(MonthlyAccountFeeService::class)->summary($account, $transactions, $month);
}

function accountFixture(string $type = 'SimpleAccess'): array
{
    return [
        'id' => 10,
        'documentId' => 'account-document-10',
        'accountNumber' => '044145600017',
        'accountType' => $type,
        'balance' => 5000,
    ];
}

function transactionFixture(string $type, float $amount = 100, array $relations = []): array
{
    return [
        'referenceNumber' => 'TEST-'.uniqid(),
        'transactionType' => $type,
        'amount' => $amount,
        'transactionDate' => '2026-02-15',
        'transactionStatus' => 'Completed',
    ] + $relations;
}

test('SimpleAccess returns 0.90 monthly fee', function () {
    $summary = feeSummary(accountFixture('SimpleAccess'), []);

    expect($summary['calculated_monthly_fee'])->toBe(0.90);
});

test('Savings with 0 withdrawals returns 0', function () {
    $summary = feeSummary(accountFixture('Savings'), []);

    expect($summary['calculated_monthly_fee'])->toBe(0.00)
        ->and($summary['withdrawal_count'])->toBe(0);
});

test('Savings with 1 withdrawal returns 0', function () {
    $account = accountFixture('Savings');
    $transactions = [
        transactionFixture('Withdrawal', 100, ['account' => $account]),
    ];

    $summary = feeSummary($account, $transactions);

    expect($summary['calculated_monthly_fee'])->toBe(0.00)
        ->and($summary['withdrawal_count'])->toBe(1);
});

test('Savings with 3 withdrawals returns 10.00', function () {
    $account = accountFixture('Savings');
    $transactions = [
        transactionFixture('Withdrawal', 100, ['account' => $account]),
        transactionFixture('BillPayment', 50, ['account' => $account]),
        transactionFixture('Transfer', 75, ['sourceAccount' => $account]),
    ];

    $summary = feeSummary($account, $transactions);

    expect($summary['calculated_monthly_fee'])->toBe(10.00)
        ->and($summary['withdrawal_count'])->toBe(3);
});

test('Business with monthly input 1500 returns 20.00', function () {
    $account = accountFixture('Business');
    $transactions = [
        transactionFixture('Deposit', 1000, ['account' => $account]),
        transactionFixture('Transfer', 500, ['destinationAccount' => $account]),
    ];

    $summary = feeSummary($account, $transactions);

    expect($summary['calculated_monthly_fee'])->toBe(20.00)
        ->and($summary['monthly_input'])->toBe(1500.00);
});

test('Business with monthly input 2000 returns 0.00', function () {
    $account = accountFixture('Business');
    $transactions = [
        transactionFixture('Deposit', 2000, ['account' => $account]),
    ];

    $summary = feeSummary($account, $transactions);

    expect($summary['calculated_monthly_fee'])->toBe(0.00)
        ->and($summary['monthly_input'])->toBe(2000.00);
});

test('Business with monthly input 2500 returns 0.00', function () {
    $account = accountFixture('Business');
    $transactions = [
        transactionFixture('Deposit', 1500, ['account' => $account]),
        transactionFixture('Transfer', 1000, ['destinationAccount' => $account]),
    ];

    $summary = feeSummary($account, $transactions);

    expect($summary['calculated_monthly_fee'])->toBe(0.00)
        ->and($summary['monthly_input'])->toBe(2500.00);
});
