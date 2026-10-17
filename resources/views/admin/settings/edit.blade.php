@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-navy-500">Pengaturan</h1>
    <p class="mt-1 text-sm text-text-muted">Atur biaya pengiriman, berat default, dan pengukuran GA4.</p>
</div>

<form method="POST" action="{{ route('office.settings.update') }}" class="mt-5 space-y-5">
    @csrf
    @method('PATCH')

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-lg font-semibold text-navy-500">Harga &amp; Pengiriman</h2>
        <p class="mt-1 text-sm text-text-muted">Nilai baru berlaku untuk pesanan berikutnya. Rincian pesanan yang sudah dibuat tetap tersimpan di snapshot.</p>

        <div class="mt-5 space-y-4 border-l-4 border-[#1E88E5] bg-[#F8F9FA] p-4">
            <h3 class="font-semibold">🚚 Pengiriman</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="ongkir_cn_btm_per_kg" class="mb-1 block text-sm font-medium">Ongkir CN → Batam (Rp/kg)</label>
                    <input id="ongkir_cn_btm_per_kg" name="ongkir_cn_btm_per_kg" type="number" min="0" step="1" required
                           value="{{ old('ongkir_cn_btm_per_kg', $settings['ongkir_cn_btm_per_kg'] ?? 25000) }}"
                           class="w-full rounded-lg border-border">
                    <p class="mt-1 text-xs text-text-muted">Biaya pengiriman per kilogram dari China ke Batam.</p>
                    @error('ongkir_cn_btm_per_kg')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ongkir_btm_jkt_per_kg" class="mb-1 block text-sm font-medium">Ongkir Batam → Jakarta (Rp/kg)</label>
                    <input id="ongkir_btm_jkt_per_kg" name="ongkir_btm_jkt_per_kg" type="number" min="0" step="1" required
                           value="{{ old('ongkir_btm_jkt_per_kg', $settings['ongkir_btm_jkt_per_kg'] ?? 60000) }}"
                           class="w-full rounded-lg border-border">
                    <p class="mt-1 text-xs text-text-muted">Biaya pengiriman per kilogram dari Batam ke Jakarta.</p>
                    @error('ongkir_btm_jkt_per_kg')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="default_weight_gram" class="mb-1 block text-sm font-medium">Berat Default Produk (gram)</label>
                    <input id="default_weight_gram" name="default_weight_gram" type="number" min="0" step="1" required
                           value="{{ old('default_weight_gram', $settings['default_weight_gram'] ?? 0) }}"
                           class="w-full rounded-lg border-border">
                    <p class="mt-1 text-xs text-text-muted">Digunakan bila berat produk tidak diisi. Nilai 0 berarti tanpa berat default.</p>
                    @error('default_weight_gram')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-lg font-semibold text-navy-500">Google Analytics 4</h2>
        <p class="mt-1 text-sm text-text-muted">GA4 saja, tanpa Google Tag Manager. Script hanya aktif di halaman publik jika Measurement ID terisi.</p>
        <div class="mt-4 max-w-xl">
            <label for="ga4_measurement_id" class="mb-1 block text-sm font-medium">Measurement ID</label>
            <input id="ga4_measurement_id" name="ga4_measurement_id" type="text" maxlength="22"
                   value="{{ old('ga4_measurement_id', $settings['ga4_measurement_id'] ?? '') }}"
                   placeholder="G-XXXXXXXXXX" autocomplete="off"
                   class="w-full rounded-lg border-border">
            <p class="mt-1 text-xs text-text-muted">Kosongkan untuk menonaktifkan GA4.</p>
            @error('ga4_measurement_id')<p class="mt-1 text-sm text-danger-500">Masukkan Measurement ID GA4 yang valid dengan format G-XXXXXXXXXX.</p>@enderror
        </div>
    </section>

    <div class="flex justify-end">
        <x-button type="submit">Simpan Pengaturan</x-button>
    </div>
</form>
@endsection
