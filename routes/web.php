<?php

use App\Http\Controllers\AccountStatementController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminChatbotFaqController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLoanController;
use App\Http\Controllers\Admin\AdminNotificationSettingsController;
use App\Http\Controllers\Admin\AdminSupportTicketController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\BillPaymentController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ScheduledPaymentController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'redirectToLogin']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['admin', 'throttle:60,1'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/accounts', [AdminAccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AdminAccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AdminAccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{id}', [AdminAccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{id}/edit', [AdminAccountController::class, 'edit'])->name('accounts.edit');
    Route::match(['put', 'patch'], '/accounts/{id}', [AdminAccountController::class, 'update'])->name('accounts.update');
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans.index');
    Route::get('/support-tickets', [AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/{id}', [AdminSupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::patch('/support-tickets/{id}', [AdminSupportTicketController::class, 'update'])->name('support-tickets.update');
    Route::get('/notification-settings', [AdminNotificationSettingsController::class, 'index'])->name('notification-settings.index');
    Route::patch('/notification-settings', [AdminNotificationSettingsController::class, 'update'])->name('notification-settings.update');
    Route::get('/chatbot-faqs', [AdminChatbotFaqController::class, 'index'])->name('chatbot-faqs.index');
    Route::get('/chatbot-faqs/create', [AdminChatbotFaqController::class, 'create'])->name('chatbot-faqs.create');
    Route::post('/chatbot-faqs', [AdminChatbotFaqController::class, 'store'])->name('chatbot-faqs.store');
    Route::get('/chatbot-faqs/{id}/edit', [AdminChatbotFaqController::class, 'edit'])->name('chatbot-faqs.edit');
    Route::match(['put', 'patch'], '/chatbot-faqs/{id}', [AdminChatbotFaqController::class, 'update'])->name('chatbot-faqs.update');
});

Route::middleware(['banking.session', 'throttle:60,1'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions');
    Route::get('/debug-customer-session', [DashboardController::class, 'debugCustomerSession']);

    Route::get('/beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries');
    Route::post('/beneficiaries', [BeneficiaryController::class, 'store'])->name('beneficiaries.store');

    Route::get('/scheduled-payments', [ScheduledPaymentController::class, 'index'])->name('scheduled-payments');

    Route::get('/support-chat', [SupportChatController::class, 'index'])->name('support-chat.index');
    Route::get('/support-chat/create', [SupportChatController::class, 'create'])->name('support-chat.create');
    Route::post('/support-chat', [SupportChatController::class, 'store'])->name('support-chat.store');
    Route::get('/support-chat/{id}', [SupportChatController::class, 'show'])->name('support-chat.show');
    Route::patch('/support-chat/{id}/resolved', [SupportChatController::class, 'resolved'])->name('support-chat.resolved');
    Route::patch('/support-chat/{id}/needs-consultant', [SupportChatController::class, 'needsConsultant'])->name('support-chat.needs-consultant');
    Route::patch('/support-chat/{id}/rating', [SupportChatController::class, 'rate'])->name('support-chat.rating');

    Route::get('/otp-verification', [OtpController::class, 'show'])->name('otp.verification');
    Route::post('/otp-verification', [OtpController::class, 'verify'])->name('otp.verification.submit');

    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer');
    Route::post('/transfer', [TransferController::class, 'submit'])->name('transfer.submit');

    Route::get('/bill-payment', [BillPaymentController::class, 'index'])->name('bill-payment');
    Route::post('/bill-payment', [BillPaymentController::class, 'submit'])->name('bill-payment.submit');

    Route::get('/withdraw', [WithdrawalController::class, 'create'])->name('withdraw');
    Route::post('/withdraw', [WithdrawalController::class, 'store'])->name('withdraw.submit');

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
});
