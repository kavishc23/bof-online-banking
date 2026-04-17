<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountStatementController;

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
                foreach ($accountIds as $accountId) {
                    if (transactionBelongsToAccount($transaction, (int) $accountId)) {
                        $transactions[] = $transaction;
                        break;
                    }
                }
            }
        }

        usort($transactions, function ($a, $b) {
            return strcmp(
                (string) ($b['transactionDate'] ?? ''),
                (string) ($a['transactionDate'] ?? '')
            );
        });

        return [
            'customer' => $matchedCustomer,
            'transactions' => $transactions,
        ];
    }
}

if (!function_exists('fetchLoanApplications')) {
    function fetchLoanApplications(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/loan-applications?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=submittedAt:desc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('transactionBelongsToAccount')) {
    function transactionBelongsToAccount(array $transaction, int $accountId): bool
    {
        $sourceAccountId = $transaction['sourceAccount']['id'] ?? null;
        $legacyAccountId = $transaction['account']['id'] ?? null;
        $destinationAccountId = $transaction['destinationAccount']['id'] ?? null;

        $transactionType = strtolower($transaction['transactionType'] ?? '');
        $transferType = strtolower($transaction['transferType'] ?? '');

        $isDeposit = $transactionType === 'deposit' || $transferType === 'deposit';

        if ($isDeposit) {
            return
                (int) $legacyAccountId === (int) $accountId ||
                (int) $destinationAccountId === (int) $accountId;
        }

        return
            (int) $legacyAccountId === (int) $accountId ||
            (int) $sourceAccountId === (int) $accountId;
    }
}

if (!function_exists('fetchFreshCustomer')) {
    function fetchFreshCustomer(string $jwt, array $user): ?array
    {
        $customerResponse = Http::withToken($jwt)->get(
            'http://localhost:1337/api/customers?populate[accounts][populate]=*&populate[tinSupportingDocument]=*&filters[email][$eq]=' . urlencode($user['email'] ?? '')
        );

        $customerRows = $customerResponse->json()['data'] ?? [];
        return $customerRows[0] ?? null;
    }
}

if (!function_exists('fetchCustomerByDocumentId')) {
    function fetchCustomerByDocumentId(string $jwt, string $documentId): ?array
    {
        $response = Http::withToken($jwt)->get(
            "http://localhost:1337/api/customers/{$documentId}?populate[accounts][populate]=*&populate[tinSupportingDocument]=*"
        );

        return $response->json()['data'] ?? null;
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

if (!function_exists('fetchBeneficiaries')) {
    function fetchBeneficiaries(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/beneficiaries?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=nickname:asc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('fetchScheduledTransfers')) {
    function fetchScheduledTransfers(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/scheduled-transfers?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=scheduledDate:asc&populate=*'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('fetchCustomerByEmail')) {
    function fetchCustomerByEmail(string $jwt, string $email): ?array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/customers?filters[email][$eq]=' . urlencode($email) . '&populate=*'
        );

        $rows = $response->json()['data'] ?? [];
        return $rows[0] ?? null;
    }
}

if (!function_exists('fetchInvestmentsByEmail')) {
    function fetchInvestmentsByEmail(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/investments?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=submittedAt:desc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('fetchTaxReportsByEmail')) {
    function fetchTaxReportsByEmail(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/tax-reports?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=reportingYear:desc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('calculateTaxProfileStatus')) {
    function calculateTaxProfileStatus(?string $tin, string $residencyStatus): string
    {
        if ($residencyStatus === 'NonResident') {
            return 'NonResident';
        }

        if (empty(trim((string) $tin))) {
            return 'MissingTIN';
        }

        return 'ValidTIN';
    }
}

if (!function_exists('calculateWithholdingTaxRate')) {
    function calculateWithholdingTaxRate(?string $tin, string $residencyStatus): float
    {
        if ($residencyStatus === 'NonResident') {
            return 15.0;
        }

        if (empty(trim((string) $tin))) {
            return 15.0;
        }

        return 0.0;
    }
}

if (!function_exists('calculateGrossInterestForYear')) {
    function calculateGrossInterestForYear(array $investments, int $year): float
    {
        $grossInterest = 0.0;

        foreach ($investments as $investment) {
            $dateToCheck = $investment['submittedAt'] ?? $investment['startDate'] ?? null;

            if (!$dateToCheck) {
                continue;
            }

            try {
                $investmentYear = \Carbon\Carbon::parse($dateToCheck)->year;
            } catch (\Exception $e) {
                continue;
            }

            if ($investmentYear === $year) {
                $grossInterest += (float) ($investment['estimatedReturn'] ?? 0);
            }
        }

        return round($grossInterest, 2);
    }
}

if (!function_exists('fetchLoanProducts')) {
    function fetchLoanProducts(): array
    {
        $response = Http::get(
            'http://localhost:1337/api/loan-products?filters[isActive][$eq]=true&sort[0]=advertisedRate:asc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('fetchScheduledBillPayments')) {
    function fetchScheduledBillPayments(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/scheduled-bill-payments?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=scheduledDate:asc&populate=*'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('requiresOtp')) {
    function requiresOtp(float $amount): bool
    {
        return $amount >= 1000;
    }
}

if (!function_exists('generateOtpCode')) {
    function generateOtpCode(): string
    {
        if (app()->environment('local')) {
            return '123456';
        }

        return (string) random_int(100000, 999999);
    }
}

if (!function_exists('fetchInvestments')) {
    function fetchInvestments(string $jwt, string $email): array
    {
        $response = Http::withToken($jwt)->get(
            'http://localhost:1337/api/investments?filters[customerEmail][$eq]=' . urlencode($email) . '&sort[0]=submittedAt:desc'
        );

        return $response->json()['data'] ?? [];
    }
}

if (!function_exists('findCustomerAccount')) {
    function findCustomerAccount(array $customer, int $accountId): ?array
    {
        foreach (($customer['accounts'] ?? []) as $account) {
            if ((int) $account['id'] === $accountId) {
                return $account;
            }
        }

        return null;
    }
}

if (!function_exists('processImmediateTransfer')) {
    function processImmediateTransfer(Request $request, string $jwt, array $customer, array $user)
    {
        $fromAccount = findCustomerAccount($customer, (int) $request->from_account_id);

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

            return redirect('/dashboard')->with(
                'success',
                'Transaction successful. SMS confirmation sent to sender and funds received notification sent to destination account holder. Reference: ' . $referenceOut
            );

            return redirect('/dashboard')->with(
                'success',
                'Transaction successful. SMS confirmation sent. Reference: ' . $referenceOut
            );
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

        return redirect('/dashboard')->with(
            'success',
            'Transaction successful. SMS confirmation sent. Reference: ' . $referenceNumber
        );
    }
}

if (!function_exists('processImmediateBillPayment')) {
    function processImmediateBillPayment(Request $request, string $jwt, array $customer, array $user)
    {
        $fromAccount = findCustomerAccount($customer, (int) $request->from_account_id);

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

        return redirect('/dashboard')->with(
            'success',
            'Bill payment successful. SMS confirmation sent. Reference: ' . $referenceNumber        );
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

    $customer = session('customer');
    $transactions = session('transactions', []);
    $accounts = $customer['accounts'] ?? [];

    $primaryAccount = $accounts[0] ?? null;
    $recentTransactions = array_slice($transactions, 0, 5);

    $totalIncoming = 0;
    $totalOutgoing = 0;
    $billPaymentCount = 0;

    foreach ($transactions as $transaction) {
        $transactionType = strtolower($transaction['transactionType'] ?? '');
        $transferType = strtolower($transaction['transferType'] ?? '');
        $amount = (float) ($transaction['amount'] ?? 0);

        if ($transactionType === 'deposit' || $transferType === 'deposit') {
            $totalIncoming += $amount;
        } else {
            $totalOutgoing += $amount;
        }

        if ($transferType === 'billpayment') {
            $billPaymentCount++;
        }
    }

    return view('dashboard', [
        'user' => session('user'),
        'customer' => $customer,
        'accounts' => $accounts,
        'primaryAccount' => $primaryAccount,
        'transactions' => $transactions,
        'recentTransactions' => $recentTransactions,
        'totalIncoming' => $totalIncoming,
        'totalOutgoing' => $totalOutgoing,
        'billPaymentCount' => $billPaymentCount,
    ]);
})->name('dashboard');

Route::get('/transactions', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $customer = session('customer');
    $transactions = session('transactions', []);
    $accounts = $customer['accounts'] ?? [];
    $selectedAccountId = request('account_id');

    if (!empty($selectedAccountId)) {
        $transactions = array_values(array_filter($transactions, function ($transaction) use ($selectedAccountId) {
            return transactionBelongsToAccount($transaction, (int) $selectedAccountId);
        }));
    }

    return view('transactions', [
    'customer' => $customer,
    'transactions' => $transactions,
    'accounts' => $accounts,
    'selectedAccountId' => $selectedAccountId,
    'statements' => [],
    ]);
})->name('transactions');

Route::get('/beneficiaries', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';

    $beneficiaries = fetchBeneficiaries($jwt, $email);

    return view('beneficiaries', [
        'customer' => session('customer'),
        'beneficiaries' => $beneficiaries,
    ]);
})->name('beneficiaries');

Route::post('/beneficiaries', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'nickname' => 'required|string|max:100',
        'beneficiary_name' => 'required|string|max:100',
        'transfer_mode' => 'required|in:Internal,LocalBank',
        'institution_name' => 'nullable|string|max:100',
        'account_number' => 'required|string|max:100',
    ]);

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';

    $response = Http::withToken($jwt)->post('http://localhost:1337/api/beneficiaries', [
        'data' => [
            'nickname' => $request->nickname,
            'beneficiaryName' => $request->beneficiary_name,
            'transferMode' => $request->transfer_mode,
            'institutionName' => $request->institution_name,
            'accountNumber' => $request->account_number,
            'customerEmail' => $email,
            'isFavorite' => false,
        ],
    ]);

    if (!$response->successful()) {
        dd([
            'step' => 'beneficiary_create',
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json(),
        ]);
    }

    return redirect()->route('beneficiaries')->with('success', 'Beneficiary saved successfully.');
})->name('beneficiaries.store');

Route::get('/scheduled-payments', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';

    $scheduledTransfers = fetchScheduledTransfers($jwt, $email);
    $scheduledBillPayments = fetchScheduledBillPayments($jwt, $email);

    return view('scheduled-payments', [
        'customer' => session('customer'),
        'scheduledTransfers' => $scheduledTransfers,
        'scheduledBillPayments' => $scheduledBillPayments,
    ]);
})->name('scheduled-payments');

Route::get('/otp-verification', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    if (!session()->has('pending_otp') || !session()->has('pending_action') || !session()->has('pending_payload')) {
        return redirect('/dashboard')->with('error', 'No OTP verification is pending.');
    }

    return view('otp-verification');
})->name('otp.verification');

Route::post('/otp-verification', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'otp_code' => 'required|string',
    ]);

    $otpExpiresAt = session('otp_expires_at');
    $pendingOtp = session('pending_otp');
    $pendingAction = session('pending_action');
    $pendingPayload = session('pending_payload');

    if (!$otpExpiresAt || now()->greaterThan($otpExpiresAt)) {
        session()->forget(['pending_otp', 'pending_action', 'pending_payload', 'otp_expires_at']);
        return redirect('/dashboard')->with('error', 'OTP has expired. Please try again.');
    }

    if (!$pendingOtp || !$pendingAction || !$pendingPayload) {
        return redirect('/dashboard')->with('error', 'OTP session expired.');
    }

    if ((string) $request->otp_code !== (string) $pendingOtp) {
        return back()->withInput()->with('error', 'Invalid OTP code.');
    }

    session()->forget(['pending_otp', 'pending_action', 'pending_payload', 'otp_expires_at']);

    $jwt = session('jwt');
    $customer = session('customer');
    $user = session('user');

    if ($pendingAction === 'transfer') {
        $pendingRequest = Request::create('/transfer', 'POST', $pendingPayload['form_data'] ?? []);
        return processImmediateTransfer($pendingRequest, $jwt, $customer, $user);
    }

    if ($pendingAction === 'bill_payment') {
        $pendingRequest = Request::create('/bill-payment', 'POST', $pendingPayload['form_data'] ?? []);
        return processImmediateBillPayment($pendingRequest, $jwt, $customer, $user);
    }

    return redirect('/dashboard')->with('error', 'Unknown OTP action.');
})->name('otp.verification.submit');

