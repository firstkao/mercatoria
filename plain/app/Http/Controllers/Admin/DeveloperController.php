<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        return view('admin.developers.index', [
            'developers' => Developer::query()
                ->withCount(['games', 'products'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.developers.form', [
            'developer' => new Developer,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $developer = Developer::create($data);
        AdminLog::record('create_developer', $developer, ['name' => $developer->name]);

        return redirect()->route('admin.developers.index')->with('status', 'Developer ditambahkan.');
    }

    public function edit(Developer $developer): View
    {
        return view('admin.developers.form', ['developer' => $developer]);
    }

    public function update(Request $request, Developer $developer): RedirectResponse
    {
        $developer->update($this->validated($request));
        AdminLog::record('update_developer', $developer, ['name' => $developer->name]);

        return redirect()->route('admin.developers.index')->with('status', 'Developer diperbarui.');
    }

    public function destroy(Developer $developer): RedirectResponse
    {
        if ($developer->products()->exists() || $developer->games()->exists()) {
            return back()->withErrors(['developer' => 'Developer ini masih dipakai produk / game, tidak bisa dihapus.']);
        }

        $name = $developer->name;
        $developer->delete();
        AdminLog::record('delete_developer', null, ['name' => $name]);

        return redirect()->route('admin.developers.index')->with('status', 'Developer dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        return $data;
    }
}