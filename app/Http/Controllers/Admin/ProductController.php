<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Product;
use App\Models\Store;
use App\Services\Product\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly StoreService $storeService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);

        $products = Product::query()
            ->with(['store', 'games'])
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
        return view('admin.products.form', [
            'product' => new Product,
            'stores' => Store::query()->orderBy('name')->get(),
            'games' => Game::active()->with('defaultStore')->orderBy('name')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $product = DB::transaction(function () use ($validated): Product {
            $product = Product::create(collect($validated)->except('game_ids')->all());
            $product->games()->sync($validated['game_ids']);

            return $product;
        });

        return redirect()->route('office.products.edit', $product)->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('games'),
            'stores' => Store::query()->orderBy('name')->get(),
            'games' => Game::query()->with('defaultStore')->orderBy('name')->get(),
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        DB::transaction(function () use ($validated, $product): void {
            $product->update(collect($validated)->except('game_ids')->all());
            $product->games()->sync($validated['game_ids']);
        });

        return redirect()->route('office.products.edit', $product)->with('success', 'Perubahan produk berhasil disimpan.');
    }

    /**
     * @return array{name: string, slug: string, sku: string, store_id: int|null, description: string|null, type: string, availability: string, is_active: bool, price_rmb: numeric-string, game_ids: list<int>}
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
            'is_active' => ['required', 'boolean'],
            'price_rmb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'game_ids' => ['required', 'array', 'min:1'],
            'game_ids.*' => ['integer', 'distinct', 'exists:games,id'],
        ]);
    }
}
