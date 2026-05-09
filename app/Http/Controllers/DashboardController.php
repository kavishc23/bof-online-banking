<?php

namespace App\Http\Controllers;

use App\Services\AccountFees\MonthlyAccountFeeService;
use App\Services\BofService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BofService $bofService,
        private readonly MonthlyAccountFeeService $monthlyAccountFeeService,
    ) {}

    public function index(): View|RedirectResponse
    {
        $customer = session('customer');
        $transactions = session('transactions', []);
        $accounts = $customer['accounts'] ?? [];
        $primaryAccount = $accounts[0] ?? null;
        $recentTransactions = array_slice($transactions, 0, 5);
        $selectedFeeMonth = now()->format('Y-m');
        $monthlyAccountFees = $this->monthlyAccountFeeService->summaries($accounts, $transactions, $selectedFeeMonth);
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
            'monthlyAccountFees',
            'selectedFeeMonth',
            'totalIncoming',
            'totalOutgoing',
            'billPaymentCount'
        ))->with('user', session('user'));
    }

    public function transactions(): View|RedirectResponse
    {
        $customer = session('customer');
        $transactions = session('transactions', []);
        $freshData = $this->bofService->fetchCustomerAndTransactions((string) session('jwt'), session('user', []));

        if (! empty($freshData['customer'])) {
            $customer = $freshData['customer'];
            $transactions = collect($transactions)
                ->merge($freshData['transactions'])
                ->keyBy(fn (array $transaction): string => (string) ($transaction['referenceNumber'] ?? uniqid('tx-', true)))
                ->sortByDesc(fn (array $transaction): string => (string) ($transaction['transactionDate'] ?? ''))
                ->values()
                ->all();

            session([
                'customer' => $customer,
                'transactions' => $transactions,
            ]);
        }

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