Route::get('/transfer', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';

    $otherLocalBanks = fetchActiveOtherLocalBanks($jwt);
    $beneficiaries = fetchBeneficiaries($jwt, $email);

    return view('transfer', [
        'customer' => session('customer'),
        'otherLocalBanks' => $otherLocalBanks,
        'beneficiaries' => $beneficiaries,
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

        'is_scheduled_transfer' => 'nullable',
        'scheduled_date' => 'nullable|date',
        'frequency' => 'nullable|in:Once,Daily,Weekly,Monthly',
    ]);

    $jwt = session('jwt');
    $customer = session('customer');
    $user = session('user');

    if (!$customer || empty($customer['accounts'])) {
        return back()->with('error', 'No customer accounts found.');
    }

    $fromAccount = findCustomerAccount($customer, (int) $request->from_account_id);

    if (!$fromAccount) {
        return back()->with('error', 'Selected source account not found.');
    }

    $amount = (float) $request->amount;
    $fromBalance = (float) ($fromAccount['balance'] ?? 0);

    if ($request->filled('is_scheduled_transfer')) {
        $request->validate([
            'scheduled_date' => 'required|date',
            'frequency' => 'required|in:Once,Daily,Weekly,Monthly',
        ]);

        $referenceNumber = 'SCH-TXN-' . time();

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

            $scheduledResponse = Http::withToken($jwt)->post('http://localhost:1337/api/scheduled-transfers', [
                'data' => [
                    'referenceNumber' => $referenceNumber,
                    'transferMode' => 'Internal',
                    'amount' => $amount,
                    'scheduledDate' => $request->scheduled_date,
                    'frequency' => $request->frequency,
                    'description' => $request->description,
                    'destinationInstitution' => 'BoF',
                    'destinationAccountNumber' => $toAccount['accountNumber'] ?? '',
                    'beneficiaryName' => $toAccount['accountHolderName'] ?? '',
                    'scheduleStatus' => 'Pending',
                    'customerEmail' => $user['email'] ?? '',
                    'sourceAccount' => $fromAccount['id'],
                    'destinationAccount' => $toAccount['id'],
                ],
            ]);

            if (!$scheduledResponse->successful()) {
                dd([
                    'step' => 'scheduled_internal_transfer_create',
                    'status' => $scheduledResponse->status(),
                    'body' => $scheduledResponse->body(),
                    'json' => $scheduledResponse->json(),
                ]);
            }

            return redirect('/dashboard')->with('success', 'Internal transfer scheduled successfully.');
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

        $scheduledResponse = Http::withToken($jwt)->post('http://localhost:1337/api/scheduled-transfers', [
            'data' => [
                'referenceNumber' => $referenceNumber,
                'transferMode' => 'LocalBank',
                'amount' => $amount,
                'scheduledDate' => $request->scheduled_date,
                'frequency' => $request->frequency,
                'description' => $request->description,
                'destinationInstitution' => $selectedInstitution['name'] ?? '',
                'destinationAccountNumber' => $request->destination_account_number,
                'beneficiaryName' => $request->beneficiary_name,
                'scheduleStatus' => 'Pending',
                'customerEmail' => $user['email'] ?? '',
                'sourceAccount' => $fromAccount['id'],
            ],
        ]);

        if (!$scheduledResponse->successful()) {
            dd([
                'step' => 'scheduled_local_bank_transfer_create',
                'status' => $scheduledResponse->status(),
                'body' => $scheduledResponse->body(),
                'json' => $scheduledResponse->json(),
            ]);
        }

        return redirect('/dashboard')->with('success', 'Local bank transfer scheduled successfully.');
    }

if (requiresOtp($amount)) {
    $otpCode = generateOtpCode();

    session([
        'pending_otp' => $otpCode,
        'pending_action' => 'transfer',
        'otp_expires_at' => now()->addMinutes(2),
        'pending_payload' => [
            'form_data' => $request->except(['_token']),
        ],
    ]);

    return redirect()->route('otp.verification')
        ->with('info', 'OTP sent to your registered mobile number. (Demo OTP: ' . $otpCode . ')');
}

return processImmediateTransfer($request, $jwt, $customer, $user);
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

Route::get('/debug-customer-session', function () {
    dd([
        'user' => session('user'),
        'customer' => session('customer'),
    ]);
});

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

        'is_scheduled_bill' => 'nullable',
        'scheduled_date' => 'nullable|date',
        'frequency' => 'nullable|in:Once,Daily,Weekly,Monthly',
    ]);

    $jwt = session('jwt');
    $customer = session('customer');
    $user = session('user');

    if (!$customer || empty($customer['accounts'])) {
        return back()->with('error', 'No customer accounts found.');
    }

    $fromAccount = findCustomerAccount($customer, (int) $request->from_account_id);

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

    if ($request->filled('is_scheduled_bill')) {
        $request->validate([
            'scheduled_date' => 'required|date',
            'frequency' => 'required|in:Once,Daily,Weekly,Monthly',
        ]);

        $referenceNumber = 'SCH-BILL-' . time();

        $scheduledBillResponse = Http::withToken($jwt)->post('http://localhost:1337/api/scheduled-bill-payments', [
            'data' => [
                'referenceNumber' => $referenceNumber,
                'amount' => $amount,
                'scheduledDate' => $request->scheduled_date,
                'frequency' => $request->frequency,
                'billReference' => $request->bill_reference,
                'notes' => $request->notes,
                'scheduleStatus' => 'Pending',
                'customerEmail' => $user['email'] ?? '',
                'billerName' => $selectedBiller['name'] ?? '',
                'sourceAccount' => $fromAccount['id'],
            ],
        ]);

        if (!$scheduledBillResponse->successful()) {
            dd([
                'step' => 'scheduled_bill_payment_create',
                'status' => $scheduledBillResponse->status(),
                'body' => $scheduledBillResponse->body(),
                'json' => $scheduledBillResponse->json(),
            ]);
        }

        return redirect('/dashboard')->with('success', 'Bill payment scheduled successfully.');
    }

  if (requiresOtp($amount)) {
    $otpCode = generateOtpCode();

    session([
        'pending_otp' => $otpCode,
        'pending_action' => 'bill_payment',
        'otp_expires_at' => now()->addMinutes(2),
        'pending_payload' => [
            'form_data' => $request->except(['_token']),
        ],
    ]);

    return redirect()->route('otp.verification')
        ->with('info', 'OTP sent to your registered mobile number. (Demo OTP: ' . $otpCode . ')');
}

    return processImmediateBillPayment($request, $jwt, $customer, $user);
})->name('bill-payment.submit');

