@php
    $unreadCount = \App\Models\AdminNotification::query()->where('is_read', false)->count();
    $initialItems = \App\Models\AdminNotification::query()->latest()->limit((int) config('mercatoria.bell.max_dropdown', 10))->get()
        ->map(fn ($notification) => [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'is_read' => $notification->is_read,
            'time' => $notification->created_at->diffForHumans(),
            'iso' => $notification->created_at->toIso8601String(),
            'open_url' => route('office.notifications.open', $notification),
        ])->all();
@endphp

<div x-data="{
        open: false,
        unread: @js($unreadCount),
        items: @js($initialItems),
        async refresh() {
            try {
                const response = await fetch(@js(route('office.notifications.unread-count')), { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Could not refresh admin notifications');
                const data = await response.json();
                this.unread = data.count;
                this.items = data.items;
            } catch (error) {
                console.error(error);
            }
        },
        init() { window.setInterval(() => this.refresh(), {{ (int) config('mercatoria.bell.poll_interval', 60) * 1000 }}); }
    }" @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Notifikasi admin" class="relative rounded-lg p-2 text-navy-500 hover:bg-surface-2">
        <span aria-hidden="true" class="text-xl">🔔</span>
        <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread" class="absolute -right-1 -top-1 rounded-full bg-[#E53935] px-1.5 py-0.5 text-[10px] font-bold text-white"></span>
    </button>
    <section x-show="open" x-cloak x-transition class="absolute right-0 z-50 mt-2 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-xl border border-border bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-border p-4">
            <div><h2 class="font-semibold">Notifikasi Admin</h2><p class="text-xs text-text-muted"><span x-text="unread"></span> belum dibaca</p></div>
            <form method="POST" action="{{ route('office.notifications.read-all') }}">@csrf<button type="submit" class="text-xs font-medium text-navy-500 hover:underline">Tandai semua dibaca</button></form>
        </div>
        <ul class="max-h-[28rem] divide-y divide-border overflow-y-auto">
            <template x-for="item in items" :key="item.id">
                <li :class="item.is_read ? '' : 'bg-[#E3F2FD]'">
                    <form method="POST" :action="item.open_url">@csrf
                        <button type="submit" class="w-full p-3 text-left hover:bg-surface-2">
                            <span class="block text-sm font-semibold" x-text="item.title"></span>
                            <span class="mt-1 block text-xs text-text-muted" x-text="item.message"></span>
                            <time class="mt-1 block text-[11px] text-text-light" :datetime="item.iso" x-text="item.time"></time>
                        </button>
                    </form>
                </li>
            </template>
            <li x-show="items.length === 0" class="p-6 text-center text-sm text-text-muted">Belum ada notifikasi.</li>
        </ul>
        <a href="{{ route('office.notifications.index') }}" class="block border-t border-border p-3 text-center text-sm font-semibold text-navy-500 hover:bg-surface-2">Lihat semua notifikasi</a>
    </section>
</div>
