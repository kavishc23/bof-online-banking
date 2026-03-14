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

Route::get('/transactions', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    return view('transactions', [
        'customer' => session('customer'),
        'transactions' => session('transactions', []),
    ]);
})->name('transactions');

Route::get('/transfer', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    return view('transfer', [
        'customer' => session('customer'),
    ]);
})->name('transfer');

Route::post('/transfer', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'from_account_id' => 'required|integer',
        'to_account_number' => 'required|string',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string',
    ]);

    $jwt = session('jwt');
    $customer = session('customer');
    $user = session('user');

    if (!$customer || empty($customer['accounts'])) {
        return back()->with('error', 'No customer accounts found.');
    }

    $fromAccount = null;

    foreach ($customer['accounts'] as $account) {
        if ((int) $account['id'] === (int) $request->from_account_id) {
            $fromAccount = $account;
            break;
        }
    }

    if (!$fromAccount) {
        return back()->with('error', 'Selected source account not found.');
    }

    $amount = (float) $request->amount;
    $fromBalance = (float) ($fromAccount['balance'] ?? 0);

    if ($amount > $fromBalance) {
        return back()->withInput()->with('error', 'Insufficient balance.');
    }

    $accountsResponse = Http::withToken($jwt)->get(
        'http://localhost:1337/api/accounts?populate=*'
    );

    $allAccounts = $accountsResponse->json()['data'] ?? [];
    $toAccount = null;

    foreach ($allAccounts as $account) {
        if (
            isset($account['accountNumber']) &&
            trim((string) $account['accountNumber']) === trim((string) $request->to_account_number)
        ) {
            $toAccount = $account;
            break;
        }
    }

    if (!$toAccount) {
        return back()->withInput()->with('error', 'Destination account not found.');
    }

    if ((int) $toAccount['id'] === (int) $fromAccount['id']) {
        return back()->withInput()->with('error', 'Cannot transfer to the same account.');
    }

    $newFromBalance = $fromBalance - $amount;
    $toBalance = (float) ($toAccount['balance'] ?? 0);
    $newToBalance = $toBalance + $amount;

    $updateFromResponse = Http::withToken($jwt)->put(
        "http://localhost:1337/api/accounts/{$fromAccount['documentId']}",
        [
            'data' => [
                'balance' => $newFromBalance,
            ],
        ]
    );

    $updateToResponse = Http::withToken($jwt)->put(
        "http://localhost:1337/api/accounts/{$toAccount['documentId']}",
        [
            'data' => [
                'balance' => $newToBalance,
            ],
        ]
    );

    if (!$updateFromResponse->successful() || !$updateToResponse->successful()) {
        return back()->withInput()->with('error', 'Transfer failed while updating balances.');
    }

    $referenceOut = 'TXN-OUT-' . time();
    $referenceIn = 'TXN-IN-' . time();

    $outgoingTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
        'data' => [
            'referenceNumber' => $referenceOut,
            'transactionType' => 'Transfer',
            'amount' => $amount,
            'transactionDate' => now()->toISOString(),
            'description' => $request->description ?: 'Transfer to account ' . $toAccount['accountNumber'],
            'account' => $fromAccount['id'],
        ],
    ]);

    $incomingTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
        'data' => [
            'referenceNumber' => $referenceIn,
            'transactionType' => 'Deposit',
            'amount' => $amount,
            'transactionDate' => now()->toISOString(),
            'description' => 'Transfer received from account ' . $fromAccount['accountNumber'],
            'account' => $toAccount['id'],
        ],
    ]);

    if (!$outgoingTransactionResponse->successful() || !$incomingTransactionResponse->successful()) {
        return back()->withInput()->with('error', 'Transfer completed, but transaction records could not be created.');
    }

    $customerResponse = Http::withToken($jwt)->get(
        'http://localhost:1337/api/customers?populate=*'
    );

    $customerRows = $customerResponse->json()['data'] ?? [];
    $matchedCustomer = null;

    foreach ($customerRows as $cust) {
        if (
            isset($cust['email']) &&
            isset($user['email']) &&
            strtolower(trim((string) $cust['email'])) === strtolower(trim((string) $user['email']))
        ) {
            $matchedCustomer = $cust;
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
        'customer' => $matchedCustomer,
        'transactions' => $transactions,
    ]);

    return redirect('/dashboard')->with('success', 'Transfer completed successfully.');
})->name('transfer.submit');

Route::post('/logout', function (Request $request) {
    session()->forget(['jwt', 'user', 'customer', 'transactions']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');