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
            'developers' => Developer::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:developers,slug'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']).'-'.Str::random(4);
        $data['is_active'] = $request->boolean('is_active', true);

        $developer = Developer::create($data);
        AdminLog::record($request->user(), 'create', 'Developer', $developer->id, null, $data, 'Tambah developer');

        return redirect()->route('office.developers.index')->with('status', 'Developer dibuat.');
    }

    public function update(Request $request, Developer $developer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($developer, ['name', 'is_active'], $data);
        $developer->update($data);
        AdminLog::record($request->user(), 'update', 'Developer', $developer->id, $old, $new, 'Ubah developer');

        return redirect()->route('office.developers.index')->with('status', 'Developer diperbarui.');
    }

    public function destroy(Request $request, Developer $developer): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Developer', $developer->id, $developer->only(['name', 'slug']), null, 'Hapus developer');
        $developer->delete();

        return redirect()->route('office.developers.index')->with('status', 'Developer dihapus.');
    }
}
