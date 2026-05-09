<?php

namespace App\Services\Support;

use App\Services\Audit\AdminAuditLogger;
use App\Services\Strapi\StrapiApiService;
use Illuminate\Http\Client\Response;

class SupportTicketService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly AdminAuditLogger $audit,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tickets(): array
    {
        return $this->strapi->data($this->strapi->get('/api/support-tickets', [
            'populate' => '*',
            'sort' => 'createdAt:desc',
        ]));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function filteredTickets(array $filters): array
    {
        return collect($this->tickets())
            ->filter(fn (array $ticket): bool => $this->matchesFilters($ticket, $filters))
            ->sortBy($this->sortField($filters), SORT_REGULAR, ($filters['direction'] ?? 'desc') === 'desc')
            ->values()
            ->all();
    }

    public function find(string $id): ?array
    {
        $response = $this->strapi->get('/api/support-tickets/'.$id, [
            'populate' => '*',
        ]);

        return $response->json('data');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(string $id, array $validated): Response
    {
        $payload = [
            'data' => [
                'consultantReply' => $validated['consultantReply'] ?? null,
                'ticketStatus' => $validated['ticketStatus'],
            ],
        ];

        if ($validated['ticketStatus'] === 'Resolved') {
            $payload['data']['resolvedAt'] = now()->toISOString();
        }

        $response = $this->strapi->put('/api/support-tickets/'.$id, $payload);

        if ($response->successful()) {
            $this->audit->log('support_ticket_updated', [
                'ticket_id' => $id,
                'payload' => $payload,
            ]);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function matchesFilters(array $ticket, array $filters): bool
    {
        $search = strtolower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $haystack = strtolower(implode(' ', [
                $ticket['ticketNumber'] ?? '',
                $ticket['customerName'] ?? '',
                $ticket['customerEmail'] ?? '',
                $ticket['subject'] ?? '',
            ]));

            if (! str_contains($haystack, $search)) {
                return false;
            }
        }

        if (! empty($filters['ticketStatus']) && ($ticket['ticketStatus'] ?? null) !== $filters['ticketStatus']) {
            return false;
        }

        if (($filters['satisfactionRating'] ?? '') !== '' && (string) ($ticket['satisfactionRating'] ?? '') !== (string) $filters['satisfactionRating']) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function sortField(array $filters): callable
    {
        return match ($filters['sort'] ?? 'createdAt') {
            'ticketStatus' => fn (array $ticket): string => (string) ($ticket['ticketStatus'] ?? ''),
            default => fn (array $ticket): string => (string) ($ticket['createdAt'] ?? ''),
        };
    }
}
