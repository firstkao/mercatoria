@extends('admin.layouts.admin')

@section('title', 'Log Sistem')

@section('content')
@php
    // ── Tentukan tab aktif dari ROUTE NAME, bukan query param ──
    $routeName = request()->route()->getName();
    $currentTab = match(true) {
        str_contains($routeName, 'activity') => 'activity-logs',
        str_contains($routeName, 'auth')     => 'auth-logs',
        default                              => 'admin-logs',
    };

    // ── Route mapping untuk navigasi + reset filter ──
    $tabRoutes = [
        'activity-logs' => 'office.activity-logs',
        'auth-logs'     => 'office.auth-logs',
        'admin-logs'    => 'office.admin-logs',
    ];
    $currentRoute = $tabRoutes[$currentTab] ?? 'office.admin-logs';

    // ── Mapping field → label manusia ──
    $fieldLabels = [
        'name' => 'Nama', 'email' => 'Email', 'slug' => 'Slug',
        'is_active' => 'Status', 'price' => 'Harga', 'price_rmb' => 'Harga RMB',
        'code' => 'Kode', 'subject' => 'Subjek', 'body' => 'Isi',
        'description' => 'Deskripsi', 'role' => 'Role', 'title' => 'Judul',
        'content' => 'Konten', 'image' => 'Gambar', 'image_path' => 'Gambar',
        'logo_path' => 'Logo', 'url' => 'URL', 'type' => 'Tipe',
        'fee' => 'Fee', 'instructions' => 'Instruksi', 'no_hp' => 'No. HP',
        'address' => 'Alamat', 'weight' => 'Berat', 'weight_gram' => 'Berat (g)',
        'stock' => 'Stok', 'sku' => 'SKU', 'sort_order' => 'Urutan',
        'meta_description' => 'Meta Deskripsi', 'discount_nominal' => 'Diskon (Rp)',
        'ongkir_lokal_cn' => 'Ongkir Lokal CN', 'ongkir_cn_btm' => 'Ongkir CN-BTM',
        'ongkir_btm_jkt' => 'Ongkir BTM-JKT', 'berat' => 'Berat',
        'ketersediaan' => 'Ketersediaan', 'store_id' => 'Store',
        'game_id' => 'Game', 'developer_id' => 'Developer',
        'category_id' => 'Kategori', 'product_id' => 'Produk',
        'variant_id' => 'Varian', 'user_id' => 'User', 'admin_id' => 'Admin',
        'order_id' => 'Order', 'max_purchase' => 'Maks. Beli',
        'min_purchase' => 'Min. Beli', 'min_belanja_rmb' => 'Min Belanja RMB',
        'ongkir_rmb' => 'Ongkir RMB', 'start_date' => 'Mulai',
        'end_date' => 'Berakhir', 'discount_amount' => 'Nominal Diskon',
        'discount_percent' => 'Diskon (%)', 'is_featured' => 'Unggulan',
        'notes' => 'Catatan', 'reason' => 'Alasan',
        'reject_reason' => 'Alasan Tolak',
        'description_image_path' => 'Gambar Deskripsi',
        'default_store_id' => 'Store Default',
        'payment_method_id' => 'Metode Bayar', 'no_order' => 'No. Order',
        'total' => 'Total', 'status' => 'Status',
        'customer_note' => 'Catatan Pembeli', 'admin_note' => 'Catatan Admin',
        'ongkir' => 'Ongkir', 'discount' => 'Diskon',
        'coin_used' => 'Koin Dipakai', 'voucher_code' => 'Kode Voucher',
        'spammer_id' => 'Spammer', 'ip_address' => 'IP',
        'email_attempt' => 'Email', 'value' => 'Nilai',
        'valid_days' => 'Masa Berlaku (hari)', 'nominal' => 'Nominal',
        'min_belanja' => 'Min. Belanja',
        'keywords' => 'Keyword', 'priority' => 'Prioritas',
    ];

    // ── Mapping aksi → label + warna ──
    $actionLabels = [
        'create'       => ['label' => 'Tambah',         'color' => 'green'],
        'store'        => ['label' => 'Tambah',         'color' => 'green'],
        'update'       => ['label' => 'Ubah',           'color' => 'amber'],
        'edit'         => ['label' => 'Ubah',           'color' => 'amber'],
        'delete'       => ['label' => 'Hapus',          'color' => 'red'],
        'destroy'      => ['label' => 'Hapus',          'color' => 'red'],
        'bulk_update'  => ['label' => 'Ubah Massal',    'color' => 'amber'],
        'bulk_delete'  => ['label' => 'Hapus Massal',   'color' => 'red'],
        'login'        => ['label' => 'Masuk',          'color' => 'green'],
        'login_failed' => ['label' => 'Gagal Masuk',    'color' => 'red'],
        'logout'       => ['label' => 'Keluar',         'color' => 'gray'],
        'force_delete' => ['label' => 'Hapus Permanen', 'color' => 'red'],
        'issue'        => ['label' => 'Terbitkan',      'color' => 'green'],
        'revoke'       => ['label' => 'Cabut',          'color' => 'red'],
    ];

    $colorMap = [
        'green' => 'bg-[#E8F5E9] text-[#2E7D32] border-[#C8E6C9]',
        'red'   => 'bg-[#FDECEA] text-[#9E2B25] border-[#F5C6C2]',
        'amber' => 'bg-[#FFF8E1] text-[#8D6E00] border-[#F5E6A3]',
        'blue'  => 'bg-[#E8F0FE] text-[#1A56A8] border-[#C5D9F7]',
        'gray'  => 'bg-[#F5F2EC] text-[#57534E] border-[#D8D2C4]',
    ];

    // ── Format value → teks manusia ──
    $fmtVal = function ($v) {
        if (is_bool($v)) return $v ? 'Aktif' : 'Nonaktif';
        if (is_null($v)) return '—';
        return (string) $v;
    };

    // ── Format diff → perbandingan manusia ──
    $fmtDiff = function ($old, $new) use ($fieldLabels, $fmtVal) {
        if (is_null($old) && is_null($new)) return ['old' => '', 'new' => '', 'type' => 'none'];
        if (is_null($old)) return ['old' => '', 'new' => 'Data baru dibuat', 'type' => 'created'];
        if (is_null($new)) return ['old' => '', 'new' => 'Data dihapus', 'type' => 'deleted'];

        $oldParts = [];
        $newParts = [];
        foreach ($new as $k => $v) {
            $label = $fieldLabels[$k] ?? str_replace('_', ' ', ucwords($k, '_'));
            $newParts[] = $label . ': ' . $fmtVal($v);
            $oldVal = is_array($old) && array_key_exists($k, $old) ? $old[$k] : null;
            $oldParts[] = $label . ': ' . $fmtVal($oldVal);
        }
        return [
            'old'  => implode(' | ', $oldParts),
            'new'  => implode(' | ', $newParts),
            'type' => 'changed',
        ];
    };

    // ── Model label → nama manusia ──
    $modelLabels = [
        'Game'           => 'Game',          'Developer'      => 'Developer',
        'Store'          => 'Store',         'Product'        => 'Produk',
        'Setting'        => 'Pengaturan',    'Page'           => 'Halaman CMS',
        'Tag'            => 'Tag',           'PaymentMethod'  => 'Metode Bayar',
        'User'           => 'User',          'Order'          => 'Pesanan',
        'Voucher'        => 'Voucher',       'MerchCategory'  => 'Kategori Merch',
        'EmailTemplate'  => 'Template Email','HeroSlide'      => 'Hero Slide',
        'Spammer'        => 'Spammer',       'Refund'         => 'Refund',
    ];
@endphp

{{-- Header --}}
<div class="flex items-end justify-between">
    <div>
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Keamanan</p>
        <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Log Sistem</h1>
        <p class="mt-1 text-sm text-ink/55">Retensi otomatis 30 hari &mdash; Cron 03:00 WIB</p>
    </div>
    @if(Route::has('office.logs.export'))
        <a href="{{ route('office.logs.export', ['tab' => $currentTab]) }}"
           class="rounded-lg border border-rule bg-white px-4 py-2 text-sm font-medium text-ink transition-colors hover:bg-paper">
            <i class="bi bi-download me-1.5" style="font-size:0.85rem;"></i> Export CSV
        </a>
    @endif
</div>

{{-- Tab Menu: Activity / Auth / Admin --}}
<div class="mt-5 flex gap-1 rounded-lg border border-rule bg-white p-1">
    @if(Route::has('office.activity-logs'))
        <a href="{{ route('office.activity-logs') }}"
           class="flex-1 rounded-md px-4 py-2 text-center text-sm font-medium transition-colors
                  {{ $currentTab === 'activity-logs' ? 'bg-ink text-white' : 'text-ink/65 hover:bg-paper' }}">
            <i class="bi bi-activity me-1.5" style="font-size:0.85rem;"></i> Activity Log
        </a>
    @endif
    @if(Route::has('office.auth-logs'))
        <a href="{{ route('office.auth-logs') }}"
           class="flex-1 rounded-md px-4 py-2 text-center text-sm font-medium transition-colors
                  {{ $currentTab === 'auth-logs' ? 'bg-ink text-white' : 'text-ink/65 hover:bg-paper' }}">
            <i class="bi bi-shield-lock me-1.5" style="font-size:0.85rem;"></i> Auth Log
        </a>
    @endif
    @if(Route::has('office.admin-logs'))
        <a href="{{ route('office.admin-logs') }}"
           class="flex-1 rounded-md px-4 py-2 text-center text-sm font-medium transition-colors
                  {{ $currentTab === 'admin-logs' ? 'bg-ink text-white' : 'text-ink/65 hover:bg-paper' }}">
            <i class="bi bi-person-gear me-1.5" style="font-size:0.85rem;"></i> Admin Log
        </a>
    @endif
