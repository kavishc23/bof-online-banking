<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminTransactionService;
use Illuminate\View\View;

class AdminTransactionController extends Controller
{
    public function __construct(private readonly AdminTransactionService $transactions) {}

    public function index(): View
    {
        return view('admin.transactions.index', [
            'transactions' => $this->transactions->transactions(),
        ]);
    }
}
