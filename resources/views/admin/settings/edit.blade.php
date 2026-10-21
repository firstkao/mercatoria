@extends('layouts.admin')

@section('title', 'Pengaturan')

@php
    $val = fn (string $key, $default = '') => old($key, $settings[$key] ?? $default);
    $paused = (string) $val('pause_customer_emails', '0') === '1';
    $input = 'w-full rounded-lg border-border';
@endphp

@section('content')
<div>
    <h1 class="text-2xl font-bold text-navy-500">Pengaturan</h1>
    <p class="mt-1 text-sm text-text-muted">Atur harga, pengiriman, email, keamanan foto, dan pengukuran GA4.</p>
</div>

<form method="POST" action="{{ route('office.settings.update') }}" class="mt-5" x-data="{ tab: @js($tab), pauseModal: false, paused: @js($paused) }" x-ref="form">
    @csrf
    @method('PATCH')

    <nav class="flex gap-1 overflow-x-auto border-b border-border" aria-label="Tab pengaturan">
        @foreach ($tabs as $key => $label)
            <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-primary-300 text-navy-500 font-semibold' : 'border-transparent text-text-muted'"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm">{{ $label }}</button>
        @endforeach
    </nav>

    <div class="mt-5 space-y-5">
        <section x-show="tab === 'umum'" @if ($tab !== 'umum') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">Umum</h2>
            <p class="mt-1 text-sm text-text-muted">Nama toko: Mercatoria · Zona waktu tampilan admin: WIB (Asia/Jakarta).</p>
            <p class="mt-3 text-sm text-text-muted">Email CS: {{ config('mercatoria.store.cs_email') }}</p>
        </section>

        <section x-show="tab === 'harga'" @if ($tab !== 'harga') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">Harga &amp; Pengiriman</h2>
            <p class="mt-1 text-sm text-text-muted">Nilai baru berlaku untuk pesanan berikutnya. Rincian pesanan yang sudah dibuat tetap tersimpan di snapshot.</p>

            <div class="mt-5 space-y-4 border-l-4 border-[#1E88E5] bg-[#F8F9FA] p-4">
                <h3 class="font-semibold">🚚 Pengiriman</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="ongkir_cn_btm_per_kg" class="mb-1 block text-sm font-medium">Ongkir CN → Batam (Rp/kg)</label>
                        <input id="ongkir_cn_btm_per_kg" name="ongkir_cn_btm_per_kg" type="number" min="0" step="1" required value="{{ $val('ongkir_cn_btm_per_kg', 25000) }}" class="{{ $input }}">
                        <p class="mt-1 text-xs text-text-muted">Biaya pengiriman per kilogram dari China ke Batam.</p>
                        @error('ongkir_cn_btm_per_kg')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ongkir_btm_jkt_per_kg" class="mb-1 block text-sm font-medium">Ongkir Batam → Jakarta (Rp/kg)</label>
                        <input id="ongkir_btm_jkt_per_kg" name="ongkir_btm_jkt_per_kg" type="number" min="0" step="1" required value="{{ $val('ongkir_btm_jkt_per_kg', 60000) }}" class="{{ $input }}">
                        <p class="mt-1 text-xs text-text-muted">Biaya pengiriman per kilogram dari Batam ke Jakarta.</p>
                        @error('ongkir_btm_jkt_per_kg')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="default_weight_gram" class="mb-1 block text-sm font-medium">Default Berat (gram)</label>
                        <input id="default_weight_gram" name="default_weight_gram" type="number" min="0" step="1" required value="{{ $val('default_weight_gram', 0) }}" class="{{ $input }}">
                        <p class="mt-1 text-xs text-text-muted">Dipakai bila berat produk kosong. 0 = admin wajib input berat per produk.</p>
                        @error('default_weight_gram')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-5 space-y-4 border-l-4 border-[#1E88E5] bg-[#F8F9FA] p-4">
                <h3 class="font-semibold">💱 Harga &amp; Fee</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ([
                        ['rate_rmb_idr', 'Rate RMB → IDR', 2200, '0.01', 'Kurs tetap yang dipakai menghitung harga.'],
                        ['profit_percent', 'Profit (%)', 11, '0.01', 'Single profit pada harga jual.'],
                        ['fee_toco_fp', 'Fee Toco Full Payment (Rp)', 1, '1', null],
                        ['fee_toco_dp', 'Fee Toco DP (Rp)', 0, '1', null],
                        ['fee_shopee_fp', 'Fee Shopee Full Payment (Rp)', 109, '1', null],
                        ['fee_shopee_dp_percent', 'Fee Shopee DP (% dari DP)', 25, '0.01', null],
                        ['fee_qris_percent', 'Fee QRIS (%)', 0.03, '0.01', 'Berlaku bila total ≥ threshold.'],
                        ['qris_threshold', 'Threshold Fee QRIS (Rp)', 500000, '1', null],
                        ['dp_minimum_percent', 'DP Minimum (%)', 50, '1', null],
                    ] as [$name, $label, $default, $step, $help])
                        <div>
                            <label for="{{ $name }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="number" min="0" step="{{ $step }}" value="{{ $val($name, $default) }}" class="{{ $input }}">
                            @if ($help)<p class="mt-1 text-xs text-text-muted">{{ $help }}</p>@endif
                            @error($name)<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section x-show="tab === 'koin'" @if ($tab !== 'koin') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">Koin</h2>
            <p class="mt-1 text-sm text-text-muted">Aturan koin (bonus referral, cashback order, masa berlaku) dikelola lewat konfigurasi sistem dan tidak diubah dari halaman ini.</p>
        </section>

        <section x-show="tab === 'email'" @if ($tab !== 'email') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">Email</h2>
            <p class="mt-3 text-sm text-text-muted">Pengirim: security@ ({{ config('mercatoria.store.security_email') }}), info@ ({{ config('mercatoria.store.info_email') }}), cs@ ({{ config('mercatoria.store.cs_email') }}). Template diatur di menu Email Templates.</p>

            <input type="hidden" name="pause_customer_emails" :value="paused ? 1 : 0">
            <div class="mt-4 rounded-lg border p-4" :class="paused ? 'border-danger-500 bg-[#FFEBEE]' : 'border-success-500 bg-[#E8F5E9]'">
                <p class="font-semibold" x-text="paused ? '🔇 EMAIL CUSTOMER: OFF (Mode Testing)' : '🔊 EMAIL CUSTOMER: ON'"></p>
                <p class="mt-1 text-xs text-text-muted">Saat OFF, email ke customer tidak dikirim dan dicatat sebagai [SUPPRESSED]. OTP Admin, notifikasi admin, dan test email tetap terkirim.</p>
                <div class="mt-3">
                    <button type="button" x-show="!paused" @click="pauseModal = true" class="rounded-lg bg-danger-500 px-4 py-2 text-sm font-semibold text-white">Stop Kirim Email ke Customer</button>
                    <button type="button" x-show="paused" @click="paused = false; $nextTick(() => $root.requestSubmit())" class="rounded-lg bg-success-500 px-4 py-2 text-sm font-semibold text-white">Aktifkan Email Customer</button>
                </div>
            </div>

            <div class="mt-4" x-show="paused || pauseModal">
                <label for="pause_reason" class="mb-1 block text-sm font-medium">Catatan / Alasan Stop Email (opsional)</label>
                <input id="pause_reason" name="pause_reason" type="text" maxlength="500" value="{{ old('pause_reason') }}" placeholder="Mis. testing migrasi user lama" class="{{ $input }}">
                @error('pause_reason')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>

            <label class="mt-4 flex items-center gap-3 text-sm">
                <input type="hidden" name="notif_admin_payment_proof" value="0">
                <input type="checkbox" name="notif_admin_payment_proof" value="1" class="rounded border-border" @checked((string) $val('notif_admin_payment_proof', '1') === '1')>
                Notif Admin Bukti Bayar
            </label>

            <div x-show="pauseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="pauseModal = false">
                <div class="w-full max-w-md rounded-xl bg-white p-6">
                    <h3 class="text-lg font-semibold text-navy-500">Stop Email Customer?</h3>
                    <p class="mt-2 text-sm">Yakin ingin menghentikan sementara pengiriman email ke seluruh customer? Email akan dicatat sebagai [SUPPRESSED] dan tidak dikirim ulang.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" @click="pauseModal = false" class="rounded-lg border border-border px-4 py-2 text-sm">Batal</button>
                        <button type="button" @click="paused = true; pauseModal = false; $nextTick(() => $root.requestSubmit())" class="rounded-lg bg-danger-500 px-4 py-2 text-sm font-semibold text-white">Ya, Stop Email</button>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="tab === 'state'" @if ($tab !== 'state') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">State</h2>
            <p class="mt-1 text-sm text-text-muted">Durasi transisi state user (MVP → EX → ONS → Blocked) dijalankan oleh scheduler dan tidak diubah dari halaman ini.</p>
        </section>

        <section x-show="tab === 'security'" @if ($tab !== 'security') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">Security — Proteksi Foto</h2>
            <p class="mt-1 text-sm text-text-muted">Teks tetap bebas disalin (copy-paste). Hanya gambar yang dikunci dan diberi watermark.</p>
            <div class="mt-4 space-y-3">
                @foreach ([
                    ['image_protection_enabled', 'Aktifkan proteksi klik-kanan, drag, dan long-press pada gambar'],
                    ['image_watermark_enabled', 'Watermark otomatis pada foto yang diunggah'],
                    ['image_watermark_heavy_direct', 'Watermark tebal saat gambar dibuka langsung'],
                ] as [$name, $label])
                    <label class="flex items-center gap-3 text-sm">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="rounded border-border" @checked((string) $val($name, $name === 'image_watermark_heavy_direct' ? '0' : '1') === '1')>
                        {{ $label }}
                    </label>
                @endforeach
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="image_watermark_text" class="mb-1 block text-sm font-medium">Teks watermark</label>
                        <input id="image_watermark_text" name="image_watermark_text" type="text" maxlength="60" value="{{ $val('image_watermark_text', 'Mercatoria') }}" class="{{ $input }}">
                        @error('image_watermark_text')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="image_watermark_pattern" class="mb-1 block text-sm font-medium">Pola</label>
                        <select id="image_watermark_pattern" name="image_watermark_pattern" class="{{ $input }}">
                            @foreach (['diagonal_tiled' => 'Diagonal Tiled', 'center' => 'Tengah', 'corner' => 'Pojok'] as $k => $l)
                                <option value="{{ $k }}" @selected($val('image_watermark_pattern', 'diagonal_tiled') === $k)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="image_watermark_opacity" class="mb-1 block text-sm font-medium">Opacity watermark (%)</label>
                        <input id="image_watermark_opacity" name="image_watermark_opacity" type="number" min="5" max="80" value="{{ $val('image_watermark_opacity', 25) }}" class="{{ $input }}">
                        @error('image_watermark_opacity')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section x-show="tab === 'seo'" @if ($tab !== 'seo') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">Google Analytics 4</h2>
            <p class="mt-1 text-sm text-text-muted">GA4 saja, tanpa Google Tag Manager. Script aktif di halaman publik jika Measurement ID terisi.</p>
            <div class="mt-4 max-w-xl">
                <label for="ga4_measurement_id" class="mb-1 block text-sm font-medium">Measurement ID</label>
                <input id="ga4_measurement_id" name="ga4_measurement_id" type="text" maxlength="22" value="{{ $val('ga4_measurement_id') }}" placeholder="G-XXXXXXXXXX" autocomplete="off" class="{{ $input }}">
                <p class="mt-1 text-xs text-text-muted">Kosongkan untuk menonaktifkan GA4.</p>
                @error('ga4_measurement_id')<p class="mt-1 text-sm text-danger-500">Masukkan Measurement ID GA4 yang valid dengan format G-XXXXXXXXXX.</p>@enderror
            </div>
        </section>

        <section x-show="tab === 'api'" @if ($tab !== 'api') x-cloak @endif class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-navy-500">API</h2>
            <p class="mt-1 text-sm text-text-muted">Kunci API pihak ketiga dikelola lewat file environment server, bukan dari panel admin.</p>
        </section>

        <div class="flex justify-end">
            <x-button type="submit">Simpan Pengaturan</x-button>
        </div>
    </div>
</form>
@endsection