</div>

{{-- Filter Bar --}}
<div class="mt-4 flex flex-wrap items-center gap-2">
    <form method="GET" action="{{ route($currentRoute) }}" class="flex flex-1 flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari user / IP / aksi..."
               class="w-64 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink placeholder-ink/35 focus:outline-none focus:ring-2 focus:ring-accent/45">

        <select name="action"
                class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            <option value="">Semua Aksi</option>
            @foreach ($actionLabels as $key => $a)
                <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $a['label'] }}</option>
            @endforeach
        </select>

        <select name="date"
                class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            <option value="">Semua Waktu</option>
            <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="week"  {{ request('date') === 'week'  ? 'selected' : '' }}>7 Hari</option>
            <option value="month" {{ request('date') === 'month' ? 'selected' : '' }}>30 Hari</option>
        </select>

        <button type="submit"
                class="rounded-lg bg-ink px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-ink/85">
            <i class="bi bi-funnel me-1" style="font-size:0.85rem;"></i> Filter
        </button>

        @if(request()->hasAny(['search', 'action', 'date']))
            <a href="{{ route($currentRoute) }}"
               class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink/60 transition-colors hover:bg-paper hover:text-ink">
                <i class="bi bi-x-circle me-1"></i> Reset
            </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="mt-4 overflow-x-auto rounded-xl border border-rule bg-white">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-rule bg-paper">
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Waktu (WIB)</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Admin</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Aksi</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Model</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Keterangan</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Perubahan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-rule/50">
            @forelse ($logs as $log)
                @php
                    $action = $actionLabels[$log->action] ?? [
                        'label' => ucwords(str_replace(['_', '-'], ' ', $log->action)),
                        'color' => 'gray',
                    ];
                    $color      = $colorMap[$action['color']];
                    $diff       = $fmtDiff($log->old_value, $log->new_value);
                    $modelLabel = $modelLabels[$log->model] ?? $log->model;
                    $isFailed   = str_contains($log->action, 'failed') || str_contains($log->description ?? '', 'gagal');
                @endphp
                <tr class="transition-colors {{ $isFailed ? 'bg-red-50/50' : '' }} hover:bg-paper/60">
                    {{-- Waktu --}}
                    <td class="whitespace-nowrap px-4 py-3">
                        <p class="text-xs font-medium text-ink">
                            {{ $log->created_at->translatedFormat('d M Y') }}
                        </p>
                        <p class="font-mono text-[11px] text-ink/45">
                            {{ $log->created_at->format('H:i') }} WIB
                        </p>
                    </td>

                    {{-- Admin --}}
                    <td class="px-4 py-3">
                        @if($log->admin ?? null)
                            <p class="text-sm font-medium text-ink">{{ $log->admin->name }}</p>
                            <p class="text-xs text-ink/45">{{ $log->admin->email }}</p>
                        @else
                            <span class="font-mono text-xs text-ink/35">&mdash;</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $color }}">
                            {{ $action['label'] }}
                        </span>
                    </td>

                    {{-- Model --}}
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-ink">{{ $modelLabel }}</span>
                        @if($log->model_id)
                            <span class="ms-1 font-mono text-[11px] text-ink/40">#{{ $log->model_id }}</span>
                        @endif
                    </td>

                    {{-- Keterangan --}}
                    <td class="px-4 py-3 text-sm text-ink/70">
                        {{ $log->description ?? '—' }}
                    </td>

                    {{-- Perubahan (Old → New) --}}
                    <td class="px-4 py-3">
                        @if ($diff['type'] === 'created')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                                <i class="bi bi-plus-circle" style="font-size:0.75rem;"></i>
                                Data baru dibuat
                            </span>
                        @elseif ($diff['type'] === 'deleted')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600">
                                <i class="bi bi-trash3" style="font-size:0.75rem;"></i>
                                Data dihapus
                            </span>
                        @elseif ($diff['type'] === 'changed')
                            <div class="space-y-1">
                                <div class="flex items-start gap-1.5">
                                    <span class="mt-0.5 inline-block rounded border border-red-200 bg-red-50 px-1.5 py-0.5 font-mono text-[10px] text-red-600">
                                        {{ \Illuminate\Support\Str::limit($diff['old'], 100) }}
                                    </span>
                                    <i class="bi bi-arrow-right mt-1 text-ink/35" style="font-size:0.75rem;"></i>
                                    <span class="mt-0.5 inline-block rounded border border-green-200 bg-green-50 px-1.5 py-0.5 font-mono text-[10px] text-green-700">
                                        {{ \Illuminate\Support\Str::limit($diff['new'], 100) }}
                                    </span>
                                </div>
                                @if(strlen($diff['old']) > 100 || strlen($diff['new']) > 100)
                                    <button type="button"
                                            onclick="this.nextElementSibling.classList.toggle('hidden'); this.classList.toggle('hidden')"
                                            class="text-[11px] text-accent hover:underline">
                                        Lihat selengkapnya &rarr;
                                    </button>
                                    <div class="hidden space-y-1 rounded border border-rule bg-paper p-2">
                                        <p class="font-mono text-[10px] text-red-600">{{ $diff['old'] }}</p>
                                        <p class="font-mono text-[10px] text-green-700">{{ $diff['new'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-ink/25">&mdash;</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <i class="bi bi-journal-text text-ink/20" style="font-size:2.5rem;"></i>
                        <p class="mt-3 text-sm text-ink/45">Belum ada log tercatat.</p>
                        @if(request()->hasAny(['search', 'action', 'date']))
                            <a href="{{ route($currentRoute) }}" class="mt-2 inline-block text-sm text-accent hover:underline">
                                Reset filter
                            </a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
    <div class="mt-5">
        {{ $logs->links() }}
    </div>
@endif
@endsection