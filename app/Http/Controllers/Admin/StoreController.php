<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Store;
use App\Services\Product\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_belanja_rmb' => ['required', 'numeric', 'min:0'],
            'ongkir_rmb' => ['required', 'numeric', 'min:0'],
        ]);

        $store = $this->stores->create($data);
        AdminLog::record($request->user(), 'create', 'Store', $store->id, null, $data, 'Tambah store');

        return redirect()->route('office.stores.index')->with('status', 'Store dibuat.');
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_belanja_rmb' => ['required', 'numeric', 'min:0'],
            'ongkir_rmb' => ['required', 'numeric', 'min:0'],
        ]);

        [$old, $new] = AdminLog::diff($store, ['name', 'min_belanja_rmb', 'ongkir_rmb'], $data);
        $this->stores->update($store, $data);
        AdminLog::record($request->user(), 'update', 'Store', $store->id, $old, $new, 'Ubah store');

        return redirect()->route('office.stores.index')->with('status', 'Store diperbarui.');
    }

    public function destroy(Request $request, Store $store): RedirectResponse
    {
        $usage = $this->stores->getUsageCount($store);
        AdminLog::record($request->user(), 'delete', 'Store', $store->id, $store->only(['name']), null, "Hapus store (produk: {$usage['products']}, game: {$usage['games']})");
        $this->stores->delete($store);

        return redirect()->route('office.stores.index')->with('status', 'Store dihapus. Referensi produk/game jadi NULL.');
    }
}
