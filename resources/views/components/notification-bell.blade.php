@php
    $user = auth()->user();
    $unreadCount = $user->notifications()->where('is_read', false)->count();
    $latestNotifications = $user->notifications()->latest()->limit(10)->get();
@endphp

<div x-data="{
    open: false,
    unread: @js($unreadCount),
    notifications: @js($latestNotifications->map(fn ($notification) => ['id' => $notification->id, 'title' => $notification->title, 'message' => $notification->message, 'icon' => $notification->icon ?: '🔔', 'is_read' => (bool) $notification->is_read, 'created_at' => $notification->created_at->toIso8601String(), 'created_at_human' => $notification->created_at->locale('id')->diffForHumans()])->values()),
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

    <section x-show="open" x-cloak x-transition class="z-50 mt-2 overflow-hidden border border-border bg-white shadow-xl max-md:fixed max-md:inset-x-0 max-md:top-16 max-md:w-full md:absolute md:right-0 md:w-[380px] md:rounded-xl">
        <div class="flex items-center justify-between border-b border-border p-4">
            <div>
                <h2 class="font-semibold">Notifikasi (<span x-text="unread"></span> belum dibaca)</h2>
                <form method="POST" action="{{ route('notifications.read-all') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-navy-500 hover:underline">✓ Tandai semua dibaca</button>
                </form>
            </div>
        </div>
        <ul class="max-h-96 divide-y divide-border overflow-y-auto">
            <template x-for="item in notifications.slice(0, 10)" :key="item.id">
                <li :class="item.is_read ? '' : 'bg-[#E3F2FD]'">
                    <form method="POST" :action="'{{ url('/profile/notifications') }}/' + item.id + '/open'">
                        @csrf
                        <button type="submit" class="flex w-full items-start gap-3 p-4 text-left hover:bg-surface-2">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-2 text-xl" x-text="item.icon"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-bold" x-text="item.title"></span>
                                <span class="mt-0.5 line-clamp-2 block text-sm text-text-muted" x-text="item.message"></span>
                                <span class="mt-1 block text-xs text-text-light" x-text="item.created_at_human"></span>
                            </span>
                            <span x-show="!item.is_read" class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-600" aria-label="Belum dibaca"></span>
                        </button>
                    </form>
                </li>
            </template>
            <li x-show="notifications.length === 0" class="p-6 text-center text-sm text-text-muted">Belum ada notifikasi.</li>
        </ul>
        <a href="{{ route('notifications.index') }}" class="block border-t border-border p-3 text-center text-sm font-semibold text-navy-500 hover:bg-surface-2">Lihat Semua Notifikasi →</a>
    </section>
</div>
