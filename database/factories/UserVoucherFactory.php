<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserVoucher>
 */
class UserVoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'voucher_id' => Voucher::factory(),
            'order_id' => null,
            'status' => UserVoucher::STATUS_ACTIVE,
            'used_at' => null,
            'expires_at' => now()->addDays(30),
        ];
    }
}
