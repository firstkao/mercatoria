<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG FIX (ikon sosmed tidak muncul walau data ada di tabel social_media):
     * normalisasi baris lama yang tersimpan sebelum perbaikan input di
     * SettingsController:
     * - url/icon_url berisi string kosong atau hanya spasi -> NULL, supaya
     *   filter layout (buang URL kosong) konsisten dan admin bisa melihat
     *   baris mana yang benar-benar belum diisi.
     * - icon_key dengan kapitalisasi/spasi liar ('Instagram', ' X ') -> lower
     *   & trim, supaya SVG bawaan di partials/social-icon.blade.php cocok.
     * - is_active string '0'/'1' -> 0/1 murni.
     */
    public function up(): void
    {
        if (! Schema::hasTable('social_media')) {
            return;
        }

        // String kosong/spasi pada url & icon_url menjadi NULL.
        DB::table('social_media')
            ->whereNotNull('url')
            ->update(['url' => DB::raw("CASE WHEN TRIM(url) = '' THEN NULL ELSE TRIM(url) END")]);

        DB::table('social_media')
            ->whereNotNull('icon_url')
            ->update(['icon_url' => DB::raw("CASE WHEN TRIM(icon_url) = '' THEN NULL ELSE TRIM(icon_url) END")]);

        // Normalisasi icon_key per baris (trim + lower).
        $rows = DB::table('social_media')
            ->whereNotNull('icon_key')
            ->get(['id', 'icon_key']);

        foreach ($rows as $row) {
            $clean = strtolower(trim((string) $row->icon_key));
            $update = [];

            if ($clean === '') {
                $update['icon_key'] = null;
            } elseif ($clean !== $row->icon_key) {
                $update['icon_key'] = $clean;
            }

            if ($update !== []) {
                DB::table('social_media')->where('id', $row->id)->update($update);
            }
        }

        // is_active: pastikan 0/1 integer (bukan 'on'/'yes'/string lain).
        DB::table('social_media')->update([
            'is_active' => DB::raw('CAST(is_active AS UNSIGNED) > 0'),
        ]);
    }

    public function down(): void
    {
        // Data cleanup non-destruktif — tidak perlu rollback.
    }
};
