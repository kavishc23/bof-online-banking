<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditCardPaymentRequest;
use App\Http\Requests\LinkCreditCardRequest;
use App\Models\CreditCard;
use App\Services\CreditCards\CreditCardDemoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreditCardController extends Controller
{
    public function __construct(private readonly CreditCardDemoService $cards) {}

    public function index(): View
    {
        $card = $this->cards->primaryCard()->load('transactions');

        return view('credit-cards.index', [
            'card' => $card,
            'recentTransactions' => $card->transactions()->limit(5)->get(),
            'utilization' => $card->utilization_percentage,
            'creditScore' => 742,
            'rewardGoal' => 5000,
        ]);
    }

    public function show(CreditCard $creditCard): View
    {
        $this->authorizeCustomerCard($creditCard);

        return view('credit-cards.show', [
            'card' => $creditCard->load('transactions'),
            'utilization' => $creditCard->utilization_percentage,
            'creditScore' => 742,
        ]);
    }

    public function transactions(): View
    {
        $card = $this->cards->primaryCard();

        return view('credit-cards.transactions', [
            'card' => $card,
            'transactions' => $card->transactions()->paginate(12),
        ]);
    }

    public function payment(): View
    {
        return view('credit-cards.pay', [
            'card' => $this->cards->primaryCard(),
        ]);
    }

    public function pay(CreditCardPaymentRequest $request): RedirectResponse
    {
        $card = $this->cards->primaryCard();
        $amount = (float) $request->validated('amount');

        if ($amount > (float) $card->outstanding_balance) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount cannot exceed the outstanding balance.');
        }

        $this->cards->makePayment(
            $card,
            $amount,
            (string) $request->validated('payment_source'),
            $request->validated('notes')
        );

        $message = $amount < (float) $card->minimum_payment_due
            ? 'Payment successful. Minimum payment warning: this payment is below the current minimum due.'
            : 'Payment Successful. Your available credit has been updated.';

        return redirect()->route('credit-cards.index')->with('success', $message);
    }

    public function link(): View
    {
        return view('credit-cards.link');
    }

    public function storeLink(LinkCreditCardRequest $request): RedirectResponse
    {
        $this->cards->linkCard($request->validated());

        return redirect()->route('credit-cards.index')->with('success', 'Card Linked Successfully.');
    }

    public function toggleFreeze(CreditCard $creditCard): RedirectResponse
    {
        $this->authorizeCustomerCard($creditCard);
        $card = $this->cards->toggleFreeze($creditCard);

        return back()->with('success', $card->card_status === 'Frozen' ? 'Card frozen successfully.' : 'Card reactivated successfully.');
    }

    private function authorizeCustomerCard(CreditCard $creditCard): void
    {
        abort_unless($creditCard->customer_email === $this->cards->customerEmail(), 403);
    }
}
