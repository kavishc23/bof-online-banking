<?php

namespace App\Services\Admin;

use App\Services\Notifications\NotificationSettingsService;
use App\Services\Strapi\StrapiApiService;
use App\Services\Support\SupportTicketService;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminAccountService $accounts,
        private readonly AdminLoanService $loans,
        private readonly AdminTransactionService $transactions,
        private readonly AdminChatbotFaqService $faqs,
        private readonly SupportTicketService $tickets,
        private readonly NotificationSettingsService $settings,
        private readonly StrapiApiService $strapi,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $accounts = $this->accounts->accounts();
        $transactions = $this->transactions->transactions();
        $loans = $this->loans->loanApplications();
        $tickets = $this->tickets->tickets();
        $settings = $this->settings->settings();
        $customers = $this->strapi->data($this->strapi->get('/api/customers', [
            'populate' => '*',
        ]));

        return [
            'total_accounts' => count($accounts),
            'total_customers' => count($customers),
            'total_transactions' => count($transactions),
            'total_deposits' => collect($transactions)->where('transactionType', 'Deposit')->count(),
            'total_withdrawals' => collect($transactions)->where('transactionType', 'Withdrawal')->count(),
            'total_transfers' => collect($transactions)->where('transactionType', 'Transfer')->count(),
            'total_bill_payments' => collect($transactions)->where('transactionType', 'BillPayment')->count(),
            'total_loan_applications' => count($loans),
            'pending_loan_applications' => collect($loans)->where('applicationStatus', 'Pending')->count(),
            'total_support_tickets' => count($tickets),
            'faq_auto_resolved_chats' => collect($tickets)
                ->filter(fn (array $ticket): bool => ! empty($ticket['consultantReply']) && ($ticket['ticketStatus'] ?? null) === 'Resolved')
                ->count(),
            'open_chats_needing_consultant' => collect($tickets)->where('ticketStatus', 'Open')->count(),
            'open_support_tickets' => collect($tickets)->where('ticketStatus', 'Open')->count(),
            'unresolved_support_tickets' => collect($tickets)->where('ticketStatus', 'Unresolved')->count(),
            'active_chatbot_faqs' => collect($this->faqs->faqs())->where('isActive', true)->count(),
            'enabled_notifications' => collect($settings)->where('enabled', true)->count(),
            'latest_transactions' => array_slice($transactions, 0, 5),
            'latest_support_chats' => array_slice($tickets, 0, 5),
            'latest_accounts' => array_slice($accounts, 0, 5),
        ];
    }
}
