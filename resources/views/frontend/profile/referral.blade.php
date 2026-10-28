@extends('layouts.app')

@section('title', 'Undang Teman — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Undang Teman</h1>
    <p class="mt-1 text-sm text-gray-500">+3.000 koin saat temanmu menyelesaikan order pertama · teman dapat +1.000 koin saat pembayaran pertama terverifikasi.</p>

    <div class="mt-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <p class="text-xs text-gray-500">KODE UNDANGANMU</p>
        <p class="mt-2 text-2xl font-semibold tracking-widest">{{ $user->referral_code }}</p>
        <p class="mt-2 text-xs text-gray-400">
            Link: {{ url('/register?ref='.$user->referral_code) }}
        </p>
    </div>

    <div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Teman yang Diundang</h2>
        @if ($referrals->isEmpty())
            <p class="mt-2 text-sm text-gray-400">Belum ada yang bergabung lewat kode kamu.</p>
        @else
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($referrals as $referral)
                    <li class="flex justify-between border-b border-gray-50 py-1">
                        <span>{{ $referral->name }}</span>
                        <span class="text-gray-400">{{ $referral->created_at->format('d/m/Y') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
