<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductFormRequest;
use App\Models\AdminLog;
use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Tag;
use App\Services\Image\ImageWatermarkService;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ImageWatermarkService $watermark,
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
            'products' => $query->orderByDesc('id')->paginate(30),
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
            'categories' => MerchCategory::orderBy('priority')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'developers' => Developer::orderBy('name')->get(),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('variants', 'tags', 'developer'),
            'games' => Game::orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
            'categories' => MerchCategory::orderBy('priority')->get(),
            'tags' => Tag::orderBy('name')->get(),
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
            ->filter(fn ($v) => filled(data_get($v, 'name')) || filled(data_get($v, 'sku')));
        if ($variantRows->isNotEmpty()) {
            $data['type'] = 'variant';
        }

        $product = Product::create($data + ['sold_count' => 0]);

        if (! $product->merch_category_id) {
            $product->update(['merch_category_id' => MerchCategory::matchFor($product->name, $product->slug)?->id]);
        }

        if ($request->filled('tags')) {
            $product->tags()->sync($request->input('tags'));
        }

        // [FIX #6] Create inline variants
        $this->processInlineVariants($request, $product);

        AdminLog::record($request->user(), 'create', 'Product', $product->id, null, $product->only(array_keys($data)), 'Tambah produk');

        return redirect()->route('office.products.edit', $product)->with('status', 'Produk dibuat.');
    }

    /**
     * [FIX #6] Simpan inline variants setelah product dibuat.
     */
    private function processInlineVariants(Request $request, Product $product): void
    {
        $rows = collect($request->input('variants', []));

        foreach ($rows as $i => $v) {
            if (blank(data_get($v, 'name')) && blank(data_get($v, 'sku'))) {
                continue;
            }

            $v['availability'] = $v['availability'] ?? 'tersedia';

            if ($request->hasFile("variants.{$i}.image")) {
                $v['image_path'] = $this->storeImage($request->file("variants.{$i}.image"), 'var', null);
            }
            unset($v['image']);

            $product->variants()->create($v);
        }
    }

    public function update(ProductFormRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['variants'], $data['tags']);
        $data['is_active'] = $request->boolean('is_active');
        $data = $this->processUploads($request, $data, $product);
        [$old, $new] = AdminLog::diff($product, array_keys($data), $data);

        $product->update($data);
        $product->tags()->sync($request->input('tags', []));

        // PRD 14: auto-group kategori merch by keyword (override manual menang).
        if (! $product->merch_category_id) {
            $product->update(['merch_category_id' => MerchCategory::matchFor($product->name, $product->slug)?->id]);
        }

        AdminLog::record($request->user(), 'update', 'Product', $product->id, $old, $new, 'Ubah produk');

        return redirect()->route('office.products.edit', $product)->with('status', 'Produk diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        // Hard delete (PRD 13.5): snapshot order tetap utuh, order_items.product_id → NULL.
        $snapshotCount = OrderItem::where('product_id', $product->id)->count();

        AdminLog::record($request->user(), 'delete', 'Product', $product->id, $product->only(['name', 'sku']), null, "Hapus permanen ({$snapshotCount} order_item terdampak)");

        $imagePaths = array_filter([$product->image_path, $product->description_image_path]);
        foreach ($product->variants as $variant) {
            $imagePaths[] = $variant->image_path;
        }

        $product->delete();

        // PRD 13.5: file gambar produk asli ikut dihapus; snapshot order TIDAK.
        foreach (array_filter($imagePaths) as $path) {
            if (str_starts_with($path, 'products/')) {
                @unlink(storage_path('app/private/'.$path));
                @unlink(storage_path('app/private/originals/'.$path));
            }
        }

        return redirect()->route('office.products.index')->with('status', 'Produk dihapus permanen.');
    }

    // ──────────────────────────────────────
    // DUPLICATE / PREVIEW
    // ──────────────────────────────────────

    /**
     * PRD 13.5/22.6: duplikat produk (field + varian) sebagai draft.
     */
    public function duplicate(Request $request, Product $product): RedirectResponse
    {
        $copy = $product->replicate(['sold_count']);
        $copy->sku = $product->sku.'-COPY-'.Str::random(4);
        $copy->slug = $product->slug.'-copy-'.Str::random(4);
        $copy->is_active = false;
        $copy->save();

        foreach ($product->variants as $variant) {
            $variantCopy = $variant->replicate();
            $variantCopy->sku = $variant->sku.'-COPY-'.Str::random(4);
            $variantCopy->product_id = $copy->id;
            $variantCopy->save();
        }

        AdminLog::record($request->user(), 'create', 'Product', $copy->id, null, [
            'duplicate_of' => $product->id,
            'sku' => $copy->sku,
        ], 'Duplikat produk (draft)');

        return redirect()->route('office.products.edit', $copy)->with('status', 'Produk diduplikat sebagai draft.');
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

    public function bulk(ProductFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $products = Product::whereIn('id', $data['ids'])->get();

        foreach ($products as $product) {
            if ($data['action'] === 'hapus') {
                AdminLog::record($request->user(), 'delete', 'Product', $product->id, $product->only(['name', 'sku']), null, 'Bulk hapus permanen');
                $product->delete();
            } else {
                $active = $data['action'] === 'aktifkan';
                $product->update(['is_active' => $active]);
                AdminLog::record($request->user(), 'update', 'Product', $product->id, ['is_active' => ! $active], ['is_active' => $active], 'Bulk '.($active ? 'aktifkan' : 'nonaktifkan'));
            }
        }

        return redirect()->route('office.products.index')->with('status', count($data['ids']).' produk diproses.');
    }

    /**
     * Bulk edit berat varian — support AJAX (fetch) & form POST.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function bulkWeight(ProductFormRequest $request, Product $product)
    {
        $data = $request->validated();

        $updated = ProductVariant::whereIn('id', $data['ids'])
            ->where('product_id', $product->id)
            ->update(['weight_gram' => $data['weight_gram']]);

        AdminLog::record($request->user(), 'update', 'Product', $product->id, null, ['weight_gram' => $data['weight_gram'], 'varian' => $data['ids']], "Bulk edit berat {$updated} varian");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'updated' => $updated,
                'message' => "{$updated} varian diperbarui.",
            ]);
        }

        return redirect()->route('office.products.edit', $product)->with('status', "{$updated} varian diperbarui.");
    }

    // ──────────────────────────────────────
    // VARIANT CRUD
    // ──────────────────────────────────────

    public function storeVariant(ProductFormRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'var', null);
        }
        unset($data['image']);

        $variant = $product->variants()->create($data);

        AdminLog::record($request->user(), 'create', 'ProductVariant', $variant->id, null, $data, 'Tambah varian');

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian dibuat.');
    }

    public function updateVariant(ProductFormRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $data = $request->validated();

        $old = $variant->only(['name', 'sku', 'price_rmb', 'weight_gram', 'availability']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'var', $variant->image_path);
        }
        unset($data['image']);

        $variant->update($data);

        AdminLog::record($request->user(), 'update', 'ProductVariant', $variant->id, $old, $data, 'Edit varian');

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian diperbarui.');
    }

    public function destroyVariant(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        AdminLog::record($request->user(), 'delete', 'ProductVariant', $variant->id, $variant->only(['name', 'sku']), null, 'Hapus varian');

        // Auto-delete foto varian (PRD 13.6).
        if ($variant->image_path && str_starts_with($variant->image_path, 'products/')) {
            @unlink(storage_path('app/private/'.$variant->image_path));
            @unlink(storage_path('app/private/originals/'.$variant->image_path));
        }

        $variant->delete();

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian dihapus.');
    }

    // ──────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────

    /**
     * PRD 13.6: upload gambar → watermark + master asli; handle remove flag.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function processUploads(Request $request, array $data, ?Product $product): array
    {
        // ── Foto utama ──
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'img', $product?->image_path);
        }

        // ── Gambar deskripsi ──
        if ($request->hasFile('description_image')) {
            // Upload baru → replace yang lama.
            $data['description_image_path'] = $this->storeImage($request->file('description_image'), 'desc', $product?->description_image_path);
        } elseif ($request->boolean('remove_description_image') && $product?->description_image_path) {
            // User klik "Hapus" tanpa upload baru → hapus file existing.
            if (str_starts_with($product->description_image_path, 'products/')) {
                @unlink(storage_path('app/private/'.$product->description_image_path));
                @unlink(storage_path('app/private/originals/'.$product->description_image_path));
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
    private function storeImage(UploadedFile $file, string $prefix, ?string $oldPath): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $target = $this->watermark->watermark(
            $file->getRealPath(),
            'products/'.sprintf('%s-%s.%s', $prefix, Str::random(20), $ext)
        );

        // PRD 13.6: auto-delete foto lama saat override (snapshot order tidak tersentuh).
        if ($oldPath && $oldPath !== $target && str_starts_with($oldPath, 'products/')) {
            @unlink(storage_path('app/private/'.$oldPath));
            @unlink(storage_path('app/private/originals/'.$oldPath));
        }

        return $target;
    }
}
