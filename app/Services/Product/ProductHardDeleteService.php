<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductHardDeleteService
{
    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $this->safeDeleteFile($product->main_image_path);
            $this->safeDeleteFile($product->description_image_path);

            foreach ($product->variants as $variant) {
                $this->safeDeleteFile($variant->image_path);
            }

            $product->games()->detach();
            $product->tags()->detach();
            $product->merchCategories()->detach();

            DB::table('product_related')
                ->where('product_id', $product->id)
                ->orWhere('related_id', $product->id)
                ->delete();

            DB::table('product_views')->where('product_id', $product->id)->delete();
            DB::table('share_clicks')->where('product_id', $product->id)->delete();
            DB::table('carts')->where('product_id', $product->id)->delete();

            $product->forceDelete();
        });
    }

    public function bulkDelete(array $productIds): array
    {
        $result = ['success' => 0, 'failed' => 0, 'errors' => []];

        Product::whereIn('id', $productIds)
            ->chunk(20, function ($products) use (&$result) {
                foreach ($products as $product) {
                    try {
                        $this->delete($product);
                        $result['success']++;
                    } catch (\Throwable $e) {
                        $result['failed']++;
                        $result['errors'][$product->id] = $e->getMessage();
                        Log::error("Bulk delete failed", [
                            'product_id' => $product->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $result;
    }

    protected function safeDeleteFile(?string $path): void
    {
        if (empty($path)) return;
        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
