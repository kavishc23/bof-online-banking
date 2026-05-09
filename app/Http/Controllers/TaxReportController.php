<?php

namespace App\Http\Controllers;

use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class TaxReportController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function index(Request $request): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $jwt = session('jwt');
        $user = session('user');
        $email = $user['email'] ?? '';
        $selectedYear = (int) $request->query('year', now()->year);

        try {
            $customer = $this->bofService->fetchCustomerByEmail($jwt, $email);
            $investments = $this->bofService->fetchInvestmentsByEmail($jwt, $email);
            $taxReports = $this->bofService->fetchTaxReportsByEmail($jwt, $email);

            if (! $customer) {
                return back()->with('error', 'Customer profile not found.');
            }

            $tin = $customer['tin'] ?? '';
            $residencyStatus = $customer['residencyStatus'] ?? 'Resident';
            $calculatedTaxProfileStatus = $this->bofService->calculateTaxProfileStatus($tin, $residencyStatus);
            $calculatedWithholdingTaxRate = $this->bofService->calculateWithholdingTaxRate($tin, $residencyStatus);

            if (
                ($customer['taxProfileStatus'] ?? null) !== $calculatedTaxProfileStatus ||
                (float) ($customer['withholdingTaxRate'] ?? null) !== $calculatedWithholdingTaxRate
            ) {
                Http::withToken($jwt)->put("http://localhost:1337/api/customers/{$customer['documentId']}", [
                    'data' => [
                        'taxProfileStatus' => $calculatedTaxProfileStatus,
                        'withholdingTaxRate' => $calculatedWithholdingTaxRate,
                        'taxLastUpdatedAt' => now()->toISOString(),
                    ],
                ]);

                $customer['taxProfileStatus'] = $calculatedTaxProfileStatus;
                $customer['withholdingTaxRate'] = $calculatedWithholdingTaxRate;
            }

            $selectedTaxReport = collect($taxReports)
                ->first(fn ($report) => (int) ($report['reportingYear'] ?? 0) === $selectedYear);

            if (! $selectedTaxReport) {
                $selectedTaxReport = $this->createTaxReport($jwt, $customer, $investments, $email, $tin, $residencyStatus, $selectedYear);

                if ($selectedTaxReport instanceof RedirectResponse) {
                    return $selectedTaxReport;
                }

                $taxReports = $this->bofService->fetchTaxReportsByEmail($jwt, $email);
            }

            return view('tax-report', [
                'customer' => $customer,
                'taxReport' => $selectedTaxReport,
                'taxReports' => $taxReports,
            ]);
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Tax report could not be loaded. Please try again.');
        }
    }

    private function createTaxReport(string $jwt, array $customer, array $investments, string $email, string $tin, string $residencyStatus, int $selectedYear): array|RedirectResponse|null
    {
        $grossInterest = $this->bofService->calculateGrossInterestForYear($investments, $selectedYear);
        $withholdingTaxRate = (float) ($customer['withholdingTaxRate'] ?? 0);
        $withholdingTaxAmount = round($grossInterest * ($withholdingTaxRate / 100), 2);
        $netInterest = round($grossInterest - $withholdingTaxAmount, 2);

        $createResponse = Http::withToken($jwt)->post('http://localhost:1337/api/tax-reports', [
            'data' => [
                'referenceNumber' => 'TAX-'.$selectedYear.'-'.time(),
                'reportingYear' => $selectedYear,
                'customerEmail' => $email,
                'customerName' => trim(($customer['firstName'] ?? '').' '.($customer['lastName'] ?? '')),
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

        if (! $createResponse->successful()) {
            $this->bofService->reportApiFailure('tax_report_create', $createResponse);

            return back()->withInput()->with('error', 'Tax report could not be generated. Please try again.');
        }

        return $createResponse->json()['data'] ?? null;
    }
}
