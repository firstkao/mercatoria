@php
    $user = auth()->user();
    $unreadCount = $user->notifications()->where('is_read', false)->count();
    $latestNotifications = $user->notifications()->latest()->limit(10)->get();
@endphp

<div x-data="{ open: false, unread: @js($unreadCount), init() { window.setInterval(() => fetch('{{ route('notifications.unread-count') }}', { headers: { Accept: 'application/json' } }).then((response) => { if (!response.ok) throw new Error('Could not refresh notification count'); return response.json(); }).then((data) => this.unread = data.count).catch((error) => console.error(error)), 60000); } }" @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Notifikasi" class="relative rounded-lg p-2 text-navy-500 hover:bg-surface-2">
        <span aria-hidden="true" class="text-xl">🔔</span>
        <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread" class="absolute -right-1 -top-1 rounded-full bg-[#E53935] px-1.5 py-0.5 text-[10px] font-bold text-white"></span>
    </button>

    <section x-show="open" x-cloak x-transition class="absolute right-0 z-50 mt-2 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-xl border border-border bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-border p-4">
            <div>
                <h2 class="font-semibold">Notifikasi</h2>
                <p class="text-xs text-text-muted"><span x-text="unread"></span> belum dibaca</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-xs font-medium text-navy-500 hover:underline">Tandai semua dibaca</button>
            </form>
        </div>
        <ul class="max-h-[28rem] divide-y divide-border overflow-y-auto">
            @forelse ($latestNotifications as $notification)
                <li class="{{ $notification->is_read ? '' : 'bg-[#E3F2FD]' }}">
                    <form method="POST" action="{{ route('notifications.open', $notification) }}">
                        @csrf
                        <button type="submit" class="flex w-full gap-3 p-3 text-left hover:bg-surface-2">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-2 text-xl">{{ $notification->icon ?: '🔔' }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm {{ $notification->is_read ? 'font-medium' : 'font-bold' }}">{{ $notification->title }}</span>
                                <span class="mt-0.5 block line-clamp-2 text-xs text-text-muted">{{ $notification->message }}</span>
                                <time class="mt-1 block text-[11px] text-text-light" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            </span>
                            @unless ($notification->is_read)
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-600" aria-label="Belum dibaca"></span>
                            @endunless
                        </button>
                    </form>
                </li>
            @empty
                <li class="p-6 text-center text-sm text-text-muted">Belum ada notifikasi.</li>
            @endforelse
        </ul>
        <a href="{{ route('notifications.index') }}" class="block border-t border-border p-3 text-center text-sm font-semibold text-navy-500 hover:bg-surface-2">Lihat Semua Notifikasi →</a>
    </section>
</div>
