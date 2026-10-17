<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_item_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique()
                ->constrained('order_items')->cascadeOnDelete();

            // Audit
            $table->unsignedBigInteger('product_id_audit')->nullable();
            $table->unsignedBigInteger('variant_id_audit')->nullable();

            // Produk snapshot
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_sku', 64);
            $table->string('game_name')->nullable();
            $table->string('developer_name')->nullable();
            $table->string('store_name', 100)->nullable();
            $table->string('tag', 32)->nullable();

            // Varian snapshot
            $table->string('variant_name')->nullable();
            $table->string('variant_sku', 64)->nullable();
            $table->json('variant_attributes')->nullable();

            // Harga
            $table->decimal('base_price', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('final_price', 14, 2)->default(0);
            $table->integer('qty')->default(1);
            $table->decimal('subtotal', 14, 2)->default(0);

            // Berat & ongkir breakdown (v4.4)
            $table->integer('weight_gram')->nullable();
            $table->decimal('ongkir_lokal_cn_idr', 14, 2)->default(0);
            $table->decimal('ongkir_cn_btm_idr', 14, 2)->default(0);
            $table->decimal('ongkir_btm_jkt_idr', 14, 2)->default(0);
            $table->decimal('ongkir_total_idr', 14, 2)->default(0);

            // Gambar (copy)
            $table->string('product_image_path')->nullable();
            $table->string('variant_image_path')->nullable();

            $table->timestamp('snapshot_at')->nullable();
            $table->timestamps();

            $table->index(['product_id_audit']);
            $table->index(['snapshot_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('order_item_snapshots'); }
};
