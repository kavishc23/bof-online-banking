<?php

use App\Services\Notifications\NotificationSettingsService;
use Illuminate\Support\Facades\Http;

function cs415NotificationAdminSession(): array
{
    return [
        'jwt' => 'admin-token',
        'user_role' => 'Admin',
        'user' => ['email' => 'admin@example.com'],
        'customer' => ['firstName' => 'Admin', 'userRole' => 'Admin'],
    ];
}

function cs415NotificationCustomerSession(): array
{
    return [
        'jwt' => 'customer-token',
        'user_role' => 'Customer',
        'user' => ['email' => 'customer@example.com'],
        'customer' => [
            'email' => 'customer@example.com',
            'accounts' => [
                [
                    'id' => 10,
                    'documentId' => 'account-10',
                    'accountNumber' => '1001',
                    'balance' => 5000,
                ],
            ],
        ],
    ];
}

function cs415FakeNotificationSettings(bool $moneySentEnabled, bool $billPaymentsEnabled = true): void
{
    Http::fake([
        '*localhost:1337/api/notification-settings*' => Http::response([
            'data' => [
                [
                    'id' => 41,
                    'documentId' => 'notification-money',
                    'eventKey' => 'money_sent',
                    'eventLabel' => 'Money Sent',
                    'enabled' => $moneySentEnabled,
                ],
                [
                    'id' => 42,
                    'documentId' => 'notification-bill',
                    'eventKey' => 'bill_paid',
                    'eventLabel' => 'Bill Paid',
                    'enabled' => $billPaymentsEnabled,
                ],
            ],
        ]),
        '*localhost:1337/api/other-local-banks*' => Http::response([
            'data' => [
                ['id' => 20, 'name' => 'Fiji Demo Bank', 'isActive' => true],
            ],
        ]),
        '*localhost:1337/api/accounts/*' => Http::response(['data' => ['id' => 10]]),
        '*localhost:1337/api/transactions*' => Http::response(['data' => []]),
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
                            'balance' => 4900,
                        ],
                    ],
                ],
            ],
        ]),
    ]);
}

function cs415NotificationTransferPayload(): array
{
    return [
        'transfer_mode' => 'local_bank',
        'from_account_id' => 10,
        'destination_institution_id' => 20,
        'destination_account_number' => '998877',
        'beneficiary_name' => 'Demo Recipient',
        'amount' => 100,
        'description' => 'CS415 notification test transfer',
    ];
}

test('CS415 notification settings: admin can enable and disable notification services', function () {
    cs415FakeNotificationSettings(moneySentEnabled: true);

    $response = $this->withSession(cs415NotificationAdminSession())->patch('/admin/notification-settings', [
        'settings' => [
            'money_sent' => '0',
            'bill_paid' => '1',
        ],
    ]);

    $response->assertRedirect('/admin/notification-settings');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/notification-settings/notification-money')
        && $request['data']['enabled'] === false);

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/notification-settings/notification-bill')
        && $request['data']['enabled'] === true);
});

test('CS415 notification settings: disabled notification event should not display notification toast', function () {
    cs415FakeNotificationSettings(moneySentEnabled: false);

    $response = $this->withSession(cs415NotificationCustomerSession())->post('/transfer', cs415NotificationTransferPayload());

    $response->assertRedirect('/dashboard');
    $response->assertSessionMissing('success');
});

test('CS415 notification settings: enabled notification event should trigger notification behavior', function () {
    cs415FakeNotificationSettings(moneySentEnabled: true);

    $response = $this->withSession(cs415NotificationCustomerSession())->post('/transfer', cs415NotificationTransferPayload());

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Transaction successful.')
        && str_contains($message, 'SMS confirmation sent.'));
});

test('CS415 notification service returns enabled state from Strapi setting', function () {
    cs415FakeNotificationSettings(moneySentEnabled: true);

    expect(app(NotificationSettingsService::class)->isEnabled('money_sent'))->toBeTrue();
});
