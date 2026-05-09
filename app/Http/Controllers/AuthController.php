<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

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

        return redirect('/login');
    }
}
