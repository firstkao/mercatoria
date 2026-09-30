<?php

namespace App\Actions;

use App\Models\IdentityRecord;
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
            // Ambil dulu sebelum di-null-kan; dipakai untuk membersihkan identity_records di bawah.
            $identityValues = array_filter([
                IdentityRecord::KIND_EMAIL => $user->email,
                IdentityRecord::KIND_WHATSAPP => $user->whatsapp,
            ]);

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

            // 4) Tabel lain yang menyimpan nama/email/WhatsApp/alamat/IP milik user ini
            foreach (['reseller_applications', 'contact_messages'] as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    DB::table($table)->where('user_id', $user->id)->delete();
                }
            }

            // 5) Log admin lama menyimpan nama/email di metadata (mis. update_user)
            if (\Illuminate\Support\Facades\Schema::hasTable('admin_logs')) {
                DB::table('admin_logs')
                    ->where('subject_type', $user->getMorphClass())
                    ->where('subject_id', $user->id)
                    ->update(['metadata' => null]);
            }

            // 6) identity_records menyimpan email/WhatsApp asli. Hapus yang tidak punya riwayat
            //    penyalahgunaan; yang pernah dihapus sebagai spammer atau diblokir tetap disimpan
            //    karena itulah fungsi anti-spamnya. (registration_events ikut terhapus via cascade.)
            foreach ($identityValues as $kind => $value) {
                IdentityRecord::query()
                    ->where('kind', $kind)
                    ->where('value', $value)
                    ->where('deletion_count', 0)
                    ->whereNull('blocked_at')
                    ->delete();
            }
        });
    }
}
