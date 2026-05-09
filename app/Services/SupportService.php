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

    /**
     * @param  array<string, mixed>  $context
     */
    public function logSatisfactionRating(string $ticketReference, array $context = []): void
    {
        $this->logger->activity('support.rating', 'Customer support satisfaction rating submitted.', $context + [
            'ticket_reference' => $ticketReference,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function logChatbotFaqMatch(string $faqQuestion, array $context = []): void
    {
        $this->logger->activity('support.chatbot_matched', 'Chatbot FAQ auto reply matched.', $context + [
            'faq_question' => $faqQuestion,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function logChatbotResolution(string $ticketReference, array $context = []): void
    {
        $this->logger->activity('support.chatbot_resolved', 'Customer marked chatbot answer as resolved.', $context + [
            'ticket_reference' => $ticketReference,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function logConsultantEscalation(string $ticketReference, array $context = []): void
    {
        $this->logger->activity('support.consultant_requested', 'Customer escalated support chat to consultant.', $context + [
            'ticket_reference' => $ticketReference,
        ]);
    }
}
