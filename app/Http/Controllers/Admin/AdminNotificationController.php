<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AdminNotification::query()->latest()->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAllRead(): RedirectResponse
    {
        AdminNotification::query()->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function open(int $notification): RedirectResponse
    {
        $notification = AdminNotification::query()->findOrFail($notification);
        $notification->markAsRead();
        $path = parse_url($notification->link_url ?? '', PHP_URL_PATH);
        $query = parse_url($notification->link_url ?? '', PHP_URL_QUERY);
        $url = $path && str_starts_with($path, '/') ? $path.($query ? '?'.$query : '') : route('office.notifications.index');

        return redirect($url);
    }
}
