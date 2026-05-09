<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminLoanService;
use Illuminate\View\View;

class AdminLoanController extends Controller
{
    public function __construct(private readonly AdminLoanService $loans) {}

    public function index(): View
    {
        return view('admin.loans.index', [
            'loans' => $this->loans->loanApplications(),
        ]);
    }
}
