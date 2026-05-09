<?php

namespace App\Listeners;

use App\Events\BankingActivityOccurred;
use App\Services\NotificationService;

class SendBankingNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(BankingActivityOccurred $event): void
    {
        $this->notifications->sendForActivity($event->type, $event->context);
    }
}
