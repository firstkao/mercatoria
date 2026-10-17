@php
    $unreadCount = \App\Models\AdminNotification::query()->where('is_read', false)->count();
    $latestNotifications = \App\Models\AdminNotification::query()->latest()->limit(10)->get();
@endphp

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Notifikasi admin" class="relative rounded-lg p-2 text-navy-500 hover:bg-surface-2">
        <span aria-hidden="true" class="text-xl">🔔</span>
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 rounded-full bg-[#E53935] px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>
    <section x-show="open" x-cloak x-transition class="absolute right-0 z-50 mt-2 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-xl border border-border bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-border p-4">
            <div><h2 class="font-semibold">Notifikasi Admin</h2><p class="text-xs text-text-muted">{{ $unreadCount }} belum dibaca</p></div>
            <form method="POST" action="{{ route('office.notifications.read-all') }}">@csrf<button type="submit" class="text-xs font-medium text-navy-500 hover:underline">Tandai semua dibaca</button></form>
        </div>
        <ul class="max-h-[28rem] divide-y divide-border overflow-y-auto">
            @forelse ($latestNotifications as $notification)
                <li class="{{ $notification->is_read ? '' : 'bg-[#E3F2FD]' }}">
                    <form method="POST" action="{{ route('office.notifications.open', $notification) }}">@csrf
                        <button type="submit" class="w-full p-3 text-left hover:bg-surface-2">
                            <span class="block text-sm font-semibold">{{ $notification->title }}</span>
                            <span class="mt-1 block text-xs text-text-muted">{{ $notification->message }}</span>
                            <time class="mt-1 block text-[11px] text-text-light" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                        </button>
                    </form>
                </li>
            @empty
                <li class="p-6 text-center text-sm text-text-muted">Belum ada notifikasi.</li>
            @endforelse
        </ul>
        <a href="{{ route('office.notifications.index') }}" class="block border-t border-border p-3 text-center text-sm font-semibold text-navy-500 hover:bg-surface-2">Lihat semua notifikasi</a>
    </section>
</div>
