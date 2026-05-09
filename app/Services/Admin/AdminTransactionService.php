<?php

namespace App\Services\Admin;

use App\Services\Strapi\StrapiApiService;

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
}
