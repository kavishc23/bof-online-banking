<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTransactionController extends Controller
{
    public function __construct(private readonly AdminTransactionService $transactions) {}

    public function index(Request $request): View
    {
        return view('admin.transactions.index', [
            'transactions' => $this->transactions->filteredTransactions($request->query()),
            'filters' => $request->query(),
        ]);
    }

    public function show(string $id): View|RedirectResponse
    {
        $transaction = $this->transactions->find($id);

        if (! $transaction) {
            return redirect()->route('admin.transactions.index')->with('error', 'Transaction not found.');
        }

        return view('admin.transactions.show', [
            'transaction' => $transaction,
        ]);
    }
}
