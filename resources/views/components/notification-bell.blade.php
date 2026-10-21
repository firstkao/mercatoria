@php
    $user = auth()->user();
    $unreadCount = $user->notifications()->where('is_read', false)->count();
    $latestNotifications = $user->notifications()->latest()->limit(10)->get();
@endphp

<div x-data="{
    open: false,
    unread: @js($unreadCount),
    notifications: @js($latestNotifications->map(fn ($notification) => ['id' => $notification->id, 'title' => $notification->title, 'message' => $notification->message, 'icon' => $notification->icon ?: '🔔', 'is_read' => (bool) $notification->is_read, 'created_at' => $notification->created_at->toIso8601String(), 'created_at_human' => $notification->created_at->diffForHumans()])->values()),
    toast: '',
    poll() {
        if (document.hidden) return;
        fetch('{{ route('notifications.unread-count') }}', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(response => { if (!response.ok) throw new Error('Could not refresh notifications'); return response.json(); })
            .then(data => {
                if (data.count > this.unread) {
                    this.toast = 'Kamu memiliki notifikasi baru.';
                    window.setTimeout(() => this.toast = '', 5000);
                }
                this.unread = data.count;
                this.notifications = data.notifications;
            })
            .catch(() => {});
    },
    init() {
        window.setInterval(() => this.poll(), 60000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) this.poll(); });
    }
}" @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Notifikasi" class="relative rounded-lg p-2 text-navy-500 hover:bg-surface-2">
        <span aria-hidden="true" class="text-xl">🔔</span>
        <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread" class="absolute -right-1 -top-1 rounded-full bg-[#E53935] px-1.5 py-0.5 text-[10px] font-bold text-white"></span>
    </button>
    <div x-show="toast" x-cloak x-transition role="status" aria-live="polite" x-text="toast" class="absolute right-0 top-12 z-[60] w-64 rounded-lg bg-navy-500 p-3 text-sm font-medium text-white shadow-lg"></div>

    <section x-show="open" x-cloak x-transition class="absolute right-0 z-50 mt-2 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-xl border border-border bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-border p-4">
            <div>
                <h2 class="font-semibold">Notifikasi</h2>
                <p class="text-xs text-text-muted"><span x-text="unread"></span> belum dibaca</p>
            </div>
