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
        ]);
        $data['slug'] = Str::slug($data['name']);

        $tag = Tag::create($data);
        AdminLog::record($request->user(), 'create', 'Tag', $tag->id, null, $data, 'Tambah tag');

        return redirect()->route('office.tags.index')->with('status', 'Tag dibuat.');
    }

    public function destroy(Request $request, Tag $tag): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Tag', $tag->id, $tag->only(['name', 'slug']), null, 'Hapus tag');
        $tag->delete();

        return redirect()->route('office.tags.index')->with('status', 'Tag dihapus.');
    }
}
