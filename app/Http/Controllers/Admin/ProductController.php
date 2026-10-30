<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Tag;
use App\Services\Image\ImageWatermarkService;
use App\Services\Order\ShippingCalculatorService;
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

    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with(['game', 'store', 'merchCategory'])->orderByDesc('id')->paginate(30),
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']).'-'.Str::random(4);
        $data = $this->processUploads($request, $data, null);

        $product = Product::create($data + ['sold_count' => 0]);

        // PRD 14: auto-group kategori merch by keyword (override manual menang).
        if (! $product->merch_category_id) {
            $product->update(['merch_category_id' => MerchCategory::matchFor($product->name, $product->slug)?->id]);
        }

        if ($request->filled('tags')) {
            $product->tags()->sync($request->input('tags'));
        }

        AdminLog::record($request->user(), 'create', 'Product', $product->id, null, $product->only(array_keys($data)), 'Tambah produk');

        return redirect()->route('office.products.edit', $product)->with('status', 'Produk dibuat.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('variants', 'tags'),
            'games' => Game::orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
            'categories' => MerchCategory::orderBy('priority')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);
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
        $product->load(['variants', 'tags', 'merchCategory', 'game']);

        return view('frontend.product.show', [
            'game' => $product->game,
            'product' => $product,
            'variant' => null,
            'pricing' => $this->shipping->calculateItemShipping($product),
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

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:aktifkan,nonaktifkan,hapus'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

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

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:64', 'unique:product_variants,sku'],
            'price_rmb' => ['nullable', 'numeric', 'min:0'],
            'weight_gram' => ['nullable', 'integer', 'min:0'],
            'availability' => ['required', 'in:tersedia,kosong'],
            // PRD 13.6: foto varian 1 per varian, max 1MB.
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'var', null);
        }
        unset($data['image']);

        $variant = $product->variants()->create($data);

        AdminLog::record($request->user(), 'create', 'ProductVariant', $variant->id, null, $data, 'Tambah varian');

        return redirect()->route('office.products.edit', $product)->with('status', 'Varian dibuat.');
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkWeight(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'weight_gram' => ['required', 'integer', 'min:0'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:product_variants,id'],
        ]);

        $updated = ProductVariant::whereIn('id', $data['ids'])
            ->where('product_id', $product->id)
            ->update(['weight_gram' => $data['weight_gram']]);

        AdminLog::record($request->user(), 'update', 'Product', $product->id, null, ['weight_gram' => $data['weight_gram'], 'varian' => $data['ids']], "Bulk edit berat {$updated} varian");

        return redirect()->route('office.products.edit', $product)->with('status', "{$updated} varian diperbarui.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'merch_category_id' => ['nullable', 'exists:merch_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:64', $product ? 'unique:products,sku,'.$product->id : 'unique:products,sku'],
            'slug' => ['nullable', 'string', 'max:170', $product ? 'unique:products,slug,'.$product->id : 'unique:products,slug'],
            'price_rmb' => ['required', 'numeric', 'min:0'],
            'weight_gram' => ['nullable', 'integer', 'min:0'],
            'type' => ['required', 'in:single,variant'],
            'description' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_start' => ['nullable', 'date'],
            'discount_end' => ['nullable', 'date'],
            'availability' => ['required', 'in:tersedia,kosong'],
            'is_active' => ['nullable', 'boolean'],
            // PRD 13.6: 1 file, max 1MB, jpg/jpeg/png. Field path manual tetap kompatibel.
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'description_image_path' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $data['slug'] ?? '';

        return $data;
    }

    /**
     * PRD 13.6: upload gambar → watermark + master asli; path manual lama tetap berlaku.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function processUploads(Request $request, array $data, ?Product $product): array
    {
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'img', $product?->image_path);
        }

        if ($request->hasFile('description_image')) {
            $data['description_image_path'] = $this->storeImage($request->file('description_image'), 'desc', $product?->description_image_path);
        }

        // Jangan bawa objek file ke create/diff/admin_logs.
        unset($data['image'], $data['description_image']);

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
