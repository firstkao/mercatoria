<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dropdown alamat berantai (Kemendagri): provinsi -> kota/kabupaten -> kecamatan -> kode pos.
        Schema::create('indonesia_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('indonesia_regions')->cascadeOnDelete();
            $table->string('type', 20); // provinsi | kota | kecamatan | kode_pos
            $table->string('name', 100);
            $table->string('subtype', 20)->nullable(); // kota | kabupaten (untuk type=kota)
            $table->timestamps();

            $table->index(['type', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indonesia_regions');
    }
};
