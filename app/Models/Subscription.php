<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'months',
        'status',
        'cinetpay_transaction_id',
        'amount',
        'currency',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
        'amount'     => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at?->isFuture();
    }

    /** Plans disponibles avec tarifs */
    public static function plans(): array
    {
        return [
            'monthly' => [
                'label'    => 'Mensuel',
                'amount'   => (float) config('subscription.monthly_price', 1000),
                'currency' => 'XOF',
                'days'     => 30,
            ],
            'yearly' => [
                'label'    => 'Annuel',
                'amount'   => (float) config('subscription.yearly_price', 9000),
                'currency' => 'XOF',
                'days'     => 365,
            ],
        ];
    }
}
