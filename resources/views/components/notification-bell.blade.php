@props(['notifications', 'unreadCount'])

{{-- Bell notifikasi (PRD 43.2–43.4): navbar &mdash; badge merah #E53935, dropdown 10 terbaru --}}
<details class="notification-bell relative">
    <summary class="relative list-none cursor-pointer text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Notifikasi">
        <x-icons.bell width="18" height="18" />
        @if ($unreadCount > 0)
            <span class="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#E53935] px-1 text-[9px] font-semibold text-white"
                  data-unread="{{ $unreadCount }}">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </summary>

    <div class="absolute right-0 top-full z-30 mt-2 w-[380px] max-w-[90vw] rounded-xl border border-gray-100 bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <div>
                <p class="text-sm font-semibold">Notifikasi</p>
                <p class="text-xs text-gray-500">{{ $unreadCount }} belum dibaca</p>
            </div>
            <form method="POST" action="{{ route('profile.notifications.read-all') }}">
                @csrf
                <button class="text-xs text-[#4FC3F7] hover:underline">Tandai semua dibaca</button>
            </form>
        </div>

        <ul class="max-h-80 overflow-y-auto">
            @forelse ($notifications as $notification)
                <li data-notification-item class="border-b border-gray-50 px-4 py-2.5 text-sm {{ $notification->is_read ? 'text-gray-500' : 'font-medium' }}">
                    <a href="{{ $notification->target_url ?? '#' }}" class="block hover:text-black">
                        {{ $notification->title }}
                        <span class="block text-[11px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </a>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-ink/45">Belum ada notifikasi.</li>
            @endforelse
        </ul>

        <a href="{{ route('profile.notifications') }}" class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs text-gray-500 hover:text-black">
            Lihat Semua Notifikasi
        </a>
    </div>
</details>
