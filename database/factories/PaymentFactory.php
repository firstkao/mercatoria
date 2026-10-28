<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => Payment::TYPE_FULL,
            'method' => 'bca',
            'amount' => 100000,
            'file_path' => 'payment-proofs/test.jpg',
            'status' => Payment::STATUS_PENDING,
            'verified_by' => null,
            'verified_at' => null,
            'reject_reason' => null,
            'uploaded_at' => now(),
        ];
    }
}
