@extends('layouts.app')

@section('title', 'Lengkapi Profil')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8"
     x-data="{
        open: false,
        tgl: '{{ old('tgl_lahir') }}',
        konf: '',
        get label() {
            if (!this.tgl) return '';
            return new Date(this.tgl + 'T00:00:00')
                .toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }
     }">

    <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-800">← Kembali ke Dashboard</a>

    <h1 class="text-2xl font-bold text-gray-800 mt-3 mb-2">Lengkapi Profil</h1>
    <div class="mb-6 rounded-lg border-l-4 border-[#1E88E5] bg-[#E3F2FD] p-3 text-sm text-gray-700">
        ℹ️ Satu langkah lagi! Isi tanggal lahir untuk unlock fitur order.
    </div>

    <form method="POST" action="{{ route('profile.complete') }}" class="bg-white border border-gray-200 rounded-lg p-6">
        @csrf

        <label for="tgl_lahir" class="block text-sm font-medium text-gray-700 mb-1">📅 Tanggal Lahir *</label>
        <input id="tgl_lahir" type="date" name="tgl_lahir" x-model="tgl"
               max="{{ now()->toDateString() }}" min="1900-01-02" required
               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400
                      {{ $errors->has('tgl_lahir') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
        @error('tgl_lahir')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror

        <div class="mt-4 rounded-lg border-l-4 border-[#E53935] bg-[#FFEBEE] p-4 text-sm text-gray-800">
            <p class="font-semibold mb-1">⚠️ PENTING — Baca dulu:</p>
            <ul class="list-disc ml-5 space-y-1">
                <li>Tanggal lahir hanya bisa diisi 1x seumur hidup.</li>
                <li>Setelah disimpan, <strong>TIDAK BISA DIUBAH</strong>.</li>
                <li>Dipakai untuk kalkulasi umur &amp; bonus ulang tahun.</li>
            </ul>
            <p class="mt-2">Pastikan tanggalnya benar!</p>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('dashboard') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Nanti</a>
            <button type="button" @click="open = true" :disabled="!tgl"
                    class="rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                Simpan
            </button>
        </div>

        {{-- Modal konfirmasi (di dalam form, supaya input "konfirmasi" ikut ter-submit) --}}
        <div x-show="open || {{ $errors->has('konfirmasi') ? 'true' : 'false' }}" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
             @keydown.escape.window="open = false; konf = ''">
            <div class="w-full max-w-md rounded-lg bg-white p-6" role="dialog" aria-modal="true" @click.outside="open = false; konf = ''">
                <h2 class="text-lg font-bold text-gray-900 mb-3">⚠️ Yakin tanggal lahir ini benar?</h2>

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-center mb-4">
                    <p class="text-lg font-semibold text-gray-900" x-text="'📅 ' + label"></p>
                    <p class="mt-2 text-xs text-gray-600">
                        Tanggal lahir TIDAK BISA DIUBAH setelah disimpan. Pastikan tidak ada typo.
                    </p>
                </div>

                <label for="konfirmasi" class="block text-sm text-gray-700 mb-1">Ketik "YAKIN" untuk konfirmasi:</label>
                <input id="konfirmasi" type="text" name="konfirmasi" x-model="konf" autocomplete="off"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
                @error('konfirmasi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="open = false; konf = ''"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" :disabled="konf !== 'YAKIN'"
                            class="rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan Tgl Lahir
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection