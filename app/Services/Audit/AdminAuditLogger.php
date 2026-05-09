<?php

namespace App\Services\Audit;

use App\Services\Logging\BankingLogger;

class AdminAuditLogger
{
    public function __construct(private readonly BankingLogger $logger) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $action, array $context = []): void
    {
        $this->logger->audit('admin.'.$action, $context + [
            'admin_email' => session('user.email') ?? session('customer.email') ?? null,
        ]);
    }
}
