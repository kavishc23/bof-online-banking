<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCard extends Model
{
    protected $fillable = [
        'customer_email',
        'customer_name',
        'card_type',
        'masked_card_number',
        'credit_limit',
        'outstanding_balance',
        'available_credit',
        'minimum_payment_due',
        'payment_due_date',
        'reward_points',
        'card_status',
        'card_reference_number',
        'linked_at',
        'frozen_at',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'available_credit' => 'decimal:2',
            'minimum_payment_due' => 'decimal:2',
            'payment_due_date' => 'date',
            'reward_points' => 'integer',
            'linked_at' => 'datetime',
            'frozen_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditCardTransaction::class)->latest('transaction_date');
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ((float) $this->credit_limit <= 0) {
            return 0.0;
        }

        return round(((float) $this->outstanding_balance / (float) $this->credit_limit) * 100, 1);
    }
}
