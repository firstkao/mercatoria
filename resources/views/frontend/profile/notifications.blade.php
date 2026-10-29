@extends('layouts.app')

@section('title', 'Notifikasi — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Notifikasi</h1>

    <div class="mt-4 space-y-2">
        @forelse ($notifications as $notification)
            <a href="{{ $notification->target_url ?? '#' }}"
               class="block rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100 hover:shadow {{ $notification->is_read ? 'opacity-60' : '' }}">
                <p class="text-sm {{ $notification->is_read ? '' : 'font-semibold' }}">{{ $notification->title }}</p>
                <p class="text-xs text-gray-400">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
            </a>
        @empty
            <div class="rounded-xl bg-white p-8 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-100">
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
