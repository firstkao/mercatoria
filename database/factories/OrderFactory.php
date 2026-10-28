<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'no_order' => 'MER-'.fake()->unique()->bothify('?????').'-'.fake()->unique()->numerify('####'),
            'status' => 'menunggu_pembayaran',
            'scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'subtotal' => 255000,
            'discount_type' => null,
            'discount_amount' => 0,
            'coin_spent' => 0,
            'user_voucher_id' => null,
            'total' => 255000,
            'fee_qris' => 0,
            'tagihan_web' => 255000,
            'estimasi_co_marketplace' => null,
            'fee_co_marketplace' => null,
            'customer_note' => null,
            'payment_deadline_at' => now()->addHours(24),
        ];
    }
}
