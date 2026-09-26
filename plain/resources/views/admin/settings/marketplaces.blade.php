@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.marketplaces.update') }}" class="panel stack" novalidate>
        @csrf
        @method('PUT')
        <h2>Biaya marketplace</h2>
        <p class="hint">Biaya ditambahkan ke tagihan di website saat pembeli memilih marketplace.</p>

        <div class="grid-2">
            @foreach ($marketplaces as $mp)
                <div class="panel panel--inner stack">
                    <div class="panel__head">
                        <h3>{{ $mp->name }}</h3>
                        <label class="switch">
                            <input type="checkbox" name="marketplaces[{{ $mp->id }}][is_active]" value="1" @checked(old("marketplaces.{$mp->id}.is_active", $mp->is_active))>
                            <span>Aktif</span>
                        </label>
                    </div>
                    <label class="field">
                        <span>Biaya FP (Rp)</span>
                        <input type="number" name="marketplaces[{{ $mp->id }}][fp_fee_idr]" value="{{ old("marketplaces.{$mp->id}.fp_fee_idr", $mp->fp_fee_idr) }}" step="1" min="0" required>
                        @include('admin.partials.error', ['name' => "marketplaces.{$mp->id}.fp_fee_idr"])
                    </label>
                    <label class="field">
                        <span>Biaya DP (%)</span>
                        <input type="number" name="marketplaces[{{ $mp->id }}][dp_fee_percent]" value="{{ old("marketplaces.{$mp->id}.dp_fee_percent", floatval($mp->dp_fee_percent)) }}" step="0.01" min="0" max="100" required>
                        <small class="hint">Dihitung dari nominal pelunasan.</small>
                        @include('admin.partials.error', ['name' => "marketplaces.{$mp->id}.dp_fee_percent"])
                    </label>
                </div>
            @endforeach
        </div>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan biaya</button>
        </div>
    </form>
@endsection
