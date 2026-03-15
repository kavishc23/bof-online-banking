<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

if (!function_exists('fetchCustomerAndTransactions')) {
    function fetchCustomerAndTransactions(string $jwt, array $user): array
    {
        $customerResponse = Http::withToken($jwt)->get(
            'http://localhost:1337/api/customers?populate[accounts][populate]=*'
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
                $sourceAccountId = $transaction['sourceAccount']['id'] ?? null;
                $legacyAccountId = $transaction['account']['id'] ?? null;
                $destinationAccountId = $transaction['destinationAccount']['id'] ?? null;

                if (
                    in_array($sourceAccountId, $accountIds) ||
                    in_array($legacyAccountId, $accountIds) ||
                    in_array($destinationAccountId, $accountIds)
                ) {
                    $transactions[] = $transaction;
                }
            }
        }

        usort($transactions, function ($a, $b) {
            return strcmp(
                (string)($b['transactionDate'] ?? ''),
                (string)($a['transactionDate'] ?? '')
            );
        });

        return [
            'customer' => $matchedCustomer,
            'transactions' => $transactions,
        ];
    }
}

if (!function_exists('fetchActiveBillers')) {
    function fetchActiveBillers(string $jwt): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/billers?filters[isActive][$eq]=true&sort[0]=name:asc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('fetchActiveOtherLocalBanks')) {
    function fetchActiveOtherLocalBanks(string $jwt): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/other-local-banks?filters[isActive][$eq]=true&sort[0]=name:asc'
        );

        return $response->json()['data'] ?? [];
    }
}

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

    $result = fetchCustomerAndTransactions($jwt, $user);

    session([
        'jwt' => $jwt,
        'user' => $user,
        'customer' => $result['customer'],
        'transactions' => $result['transactions'],
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

    $jwt = session('jwt');
    $otherLocalBanks = fetchActiveOtherLocalBanks($jwt);

    return view('transfer', [
        'customer' => session('customer'),
        'otherLocalBanks' => $otherLocalBanks,
    ]);
})->name('transfer');

Route::post('/transfer', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'transfer_mode' => 'required|in:internal,local_bank',
        'from_account_id' => 'required|integer',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string',

        'to_account_number' => 'nullable|string|required_if:transfer_mode,internal',

        'destination_institution_id' => 'nullable|integer|required_if:transfer_mode,local_bank',
        'destination_account_number' => 'nullable|string|required_if:transfer_mode,local_bank',
        'beneficiary_name' => 'nullable|string|required_if:transfer_mode,local_bank',
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

    if ($request->transfer_mode === 'internal') {
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
            return back()->withInput()->with('error', 'Destination BoF account not found.');
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
                'transferType' => 'Internal',
                'amount' => $amount,
                'transactionDate' => now()->toISOString(),
                'description' => $request->description ?: 'Transfer to account ' . $toAccount['accountNumber'],
                'destinationInstitution' => 'BoF',
                'destinationAccountNumber' => $toAccount['accountNumber'] ?? '',
                'beneficiaryName' => $toAccount['accountHolderName'] ?? '',
                'remarks' => $request->description,
                'transactionStatus' => 'Completed',
                'sourceAccount' => $fromAccount['id'],
                'destinationAccount' => $toAccount['id'],
                'account' => $fromAccount['id'],
            ],
        ]);

        if (!$outgoingTransactionResponse->successful()) {
            dd([
                'step' => 'internal_outgoing_transaction_create',
                'status' => $outgoingTransactionResponse->status(),
                'body' => $outgoingTransactionResponse->body(),
                'json' => $outgoingTransactionResponse->json(),
            ]);
        }

        $incomingTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
            'data' => [
                'referenceNumber' => $referenceIn,
                'transactionType' => 'Deposit',
                'transferType' => 'Deposit',
                'amount' => $amount,
                'transactionDate' => now()->toISOString(),
                'description' => 'Transfer received from account ' . $fromAccount['accountNumber'],
                'destinationInstitution' => 'BoF',
                'destinationAccountNumber' => $toAccount['accountNumber'] ?? '',
                'beneficiaryName' => $toAccount['accountHolderName'] ?? '',
                'remarks' => 'Incoming internal transfer',
                'transactionStatus' => 'Completed',
                'sourceAccount' => $fromAccount['id'],
                'destinationAccount' => $toAccount['id'],
                'account' => $toAccount['id'],
            ],
        ]);

        if (!$incomingTransactionResponse->successful()) {
            dd([
                'step' => 'internal_incoming_transaction_create',
                'status' => $incomingTransactionResponse->status(),
                'body' => $incomingTransactionResponse->body(),
                'json' => $incomingTransactionResponse->json(),
            ]);
        }

        $result = fetchCustomerAndTransactions($jwt, $user);

        session([
            'customer' => $result['customer'],
            'transactions' => $result['transactions'],
        ]);

        return redirect('/dashboard')->with('success', 'Internal transfer completed successfully.');
    }

    $otherLocalBanks = fetchActiveOtherLocalBanks($jwt);
    $selectedInstitution = null;

    foreach ($otherLocalBanks as $bank) {
        if ((int) ($bank['id'] ?? 0) === (int) $request->destination_institution_id) {
            $selectedInstitution = $bank;
            break;
        }
    }

    if (!$selectedInstitution) {
        return back()->withInput()->with('error', 'Selected destination institution not found.');
    }

    $newFromBalance = $fromBalance - $amount;

    $updateFromResponse = Http::withToken($jwt)->put(
        "http://localhost:1337/api/accounts/{$fromAccount['documentId']}",
        [
            'data' => [
                'balance' => $newFromBalance,
            ],
        ]
    );

    if (!$updateFromResponse->successful()) {
        return back()->withInput()->with('error', 'Local bank transfer failed while updating balance.');
    }

    $referenceNumber = 'LBT-' . time();

    $localTransferTransactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
        'data' => [
            'referenceNumber' => $referenceNumber,
            'transactionType' => 'Transfer',
            'transferType' => 'LocalBank',
            'amount' => $amount,
            'transactionDate' => now()->toISOString(),
            'description' => $request->description ?: 'Transfer to ' . ($selectedInstitution['name'] ?? 'External Institution'),
            'destinationInstitution' => $selectedInstitution['name'] ?? '',
            'destinationAccountNumber' => $request->destination_account_number,
            'beneficiaryName' => $request->beneficiary_name,
            'remarks' => $request->description,
            'transactionStatus' => 'Completed',
            'sourceAccount' => $fromAccount['id'],
            'account' => $fromAccount['id'],
        ],
    ]);

    if (!$localTransferTransactionResponse->successful()) {
        dd([
            'step' => 'local_bank_transaction_create',
            'status' => $localTransferTransactionResponse->status(),
            'body' => $localTransferTransactionResponse->body(),
            'json' => $localTransferTransactionResponse->json(),
        ]);
    }

    $result = fetchCustomerAndTransactions($jwt, $user);

    session([
        'customer' => $result['customer'],
        'transactions' => $result['transactions'],
    ]);

    return redirect('/dashboard')->with('success', 'Local bank transfer completed successfully.');
})->name('transfer.submit');

