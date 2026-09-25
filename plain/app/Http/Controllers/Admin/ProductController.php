<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SaveProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\AdminLog;
use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Models\ShippingTier;
use App\Support\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = in_array($request->query('status'), ['tayang', 'draf', 'habis'], true) ? $request->query('status') : null;
        $gameId = $request->integer('game') ?: null;
        $developerId = $request->integer('developer') ?: null;
        $tag = array_key_exists((string) $request->query('tag'), Product::TAGS) ? $request->query('tag') : null;

        $products = Product::query()
            ->with(['images', 'variants', 'shippingTier', 'game', 'developer'])
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('sku', 'like', '%'.$search.'%')
                ->orWhereHas('variants', fn ($variants) => $variants->where('sku', 'like', '%'.$search.'%'))))
            ->when($gameId, fn ($query) => $query->where('game_id', $gameId))
            ->when($developerId, fn ($query) => $query->where('developer_id', $developerId))
            ->when($tag, fn ($query) => $query->where('tag', $tag))
            ->when($status === 'tayang', fn ($query) => $query->where('is_published', true))
            ->when($status === 'draf', fn ($query) => $query->where('is_published', false))
            ->when($status === 'habis', fn ($query) => $query->whereDoesntHave('variants', fn ($variants) => $variants->where('status', 'available')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'games' => Game::query()->orderBy('name')->get(['id', 'name']),
            'developers' => Developer::query()->orderBy('name')->get(['id', 'name']),
            'filters' => ['q' => $search, 'status' => $status, 'game' => $gameId, 'developer' => $developerId, 'tag' => $tag],
            'calculator' => PriceCalculator::fromSettings(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product(['is_published' => true]));
    }

    public function store(ProductRequest $request, SaveProduct $saveProduct): RedirectResponse
    {
        $product = $saveProduct->handle(new Product, $request->validated(), $request->file('images', []), $this->variantImages($request));
        AdminLog::record('create_product', $product, ['name' => $product->name]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Produk disimpan.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load(['images', 'variants']));
    }

    public function update(ProductRequest $request, Product $product, SaveProduct $saveProduct): RedirectResponse
    {
        $saveProduct->handle($product, $request->validated(), $request->file('images', []), $this->variantImages($request));
        AdminLog::record('update_product', $product, ['name' => $product->name]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $files = [
            ...$product->images()->pluck('image_path')->all(),
            ...$product->variants()->whereNotNull('image_path')->pluck('image_path')->all(),
        ];

        AdminLog::record('delete_product', $product, ['name' => $product->name]);
        $product->delete();
        Storage::disk('public')->delete($files);

        return redirect()->route('admin.products.index')->with('status', 'Produk dihapus.');
    }

    private function form(Product $product): View
    {
        $calculator = PriceCalculator::fromSettings();

        return view('admin.products.form', [
            'product' => $product,
            'games' => Game::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'developers' => Developer::query()->orderBy('name')->get(['id', 'name']),
            'tiers' => ShippingTier::query()->orderByDesc('code')->get(),
            'pricing' => $calculator->toArray(),
        ]);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function variantImages(Request $request): array
    {
        return collect((array) $request->file('variants', []))
            ->map(fn ($files) => $files['image'] ?? null)
            ->all();
    }
}