<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter');

        $query = $request->user()->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(30)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $request->user()->unreadNotificationsCount(),
            'title' => 'Notifikasi',
        ]);
    }

    public function read(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        // ✅ BUG FIX: redirect($notification->url) tanpa validasi = open redirect.
        // URL notifikasi tersimpan di DB; kalau suatu saat ada baris dengan URL
        // external (data impor / bug di NotificationService), user dikirim ke
        // situs lain setelah mengklik notifikasi dari domain mercatoria.
        if ($notification->url && $this->isSafeInternalUrl($notification->url)) {
            return redirect($notification->url);
        }

        return redirect()->route('notifications.index');
    }

    /**
     * Hanya izinkan tujuan di dalam aplikasi sendiri: path relatif, atau URL
     * absolut yang masih berada di bawah APP_URL.
     */
    private function isSafeInternalUrl(string $url): bool
    {
        // Tolak "//evil.com" (protocol-relative) dan "/\evil.com" (trik browser).
        if (preg_match('#^/{2}|^/\\\\#', $url) === 1) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        $appUrl = config('app.url');

        return is_string($appUrl) && $appUrl !== ''
            && str_starts_with($url, rtrim($appUrl, '/'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function destroy(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return back()->with('status', 'Notifikasi dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back()->with('status', 'Semua notifikasi dihapus.');
    }

    public function markReadJson(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }
}