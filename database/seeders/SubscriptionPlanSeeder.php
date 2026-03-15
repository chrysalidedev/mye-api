<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('subscription_plans')->updateOrInsert(
            ['slug' => 'monthly'],
            [
                'name'            => 'Mensuel',
                'slug'            => 'monthly',
                'price_per_month' => 10.00,
                'currency'        => 'EUR',
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );
    }
}
