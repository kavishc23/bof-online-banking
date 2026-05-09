<?php

use Illuminate\Support\Facades\Http;

function adminSession(): array
{
    return [
        'jwt' => 'test-token',
        'user_role' => 'Admin',
        'user' => ['email' => 'admin@example.com'],
        'customer' => ['firstName' => 'Admin', 'userRole' => 'Admin'],
    ];
}

function customerSession(): array
{
    return [
        'jwt' => 'test-token',
        'user_role' => 'Customer',
        'user' => ['email' => 'customer@example.com'],
        'customer' => ['firstName' => 'Customer', 'userRole' => 'Customer'],
    ];
}

function fakeAdminStrapi(): void
{
    Http::fake([
        '*localhost:1337/api/accounts*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'documentId' => 'account-1',
                    'accountNumber' => '1001',
                    'accountType' => 'Savings',
                    'balance' => 1000,
                    'monthlyMaintenanceFee' => 0,
                    'interestRate' => 0.02,
                    'openedAt' => '2026-05-09T00:00:00.000Z',
                ],
            ],
        ]),
        '*localhost:1337/api/transactions*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'referenceNumber' => 'TXN-1',
                    'transactionType' => 'Deposit',
                    'amount' => 100,
                    'transactionDate' => '2026-05-09T00:00:00.000Z',
                    'transactionStatus' => 'Completed',
                ],
            ],
        ]),
        '*localhost:1337/api/loan-applications*' => Http::response([
            'data' => [
                [
                    'id' => 31,
                    'documentId' => 'loan-31',
                    'referenceNumber' => 'LOAN-31',
                    'customerEmail' => 'jane@example.com',
                    'loanType' => 'Personal',
                    'amountRequested' => 5000,
                    'repaymentMonths' => 24,
                    'applicationStatus' => 'Pending',
                    'submittedAt' => '2026-05-09T00:00:00.000Z',
                ],
            ],
        ]),
        '*localhost:1337/api/support-tickets*' => Http::response([
            'data' => [
                [
                    'id' => 10,
                    'documentId' => 'ticket-10',
                    'ticketNumber' => 'SUP-10',
                    'customerName' => 'Jane Customer',
                    'customerEmail' => 'jane@example.com',
                    'subject' => 'Card issue',
                    'message' => 'My card is blocked.',
                    'consultantReply' => null,
                    'ticketStatus' => 'Open',
                    'satisfactionRating' => null,
                    'createdAt' => '2026-05-09T00:00:00.000Z',
                ],
            ],
        ]),
        '*localhost:1337/api/notification-settings*' => Http::response([
            'data' => [
                [
                    'id' => 21,
                    'documentId' => 'notification-21',
                    'eventKey' => 'money_sent',
                    'eventLabel' => 'Money Sent',
                    'enabled' => true,
                    'description' => 'Notify customers when money is sent.',
                ],
            ],
        ]),
    ]);
}

test('customer cannot access admin area', function () {
    $response = $this->withSession(customerSession())->get('/admin');

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('error', 'You are not authorized to access the admin area.');
});

test('admin can access admin dashboard', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->get('/admin');

    $response->assertOk();
    $response->assertSee('Admin Dashboard');
});

test('admin can view accounts', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->get('/admin/accounts');

    $response->assertOk();
    $response->assertSee('1001');
});

test('admin can view all transactions', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->get('/admin/transactions');

    $response->assertOk();
    $response->assertSee('TXN-1');
});

test('admin can view loan applications', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->get('/admin/loans');

    $response->assertOk();
    $response->assertSee('LOAN-31');
});

test('admin can view notification settings', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->get('/admin/notification-settings');

    $response->assertOk();
    $response->assertSee('Money Sent');
});

test('admin can update notification setting', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->patch('/admin/notification-settings', [
        'settings' => [
            'money_sent' => '0',
        ],
    ]);

    $response->assertRedirect('/admin/notification-settings');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/notification-settings/notification-21')
        && $request['data']['enabled'] === false);
});

test('admin can view support tickets', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->get('/admin/support-tickets');

    $response->assertOk();
    $response->assertSee('SUP-10');
});

test('admin can update ticket status', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->patch('/admin/support-tickets/ticket-10', [
        'consultantReply' => 'Your issue has been resolved.',
        'ticketStatus' => 'Resolved',
    ]);

    $response->assertRedirect('/admin/support-tickets/ticket-10');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-10')
        && $request['data']['ticketStatus'] === 'Resolved'
        && array_key_exists('resolvedAt', $request['data']));
});

test('admin can create account', function () {
    fakeAdminStrapi();

    $response = $this->withSession(adminSession())->post('/admin/accounts', [
        'accountNumber' => '1002',
        'accountType' => 'Savings',
        'balance' => '1000',
        'monthlyMaintenanceFee' => '0',
        'interestRate' => '0.02',
        'openedAt' => '2026-05-09',
        'customer' => '5',
    ]);

    $response->assertRedirect('/admin/accounts');

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/accounts')
        && $request['data']['accountNumber'] === '1002'
        && $request['data']['accountType'] === 'Savings'
        && $request['data']['customer'] === 5);
});

test('admin login redirects directly to admin module', function () {
    Http::fake([
        '*localhost:1337/api/auth/local' => Http::response([
            'jwt' => 'admin-token',
            'user' => [
                'email' => 'admin@example.com',
                'username' => 'admin',
            ],
        ]),
        '*localhost:1337/api/customers*' => Http::response([
            'data' => [
                [
                    'id' => 99,
                    'documentId' => 'customer-99',
                    'email' => 'admin@example.com',
                    'firstName' => 'Admin',
                    'userRole' => 'Admin',
                    'accounts' => [],
                ],
            ],
        ]),
        '*localhost:1337/api/transactions*' => Http::response(['data' => []]),
    ]);

    $response = $this->post('/login', [
        'identifier' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
    $response->assertSessionHas('user_role', 'Admin');
});
