<?php

namespace App\Services\CreditCards;

use App\Models\CreditCard;
use Carbon\Carbon;

class CreditCardDemoService
{
    public function customerEmail(): string
    {
        return (string) (session('customer.email') ?? session('user.email') ?? 'demo.customer@bof.local');
    }

    public function customerName(): string
    {
        $firstName = (string) session('customer.firstName');
        $lastName = (string) session('customer.lastName');
        $name = trim($firstName.' '.$lastName);

        return $name !== '' ? $name : (string) (session('user.username') ?? 'Kavish Chandra');
    }

    public function primaryCard(): CreditCard
    {
        $card = CreditCard::query()
            ->where('customer_email', $this->customerEmail())
            ->latest()
            ->first();

        return $card ?? $this->createDemoCard();
    }

    public function linkCard(array $validated): CreditCard
    {
        $lastFour = (string) $validated['last_four_digits'];
        $creditLimit = 10000.00;
        $outstandingBalance = 2450.00;

        $card = CreditCard::query()->create([
            'customer_email' => $this->customerEmail(),
            'customer_name' => $validated['cardholder_name'],
            'card_type' => 'Visa Platinum',
            'masked_card_number' => '**** **** **** '.$lastFour,
            'credit_limit' => $creditLimit,
            'outstanding_balance' => $outstandingBalance,
            'available_credit' => $creditLimit - $outstandingBalance,
            'minimum_payment_due' => 150.00,
            'payment_due_date' => Carbon::create(2026, 5, 25),
            'reward_points' => 4200,
            'card_status' => 'Active',
            'card_reference_number' => $validated['card_reference_number'],
            'linked_at' => now(),
        ]);

        $this->seedTransactions($card);

        return $card;
    }

    public function makePayment(CreditCard $card, float $amount, string $source, ?string $notes = null): CreditCard
    {
        $newOutstanding = max(0, round((float) $card->outstanding_balance - $amount, 2));
        $card->update([
            'outstanding_balance' => $newOutstanding,
            'available_credit' => round((float) $card->credit_limit - $newOutstanding, 2),
            'minimum_payment_due' => $newOutstanding <= 0 ? 0 : min((float) $card->minimum_payment_due, $newOutstanding),
        ]);

        $card->transactions()->create([
            'merchant' => 'BoF Card Payment',
            'transaction_date' => now()->toDateString(),
            'amount' => $amount,
            'transaction_type' => 'Payment',
            'status' => 'Posted',
            'reference_number' => 'CCPAY-'.now()->format('Y').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'description' => trim('Payment from '.$source.'. '.(string) $notes),
        ]);

        return $card->refresh();
    }

    public function toggleFreeze(CreditCard $card): CreditCard
    {
        $isFrozen = $card->card_status === 'Frozen';

        $card->update([
            'card_status' => $isFrozen ? 'Active' : 'Frozen',
            'frozen_at' => $isFrozen ? null : now(),
        ]);

        return $card->refresh();
    }

    public function createDemoCard(): CreditCard
    {
        $card = CreditCard::query()->create([
            'customer_email' => $this->customerEmail(),
            'customer_name' => $this->customerName(),
            'card_type' => 'Visa Platinum',
            'masked_card_number' => '**** **** **** 4821',
            'credit_limit' => 10000.00,
            'outstanding_balance' => 2450.00,
            'available_credit' => 7550.00,
            'minimum_payment_due' => 150.00,
            'payment_due_date' => Carbon::create(2026, 5, 25),
            'reward_points' => 4200,
            'card_status' => 'Active',
            'card_reference_number' => 'BOF-CC-4821',
            'linked_at' => now(),
        ]);

        $this->seedTransactions($card);

        return $card;
    }

    private function seedTransactions(CreditCard $card): void
    {
        if ($card->transactions()->exists()) {
            return;
        }

        foreach ([
            ['Amazon Purchase', '2026-05-07', 189.95, 'Purchase', 'Posted'],
            ['Vodafone Fiji', '2026-05-06', 45.00, 'Bill Payment', 'Posted'],
            ['Fuel Station', '2026-05-04', 112.40, 'Purchase', 'Posted'],
            ['Restaurant Payment', '2026-05-02', 86.75, 'Purchase', 'Posted'],
            ['Netflix Subscription', '2026-05-01', 21.99, 'Subscription', 'Posted'],
            ['Airline Booking', '2026-04-28', 840.00, 'Travel', 'Posted'],
        ] as [$merchant, $date, $amount, $type, $status]) {
            $card->transactions()->create([
                'merchant' => $merchant,
                'transaction_date' => $date,
                'amount' => $amount,
                'transaction_type' => $type,
                'status' => $status,
                'reference_number' => 'CCTX-'.str_replace('-', '', $date).'-'.random_int(100, 999),
                'description' => $merchant.' card activity',
            ]);
        }
    }
}
