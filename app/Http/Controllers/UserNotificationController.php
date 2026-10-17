<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->notifications()->where('is_read', false)->count(),
        ]);
    }

    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function open(Request $request, int $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();
        $path = parse_url($notification->link_url ?? '', PHP_URL_PATH);
        $query = parse_url($notification->link_url ?? '', PHP_URL_QUERY);
        $url = $path && str_starts_with($path, '/') ? $path.($query ? '?'.$query : '') : route('notifications.index');

        return redirect($url);
    }
}
