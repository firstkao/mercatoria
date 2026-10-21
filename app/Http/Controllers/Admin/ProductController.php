<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tag;
use App\Services\Image\ImageWatermarkService;
use App\Services\Product\ProductDeletionService;
use App\Services\Product\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const VARIANTS_PER_PAGE = 20;

    public function __construct(
        private readonly StoreService $storeService,
        private readonly ProductDeletionService $deletionService,
        private readonly ImageWatermarkService $images,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);

        $products = Product::query()
            ->with(['store', 'games'])
            ->withCount('orderItems')
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->whereIn(
                'products.id',
                $this->storeService->productsUsing(Store::findOrFail($storeId))->select('products.id'),
            ))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'stores' => Store::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formData(new Product, false));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $product = DB::transaction(function () use ($validated, $request): Product {
            $this->assertDiscountValid($validated);
            $product = Product::create($this->attributes($request, $validated));
            $this->syncRelations($product, $validated);

            return $product;
        });

        return redirect()->route('office.products.edit', $product)->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', $this->formData($product, true));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        $oldPaths = [$product->main_image_path, $product->description_image_path];

        DB::transaction(function () use ($validated, $request, $product): void {
            $this->assertDiscountValid($validated);
            $product->update($this->attributes($request, $validated, $product));
            $this->syncRelations($product, $validated);
        });

        $product->refresh();
        foreach ($oldPaths as $old) {
            if ($old && ! in_array($old, [$product->main_image_path, $product->description_image_path], true)) {
                Storage::disk('public')->delete($old);
            }
        }

        return redirect()->route('office.products.edit', $product)->with('success', 'Perubahan produk berhasil disimpan.');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $copy = DB::transaction(function () use ($product): Product {
            $suffix = Str::lower(Str::random(4));
            $copy = $product->replicate(['sold_count', 'main_image_path', 'description_image_path']);
            $copy->name = $product->name.' (Salinan)';
            $copy->slug = Str::limit($product->slug, 240, '').'-salinan-'.$suffix;
            $copy->sku = Str::limit($product->sku, 56, '').'-'.Str::upper($suffix);
            $copy->is_active = false;
            $copy->sold_count = 0;
            $copy->save();

            $copy->games()->sync($product->games()->pluck('games.id')->all());
            $copy->tags()->sync($product->tags()->pluck('tags.id')->all());
            $copy->merchCategories()->sync($product->merchCategories()->pluck('merch_categories.id')->all());

            foreach ($product->variants()->with('combinations')->get() as $variant) {
                $newVariant = $variant->replicate(['image_path']);
                $newVariant->product_id = $copy->id;
                $newVariant->sku = Str::limit($variant->sku, 56, '').'-'.Str::upper($suffix);
                $newVariant->save();
                foreach ($variant->combinations as $combination) {
                    $newVariant->combinations()->create([
                        'attribute_key' => $combination->attribute_key,
                        'attribute_value' => $combination->attribute_value,
                    ]);
                }
            }

            return $copy;
        });

        return redirect()->route('office.products.edit', $copy)
            ->with('success', 'Produk berhasil diduplikasi (nonaktif). Lengkapi foto lalu terbitkan.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $ordersCount = $product->orderItems()->count();
        $this->deletionService->deleteMany([$product->id]);

        return redirect()->route('office.products.index')->with(
            'success',
            "Produk dihapus permanen. {$ordersCount} item order terdahulu tetap tersimpan sebagai snapshot.",
        );
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ]);
        $count = $this->deletionService->deleteMany(array_map('intval', $validated['product_ids']));

        return redirect()->route('office.products.index')->with('success', "{$count} produk dihapus permanen.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Product $product, bool $isEditing): array
    {
        $data = [
            'product' => $product,
            'stores' => Store::query()->orderBy('name')->get(),
            'games' => Game::query()->with('defaultStore')->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'merchCategories' => MerchCategory::query()->orderBy('name')->get(),
            'isEditing' => $isEditing,
            'variants' => null,
            'relatedCandidates' => collect(),
            'ordersCount' => 0,
        ];

        if ($isEditing) {
            $product->load(['games.defaultStore', 'tags', 'merchCategories', 'relatedProducts']);
            $data['variants'] = $product->variants()
                ->with('combinations')
                ->reorder('id')
                ->paginate(self::VARIANTS_PER_PAGE, ['*'], 'varian_page');
            $data['relatedCandidates'] = Product::query()
                ->whereKeyNot($product->id)
                ->orderBy('name')
                ->limit(300)
                ->get(['id', 'name', 'sku']);
            $data['ordersCount'] = $product->orderItems()->count();
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributes(Request $request, array $validated, ?Product $product = null): array
    {
        $data = collect($validated)->except([
            'game_ids', 'tag_ids', 'merch_category_ids', 'related_ids', 'main_image', 'description_image',
        ])->all();

        if ($request->hasFile('main_image')) {
            $data['main_image_path'] = $this->images->storeWithWatermark($request->file('main_image'), 'products');
        }
        if ($request->hasFile('description_image')) {
            $data['description_image_path'] = $this->images->storeWithWatermark($request->file('description_image'), 'products/descriptions');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncRelations(Product $product, array $validated): void
    {
        $product->games()->sync($validated['game_ids']);
        $product->tags()->sync($validated['tag_ids'] ?? []);
        $product->merchCategories()->sync($validated['merch_category_ids'] ?? []);
        $product->relatedProducts()->sync(
            collect($validated['related_ids'] ?? [])->reject(fn ($id) => (int) $id === $product->id)->values()->all()
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assertDiscountValid(array $validated): void
    {
        $discount = (float) ($validated['discount_amount'] ?? 0);
        if ($discount <= 0) {
            return;
        }

        $probe = new Product([
            'price_rmb' => $validated['price_rmb'],
            'weight_gram' => $validated['weight_gram'],
            'store_id' => $validated['store_id'] ?? null,
            'type' => $validated['type'],
        ]);
        if (! empty($validated['game_ids'])) {
            $probe->setRelation('games', Game::with('defaultStore')->whereIn('id', $validated['game_ids'])->get());
        }
        if (! empty($validated['store_id'])) {
            $probe->setRelation('store', Store::find($validated['store_id']));
        }

        try {
            $base = (float) $probe->hargaJual();
        } catch (\Throwable) {
            return;
        }

        if ($discount > $base) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Diskon tidak boleh lebih besar dari harga jual (Rp '.number_format($base, 0, ',', '.').').',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product)],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($product)],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['single', 'variant'])],
            'availability' => ['required', Rule::in(['tersedia', 'kosong'])],
            'weight_gram' => ['required', 'integer', 'min:1', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
            'price_rmb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'game_ids' => ['required', 'array', 'min:1'],
            'game_ids.*' => ['integer', 'distinct', 'exists:games,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id'],
            'merch_category_ids' => ['nullable', 'array'],
            'merch_category_ids.*' => ['integer', 'distinct', 'exists:merch_categories,id'],
            'related_ids' => ['nullable', 'array', 'max:12'],
            'related_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'discount_start' => ['nullable', 'date'],
            'discount_end' => ['nullable', 'date', 'after_or_equal:discount_start'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'description_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'slug.unique' => 'Slug sudah dipakai produk lain.',
            'sku.unique' => 'SKU sudah dipakai produk lain.',
            'game_ids.required' => 'Pilih minimal satu game.',
            'main_image.max' => 'Foto produk maksimal 1 MB.',
            'main_image.mimes' => 'Foto produk harus berformat JPG, PNG, atau WebP.',
            'description_image.max' => 'Gambar deskripsi maksimal 2 MB.',
            'description_image.mimes' => 'Gambar deskripsi harus berformat JPG, PNG, atau WebP.',
            'discount_end.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);
    }
}
