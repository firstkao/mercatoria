@extends('admin.layouts.app', ['title' => 'Game'])

@section('actions')
    <a href="{{ route('admin.games.create') }}" class="btn btn--primary">+ Tambah Game</a>
@endsection

@section('content')
    @error('game')
        <div class="alert alert--danger">{{ $message }}</div>
    @enderror

    <p class="hint intro">Daftar game/franchise yang dipakai untuk mengelompokkan produk. Game yang masih dipakai produk tidak bisa dihapus.</p>

    @if ($games->isEmpty())
        <div class="empty">
            <p>Belum ada game.</p>
            <a href="{{ route('admin.games.create') }}" class="btn btn--primary">Tambah game</a>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Nama</th>
                        <th>Developer</th>
                        <th>Produk</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($games as $game)
                        <tr>
                            <td class="col-sm">
                                @if ($game->image_path)
                                    <img src="{{ $game->imageUrl() }}" alt="{{ $game->name }}" class="thumb-sm">
                                @else
                                    <span class="thumb-empty thumb-empty--sm thumb-empty--box">No img</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $game->name }}</strong>
                                <div class="muted small">{{ $game->slug }}</div>
                            </td>
                            <td class="muted">{{ $game->developer?->name ?? '—' }}</td>
                            <td class="muted">{{ number_format($game->products_count, 0, ',', '.') }}</td>
                            <td class="muted">{{ $game->sort_order }}</td>
                            <td class="nowrap">
                                <a href="{{ route('admin.games.edit', $game) }}" class="link">Edit</a>
                                <form method="POST" action="{{ route('admin.games.destroy', $game) }}"
                                      onsubmit="return confirm('Hapus game {{ $game->name }}?');" class="inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link link--danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        <ul class="cards only-mobile">
            @foreach($games as $game)
                <li>
                    <div class="card-row">
                        @if ($game->image_path)<img src="{{ $game->imageUrl() }}" alt="" class="card-row__thumb">@endif
                        <div class="card-row__body">
                            <span class="card-row__title">{{ $game->name }}</span>
                            <span class="card-row__meta">{{ $game->developer?->name ?? '&mdash;' }} &middot; {{ number_format($game->products_count, 0, ',', '.') }} produk</span>
                            <div class="mt-2"><a href="{{ route('admin.games.edit', $game) }}" class="link">Edit</a></div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        </div>
    @endif
@endsection
