<?php

use App\Services\AccountFees\MonthlyAccountFeeService;
use Illuminate\Support\Facades\Http;

function withdrawalCustomerSession(): array
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
                    'accountType' => 'Savings',
                    'balance' => 500,
                ],
            ],
        ],
    ];
}

function fakeWithdrawalStrapi(array $transactions = []): void
{
    Http::fake([
        '*localhost:1337/api/accounts/account-10' => Http::response([
            'data' => [
                'id' => 10,
                'documentId' => 'account-10',
                'balance' => 400,
            ],
        ]),
        '*localhost:1337/api/transactions*' => Http::response([
            'data' => $transactions,
        ]),
        '*localhost:1337/api/customers*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'email' => 'customer@example.com',
                    'accounts' => [
                        [
                            'id' => 10,
                            'documentId' => 'account-10',
                            'accountNumber' => '1001',
                            'accountType' => 'Savings',
                            'balance' => 400,
                        ],
                    ],
                ],
            ],
        ]),
    ]);
}

function priorWithdrawalFixture(): array
{
    return [
        'id' => 80,
        'referenceNumber' => 'WDL-2026-0001',
        'transactionType' => 'Withdrawal',
        'amount' => 100,
        'transactionDate' => now()->toISOString(),
        'transactionStatus' => 'Completed',
        'account' => [
            'id' => 10,
            'documentId' => 'account-10',
            'accountNumber' => '1001',
        ],
    ];
}

test('customer can open withdrawal page', function () {
    $response = $this->withSession(withdrawalCustomerSession())->get('/withdraw');

    $response->assertOk();
    $response->assertSee('Withdraw Funds');
    $response->assertSee('1001');
    $response->assertSee('value="10"', false);
});

test('withdrawal creates Withdrawal transaction', function () {
    fakeWithdrawalStrapi();

    $response = $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
        'remarks' => 'ATM withdrawal',
    ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('success', 'Withdrawal successful.');

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions')
        && str_starts_with((string) $request['data']['referenceNumber'], 'WDL-')
        && $request['data']['transactionType'] === 'Withdrawal'
        && $request['data']['transferType'] === 'Withdrawal'
        && (float) $request['data']['amount'] === 100.0
        && $request['data']['description'] === 'Cash withdrawal'
        && $request['data']['remarks'] === 'ATM withdrawal'
        && $request['data']['transactionStatus'] === 'Completed'
        && $request['data']['account'] === 10
        && ! isset($request['data']['sourceAccount']));
});

test('withdrawal session is refreshed from strapi and not manually appended', function () {
    fakeWithdrawalStrapi();

    $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
        'remarks' => 'ATM withdrawal',
    ]);

    $transactions = session('transactions', []);

    expect($transactions)->toBe([]);
});

test('withdrawal appears in session only when returned from strapi refresh', function () {
    fakeWithdrawalStrapi([
        [
            'referenceNumber' => 'WDL-PERSISTED-2026-0001',
            'transactionType' => 'Withdrawal',
            'transferType' => 'Withdrawal',
            'amount' => 100,
            'transactionDate' => '2026-05-10T10:00:00.000Z',
            'transactionStatus' => 'Completed',
            'account' => ['id' => 10, 'accountNumber' => '1001'],
        ],
    ]);

    $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
    ]);

    $referenceNumbers = collect(session('transactions', []))
        ->pluck('referenceNumber')
        ->all();

    expect($referenceNumbers)->toContain('WDL-PERSISTED-2026-0001');
});

test('withdrawal reduces balance', function () {
    fakeWithdrawalStrapi();

    $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
    ]);

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/account-10')
        && ! str_contains($request->url(), '/api/accounts/1001')
        && (float) $request['data']['balance'] === 400.0);
});

test('failed withdrawal transaction creation shows error and does not update balance', function () {
    Http::fake([
        '*localhost:1337/api/transactions*' => Http::response([
            'error' => [
                'message' => 'transferType must be one of the following values',
            ],
        ], 400),
        '*localhost:1337/api/accounts/*' => Http::response(['data' => ['id' => 10]]),
        '*localhost:1337/api/customers*' => Http::response(['data' => []]),
    ]);

    $response = $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
        'remarks' => 'ATM withdrawal',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Withdrawal failed while recording the transaction.');
    $response->assertSessionMissing('success');

    Http::assertNotSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/'));

});

test('failed withdrawal balance update shows error and does not show success', function () {
    Http::fake(function ($request) {
        if ($request->method() === 'GET' && str_contains($request->url(), '/api/transactions')) {
            return Http::response(['data' => []]);
        }

        if ($request->method() === 'POST' && str_contains($request->url(), '/api/transactions')) {
            return Http::response([
                'data' => [
                    'id' => 50,
                    'referenceNumber' => 'WDL-2026-404',
                ],
            ]);
        }

        if ($request->method() === 'PUT' && str_contains($request->url(), '/api/accounts/account-10')) {
            return Http::response([
                'error' => ['message' => 'Not Found'],
            ], 404);
        }

        if ($request->method() === 'PUT' && str_contains($request->url(), '/api/accounts/1001')) {
            return Http::response([
                'error' => ['message' => 'Wrong account identifier'],
            ], 404);
        }

        return Http::response(['data' => []]);
    });

    $response = $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Withdrawal failed while updating account balance.');
    $response->assertSessionMissing('success');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/account-10'));

    Http::assertNotSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/1001'));
});

