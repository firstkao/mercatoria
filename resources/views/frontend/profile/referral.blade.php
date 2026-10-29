@extends('layouts.app')

@section('title', 'Undang Teman — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Undang Teman</h1>
    <p class="mt-1 text-sm text-ink/65">+3.000 koin saat temanmu menyelesaikan order pertama · teman dapat +1.000 koin saat pembayaran pertama terverifikasi.</p>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-6">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">KODE UNDANGANMU</p>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-[0.28em] text-ink">{{ $user->referral_code }}</p>
        <p class="mt-2 text-xs text-ink/50">
            Link: {{ url('/register?ref='.$user->referral_code) }}
        </p>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-4">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Teman yang Diundang</h2>
        @if ($referrals->isEmpty())
            <p class="mt-2 text-sm text-ink/45">Belum ada yang bergabung lewat kode kamu.</p>
        @else
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($referrals as $referral)
                    <li class="flex justify-between border-b border-rule/50 py-1">
                        <span class="text-ink/80">{{ $referral->name }}</span>
                        <span class="text-ink/50">{{ $referral->created_at->format('d/m/Y') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
