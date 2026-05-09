<?php

namespace App\Services\Admin;

use App\Services\Notifications\NotificationSettingsService;
use App\Services\Support\SupportTicketService;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminAccountService $accounts,
        private readonly AdminLoanService $loans,
        private readonly AdminTransactionService $transactions,
        private readonly SupportTicketService $tickets,
        private readonly NotificationSettingsService $settings,
    ) {}

    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        $accounts = $this->accounts->accounts();
        $transactions = $this->transactions->transactions();
        $loans = $this->loans->loanApplications();
        $tickets = $this->tickets->tickets();
        $settings = $this->settings->settings();

        return [
            'total_accounts' => count($accounts),
            'total_transactions' => count($transactions),
            'total_loan_applications' => count($loans),
            'pending_loan_applications' => collect($loans)->where('applicationStatus', 'Pending')->count(),
            'total_support_tickets' => count($tickets),
            'open_support_tickets' => collect($tickets)->where('ticketStatus', 'Open')->count(),
            'unresolved_support_tickets' => collect($tickets)->where('ticketStatus', 'Unresolved')->count(),
            'enabled_notifications' => collect($settings)->where('enabled', true)->count(),
        ];
    }
}
