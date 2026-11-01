@extends('admin.layouts.admin')

@section('title', 'Metode Bayar')

@section('content')
    <style>
        .pm-row { border-bottom: 1.5px solid #f5f5f5 !important; }
        .pm-thead { border-top: 1.5px solid #f5f5f5 !important; border-bottom: 1px solid #f5f5f5 !important; }
    </style>

    @php
        $stats = [
            'total' => $methods->count(),
            'active' => $methods->where('is_active', true)->count(),
            'inactive' => $methods->where('is_active', false)->count(),
        ];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Metode Bayar</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[#43A047] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['active'] }} Aktif</span>
            @if ($stats['inactive'] > 0)
                <span class="rounded-full bg-[#9E9E9E] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['inactive'] }} Nonaktif</span>
            @endif
        </div>
    </div>

    {{-- ═══ FORM TAMBAH ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="border-b border-rule px-5 py-2">
            <h2 class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tambah Metode Bayar</h2>
        </div>

        <form method="POST" action="{{ route('office.payment-methods.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="px-5 py-4">
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                        <p class="flex items-center gap-2 text-xs font-semibold text-red-700">
                            <i class="bi bi-exclamation-circle"></i> Gagal menyimpan:
                        </p>
                        <ul class="mt-1.5 space-y-0.5 text-xs text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>&bull; {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Baris 1: Code, Nama, Tipe, Fee, Aktif, Button --}}
                <div class="flex items-end gap-3">
                    <div class="w-[110px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" required value="{{ old('code') }}" placeholder="bca"
                               class="h-11 w-full rounded-lg border border-rule px-3 font-mono text-xs text-ink">
                    </div>

                    <div class="w-[180px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}" placeholder="Bank BCA"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>

                    <div class="w-[150px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Tipe</label>
                        <select name="type" id="pm-type-select" class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                            <option value="bank_transfer" @selected(old('type') === 'bank_transfer')>Bank Transfer</option>
                            <option value="qris" @selected(old('type') === 'qris')>QR Code</option>
                        </select>
                    </div>

                    <div class="w-[120px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Fee (Rp)</label>
                        <input type="text" name="fee" value="{{ old('fee') }}" placeholder="0"
                               inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>

                    <div class="flex h-11 shrink-0 items-center">
                        <label class="flex items-center gap-1.5 text-sm text-ink/70 whitespace-nowrap">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                                   class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                            Aktif
                        </label>
                    </div>

                    <button class="h-11 shrink-0 rounded-lg bg-black px-5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                        + Tambah
                    </button>
                </div>

                {{-- Bank Transfer fields --}}
                <div id="pm-bank-fields" class="mt-3 flex items-end gap-3">
                    <div class="w-[160px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="BCA"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>
                    <div class="w-[200px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">No. Rekening</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="1234567890"
                               inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Nama Pemilik</label>
                        <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="PT Example Indonesia"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Instruksi</label>
                        <input type="text" name="instructions" value="{{ old('instructions') }}" placeholder="Transfer sesuai nominal..."
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>
                </div>

                {{-- QR Code fields --}}
                <div id="pm-qr-fields" class="mt-3 items-end gap-3" style="display:none;">
                    <div class="flex items-center gap-2">
                        <div id="pm-add-qr-preview" class="h-[72px] w-[72px] shrink-0 overflow-hidden rounded-lg border-2 border-dashed border-rule bg-paper flex items-center justify-center">
                            <i class="bi bi-qr-code text-ink/25" style="font-size:1.5rem;"></i>
                        </div>
                        <label class="flex h-11 cursor-pointer items-center gap-1.5 rounded-lg border border-rule px-3 text-xs text-ink/60 transition-colors hover:border-ink hover:text-ink">
                            <i class="bi bi-cloud-arrow-up" style="font-size:0.85rem;"></i>
                            <span class="pm-add-qr-label">Upload QR</span>
                            <input type="file" name="qr_image_path" accept=".jpg,.jpeg,.png,.webp" class="hidden" onchange="previewPmQr(this)">
                        </label>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Instruksi</label>
                        <input type="text" name="instructions" value="{{ old('instructions') }}" placeholder="Scan QR di atas untuk bayar..."
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>
                </div>

                <p class="mt-2 flex items-center gap-1 text-[10px] text-ink/40">
                    <i class="bi bi-info-circle" style="font-size:0.7rem;"></i>
                    <span id="pm-form-hint">Bank Transfer: isi data rekening &middot; QR Code: upload gambar QR &middot; Fee dalam Rupiah</span>
                </p>
            </div>
        </form>
    </div>

    {{-- ═══ BULK ACTIONS + TABLE ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-2.5">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-ink/70">
                    <input type="checkbox" id="pm-select-all" class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                    Pilih Semua
                </label>
                <span class="text-xs text-ink/45" id="pm-selected-info">0 terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="pm-bulk-save"
                        class="rounded-lg bg-black px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink disabled:opacity-40">
                    <i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan
                </button>
                <button type="button" id="pm-bulk-delete"
                        class="rounded-lg border border-danger/30 px-3.5 py-2 text-xs font-semibold text-danger transition-colors hover:border-danger hover:bg-red-50 disabled:opacity-40">
                    <i class="bi bi-trash me-1" style="font-size:0.85rem;"></i> Hapus Terpilih
                </button>
            </div>
        </div>

        @if ($methods->isNotEmpty())
            <table class="w-full table-fixed">
                <colgroup>
                    <col class="w-9">
                    <col class="w-[100px]">
                    <col class="w-[140px]">
                    <col class="w-[110px]">
                    <col class="w-[210px]">
                    <col class="w-[80px]">
                    <col>
                    <col class="w-[50px]">
                </colgroup>
                <thead>
                    <tr class="pm-thead bg-[#F8F9FA]">
                        <th class="px-2 py-2"></th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Code
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Nama
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Tipe
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Detail Rekening / QR
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Fee
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Instruksi
                        </th>
                        <th class="px-2 py-2 text-center font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Aktif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($methods as $method)
                        <tr class="pm-row transition-colors hover:bg-paper/15" data-pm-row data-type="{{ $method->type }}">
                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" class="pm-checkbox h-4 w-4 rounded border-rule" data-id="{{ $method->id }}" style="accent-color:#43A047">
                            </td>

                            {{-- Code --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ $method->code }}" data-field="code" data-orig="{{ $method->code }}"
                                       class="w-full px-2.5 py-1.5 font-mono text-xs text-ink/75"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Nama --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ $method->name }}" data-field="name" data-orig="{{ $method->name }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Tipe --}}
                            <td class="px-2 py-3">
                                <select data-field="type" data-orig="{{ $method->type }}" onchange="togglePmRowFields(this)"
                                        class="w-full px-2 py-1.5 text-sm text-ink"
                                        style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                        onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                        onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                                    <option value="bank_transfer" @selected($method->type === 'bank_transfer')>Bank Transfer</option>
                                    <option value="qris" @selected($method->type === 'qris')>QR Code</option>
                                </select>
                            </td>

                            {{-- Detail (conditional per type) --}}
                            <td class="px-2 py-3">
                                @if ($method->type === 'bank_transfer')
                                    <div class="flex flex-col gap-1">
                                        <input type="text" value="{{ $method->bank_name }}" data-field="bank_name" data-orig="{{ $method->bank_name }}" placeholder="Nama Bank"
                                               class="w-full px-2 py-1 text-[11px] text-ink/75"
                                               style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;outline:none;transition:border-color .15s;"
                                               onfocus="this.style.borderColor='#aaa'" onblur="this.style.borderColor='#f5f5f5'">
                                        <input type="text" value="{{ $method->account_number }}" data-field="account_number" data-orig="{{ $method->account_number }}" placeholder="No. Rekening"
                                               inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                               class="w-full px-2 py-1 text-[11px] text-ink/75"
                                               style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;outline:none;transition:border-color .15s;"
                                               onfocus="this.style.borderColor='#aaa'" onblur="this.style.borderColor='#f5f5f5'">
                                        <input type="text" value="{{ $method->account_name }}" data-field="account_name" data-orig="{{ $method->account_name }}" placeholder="Nama Pemilik"
                                               class="w-full px-2 py-1 text-[11px] text-ink/75"
                                               style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;outline:none;transition:border-color .15s;"
                                               onfocus="this.style.borderColor='#aaa'" onblur="this.style.borderColor='#f5f5f5'">
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded border border-rule/60 bg-paper flex items-center justify-center">
                                            @if ($method->qr_image_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($method->qr_image_path) }}" alt="QR" class="h-full w-full object-cover">
                                            @else
                                                <i class="bi bi-qr-code text-ink/20" style="font-size:0.9rem;"></i>
                                            @endif
                                        </div>
                                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-1 px-2 py-1.5 text-[9px] text-ink/55"
                                               style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;transition:border-color .15s;"
                                               onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'">
                                            <i class="bi bi-cloud-arrow-up" style="font-size:0.65rem;"></i>
                                            <span>Ganti QR</span>
                                            <input type="file" data-field="qr_image_path" accept=".jpg,.jpeg,.png,.webp" class="hidden pm-row-qr-input">
                                        </label>
                                    </div>
                                @endif
                            </td>

                            {{-- Fee --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ (int) $method->fee }}" data-field="fee" data-orig="{{ (int) $method->fee }}"
                                       inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Instruksi --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ $method->instructions }}" data-field="instructions" data-orig="{{ $method->instructions }}" placeholder="Instruksi..."
                                       class="w-full px-2.5 py-1.5 text-sm text-ink/75"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Aktif --}}
                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" data-field="is_active" data-orig="{{ $method->is_active ? '1' : '0' }}"
                                       @checked($method->is_active)
                                       class="h-4 w-4 cursor-pointer rounded border-rule" style="accent-color:#43A047">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-xs text-ink/50"><strong class="text-ink/70">{{ $methods->count() }}</strong> metode bayar</p>
                <p class="text-[10px] text-ink/35">Edit langsung di baris &rarr; Simpan Perubahan</p>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-14">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-paper">
                    <i class="bi bi-credit-card text-ink/25" style="font-size:2rem;"></i>
                </div>
                <p class="text-sm font-medium text-ink/60">Belum ada metode bayar</p>
                <p class="text-xs text-ink/40">Tambahkan metode bayar pertama di form atas.</p>
            </div>
        @endif
    </div>

    {{-- ═══ INFO ═══ --}}
    <div class="mt-5 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
        <p class="flex items-start gap-2 text-xs text-ink/65">
            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.8rem;"></i>
            Metode bayar tampil di halaman checkout. <strong class="mx-0.5">Bank Transfer</strong> butuh data rekening,
            <strong class="mx-0.5">QR Code</strong> butuh gambar QR. Metode yang sudah dipakai order tidak bisa dihapus.
        </p>
    </div>

    {{-- ═══ MODAL ═══ --}}
    <div id="pm-modal" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.35);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:360px;box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="pm-modal-icon" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0;"></div>
                <div>
                    <p id="pm-modal-title" style="font-size:14px;font-weight:600;color:#222;margin:0;">Berhasil</p>
                    <p id="pm-modal-text" style="font-size:12px;color:#888;margin:4px 0 0 0;"></p>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
                <button id="pm-modal-cancel" style="display:none;padding:8px 16px;border-radius:8px;border:1px solid #e0e0e0;background:#fff;font-size:12px;font-weight:600;color:#666;cursor:pointer;">Batal</button>
                <button id="pm-modal-ok" style="padding:8px 20px;border-radius:8px;border:none;background:#111;color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Mengerti</button>
            </div>
        </div>
    </div>

    {{-- CONFIG --}}
    @php
        $pmCfg = ['csrf' => csrf_token(), 'baseUrl' => route('office.payment-methods.index')];
    @endphp
    <script type="application/json" id="pm-page-config">{!! json_encode($pmCfg) !!}</script>

    <script>
    (function () {
        var config = JSON.parse(document.getElementById('pm-page-config').textContent);

        // ── Toggle bank/QR fields di form tambah ──
        var typeSelect = document.getElementById('pm-type-select');
        var bankFields = document.getElementById('pm-bank-fields');
        var qrFields = document.getElementById('pm-qr-fields');

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                if (this.value === 'bank_transfer') {
                    bankFields.style.display = 'flex';
                    qrFields.style.display = 'none';
                } else {
                    bankFields.style.display = 'none';
                    qrFields.style.display = 'flex';
                }
            });
        }

        // ── Preview QR upload di form tambah ──
        window.previewPmQr = function (input) {
            var preview = document.getElementById('pm-add-qr-preview');
            var label = input.closest('label').querySelector('.pm-add-qr-label');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="h-full w-full object-cover" alt="">';
                };
                reader.readAsDataURL(input.files[0]);
                if (label) label.textContent = input.files[0].name.substring(0, 10) + '..';
            } else {
                preview.innerHTML = '<i class="bi bi-qr-code text-ink/25" style="font-size:1.5rem;"></i>';
                if (label) label.textContent = 'Upload QR';
            }
        };

        // ── Toggle row fields saat ganti tipe ──
        window.togglePmRowFields = function (select) {
            var row = select.closest('[data-pm-row]');
            if (!row) return;
            var detailCell = select.closest('tr').children[4];
            if (!detailCell) return;

            if (select.value === 'bank_transfer') {
                detailCell.innerHTML = '<div class="flex flex-col gap-1">' +
                    '<input type="text" data-field="bank_name" data-orig="" placeholder="Nama Bank" class="w-full px-2 py-1 text-[11px] text-ink/75" style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;outline:none;">' +
                    '<input type="text" data-field="account_number" data-orig="" placeholder="No. Rekening" inputmode="numeric" class="w-full px-2 py-1 text-[11px] text-ink/75" style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;outline:none;">' +
                    '<input type="text" data-field="account_name" data-orig="" placeholder="Nama Pemilik" class="w-full px-2 py-1 text-[11px] text-ink/75" style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;outline:none;">' +
                    '</div>';
            } else {
                detailCell.innerHTML = '<div class="flex items-center gap-2">' +
                    '<div class="h-10 w-10 shrink-0 overflow-hidden rounded border border-rule/60 bg-paper flex items-center justify-center"><i class="bi bi-qr-code text-ink/20" style="font-size:0.9rem;"></i></div>' +
                    '<label class="inline-flex shrink-0 cursor-pointer items-center gap-1 px-2 py-1.5 text-[9px] text-ink/55" style="border:2px solid #f5f5f5;background:#fff;border-radius:5px;">' +
                    '<i class="bi bi-cloud-arrow-up" style="font-size:0.65rem;"></i><span>Ganti QR</span>' +
                    '<input type="file" data-field="qr_image_path" accept=".jpg,.jpeg,.png,.webp" class="hidden pm-row-qr-input">' +
                    '</label></div>';
            }
        };

        // ═══ MODAL HELPER ═══
        var modalEl = document.getElementById('pm-modal');
        var modalIcon = document.getElementById('pm-modal-icon');
        var modalTitle = document.getElementById('pm-modal-title');
        var modalText = document.getElementById('pm-modal-text');
        var modalOk = document.getElementById('pm-modal-ok');
        var modalCancel = document.getElementById('pm-modal-cancel');

        function showPmModal(type, title, text, onOk, showCancel) {
            if (type === 'success') {
                modalIcon.style.background = '#f0fdf4';
                modalIcon.innerHTML = '<i class="bi bi-check-lg" style="font-size:1.3rem;color:#43A047;"></i>';
            } else if (type === 'error') {
                modalIcon.style.background = '#fef2f2';
                modalIcon.innerHTML = '<i class="bi bi-x-lg" style="font-size:1.15rem;color:#ef4444;"></i>';
            } else {
                modalIcon.style.background = '#fffbeb';
                modalIcon.innerHTML = '<i class="bi bi-info-lg" style="font-size:1.15rem;color:#f59e0b;"></i>';
            }

            modalTitle.textContent = title;
            modalText.textContent = text;

            if (showCancel) {
                modalCancel.style.display = 'inline-block';
                modalOk.textContent = 'Ya, Hapus';
                modalOk.style.background = '#dc2626';
            } else {
                modalCancel.style.display = 'none';
                modalOk.textContent = 'Mengerti';
                modalOk.style.background = '#111';
            }

            modalOk.onclick = function () {
                closePmModal();
                if (onOk) onOk();
            };
            modalCancel.onclick = function () {
                closePmModal();
            };

            modalEl.style.display = 'flex';
        }

        window.closePmModal = function () {
            modalEl.style.display = 'none';
        };

        // ── Clear all selections ──
        var selectAll = document.getElementById('pm-select-all');

        function clearSelections() {
            document.querySelectorAll('.pm-checkbox').forEach(function (cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            var selInfo = document.getElementById('pm-selected-info');
            if (selInfo) selInfo.textContent = '0 terpilih';
        }

        // ── Counter ──
        var info = document.getElementById('pm-selected-info');

        function updateCounter() {
            if (!info) return;
            info.textContent = document.querySelectorAll('.pm-checkbox:checked').length + ' terpilih';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.pm-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
                updateCounter();
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('pm-checkbox')) updateCounter();
        });

        // ── Bulk save ──
        var saveBtn = document.getElementById('pm-bulk-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var rows = document.querySelectorAll('[data-pm-row]');
                var pending = [];

                rows.forEach(function (row) {
                    var fields = {}; var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        if (el.type === 'file') {
                            if (el.files && el.files.length) changed = true;
                            return;
                        }
                        var val = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
                        if (val !== el.getAttribute('data-orig')) changed = true;
                        fields[el.getAttribute('data-field')] = val;
                    });
                    if (changed) {
                        pending.push({ row: row, id: row.querySelector('.pm-checkbox').getAttribute('data-id'), fields: fields });
                    }
                });

                if (!pending.length) {
                    showPmModal('info', 'Tidak ada perubahan', 'Semua data masih sama. Tidak ada yang perlu disimpan.');
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1">&#10227;</span> Menyimpan...';
                var done = 0, success = 0, fail = 0;

                pending.forEach(function (item) {
                    var form = new FormData();
                    form.append('_token', config.csrf);
                    form.append('_method', 'PUT');
                    form.append('code', item.fields.code || '');
                    form.append('name', item.fields.name || '');
                    form.append('type', item.fields.type || 'bank_transfer');
                    form.append('fee', item.fields.fee || '0');
                    form.append('bank_name', item.fields.bank_name || '');
                    form.append('account_number', item.fields.account_number || '');
                    form.append('account_name', item.fields.account_name || '');
                    form.append('instructions', item.fields.instructions || '');
                    form.append('is_active', item.fields.is_active || '0');

                    // QR file (kalau ada upload baru)
                    var qrInput = item.row.querySelector('.pm-row-qr-input');
                    if (qrInput && qrInput.files && qrInput.files.length) {
                        form.append('qr_image_path', qrInput.files[0]);
                    }

                    fetch(config.baseUrl + '/' + item.id, {
                        method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                    })
                    .then(function (r) {
                        if (r.ok) {
                            success++;
                            item.row.querySelectorAll('[data-field]').forEach(function (el) {
                                if (el.type === 'file') { el.value = ''; return; }
                                el.setAttribute('data-orig', el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value);
                            });
                        } else {
                            fail++;
                        }
                    })
                    .catch(function () { fail++; })
                    .finally(function () {
                        done++;
                        if (done === pending.length) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan';
                            clearSelections();

                            if (fail === 0) {
                                showPmModal('success', 'Berhasil disimpan', success + ' metode bayar berhasil diperbarui.');
                            } else if (success === 0) {
                                showPmModal('error', 'Gagal menyimpan', 'Semua ' + fail + ' metode bayar gagal disimpan. Coba lagi.');
                            } else {
                                showPmModal('info', 'Sebagian berhasil', success + ' berhasil, ' + fail + ' gagal disimpan.');
                            }
                        }
                    });
                });
            });
        }

        // ── Bulk delete ──
        var delBtn = document.getElementById('pm-bulk-delete');
        if (delBtn) {
            delBtn.addEventListener('click', function () {
                var checked = document.querySelectorAll('.pm-checkbox:checked');
                if (!checked.length) {
                    showPmModal('info', 'Belum ada yang dipilih', 'Centang metode bayar yang mau dihapus terlebih dahulu.');
                    return;
                }

                showPmModal('info', 'Konfirmasi hapus', 'Hapus ' + checked.length + ' metode bayar terpilih? Metode yang sudah dipakai order akan ditolak.', function () {
                    delBtn.disabled = true;
                    var done = 0, deleted = 0, rejected = 0;

                    checked.forEach(function (cb) {
                        var form = new FormData();
                        form.append('_token', config.csrf);
                        form.append('_method', 'DELETE');

                        fetch(config.baseUrl + '/' + cb.getAttribute('data-id'), {
                            method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                        })
                        .then(function (r) {
                            if (r.ok) {
                                deleted++;
                                var tr = cb.closest('[data-pm-row]');
                                if (tr) tr.remove();
                            } else {
                                rejected++;
                            }
                        })
                        .catch(function () { rejected++; })
                        .finally(function () {
                            done++;
                            if (done === checked.length) {
                                delBtn.disabled = false;
                                clearSelections();

                                if (rejected === 0) {
                                    showPmModal('success', 'Berhasil dihapus', deleted + ' metode bayar berhasil dihapus.');
                                } else if (deleted === 0) {
                                    showPmModal('error', 'Gagal dihapus', 'Metode yang dipilih tidak bisa dihapus (masih dipakai order).');
                                } else {
                                    showPmModal('info', 'Sebagian terhapus', deleted + ' terhapus, ' + rejected + ' ditolak.');
                                }
                            }
                        });
                    });
                }, true);
            });
        }
    })();
    </script>
@endsection