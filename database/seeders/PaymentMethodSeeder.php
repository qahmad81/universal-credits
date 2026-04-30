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
                'config' => [
                    'bank_name' => 'Universal Bank',
                    'account_number' => '1234567890',
                    'account_holder' => 'Universal Credits Ltd',
                    'reference_instructions' => 'Include your Username in transfer description',
                ],
            ],
            [
                'slug' => 'stripe',
                'name' => 'Stripe (Credit Card)',
                'is_active' => true,
            ],
            [
                'slug' => 'crypto',
                'name' => 'Cryptocurrency',
                'is_active' => true,
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
