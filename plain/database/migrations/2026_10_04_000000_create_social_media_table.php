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
            $table->string('name'); // Instagram, Facebook, X, Threads, WhatsApp
            $table->string('url')->nullable();
            $table->string('icon_url')->nullable();   // URL icon custom (SVG/PNG)
            $table->string('icon_key')->nullable();   // Kunci ikon bawaan: instagram|facebook|x|whatsapp|threads
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default dari data settings lama + satu entri Threads baru.
        // Ikon bawaan dirender via SVG inline di layout (bukan FontAwesome).
        $defaults = [
           $defaults = [
                ['Instagram', 'instagram', 1],
                ['Facebook',  'facebook',  2],
                ['X',         'x',         3],
                ['Threads',   'threads',   4],
            ];
        ];

        foreach ($defaults as [$name, $settingKey, $iconKey, $order]) {
            $url = null;
            if ($settingKey !== null) {
                try {
                    $url = DB::table('settings')->where('key', $settingKey)->value('value');
                } catch (\Throwable $e) {
                    $url = null;
                }
            }

            if ($name === 'Threads') {
                // BUG FIX: kunci di database produksi adalah 'social_thread'
                // (bentuk tunggal), bukan 'social_threads'. Baca keduanya supaya
                // URL Threads dari settings lama ikut termigrasi, baru fallback
                // ke nilai bawaan.
                try {
                    $url = DB::table('settings')->whereIn('key', ['social_threads', 'social_thread'])->value('value')
                        ?? 'https://www.threads.com/@mercatoria_id';
                } catch (\Throwable $e) {
                    $url = 'https://www.threads.com/@mercatoria_id';
                }
            }

            // Normalisasi nomor WA mentah (08xxx) jadi link wa.me penuh.
            if ($iconKey === 'whatsapp' && $url !== null && ! str_starts_with($url, 'http')) {
                $num = preg_replace('/\D/', '', $url);
                if (str_starts_with($num, '0')) {
                    $num = '62'.substr($num, 1);
                }
                $url = 'https://wa.me/'.$num;
            }

            DB::table('social_media')->insert([
                'name' => $name,
                'url' => $url,
                'icon_url' => null,
                'icon_key' => $iconKey,
                'sort_order' => $order,
                'is_active' => true,
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
