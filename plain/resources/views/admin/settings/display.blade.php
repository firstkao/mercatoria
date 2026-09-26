@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.display.update') }}" class="panel stack" novalidate>
        @csrf
        @method('PUT')
        <h2>Tampilan toko</h2>

        <label class="field">
            <span>Teks bar promo</span>
            <input type="text" name="promo_bar_text" value="{{ old('promo_bar_text', $settings['promo_bar_text']) }}" maxlength="200" placeholder="Kosongkan untuk menyembunyikan">
            @include('admin.partials.error', ['name' => 'promo_bar_text'])
        </label>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan tampilan</button>
        </div>
    </form>
@endsection
