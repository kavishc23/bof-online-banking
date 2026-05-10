<?php

use App\Services\Reports\NetIncomeReportService;
use Carbon\Carbon;

function netIncomeAdminSession(): array
{
    return [
        'jwt' => 'test-token',
        'user_role' => 'Admin',
        'user' => ['email' => 'admin@example.com'],
        'customer' => ['firstName' => 'Admin', 'userRole' => 'Admin'],
    ];
}

test('monthly fee is detected correctly', function () {
    $service = app(NetIncomeReportService::class);

    expect($service->detectFeeCategory([
        'referenceNumber' => 'FEE-2026-0001',
        'transactionType' => 'Fee',
        'description' => 'Monthly account fee',
    ]))->toBe('monthly_account_fee');
});

test('withdrawal fee is detected correctly', function () {
    $service = app(NetIncomeReportService::class);

    expect($service->detectFeeCategory([
        'referenceNumber' => 'WDL-FEE-2026-0001',
        'transactionType' => 'Fee',
        'description' => 'Savings withdrawal fee',
    ]))->toBe('withdrawal_fee');
});

test('other fee is detected correctly', function () {
    $service = app(NetIncomeReportService::class);

    expect($service->detectFeeCategory([
        'referenceNumber' => 'CHG-2026-0001',
        'transactionType' => 'Fee',
        'description' => 'Statement replacement charge',
    ]))->toBe('other_fee');
});

test('net income and interest paid calculations are correct', function () {
    $service = app(NetIncomeReportService::class);
    $report = $service->generate([
        [
            'referenceNumber' => 'FEE-2026-0001',
            'transactionType' => 'Fee',
            'amount' => 20,
            'transactionDate' => '2026-05-10',
            'transactionStatus' => 'Completed',
            'description' => 'Monthly account fee',
        ],
        [
            'referenceNumber' => 'WDL-FEE-2026-0002',
            'transactionType' => 'Fee',
            'amount' => 5,
            'transactionDate' => '2026-05-11',
            'transactionStatus' => 'Completed',
            'description' => 'Savings withdrawal fee',
        ],
        [
            'referenceNumber' => 'CHG-2026-0003',
            'transactionType' => 'Fee',
            'amount' => 10,
            'transactionDate' => '2026-05-12',
            'transactionStatus' => 'Completed',
            'description' => 'Other charge',
        ],
    ], [
        [
            'accountNumber' => '1001',
            'accountType' => 'Savings',
            'balance' => 3650,
            'interestRate' => 10,
        ],
    ], Carbon::parse('2026-05-01'), Carbon::parse('2026-05-31'), 1);

    expect($report['total_monthly_account_fees'])->toBe(20.0)
        ->and($report['total_withdrawal_fees'])->toBe(5.0)
        ->and($report['total_other_fees'])->toBe(10.0)
        ->and($report['total_fees_collected'])->toBe(35.0)
        ->and($report['total_interest_paid'])->toBe(1.0)
        ->and($report['net_income'])->toBe(34.0);
});

test('date range filter excludes transactions outside range', function () {
    $service = app(NetIncomeReportService::class);
    $filtered = $service->filterTransactionsByDateRange([
        ['referenceNumber' => 'IN', 'transactionDate' => '2026-05-10'],
        ['referenceNumber' => 'OUT', 'transactionDate' => '2026-06-01'],
    ], Carbon::parse('2026-05-01'), Carbon::parse('2026-05-31'));

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]['referenceNumber'])->toBe('IN');
});

test('admin can open net income report form', function () {
    $response = $this->withSession(netIncomeAdminSession())->get('/admin/reports/net-income');

    $response->assertOk();
    $response->assertSee('Net Income Report');
    $response->assertSee('Generate PDF Report');
});

test('customer cannot access net income report form', function () {
    $response = $this->withSession([
        'jwt' => 'customer-token',
        'user_role' => 'Customer',
        'user' => ['email' => 'customer@example.com'],
        'customer' => ['firstName' => 'Customer', 'userRole' => 'Customer'],
    ])->get('/admin/reports/net-income');

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('error', 'You are not authorized to access the admin area.');
});

test('pdf route validates required dates', function () {
    $response = $this->withSession(netIncomeAdminSession())->post('/admin/reports/net-income/generate', []);

    $response->assertSessionHasErrors(['start_date', 'end_date']);
});
