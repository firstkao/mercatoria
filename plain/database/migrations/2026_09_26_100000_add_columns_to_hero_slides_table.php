<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            if (! Schema::hasColumn('hero_slides', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (! Schema::hasColumn('hero_slides', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('title');
            }
            if (! Schema::hasColumn('hero_slides', 'image_path')) {
                $table->string('image_path')->nullable()->after('subtitle');
            }
            if (! Schema::hasColumn('hero_slides', 'link_url')) {
                $table->string('link_url')->nullable()->after('image_path');
            }
            if (! Schema::hasColumn('hero_slides', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('link_url');
            }
            if (! Schema::hasColumn('hero_slides', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('alt_text');
            }
            if (! Schema::hasColumn('hero_slides', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropColumn([
                'title', 'subtitle', 'image_path', 'link_url',
                'alt_text', 'sort_order', 'is_active',
            ]);
        });
    }
};