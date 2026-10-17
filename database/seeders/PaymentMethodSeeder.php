<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Bank BCA',
                'code' => 'bca',
                'type' => 'transfer',
                'account_number' => '1234567890',
                'account_name' => 'Mercatoria',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'QRIS',
                'code' => 'qris',
                'type' => 'qr',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'ShopeePay',
                'code' => 'shopeepay',
                'type' => 'qr',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(['code' => $method['code']], $method);
        }
    }
}
