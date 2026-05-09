<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class InvestmentController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function create(): View|RedirectResponse
    {
        return view('investments', [
            'customer' => session('customer'),
        ]);
    }

    public function store(InvestmentRequest $request): RedirectResponse
    {
        $jwt = session('jwt');
        $user = session('user');
        $customer = session('customer');
        $selectedAccount = null;

        foreach (($customer['accounts'] ?? []) as $account) {
            if (($account['accountNumber'] ?? '') === $request->funding_account_number) {
                $selectedAccount = $account;
                break;
            }
        }

        if (! $selectedAccount) {
            return back()->withInput()->with('error', 'Selected funding account not found.');
        }

        $amount = (float) $request->amount;

        if ($amount > (float) ($selectedAccount['balance'] ?? 0)) {
            return back()->withInput()->with('error', 'Insufficient balance in the selected funding account.');
        }

        $referenceNumber = 'INV-'.time();

        try {
            $response = Http::withToken($jwt)->post('http://localhost:1337/api/investments', [
                'data' => [
                    'referenceNumber' => $referenceNumber,
                    'investmentType' => $request->investment_type,
                    'fundingAccountNumber' => $request->funding_account_number,
                    'amount' => $amount,
                    'termMonths' => (int) $request->term_months,
                    'interestRate' => (float) $request->interest_rate,
                    'estimatedReturn' => $request->estimated_return !== null ? (float) $request->estimated_return : null,
                    'estimatedMaturityAmount' => $request->estimated_maturity_amount !== null ? (float) $request->estimated_maturity_amount : null,
                    'startDate' => $request->start_date,
                    'maturityDate' => $request->maturity_date,
                    'maturityInstruction' => $request->maturity_instruction,
                    'riskLevel' => $request->risk_level,
                    'liquidityType' => $request->liquidity_type,
                    'investmentStatus' => 'Pending',
                    'nomineeName' => $request->nominee_name,
                    'nomineeRelationship' => $request->nominee_relationship,
                    'nomineeContact' => $request->nominee_contact,
                    'productDescription' => $request->product_description,
                    'customerEmail' => $user['email'] ?? '',
                    'submittedAt' => now()->toISOString(),
                    'lastUpdatedAt' => now()->toISOString(),
                ],
            ]);

            if (! $response->successful()) {
                $this->bofService->reportApiFailure('investment_create', $response);

                return back()->withInput()->with('error', 'Investment could not be submitted. Please try again.');
            }

            return redirect()->route('my-investments')->with(
                'success',
                'Investment submitted successfully. Reference: '.$referenceNumber
            );
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Investment could not be submitted. Please try again.');
        }
    }

    public function index(): View|RedirectResponse
    {
        $user = session('user');

        return view('my-investments', [
            'customer' => session('customer'),
            'investments' => $this->bofService->fetchInvestments(session('jwt'), $user['email'] ?? ''),
        ]);
    }
}
