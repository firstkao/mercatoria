@extends('admin.layouts.app', ['title' => 'Blokir & banding'])

@section('tabs')
    @include('admin.identities.tabs')
@endsection

@section('content')
    <div class="form-grid">
        <div class="form-grid__main">
            <form method="GET" action="{{ route('admin.names.index') }}" class="filters">
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama" aria-label="Cari nama">
                <button type="submit" class="btn">Cari</button>
            </form>

            @if ($names->isEmpty())
                <div class="empty"><p>Belum ada nama terlarang.</p></div>
            @else
                <ul class="chips">
                    @foreach ($names as $name)
                        <li class="chip">
                            {{ $name->name }}
                            <form method="POST" action="{{ route('admin.names.destroy', $name) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="chip__remove" aria-label="Hapus {{ $name->name }}">×</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                @include('admin.partials.pagination', ['paginator' => $names])
            @endif
        </div>

        <aside class="form-grid__side">
            <form method="POST" action="{{ route('admin.names.store') }}" class="panel stack">
                @csrf
                <h2>Tambah nama</h2>
                <p class="hint">Nama karakter atau nama asal-asalan yang ditolak saat daftar. Pencocokan tidak membedakan huruf besar-kecil. Satu nama per baris.</p>
                <textarea name="names" rows="6" placeholder="Raiden Shogun&#10;Zhongli Morax" required>{{ old('names') }}</textarea>
                @include('admin.partials.error', ['name' => 'names'])
                <button type="submit" class="btn btn--primary">Tambah</button>
            </form>
        </aside>
    </div>
@endsection