<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkPriceRequest;
use App\Http\Requests\Admin\BulkProductRequest;
use App\Http\Requests\Admin\BulkWeightRequest;
use App\Http\Requests\Admin\ProductFormRequest;
use App\Http\Requests\Admin\VariantFormRequest;
use App\Models\AdminLog;
use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Tag;
use App\Services\Image\ImageOptimizerService;
use App\Services\Image\ImageWatermarkService;
use App\Services\Image\UploadError;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ImageWatermarkService $watermark,
        private ImageOptimizerService $optimizer,
        private ShippingCalculatorService $shipping,
    ) {}

    // ──────────────────────────────────────
    // INDEX / CREATE / EDIT
    // ──────────────────────────────────────

    public function index(): View
    {
        $query = Product::with(['game', 'developer', 'store', 'merchCategory']);

        if (request()->filled('game_id')) {
            $query->where('game_id', request('game_id'));
        }
        if (request()->filled('developer_id')) {
            $query->where('developer_id', request('developer_id'));
        }
        if (request()->filled('q')) {
            $q = request('q');
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
        }

        return view('admin.products.index', [
            'products' => $query->orderByDesc('id')->paginate(30)->withQueryString(),
            'games' => Game::orderBy('name')->get(),
            'developers' => Developer::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'games' => Game::orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
            'categories' => MerchCategory::navList(),
            'tags' => Tag::navList(),
            'developers' => Developer::orderBy('name')->get(),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('variants', 'tags', 'developer'),
            'games' => Game::orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
            'categories' => MerchCategory::navList(),
            'tags' => Tag::navList(),
            'developers' => Developer::orderBy('name')->get(),
        ]);
    }

    // ──────────────────────────────────────
    // STORE / UPDATE / DESTROY
    // ──────────────────────────────────────

    public function store(ProductFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['variants'], $data['tags']);
        $data['is_active'] = $request->boolean('is_active');

        $data['slug'] = blank($data['slug'] ?? null)
            ? Str::slug($data['name']).'-'.Str::random(4)
            : $data['slug'];
        $data = $this->processUploads($request, $data, null);

        // Auto type=variant kalau ada inline variants
        $variantRows = collect($request->input('variants', []))
            ->filter(fn ($v) => filled(data_get($v, 'name')) || filled(data_get($v, 'sku')) || filled(data_get($v, 'price_rmb')));
        if ($variantRows->isNotEmpty()) {
            $data['type'] = 'variant';
        }

        $product = Product::create($data + ['sold_count' => 0]);

        if (! $product->merch_category_id) {
            $product->update([
                'merch_category_id' => MerchCategory::matchFor($product->name, $product->slug)?->id,
            ]);
        }

        if ($request->filled('tags')) {
            $product->tags()->sync($request->input('tags'));
        }

        // [FIX] Create inline variants with validation
        $this->processInlineVariants($request, $product);
        $this->syncSplitBox($product);

        AdminLog::record($request->user(), 'create', 'Product', $product->id, null, $product->only(array_keys($data)), 'Tambah produk');

        return redirect()->route('office.products.edit', $product)->with('status', 'Produk dibuat.');
    }

    /**
     * [FIX] Simpan inline variants setelah product dibuat — dengan validasi per-row.
     */
    private function processInlineVariants(Request $request, Product $product): void
    {
        $rows = collect($request->input('variants', []));

        foreach ($rows as $i => $v) {
            if (blank(data_get($v, 'name')) && blank(data_get($v, 'sku')) && blank(data_get($v, 'price_rmb'))) {
                continue;
            }

            // [FIX #5] Validasi per-row sebelum insert
            $validated = validator($v, [
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:64|unique:product_variants,sku',
                'price_rmb' => 'required|numeric|min:0',
                'weight_gram' => 'nullable|integer|min:1',
                'availability' => 'nullable|in:tersedia,kosong',
            ])->validate();

            $validated['availability'] = $validated['availability'] ?? 'tersedia';
            $validated['sku'] = $validated['sku'] ?? ProductVariant::generateSku($product);

            if ($request->hasFile("variants.{$i}.image")) {
                $validated['image_path'] = $this->storeImage(
                    $request->file("variants.{$i}.image"), 'var', null, "variants.{$i}.image"
                );
            }

            $product->variants()->create($validated);
        }
    }

    public function update(ProductFormRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['variants'], $data['tags']);
        $data['is_active'] = $request->boolean('is_active');
        if ($error = $this->splitBoxProductUniformError($product, $data)) {
            return back()->withErrors(['price_rmb' => $error])->withInput();
        }

        $data = $this->processUploads($request, $data, $product);
        [$old, $new] = AdminLog::diff($product, array_keys($data), $data);

        $product->update($data);
        $product->tags()->sync($request->input('tags', []));
        $this->syncSplitBox($product);

        // PRD 14: auto-group kategori merch by keyword (override manual menang).
        if (! $product->merch_category_id) {
            $product->update([
                'merch_category_id' => MerchCategory::matchFor($product->name, $product->slug)?->id,
            ]);
        }

        AdminLog::record($request->user(), 'update', 'Product', $product->id, $old, $new, 'Ubah produk');

        return redirect()->route('office.products.edit', $product)->with('status', 'Produk diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        // [FIX #3] Shared delete logic
        $this->deleteProductWithFiles($product, $request->user(), 'Hapus permanen');

        return redirect()->route('office.products.index')->with('status', 'Produk dihapus permanen.');
    }

    // ──────────────────────────────────────
    // DUPLICATE / PREVIEW
    // ──────────────────────────────────────

    /**
     * PRD 13.5/22.6: duplikat produk (field + varian + file + tags) sebagai draft.
     */
    public function duplicate(Request $request, Product $product): RedirectResponse
    {
        $copy = $this->duplicateProduct($product, $request->user());

        return redirect()->route('office.products.edit', $copy)
            ->with('status', 'Produk diduplikat sebagai draft.');
    }

    /**
     * Modal "Buat Split Box": salinan produk (tanpa varian) dengan is_split_box aktif.
     * Varian dipecah manual oleh admin; produk asli tidak berubah.
     */
    public function splitClone(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'split_name' => ['required', 'string', 'max:150'],
            'split_price_rmb' => ['required', 'numeric', 'min:0'],
        ]);

        $copy = $this->duplicateProduct($product, $request->user(), withVariants: false);

        // Nama selalu berakhiran " (Split Box)" dan slug selalu di-regenerate dari nama + "-split-box".
        $baseName = trim(preg_replace('/\s*\(\s*split\s*box\s*\)\s*$/i', '', $data['split_name']));
        $name = ($baseName !== '' ? $baseName : $product->name).' (Split Box)';
        $baseSlug = Str::slug($baseName !== '' ? $baseName : $product->name).'-split-box';
        $slug = $baseSlug;
        for ($n = 2; Product::where('slug', $slug)->whereKeyNot($copy->id)->exists(); $n++) {
            $slug = $baseSlug.'-'.$n;
        }

        $copy->update([
            'name' => $name,
            'slug' => $slug,
            'price_rmb' => $data['split_price_rmb'],
            'is_split_box' => true,
        ]);
        $this->syncSplitBox($copy);

        return redirect()->route('office.products.edit', $copy)
            ->with('status', 'Produk split box dibuat sebagai draft. Pecah varian secara manual.');
    }

    /**
     * [FIX #2] Shared duplicate logic — copy file gambar + tags.
     */
    private function duplicateProduct(Product $product, $user, bool $withVariants = true): Product
    {
        $copy = $product->replicate(['sold_count']);
        $copy->name = $product->name.' (Salinan)';
        $copy->sku = $product->sku.'-COPY-'.Str::random(4);
        $copy->slug = $product->slug.'-copy-'.Str::random(4);
        $copy->is_active = false;

        // [FIX #2] Copy file foto utama
        if ($product->image_path) {
            $copy->image_path = $this->copyImageFile($product->image_path);
        }

        // [FIX #2] Copy file deskripsi
        if ($product->description_image_path) {
            $copy->description_image_path = $this->copyImageFile($product->description_image_path);
        }

        $copy->save();

        // [FIX #2] Copy varian + foto varian
        foreach ($withVariants ? $product->variants : [] as $variant) {
            $variantCopy = $variant->replicate();
            $variantCopy->sku = $variant->sku.'-COPY-'.Str::random(4);
            $variantCopy->product_id = $copy->id;

            if ($variant->image_path) {
                $variantCopy->image_path = $this->copyImageFile($variant->image_path);
            }

            $variantCopy->save();
        }

        // [FIX #2] Copy tags
        $copy->tags()->sync($product->tags->pluck('id')->all());

        if ($withVariants) {
            $this->duplicateSplitBox($product, $copy);
        }

        AdminLog::record($user, 'create', 'Product', $copy->id, null, [
            'duplicate_of' => $product->id,
            'sku' => $copy->sku,
        ], 'Duplikat produk (draft)');

        return $copy;
    }

    /**
     * Produk Split Box wajib punya record SplitBox dan satu slot (default Normal) per varian,
     * agar admin bisa mengatur role/qty dan membuka kloter dari tab Split Box.
     */
    private function syncSplitBox(Product $product): void
    {
        $product->refresh();

        if (! $product->is_split_box) {
            return;
        }

        $splitBox = $product->splitBox()->firstOrCreate([]);
        $existing = $splitBox->slots()->pluck('variant_id');
        $order = (int) $splitBox->slots()->max('sort_order');

        foreach ($product->variants()->whereNotIn('id', $existing)->orderBy('id')->get() as $variant) {
            $splitBox->slots()->create([
                'variant_id' => $variant->id,
                'split_role' => 'normal',
                'qty_per_box' => 1,
                'sort_order' => ++$order,
            ]);
        }
    }

    /**
     * Salin konfigurasi SplitBox + slot (dipetakan ke varian salinan). Kloter/order tidak disalin; admin membuka kloter baru.
     */
    private function duplicateSplitBox(Product $product, Product $copy): void
    {
        $source = $product->splitBox()->with('slots')->first();

        if (! $product->is_split_box || ! $source) {
            return;
        }

        $newVariants = $copy->variants()->get()->keyBy(fn ($v) => preg_replace('/-COPY-[A-Za-z0-9]{4}$/', '', $v->sku));
        $splitBox = $copy->splitBox()->create($source->only(['max_auto_batches', 'auto_open_batch']));

        foreach ($source->slots as $slot) {
            $variant = $newVariants->get($slot->variant?->sku);
            if ($variant) {
                $splitBox->slots()->create([
                    'variant_id' => $variant->id,
                    'split_role' => $slot->split_role,
                    'qty_per_box' => $slot->qty_per_box,
                    'sort_order' => $slot->sort_order,
                ]);
            }
        }
    }

    /**
     * PRD 13.5/22.6: preview tampilan user sebelum publish.
     */
    public function preview(Product $product): View
    {
        $product->load(['variants', 'tags', 'merchCategory', 'game', 'developer']);

        return view('frontend.product.show', [
            'game' => $product->game,
            'product' => $product,
            'variant' => null,
            'pricing' => $this->shipping->calculateItemShipping($product),
            'userState' => null,
            'kuotaViewUsed' => null,
            'kuotaViewTotal' => null,
            'related' => Product::where('is_active', true)
                ->where('game_id', $product->game_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit((int) setting('related_products_count', 6))
                ->with(['game', 'tags'])
                ->get(),
            'shipping' => $this->shipping,
        ]);
    }

    // ──────────────────────────────────────
    // BULK OPERATIONS
    // ──────────────────────────────────────

    /**
     * [FIX #1] Bulk action: aktifkan / nonaktifkan / hapus / salin.
     */
    public function bulk(BulkProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $products = Product::whereIn('id', $data['ids'])->get();

        foreach ($products as $product) {
            switch ($data['action']) {
                case 'hapus':
                    // [FIX #3] Shared delete with file cleanup
                    $this->deleteProductWithFiles($product, $request->user(), 'Bulk hapus permanen');
                    break;

                case 'salin':
                    // [FIX #2] Shared duplicate with file copy
                    $this->duplicateProduct($product, $request->user());
                    break;

                case 'aktifkan':
                    $product->update(['is_active' => true]);
                    AdminLog::record($request->user(), 'update', 'Product', $product->id,
                        ['is_active' => false], ['is_active' => true], 'Bulk aktifkan');
                    break;

                case 'nonaktifkan':
                    $product->update(['is_active' => false]);
                    AdminLog::record($request->user(), 'update', 'Product', $product->id,
                        ['is_active' => true], ['is_active' => false], 'Bulk nonaktifkan');
                    break;
            }
        }

        $verb = match ($data['action']) {
            'hapus' => 'dihapus permanen',
            'salin' => 'disalin',
            'aktifkan' => 'diaktifkan',
            'nonaktifkan' => 'dinonaktifkan',
        };

        return redirect()->route('office.products.index')
            ->with('status', count($data['ids'])." produk {$verb}.");
    }

    /**
     * [FIX #1] Bulk edit berat varian — support AJAX (fetch) & form POST.
     */
    public function bulkWeight(BulkWeightRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        if ($error = $this->splitBoxBulkError($product, $data['ids'], ['weight_gram' => $data['weight_gram']])) {
            return $this->bulkErrorResponse($request, $product, $error);
        }

        $updated = ProductVariant::whereIn('id', $data['ids'])
            ->where('product_id', $product->id)
            ->update(['weight_gram' => $data['weight_gram']]);

        AdminLog::record($request->user(), 'update', 'Product', $product->id, null, [
            'weight_gram' => $data['weight_gram'],
            'varian' => $data['ids'],
        ], "Bulk edit berat {$updated} varian");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'updated' => $updated,
                'message' => "{$updated} varian diperbarui.",
            ]);
        }

        return redirect()->route('office.products.edit', $product)->with('status', "{$updated} varian diperbarui.");
    }

    /**
     * Bulk edit harga RMB varian (Backlog EPIC-04-26) — pola sama dengan bulkWeight.
     */
    public function bulkPrice(BulkPriceRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        if ($error = $this->splitBoxBulkError($product, $data['ids'], ['price_rmb' => $data['price_rmb']])) {
            return $this->bulkErrorResponse($request, $product, $error);
        }

        $updated = ProductVariant::whereIn('id', $data['ids'])
            ->where('product_id', $product->id)
            ->update(['price_rmb' => $data['price_rmb']]);

        AdminLog::record($request->user(), 'update', 'Product', $product->id, null, [
            'price_rmb' => $data['price_rmb'],
            'varian' => $data['ids'],
        ], "Bulk edit harga RMB {$updated} varian");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'updated' => $updated, 'message' => "{$updated} varian diperbarui."]);
        }

        return redirect()->route('office.products.edit', $product)->with('status', "{$updated} varian diperbarui.");
    }

    // ──────────────────────────────────────
    // VARIANT CRUD
    // ──────────────────────────────────────

    /**
     * [FIX #1] Dedicated VariantFormRequest — bukan ProductFormRequest.
     */
    public function storeVariant(VariantFormRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'var', null, 'image');
        }
        unset($data['image']);

        if ($error = $this->splitBoxUniformError($product, $data)) {
            return back()->withErrors(['price_rmb' => $error])->withInput();
        }

        $data['sku'] = $data['sku'] ?? ProductVariant::generateSku($product);

        $variant = $product->variants()->create($data);
        $this->syncSplitBox($product);

        AdminLog::record($request->user(), 'create', 'ProductVariant', $variant->id, null, $data, 'Tambah varian');

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian dibuat.');
    }

    /**
     * [FIX #1] Dedicated VariantFormRequest — bukan ProductFormRequest.
     */
    public function updateVariant(VariantFormRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $data = $request->validated();

        $old = $variant->only(['name', 'sku', 'price_rmb', 'weight_gram', 'availability']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'var', $variant->image_path, 'image');
        }
        unset($data['image']);

        if ($error = $this->splitBoxUniformError($product, $data, $variant)) {
            return back()->withErrors(['price_rmb' => $error])->withInput();
        }

        $data['sku'] = $data['sku'] ?? $variant->sku;

        $variant->update($data);

        AdminLog::record($request->user(), 'update', 'ProductVariant', $variant->id, $old, $data, 'Edit varian');

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian diperbarui.');
    }

    /**
     * PRD 49.5: seluruh karakter Split Box memakai price_rmb & weight_gram yang sama.
     *
     * @param  array<string, mixed>  $data
     */
    private function splitBoxUniformError(Product $product, array $data, ?ProductVariant $ignore = null): ?string
    {
        if (! $product->is_split_box) {
            return null;
        }

        $others = $product->variants()->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))->get();

        foreach ($others as $other) {
            if ((float) $other->price_rmb !== (float) ($data['price_rmb'] ?? $other->price_rmb)
                || (int) $other->weight_gram !== (int) ($data['weight_gram'] ?? $other->weight_gram)) {
                return 'Seluruh karakter Split Box harus memakai price_rmb dan weight_gram yang sama.';
            }
        }

        return null;
    }

    /**
     * PRD 49.5: pada update produk Split Box, harga/berat efektif (varian, fallback produk) semua varian harus sama.
     *
     * @param  array<string, mixed>  $data
     */
    private function splitBoxProductUniformError(Product $product, array $data): ?string
    {
        if (! ($data['is_split_box'] ?? $product->is_split_box)) {
            return null;
        }

        $price = $data['price_rmb'] ?? $product->price_rmb;
        $weight = $data['weight_gram'] ?? $product->weight_gram;
        $variants = $product->variants()->get();

        $prices = $variants->map(fn ($v) => (float) ($v->price_rmb ?? $price))->unique();
        $weights = $variants->map(fn ($v) => (int) ($v->weight_gram ?? $weight))->unique();

        if ($prices->count() > 1 || $weights->count() > 1) {
            return 'Seluruh karakter Split Box harus memakai price_rmb dan weight_gram yang sama.';
        }

        return null;
    }

    /**
     * PRD 49.5: bulk edit pada produk Split Box harus mencakup SEMUA varian agar harga/berat tetap seragam.
     *
     * @param  array<int, int>  $ids
     * @param  array<string, mixed>  $changes
     */
    private function splitBoxBulkError(Product $product, array $ids, array $changes): ?string
    {
        if (! $product->is_split_box) {
            return null;
        }

        $allIds = $product->variants()->pluck('id')->all();
        if (array_diff($allIds, array_map('intval', $ids)) !== []) {
            return 'Produk Split Box: harga dan berat harus sama untuk seluruh karakter, pilih semua varian.';
        }

        return null;
    }

    private function bulkErrorResponse(Request $request, Product $product, string $error): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return redirect()->route('office.products.edit', $product)->withErrors(['bulk' => $error]);
    }

    public function destroyVariant(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        AdminLog::record($request->user(), 'delete', 'ProductVariant', $variant->id, $variant->only(['name', 'sku']), null, 'Hapus varian');

        // Auto-delete foto varian (PRD 13.6).
        if ($variant->image_path && str_starts_with($variant->image_path, 'products/')) {
            @unlink(storage_path('app/private/'.$variant->image_path));
            @unlink(storage_path('app/private/originals/'.$variant->image_path));
            $this->optimizer->deleteVariants($variant->image_path);
        }

        $variant->delete();

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian dihapus.');
    }

    // ──────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────

    /**
     * [FIX #3] Shared: hapus produk + semua file terkait.
     */
    private function deleteProductWithFiles(Product $product, $user, string $logMessage): void
    {
        $snapshotCount = OrderItem::where('product_id', $product->id)->count();

        AdminLog::record($user, 'delete', 'Product', $product->id,
            $product->only(['name', 'sku']), null,
            "{$logMessage} ({$snapshotCount} order_item terdampak)");

        // Kumpulkan semua path file sebelum delete
        $imagePaths = array_filter([$product->image_path, $product->description_image_path]);
        foreach ($product->variants as $variant) {
            if ($variant->image_path) {
                $imagePaths[] = $variant->image_path;
            }
        }

        $product->delete();

        // PRD 13.5: file gambar produk asli ikut dihapus; snapshot order TIDAK.
        foreach (array_filter($imagePaths) as $path) {
            if (str_starts_with($path, 'products/')) {
                @unlink(storage_path('app/private/'.$path));
                @unlink(storage_path('app/private/originals/'.$path));
                $this->optimizer->deleteVariants($path);
            }
        }
    }

    /**
     * [FIX #2] Copy file gambar ke path baru (master + originals).
     */
    private function copyImageFile(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $newPath = $dir.'/'.Str::random(20).'.'.$ext;

        // Copy versi ter-watermark (app/private/)
        $srcMain = storage_path('app/private/'.$path);
        if (file_exists($srcMain)) {
            @copy($srcMain, storage_path('app/private/'.$newPath));
        }

        // Copy master asli (app/private/originals/)
        $srcOrig = storage_path('app/private/originals/'.$path);
        if (file_exists($srcOrig)) {
            @copy($srcOrig, storage_path('app/private/originals/'.$newPath));
        }

        $this->optimizer->copyVariants($path, $newPath);

        return $newPath;
    }

    /**
     * PRD 13.6: upload gambar → watermark + master asli; handle remove flag.
     */
    private function processUploads(Request $request, array $data, ?Product $product): array
    {
        // ── Foto utama ──
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'img', $product?->image_path, 'image');
        }

        // ── Gambar deskripsi ──
        if ($request->hasFile('description_image')) {
            $data['description_image_path'] = $this->storeImage(
                $request->file('description_image'), 'desc', $product?->description_image_path, 'description_image'
            );
        } elseif ($request->boolean('remove_description_image') && $product?->description_image_path) {
            // User klik "Hapus" tanpa upload baru → hapus file existing.
            if (str_starts_with($product->description_image_path, 'products/')) {
                @unlink(storage_path('app/private/'.$product->description_image_path));
                @unlink(storage_path('app/private/originals/'.$product->description_image_path));
                $this->optimizer->deleteVariants($product->description_image_path);
            }
            $data['description_image_path'] = null;
        }

        // Jangan bawa objek file / flag ke create/diff/admin_logs.
        unset($data['image'], $data['description_image'], $data['remove_description_image']);

        return $data;
    }

    /**
     * Simpan via ImageWatermarkService (master → private/originals, versi tayang ter-watermark).
     */
    private function storeImage(UploadedFile $file, string $prefix, ?string $oldPath, string $field = 'image'): string
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                $field => 'Upload gagal: '.UploadError::describe($file),
            ]);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        try {
            $target = $this->watermark->watermark(
                $file->getRealPath(),
                'products/'.sprintf('%s-%s.%s', $prefix, Str::random(20), $ext)
            );
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                $field => 'Upload gagal: '.UploadError::describeProcessing($e),
            ]);
        }

        // PRD 13.6: auto-delete foto lama saat override (snapshot order tidak tersentuh).
        if ($oldPath && $oldPath !== $target && str_starts_with($oldPath, 'products/')) {
            @unlink(storage_path('app/private/'.$oldPath));
            @unlink(storage_path('app/private/originals/'.$oldPath));
            $this->optimizer->deleteVariants($oldPath);
        }

        try {
            $this->optimizer->generate(storage_path('app/private/'.$target));
        } catch (\Throwable $e) {
            report($e);
        }

        return $target;
    }
}