test('second savings withdrawal creates fee transaction and reduces balance by fee', function () {
    fakeWithdrawalStrapi([priorWithdrawalFixture()]);

    $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 100,
    ]);

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions')
        && str_starts_with((string) $request['data']['referenceNumber'], 'WDL-FEE-')
        && $request['data']['transactionType'] === 'Fee'
        && $request['data']['transferType'] === 'Fee'
        && (float) $request['data']['amount'] === 5.0
        && $request['data']['description'] === 'Savings withdrawal fee'
        && $request['data']['transactionStatus'] === 'Completed'
        && $request['data']['account'] === 10
        && ! isset($request['data']['sourceAccount']));

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/account-10')
        && ! str_contains($request->url(), '/api/accounts/1001')
        && (float) $request['data']['balance'] === 395.0);
});

test('customer cannot withdraw more than balance', function () {
    fakeWithdrawalStrapi();

    $response = $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 10,
        'amount' => 600,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Insufficient balance for this withdrawal.');

    Http::assertNotSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/transactions'));
});

test('customer cannot withdraw from another customers account', function () {
    fakeWithdrawalStrapi();

    $response = $this->withSession(withdrawalCustomerSession())->post('/withdraw', [
        'account_id' => 99,
        'amount' => 100,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Selected account was not found for this customer.');

    Http::assertNotSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/accounts/'));
});

test('savings fee logic counts Withdrawal transactions correctly', function () {
    $account = [
        'id' => 10,
        'documentId' => 'account-10',
        'accountNumber' => '1001',
        'accountType' => 'Savings',
    ];

    $summary = app(MonthlyAccountFeeService::class)->summary($account, [
        [
            'transactionType' => 'Withdrawal',
            'amount' => 100,
            'transactionDate' => '2026-05-10',
            'transactionStatus' => 'Completed',
            'account' => $account,
        ],
        [
            'transactionType' => 'Withdrawal',
            'amount' => 50,
            'transactionDate' => '2026-05-11',
            'transactionStatus' => 'Completed',
            'account' => $account,
        ],
    ], '2026-05');

    expect($summary['withdrawal_count'])->toBe(2)
        ->and($summary['calculated_monthly_fee'])->toBe(5.00);
});

test('savings fee logic does not count savings fee transactions as withdrawals', function () {
    $account = [
        'id' => 10,
        'documentId' => 'account-10',
        'accountNumber' => '1001',
        'accountType' => 'Savings',
    ];

    $summary = app(MonthlyAccountFeeService::class)->summary($account, [
        [
            'referenceNumber' => 'WDL-2026-0001',
            'transactionType' => 'Withdrawal',
            'amount' => 100,
            'transactionDate' => '2026-05-10',
            'transactionStatus' => 'Completed',
            'account' => $account,
        ],
        [
            'referenceNumber' => 'WDL-FEE-2026-0002',
            'transactionType' => 'Withdrawal',
            'amount' => 5,
            'transactionDate' => '2026-05-10',
            'description' => 'Savings withdrawal fee',
            'transactionStatus' => 'Completed',
            'account' => $account,
        ],
    ], '2026-05');

    expect($summary['withdrawal_count'])->toBe(1)
        ->and($summary['calculated_monthly_fee'])->toBe(0.00);
});

test('transactions page shows withdrawal transactions with numeric account relation', function () {
    $session = withdrawalCustomerSession() + [
        'transactions' => [
            [
                'referenceNumber' => 'WDL-2026-1234',
                'transactionType' => 'Withdrawal',
                'amount' => 100,
                'transactionDate' => '2026-05-10',
                'transactionStatus' => 'Completed',
                'account' => 10,
            ],
        ],
    ];

    $response = $this->withSession($session)->get('/transactions?account_id=10');

    $response->assertOk();
    $response->assertSee('WDL-2026-1234');
    $response->assertSee('Withdrawal');
});

test('transactions page shows withdrawal transactions with populated strapi data relation', function () {
    $session = withdrawalCustomerSession() + [
        'transactions' => [
            [
                'referenceNumber' => 'WDL-2026-5678',
                'transactionType' => 'Withdrawal',
                'amount' => 100,
                'transactionDate' => '2026-05-10',
                'transactionStatus' => 'Completed',
                'account' => [
                    'data' => [
                        'id' => 10,
                        'documentId' => 'account-10',
                        'attributes' => [
                            'accountNumber' => '1001',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->withSession($session)->get('/transactions?account_id=10');

    $response->assertOk();
    $response->assertSee('WDL-2026-5678');
    $response->assertSee('Withdrawal');
});

test('withdrawal transaction appears in transaction history from strapi refresh', function () {
    Http::fake([
        '*localhost:1337/api/customers*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'email' => 'customer@example.com',
                    'accounts' => [
                        [
                            'id' => 10,
                            'documentId' => 'account-10',
                            'accountNumber' => '1001',
                            'accountType' => 'Savings',
                            'balance' => 400,
                        ],
                    ],
                ],
            ],
        ]),
        '*localhost:1337/api/transactions*' => Http::response([
            'data' => [
                [
                    'referenceNumber' => 'WDL-2026-9999',
                    'transactionType' => 'Withdrawal',
                    'transferType' => 'Withdrawal',
                    'amount' => 100,
                    'transactionDate' => '2026-05-10T10:00:00.000Z',
                    'description' => 'Cash withdrawal',
                    'transactionStatus' => 'Completed',
                    'account' => [
                        'id' => 10,
                        'documentId' => 'account-10',
                        'accountNumber' => '1001',
                    ],
                    'sourceAccount' => [
                        'id' => 10,
                        'documentId' => 'account-10',
                        'accountNumber' => '1001',
                    ],
                ],
            ],
        ]),
    ]);

    $response = $this->withSession(withdrawalCustomerSession())->get('/transactions');

    $response->assertOk();
    $response->assertSee('WDL-2026-9999');
    $response->assertSee('Withdrawal');
    $response->assertSee('DR');
});

test('withdrawal transaction survives logout reload when strapi relation is account number', function () {
    Http::fake([
        '*localhost:1337/api/customers*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'email' => 'customer@example.com',
                    'accounts' => [
                        [
                            'id' => 10,
                            'documentId' => 'account-10',
                            'accountNumber' => '1001',
                            'accountType' => 'Savings',
                            'balance' => 400,
                        ],
                    ],
                ],
            ],
        ]),
        '*localhost:1337/api/transactions*' => Http::response([
            'data' => [
                [
                    'referenceNumber' => 'WDL-2026-RELOAD',
                    'transactionType' => 'Withdrawal',
                    'transferType' => 'Withdrawal',
                    'amount' => 100,
                    'transactionDate' => '2026-05-10T10:00:00.000Z',
                    'description' => 'Cash withdrawal',
                    'transactionStatus' => 'Completed',
                    'account' => 1001,
                    'sourceAccount' => 1001,
                ],
            ],
        ]),
    ]);

    $response = $this->withSession(withdrawalCustomerSession())->get('/transactions');

    $response->assertOk();
    $response->assertSee('WDL-2026-RELOAD');
    $response->assertSee('Withdrawal');
});

