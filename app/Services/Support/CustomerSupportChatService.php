<?php

namespace App\Services\Support;

use App\Services\Strapi\StrapiApiService;
use App\Services\SupportService;
use Illuminate\Http\Client\Response;

class CustomerSupportChatService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly ChatbotFaqService $faqs,
        private readonly SupportService $logger,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function chats(): array
    {
        return collect($this->strapi->data($this->strapi->get('/api/support-tickets', [
            'filters[customerEmail][$eq]' => $this->customerEmail(),
            'sort' => 'createdAt:desc',
        ])))
            ->filter(fn (array $ticket): bool => $this->belongsToCustomer($ticket))
            ->values()
            ->all();
    }

    public function findForCustomer(string $id): ?array
    {
        $ticket = $this->strapi->get('/api/support-tickets/'.$id)->json('data');

        if (! $ticket || ! $this->belongsToCustomer($ticket)) {
            return null;
        }

        return $ticket;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Response
    {
        $ticketNumber = $this->generateTicketNumber();
        $faqMatch = $this->faqs->match($validated['subject'], $validated['message']);
        $payload = [
            'data' => [
                'ticketNumber' => $ticketNumber,
                'customerName' => $this->customerName(),
                'customerEmail' => $this->customerEmail(),
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'ticketStatus' => $faqMatch ? 'InProgress' : 'Open',
            ],
        ];

        if ($faqMatch) {
            $payload['data']['consultantReply'] = $faqMatch['answer'];
        }

        $response = $this->strapi->post('/api/support-tickets', $payload);

        if ($response->successful()) {
            $this->logger->logChatMessage($ticketNumber, $validated['message'], [
                'subject' => $validated['subject'],
                'customer_email' => $this->customerEmail(),
            ]);
        }

        return $response;
    }

    /**
     * @return array{successful: bool, message: string}
     */
    public function markResolved(string $id): array
    {
        $ticket = $this->findForCustomer($id);

        if (! $ticket) {
            return ['successful' => false, 'message' => 'Support chat not found.'];
        }

        $response = $this->strapi->put('/api/support-tickets/'.$id, [
            'data' => [
                'ticketStatus' => 'Resolved',
                'resolvedAt' => now()->toISOString(),
            ],
        ]);

        if ($response->successful()) {
            $this->logger->logChatbotResolution((string) ($ticket['ticketNumber'] ?? $id), [
                'customer_email' => $this->customerEmail(),
            ]);
        }

        return [
            'successful' => $response->successful(),
            'message' => $response->successful()
                ? 'Glad the virtual assistant helped. You can now rate this support chat.'
                : 'Support chat could not be marked resolved.',
        ];
    }

    /**
     * @return array{successful: bool, message: string}
     */
    public function needsConsultant(string $id): array
    {
        $ticket = $this->findForCustomer($id);

        if (! $ticket) {
            return ['successful' => false, 'message' => 'Support chat not found.'];
        }

        $response = $this->strapi->put('/api/support-tickets/'.$id, [
            'data' => [
                'ticketStatus' => 'Open',
            ],
        ]);

        if ($response->successful()) {
            $this->logger->logConsultantEscalation((string) ($ticket['ticketNumber'] ?? $id), [
                'customer_email' => $this->customerEmail(),
            ]);
        }

        return [
            'successful' => $response->successful(),
            'message' => $response->successful()
                ? 'A consultant will review your query.'
                : 'Support chat could not be escalated.',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{successful: bool, message: string}
     */
    public function rate(string $id, array $validated): array
    {
        $ticket = $this->findForCustomer($id);

        if (! $ticket) {
            return [
                'successful' => false,
                'message' => 'Support chat not found.',
            ];
        }

        if (($ticket['ticketStatus'] ?? null) !== 'Resolved') {
            return [
                'successful' => false,
                'message' => 'Only resolved support chats can be rated.',
            ];
        }

        if (! empty($ticket['satisfactionRating'])) {
            return [
                'successful' => false,
                'message' => 'This support chat has already been rated.',
            ];
        }

        $payload = [
            'data' => [
                'satisfactionRating' => (int) $validated['satisfactionRating'],
                'satisfactionComment' => $validated['satisfactionComment'] ?? null,
            ],
        ];

        $response = $this->strapi->put('/api/support-tickets/'.$id, $payload);

        if ($response->successful()) {
            $this->logger->logSatisfactionRating((string) ($ticket['ticketNumber'] ?? $id), [
                'rating' => (int) $validated['satisfactionRating'],
                'customer_email' => $this->customerEmail(),
            ]);
        }

        return [
            'successful' => $response->successful(),
            'message' => $response->successful()
                ? 'Thank you for rating this support chat.'
                : 'Your rating could not be submitted.',
        ];
    }

    /**
     * @param  array<string, mixed>  $ticket
     */
    private function belongsToCustomer(array $ticket): bool
    {
        return strtolower((string) ($ticket['customerEmail'] ?? '')) === strtolower($this->customerEmail());
    }

    private function customerEmail(): string
    {
        return (string) (session('customer.email') ?? session('user.email') ?? '');
    }

    private function customerName(): string
    {
        $firstName = session('customer.firstName');
        $lastName = session('customer.lastName');
        $fullName = trim((string) $firstName.' '.(string) $lastName);

        return $fullName !== '' ? $fullName : (string) (session('user.username') ?? 'Customer');
    }

    private function generateTicketNumber(): string
    {
        return 'SUP-'.now()->format('Y').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
