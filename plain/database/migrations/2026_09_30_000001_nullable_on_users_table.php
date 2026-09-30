<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AnonymizeCustomer meng-null-kan email dan password. Skema produksi sudah mengizinkannya,
 * tapi migrasi awal membuat keduanya NOT NULL, jadi database baru (staging/lokal/pindah
 * hosting) akan error saat anonimisasi. Migrasi ini menyamakannya; aman dijalankan di
 * produksi karena kolomnya sudah nullable (dilewati).
 */
return new class extends Migration
{
    public function up(): void
    {
        $columns = collect(Schema::getColumns('users'))->keyBy('name');

        Schema::table('users', function (Blueprint $table) use ($columns): void {
            if ($columns->has('email') && ! $columns['email']['nullable']) {
                $table->string('email')->nullable()->change();
            }

            if ($columns->has('password') && ! $columns['password']['nullable']) {
                $table->string('password')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Sengaja kosong: mengembalikan NOT NULL akan gagal untuk akun yang sudah dianonimkan.
    }
};
