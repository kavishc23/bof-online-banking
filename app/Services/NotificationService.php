<?php

namespace App\Services;

use App\Services\Logging\BankingLogger;

class NotificationService
{
    public function __construct(private readonly BankingLogger $logger) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function sendForActivity(string $type, array $context = []): void
    {
        match ($type) {
            'transfer.completed' => $this->moneySent($context),
            'transfer.received' => $this->moneyReceived($context),
            'bill-payment.completed' => $this->billPayment($context),
            'loan.reminder' => $this->loanReminder($context),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function transactionAlert(array $context): void
    {
        $this->logger->activity('notification.transaction', 'Transaction alert notification queued.', $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function moneySent(array $context): void
    {
        $this->logger->activity('notification.money_sent', 'Money sent notification queued.', $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function moneyReceived(array $context): void
    {
        $this->logger->activity('notification.money_received', 'Money received notification queued.', $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function loanReminder(array $context): void
    {
        $this->logger->activity('notification.loan_reminder', 'Loan reminder notification queued.', $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function billPayment(array $context): void
    {
        $this->logger->activity('notification.bill_payment', 'Bill payment notification queued.', $context);
    }
}
