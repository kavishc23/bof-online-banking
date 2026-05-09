<?php

namespace App\Http\Controllers;

use App\Events\BankingActivityOccurred;
use App\Http\Requests\LoginRequest;
use App\Services\BofService;
use App\Services\Logging\BankingLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly BofService $bofService,
        private readonly BankingLogger $logger,
    ) {}

    public function showLogin(): View
    {
        return view('login');
    }

    public function redirectToLogin(): RedirectResponse
    {
        return redirect('/login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $loginResponse = Http::post('http://localhost:1337/api/auth/local', [
                'identifier' => $request->identifier,
                'password' => $request->password,
            ]);

            if (! $loginResponse->successful()) {
                $errorMessage = $loginResponse->json()['error']['message'] ?? 'Invalid login details';
                $this->logger->activity('login.failed', 'Customer login failed.', [
                    'identifier' => $request->identifier,
                    'status' => $loginResponse->status(),
                ]);

                return back()->withInput()->with('error', $errorMessage);
            }

            $loginData = $loginResponse->json();
            $jwt = $loginData['jwt'];
            $user = $loginData['user'];
            $result = $this->bofService->fetchCustomerAndTransactions($jwt, $user);

            session([
                'jwt' => $jwt,
                'user' => $user,
                'customer' => $result['customer'],
                'transactions' => $result['transactions'],
            ]);

            event(new BankingActivityOccurred('login.succeeded', 'Customer login succeeded.', [
                'email' => $user['email'] ?? $request->identifier,
            ]));

            return redirect('/dashboard');
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Login failed. Please try again.');
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget([
            'jwt',
            'user',
            'customer',
            'transactions',
            'pending_otp',
            'pending_action',
            'pending_payload',
            'otp_expires_at',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        event(new BankingActivityOccurred('logout.succeeded', 'Customer logged out.'));

        return redirect('/login');
    }
}
