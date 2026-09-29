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
            // 1) Hapus data pribadi dari row user
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

            // 2) Bersihkan data turunan yang mengandung info personal
            $user->activityLogs()->delete();
            DB::table('cart_items')->where('user_id', $user->id)->delete();
            DB::table('product_views')->where('user_id', $user->id)->delete();
            DB::table('coin_lots')->where('user_id', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            // 3) Bersihkan tabel dari batch-batch terbaru (kalau tabelnya ada)
            if (\Illuminate\Support\Facades\Schema::hasTable('user_notifications')) {
                DB::table('user_notifications')->where('user_id', $user->id)->delete();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('cart_reminders')) {
                DB::table('cart_reminders')->where('user_id', $user->id)->delete();
            }
        });
    }
}