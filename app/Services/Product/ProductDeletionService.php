<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductDeletionService
{
    /**
     * @param  list<int>  $productIds
     */
    public function deleteMany(array $productIds): int
    {
        $products = Product::with(['images', 'variants.images'])
            ->whereIn('id', $productIds)
            ->get();

        if ($products->count() !== count(array_unique($productIds))) {
            abort(404);
        }

        $paths = [];
        foreach ($products as $product) {
            $paths[] = $product->main_image_path;
            $paths[] = $product->description_image_path;
            array_push($paths, ...$product->images->pluck('image_path')->all());
            foreach ($product->variants as $variant) {
                $paths[] = $variant->image_path;
                array_push($paths, ...$variant->images->pluck('image_path')->all());
            }
        }
        $paths = collect($paths)->filter()->unique()->values();

        DB::transaction(function () use ($products): void {
            foreach ($products as $product) {
                $product->delete();
            }
        });

        $disk = Storage::disk('public');
        foreach ($paths as $path) {
            if ($disk->exists($path) && ! $disk->delete($path)) {
                Log::error('Hard delete removed product records but failed to remove its media.', [
                    'path' => $path,
                    'product_ids' => $products->modelKeys(),
                ]);

                throw new RuntimeException("Gagal menghapus file media produk: {$path}");
            }
        }

        return $products->count();
    }
}
