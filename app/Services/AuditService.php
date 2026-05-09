<?php

namespace App\Services;

use App\Contracts\AuditLogger;
use App\Services\Logging\BankingLogger;

class AuditService implements AuditLogger
{
    public function __construct(private readonly BankingLogger $logger) {}

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, array $before = [], array $after = [], array $context = []): void
    {
        $this->logger->audit($action, [
            'actor' => session('user.email') ?? session('user.username') ?? 'guest',
            'performed_at' => now()->toISOString(),
            'before' => $before,
            'after' => $after,
            'context' => $context,
        ]);
    }
}
