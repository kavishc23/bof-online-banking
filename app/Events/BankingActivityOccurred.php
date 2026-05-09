<?php

namespace App\Events;

/**
 * AOP event join point for banking workflows.
 *
 * Controllers and services dispatch this event after domain actions; listeners
 * handle cross-cutting logging, notification, and audit behavior separately.
 */
class BankingActivityOccurred
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly string $type,
        public readonly string $description,
        public readonly array $context = [],
    ) {}
}
