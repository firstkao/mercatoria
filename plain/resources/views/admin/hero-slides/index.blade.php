@extends('admin.layouts.app', ['title' => 'Hero Slider'])

@section('actions')
    <a href="{{ route('admin.hero-slides.create') }}" class="btn btn--primary">+ Tambah Slide</a>
@endsection

@section('content')
    <p class="hint intro">Slider yang tampil di halaman beranda. Aktifkan slide untuk menampilkannya. Urutan terkecil tampil duluan.</p>

    @if ($slides->isEmpty())
        <div class="empty">
            <p>Belum ada slide.</p>
            <a href="{{ route('admin.hero-slides.create') }}" class="btn btn--primary">Tambah slide</a>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Judul</th>
                        <th>Link</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slides as $slide)
                        <tr>
                            <td class="col-md">
                                @if ($slide->image_path)
                                    <img src="{{ $slide->imageUrl() }}" alt="" class="thumb-wide">
                                @else
                                    <span class="thumb-empty thumb-empty--wide thumb-empty--box">No img</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $slide->title ?: '—' }}</strong>
                                @if ($slide->subtitle)
                                    <div class="muted small">{{ $slide->subtitle }}</div>
                                @endif
                            </td>
                            <td class="muted small">
                                @if ($slide->link_url)
                                    <a href="{{ $slide->link_url }}" target="_blank" rel="noopener" class="link">{{ Str::limit($slide->link_url, 30) }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="muted">{{ $slide->sort_order }}</td>
                            <td>
                                <span @class(['badge', 'badge--on' => $slide->is_active, 'badge--muted' => ! $slide->is_active])>
                                    {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="nowrap">
                                <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="link">Edit</a>
                                <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}"
                                      onsubmit="return confirm('Hapus slide ini?');"
                                      class="inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link text-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        <ul class="cards only-mobile">
            @foreach ($slides as $slide)
                <li>
                    <div class="card-row">
                        @if ($slide->image_path)<img src="{{ $slide->imageUrl() }}" alt="" class="card-row__thumb">@endif
                        <div class="card-row__body">
                            <span class="card-row__title">{{ $slide->title ?: '&mdash;' }}</span>
                            <span class="card-row__meta">{{ $slide->is_active ? 'Aktif' : 'Nonaktif' }} &middot; urutan {{ $slide->sort_order }}</span>
                            <div class="mt-2"><a href="{{ route('admin.hero-slides.edit', $slide) }}" class="link">Edit</a></div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        </div>
    @endif
@endsection