<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Developer;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GameController extends Controller
{
    public function index(): View
    {
        return view('admin.games.index', [
            'games' => Game::query()->with('developer')->withCount('products')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.games.form', [
            'game' => new Game(['sort_order' => 0]),
            'developers' => Developer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $game = Game::create($data);
        AdminLog::record('create_game', $game, ['name' => $game->name]);

        return redirect()->route('admin.games.index')->with('status', 'Game ditambahkan.');
    }

    public function edit(Game $game): View
    {
        return view('admin.games.form', [
            'game' => $game,
            'developers' => Developer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($game->image_path) {
                Storage::disk('public')->delete($game->image_path);
            }
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $game->update($data);
        AdminLog::record('update_game', $game, ['name' => $game->name]);

        return redirect()->route('admin.games.index')->with('status', 'Game diperbarui.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        if ($game->products()->exists()) {
            return back()->withErrors(['game' => 'Game ini masih dipakai produk, tidak bisa dihapus.']);
        }

        if ($game->image_path) {
            Storage::disk('public')->delete($game->image_path);
        }

        $name = $game->name;
        $game->delete();
        AdminLog::record('delete_game', null, ['name' => $name]);

        return redirect()->route('admin.games.index')->with('status', 'Game dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        unset($data['image']);

        return $data;
    }

    private function storeImage($file): string
    {
        $filename = 'games/' . Str::random(40) . '.webp';
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file)->scaleDown(width: 400);
        Storage::disk('public')->put($filename, (string) $img->toWebp(80));

        return $filename;
    }
}