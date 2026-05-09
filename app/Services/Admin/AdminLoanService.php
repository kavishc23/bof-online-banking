<?php

namespace App\Services\Admin;

use App\Services\Strapi\StrapiApiService;

class AdminLoanService
{
    public function __construct(private readonly StrapiApiService $strapi) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function loanApplications(): array
    {
        return $this->strapi->data($this->strapi->get('/api/loan-applications', [
            'populate' => '*',
            'sort' => 'submittedAt:desc',
        ]));
    }
}
