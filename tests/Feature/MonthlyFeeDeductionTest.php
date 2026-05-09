<?php

use App\Services\AccountFees\MonthlyAccountFeeService;
use App\Services\AccountFees\MonthlyFeeDeductionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

function feeAccount(array $overrides = []): array
{
    return $overrides + [
        'id' => 10,
        'documentId' => 'account-10',
        'accountNumber' => '1001',
        'accountType' => 'SimpleAccess',
        'balance' => 100,
        'openedAt' => '2026-04-10T00:00:00.000Z',
    ];
}

function feeTransaction(array $overrides = []): array
{
    return $overrides + [
        'referenceNumber' => 'TXN-1',
        'transactionType' => 'Withdrawal',
        'transferType' => 'Withdrawal',
        'amount' => 10,
        'transactionDate' => '2026-05-10T10:00:00.000Z',
        'transactionStatus' => 'Completed',
        'account' => ['id' => 10, 'documentId' => 'account-10', 'accountNumber' => '1001'],
    ];
}

function fakeFeeStrapi(array $accounts, array $transactions = []): void
{
    Http::fake([
        '*localhost:1337/api/accounts*' => Http::response(['data' => $accounts]),
        '*localhost:1337/api/transactions*' => Http::response(['data' => $transactions]),
    ]);
}

function feeCustomerSession(): array
{
    return [
        'jwt' => 'customer-token',
        'user_role' => 'Customer',
        'user' => ['email' => 'customer@example.com'],
        'customer' => [
            'id' => 1,
            'email' => 'customer@example.com',
            'accounts' => [
                [
                    'id' => 10,
                    'documentId' => 'account-10',
                    'accountNumber' => '1001',
                    'accountType' => 'SimpleAccess',
                    'balance' => 99.10,
                ],
            ],
        ],
    ];
}

test('monthly fee command exists', function () {
    fakeFeeStrapi([]);

    $this->artisan('accounts:charge-monthly-fees')
        ->expectsOutput('Starting monthly account fee deduction...')
        ->assertSuccessful();
});

test('access account on billing day charges flat monthly fee', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([feeAccount()]);

    $this->artisan('accounts:charge-monthly-fees')->assertSuccessful();

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions')
        && $request['data']['transactionType'] === 'Fee'
        && $request['data']['transferType'] === 'Fee'
        && (float) $request['data']['amount'] === 0.90
        && $request['data']['description'] === 'Monthly account fee'
        && $request['data']['account'] === 10);

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/account-10')
        && (float) $request['data']['balance'] === 99.10);

    Carbon::setTestNow();
});

test('savings account with one withdrawal has zero monthly fee', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([
        feeAccount(['accountType' => 'Savings']),
    ], [
        feeTransaction(),
    ]);

    app(MonthlyFeeDeductionService::class)->chargeDueFees();

    Http::assertNotSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions'));

    Carbon::setTestNow();
});

test('savings account with three withdrawals charges monthly fee', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([
        feeAccount(['accountType' => 'Savings', 'balance' => 100]),
    ], [
        feeTransaction(['referenceNumber' => 'WDL-1']),
        feeTransaction(['referenceNumber' => 'WDL-2']),
        feeTransaction(['referenceNumber' => 'WDL-3']),
    ]);

    app(MonthlyFeeDeductionService::class)->chargeDueFees();

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions')
        && $request['data']['transactionType'] === 'Fee'
        && (float) $request['data']['amount'] === 10.0);

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/account-10')
        && (float) $request['data']['balance'] === 90.0);

    Carbon::setTestNow();
});

test('savings fee logic ignores prior fee transaction', function () {
    $summary = app(MonthlyAccountFeeService::class)->summary(
        feeAccount(['accountType' => 'Savings']),
        [
            feeTransaction(),
            feeTransaction([
                'referenceNumber' => 'FEE-2026-ABC123',
                'transactionType' => 'Fee',
                'transferType' => 'Fee',
                'description' => 'Monthly account fee',
                'amount' => 5,
            ]),
        ],
        '2026-05'
    );

    expect($summary['withdrawal_count'])->toBe(1)
        ->and($summary['calculated_monthly_fee'])->toBe(0.00);
});

test('business account below monthly input threshold charges fee', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([
        feeAccount(['accountType' => 'Business', 'balance' => 100]),
    ], [
        feeTransaction([
            'transactionType' => 'Deposit',
            'transferType' => 'Deposit',
            'amount' => 1500,
        ]),
    ]);

    app(MonthlyFeeDeductionService::class)->chargeDueFees();

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions')
        && (float) $request['data']['amount'] === 20.0);

    Carbon::setTestNow();
});

test('business account at monthly input threshold has zero fee', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([
        feeAccount(['accountType' => 'Business', 'balance' => 100]),
    ], [
        feeTransaction([
            'transactionType' => 'Deposit',
            'transferType' => 'Deposit',
            'amount' => 2000,
        ]),
    ]);

    app(MonthlyFeeDeductionService::class)->chargeDueFees();

    Http::assertNotSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions'));

    Carbon::setTestNow();
});

test('not billing day does not deduct fee', function () {
    Carbon::setTestNow('2026-05-11 09:00:00');
    fakeFeeStrapi([feeAccount()]);

    app(MonthlyFeeDeductionService::class)->chargeDueFees();

    Http::assertNotSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions'));

    Carbon::setTestNow();
});

test('duplicate monthly fee is prevented', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([feeAccount()], [
        feeTransaction([
            'referenceNumber' => 'FEE-2026-ABC123',
            'transactionType' => 'Fee',
            'transferType' => 'Fee',
            'description' => 'Monthly account fee',
        ]),
    ]);

    $summary = app(MonthlyFeeDeductionService::class)->chargeDueFees();

    expect($summary['duplicates_prevented'])->toBe(1);

    Http::assertNotSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions'));

    Carbon::setTestNow();
});

test('account opened on thirty first charges on last day of shorter month', function () {
    Carbon::setTestNow('2026-02-28 09:00:00');
    fakeFeeStrapi([
        feeAccount(['openedAt' => '2026-01-31T00:00:00.000Z']),
    ]);

    app(MonthlyFeeDeductionService::class)->chargeDueFees();

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions')
        && $request['data']['transactionType'] === 'Fee');

    Carbon::setTestNow();
});

test('insufficient balance skips fee deduction', function () {
    Carbon::setTestNow('2026-05-10 09:00:00');
    fakeFeeStrapi([
        feeAccount(['accountType' => 'Business', 'balance' => 5]),
    ]);

    $summary = app(MonthlyFeeDeductionService::class)->chargeDueFees();

    expect($summary['insufficient_balance'])->toBe(1);

    Http::assertNotSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions'));

    Carbon::setTestNow();
});

test('fee transaction appears in transaction history as debit', function () {
    fakeFeeStrapi([], []);

    $session = feeCustomerSession() + [
        'transactions' => [
            [
                'referenceNumber' => 'FEE-2026-ABC123',
                'transactionType' => 'Fee',
                'transferType' => 'Fee',
                'amount' => 0.90,
                'transactionDate' => '2026-05-10',
                'description' => 'Monthly account fee',
                'transactionStatus' => 'Completed',
                'account' => ['id' => 10, 'accountNumber' => '1001'],
            ],
        ],
    ];

    $response = $this->withSession($session)->get('/transactions');

    $response->assertOk();
    $response->assertSee('Monthly Account Fee');
    $response->assertSee('DR');
});
