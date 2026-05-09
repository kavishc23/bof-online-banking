<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerProfileRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class CustomerProfileController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function edit(): View|RedirectResponse
    {
        return view('customer-profile', [
            'customer' => session('customer'),
        ]);
    }

    public function update(CustomerProfileRequest $request): RedirectResponse
    {
        $jwt = session('jwt');
        $user = session('user');
        $customer = session('customer');
        $customerDocumentId = $customer['documentId'] ?? null;

        if (! $customerDocumentId) {
            return redirect('/dashboard')->with('error', 'Customer profile document ID not found.');
        }

        try {
            $uploadedTinDocumentId = $this->uploadTinDocument($request, $jwt);

            if ($uploadedTinDocumentId instanceof RedirectResponse) {
                return $uploadedTinDocumentId;
            }

            $payload = [
                'email' => $request->email,
                'phone' => $request->phone,
                'tin' => $request->tin,
            ];

            if ($uploadedTinDocumentId) {
                $payload['tinSupportingDocument'] = $uploadedTinDocumentId;
            }

            $updateResponse = Http::withToken($jwt)->put("http://localhost:1337/api/customers/{$customerDocumentId}", [
                'data' => $payload,
            ]);

            if (! $updateResponse->successful()) {
                $this->bofService->reportApiFailure('customer_profile_update', $updateResponse);

                return back()->withInput()->with('error', 'Customer profile could not be updated. Please try again.');
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
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Customer profile could not be updated. Please try again.');
        }
    }

    private function uploadTinDocument(CustomerProfileRequest $request, string $jwt): int|RedirectResponse|null
    {
        if (! $request->hasFile('tin_supporting_document')) {
            return null;
        }

        $file = $request->file('tin_supporting_document');
        $uploadResponse = Http::withToken($jwt)
            ->attach('files', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post('http://localhost:1337/api/upload');

        if (! $uploadResponse->successful()) {
            $this->bofService->reportApiFailure('tin_document_upload_failed', $uploadResponse);

            return back()->withInput()->with('error', 'TIN document upload failed. Please try again.');
        }

        return $uploadResponse->json()[0]['id'] ?? null;
    }
}
