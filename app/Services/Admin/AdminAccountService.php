<?php

namespace App\Services\Admin;

use App\Services\Audit\AdminAuditLogger;
use App\Services\Strapi\StrapiApiService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;

class AdminAccountService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly AdminAuditLogger $audit,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function accounts(): array
    {
        return $this->strapi->data($this->strapi->get('/api/accounts', [
            'populate' => '*',
        ]));
    }

    public function find(string $id): ?array
    {
        $response = $this->strapi->get('/api/accounts/'.$id, [
            'populate' => '*',
        ]);

        return $response->json('data');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Response
    {
        $payload = ['data' => $this->accountPayload($validated, includeCreationFields: true)];
        $response = $this->strapi->post('/api/accounts', $payload);

        if ($response->successful()) {
            $this->audit->log('account_created', [
                'account_number' => $validated['accountNumber'],
                'payload' => $payload,
            ]);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(string $id, array $validated): Response
    {
        $payload = ['data' => $this->accountPayload($validated, includeCreationFields: false)];
        $response = $this->strapi->put('/api/accounts/'.$id, $payload);

        if ($response->successful()) {
            $this->audit->log('account_updated', [
                'account_id' => $id,
                'payload' => $payload,
            ]);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function accountPayload(array $validated, bool $includeCreationFields): array
    {
        $payload = [
            'accountType' => $validated['accountType'],
            'balance' => (float) $validated['balance'],
            'monthlyMaintenanceFee' => isset($validated['monthlyMaintenanceFee']) ? (float) $validated['monthlyMaintenanceFee'] : 0,
            'interestRate' => isset($validated['interestRate']) ? (float) $validated['interestRate'] : 0,
        ];

        if ($includeCreationFields) {
            $payload['accountNumber'] = $validated['accountNumber'];
            $payload['openedAt'] = isset($validated['openedAt']) && $validated['openedAt']
                ? Carbon::parse($validated['openedAt'])->toISOString()
                : now()->startOfDay()->toISOString();
        }

        if (! empty($validated['customer'])) {
            $payload['customer'] = (int) $validated['customer'];
        }

        return $payload;
    }
}
