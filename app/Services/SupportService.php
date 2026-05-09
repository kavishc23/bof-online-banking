<?php

namespace App\Services;

use App\Services\Logging\BankingLogger;

class SupportService
{
    public function __construct(private readonly BankingLogger $logger) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function logChatMessage(string $ticketReference, string $message, array $context = []): void
    {
        $this->logger->activity('support.chat', 'Customer support chat message recorded.', $context + [
            'ticket_reference' => $ticketReference,
            'message' => $message,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function resolveTicket(string $ticketReference, array $context = []): void
    {
        $this->logger->activity('support.ticket_resolved', 'Customer support ticket resolved.', $context + [
            'ticket_reference' => $ticketReference,
        ]);
    }
}
