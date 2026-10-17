@extends('layouts.admin')

@section('title', 'Notifikasi Admin')

@section('content')
<div class="flex items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-navy-500">Notifikasi Admin</h1>
    <form method="POST" action="{{ route('office.notifications.read-all') }}">@csrf<button type="submit" class="text-sm font-semibold text-navy-500 hover:underline">Tandai semua dibaca</button></form>
</div>
<ul class="mt-5 divide-y divide-border overflow-hidden rounded-xl border border-border bg-white">
    @forelse ($notifications as $notification)
        <li class="{{ $notification->is_read ? '' : 'bg-[#E3F2FD]' }}">
            <form method="POST" action="{{ route('office.notifications.open', $notification) }}">@csrf
                <button class="w-full p-4 text-left hover:bg-surface-2">
                    <span class="block font-semibold">{{ $notification->title }}</span>
                    <span class="mt-1 block text-sm text-text-muted">{{ $notification->message }}</span>
                    <time class="mt-2 block text-xs text-text-light" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                </button>
            </form>
        </li>
    @empty
        <li class="p-8 text-center text-text-muted">Belum ada notifikasi admin.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $notifications->links() }}</div>
@endsection
