@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('content')
@php
    $allSettings = collect($groups)->flatten(1)->keyBy('key');

    $tabMap = [
        'Umum'       => ['display', 'order', 'product', 'social', 'snapshot'],
        'Harga'      => ['pricing'],
        'Koin'       => ['coin'],
        'Email'      => ['email', 'notification'],
        'State'      => ['tier', 'penalty'],
        'Security'   => ['security'],
        'SEO'        => ['seo'],
        'Lainnya'    => ['user', 'splitbox'],
    ];

    $sections = [];
    foreach ($tabMap as $tabName => $groupNames) {
        foreach ($groupNames as $gn) {
            if (!isset($groups[$gn])) continue;
            foreach ($groups[$gn] as $setting) {
                if ($gn === 'coin' && str_starts_with($setting->key, 'voucher_birthday')) continue;
                $sections[$tabName][] = $setting;
            }
        }
    }
    $mappedGroups = collect($tabMap)->flatten()->all();
    foreach ($groups as $group => $items) {
        if (in_array($group, $mappedGroups) || $group === 'marketing') continue;
        foreach ($items as $setting) {
            $sections['Lainnya'][] = $setting;
        }
    }

    $unitFor = function (string $key): string {
        if (str_contains($key, 'percent') || str_contains($key, 'opacity')) return '%';
        if (str_contains($key, 'chars')) return 'karakter';
        if (str_contains($key, 'days')) return 'hari';
        if (str_contains($key, 'hours')) return 'jam';
        if (str_contains($key, 'minutes') || str_contains($key, 'per_minute')) return 'menit';
        if (str_contains($key, 'seconds') || str_contains($key, 'polling')) return 'detik';
        if (str_contains($key, 'per_kg')) return 'Rp/kg';
        if (str_contains($key, 'weight') || str_contains($key, 'berat')) return 'gram';
        if (str_starts_with($key, 'coin_')) return 'koin';
        if (preg_match('/(nominal|min_belanja|threshold|bonus|penalty|price|fee|rate|rounding|discount|purchase|ongkir|rp_)/', $key)) return 'Rp';
        return '';
    };

    $unitHints = ['(persen)', '(idr)', '(rp)', '(hari)', '(jam)', '(menit)', '(detik)', '(gram)', '(kg)', '(koin)', '(karakter)', '(%)'];

    $sv = function (string $key) use ($allSettings) {
        return $allSettings[$key]->value ?? '';
    };
@endphp

{{-- Header --}}
<div class="flex items-end justify-between">
    <div>
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
        <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Settings</h1>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="mt-5 flex flex-wrap gap-1 border-b border-rule" role="tablist">
    @foreach (array_keys($sections) as $i => $name)
        <button type="button" data-settings-tab="{{ $name }}"
                class="settings-tab border-b-2 px-4 py-2 text-sm {{ $i === 0 ? 'border-ink font-semibold text-ink' : 'border-transparent text-ink/55 hover:text-ink' }}">
            {{ $name }}
        </button>
    @endforeach
</div>

