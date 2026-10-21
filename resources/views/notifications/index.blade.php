@extends('layouts.app')

@section('title', 'Semua Notifikasi')

@section('content')
<div class='mx-auto max-w-5xl px-4 py-8'>
    <div class='flex flex-wrap items-center justify-between gap-3'><h1 class='text-2xl font-bold text-navy-500'>Semua Notifikasi</h1><form method='POST' action='{{ route('notifications.read-all') }}'>@csrf<button type='submit' class='text-sm font-semibold text-navy-500 hover:underline'>Tandai semua dibaca</button></form></div>
    @if (session('success'))<p role='status' class='mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800'>{{ session('success') }}</p>@endif
    @if ($errors->any())<p role='alert' class='mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800'>{{ $errors->first() }}</p>@endif
    <nav aria-label='Filter notifikasi' class='mt-5 flex gap-2 overflow-x-auto pb-2 text-sm'>
        @foreach ([['all', 'Semua'], ['unread', 'Belum Dibaca'], ['order', 'Order'], ['coin', 'Koin'], ['voucher', 'Voucher'], ['state', 'State'], ['system', 'Sistem']] as [$key, $label])
            <a href='{{ route('notifications.index', ['filter' => $key]) }}' class='whitespace-nowrap rounded-full px-3 py-2 {{ ($filters['filter'] ?? 'all') === $key ? 'bg-navy-500 text-white' : 'border border-border bg-white text-text-muted' }}'>{{ $label }}</a>
        @endforeach
    </nav>
    <form method='GET' action='{{ route('notifications.index') }}' class='mt-3 flex flex-wrap gap-2'>
        @if (($filters['filter'] ?? 'all') !== 'all')<input type='hidden' name='filter' value='{{ $filters['filter'] }}'>@endif
        <input type='search' name='q' value='{{ $filters['q'] ?? '' }}' placeholder='🔍 Cari notifikasi...' class='min-w-[14rem] flex-1 rounded-lg border-border'>
        <label class='sr-only' for='notification-sort'>Urutkan</label><select id='notification-sort' name='sort' class='rounded-lg border-border'><option value='newest' @selected(($filters['sort'] ?? 'newest') === 'newest')>Terbaru</option><option value='oldest' @selected(($filters['sort'] ?? '') === 'oldest')>Terlama</option></select>
        <button type='submit' class='rounded-lg bg-navy-500 px-4 py-2 text-sm font-semibold text-white'>Cari</button>
    </form>
    <form id='notifications-bulk' method='POST' action='{{ route('notifications.bulk') }}' class='mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-border bg-white p-3'>@csrf
        <label class='mr-auto inline-flex items-center gap-2 text-sm'><input id='notification-select-all' type='checkbox' class='rounded border-border'> Pilih Semua</label>
        <button type='submit' name='action' value='read' class='rounded-lg border border-border px-3 py-2 text-xs font-semibold'>Tandai Dibaca</button><button type='submit' name='action' value='unread' class='rounded-lg border border-border px-3 py-2 text-xs font-semibold'>Tandai Belum</button><button type='submit' name='action' value='delete' class='rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700'>Hapus</button>
    </form>
    <ul class='mt-3 divide-y divide-border overflow-hidden rounded-xl border border-border bg-white'>
        @forelse ($notifications as $notification)
            <li class='flex items-start gap-3 p-4 {{ $notification->is_read ? '' : 'bg-[#E3F2FD]' }}'>
                <input form='notifications-bulk' type='checkbox' name='notification_ids[]' value='{{ $notification->id }}' class='notification-checkbox mt-2 rounded border-border' aria-label='Pilih notifikasi {{ $notification->title }}'>
                <form method='POST' action='{{ route('notifications.open', $notification) }}' class='min-w-0 flex-1'>@csrf
                    <button type='submit' class='flex w-full items-start gap-3 text-left hover:bg-surface-2'><span class='flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-2 text-xl'>{{ $notification->icon ?: '🔔' }}</span><span class='min-w-0 flex-1'><span class='block font-semibold'>{{ $notification->title }}</span><span class='mt-1 block text-sm text-text-muted'>{{ $notification->message }}</span><time class='mt-2 block text-xs text-text-light' datetime='{{ $notification->created_at->toIso8601String() }}'>{{ $notification->created_at->diffForHumans() }}</time></span>@unless ($notification->is_read)<span class='mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-600' aria-label='Belum dibaca'></span>@endunless</button>
                </form>
            </li>
        @empty<li class='p-8 text-center text-text-muted'>Belum ada notifikasi yang cocok dengan filter ini.</li>@endforelse
    </ul>
    <div class='mt-5'>{{ $notifications->links() }}</div><p class='mt-4 text-center text-xs text-text-light'>Notifikasi disimpan selama 90 hari.</p>
</div>
@endsection

@push('scripts')
<script>
    const notificationChecks = [...document.querySelectorAll('.notification-checkbox')];
    document.getElementById('notification-select-all')?.addEventListener('change', event => notificationChecks.forEach(input => input.checked = event.target.checked));
</script>
@endpush
