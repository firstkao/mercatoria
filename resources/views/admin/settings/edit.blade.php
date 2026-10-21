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

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-lg font-semibold text-navy-500">Email</h2>
        <label class="mt-4 flex items-start gap-3">
            <input type="hidden" name="pause_customer_emails" value="0">
            <input type="checkbox" name="pause_customer_emails" value="1" class="mt-1 rounded border-border"
                   @checked(old('pause_customer_emails', $settings['pause_customer_emails'] ?? '0') == '1')
                   onchange="if (this.checked && !confirm('Semua email ke customer akan dihentikan. Lanjutkan?')) this.checked = false">
            <span>
                <span class="block text-sm font-medium">Stop Kirim Email ke Customer (Mode Maintenance / Testing)</span>
                <span class="block text-xs text-text-muted">Email ke customer tidak dikirim dan dicatat sebagai suppressed_maintenance. OTP Admin tetap terkirim.</span>
            </span>
        </label>
    </section>

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-lg font-semibold text-navy-500">Security — Proteksi Foto</h2>
        <p class="mt-1 text-sm text-text-muted">Teks tetap bebas disalin. Hanya gambar yang dikunci dan diberi watermark.</p>
        <div class="mt-4 space-y-3">
            <label class="flex items-center gap-3 text-sm">
                <input type="hidden" name="image_protection_enabled" value="0">
                <input type="checkbox" name="image_protection_enabled" value="1" class="rounded border-border"
                       @checked(old('image_protection_enabled', $settings['image_protection_enabled'] ?? '1') == '1')>
                Aktifkan proteksi klik-kanan, drag, dan long-press pada gambar
            </label>
            <label class="flex items-center gap-3 text-sm">
                <input type="hidden" name="image_watermark_enabled" value="0">
                <input type="checkbox" name="image_watermark_enabled" value="1" class="rounded border-border"
                       @checked(old('image_watermark_enabled', $settings['image_watermark_enabled'] ?? '1') == '1')>
                Watermark otomatis pada foto yang diunggah
            </label>
            <div class="max-w-xs">
                <label for="image_watermark_opacity" class="mb-1 block text-sm font-medium">Opacity watermark (%)</label>
                <input id="image_watermark_opacity" name="image_watermark_opacity" type="number" min="5" max="80"
                       value="{{ old('image_watermark_opacity', $settings['image_watermark_opacity'] ?? 25) }}"
                       class="w-full rounded-lg border-border">
                @error('image_watermark_opacity')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="flex justify-end">
        <x-button type="submit">Simpan Pengaturan</x-button>
    </div>
</form>
@endsection
