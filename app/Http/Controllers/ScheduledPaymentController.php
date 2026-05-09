<?php

namespace App\Http\Controllers;

use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduledPaymentController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function index(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $user = session('user');
        $email = $user['email'] ?? '';

        return view('scheduled-payments', [
            'customer' => session('customer'),
            'scheduledTransfers' => $this->bofService->fetchScheduledTransfers(session('jwt'), $email),
            'scheduledBillPayments' => $this->bofService->fetchScheduledBillPayments(session('jwt'), $email),
        ]);
    }
}
