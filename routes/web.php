<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'identifier' => 'required|string',
        'password' => 'required|string',
    ]);

    $loginResponse = Http::post('http://localhost:1337/api/auth/local', [
        'identifier' => $request->identifier,
        'password' => $request->password,
    ]);

    if (!$loginResponse->successful()) {
        $errorMessage = 'Invalid login details';
        $json = $loginResponse->json();

        if (isset($json['error']['message'])) {
            $errorMessage = $json['error']['message'];
        }

        return back()->withInput()->with('error', $errorMessage);
    }

    $loginData = $loginResponse->json();
    $jwt = $loginData['jwt'];
    $user = $loginData['user'];

    $customerResponse = Http::withToken($jwt)->get(
        'http://localhost:1337/api/customers?populate=*'
    );

    $customerRows = $customerResponse->json()['data'] ?? [];
    $matchedCustomer = null;

    foreach ($customerRows as $customer) {
        if (
            isset($customer['email']) &&
            isset($user['email']) &&
            strtolower(trim((string) $customer['email'])) === strtolower(trim((string) $user['email']))
        ) {
            $matchedCustomer = $customer;
            break;
        }
    }

    $transactions = [];

    if ($matchedCustomer && !empty($matchedCustomer['accounts'])) {
        $accountIds = array_map(function ($account) {
            return $account['id'];
        }, $matchedCustomer['accounts']);

        $transactionResponse = Http::withToken($jwt)->get(
            'http://localhost:1337/api/transactions?populate=*'
        );

        $transactionRows = $transactionResponse->json()['data'] ?? [];

        foreach ($transactionRows as $transaction) {
            if (
                isset($transaction['account']) &&
                isset($transaction['account']['id']) &&
                in_array($transaction['account']['id'], $accountIds)
            ) {
                $transactions[] = $transaction;
            }
        }
    }

    session([
        'jwt' => $jwt,
        'user' => $user,
        'customer' => $matchedCustomer,
        'transactions' => $transactions,
    ]);

    return redirect('/dashboard');
})->name('login.post');

Route::get('/dashboard', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    return view('dashboard', [
        'user' => session('user'),
        'customer' => session('customer'),
        'transactions' => session('transactions', []),
    ]);
})->name('dashboard');

Route::post('/logout', function (Request $request) {
    session()->forget(['jwt', 'user', 'customer', 'transactions']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');