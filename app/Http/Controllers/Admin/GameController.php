<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Game;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'slug' => ['nullable', 'string', 'max:120', 'unique:games,slug'],
            'default_store_id' => ['nullable', 'exists:stores,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']).'-'.Str::random(4);
        $data['is_active'] = $request->boolean('is_active', true);

        $game = Game::create($data);
        AdminLog::record($request->user(), 'create', 'Game', $game->id, null, $data, 'Tambah game');

        return redirect()->route('office.games.index')->with('status', 'Game dibuat.');
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'default_store_id' => ['nullable', 'exists:stores,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($game, ['name', 'default_store_id', 'is_active'], $data);
        $game->update($data);
        AdminLog::record($request->user(), 'update', 'Game', $game->id, $old, $new, 'Ubah game');

        return redirect()->route('office.games.index')->with('status', 'Game diperbarui.');
    }

    public function destroy(Request $request, Game $game): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Game', $game->id, $game->only(['name', 'slug']), null, 'Hapus game');
        $game->delete();

        return redirect()->route('office.games.index')->with('status', 'Game dihapus.');
    }
}
