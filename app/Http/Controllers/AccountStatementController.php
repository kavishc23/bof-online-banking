<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class AccountStatementController extends Controller
{
    public function __construct(private readonly ReportingService $reports) {}

    public function index(): View
    {
        return view('account-statement', [
            'statements' => $this->reports->accountStatements(),
        ]);
    }

    public function download(string $id): Response|RedirectResponse
    {
        try {
            return $this->reports->downloadAccountStatement($id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Account statement could not be downloaded. Please try again.');
        }
    }

    public function preview(string $id): Response|RedirectResponse
    {
        try {
            return $this->reports->previewAccountStatement($id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Account statement could not be previewed. Please try again.');
        }
    }
}
