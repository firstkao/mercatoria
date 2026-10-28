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
                'instructions' => 'Transfer manual. Nomor rekening & atas nama akan tampil setelah pesanan dibuat.',
            ],
            [
                'code' => PaymentMethod::CODE_QRIS,
                'name' => 'QRIS',
                'instructions' => 'Scan QR. QR code tampil setelah pesanan dibuat. Fee 0,03% jika total ≥ Rp 500.000.',
            ],
            [
                'code' => PaymentMethod::CODE_SHOPEEPAY,
                'name' => 'ShopeePay',
                'instructions' => 'Scan QR ShopeePay. QR code tampil setelah pesanan dibuat.',
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
