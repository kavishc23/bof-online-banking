<?php

use Illuminate\Support\Facades\Http;

function notificationToastCustomerSession(): array
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

function fakeNotificationToastStrapi(string $eventKey, bool $enabled): void
{
    Http::fake([
        '*localhost:1337/api/notification-settings*' => Http::response([
            'data' => [
                [
                    'id' => 41,
                    'documentId' => 'notification-41',
                    'eventKey' => $eventKey,
                    'enabled' => $enabled,
                ],
            ],
        ]),
        '*localhost:1337/api/other-local-banks*' => Http::response([
            'data' => [
                [
                    'id' => 20,
                    'name' => 'Fiji Demo Bank',
                    'isActive' => true,
                ],
            ],
        ]),
        '*localhost:1337/api/billers*' => Http::response([
            'data' => [
                [
                    'id' => 30,
                    'name' => 'Water Authority',
                    'isActive' => true,
                ],
            ],
        ]),
        '*localhost:1337/api/accounts/*' => Http::response(['data' => ['id' => 10]]),
        '*localhost:1337/api/bill-payments*' => Http::response(['data' => ['id' => 70]]),
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

function transferPayloadForNotificationToast(): array
{
    return [
        'transfer_mode' => 'local_bank',
        'from_account_id' => 10,
        'destination_institution_id' => 20,
        'destination_account_number' => '998877',
        'beneficiary_name' => 'Demo Recipient',
        'amount' => 100,
        'description' => 'Demo transfer',
    ];
}

function billPaymentPayloadForNotificationToast(): array
{
    return [
        'from_account_id' => 10,
        'biller_id' => 30,
        'bill_reference' => 'WAF-123',
        'amount' => 100,
        'notes' => 'Demo bill',
    ];
}

test('money sent enabled shows sms confirmation in success flash', function () {
    fakeNotificationToastStrapi('money_sent', true);

    $response = $this->withSession(notificationToastCustomerSession())->post('/transfer', transferPayloadForNotificationToast());

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Transaction successful.')
        && str_contains($message, 'SMS confirmation sent.'));
});

test('money sent disabled does not show a success notification flash', function () {
    fakeNotificationToastStrapi('money_sent', false);

    $response = $this->withSession(notificationToastCustomerSession())->post('/transfer', transferPayloadForNotificationToast());

    $response->assertRedirect('/dashboard');
    $response->assertSessionMissing('success');
});

test('bill payments enabled shows sms confirmation in success flash', function () {
    fakeNotificationToastStrapi('bill_paid', true);

    $response = $this->withSession(notificationToastCustomerSession())->post('/bill-payment', billPaymentPayloadForNotificationToast());

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Bill payment successful.')
        && str_contains($message, 'SMS confirmation sent.'));
});

test('bill payments disabled does not show a success notification flash', function () {
    fakeNotificationToastStrapi('bill_paid', false);

    $response = $this->withSession(notificationToastCustomerSession())->post('/bill-payment', billPaymentPayloadForNotificationToast());

    $response->assertRedirect('/dashboard');
    $response->assertSessionMissing('success');
});
