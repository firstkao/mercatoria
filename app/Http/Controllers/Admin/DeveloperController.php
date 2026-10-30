<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        return view('admin.developers.index', [
            'developers' => Developer::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['logo_path'] = $request->hasFile('logo') ? $request->file('logo')->store('developers') : null;
        $data['is_active'] = $request->boolean('is_active', true);
        unset($data['logo']);

        $developer = Developer::create($data);
        AdminLog::record($request->user(), 'create', 'Developer', $developer->id, null, $data, 'Tambah developer');

        return redirect()->route('office.developers.index')->with('status', 'Developer dibuat.');
    }

    public function update(Request $request, Developer $developer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:developers,slug,'.$developer->id],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Slug manual bila diisi; dikosongkan -> auto-generate dari nama; tidak dikirim -> tetap.
        if ($request->has('slug')) {
            $data['slug'] = filled($data['slug'] ?? null)
                ? $data['slug']
                : $this->uniqueSlug($data['name'], $developer->id);
        }

        if ($request->hasFile('logo')) {
            if ($developer->logo_path) {
                @unlink(storage_path('app/private/'.$developer->logo_path));
            }
            $data['logo_path'] = $request->file('logo')->store('developers');
        }
        unset($data['logo']);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($developer, ['name', 'is_active'], $data);
        $developer->update($data);
        AdminLog::record($request->user(), 'update', 'Developer', $developer->id, $old, $new, 'Ubah developer');

        return redirect()->route('office.developers.index')->with('status', 'Developer diperbarui.');
    }

    public function destroy(Request $request, Developer $developer): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Developer', $developer->id, $developer->only(['name', 'slug']), null, 'Hapus developer');
        if ($developer->logo_path) {
            @unlink(storage_path('app/private/'.$developer->logo_path));
        }
        $developer->delete();

        return redirect()->route('office.developers.index')->with('status', 'Developer dihapus.');
    }

    /**
     * Bulk developer: item dengan id = update, tanpa id = create (slug auto-generate).
     */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'developers' => ['required', 'array', 'min:1'],
            'developers.*.id' => ['nullable', 'integer', 'exists:developers,id'],
            'developers.*.name' => ['required', 'string', 'max:100'],
            'developers.*.is_active' => ['nullable', 'boolean'],
        ]);

        $diperbarui = 0;
        $dibuat = 0;

        foreach ($data['developers'] as $item) {
            if (! empty($item['id'])) {
                $developer = Developer::findOrFail($item['id']);
                [$old, $new] = AdminLog::diff($developer, ['name', 'is_active'], $item);
                $developer->update([
                    'name' => $item['name'],
                    'is_active' => (bool) ($item['is_active'] ?? true),
                ]);
                AdminLog::record($request->user(), 'update', 'Developer', $developer->id, $old, $new, 'Bulk ubah developer');
                $diperbarui++;
            } else {
                $developer = Developer::create([
                    'name' => $item['name'],
                    'slug' => $this->uniqueSlug($item['name']),
                    'is_active' => (bool) ($item['is_active'] ?? true),
                ]);
                AdminLog::record($request->user(), 'create', 'Developer', $developer->id, null, $developer->only(['name', 'slug']), 'Bulk tambah developer');
                $dibuat++;
            }
        }

        return redirect()->route('office.developers.index')
            ->with('status', "{$diperbarui} developer diperbarui, {$dibuat} developer dibuat.");
    }

    /**
     * Bulk hapus developer — ditolak bila masih terhubung ke game.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:developers,id'],
        ]);

        $developers = Developer::withCount('games')->whereIn('id', $data['ids'])->get();
        $terpakai = $developers->filter(fn (Developer $developer) => $developer->games_count > 0);

        if ($terpakai->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ids' => 'Developer '.implode(', ', $terpakai->pluck('name')->all()).' masih terhubung ke game.',
            ]);
        }

        foreach ($developers as $developer) {
            AdminLog::record($request->user(), 'delete', 'Developer', $developer->id, $developer->only(['name', 'slug']), null, 'Bulk hapus developer');
            if ($developer->logo_path) {
                @unlink(storage_path('app/private/'.$developer->logo_path));
            }
            $developer->delete();
        }

        return redirect()->route('office.developers.index')->with('status', $developers->count().' developer dihapus.');
    }

    /**
     * Generate slug bersih dari nama — ala WordPress.
     * Contoh: "MiHoYo" → "mihoyo"
     * Duplikat: "mihoyo" sudah ada → "mihoyo-2"
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 2;

        while (Developer::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
