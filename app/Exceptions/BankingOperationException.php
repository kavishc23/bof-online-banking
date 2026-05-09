<?php

namespace App\Exceptions;

use RuntimeException;

class BankingOperationException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        public readonly array $context = [],
        int $code = 0,
    ) {
        parent::__construct($message, $code);
    }
}
