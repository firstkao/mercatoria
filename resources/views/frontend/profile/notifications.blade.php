@extends('layouts.app')

@section('title', 'Notifikasi &mdash; Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Notifikasi</h1>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <div class="mt-5 space-y-2">
        @forelse ($notifications as $notification)
            <a href="{{ $notification->target_url ?? '#' }}"
               class="block rounded-xl border border-rule bg-white px-4 py-3 transition-colors hover:border-ink {{ $notification->is_read ? 'opacity-60' : '' }}">
                <p class="text-sm text-ink {{ $notification->is_read ? '' : 'font-semibold' }}">{{ $notification->title }}</p>
                <p class="mt-0.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
            </a>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