Route::get('/loan-application', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $loanProducts = fetchLoanProducts();
    $selectedLoanType = $request->query('type');

    return view('loan-application', [
        'customer' => session('customer'),
        'loanProducts' => $loanProducts,
        'selectedLoanType' => $selectedLoanType,
    ]);
})->name('loan-application');

Route::post('/loan-application', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'loan_type' => 'required|in:Personal,Home,Car,Business',
        'amount_requested' => 'required|numeric|min:1',
        'repayment_months' => 'required|integer|min:1',
        'employment_status' => 'required|in:Employed,Self-Employed,Unemployed',
        'monthly_income' => 'required|numeric|min:0',
        'loan_purpose' => 'nullable|string',

        'interest_rate' => 'nullable|numeric|min:0',
        'estimated_monthly_repayment' => 'nullable|numeric|min:0',
        'estimated_total_repayment' => 'nullable|numeric|min:0',
        'estimated_total_interest' => 'nullable|numeric|min:0',

        'property_value' => 'nullable|numeric|min:0',
        'deposit_amount' => 'nullable|numeric|min:0',

        'vehicle_details' => 'nullable|string',
        'vehicle_price' => 'nullable|numeric|min:0',

        'business_name' => 'nullable|string',
        'business_purpose' => 'nullable|string',

        'supporting_documents' => 'nullable|array|max:4',
        'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);

    $jwt = session('jwt');
    $user = session('user');

    $referenceNumber = 'LOAN-' . time();

    $uploadedDocumentIds = [];

    if ($request->hasFile('supporting_documents')) {
        foreach ($request->file('supporting_documents') as $file) {
            $uploadResponse = Http::withToken($jwt)
                ->attach(
                    'files',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post('http://localhost:1337/api/upload');

            if (!$uploadResponse->successful()) {
                return back()->withInput()->with('error', 'Supporting document upload failed. Please try again.');
            }

            $uploadedFiles = $uploadResponse->json();

            foreach ($uploadedFiles as $uploadedFile) {
                if (isset($uploadedFile['id'])) {
                    $uploadedDocumentIds[] = $uploadedFile['id'];
                }
            }
        }
    }

    $response = Http::withToken($jwt)->post('http://localhost:1337/api/loan-applications', [
        'data' => [
            'referenceNumber' => $referenceNumber,
            'loanType' => $request->loan_type,
            'amountRequested' => (float) $request->amount_requested,
            'repaymentMonths' => (int) $request->repayment_months,
            'employmentStatus' => $request->employment_status,
            'monthlyIncome' => (float) $request->monthly_income,
            'loanPurpose' => $request->loan_purpose,

            'interestRate' => $request->interest_rate !== null ? (float) $request->interest_rate : null,
            'estimatedMonthlyRepayment' => $request->estimated_monthly_repayment !== null ? (float) $request->estimated_monthly_repayment : null,
            'estimatedTotalRepayment' => $request->estimated_total_repayment !== null ? (float) $request->estimated_total_repayment : null,
            'estimatedTotalInterest' => $request->estimated_total_interest !== null ? (float) $request->estimated_total_interest : null,

            'propertyValue' => $request->property_value !== null ? (float) $request->property_value : null,
            'depositAmount' => $request->deposit_amount !== null ? (float) $request->deposit_amount : null,

            'vehicleDetails' => $request->vehicle_details,
            'vehiclePrice' => $request->vehicle_price !== null ? (float) $request->vehicle_price : null,

            'businessName' => $request->business_name,
            'businessPurpose' => $request->business_purpose,

            'supportingDocuments' => $uploadedDocumentIds,

            'applicationStatus' => 'Pending',
            'customerEmail' => $user['email'] ?? '',
            'submittedAt' => now()->toISOString(),
            'lastUpdatedAt' => now()->toISOString(),
        ],
    ]);

    if (!$response->successful()) {
        dd([
            'step' => 'loan_application_create',
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json(),
        ]);
    }

    return redirect()->route('my-loans')->with(
        'success',
        'Loan application submitted successfully. Reference: ' . $referenceNumber
    );
})->name('loan-application.submit');

