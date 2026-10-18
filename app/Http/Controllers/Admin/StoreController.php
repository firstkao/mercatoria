<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Product\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(private readonly StoreService $storeService) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $stores = Store::query()
            ->withCount('games')
            ->when($validated['q'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stores->getCollection()->each(function (Store $store): void {
            $store->products_count = $this->storeService->getUsageCount($store)['products'];
        });

        return view('admin.stores.index', [
            'stores' => $stores,
            'search' => $validated['q'] ?? '',
        ]);
    }

    public function create(): View
    {
        return view('admin.stores.form', [
            'store' => new Store,
            'usage' => ['games' => 0, 'products' => 0],
            'games' => collect(),
            'isEditing' => false,
            'rateRmbIdr' => (float) setting('rate_rmb_idr', 2200),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $this->storeService->create($this->validateStore($request));

        return redirect()->route('office.stores.edit', $store)
            ->with('success', 'Store berhasil dibuat.');
    }

    public function edit(Store $store): View
    {
        return view('admin.stores.form', [
            'store' => $store,
            'usage' => $this->storeService->getUsageCount($store),
            'games' => $store->games()->orderBy('name')->get(['games.id', 'games.name']),
            'isEditing' => true,
            'rateRmbIdr' => (float) setting('rate_rmb_idr', 2200),
        ]);
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $this->storeService->update($store, $this->validateStore($request, $store));

        return redirect()->route('office.stores.edit', $store)
            ->with('success', 'Perubahan store berhasil disimpan.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $this->storeService->delete($store);

        return redirect()->route('office.stores.index')
            ->with('success', 'Store berhasil dihapus. Hubungan game dan produk yang memakai store ini telah dihapus.');
    }

    /**
     * @return array{name: string, min_belanja_rmb: string, ongkir_rmb: string}
     */
    private function validateStore(Request $request, ?Store $store = null): array
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('stores', 'name')->ignore($store)],
            'min_belanja_rmb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'ongkir_rmb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ], [
            'name.required' => 'Nama store wajib diisi.',
            'name.unique' => 'Nama store sudah digunakan.',
            'min_belanja_rmb.required' => 'Minimum belanja wajib diisi.',
            'min_belanja_rmb.min' => 'Minimum belanja tidak boleh kurang dari 0.',
            'ongkir_rmb.required' => 'Ongkir wajib diisi.',
            'ongkir_rmb.min' => 'Ongkir tidak boleh kurang dari 0.',
        ]);
    }
}
