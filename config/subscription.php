<?php

return [
    'monthly_price' => env('SUBSCRIPTION_MONTHLY_PRICE', 1000),
    'yearly_price'  => env('SUBSCRIPTION_YEARLY_PRICE', 9000),

    'cinetpay' => [
        'apikey'  => env('91988269679b43217f5bb6.89080309'),
        'site_id' => env('105886764'),
        // 'apikey'  => env('CINETPAY_API_KEY'),
        // 'site_id' => env('CINETPAY_SITE_ID'),
    ],
];
