<?php

use App\Http\Controllers\AccountStatementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\BillPaymentController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ScheduledPaymentController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'redirectToLogin']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions');
Route::get('/debug-customer-session', [DashboardController::class, 'debugCustomerSession']);

Route::get('/beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries');
Route::post('/beneficiaries', [BeneficiaryController::class, 'store'])->name('beneficiaries.store');

Route::get('/scheduled-payments', [ScheduledPaymentController::class, 'index'])->name('scheduled-payments');

Route::get('/otp-verification', [OtpController::class, 'show'])->name('otp.verification');
Route::post('/otp-verification', [OtpController::class, 'verify'])->name('otp.verification.submit');

Route::get('/transfer', [TransferController::class, 'index'])->name('transfer');
Route::post('/transfer', [TransferController::class, 'submit'])->name('transfer.submit');

Route::get('/bill-payment', [BillPaymentController::class, 'index'])->name('bill-payment');
Route::post('/bill-payment', [BillPaymentController::class, 'submit'])->name('bill-payment.submit');

Route::get('/loan-application', [LoanController::class, 'create'])->name('loan-application');
Route::post('/loan-application', [LoanController::class, 'store'])->name('loan-application.submit');
Route::get('/my-loans', [LoanController::class, 'index'])->name('my-loans');
Route::get('/loan-products', [LoanController::class, 'products'])->name('loan-products');

Route::get('/investments', [InvestmentController::class, 'create'])->name('investments');
Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.submit');
Route::get('/my-investments', [InvestmentController::class, 'index'])->name('my-investments');

Route::get('/tax-report', [TaxReportController::class, 'index'])->name('tax-report');

Route::get('/account-statement', [AccountStatementController::class, 'index'])->name('account-statement');
Route::get('/account-statement/download/{id}', [AccountStatementController::class, 'download'])->name('account-statement.download');
Route::get('/account-statement/preview/{id}', [AccountStatementController::class, 'preview'])->name('account-statement.preview');

Route::get('/customer-profile', [CustomerProfileController::class, 'edit'])->name('customer-profile');
Route::post('/customer-profile', [CustomerProfileController::class, 'update'])->name('customer-profile.update');
