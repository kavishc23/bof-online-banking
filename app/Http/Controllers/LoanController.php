<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class LoanController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function create(Request $request): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        return view('loan-application', [
            'customer' => session('customer'),
            'loanProducts' => $this->bofService->fetchLoanProducts(),
            'selectedLoanType' => $request->query('type'),
        ]);
    }

    public function store(LoanApplicationRequest $request): RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $jwt = session('jwt');
        $user = session('user');
        $referenceNumber = 'LOAN-'.time();
        $uploadedDocumentIds = [];

        try {
            if ($request->hasFile('supporting_documents')) {
                foreach ($request->file('supporting_documents') as $file) {
                    $uploadResponse = Http::withToken($jwt)
                        ->attach('files', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                        ->post('http://localhost:1337/api/upload');

                    if (! $uploadResponse->successful()) {
                        return back()->withInput()->with('error', 'Supporting document upload failed. Please try again.');
                    }

                    foreach ($uploadResponse->json() as $uploadedFile) {
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

            if (! $response->successful()) {
                $this->bofService->reportApiFailure('loan_application_create', $response);

                return back()->withInput()->with('error', 'Loan application could not be submitted. Please try again.');
            }

            return redirect()->route('my-loans')->with(
                'success',
                'Loan application submitted successfully. Reference: '.$referenceNumber
            );
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Loan application could not be submitted. Please try again.');
        }
    }

    public function index(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $user = session('user');

        return view('my-loans', [
            'customer' => session('customer'),
            'loanApplications' => $this->bofService->fetchLoanApplications(session('jwt'), $user['email'] ?? ''),
        ]);
    }

    public function products(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        return view('loan-products', [
            'customer' => session('customer'),
            'loanProducts' => $this->bofService->fetchLoanProducts(),
        ]);
    }
}
