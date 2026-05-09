<?php

namespace App\Services\Admin;

use App\Services\Strapi\StrapiApiService;
use Carbon\Carbon;

class AdminTransactionService
{
    public function __construct(private readonly StrapiApiService $strapi) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function transactions(): array
    {
        return $this->strapi->data($this->strapi->get('/api/transactions', [
            'populate' => '*',
            'sort' => 'transactionDate:desc',
        ]));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function filteredTransactions(array $filters): array
    {
        return collect($this->transactions())
            ->filter(fn (array $transaction): bool => $this->matchesFilters($transaction, $filters))
            ->sortBy($this->sortField($filters), SORT_REGULAR, ($filters['direction'] ?? 'desc') === 'desc')
            ->values()
            ->all();
    }

    public function find(string $id): ?array
    {
        return $this->strapi->get('/api/transactions/'.$id, [
            'populate' => '*',
        ])->json('data');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function matchesFilters(array $transaction, array $filters): bool
    {
        $search = strtolower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $haystack = strtolower(implode(' ', [
                $transaction['referenceNumber'] ?? '',
                $transaction['description'] ?? '',
                $transaction['beneficiaryName'] ?? '',
                $transaction['remarks'] ?? '',
            ]));

            if (! str_contains($haystack, $search)) {
                return false;
            }
        }

        if (! empty($filters['transactionType']) && ($transaction['transactionType'] ?? null) !== $filters['transactionType']) {
            return false;
        }

        if (! empty($filters['transactionStatus']) && ($transaction['transactionStatus'] ?? null) !== $filters['transactionStatus']) {
            return false;
        }

        $amount = (float) ($transaction['amount'] ?? 0);

        if (($filters['minAmount'] ?? '') !== '' && $amount < (float) $filters['minAmount']) {
            return false;
        }

        if (($filters['maxAmount'] ?? '') !== '' && $amount > (float) $filters['maxAmount']) {
            return false;
        }

        $date = ! empty($transaction['transactionDate']) ? Carbon::parse($transaction['transactionDate']) : null;

        if (! empty($filters['startDate']) && (! $date || $date->lt(Carbon::parse($filters['startDate'])->startOfDay()))) {
            return false;
        }

        if (! empty($filters['endDate']) && (! $date || $date->gt(Carbon::parse($filters['endDate'])->endOfDay()))) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function sortField(array $filters): callable
    {
        return match ($filters['sort'] ?? 'transactionDate') {
            'amount' => fn (array $transaction): float => (float) ($transaction['amount'] ?? 0),
            'transactionType' => fn (array $transaction): string => (string) ($transaction['transactionType'] ?? ''),
            'transactionStatus' => fn (array $transaction): string => (string) ($transaction['transactionStatus'] ?? ''),
            default => fn (array $transaction): string => (string) ($transaction['transactionDate'] ?? ''),
        };
    }
}