Route::get('/bill-payment', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $billers = fetchActiveBillers($jwt);

    return view('bill-payment', [
        'customer' => session('customer'),
        'billers' => $billers,
    ]);
})->name('bill-payment');

Route::post('/bill-payment', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'from_account_id' => 'required|integer',
        'biller_id' => 'required|integer',
        'bill_reference' => 'nullable|string',
        'amount' => 'required|numeric|min:1',
        'notes' => 'nullable|string',
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
        return back()->with('error', 'Selected account not found.');
    }

    $billers = fetchActiveBillers($jwt);
    $selectedBiller = null;

    foreach ($billers as $biller) {
        if ((int) ($biller['id'] ?? 0) === (int) $request->biller_id) {
            $selectedBiller = $biller;
            break;
        }
    }

    if (!$selectedBiller) {
        return back()->withInput()->with('error', 'Selected biller not found.');
    }

    $amount = (float) $request->amount;
    $fromBalance = (float) ($fromAccount['balance'] ?? 0);

    if ($amount > $fromBalance) {
        return back()->withInput()->with('error', 'Insufficient balance.');
    }

    $newFromBalance = $fromBalance - $amount;

    $updateFromResponse = Http::withToken($jwt)->put(
        "http://localhost:1337/api/accounts/{$fromAccount['documentId']}",
        [
            'data' => [
                'balance' => $newFromBalance,
            ],
        ]
    );

    if (!$updateFromResponse->successful()) {
        return back()->withInput()->with('error', 'Bill payment failed while updating account balance.');
    }

    $billPaymentPayload = [
        'billerName' => $selectedBiller['name'] ?? '',
        'billReference' => $request->bill_reference,
        'amount' => $amount,
        'paymentDate' => now()->toISOString(),
        'isScheduled' => false,
        'notes' => $request->notes,
        'account' => $fromAccount['id'],
    ];

    $billPaymentResponse = Http::withToken($jwt)->post('http://localhost:1337/api/bill-payments', [
        'data' => $billPaymentPayload,
    ]);

    if (!$billPaymentResponse->successful()) {
        dd([
            'step' => 'bill_payment_record_create',
            'status' => $billPaymentResponse->status(),
            'body' => $billPaymentResponse->body(),
            'json' => $billPaymentResponse->json(),
        ]);
    }

    $referenceNumber = 'BILL-' . time();

    $transactionResponse = Http::withToken($jwt)->post('http://localhost:1337/api/transactions', [
        'data' => [
            'referenceNumber' => $referenceNumber,
            'transactionType' => 'BillPayment',
            'transferType' => 'BillPayment',
            'amount' => $amount,
            'transactionDate' => now()->toISOString(),
            'description' => 'Bill payment to ' . ($selectedBiller['name'] ?? 'Biller'),
            'destinationInstitution' => $selectedBiller['name'] ?? '',
            'destinationAccountNumber' => $request->bill_reference,
            'remarks' => $request->notes,
            'transactionStatus' => 'Completed',
            'sourceAccount' => $fromAccount['id'],
            'account' => $fromAccount['id'],
        ],
    ]);

    if (!$transactionResponse->successful()) {
        dd([
            'step' => 'bill_payment_transaction_create',
            'status' => $transactionResponse->status(),
            'body' => $transactionResponse->body(),
            'json' => $transactionResponse->json(),
        ]);
    }

    $result = fetchCustomerAndTransactions($jwt, $user);

    session([
        'customer' => $result['customer'],
        'transactions' => $result['transactions'],
    ]);

    return redirect('/dashboard')->with('success', 'Bill payment completed successfully.');
})->name('bill-payment.submit');

Route::post('/logout', function (Request $request) {
    session()->forget(['jwt', 'user', 'customer', 'transactions']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');