<?php

namespace App\Listeners;

use App\Events\BankingActivityOccurred;
use App\Services\Logging\BankingLogger;

class LogBankingActivity
{
    public function __construct(private readonly BankingLogger $logger) {}

    public function handle(BankingActivityOccurred $event): void
    {
        $this->logger->activity($event->type, $event->description, $event->context);
    }
}
