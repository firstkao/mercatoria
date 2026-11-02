<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', [
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:tags,name'],
            'slug' => ['nullable', 'string', 'max:60', 'unique:tags,slug'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $tag = Tag::create($data);
        AdminLog::record($request->user(), 'create', 'Tag', $tag->id, null, $data, 'Tambah tag');

        return redirect()->route('office.tags.index')->with('status', 'Tag dibuat.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:tags,name,'.$tag->id],
            'slug' => ['nullable', 'string', 'max:60', 'unique:tags,slug,'.$tag->id],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $old = $tag->only(['name', 'slug']);
        $tag->update($data);
        AdminLog::record($request->user(), 'update', 'Tag', $tag->id, $old, $data, 'Update tag');

        return redirect()->route('office.tags.index')->with('status', 'Tag diperbarui.');
    }

    public function destroy(Request $request, Tag $tag): RedirectResponse
    {
        // Cek masih dipake di product_tag
        if ($tag->products()->exists()) {
            return redirect()->route('office.tags.index')->with('error', 'Tag "'.$tag->name.'" masih terpakai di produk.');
        }

        AdminLog::record($request->user(), 'delete', 'Tag', $tag->id, $tag->only(['name', 'slug']), null, 'Hapus tag');
        $tag->delete();

        return redirect()->route('office.tags.index')->with('status', 'Tag dihapus.');
    }
}
