@extends('admin.layouts.app', ['title' => 'Developer'])

@section('actions')
    <a href="{{ route('admin.developers.create') }}" class="btn btn--primary">+ Tambah Developer</a>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert--success">{{ session('status') }}</div>
    @endif
    @error('developer')
        <div class="alert alert--danger">{{ $message }}</div>
    @enderror

    <p class="hint intro">Studio/penerbit game. Developer yang masih punya game atau produk tidak bisa dihapus.</p>

    @if ($developers->isEmpty())
        <div class="empty">
            <p>Belum ada developer.</p>
            <a href="{{ route('admin.developers.create') }}" class="btn btn--primary">Tambah developer</a>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Slug</th>
                        <th>Game</th>
                        <th>Produk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($developers as $developer)
                        <tr>
                            <td><strong>{{ $developer->name }}</strong></td>
                            <td class="muted small">{{ $developer->slug }}</td>
                            <td class="muted">{{ number_format($developer->games_count, 0, ',', '.') }}</td>
                            <td class="muted">{{ number_format($developer->products_count, 0, ',', '.') }}</td>
                            <td class="nowrap">
                                <a href="{{ route('admin.developers.edit', $developer) }}" class="link">Edit</a>
                                <form method="POST" action="{{ route('admin.developers.destroy', $developer) }}"
                                      onsubmit="return confirm('Hapus developer {{ $developer->name }}?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link link--danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
