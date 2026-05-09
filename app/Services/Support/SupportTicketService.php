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
}
