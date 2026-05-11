<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardTransaction extends Model
{
    protected $fillable = [
        'credit_card_id',
        'merchant',
        'transaction_date',
        'amount',
        'transaction_type',
        'status',
        'reference_number',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }
}
