@extends('admin.layouts.admin')

@section('title', 'Admin Log')

@section('content')
    <h1 class="text-xl font-semibold">Admin Log</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2">Admin</th>
                    <th class="px-3 py-2">Aksi</th>
                    <th class="px-3 py-2">Model</th>
                    <th class="px-3 py-2">Deskripsi</th>
                    <th class="px-3 py-2">Old → New</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr class="border-b border-gray-50 align-top">
                        <td class="px-3 py-2">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2">{{ $log->admin->name ?? '-' }}</td>
                        <td class="px-3 py-2"><x-log-label :action="$log->action" /></td>
                        <td class="px-3 py-2">{{ $log->model }} #{{ $log->model_id }}</td>
                        <td class="px-3 py-2">{{ $log->description }}</td>
                        <td class="px-3 py-2 text-xs">
                            @if ($log->old_value || $log->new_value)
                                @php
                                    $fieldLabels = [
                                        'name' => 'Nama', 'email' => 'Email', 'slug' => 'Slug',
                                        'is_active' => 'Status', 'price' => 'Harga', 'code' => 'Kode',
                                        'subject' => 'Subjek', 'body' => 'Isi', 'description' => 'Deskripsi',
                                        'role' => 'Role', 'title' => 'Judul', 'content' => 'Konten',
                                        'image' => 'Gambar', 'image_path' => 'Gambar', 'url' => 'URL',
                                        'type' => 'Tipe', 'fee' => 'Fee', 'instructions' => 'Instruksi',
                                        'no_hp' => 'No. HP', 'address' => 'Alamat', 'weight' => 'Berat',
                                        'stock' => 'Stok', 'sku' => 'SKU', 'sort_order' => 'Urutan',
                                        'meta_description' => 'Meta Deskripsi', 'discount_nominal' => 'Diskon',
                                        'harga_rmb' => 'Harga RMB', 'ongkir_lokal_cn' => 'Ongkir Lokal CN',
                                        'ongkir_cn_btm' => 'Ongkir CN-BTM', 'ongkir_btm_jkt' => 'Ongkir BTM-JKT',
                                        'berat' => 'Berat', 'ketersediaan' => 'Ketersediaan',
                                        'store_id' => 'Store', 'game_id' => 'Game', 'developer_id' => 'Developer',
                                        'category_id' => 'Kategori', 'product_id' => 'Produk', 'variant_id' => 'Varian',
                                        'user_id' => 'User', 'admin_id' => 'Admin', 'order_id' => 'Order',
                                        'max_purchase' => 'Maks. Beli', 'min_purchase' => 'Min. Beli',
                                        'start_date' => 'Mulai', 'end_date' => 'Berakhir',
                                        'discount_amount' => 'Nominal Diskon', 'discount_percent' => 'Diskon (%)',
                                        'is_featured' => 'Unggulan', 'notes' => 'Catatan',
                                        'reason' => 'Alasan', 'reject_reason' => 'Alasan Tolak',
                                        'description_image_path' => 'Gambar Deskripsi',
                                        'default_store_id' => 'Store Default',
                                        'payment_method_id' => 'Metode Bayar',
                                        'no_order' => 'No. Order', 'total' => 'Total',
                                        'status' => 'Status', 'customer_note' => 'Catatan Pembeli',
                                        'admin_note' => 'Catatan Admin', 'ongkir' => 'Ongkir',
                                        'discount' => 'Diskon', 'coin_used' => 'Koin Dipakai',
                                        'voucher_code' => 'Kode Voucher',
                                        'spammer_id' => 'Spammer', 'ip_address' => 'IP',
                                        'email_attempt' => 'Email',
                                    ];

                                    $fmtVal = function ($v) {
                                        if (is_bool($v)) return $v ? 'Aktif' : 'Nonaktif';
                                        if (is_null($v)) return '-';
                                        return (string) $v;
                                    };

                                    $fmtDiff = function ($old, $new) use ($fieldLabels, $fmtVal) {
                                        if (is_null($old) && is_null($new)) return '';
                                        if (is_null($old)) return 'Data baru dibuat';
                                        if (is_null($new)) return 'Data dihapus';
                                        $parts = [];
                                        foreach ($new as $k => $v) {
                                            $label = $fieldLabels[$k] ?? str_replace('_', ' ', ucwords($k, '_'));
                                            $parts[] = $label . ': ' . $fmtVal($v);
                                        }
                                        return implode(' | ', $parts);
                                    };
                                @endphp
                                @if ($log->old_value === null && $log->new_value !== null)
                                    <span class="text-green-700">Data baru dibuat</span>
                                @elseif ($log->new_value === null && $log->old_value !== null)
                                    <span class="text-red-600">Data dihapus</span>
                                @else
                                    <span class="text-red-600">{{ \Illuminate\Support\Str::limit(json_encode($log->old_value), 80) }}</span>
                                    &rarr;
                                    <span class="text-green-700">{{ $fmtDiff($log->old_value, $log->new_value) }}</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
