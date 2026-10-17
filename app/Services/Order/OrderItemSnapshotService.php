<?php

namespace App\Services\Order;

use App\Models\OrderItem;
use App\Models\OrderItemSnapshot;
use App\Services\Cart\CartPricingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderItemSnapshotService
{
    public function __construct(
        protected CartPricingService $cartPricing,
    ) {}

    public function createForOrderItem(OrderItem $item, string $orderNo): OrderItemSnapshot
    {
        if (! $item->product) {
            throw new \RuntimeException("Produk tidak ditemukan untuk order item #{$item->id}");
        }

        $product = $item->product;
        $variant = $item->variant;
        $pricing = $this->cartPricing->calculate($product, $variant);
        $shipping = $pricing['shipping'];

        $game = $product->games->first();
        $developerName = $game?->developers->first()?->name;

        $productImagePath = $this->copyImage(
            $product->main_image_path,
            "order-snapshots/{$orderNo}/p-{$item->id}.jpg"
        );

        $variantImagePath = null;
        if ($variant?->image_path) {
            $variantImagePath = $this->copyImage(
                $variant->image_path,
                "order-snapshots/{$orderNo}/v-{$item->id}.jpg"
            );
        }

        return OrderItemSnapshot::create([
            'order_item_id' => $item->id,
            'product_id_audit' => $product->id,
            'variant_id_audit' => $variant?->id,

            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_sku' => $product->sku,
            'game_name' => $game?->name,
            'developer_name' => $developerName,
            'store_name' => $shipping['store_name'],
            'tag' => $product->tags->first()?->name,

            'variant_name' => $variant?->name,
            'variant_sku' => $variant?->sku,
            'variant_attributes' => $variant?->attributesArray(),

            'base_price' => $pricing['base_price'],
            'discount_amount' => $pricing['discount_amount'],
            'final_price' => $pricing['final_price'],
            'qty' => $item->qty,
            'subtotal' => $pricing['final_price'] * $item->qty,

            'weight_gram' => $shipping['weight_gram'],
            'ongkir_lokal_cn_idr' => $shipping['ongkir_lokal_cn_idr'],
            'ongkir_cn_btm_idr' => $shipping['ongkir_cn_btm_idr'],
            'ongkir_btm_jkt_idr' => $shipping['ongkir_btm_jkt_idr'],
            'ongkir_total_idr' => $shipping['ongkir_total_idr'],

            'product_image_path' => $productImagePath,
            'variant_image_path' => $variantImagePath,

            'snapshot_at' => now(),
        ]);
    }

    protected function copyImage(?string $sourcePath, string $destPath): ?string
    {
        if (empty($sourcePath)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($sourcePath)) {
            Log::warning('Snapshot copy: source not found', [
                'source' => $sourcePath,
                'dest' => $destPath,
            ]);

            return null;
        }

        try {
            $disk->copy($sourcePath, $destPath);

            return $destPath;
        } catch (\Throwable $e) {
            Log::error('Snapshot copy failed', [
                'source' => $sourcePath,
                'dest' => $destPath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
