<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Creates or updates a product together with its images and variants.
 */
class SaveProduct
{
    /**
     * @param  array<string, mixed>  $data  Validated ProductRequest data.
     * @param  array<int, UploadedFile>  $newImages
     * @param  array<int|string, UploadedFile|null>  $variantImages  Keyed like $data['variants'].
     */
    public function handle(Product $product, array $data, array $newImages = [], array $variantImages = []): Product
    {
        $filesToDelete = [];

        DB::transaction(function () use ($product, $data, $newImages, $variantImages, &$filesToDelete): void {
            $product->fill([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'sku' => $data['sku'] ?? null,
                'game_id' => $data['game_id'] ?? null,
                'developer_id' => $data['developer_id'] ?? null,
                'shipping_tier_id' => $data['shipping_tier_id'],
                'tag' => $data['tag'] ?? null,
                'description' => $data['description'] ?? null,
                'sale_starts_at' => $this->jakartaDate($data['sale_starts_at'] ?? null),
                'sale_ends_at' => $this->jakartaDate($data['sale_ends_at'] ?? null),
                'is_published' => $data['is_published'] ?? false,
            ])->save();

            $removedImages = $product->images()->whereIn('id', $data['remove_images'] ?? [])->get();
            $filesToDelete = $removedImages->pluck('image_path')->all();
            $removedImages->each->delete();

            $nextOrder = (int) $product->images()->max('sort_order');
            $newImageIds = [];
            foreach ($newImages as $image) {
                $newImageIds[] = $product->images()->create([
                    'image_path' => $image->store('products', 'public'),
                    'sort_order' => ++$nextOrder,
                ])->id;
            }

            $this->applyMainImage($product, $data['main_image'] ?? null, $newImageIds);

            $existing = $product->variants()->get()->keyBy('id');
            $keptIds = [];
            $position = 0;

            foreach ($data['variants'] as $key => $row) {
                /** @var ProductVariant $variant */
                $variant = isset($row['id']) && $existing->has((int) $row['id'])
                    ? $existing->get((int) $row['id'])
                    : $product->variants()->make();

                $variant->fill([
                    'name' => $row['name'],
                    'sku' => $row['sku'] ?? null,
                    'price_yuan' => $row['price_yuan'],
                    'compare_price_yuan' => $row['compare_price_yuan'] ?? null,
                    'weight_grams' => $row['weight_grams'],
                    'status' => $row['status'],
                    'sort_order' => ++$position,
                ]);

                $upload = $variantImages[$key] ?? null;
                if ($upload instanceof UploadedFile || ! empty($row['remove_image'])) {
                    if ($variant->image_path) {
                        $filesToDelete[] = $variant->image_path;
                    }
                    $variant->image_path = $upload instanceof UploadedFile ? $upload->store('products/variants', 'public') : null;
                }

                $variant->save();
                $keptIds[] = $variant->id;
            }

            // Past orders keep their own snapshots, so removed variants can be deleted.
            $removedVariants = $existing->except($keptIds);
            $filesToDelete = [...$filesToDelete, ...$removedVariants->pluck('image_path')->filter()->all()];
            $removedVariants->each->delete();
        });

        Storage::disk('public')->delete($filesToDelete);

        return $product;
    }

    /**
     * Move the chosen image to the front; "new-N" refers to the N-th image uploaded in this request.
     *
     * @param  array<int, int>  $newImageIds
     */
    private function applyMainImage(Product $product, ?string $choice, array $newImageIds): void
    {
        if ($choice === null || $choice === '') {
            return;
        }

        $mainId = str_starts_with($choice, 'new-')
            ? ($newImageIds[(int) substr($choice, 4)] ?? null)
            : (int) $choice;

        $images = $product->images()->get();

        if ($mainId === null || ! $images->contains('id', $mainId)) {
            return;
        }

        $position = 1;
        foreach ($images->sortBy(fn ($image) => $image->id === $mainId ? 0 : 1)->values() as $image) {
            $image->update(['sort_order' => $position++]);
        }
    }

    /**
     * Admin enters sale dates in WIB; they are stored in UTC.
     */
    private function jakartaDate(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value, 'Asia/Jakarta')->utc() : null;
    }
}