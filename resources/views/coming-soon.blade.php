@extends('layouts.app')

@section('title', 'Menyusul &mdash; Mercatoria')

@section('content')
    <div class="mx-auto max-w-xl rounded-xl border border-rule bg-white p-10 text-center">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Info</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Menyusul</h1>
        <p class="mt-3 text-sm text-ink/65">
            Halaman ini belum dibangun. Fitur sedang dalam antrean pengembangan &mdash;
            tidak ada yang perlu dikhawatirkan dari pesananmu.
        </p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-block rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
            Kembali ke Dashboard
        </a>
    </div>
@endsection
