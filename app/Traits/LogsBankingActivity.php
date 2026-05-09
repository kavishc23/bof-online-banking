<?php

namespace App\Traits;

use App\Services\Logging\BankingLogger;

trait LogsBankingActivity
{
    /**
     * @param  array<string, mixed>  $context
     */
    protected function logBankingActivity(string $type, string $message, array $context = []): void
    {
        app(BankingLogger::class)->activity($type, $message, $context);
    }
}