Route::get('/my-loans', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';

    $loanApplications = fetchLoanApplications($jwt, $email);

    return view('my-loans', [
        'customer' => session('customer'),
        'loanApplications' => $loanApplications,
    ]);
})->name('my-loans');

Route::get('/investments', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    return view('investments', [
        'customer' => session('customer'),
    ]);
})->name('investments');

Route::post('/investments', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'investment_type' => 'required|in:FixedDeposit,GoalSavingsPlan,TermInvestment',
        'funding_account_number' => 'required|string',
        'amount' => 'required|numeric|min:1',
        'term_months' => 'required|integer|min:1',
        'interest_rate' => 'required|numeric|min:0',
        'estimated_return' => 'nullable|numeric|min:0',
        'estimated_maturity_amount' => 'nullable|numeric|min:0',
        'start_date' => 'required|date',
        'maturity_date' => 'required|date',
        'maturity_instruction' => 'required|in:CreditToSourceAccount,RenewAutomatically,TransferToAnotherAccount',
        'risk_level' => 'required|in:Low,Moderate',
        'liquidity_type' => 'required|in:Locked,Flexible',
        'nominee_name' => 'nullable|string',
        'nominee_relationship' => 'nullable|string',
        'nominee_contact' => 'nullable|string',
        'product_description' => 'nullable|string',
    ]);

    $jwt = session('jwt');
    $user = session('user');
    $customer = session('customer');

    $selectedAccount = null;
    foreach (($customer['accounts'] ?? []) as $account) {
        if (($account['accountNumber'] ?? '') === $request->funding_account_number) {
            $selectedAccount = $account;
            break;
        }
    }

    if (!$selectedAccount) {
        return back()->withInput()->with('error', 'Selected funding account not found.');
    }

    $amount = (float) $request->amount;
    $availableBalance = (float) ($selectedAccount['balance'] ?? 0);

    if ($amount > $availableBalance) {
        return back()->withInput()->with('error', 'Insufficient balance in the selected funding account.');
    }

    $referenceNumber = 'INV-' . time();

    $response = Http::withToken($jwt)->post('http://localhost:1337/api/investments', [
        'data' => [
            'referenceNumber' => $referenceNumber,
            'investmentType' => $request->investment_type,
            'fundingAccountNumber' => $request->funding_account_number,
            'amount' => $amount,
            'termMonths' => (int) $request->term_months,
            'interestRate' => (float) $request->interest_rate,
            'estimatedReturn' => $request->estimated_return !== null ? (float) $request->estimated_return : null,
            'estimatedMaturityAmount' => $request->estimated_maturity_amount !== null ? (float) $request->estimated_maturity_amount : null,
            'startDate' => $request->start_date,
            'maturityDate' => $request->maturity_date,
            'maturityInstruction' => $request->maturity_instruction,
            'riskLevel' => $request->risk_level,
            'liquidityType' => $request->liquidity_type,
            'investmentStatus' => 'Pending',
            'nomineeName' => $request->nominee_name,
            'nomineeRelationship' => $request->nominee_relationship,
            'nomineeContact' => $request->nominee_contact,
            'productDescription' => $request->product_description,
            'customerEmail' => $user['email'] ?? '',
            'submittedAt' => now()->toISOString(),
            'lastUpdatedAt' => now()->toISOString(),
        ],
    ]);

    if (!$response->successful()) {
        dd([
            'step' => 'investment_create',
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json(),
        ]);
    }

    return redirect()->route('my-investments')->with(
        'success',
        'Investment submitted successfully. Reference: ' . $referenceNumber
    );
})->name('investments.submit');

