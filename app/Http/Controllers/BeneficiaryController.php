<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeneficiaryRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BeneficiaryController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function index(): View|RedirectResponse
    {
        $user = session('user');

        return view('beneficiaries', [
            'customer' => session('customer'),
            'beneficiaries' => $this->bofService->fetchBeneficiaries(session('jwt'), $user['email'] ?? ''),
        ]);
    }

    public function store(BeneficiaryRequest $request): RedirectResponse
    {
        $user = session('user');

        return $this->bofService->createBeneficiary($request, session('jwt'), $user['email'] ?? '');
    }
}
