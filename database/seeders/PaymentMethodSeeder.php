<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'slug' => 'manual_transfer',
                'name' => 'Manual Bank Transfer',
                'is_active' => true,
            ],
            [
                'slug' => 'stripe',
                'name' => 'Stripe',
                'is_active' => false,
            ],
            [
                'slug' => 'crypto',
                'name' => 'Cryptocurrency',
                'is_active' => false,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }
    }
}
