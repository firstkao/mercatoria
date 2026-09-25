<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\NameBlacklistEntry;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NameBlacklistController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        return view('admin.identities.names', [
            'names' => NameBlacklistEntry::query()
                ->when($search !== '', fn ($query) => $query->where('name_normalized', 'like', '%'.NameBlacklistEntry::normalize($search).'%'))
                ->orderBy('name')
                ->paginate(50)
                ->withQueryString(),
            'filters' => ['q' => $search],
            ...IdentityController::tabCounts(Setting::integer('registration_limit', 3)),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['names' => ['required', 'string', 'max:5000']], [], ['names' => 'nama']);

        $added = collect(preg_split('/[\r\n,]+/', $data['names']))
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique(fn (string $name): string => NameBlacklistEntry::normalize($name))
            ->filter(fn (string $name): bool => NameBlacklistEntry::firstOrCreate(
                ['name_normalized' => NameBlacklistEntry::normalize($name)],
                ['name' => $name],
            )->wasRecentlyCreated)
            ->values();

        if ($added->isNotEmpty()) {
            AdminLog::record('add_blacklist_name', null, ['nama' => $added->join(', ')]);
        }

        return back()->with('status', $added->isEmpty() ? 'Semua nama sudah ada di daftar.' : "{$added->count()} nama ditambahkan.");
    }

    public function destroy(NameBlacklistEntry $name): RedirectResponse
    {
        $name->delete();
        AdminLog::record('delete_blacklist_name', null, ['nama' => $name->name]);

        return back()->with('status', "{$name->name} dihapus dari daftar.");
    }
}