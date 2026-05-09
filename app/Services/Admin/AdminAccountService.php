<?php

namespace App\Services\Admin;

use App\Services\AccountFees\MonthlyAccountFeeService;
use App\Services\Audit\AdminAuditLogger;
use App\Services\Strapi\StrapiApiService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;

class AdminAccountService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly AdminAuditLogger $audit,
        private readonly MonthlyAccountFeeService $monthlyFees,
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

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function filteredAccounts(array $filters): array
    {
        $accounts = $this->accounts();
        $transactions = $this->transactions();
        $feeSummaries = $this->monthlyFees->summaries($accounts, $transactions, now()->format('Y-m'));

        return collect($accounts)
            ->map(fn (array $account): array => $this->withFeeSummary($account, $feeSummaries))
            ->filter(fn (array $account): bool => $this->matchesFilters($account, $filters))
            ->sortBy($this->sortField($filters), SORT_REGULAR, ($filters['direction'] ?? 'asc') === 'desc')
            ->values()
            ->all();
    }

    public function find(string $id): ?array
    {
        $response = $this->strapi->get('/api/accounts/'.$id, [
            'populate' => '*',
        ]);

        return $response->json('data');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $id): ?array
    {
        $account = $this->find($id);

        if (! $account) {
            return null;
        }

        $transactions = $this->accountTransactions($account, $this->transactions());
        $feeSummary = $this->monthlyFees->summary($account, $transactions, now()->format('Y-m'));

        return [
            'account' => $account + ['feeSummary' => $feeSummary],
            'transactions' => array_slice($transactions, 0, 8),
        ];
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function transactions(): array
    {
        return $this->strapi->data($this->strapi->get('/api/transactions', [
            'populate' => '*',
            'sort' => 'transactionDate:desc',
        ]));
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<int, array<string, mixed>>
     */
    private function accountTransactions(array $account, array $transactions): array
    {
        return collect($transactions)
            ->filter(fn (array $transaction): bool => $this->relationMatches($transaction['account'] ?? null, $account)
                || $this->relationMatches($transaction['sourceAccount'] ?? null, $account)
                || $this->relationMatches($transaction['destinationAccount'] ?? null, $account))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $feeSummaries
     */
    private function withFeeSummary(array $account, array $feeSummaries): array
    {
        $key = (string) ($account['accountNumber'] ?? $account['id'] ?? '');

        return $account + ['feeSummary' => $feeSummaries[$key] ?? null];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function matchesFilters(array $account, array $filters): bool
    {
        $search = strtolower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '' && ! str_contains(strtolower((string) ($account['accountNumber'] ?? '')), $search)) {
            return false;
        }

        if (! empty($filters['accountType']) && ($account['accountType'] ?? null) !== $filters['accountType']) {
            return false;
        }

        $balance = (float) ($account['balance'] ?? 0);

        if (($filters['minBalance'] ?? '') !== '' && $balance < (float) $filters['minBalance']) {
            return false;
        }

        if (($filters['maxBalance'] ?? '') !== '' && $balance > (float) $filters['maxBalance']) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function sortField(array $filters): callable
    {
        return match ($filters['sort'] ?? 'accountNumber') {
            'accountType' => fn (array $account): string => (string) ($account['accountType'] ?? ''),
            'balance' => fn (array $account): float => (float) ($account['balance'] ?? 0),
            'openedAt' => fn (array $account): string => (string) ($account['openedAt'] ?? ''),
            default => fn (array $account): string => (string) ($account['accountNumber'] ?? ''),
        };
    }

    private function relationMatches(mixed $relation, array $account): bool
    {
        if (is_numeric($relation)) {
            return (int) $relation === (int) ($account['id'] ?? 0);
        }

        if (! is_array($relation)) {
            return false;
        }

        return (string) ($relation['id'] ?? '') === (string) ($account['id'] ?? null)
            || (string) ($relation['documentId'] ?? '') === (string) ($account['documentId'] ?? null)
            || (string) ($relation['accountNumber'] ?? '') === (string) ($account['accountNumber'] ?? null);
    }
}
