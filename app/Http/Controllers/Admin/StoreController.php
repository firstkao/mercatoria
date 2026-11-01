<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormRequest;
use App\Models\AdminLog;
use App\Models\Store;
use App\Services\Product\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(private StoreService $stores) {}

    public function index(): View
    {
        return view('admin.stores.index', [
            'stores' => Store::withCount(['products', 'games'])->orderBy('name')->get(),
        ]);
    }

    public function store(StoreFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $store = $this->stores->create($data);
        AdminLog::record($request->user(), 'create', 'Store', $store->id, null, $data, 'Tambah store');

        return redirect()->route('office.stores.index')->with('status', 'Store dibuat.');
    }

    public function update(StoreFormRequest $request, Store $store): RedirectResponse
    {
        $data = $request->validated();

        [$old, $new] = AdminLog::diff($store, ['name', 'min_belanja_rmb', 'ongkir_rmb'], $data);
        $this->stores->update($store, $data);
        AdminLog::record($request->user(), 'update', 'Store', $store->id, $old, $new, 'Ubah store');

        return redirect()->route('office.stores.index')->with('status', 'Store diperbarui.');
    }

    public function destroy(StoreFormRequest $request, Store $store): RedirectResponse
    {
        $usage = $this->stores->getUsageCount($store);
        AdminLog::record($request->user(), 'delete', 'Store', $store->id, $store->only(['name']), null, "Hapus store (produk: {$usage['products']}, game: {$usage['games']})");
        $this->stores->delete($store);

        return redirect()->route('office.stores.index')->with('status', 'Store dihapus. Referensi produk/game jadi NULL.');
    }

    /**
     * Bulk store: item dengan id = update, tanpa id = create (validasi per item).
     */
    public function bulk(StoreFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $diperbarui = 0;
        $dibuat = 0;

        foreach ($data['stores'] as $item) {
            if (! empty($item['id'])) {
                $store = Store::findOrFail($item['id']);
                [$old, $new] = AdminLog::diff($store, ['name', 'min_belanja_rmb', 'ongkir_rmb'], $item);
                $this->stores->update($store, $item);
                AdminLog::record($request->user(), 'update', 'Store', $store->id, $old, $new, 'Bulk ubah store');
                $diperbarui++;
            } else {
                $store = $this->stores->create($item);
                AdminLog::record($request->user(), 'create', 'Store', $store->id, null, $item, 'Bulk tambah store');
                $dibuat++;
            }
        }

        return redirect()->route('office.stores.index')
            ->with('status', "{$diperbarui} store diperbarui, {$dibuat} store dibuat.");
    }

    /**
     * Bulk hapus store — ditolak bila ada store yang masih punya produk/game.
     */
    public function bulkDelete(StoreFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $stores = Store::withCount(['products', 'games'])->whereIn('id', $data['ids'])->get();
        $terpakai = $stores->filter(fn (Store $store) => $store->products_count > 0 || $store->games_count > 0);

        if ($terpakai->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ids' => 'Store '.implode(', ', $terpakai->pluck('name')->all()).' masih punya produk/game.',
            ]);
        }

        foreach ($stores as $store) {
            AdminLog::record($request->user(), 'delete', 'Store', $store->id, $store->only(['name']), null, 'Bulk hapus store');
            $this->stores->delete($store);
        }

        return redirect()->route('office.stores.index')->with('status', $stores->count().' store dihapus.');
    }
}
