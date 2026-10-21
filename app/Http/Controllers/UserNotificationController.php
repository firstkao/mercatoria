<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    private const FILTER_TYPES = [
        'order' => ['order', 'payment', 'order_note'],
        'coin' => ['coin', 'referral', 'birthday'],
        'voucher' => ['voucher', 'gift'],
        'state' => ['state'],
        'system' => ['system', 'cart'],
    ];

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(10)->get();

        return response()->json([
            'count' => $user->notifications()->where('is_read', false)->count(),
            'notifications' => $notifications->map(fn ($notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon ?: '🔔',
                'is_read' => (bool) $notification->is_read,
                'created_at' => $notification->created_at->toIso8601String(),
                'created_at_human' => $notification->created_at->locale('id')->diffForHumans(),
            ])->values(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'filter' => ['nullable', 'in:all,unread,order,coin,voucher,state,system'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);
        $query = $request->user()->notifications()
            ->when(($filters['filter'] ?? 'all') === 'unread', fn ($builder) => $builder->where('is_read', false))
            ->when(isset($filters['filter']) && ! in_array($filters['filter'], ['all', 'unread'], true),
                fn ($builder) => $builder->whereIn('type', self::FILTER_TYPES[$filters['filter']]))
            ->when($filters['q'] ?? null, fn ($builder, string $search) => $builder->where(function ($where) use ($search): void {
                $where->where('title', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            }));

        $notifications = ($filters['sort'] ?? 'newest') === 'oldest'
            ? $query->oldest()->paginate(20)->withQueryString()
            : $query->latest()->paginate(20)->withQueryString();

        return view('notifications.index', compact('notifications', 'filters'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notification_ids' => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['required', 'integer', 'distinct'],
            'action' => ['required', 'in:read,unread,delete'],
        ]);
        $notifications = $request->user()->notifications()->whereIn('id', $validated['notification_ids']);

        if ($validated['action'] === 'delete') {
            $notifications->delete();
        } else {
            $read = $validated['action'] === 'read';
            $notifications->update(['is_read' => $read, 'read_at' => $read ? now() : null]);
        }

        return back()->with('success', 'Notifikasi terpilih berhasil diperbarui.');
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
