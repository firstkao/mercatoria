@extends('admin.layouts.app', ['title' => 'Halaman'])

@section('actions')
    <a href="{{ route('admin.pages.create') }}" class="btn btn--primary">@include('admin.partials.icon', ['name' => 'plus']) <span>Tambah halaman</span></a>
@endsection

@section('content')
    <div class="panel panel--flush only-desktop">
        <table class="table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Footer</th>
                    <th>Diubah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr>
                        <td class="table__name">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="table__title">{{ $page->title }}</a>
                            @if ($page->is_system)
                                <span class="muted small">· halaman sistem</span>
                            @endif
                        </td>
                        <td class="muted">/{{ $page->slug }}</td>
                        <td><span @class(['badge', 'badge--on' => $page->is_published])>{{ $page->is_published ? 'Terbit' : 'Draf' }}</span></td>
                        <td class="muted">{{ $page->show_in_footer ? 'Tampil' : '—' }}</td>
                        <td class="muted nowrap">{{ $page->updated_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <ul class="cards only-mobile">
        @foreach ($pages as $page)
            <li>
                <a href="{{ route('admin.pages.edit', $page) }}" class="card-row card-row--stack">
                    <span class="card-row__head">
                        <span class="card-row__title">{{ $page->title }}</span>
                        <span @class(['badge', 'badge--on' => $page->is_published])>{{ $page->is_published ? 'Terbit' : 'Draf' }}</span>
                    </span>
                    <span class="card-row__meta">/{{ $page->slug }}{{ $page->show_in_footer ? ' · di footer' : '' }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endsection