Route::get('/my-investments', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';

    $investments = fetchInvestments($jwt, $email);

    return view('my-investments', [
        'customer' => session('customer'),
        'investments' => $investments,
    ]);
})->name('my-investments');

Route::get('/loan-products', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $loanProducts = fetchLoanProducts();

    return view('loan-products', [
        'customer' => session('customer'),
        'loanProducts' => $loanProducts,
    ]);
})->name('loan-products');

Route::get('/tax-report', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $jwt = session('jwt');
    $user = session('user');
    $email = $user['email'] ?? '';
    $selectedYear = (int) $request->query('year', now()->year);

    $customer = fetchCustomerByEmail($jwt, $email);
    $investments = fetchInvestmentsByEmail($jwt, $email);
    $taxReports = fetchTaxReportsByEmail($jwt, $email);

    if (!$customer) {
        return back()->with('error', 'Customer profile not found.');
    }

    $tin = $customer['tin'] ?? '';
    $residencyStatus = $customer['residencyStatus'] ?? 'Resident';

    $calculatedTaxProfileStatus = calculateTaxProfileStatus($tin, $residencyStatus);
    $calculatedWithholdingTaxRate = calculateWithholdingTaxRate($tin, $residencyStatus);

    $storedTaxProfileStatus = $customer['taxProfileStatus'] ?? null;
    $storedWithholdingTaxRate = $customer['withholdingTaxRate'] ?? null;

    if (
        $storedTaxProfileStatus !== $calculatedTaxProfileStatus ||
        (float) $storedWithholdingTaxRate !== (float) $calculatedWithholdingTaxRate
    ) {
        Http::withToken($jwt)->put(
            "http://localhost:1337/api/customers/{$customer['documentId']}",
            [
                'data' => [
                    'taxProfileStatus' => $calculatedTaxProfileStatus,
                    'withholdingTaxRate' => $calculatedWithholdingTaxRate,
                    'taxLastUpdatedAt' => now()->toISOString(),
                ],
            ]
        );

        $customer['taxProfileStatus'] = $calculatedTaxProfileStatus;
        $customer['withholdingTaxRate'] = $calculatedWithholdingTaxRate;
    }

    $selectedTaxReport = null;

    foreach ($taxReports as $report) {
        if ((int) ($report['reportingYear'] ?? 0) === $selectedYear) {
            $selectedTaxReport = $report;
            break;
        }
    }

    if (!$selectedTaxReport) {
        $grossInterest = calculateGrossInterestForYear($investments, $selectedYear);
        $withholdingTaxRate = (float) ($customer['withholdingTaxRate'] ?? 0);
        $withholdingTaxAmount = round($grossInterest * ($withholdingTaxRate / 100), 2);
        $netInterest = round($grossInterest - $withholdingTaxAmount, 2);

        $referenceNumber = 'TAX-' . $selectedYear . '-' . time();

        $createResponse = Http::withToken($jwt)->post('http://localhost:1337/api/tax-reports', [
            'data' => [
                'referenceNumber' => $referenceNumber,
                'reportingYear' => $selectedYear,
                'customerEmail' => $email,
                'customerName' => trim(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')),
                'tinNumber' => $tin,
                'residencyStatus' => $residencyStatus,
                'grossInterest' => $grossInterest,
                'withholdingTaxRate' => $withholdingTaxRate,
                'withholdingTaxAmount' => $withholdingTaxAmount,
                'netInterest' => $netInterest,
                'frcsSubmissionStatus' => 'Pending',
                'adjustmentStatus' => 'None',
                'adjustmentReason' => null,
                'previousTaxAmount' => null,
                'revisedTaxAmount' => null,
                'generatedAt' => now()->toISOString(),
                'lastUpdatedAt' => now()->toISOString(),
            ],
        ]);

        if (!$createResponse->successful()) {
            dd([
                'step' => 'tax_report_create',
                'status' => $createResponse->status(),
                'body' => $createResponse->body(),
                'json' => $createResponse->json(),
            ]);
        }

        $selectedTaxReport = $createResponse->json()['data'] ?? null;
        $taxReports = fetchTaxReportsByEmail($jwt, $email);
    }

    return view('tax-report', [
        'customer' => $customer,
        'taxReport' => $selectedTaxReport,
        'taxReports' => $taxReports,
    ]);
})->name('tax-report');

