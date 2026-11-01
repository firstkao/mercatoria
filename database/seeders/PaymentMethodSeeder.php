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
                'code' => PaymentMethod::CODE_BCA,
                'name' => 'Bank BCA',
                'type' => 'bank_transfer',
                'fee' => 0,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'PT Mercatoria Indonesia',
                'instructions' => 'Transfer manual. Nomor rekening & atas nama akan tampil setelah pesanan dibuat.',
                'is_active' => true,
            ],
            [
                'code' => PaymentMethod::CODE_QRIS,
                'name' => 'QRIS',
                'type' => 'qris',
                'fee' => 0,
                'qr_image_path' => null,
                'instructions' => 'Scan QR. QR code tampil setelah pesanan dibuat. Fee 0,03% jika total ≥ Rp 500.000.',
                'is_active' => true,
            ],
            [
                'code' => PaymentMethod::CODE_SHOPEEPAY,
                'name' => 'ShopeePay',
                'type' => 'qris',
                'fee' => 1000,
                'qr_image_path' => null,
                'instructions' => 'Scan QR ShopeePay. QR code tampil setelah pesanan dibuat.',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}