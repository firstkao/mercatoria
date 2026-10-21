<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Game;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('admin.games.index', [
            'games' => Game::query()->with('defaultStore')->withCount('products')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.games.form', [
            'game' => new Game,
            'stores' => Store::query()->orderBy('name')->get(),
            'developers' => Developer::query()->orderBy('name')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGame($request);
        $game = DB::transaction(function () use ($validated, $request): Game {
            $game = Game::create($this->gameAttributes($validated, $request));
            $game->developers()->sync($validated['developer_ids'] ?? []);

            return $game;
        });

        return redirect()->route('office.games.edit', $game)->with('success', 'Game berhasil dibuat.');
    }

    public function edit(Game $game): View
    {
        return view('admin.games.form', [
            'game' => $game->load('developers'),
            'stores' => Store::query()->orderBy('name')->get(),
            'developers' => Developer::query()->orderBy('name')->get(),
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $validated = $this->validateGame($request, $game);
        $oldLogo = $game->logo_path;
        DB::transaction(function () use ($validated, $game, $request): void {
            $game->update($this->gameAttributes($validated, $request));
            $game->developers()->sync($validated['developer_ids'] ?? []);
        });

        if ($game->logo_path !== $oldLogo && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return redirect()->route('office.games.edit', $game)->with('success', 'Perubahan game berhasil disimpan.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function gameAttributes(array $validated, Request $request): array
    {
        $attributes = collect($validated)->except(['developer_ids', 'logo'])->all();

        if ($request->hasFile('logo')) {
            $attributes['logo_path'] = $request->file('logo')->store('logos/games', 'public');
        }

        return $attributes;
    }

    /**
     * @return array{name: string, slug: string, default_store_id: int|null, description: string|null, is_active: bool, developer_ids?: list<int>}
     */
    private function validateGame(Request $request, ?Game $game = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('games', 'slug')->ignore($game)],
            'default_store_id' => [Rule::requiredIf(fn () => Store::query()->exists()), 'nullable', 'integer', 'exists:stores,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'developer_ids' => ['required', 'array', 'min:1'],
            'developer_ids.*' => ['integer', 'distinct', 'exists:developers,id'],
            'logo' => ['nullable', 'file', 'mimes:png,webp', 'max:500'],
        ], [
            'name.required' => 'Nama game wajib diisi.',
            'slug.required' => 'Slug wajib diisi.',
            'developer_ids.required' => 'Pilih minimal satu developer.',
            'developer_ids.min' => 'Pilih minimal satu developer.',
            'default_store_id.required' => 'Default store wajib dipilih.',
            'logo.mimes' => 'Logo harus berformat PNG atau WebP.',
            'logo.max' => 'Logo maksimal 500 KB.',
        ]);
    }
}
