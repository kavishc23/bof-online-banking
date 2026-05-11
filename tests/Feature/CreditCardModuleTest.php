<?php

use App\Models\CreditCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function creditCardSession(): array
{
    return [
        'jwt' => 'local-demo-token',
        'user_role' => 'Customer',
        'user' => ['email' => 'customer@example.com', 'username' => 'demo'],
        'customer' => [
            'email' => 'customer@example.com',
            'firstName' => 'Demo',
            'lastName' => 'Customer',
            'userRole' => 'Customer',
        ],
    ];
}

test('customer can view dummy credit card dashboard', function () {
    $response = $this->withSession(creditCardSession())->get('/credit-cards');

    $response->assertOk();
    $response->assertSee('Visa Platinum');
    $response->assertSee('**** **** **** 4821');
    $response->assertSee('FJD 10,000.00');

    expect(CreditCard::query()->where('customer_email', 'customer@example.com')->exists())->toBeTrue();
});

test('customer can link a credit card locally', function () {
    $response = $this->withSession(creditCardSession())->post('/credit-cards/link', [
        'card_reference_number' => 'BOF-CC-9999',
        'last_four_digits' => '9999',
        'cardholder_name' => 'Demo Customer',
    ]);

    $response->assertRedirect('/credit-cards');
    $response->assertSessionHas('success', 'Card Linked Successfully.');

    expect(CreditCard::query()->where('masked_card_number', '**** **** **** 9999')->exists())->toBeTrue();
});

test('payment reduces outstanding balance and increases available credit', function () {
    $this->withSession(creditCardSession())->get('/credit-cards');
    $card = CreditCard::query()->firstOrFail();

    $response = $this->withSession(creditCardSession())->post('/credit-cards/pay', [
        'amount' => 150,
        'payment_source' => 'BoF Savings Account',
    ]);

    $response->assertRedirect('/credit-cards');
    $response->assertSessionHas('success');

    $card->refresh();

    expect((float) $card->outstanding_balance)->toBe(2300.0)
        ->and((float) $card->available_credit)->toBe(7700.0)
        ->and($card->transactions()->where('transaction_type', 'Payment')->exists())->toBeTrue();
});

test('payment cannot exceed outstanding balance', function () {
    $this->withSession(creditCardSession())->get('/credit-cards');

    $response = $this->withSession(creditCardSession())->post('/credit-cards/pay', [
        'amount' => 99999,
        'payment_source' => 'BoF Savings Account',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Payment amount cannot exceed the outstanding balance.');
});
