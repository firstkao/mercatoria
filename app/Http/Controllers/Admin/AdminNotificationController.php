<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AdminNotification::query()->latest()->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function unreadCount(): JsonResponse
    {
        $items = AdminNotification::query()->latest()
            ->limit((int) config('mercatoria.bell.max_dropdown', 10))->get()
            ->map(fn (AdminNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'time' => $notification->created_at->diffForHumans(),
                'iso' => $notification->created_at->toIso8601String(),
                'open_url' => route('office.notifications.open', $notification),
            ])->all();

        return response()->json([
            'count' => AdminNotification::query()->where('is_read', false)->count(),
            'items' => $items,
        ]);
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
