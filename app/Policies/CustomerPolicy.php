<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    /**
     * @param  array<string, mixed>  $customer
     */
    public function update(User $user, array $customer): bool
    {
        return strtolower((string) $user->email) === strtolower((string) ($customer['email'] ?? ''));
    }
}
