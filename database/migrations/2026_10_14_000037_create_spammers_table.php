<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spammers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('reason', 32);
            $table->string('status', 16)->default('blocked');
            $table->integer('unblock_count')->default(0);
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['email']);
            $table->index(['no_hp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spammers');
    }
};
