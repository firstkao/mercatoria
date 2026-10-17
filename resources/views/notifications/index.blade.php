@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8">
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-navy-500">Notifikasi</h1>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button type="submit" class="text-sm font-semibold text-navy-500 hover:underline">Tandai semua dibaca</button></form>
    </div>
    @if (session('success'))<p role="status" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</p>@endif
    <ul class="mt-5 divide-y divide-border overflow-hidden rounded-xl border border-border bg-white">
        @forelse ($notifications as $notification)
            <li class="{{ $notification->is_read ? '' : 'bg-[#E3F2FD]' }}">
                <form method="POST" action="{{ route('notifications.open', $notification) }}">@csrf
                    <button class="flex w-full items-start gap-4 p-4 text-left hover:bg-surface-2">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-2 text-xl">{{ $notification->icon ?: '🔔' }}</span>
                        <span class="flex-1"><span class="block font-semibold">{{ $notification->title }}</span><span class="mt-1 block text-sm text-text-muted">{{ $notification->message }}</span><time class="mt-2 block text-xs text-text-light" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time></span>
                        @unless ($notification->is_read)<span class="mt-2 h-2 w-2 rounded-full bg-blue-600"></span>@endunless
                    </button>
                </form>
            </li>
        @empty
            <li class="p-8 text-center text-text-muted">Belum ada notifikasi.</li>
        @endforelse
    </ul>
    <div class="mt-5">{{ $notifications->links() }}</div>
</div>
@endsection
