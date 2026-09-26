<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Creates or updates a product together with its images and variants.
 */
class SaveProduct
{
    public function handle(Product $product, array $data, array $newImages = [], array $variantImages = []): Product
    {
        $filesToDelete = [];
        $manager = new ImageManager(new Driver());

        DB::transaction(function () use ($product, $data, $newImages, $variantImages, &$filesToDelete, $manager): void {
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
            
            // Kompresi & Konversi Foto Utama Produk
            foreach ($newImages as $image) {
                $filename = 'products/' . Str::random(40) . '.webp';
                $img = $manager->read($image)->scaleDown(width: 1000);
                Storage::disk('public')->put($filename, (string) $img->toWebp(75));

                $newImageIds[] = $product->images()->create([
                    'image_path' => $filename,
                    'sort_order' => ++$nextOrder,
                ])->id;
            }

            $this->applyMainImage($product, $data['main_image'] ?? null, $newImageIds);

            $existing = $product->variants()->get()->keyBy('id');
            $keptIds = [];
            $position = 0;

            foreach ($data['variants'] as $key => $row) {
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
                    
                    // Kompresi & Konversi Foto Varian
                    if ($upload instanceof UploadedFile) {
                        $filename = 'products/variants/' . Str::random(40) . '.webp';
                        $img = $manager->read($upload)->scaleDown(width: 800);
                        Storage::disk('public')->put($filename, (string) $img->toWebp(75));
                        $variant->image_path = $filename;
                    } else {
                        $variant->image_path = null;
                    }
                }

                $variant->save();
                $keptIds[] = $variant->id;
            }

            $removedVariants = $existing->except($keptIds);
            $filesToDelete = [...$filesToDelete, ...$removedVariants->pluck('image_path')->filter()->all()];
            $removedVariants->each->delete();
        });

        Storage::disk('public')->delete($filesToDelete);

        return $product;
    }

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

    private function jakartaDate(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value, 'Asia/Jakarta')->utc() : null;
    }
}