test('withdrawal transaction appears when strapi returns relation as a list', function () {
    Http::fake([
        '*localhost:1337/api/customers*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'email' => 'customer@example.com',
                    'accounts' => [
                        [
                            'id' => 10,
                            'documentId' => 'account-10',
                            'accountNumber' => '1001',
                            'accountType' => 'Savings',
                            'balance' => 400,
                        ],
                    ],
                ],
            ],
        ]),
        '*localhost:1337/api/transactions*' => Http::response([
            'data' => [
                [
                    'referenceNumber' => 'WDL-2026-LIST',
                    'transactionType' => 'Withdrawal',
                    'transferType' => 'Withdrawal',
                    'amount' => 100,
                    'transactionDate' => '2026-05-10T10:00:00.000Z',
                    'description' => 'Cash withdrawal',
                    'transactionStatus' => 'Completed',
                    'account' => [
                        [
                            'id' => 10,
                            'documentId' => 'account-10',
                            'accountNumber' => '1001',
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $response = $this->withSession(withdrawalCustomerSession())->get('/transactions');

    $response->assertOk();
    $response->assertSee('WDL-2026-LIST');
    $response->assertSee('Withdrawal');
});

test('transaction history still shows deposit transfer and bill payment types', function () {
    $session = withdrawalCustomerSession() + [
        'transactions' => [
            [
                'referenceNumber' => 'DEP-1',
                'transactionType' => 'Deposit',
                'transferType' => 'Deposit',
                'amount' => 100,
                'transactionDate' => '2026-05-10',
                'transactionStatus' => 'Completed',
                'account' => ['id' => 10, 'accountNumber' => '1001'],
            ],
            [
                'referenceNumber' => 'TRF-1',
                'transactionType' => 'Transfer',
                'transferType' => 'Internal',
                'amount' => 50,
                'transactionDate' => '2026-05-10',
                'transactionStatus' => 'Completed',
                'sourceAccount' => ['id' => 10, 'accountNumber' => '1001'],
            ],
            [
                'referenceNumber' => 'BILL-1',
                'transactionType' => 'BillPayment',
                'transferType' => 'BillPayment',
                'amount' => 25,
                'transactionDate' => '2026-05-10',
                'transactionStatus' => 'Completed',
                'account' => ['id' => 10, 'accountNumber' => '1001'],
            ],
        ],
    ];

    $response = $this->withSession($session)->get('/transactions');

    $response->assertOk();
    $response->assertSee('Deposit');
    $response->assertSee('Internal Transfer');
    $response->assertSee('Bill Payment');
});
