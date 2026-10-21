<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Developer;
use App\Models\MerchCategory;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Daftar dan pengelolaan dasar Kategori Merch, Tag, dan Developer. */
class CatalogTaxonomyController extends Controller
{
    private const TYPES = [
        'merch-categories' => [MerchCategory::class, 'Kategori Merch', 'products'],
        'tags' => [Tag::class, 'Tag', 'products'],
        'developers' => [Developer::class, 'Developer', 'games'],
    ];

    public function index(Request $request, string $type): View
    {
        [$model, $title, $relation] = $this->resolve($type);
        $search = trim((string) $request->query('q'));
        $items = $model::query()->withCount($relation)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')->paginate(30)->withQueryString();

        return view('admin.taxonomy.index', [
            'type' => $type,
            'title' => $title,
            'items' => $items,
            'countLabel' => $relation === 'games' ? 'Game' : 'Produk',
            'search' => $search,
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        [$model, $title] = $this->resolve($type);
        $table = (new $model)->getTable();
        $validated = $request->validate(['name' => ['required', 'string', 'max:100', "unique:{$table},name"]]);

        $slug = Str::slug($validated['name']) ?: Str::random(8);
        $base = $slug;
        for ($i = 2; $model::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        $item = $model::query()->create(['name' => $validated['name'], 'slug' => $slug]);
        AdminLog::record('create', $model, $item->id, [], ['name' => $item->name], "Admin menambah {$title}.");

        return back()->with('success', "{$title} berhasil ditambahkan.");
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        [$model, $title] = $this->resolve($type);
        /** @var Model $item */
        $item = $model::query()->findOrFail($id);
        $old = ['name' => $item->name];
        $item->delete();
        AdminLog::record('delete', $model, $id, $old, [], "Admin menghapus {$title}.");

        return back()->with('success', "{$title} berhasil dihapus.");
    }

    private function resolve(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }
}
