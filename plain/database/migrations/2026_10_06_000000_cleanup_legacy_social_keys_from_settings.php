<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * CLEANUP: Hapus key sosmed lama dari tabel `settings`.
 *
 * Konteks:
 * - Dulu (sebelum ada tabel `social_media`), URL sosmed disimpan sebagai
 *   key-value di tabel `settings`: social_instagram, social_facebook,
 *   social_x, social_threads/social_thread, dan contact_whatsapp.
 * - Sejak migrasi 2026_10_04_000000_create_social_media_table, semua URL
 *   sosmed dipindah ke tabel `social_media`. Key `social_*` di settings
 *   jadi DUPLIKAT dan tidak pernah dibaca lagi oleh kode manapun
 *   (sudah dicek via grep di seluruh app/ resources/ routes/).
 *
 * PENGECUALIAN:
 * - `contact_whatsapp` TIDAK dihapus. Key ini dipakai di invoice, packing
 *   slip, dan order print — fungsinya kontak CS, bukan link sosmed.
 *
 * RISIKO:
 * - Data lama dihapus permanen. Pastikan sudah migrasi ke `social_media`
 *   (cek: SELECT * FROM social_media WHERE icon_key IS NOT NULL).
 */
return new class extends Migration
{
    /** Key sosmed lama yang sudah digantikan oleh tabel `social_media`. */
    private const LEGACY_SOCIAL_KEYS = [
        'social_instagram',
        'social_facebook',
        'social_x',
        'social_threads',
        'social_thread',
    ];

    public function up(): void
    {
        // Safety: pastikan tabel social_media sudah ada dan ada isinya,
        // supaya kita nggak menghapus data lama sebelum data baru siap.
        if (! \Illuminate\Support\Facades\Schema::hasTable('social_media')) {
            return;
        }

        if (! DB::table('social_media')->exists()) {
            // Tabel social_media kosong -> jangan hapus, nanti data sosmed
            // hilang dari sistem. Biarkan admin migrasi manual dulu.
            return;
        }

        DB::table('settings')
            ->whereIn('key', self::LEGACY_SOCIAL_KEYS)
            ->delete();
    }

    public function down(): void
    {
        // Tidak bisa restore karena nilainya sudah dipindah ke social_media.
        // Kalau butuh rollback, restore dari backup DB.
    }
};
