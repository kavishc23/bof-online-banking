<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Logging\BankingLogger;
use App\Services\Reports\NetIncomeReportService;
use App\Services\Strapi\StrapiApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Admin-only controller for the CS415 Net Income PDF Report.
 *
 * The controller stays thin: it validates inputs, fetches Strapi records,
 * delegates all calculations to NetIncomeReportService, and sends the prepared
 * report data to DomPDF for download.
 */
class NetIncomeReportController extends Controller
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly NetIncomeReportService $reports,
        private readonly BankingLogger $logger,
    ) {}

    /**
     * Shows the report input form inside the admin portal.
     */
    public function index(): View
    {
        return view('admin.reports.net-income');
    }

    /**
     * Validates the report request, prepares report data, and downloads the PDF.
     */
    public function generate(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'interest_days' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'include_transaction_details' => ['nullable', 'boolean'],
            'include_account_interest_breakdown' => ['nullable', 'boolean'],
        ]);

        $transactionsResponse = $this->strapi->get('/api/transactions', [
            'populate' => '*',
            'sort' => ['transactionDate:desc'],
            'pagination' => ['pageSize' => 500],
        ]);

        if (! $transactionsResponse->successful()) {
            $this->logger->apiFailure('net_income_report_transactions_fetch', $transactionsResponse);

            return back()->withInput()->with('error', 'Transactions could not be loaded for the report.');
        }

        $accountsResponse = $this->strapi->get('/api/accounts', [
            'populate' => '*',
            'pagination' => ['pageSize' => 500],
        ]);

        if (! $accountsResponse->successful()) {
            $this->logger->apiFailure('net_income_report_accounts_fetch', $accountsResponse);

            return back()->withInput()->with('error', 'Accounts could not be loaded for the report.');
        }

        $report = $this->reports->generate(
            $this->strapi->data($transactionsResponse),
            $this->strapi->data($accountsResponse),
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date']),
            isset($validated['interest_days']) ? (int) $validated['interest_days'] : null,
            [
                'notes' => $validated['notes'] ?? null,
                'include_transaction_details' => (bool) ($validated['include_transaction_details'] ?? false),
                'include_account_interest_breakdown' => (bool) ($validated['include_account_interest_breakdown'] ?? false),
            ],
        );

        $this->logger->activity('report.net_income.generated', 'Net income PDF report generated.', [
            'start_date' => $report['report_start_date'],
            'end_date' => $report['report_end_date'],
            'total_fees_collected' => $report['total_fees_collected'],
            'total_interest_paid' => $report['total_interest_paid'],
            'net_income' => $report['net_income'],
            'generated_by' => session('user.email') ?? session('user.username') ?? null,
        ]);

        $filename = 'net-income-report-'.$report['report_start_date'].'-to-'.$report['report_end_date'].'.pdf';

        return Pdf::loadView('admin.reports.net-income-pdf', [
            'report' => $report,
        ])->setPaper('a4', 'portrait')->download($filename);
    }
}
