<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_per_month',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'price_per_month' => 'float',
        'is_active'       => 'boolean',
    ];
}
