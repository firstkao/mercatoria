<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Struktur berubah total — drop & recreate
        // Cart reminder cuma tracking, aman kalau data lama hilang.
        Schema::dropIfExists('cart_reminders');

        Schema::create('cart_reminders', function (Blueprint $table) {
            $table->id();

            // 1 user = 1 row (unique)
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('item_count')->default(0);
            $table->unsignedInteger('reminder_count')->default(0);

            // Kapan user terakhir kali tambah barang ke cart
            // → patokan timer 30 menit
            $table->timestamp('first_added_at')->nullable();

            // Kapan reminder terakhir dikirim
            // → NULL = belum pernah dikirim (baru masuk cart)
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_reminders');
    }
};
