<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.announcements.index', [
            'announcements' => Announcement::query()->latest('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.announcements.form', ['announcement' => new Announcement]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        DB::transaction(function () use ($validated): void {
            $announcement = Announcement::create($validated);
            AdminLog::record('announcement_created', Announcement::class, $announcement->id, new: $validated);
        });

        return redirect()->route('office.announcements.index')->with('success', 'Announcement berhasil dibuat.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.promotions.announcements.form', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $old = $announcement->only(array_keys($validated));
        DB::transaction(function () use ($announcement, $validated, $old): void {
            $announcement->update($validated);
            AdminLog::record('announcement_updated', Announcement::class, $announcement->id, old: $old, new: $validated);
        });

        return redirect()->route('office.announcements.index')->with('success', 'Announcement berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        DB::transaction(function () use ($announcement): void {
            AdminLog::record('announcement_deleted', Announcement::class, $announcement->id, old: $announcement->only(['text', 'link_url', 'is_active']));
            $announcement->delete();
        });

        return redirect()->route('office.announcements.index')->with('success', 'Announcement berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:500', 'regex:#^(https?://|/(?!/))#i'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
