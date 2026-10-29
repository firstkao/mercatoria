@extends('admin.layouts.admin')

@section('title', 'Input Order Lama')

@section('content')
    <h1 class="text-xl font-semibold">Input Order Lama (Inject)</h1>
    <p class="mt-1 text-sm text-gray-500">PRD 22.7 — harga snapshot bisa diedit manual, status bebas, tanpa bukti bayar.</p>

    <form method="POST" action="{{ route('office.orders.inject.store') }}" class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @csrf

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium">User <span class="text-red-500">*</span></label>
                <select name="user_id" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">— Pilih user (nama / email / HP) —</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }} · {{ $user->no_hp }}</option>
                    @endforeach
                </select>
                <a href="{{ route('office.users.create') }}" class="mt-1 inline-block text-xs text-sky-600 hover:underline">+ User Baru</a>
            </div>

            <div>
                <label class="block text-sm font-medium">Status Tujuan <span class="text-red-500">*</span></label>
                <select name="status" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Catatan Pembelian</label>
                <input type="text" name="customer_note" maxlength="500" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium">Tanggal Order</label>
                <input type="date" name="tanggal_order" max="{{ now()->format('Y-m-d') }}"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-gray-400">Opsional. Masa lalu. Kosong = pakai waktu sekarang.</p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">Item Pesanan</h2>
            <a href="{{ route('office.products.create') }}" class="text-xs text-sky-600 hover:underline">+ Produk Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="px-2 py-2">Produk</th>
                        <th class="px-2 py-2">Varian</th>
                        <th class="px-2 py-2">Qty</th>
                        <th class="px-2 py-2">Harga Snapshot (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < 3; $i++)
                        <tr class="border-b border-gray-50">
                            <td class="px-2 py-2">
                                <select name="items[{{ $i }}][product_id]" class="w-full rounded border border-gray-200 px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                <select name="items[{{ $i }}][variant_id]" class="w-full rounded border border-gray-200 px-2 py-1.5 text-sm">
                                    <option value="">— tanpa varian —</option>
                                    @foreach ($products as $product)
                                        @foreach ($product->variants as $variant)
                                            <option value="{{ $variant->id }}">{{ $product->name }} — {{ $variant->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" name="items[{{ $i }}][qty]" min="1" value="1"
                                       class="w-20 rounded border border-gray-200 px-2 py-1.5 text-sm">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" step="0.01" name="items[{{ $i }}][price]" min="0" placeholder="manual"
                                       class="w-32 rounded border border-gray-200 px-2 py-1.5 text-sm">
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-400">Baris tanpa produk diabaikan. Harga yang diketik menjadi harga snapshot final (bisa diedit).</p>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">
            SIMPAN ORDER LAMA
        </button>
    </form>
@endsection
