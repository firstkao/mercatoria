<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Image\ImageWatermarkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public const ATTRIBUTES = [
        'karakter' => 'Karakter',
        'ukuran' => 'Ukuran',
        'model' => 'Model',
        'tipe' => 'Tipe',
    ];

    private const MAX_GENERATED_VARIANTS = 200;

    public function __construct(private readonly ImageWatermarkService $images) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:product_variants,sku'],
            'name' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['nullable', 'string', 'max:100'],
            'price_rmb' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'availability' => ['nullable', Rule::in(['tersedia', 'kosong'])],
            'weight_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'image.max' => 'Foto varian maksimal 1 MB.',
            'image.mimes' => 'Foto varian harus berformat JPG, PNG, atau WebP.',
        ]);

        $attributes = $this->cleanAttributes($validated['attributes'] ?? []);
        $name = $validated['name'] ?? null;
        if (! $name && $attributes) {
            $name = implode(' / ', $attributes);
        }

        DB::transaction(function () use ($validated, $attributes, $name, $request, $product): void {
            $variant = $product->variants()->create([
                'sku' => $validated['sku'],
                'name' => $name,
                'price_rmb' => $validated['price_rmb'] ?? null,
                'availability' => $validated['availability'] ?? 'tersedia',
                'weight_gram' => $validated['weight_gram'] ?? null,
                'image_path' => $request->hasFile('image')
                    ? $this->images->storeWithWatermark($request->file('image'), 'variants')
                    : null,
            ]);
            $this->syncCombinations($variant, $attributes);
        });

        return $this->backToVariants($product)->with('success', 'Varian berhasil ditambahkan.');
    }

    public function generate(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'values' => ['required', 'array'],
            'values.*' => ['nullable', 'string', 'max:500'],
            'price_rmb' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'weight_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $lists = [];
        foreach ($validated['values'] as $key => $raw) {
            if (! array_key_exists($key, self::ATTRIBUTES)) {
                continue;
            }
            $items = collect(explode(',', (string) $raw))->map(fn ($v) => trim($v))->filter()->unique()->values()->all();
            if ($items) {
                $lists[$key] = $items;
            }
        }

        if (! $lists) {
            return back()->withErrors(['values' => 'Isi minimal satu nilai atribut (pisahkan dengan koma).'])->withInput();
        }

        $combinations = [[]];
        foreach ($lists as $key => $items) {
            $next = [];
            foreach ($combinations as $combination) {
                foreach ($items as $item) {
                    $next[] = $combination + [$key => $item];
                }
            }
            $combinations = $next;
        }

        if (count($combinations) > self::MAX_GENERATED_VARIANTS) {
            return back()->withErrors(['values' => 'Maksimal '.self::MAX_GENERATED_VARIANTS.' variasi sekali buat.'])->withInput();
        }

        $existing = $product->variants()->pluck('name')->map(fn ($n) => Str::lower((string) $n))->all();
        $created = 0;

        DB::transaction(function () use ($combinations, $existing, $product, $validated, &$created): void {
            foreach ($combinations as $combination) {
                $name = implode(' / ', $combination);
                if (in_array(Str::lower($name), $existing, true)) {
                    continue;
                }
                $variant = $product->variants()->create([
                    'sku' => $this->uniqueSku($product, $combination),
                    'name' => $name,
                    'price_rmb' => $validated['price_rmb'] ?? null,
                    'availability' => 'tersedia',
                    'weight_gram' => $validated['weight_gram'] ?? null,
                ]);
                $this->syncCombinations($variant, $combination);
                $created++;
            }
        });

        return $this->backToVariants($product)->with('success', "{$created} variasi berhasil dibuat.");
    }

    public function update(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['nullable', 'string', 'max:100'],
            'price_rmb' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'availability' => ['required', Rule::in(['tersedia', 'kosong'])],
            'weight_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'image.max' => 'Foto varian maksimal 1 MB.',
            'image.mimes' => 'Foto varian harus berformat JPG, PNG, atau WebP.',
        ]);

        $oldImage = $variant->image_path;
        $attributes = $this->cleanAttributes($validated['attributes'] ?? []);

        DB::transaction(function () use ($validated, $attributes, $request, $variant): void {
            $data = [
                'name' => $validated['name'] ?? $variant->name,
                'price_rmb' => $validated['price_rmb'] ?? null,
                'availability' => $validated['availability'],
                'weight_gram' => $validated['weight_gram'] ?? null,
            ];
            if ($request->hasFile('image')) {
                $data['image_path'] = $this->images->storeWithWatermark($request->file('image'), 'variants');
            }
            $variant->update($data);
            if (array_key_exists('attributes', $validated)) {
                $this->syncCombinations($variant, $attributes);
            }
        });

        if ($request->hasFile('image') && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $this->backToVariants($product)->with('success', 'Varian berhasil diperbarui.');
    }

    public function updateWeight(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $validated = $request->validate([
            'weight_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $variant->update($validated);

        return $this->backToVariants($product)->with('success', 'Berat varian berhasil disimpan.');
    }

    /**
     * Menyimpan perubahan massal yang di-stage di form (bulk edit berat / harga RMB).
     */
    public function saveChanges(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'weights' => ['nullable', 'array'],
            'weights.*' => ['required', 'integer', 'min:1', 'max:1000000'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $ids = array_unique([...array_keys($validated['weights'] ?? []), ...array_keys($validated['prices'] ?? [])]);
        $variants = $product->variants()->whereIn('id', $ids)->get()->keyBy('id');
        abort_unless($variants->count() === count($ids), 422, 'Varian tidak valid.');

        DB::transaction(function () use ($variants, $validated): void {
            foreach ($validated['weights'] ?? [] as $id => $weight) {
                $variants[(int) $id]->weight_gram = (int) $weight;
            }
            foreach ($validated['prices'] ?? [] as $id => $price) {
                $variants[(int) $id]->price_rmb = $price;
            }
            $variants->each->save();
        });

        return $this->backToVariants($product)->with('success', "Perubahan {$variants->count()} varian berhasil disimpan.");
    }

    public function bulkUpdateWeight(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'selected_variant_ids' => ['required', 'array', 'min:1'],
            'selected_variant_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'weight_gram' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $updatedCount = $product->variants()
            ->whereIn('id', $validated['selected_variant_ids'])
            ->update(['weight_gram' => $validated['weight_gram']]);

        return $this->backToVariants($product)
            ->with('variant_weight_updated', $updatedCount)
            ->with('success', "Berat {$updatedCount} varian berhasil diperbarui.");
    }

    public function bulkUpdatePriceRmb(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'selected_variant_ids' => ['required', 'array', 'min:1'],
            'selected_variant_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'price_rmb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $updatedCount = $product->variants()
            ->whereIn('id', $validated['selected_variant_ids'])
            ->update(['price_rmb' => $validated['price_rmb']]);

        return $this->backToVariants($product)->with('success', "Harga RMB {$updatedCount} varian berhasil diperbarui.");
    }

    private function backToVariants(Product $product): RedirectResponse
    {
        return redirect()->route('office.products.edit', $product)->with('tab', 'varian');
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    private function cleanAttributes(array $input): array
    {
        $clean = [];
        foreach (self::ATTRIBUTES as $key => $label) {
            $value = trim((string) ($input[$key] ?? ''));
            if ($value !== '') {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function syncCombinations(ProductVariant $variant, array $attributes): void
    {
        $variant->combinations()->delete();
        foreach ($attributes as $key => $value) {
            $variant->combinations()->create(['attribute_key' => $key, 'attribute_value' => $value]);
        }
    }

    /**
     * @param  array<string, string>  $combination
     */
    private function uniqueSku(Product $product, array $combination): string
    {
        $suffix = collect($combination)->map(fn ($v) => Str::upper(Str::slug($v)))->implode('-');
        $base = Str::limit($product->sku.'-'.$suffix, 56, '');
        $sku = $base;
        $counter = 2;
        while (ProductVariant::query()->where('sku', $sku)->exists()) {
            $sku = $base.'-'.$counter++;
        }

        return $sku;
    }
}
