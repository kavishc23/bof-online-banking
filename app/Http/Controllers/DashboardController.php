<?php

namespace App\Http\Controllers;

use App\Services\BofService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function index(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $customer = session('customer');
        $transactions = session('transactions', []);
        $accounts = $customer['accounts'] ?? [];
        $primaryAccount = $accounts[0] ?? null;
        $recentTransactions = array_slice($transactions, 0, 5);
        $totalIncoming = 0;
        $totalOutgoing = 0;
        $billPaymentCount = 0;

        foreach ($transactions as $transaction) {
            $transactionType = strtolower($transaction['transactionType'] ?? '');
            $transferType = strtolower($transaction['transferType'] ?? '');
            $amount = (float) ($transaction['amount'] ?? 0);

            if ($transactionType === 'deposit' || $transferType === 'deposit') {
                $totalIncoming += $amount;
            } else {
                $totalOutgoing += $amount;
            }

            if ($transferType === 'billpayment') {
                $billPaymentCount++;
            }
        }

        return view('dashboard', compact(
            'customer',
            'accounts',
            'primaryAccount',
            'transactions',
            'recentTransactions',
            'totalIncoming',
            'totalOutgoing',
            'billPaymentCount'
        ))->with('user', session('user'));
    }

    public function transactions(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $customer = session('customer');
        $transactions = session('transactions', []);
        $accounts = $customer['accounts'] ?? [];
        $selectedAccountId = request('account_id');

        if (! empty($selectedAccountId)) {
            $transactions = array_values(array_filter($transactions, function ($transaction) use ($selectedAccountId) {
                return $this->bofService->transactionBelongsToAccount($transaction, (int) $selectedAccountId);
            }));
        }

        return view('transactions', [
            'customer' => $customer,
            'transactions' => $transactions,
            'accounts' => $accounts,
            'selectedAccountId' => $selectedAccountId,
            'statements' => [],
        ]);
    }

    public function debugCustomerSession(): JsonResponse
    {
        return response()->json([
            'user' => session('user'),
            'customer' => session('customer'),
        ]);
    }
}