<form method="POST" action="{{ route('office.settings.update') }}" class="mt-5" id="settings-form">
    @csrf

    {{-- ═══ TAB: Generic (Umum / Koin / State / SEO / Lainnya) ═══ --}}
    @foreach ($sections as $name => $items)
        @if(in_array($name, ['Umum', 'Koin', 'State', 'SEO', 'Lainnya']))
            <div class="settings-panel rounded-xl border border-rule bg-white p-6" data-settings-panel="{{ $name }}" @if(!$loop->first) style="display:none" @endif>
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                    <i class="bi bi-gear me-1.5" style="font-size:0.85rem;"></i>
                    {{ $name }}
                </h2>
                <div class="mt-4 space-y-4">
                    @foreach ($items as $setting)
                        @php
                            $unit = $unitFor($setting->key);
                            $label = trim($setting->description ?: str_replace('_', ' ', ucwords($setting->key, '_')), '. ');
                            $hasHint = $unit !== '' && collect($unitHints)->contains(fn ($h) => str_contains(strtolower($label), $h));
                        @endphp
                        <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3">
                            <label class="text-sm text-ink/80" for="setting-{{ $setting->key }}">
                                {{ $label }}{{ $unit !== '' && ! $hasHint ? ' (' . $unit . ')' : '' }}
                            </label>
                            <div class="md:col-span-2">
                                @if ($setting->type === 'bool')
                                    <select name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                            class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                        <option value="1" @selected($setting->value === '1')>Aktif</option>
                                        <option value="0" @selected($setting->value === '0')>Nonaktif</option>
                                    </select>
                                @else
                                    <input type="text" name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                           value="{{ $setting->value }}"
                                           class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                @endif
                                @error('settings.'.$setting->key)<p class="text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- [FIX #2] Tombol Simpan untuk tab generic --}}
                <div class="mt-6">
                    <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                        <i class="bi bi-check-lg me-1.5" style="font-size:0.85rem;"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        @endif
    @endforeach

    {{-- ═══ TAB: Harga — DS 15.1 ═══ --}}
    <div class="settings-panel rounded-xl border border-rule bg-white p-6" data-settings-panel="Harga" style="display:none">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
            <i class="bi bi-cash-coin me-1.5" style="font-size:0.85rem;"></i>
            Harga & Pengiriman
        </h2>

        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-ink" for="setting-rate_rmb">Rate RMB &rarr; IDR *</label>
                <input type="text" name="settings[rate_rmb]" id="setting-rate_rmb"
                       value="{{ $sv('rate_rmb') }}"
                       class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                @error('settings.rate_rmb')<p class="text-xs text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-ink" for="setting-profit_percent">Profit (%) *</label>
                <input type="text" name="settings[profit_percent]" id="setting-profit_percent"
                       value="{{ $sv('profit_percent') }}"
                       class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                @error('settings.profit_percent')<p class="text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 rounded-r-lg border-l-4 border-l-[#1E88E5] bg-[#F8F9FA] p-4">
            <h3 class="flex items-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                <i class="bi bi-truck text-[#1E88E5]" style="font-size:0.95rem;"></i>
                Pengiriman
            </h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-ink" for="setting-ongkir_cn_batam_per_kg">
                        Ongkir CN &rarr; Batam (per kg) *
                        <span class="ml-1 text-[10px] font-normal text-ink/45">+ NEW v2.1</span>
                    </label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="text" name="settings[ongkir_cn_batam_per_kg]" id="setting-ongkir_cn_batam_per_kg"
                               value="{{ $sv('ongkir_cn_batam_per_kg') }}"
                               class="w-full rounded-lg border border-rule bg-white px-3 py-2.5 text-sm text-ink">
                        <span class="whitespace-nowrap text-xs text-ink/50">Rp/kg</span>
                    </div>
                    <p class="mt-1 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        Biaya per kg dari China ke Batam.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink" for="setting-ongkir_batam_jakarta_per_kg">
                        Ongkir Batam &rarr; Jakarta (per kg) *
                        <span class="ml-1 text-[10px] font-normal text-ink/45">+ NEW v2.1</span>
                    </label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="text" name="settings[ongkir_batam_jakarta_per_kg]" id="setting-ongkir_batam_jakarta_per_kg"
                               value="{{ $sv('ongkir_batam_jakarta_per_kg') }}"
                               class="w-full rounded-lg border border-rule bg-white px-3 py-2.5 text-sm text-ink">
                        <span class="whitespace-nowrap text-xs text-ink/50">Rp/kg</span>
                    </div>
                    <p class="mt-1 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        Biaya per kg dari Batam ke Jakarta.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink" for="setting-default_weight_gram">
                        Default Berat Produk (gram)
                        <span class="ml-1 text-[10px] font-normal text-ink/45">+ NEW v2.1</span>
                    </label>
                    <input type="text" name="settings[default_weight_gram]" id="setting-default_weight_gram"
                           value="{{ $sv('default_weight_gram') }}"
                           class="mt-1.5 w-full rounded-lg border border-rule bg-white px-3 py-2.5 text-sm text-ink">
                    <p class="mt-1 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        Berat default kalau admin gak input. Set 0 = admin wajib input berat per produk.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t border-rule pt-5">
            <h3 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Fee Marketplace & Pembayaran</h3>
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-xs text-ink/70" for="setting-fee_toco_fp">Fee Toco FP</label>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-xs text-ink/50">Rp</span>
                        <input type="text" name="settings[fee_toco_fp]" id="setting-fee_toco_fp"
                               value="{{ $sv('fee_toco_fp') }}"
                               class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-ink/70" for="setting-fee_toco_dp">Fee Toco DP</label>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-xs text-ink/50">Rp</span>
                        <input type="text" name="settings[fee_toco_dp]" id="setting-fee_toco_dp"
                               value="{{ $sv('fee_toco_dp') }}"
                               class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-ink/70" for="setting-fee_shopee_fp">Fee Shopee FP</label>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-xs text-ink/50">Rp</span>
                        <input type="text" name="settings[fee_shopee_fp]" id="setting-fee_shopee_fp"
                               value="{{ $sv('fee_shopee_fp') }}"
                               class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-ink/70" for="setting-fee_shopee_dp">Fee Shopee DP</label>
                    <div class="mt-1 flex items-center gap-1.5">
                        <input type="text" name="settings[fee_shopee_dp]" id="setting-fee_shopee_dp"
                               value="{{ $sv('fee_shopee_dp') }}"
                               class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                        <span class="text-xs text-ink/50">%</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-xs text-ink/70" for="setting-fee_qris_percent">Fee QRIS (%)</label>
                    <input type="text" name="settings[fee_qris_percent]" id="setting-fee_qris_percent"
                           value="{{ $sv('fee_qris_percent') }}"
                           class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                </div>
                <div>
                    <label class="block text-xs text-ink/70" for="setting-qris_threshold">Threshold</label>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-xs text-ink/50">Rp</span>
                        <input type="text" name="settings[qris_threshold]" id="setting-qris_threshold"
                               value="{{ $sv('qris_threshold') }}"
                               class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-ink/70" for="setting-dp_minimum_percent">DP Minimum (%)</label>
                    <input type="text" name="settings[dp_minimum_percent]" id="setting-dp_minimum_percent"
                           value="{{ $sv('dp_minimum_percent') }}"
                           class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                <i class="bi bi-check-lg me-1.5" style="font-size:0.85rem;"></i> Simpan Pengaturan
            </button>
        </div>
    </div>

    {{-- ═══ TAB: Email — DS 15.2 ═══ --}}
    <div class="settings-panel rounded-xl border border-rule bg-white p-6" data-settings-panel="Email" style="display:none">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
            <i class="bi bi-envelope me-1.5" style="font-size:0.85rem;"></i>
            Kontrol Pengiriman Email Customer
        </h2>

        {{-- Kill Switch Toggle --}}
        <div class="mt-5 rounded-lg border border-rule bg-paper/40 p-6">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm font-medium text-ink">Stop Kirim Email ke Customer</p>
                    <p class="mt-1 text-xs text-ink/55">Global Kill-Switch</p>
                </div>
                @php
                    $emailPaused = $sv('customer_emails_paused') === '1';
                @endphp
                <label class="flex cursor-pointer select-none items-center gap-3">
                    <span class="relative inline-flex">
                        <input type="checkbox" name="settings[customer_emails_paused]"
                               value="1" id="kill-switch"
                               {{ $emailPaused ? 'checked' : '' }}
                               class="peer sr-only">
                        {{-- [FIX #3] Pakai peer-checked: supaya toggle visual update otomatis --}}
                        <span id="kill-toggle-visual"
                              class="block h-8 w-[56px] rounded-full transition-colors peer-checked:bg-[#E53935] peer-checked:after:translate-x-[28px] bg-[#43A047] after:absolute after:left-1 after:top-1 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow-sm after:transition-transform after:content-['']">
                        </span>
                    </span>
                    <span id="kill-label-text" class="whitespace-nowrap text-sm font-medium {{ $emailPaused ? 'text-[#E53935]' : 'text-[#43A047]' }}">
                        {{ $emailPaused ? 'AKTIF — Email Dihentikan' : 'NONAKTIF — Normal' }}
                    </span>
                </label>
            </div>

            <div id="kill-warning" class="{{ $emailPaused ? '' : 'hidden' }}">
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50/50 p-4">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle text-[#E53935]" style="font-size:1rem;"></i>
                        <p class="text-sm font-semibold text-[#E53935]">Status Saat Ini: Pengiriman Email ke Customer Dihentikan</p>
                    </div>
                    <ul class="mt-3 space-y-1.5 text-xs text-ink/70">
                        <li class="flex items-start gap-2"><i class="bi bi-check text-green-600 mt-0.5"></i> Sistem web, transaksi, & Bell Notifikasi tetap jalan normal.</li>
                        <li class="flex items-start gap-2"><i class="bi bi-x text-[#E53935] mt-0.5"></i> Semua email ke Customer/Crush (order, status, reset, verifikasi, koin, reminder) TIDAK dikirim.</li>
                        <li class="flex items-start gap-2"><i class="bi bi-info-circle text-blue-500 mt-0.5"></i> Aktivitas email tetap tercatat di Email Log dengan status [SUPPRESSED].</li>
                        <li class="flex items-start gap-2"><i class="bi bi-check text-green-600 mt-0.5"></i> Email 2FA OTP Admin & Test Send Admin TETAP BERJALAN.</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-ink" for="setting-email_stop_reason">
                        Catatan / Alasan Stop Email (Opsional — untuk Admin Log)
                    </label>
                    <textarea name="settings[email_stop_reason]" id="setting-email_stop_reason"
                              rows="2"
                              class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink"
                              placeholder="Sedang tes alur checkout & inject order migrasi">{{ $sv('email_stop_reason') }}</textarea>
                </div>
            </div>
        </div>

        {{-- SMTP Pengirim --}}
        <div class="mt-6 rounded-lg border border-rule bg-paper/40 p-5">
            <h3 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                <i class="bi bi-send me-1.5" style="font-size:0.85rem;"></i>
                Pengirim Email Resmi (SMTP Hostinger — PRD 24.1)
            </h3>
            <div class="mt-4 space-y-3">
                @foreach([
                    'email_security' => 'Email Keamanan (Security)',
                    'email_notification' => 'Email Notifikasi Sistem',
                    'email_cs' => 'Email Customer Service',
                ] as $k => $lbl)
                    <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3">
                        <label class="text-sm text-ink/80" for="setting-{{ $k }}">{{ $lbl }}</label>
                        <div class="md:col-span-2">
                            <input type="text" name="settings[{{ $k }}]" id="setting-{{ $k }}"
                                   value="{{ $sv($k) }}"
                                   class="w-full rounded-lg border border-rule bg-white px-3 py-2.5 text-sm text-ink">
                        </div>
                    </div>
                @endforeach
                <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3">
                    <label class="text-sm text-ink/80" for="setting-notif_admin_bukti_bayar">Notif Admin Bukti Bayar</label>
                    <div class="md:col-span-2">
                        <select name="settings[notif_admin_bukti_bayar]" id="setting-notif_admin_bukti_bayar"
                                class="w-full rounded-lg border border-rule bg-white px-3 py-2.5 text-sm text-ink">
                            <option value="1" @selected($sv('notif_admin_bukti_bayar') === '1')>Aktif</option>
                            <option value="0" @selected($sv('notif_admin_bukti_bayar') === '0')>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Generic Email settings --}}
        @php
            $skipEmailKeys = ['customer_emails_paused','email_stop_reason','email_security','email_notification','email_cs','notif_admin_bukti_bayar'];
            $genericEmail = collect($sections['Email'] ?? [])->filter(function ($s) use ($skipEmailKeys) {
                return !in_array($s->key, $skipEmailKeys);
            });
        @endphp
        @if($genericEmail->count())
            <div class="mt-5 space-y-3">
                @foreach ($genericEmail as $setting)
                    @php
                        $unit = $unitFor($setting->key);
                        $label = trim($setting->description ?: str_replace('_', ' ', ucwords($setting->key, '_')), '. ');
                    @endphp
                    <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3">
                        <label class="text-sm text-ink/80" for="setting-{{ $setting->key }}">
                            {{ $label }}{{ $unit !== '' ? ' (' . $unit . ')' : '' }}
                        </label>
                        <div class="md:col-span-2">
                            @if ($setting->type === 'bool')
                                <select name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                        class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                    <option value="1" @selected($setting->value === '1')>Aktif</option>
                                    <option value="0" @selected($setting->value === '0')>Nonaktif</option>
                                </select>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                            @endif
                            @error('settings.'.$setting->key)<p class="text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-6 flex gap-3">
            <button type="button" id="btn-test-email"
                    class="rounded-lg border border-rule bg-white px-5 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">
                <i class="bi bi-send-check me-1.5" style="font-size:0.85rem;"></i> Kirim Email Test ke Admin
            </button>
            <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                <i class="bi bi-check-lg me-1.5" style="font-size:0.85rem;"></i> Simpan Pengaturan
            </button>
        </div>
    </div>

    {{-- ═══ TAB: Security — DS 15.3 ═══ --}}
    <div class="settings-panel rounded-xl border border-rule bg-white p-6" data-settings-panel="Security" style="display:none">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
            <i class="bi bi-shield-check me-1.5" style="font-size:0.85rem;"></i>
            Proteksi Hak Cipta Foto & Auto-Watermark
        </h2>
        <p class="mt-1 text-xs text-ink/55">Bebas copy teks — hanya gambar yang dilindungi.</p>

        <div class="mt-5 space-y-5">
            <div class="flex items-start gap-3 rounded-lg border border-rule bg-paper/30 p-4">
                <input type="checkbox" name="settings[anti_download_enabled]" id="setting-anti_download_enabled"
                       value="1" @checked($sv('anti_download_enabled') === '1')
                       class="mt-0.5 h-4 w-4 rounded border-rule">
                <div>
                    <label for="setting-anti_download_enabled" class="cursor-pointer text-sm font-medium text-ink">
                        Aktifkan Proteksi Anti-Drag & Anti-Download Foto
                    </label>
                    <p class="mt-1 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        Memblokir Klik-Kanan (Save Image As), drag foto, dan long-press save di HP khusus pada elemen gambar. Pengunjung TETAP BISA copy-paste kalimat/teks seperti biasa.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-rule bg-paper/30 p-4">
                <input type="checkbox" name="settings[auto_watermark_enabled]" id="setting-auto_watermark_enabled"
                       value="1" @checked($sv('auto_watermark_enabled') === '1')
                       class="mt-0.5 h-4 w-4 rounded border-rule">
                <div>
                    <label for="setting-auto_watermark_enabled" class="cursor-pointer text-sm font-medium text-ink">
                        Aktifkan Server-Side Auto-Watermark
                        <span class="ml-1 text-[10px] font-normal text-ink/45">(Intervention Image v3)</span>
                    </label>
                    <p class="mt-1 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        Menempatkan watermark langsung ke file gambar publik agar tetap ber-watermark walau diambil via Screenshot / Inspect.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-ink" for="setting-watermark_text">Teks / Logo Watermark</label>
                    <input type="text" name="settings[watermark_text]" id="setting-watermark_text"
                           value="{{ $sv('watermark_text') ?: 'MERCATORIA · mercatoria.id' }}"
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink" for="setting-watermark_pattern">Pola Watermark</label>
                    <select name="settings[watermark_pattern]" id="setting-watermark_pattern"
                            class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                        <option value="diagonal_tiled" @selected($sv('watermark_pattern') === 'diagonal_tiled')>Diagonal Tiled (Berulang Miring)</option>
                        <option value="center" @selected($sv('watermark_pattern') === 'center')>Center (Tengah)</option>
                        <option value="bottom_right" @selected($sv('watermark_pattern') === 'bottom_right')>Bottom Right (Kanan Bawah)</option>
                        <option value="corner_4" @selected($sv('watermark_pattern') === 'corner_4')>4 Sudut</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink" for="setting-watermark_opacity">Opacity Watermark (%)</label>
                    <input type="text" name="settings[watermark_opacity]" id="setting-watermark_opacity"
                           value="{{ $sv('watermark_opacity') ?: '25' }}"
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                </div>
                <div class="flex items-end pb-1">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="settings[heavy_watermark]" id="setting-heavy_watermark"
                               value="1" @checked($sv('heavy_watermark') === '1')
                               class="h-4 w-4 rounded border-rule">
                        <label for="setting-heavy_watermark" class="cursor-pointer text-sm text-ink">
                            Heavy watermark jika URL dibuka langsung di tab baru
                        </label>
                    </div>
                </div>
            </div>
        </div>

        @php
            $skipSecKeys = ['anti_download_enabled','auto_watermark_enabled','watermark_text','watermark_pattern','watermark_opacity','heavy_watermark'];
            $genericSec = collect($sections['Security'] ?? [])->filter(function ($s) use ($skipSecKeys) {
                return !in_array($s->key, $skipSecKeys);
            });
        @endphp
        @if($genericSec->count())
            <div class="mt-5 space-y-3 border-t border-rule pt-4">
                @foreach ($genericSec as $setting)
                    @php
                        $unit = $unitFor($setting->key);
                        $label = trim($setting->description ?: str_replace('_', ' ', ucwords($setting->key, '_')), '. ');
                    @endphp
                    <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3">
                        <label class="text-sm text-ink/80" for="setting-{{ $setting->key }}">
                            {{ $label }}{{ $unit !== '' ? ' (' . $unit . ')' : '' }}
                        </label>
                        <div class="md:col-span-2">
                            @if ($setting->type === 'bool')
                                <select name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                        class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                    <option value="1" @selected($setting->value === '1')>Aktif</option>
                                    <option value="0" @selected($setting->value === '0')>Nonaktif</option>
                                </select>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                            @endif
                            @error('settings.'.$setting->key)<p class="text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-6">
            <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                <i class="bi bi-check-lg me-1.5" style="font-size:0.85rem;"></i> Simpan Pengaturan
            </button>
        </div>
    </div>

</form>

{{-- ═══ MODAL: Kill Switch ═══ --}}
<div id="modal-kill-switch" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                <i class="bi bi-exclamation-triangle text-[#E53935]" style="font-size:1.35rem;"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-semibold text-ink">Hentikan Pengiriman Email?</h3>
                <p class="mt-0.5 text-xs text-ink/55">Konfirmasi diperlukan sebelum mengaktifkan kill-switch.</p>
            </div>
        </div>
        <div class="px-8 py-6">
            <p class="text-sm leading-relaxed text-ink/80">
                Yakin ingin menghentikan sementara pengiriman email ke seluruh customer?
            </p>
            <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50/50 p-4">
                <p class="flex items-start gap-2.5 text-xs leading-relaxed text-ink/70">
                    <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.9rem;"></i>
                    Sistem tetap berjalan normal. Log email tetap dicatat sebagai <code class="mx-0.5 rounded bg-white px-1.5 py-0.5 font-mono text-[11px]">[SUPPRESSED]</code>.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-rule px-8 py-5">
            <button type="button" id="modal-kill-cancel"
                    class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">
                Batal
            </button>
            <button type="button" id="modal-kill-confirm"
                    class="rounded-lg bg-[#E53935] px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#C62828]">
                <i class="bi bi-megaphone-fill me-1.5" style="font-size:0.85rem;"></i>
                Ya, Hentikan Email
            </button>
        </div>
    </div>
</div>

{{-- ═══ MODAL: Kirim Email Test ═══ --}}
<div id="modal-test-email" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                <i class="bi bi-send-check text-blue-600" style="font-size:1.35rem;"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-semibold text-ink">Kirim Email Test</h3>
                <p class="mt-0.5 text-xs text-ink/55">Pastikan konfigurasi SMTP berfungsi dengan baik.</p>
            </div>
        </div>
        <div class="px-8 py-6">
            <p class="text-sm leading-relaxed text-ink/80">
                Email test akan dikirim ke alamat admin yang terdaftar. Pastikan Anda sudah menyimpan pengaturan SMTP terlebih dahulu.
            </p>
            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50/50 p-4">
                <p class="flex items-start gap-2.5 text-xs leading-relaxed text-ink/70">
                    <i class="bi bi-info-circle mt-0.5 text-amber-500" style="font-size:0.9rem;"></i>
                    Email test ini TIDAK terpengaruh oleh kill-switch — selalu terkirim ke admin.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-rule px-8 py-5">
            <button type="button" id="modal-test-cancel"
                    class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">
                Batal
            </button>
            <button type="button" id="modal-test-confirm"
                    class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                <i class="bi bi-send me-1.5" style="font-size:0.85rem;"></i>
                Kirim Sekarang
            </button>
        </div>
    </div>
</div>

{{-- ═══ MODAL: Info / Alert ═══ --}}
<div id="modal-info" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-md rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-6">
            <div id="modal-info-icon-wrap" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                <i id="modal-info-icon" class="bi bi-info-circle text-blue-600" style="font-size:1.35rem;"></i>
            </div>
            <h3 id="modal-info-title" class="font-display text-lg font-semibold text-ink">Informasi</h3>
        </div>
        <div class="px-8 py-6">
            <p id="modal-info-message" class="text-sm leading-relaxed text-ink/80"></p>
        </div>
        <div class="flex items-center justify-end border-t border-rule px-8 py-5">
            <button type="button" id="modal-info-close"
                    class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    // ── Tab Switcher ──
    var tabs = document.querySelectorAll('.settings-tab');
    var panels = document.querySelectorAll('[data-settings-panel]');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) {
                t.classList.remove('border-ink', 'font-semibold', 'text-ink');
                t.classList.add('border-transparent', 'text-ink/55');
            });
            tab.classList.remove('border-transparent', 'text-ink/55');
            tab.classList.add('border-ink', 'font-semibold', 'text-ink');
            var key = tab.getAttribute('data-settings-tab');
            panels.forEach(function (p) {
                p.style.display = p.getAttribute('data-settings-panel') === key ? '' : 'none';
            });
        });
    });

    // ── Modal Helpers ──
    function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
        document.body.style.overflow = '';
    }

    ['modal-kill-switch', 'modal-test-email', 'modal-info'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', function (e) {
                if (e.target === el) closeModal(id);
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal('modal-kill-switch');
            closeModal('modal-test-email');
            closeModal('modal-info');
        }
    });

    // ── [FIX #3] Kill Switch — toggle visual + warning update ──
    var killSwitch = document.getElementById('kill-switch');
    var killLabel = document.getElementById('kill-label-text');
    var killWarning = document.getElementById('kill-warning');

    function updateKillVisual() {
        if (!killSwitch) return;
        var checked = killSwitch.checked;
        if (killLabel) {
            killLabel.textContent = checked ? 'AKTIF — Email Dihentikan' : 'NONAKTIF — Normal';
            killLabel.className = 'whitespace-nowrap text-sm font-medium ' + (checked ? 'text-[#E53935]' : 'text-[#43A047]');
        }
        if (killWarning) {
            killWarning.classList.toggle('hidden', !checked);
        }
    }

    if (killSwitch) {
        killSwitch.addEventListener('change', function (e) {
            if (e.target.checked) {
                e.target.checked = false; // Reset dulu
                openModal('modal-kill-switch');
            } else {
                updateKillVisual(); // Uncheck = matikan kill switch
            }
        });
    }

    var killConfirm = document.getElementById('modal-kill-confirm');
    if (killConfirm) {
        killConfirm.addEventListener('click', function () {
            if (killSwitch) {
                killSwitch.checked = true;
                updateKillVisual();
            }
            closeModal('modal-kill-switch');
        });
    }

    var killCancel = document.getElementById('modal-kill-cancel');
    if (killCancel) {
        killCancel.addEventListener('click', function () {
            closeModal('modal-kill-switch');
        });
    }

    // ── [FIX #1] Test Email — actual fetch ke endpoint ──
    var btnTestEmail = document.getElementById('btn-test-email');
    var testConfirm = document.getElementById('modal-test-confirm');
    var testCancel = document.getElementById('modal-test-cancel');

    if (btnTestEmail) {
        btnTestEmail.addEventListener('click', function () {
            openModal('modal-test-email');
        });
    }

    if (testCancel) {
        testCancel.addEventListener('click', function () {
            closeModal('modal-test-email');
        });
    }

    if (testConfirm) {
        testConfirm.addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            fetch('{{ route('office.settings.test-email') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                closeModal('modal-test-email');
                if (res.ok) {
                    showInfo('Email Test Terkirim', res.data.message || 'Email test telah dikirim ke alamat admin. Silakan cek inbox / folder spam.', 'success');
                } else {
                    showInfo('Gagal', res.data.message || 'Gagal mengirim email test. Periksa konfigurasi SMTP.', 'error');
                }
            })
            .catch(function () {
                closeModal('modal-test-email');
                showInfo('Error', 'Terjadi kesalahan jaringan. Coba lagi.', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-1.5" style="font-size:0.85rem;"></i> Kirim Sekarang';
            });
        });
    }

    // ── Generic Info Modal ──
    window.showInfo = function (title, message, type) {
        var infoModal = document.getElementById('modal-info');
        var infoTitle = document.getElementById('modal-info-title');
        var infoMessage = document.getElementById('modal-info-message');
        var infoIconWrap = document.getElementById('modal-info-icon-wrap');
        var infoIcon = document.getElementById('modal-info-icon');

        if (!infoModal) return;

        infoTitle.textContent = title;
        infoMessage.textContent = message;

        infoIconWrap.className = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full';
        infoIcon.className = '';

        if (type === 'success') {
            infoIconWrap.classList.add('bg-green-100');
            infoIcon.classList.add('bi', 'bi-check-circle', 'text-green-600');
        } else if (type === 'error') {
            infoIconWrap.classList.add('bg-red-100');
            infoIcon.classList.add('bi', 'bi-exclamation-circle', 'text-red-600');
        } else if (type === 'warning') {
            infoIconWrap.classList.add('bg-amber-100');
            infoIcon.classList.add('bi', 'bi-exclamation-triangle', 'text-amber-600');
        } else {
            infoIconWrap.classList.add('bg-blue-100');
            infoIcon.classList.add('bi', 'bi-info-circle', 'text-blue-600');
        }
        infoIcon.style.fontSize = '1.2rem';

        openModal('modal-info');
    };

    var infoClose = document.getElementById('modal-info-close');
    if (infoClose) {
        infoClose.addEventListener('click', function () {
            closeModal('modal-info');
        });
    }

})();
</script>
@endsection