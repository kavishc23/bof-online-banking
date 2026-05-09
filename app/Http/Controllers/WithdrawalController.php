<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawalRequest;
use App\Services\Transactions\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals) {}

    public function create(): View
    {
        return view('withdrawals.create', [
            'customer' => session('customer'),
        ]);
    }

    public function store(WithdrawalRequest $request): RedirectResponse
    {
        $customer = session('customer');
        $user = session('user');

        if (! $customer || empty($customer['accounts'])) {
            return back()->with('error', 'No customer accounts found.');
        }

        $result = $this->withdrawals->withdraw($request->validated(), $customer, $user ?? []);

        if (! $result['successful']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('dashboard')->with('success', $result['message']);
    }
}