Route::get('/account-statement', [AccountStatementController::class, 'index'])->name('account-statement');
Route::get('/account-statement/download/{id}', [AccountStatementController::class, 'download'])->name('account-statement.download');
Route::get('/account-statement/preview/{id}', [AccountStatementController::class, 'preview'])->name('account-statement.preview');

Route::get('/customer-profile', function () {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $customer = session('customer');

    return view('customer-profile', [
        'customer' => $customer,
    ]);
})->name('customer-profile');

Route::post('/customer-profile', function (Request $request) {
    if (!session()->has('jwt')) {
        return redirect('/login');
    }

    $request->validate([
        'email' => 'required|email',
        'phone' => 'required|string|max:50',
        'tin' => 'required|string|max:100',
        'tin_supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);

    $jwt = session('jwt');
    $user = session('user');
    $customer = session('customer');

    $customerDocumentId = $customer['documentId'] ?? null;

    if (!$customerDocumentId) {
        return redirect('/dashboard')->with('error', 'Customer profile document ID not found.');
    }

    $uploadedTinDocumentId = null;

    if ($request->hasFile('tin_supporting_document')) {
        $file = $request->file('tin_supporting_document');

        $uploadResponse = Http::withToken($jwt)
            ->attach(
                'files',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )
            ->post('http://localhost:1337/api/upload');

        if (!$uploadResponse->successful()) {
            dd([
                'step' => 'tin_document_upload_failed',
                'status' => $uploadResponse->status(),
                'body' => $uploadResponse->body(),
                'json' => $uploadResponse->json(),
            ]);
        }

        $uploadedFiles = $uploadResponse->json();

        if (!empty($uploadedFiles[0]['id'])) {
            $uploadedTinDocumentId = $uploadedFiles[0]['id'];
        }
    }

    $payload = [
        'email' => $request->email,
        'phone' => $request->phone,
        'tin' => $request->tin,
    ];

    if ($uploadedTinDocumentId) {
        $payload['tinSupportingDocument'] = $uploadedTinDocumentId;
    }

    $updateResponse = Http::withToken($jwt)->put(
        "http://localhost:1337/api/customers/{$customerDocumentId}",
        [
            'data' => $payload,
        ]
    );

    if (!$updateResponse->successful()) {
        dd([
            'step' => 'customer_profile_update',
            'status' => $updateResponse->status(),
            'body' => $updateResponse->body(),
            'json' => $updateResponse->json(),
        ]);
    }

    $refreshedCustomerResponse = Http::withToken($jwt)->get(
        "http://localhost:1337/api/customers/{$customerDocumentId}?populate[accounts][populate]=*&populate[tinSupportingDocument]=*"
    );

    if ($refreshedCustomerResponse->successful()) {
        $refreshedCustomer = $refreshedCustomerResponse->json()['data'] ?? null;

        if ($refreshedCustomer) {
            session([
                'customer' => $refreshedCustomer,
                'user' => array_merge($user, ['email' => $request->email]),
            ]);
        }
    }

    return redirect()->route('customer-profile')->with('success', 'Customer contact and tax details updated successfully.');
})->name('customer-profile.update');

Route::post('/logout', function (Request $request) {
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
})->name('logout');
