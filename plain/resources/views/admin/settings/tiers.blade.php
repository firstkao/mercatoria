@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.tiers.update') }}" class="panel stack" novalidate>
        @csrf
        @method('PUT')
        <h2>Tier ongkir China</h2>
        <p class="hint">Jika harga barang (yuan) di bawah minimal belanja, ongkir dihitung proporsional dari fee. Jika mencapai minimal, ongkir gratis.</p>

        <table class="table table--compact">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Fee (¥)</th>
                    <th>Min. belanja (¥)</th>
                    <th>Produk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tier-list">
                @foreach (old('tiers', $tiers) as $index => $tier)
                    @php($id = $tier['id'] ?? null)
                    @php($count = isset($tier['products_count']) ? $tier['products_count'] : 0)
                    <tr data-row>
                        <td>
                            <input type="hidden" name="tiers[{{ $index }}][id]" value="{{ $id }}">
                            <input type="text" name="tiers[{{ $index }}][code]" value="{{ $tier['code'] }}" class="input-compact" required>
                            @include('admin.partials.error', ['name' => "tiers.{$index}.code"])
                        </td>
                        <td>
                            <input type="number" name="tiers[{{ $index }}][fee_yuan]" value="{{ floatval($tier['fee_yuan']) }}" step="0.01" min="0" class="input-compact" required>
                            @include('admin.partials.error', ['name' => "tiers.{$index}.fee_yuan"])
                        </td>
                        <td>
                            <input type="number" name="tiers[{{ $index }}][min_purchase_yuan]" value="{{ floatval($tier['min_purchase_yuan']) }}" step="0.01" min="0" class="input-compact" required>
                            @include('admin.partials.error', ['name' => "tiers.{$index}.min_purchase_yuan"])
                        </td>
                        <td class="muted">{{ $count }}</td>
                        <td>
                            @if ($count === 0)
                                <label class="check check--small"><input type="checkbox" name="tiers[{{ $index }}][delete]" value="1"> <span>Hapus</span></label>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan tier</button>
            <button type="button" class="btn" id="add-tier">Tambah baris</button>
        </div>
    </form>

    <template id="tier-template">
        <tr data-row>
            <td><input type="text" name="tiers[__I__][code]" class="input-compact" required></td>
            <td><input type="number" name="tiers[__I__][fee_yuan]" step="0.01" min="0" class="input-compact" required></td>
            <td><input type="number" name="tiers[__I__][min_purchase_yuan]" step="0.01" min="0" class="input-compact" required></td>
            <td class="muted">0</td>
            <td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">@include('admin.partials.icon', ['name' => 'trash'])</button></td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        document.getElementById('add-tier').addEventListener('click', function () {
            var tbody = document.getElementById('tier-list');
            var index = Date.now();
            var html = document.getElementById('tier-template').innerHTML.replace(/__I__/g, index);
            tbody.insertAdjacentHTML('beforeend', html);
        });
    </script>
@endpush
