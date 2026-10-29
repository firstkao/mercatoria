@extends('layouts.app')

@section('title', 'Lengkapi Profil â€” Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Lengkapi Profil Kamu</h1>
    <p class="mt-1 text-sm text-ink/65">Tanggal lahir wajib diisi sebelum membuat pesanan. Terkunci permanen setelah disimpan.</p>

    <form method="POST" action="{{ route('profile.complete.store') }}" class="mt-5 space-y-4 rounded-xl border border-rule bg-white p-6">
        @csrf

        <div>
            <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                   class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            @error('tgl_lahir')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN</button>
    </form>
@endsection
