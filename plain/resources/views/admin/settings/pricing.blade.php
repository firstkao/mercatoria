@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <div class="form-grid">
        <div class="form-grid__main">
            <form id="pricing-form" method="POST" action="{{ route('admin.settings.pricing.update') }}" class="panel stack" novalidate>
                @csrf
                @method('PUT')
                <h2>Harga & kurs</h2>
                <p class="hint">Harga jual dihitung dari yuan, ditambah ongkir China (berdasarkan tier), dikalikan kurs, lalu ditambah total ongkir per kg.</p>

                <div class="field-row">
                    <label class="field">
                        <span>Kurs 1 Yuan (Rp)</span>
                        <input type="number" name="exchange_rate" value="{{ old('exchange_rate', $settings['exchange_rate']) }}" step="0.01" min="1" required data-calc="exchangeRate">
                        @include('admin.partials.error', ['name' => 'exchange_rate'])
                    </label>
                    <label class="field">
                        <span>Margin (%)</span>
                        <input type="number" name="margin_percent" value="{{ old('margin_percent', $settings['margin_percent'] ?? 11) }}" step="0.1" min="0" required data-calc="marginPercent">
                        @include('admin.partials.error', ['name' => 'margin_percent'])
                    </label>
                </div>

                <div class="field-row">
                    <label class="field">
                        <span>Ongkir CN–ID (Rp/kg)</span>
                        <input type="number" name="cn_id_rate_per_kg" value="{{ old('cn_id_rate_per_kg', $settings['cn_id_rate_per_kg']) }}" step="1" min="0" required data-calc="cnIdRate">
                        @include('admin.partials.error', ['name' => 'cn_id_rate_per_kg'])
                    </label>
                    <label class="field">
                        <span>Ongkir Batam–Jakarta (Rp/kg)</span>
                        <input type="number" name="btm_jkt_rate_per_kg" value="{{ old('btm_jkt_rate_per_kg', $settings['btm_jkt_rate_per_kg']) }}" step="1" min="0" required data-calc="btmJktRate">
                        @include('admin.partials.error', ['name' => 'btm_jkt_rate_per_kg'])
                    </label>
                </div>

                <label class="field field--short">
                    <span>Pembulatan harga (Rp)</span>
                    <select name="price_rounding" data-calc="roundingStep">
                        @foreach ([100, 500, 1000, 5000, 10000] as $step)
                            <option value="{{ $step }}" @selected((int) old('price_rounding', $settings['price_rounding'] ?? 5000) === $step)>Kelipatan {{ number_format($step, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @include('admin.partials.error', ['name' => 'price_rounding'])
                </label>
            </form>
        </div>

        <aside class="form-grid__side">
            <section class="panel stack">
                <h2>Simulasi harga</h2>
                <div class="field-row">
                    <label class="field">
                        <span>Harga barang (¥)</span>
                        <input type="number" value="100" min="0" step="0.1" data-sim="yuan">
                    </label>
                    <label class="field">
                        <span>Berat (gram)</span>
                        <input type="number" value="500" min="1" step="1" data-sim="weight">
                    </label>
                </div>
                <label class="field">
                    <span>Tier ongkir China</span>
                    <select data-sim="tier">
                        @foreach ($tiers as $tier)
                            <option value="{{ $tier->fee_yuan }}|{{ $tier->min_purchase_yuan }}">
                                {{ $tier->code }} (Fee ¥{{ floatval($tier->fee_yuan) }} / min ¥{{ floatval($tier->min_purchase_yuan) }})
                            </option>
                        @endforeach
                    </select>
                </label>
                <div class="sim-result">
                    <div class="muted">Harga jual:</div>
                    <div class="sim-result__price" data-sim="result">Rp0</div>
                </div>
            </section>
        </aside>

        <div class="savebar">
            <button type="submit" form="pricing-form" class="btn btn--primary btn--block">Simpan perubahan</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var inputs = document.querySelectorAll('[data-calc], [data-sim]');
            var result = document.querySelector('[data-sim="result"]');

            function val(name) { return parseFloat(document.querySelector('[data-calc="' + name + '"]').value) || 0; }
            function sim(name) { return parseFloat(document.querySelector('[data-sim="' + name + '"]').value) || 0; }

            function calculate() {
                var yuan = sim('yuan'), weight = sim('weight'), tier = document.querySelector('[data-sim="tier"]').value.split('|');
                var fee = parseFloat(tier[0]) || 0, min = parseFloat(tier[1]) || 0;
                var china = (fee > 0 && min > 0 && yuan < min) ? yuan * fee / min : 0;

                var net = (yuan + china) * val('exchangeRate') + (weight / 1000) * (val('cnIdRate') + val('btmJktRate'));
                var price = net * (1 + val('marginPercent') / 100);
                var step = val('roundingStep') || 1;

                var finalPrice = Math.ceil(Math.round(price / step * 1e6) / 1e6) * step;
                result.textContent = 'Rp' + finalPrice.toLocaleString('id-ID');
            }

            inputs.forEach(function (el) { el.addEventListener('input', calculate); });
            calculate();
        })();
    </script>
@endpush
