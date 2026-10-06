<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_media')) {
            return;
        }

        Schema::create('social_media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('icon_key')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Safety: kalau udah ada data (mis. re-run atau restore), jangan seed ulang.
        if (DB::table('social_media')->exists()) {
            return;
        }

        // Mapping default seed.
        // Format: [name, icon_key, sort_order, setting_keys_lama]
        //
        // - setting_keys_lama: array key di tabel `settings` yang dibaca buat
        //   ngambil URL awal. Multi-key dipakai buat handle variasi penulisan
        //   (mis. Threads: 'social_threads' vs 'social_thread' di DB produksi).
        //
        // - WhatsApp: URL-nya dibaca dari `contact_whatsapp` (kontak CS).
        //   CATATAN: contact_whatsapp TIDAK dihapus dari settings — masih
        //   dipakai di invoice & packing slip. Baris social_media ini cuma
        //   buat nampilin ikon WA di header/footer.
        $defaults = [
            ['Instagram', 'instagram', 1, ['social_instagram']],
            ['Facebook',  'facebook',  2, ['social_facebook']],
            ['X',         'x',         3, ['social_x']],
            ['Threads',   'threads',   4, ['social_threads', 'social_thread']],
        ];

        foreach ($defaults as [$name, $iconKey, $order, $settingKeys]) {
            $url = null;

            if (! empty($settingKeys)) {
                try {
                    // whereIn + value() -> ambil baris pertama yang cocok.
                    $url = DB::table('settings')
                        ->whereIn('key', $settingKeys)
                        ->value('value');
                } catch (\Throwable $e) {
                    $url = null;
                }
            }

            // Fallback khusus Threads kalau di settings nggak ketemu.
            if ($iconKey === 'threads' && empty($url)) {
                $url = 'https://www.threads.com/@mercatoria_id';
            }

            // Normalisasi nomor WA mentah (08xxx) jadi link wa.me penuh.
            if ($iconKey === 'whatsapp' && $url !== null && ! str_starts_with($url, 'http')) {
                $num = preg_replace('/\D/', '', $url);
                if (str_starts_with($num, '0')) {
                    $num = '62'.substr($num, 1);
                }
                $url = $num !== '' ? 'https://wa.me/'.$num : null;
            }

            DB::table('social_media')->insert([
                'name'       => $name,
                'url'        => $url,
                'icon_url'   => null,
                'icon_key'   => $iconKey,
                'sort_order' => $order,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media');
    }
};
