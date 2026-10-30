<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Game;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('admin.games.index', [
            'games' => Game::with('defaultStore')->orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'default_store_id' => ['nullable', 'exists:stores,id'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['logo_path'] = $request->hasFile('logo') ? $request->file('logo')->store('games') : null;
        $data['is_active'] = $request->boolean('is_active', true);
        unset($data['logo']);

        $game = Game::create($data);
        AdminLog::record($request->user(), 'create', 'Game', $game->id, null, $data, 'Tambah game');

        return redirect()->route('office.games.index')->with('status', 'Game dibuat.');
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:games,slug,'.$game->id],
            'default_store_id' => ['nullable', 'exists:stores,id'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Slug manual bila diisi; dikosongkan -> auto-generate dari nama; tidak dikirim -> tetap.
        if ($request->has('slug')) {
            $data['slug'] = filled($data['slug'] ?? null)
                ? $data['slug']
                : $this->uniqueSlug($data['name'], $game->id);
        }

        if ($request->hasFile('logo')) {
            if ($game->logo_path) {
                @unlink(storage_path('app/private/'.$game->logo_path));
            }
            $data['logo_path'] = $request->file('logo')->store('games');
        }
        unset($data['logo']);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($game, ['name', 'default_store_id', 'is_active'], $data);
        $game->update($data);
        AdminLog::record($request->user(), 'update', 'Game', $game->id, $old, $new, 'Ubah game');

        return redirect()->route('office.games.index')->with('status', 'Game diperbarui.');
    }

    public function destroy(Request $request, Game $game): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Game', $game->id, $game->only(['name', 'slug']), null, 'Hapus game');
        if ($game->logo_path) {
            @unlink(storage_path('app/private/'.$game->logo_path));
        }
        $game->delete();

        return redirect()->route('office.games.index')->with('status', 'Game dihapus.');
    }

    /**
     * Bulk game: item dengan id = update, tanpa id = create (slug auto-generate).
     */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'games' => ['required', 'array', 'min:1'],
            'games.*.id' => ['nullable', 'integer', 'exists:games,id'],
            'games.*.name' => ['required', 'string', 'max:100'],
            'games.*.default_store_id' => ['nullable', 'exists:stores,id'],
            'games.*.is_active' => ['nullable', 'boolean'],
        ]);

        $diperbarui = 0;
        $dibuat = 0;

        foreach ($data['games'] as $item) {
            if (! empty($item['id'])) {
                $game = Game::findOrFail($item['id']);
                [$old, $new] = AdminLog::diff($game, ['name', 'default_store_id', 'is_active'], $item);
                $game->update([
                    'name' => $item['name'],
                    'default_store_id' => $item['default_store_id'] ?? null,
                    'is_active' => (bool) ($item['is_active'] ?? true),
                ]);
                AdminLog::record($request->user(), 'update', 'Game', $game->id, $old, $new, 'Bulk ubah game');
                $diperbarui++;
            } else {
                $game = Game::create([
                    'name' => $item['name'],
                    'slug' => $this->uniqueSlug($item['name']),
                    'default_store_id' => $item['default_store_id'] ?? null,
                    'is_active' => (bool) ($item['is_active'] ?? true),
                ]);
                AdminLog::record($request->user(), 'create', 'Game', $game->id, null, $game->only(['name', 'slug']), 'Bulk tambah game');
                $dibuat++;
            }
        }

        return redirect()->route('office.games.index')
            ->with('status', "{$diperbarui} game diperbarui, {$dibuat} game dibuat.");
    }

    /**
     * Bulk hapus game — ditolak bila ada game yang masih punya produk.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:games,id'],
        ]);

        $games = Game::withCount('products')->whereIn('id', $data['ids'])->get();
        $terpakai = $games->filter(fn (Game $game) => $game->products_count > 0);

        if ($terpakai->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ids' => 'Game '.implode(', ', $terpakai->pluck('name')->all()).' masih punya produk.',
            ]);
        }

        foreach ($games as $game) {
            AdminLog::record($request->user(), 'delete', 'Game', $game->id, $game->only(['name', 'slug']), null, 'Bulk hapus game');
            if ($game->logo_path) {
                @unlink(storage_path('app/private/'.$game->logo_path));
            }
            $game->delete();
        }

        return redirect()->route('office.games.index')->with('status', $games->count().' game dihapus.');
    }

    /**
     * Generate slug bersih dari nama — ala WordPress.
     * Contoh: "Honkai: Star Rail" → "honkai-star-rail"
     * Duplikat: "honkai-star-rail" sudah ada → "honkai-star-rail-2"
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 2;

        while (Game::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
