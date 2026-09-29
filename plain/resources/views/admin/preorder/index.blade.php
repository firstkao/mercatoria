@extends('admin.layouts.app', ['title' => 'Pre-Order Baru'])

@section('actions')
    <a href="{{ route('admin.preorder.create') }}" class="btn btn--primary">+ Tambah Halaman PO</a>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert--success">{{ session('status') }}</div>
    @endif

    <p class="hint intro">
        Halaman Pre-Order Baru hanya bisa diakses oleh pengguna yang sudah login. Hanya <strong>satu halaman yang aktif</strong> yang akan tampil di <code>/pre-order-baru</code>.
    </p>

    @if ($pages->isEmpty())
        <div class="empty">
            <p>Belum ada halaman Pre-Order.</p>
            <a href="{{ route('admin.preorder.create') }}" class="btn btn--primary">Buat halaman pertama</a>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Slug</th>
                        <th>Banner</th>
                        <th>Status</th>
                        <th>Diubah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td><strong>{{ $page->title }}</strong></td>
                            <td class="mono small muted">{{ $page->slug }}</td>
                            <td class="muted">{{ $page->banners_count }} banner</td>
                            <td>
                                @if ($page->is_active)
                                    <span class="badge badge--on">Aktif</span>
                                @else
                                    <span class="badge badge--muted">Nonaktif</span>
                                @endif
                            </td>
                            <td class="muted small nowrap">{{ $page->updated_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}</td>
                            <td class="nowrap">
                                @unless ($page->is_active)
                                    <form method="POST" action="{{ route('admin.preorder.toggle', $page) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="link" style="color:var(--success);font-weight:600;">Aktifkan</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.preorder.toggle', $page) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="link muted">Nonaktifkan</button>
                                    </form>
                                @endunless
                                <a href="{{ route('admin.preorder.edit', $page) }}" class="link">Edit</a>
                                <form method="POST" action="{{ route('admin.preorder.destroy', $page) }}"
                                      onsubmit="return confirm('Hapus halaman & semua bannernya?');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link" style="color:var(--danger);">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection