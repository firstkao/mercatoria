<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_footer')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Moves the pages that used to live in resources/content into the database once.
        $now = now();
        $pages = [
            ['faq', 'Tanya Jawab Umum', 1, false],
            ['reseller', 'Reseller', 2, true],
            ['syarat-dan-ketentuan', 'Syarat & Ketentuan', 3, true],
            ['kebijakan-privasi', 'Kebijakan Privasi', 4, true],
        ];

        foreach ($pages as [$slug, $title, $order, $isSystem]) {
            $file = resource_path("content/{$slug}.md");

            DB::table('pages')->insert([
                'title' => $title,
                'slug' => $slug,
                'content' => is_file($file) ? file_get_contents($file) : '',
                'is_published' => true,
                'show_in_footer' => true,
                'sort_order' => $order,
                'is_system' => $isSystem,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};