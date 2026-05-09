<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditService;

class UserObserver
{
    public function __construct(private readonly AuditService $audit) {}

    public function updated(User $user): void
    {
        $this->audit->record('user.updated', $user->getOriginal(), $user->getChanges(), [
            'model' => User::class,
            'model_id' => $user->getKey(),
        ]);
    }
}
