<?php

namespace App\Contracts;

interface AuditLogger
{
    /**
     * Record an auditable banking action with before/after state.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, array $before = [], array $after = [], array $context = []): void;
}
