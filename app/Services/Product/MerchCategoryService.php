<?php

namespace App\Services\Product;

use App\Models\MerchCategory;
use App\Models\Product;
use Illuminate\Support\Str;

class MerchCategoryService
{
    /**
     * Resolve slug kategori berdasarkan nama produk.
     * Fallback: slug yang dikirim → 'other'.
     */
    public function resolveSlug(string $name, ?string $slug = null): string
    {
        return MerchCategory::matchFor($name, $slug ?? '')?->slug
            ?? $slug
            ?? 'other';
    }

    /**
     * Scan produk aktif yang belum cocok kategori manapun dan buat/gabung kategori otomatis.
     * Menulis ke DB — hanya dipanggil dari command / aksi POST admin, bukan dari GET.
     *
     * @return int jumlah kategori baru yang dibuat
     */
    public function autoGenerateFromProducts(): int
    {
        $autoCreated = 0;

        $categories = MerchCategory::all();
        $products = Product::query()->where('is_active', true)->select('id', 'name')->get();

        foreach ($products as $product) {
            $matched = false;
            foreach ($categories as $cat) {
                foreach ($cat->keywords ?? [] as $kw) {
                    if ($kw && stripos($product->name, $kw) !== false) {
                        $matched = true;
                        break 2;
                    }
                }
            }

            if ($matched) {
                continue;
            }

            $words = array_values(array_filter(
                preg_split('/[\s\-_,.\/\(\)]+/', $product->name),
                fn ($w) => strlen($w) >= 3 && ! is_numeric($w)
            ));

            if (empty($words)) {
                continue;
            }

            $name = ucfirst(strtolower($words[0]));

            $existing = $categories->firstWhere('name', $name);
            if ($existing) {
                $merged = array_unique(array_merge($existing->keywords ?? [], $words));
                $existing->update(['keywords' => array_values($merged)]);

                continue;
            }

            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $i = 2;
            while (MerchCategory::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }

            $categories->push(MerchCategory::create([
                'name' => $name,
                'slug' => $slug,
                'priority' => (MerchCategory::max('priority') ?? 0) + 1,
                'keywords' => $words,
            ]));
            $autoCreated++;
        }

        return $autoCreated;
    }
}
