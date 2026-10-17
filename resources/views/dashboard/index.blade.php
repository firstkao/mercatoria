@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header Dashboard --}}
    <div class="bg-white border border-border rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-text mb-1">Dashboard</h1>
                <p class="text-text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></p>

                {{-- Badge --}}
                <div class="flex flex-wrap gap-2 mt-3">
                    <x-badge-state :state="auth()->user()->currentState()" />
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs text-text-light border border-border">
                        Terdaftar: {{ auth()->user()->created_at->format('d M Y') }}
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                {{-- Logout: buka modal konfirmasi --}}
                <x-button variant="danger" @click="$dispatch('open-modal', 'konfirmasi-keluar')">
                    Keluar
                </x-button>
            </div>
        </div>
    </div>

    {{-- Banner: muncul sesuai kondisi (gak pernah muncul bareng) --}}
    <x-verify-banner />
    <x-profile-banner />

    {{-- Info Bar: 4 Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-border rounded-lg p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted mb-1">Total Pesanan</p>
            <p class="text-2xl font-bold text-text">0</p>
        </div>
        <div class="bg-white border border-border rounded-lg p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted mb-1">Total Belanja</p>
            <p class="text-2xl font-bold text-text">Rp 0</p>
        </div>
        <div class="bg-white border border-border rounded-lg p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted mb-1">Koin Aktif</p>
            <p class="text-2xl font-bold text-text">0</p>
        </div>
        <div class="bg-white border border-border rounded-lg p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted mb-1">Undang Teman</p>
            <p class="text-2xl font-bold text-text">0</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white border border-border rounded-lg overflow-hidden">
        <div class="border-b border-border">
            <nav class="flex overflow-x-auto">
                <button class="px-6 py-3 text-sm font-medium text-primary-600 border-b-2 border-primary-300 whitespace-nowrap">
                    Pesanan Saya
                </button>
                <button class="px-6 py-3 text-sm font-medium text-text-muted hover:text-text whitespace-nowrap">
                    Koin Saya
                </button>
                <button class="px-6 py-3 text-sm font-medium text-text-muted hover:text-text whitespace-nowrap">
                    Undang Teman
                </button>
                <button class="px-6 py-3 text-sm font-medium text-text-muted hover:text-text whitespace-nowrap">
                    Edit Profil
                </button>
            </nav>
        </div>

        <div class="p-8 text-center">
            <div class="text-4xl mb-3">📦</div>
            <p class="text-text-muted text-sm">Belum ada pesanan</p>
            <p class="text-xs text-text-light mt-1">Yuk mulai belanja merch favoritmu!</p>
            <x-button href="/" class="mt-4">Jelajahi Katalog</x-button>
        </div>
    </div>

    {{-- Modal konfirmasi keluar --}}
    <x-modal name="konfirmasi-keluar">
        <h2 class="text-lg font-bold text-text mb-2">Yakin mau keluar?</h2>
        <p class="text-sm text-text-muted mb-5">Kamu perlu login lagi untuk akses akun.</p>

        <div class="flex justify-end gap-2">
            <x-button variant="ghost" @click="$dispatch('close-modal', 'konfirmasi-keluar')">
                Batal
            </x-button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button variant="danger" type="submit">Ya, Keluar</x-button>
            </form>
        </div>
    </x-modal>

</div>
@endsection