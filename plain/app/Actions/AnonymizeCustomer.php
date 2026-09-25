<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Removes a customer's personal data while keeping their orders for reports.
 */
class AnonymizeCustomer
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->forceFill([
                'full_name' => null,
                'province' => null,
                'city' => null,
                'district' => null,
                'postal_code' => null,
                'street_address' => null,
                'whatsapp' => null,
                'email' => null,
                'password' => null,
                'remember_token' => null,
                'anonymized_at' => now(),
            ])->save();

            $user->activityLogs()->delete();
            DB::table('cart_items')->where('user_id', $user->id)->delete();
            DB::table('product_views')->where('user_id', $user->id)->delete();
            DB::table('coin_lots')->where('user_id', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });
    }
}