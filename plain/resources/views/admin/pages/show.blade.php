@extends('admin.layouts.app', ['title' => $page->title, 'back' => route('admin.pages.index')])

@section('actions')
    <a href="{{ route('admin.pages.edit', $page) }}" class="btn">Edit</a>
@endsection

@section('content')
<div class="panel">
    <div class="detail-grid">
        <div>
            <h2>{{ $page->title }}</h2>
            <p class="muted"><code>{{ $page->slug }}</code></p>
        </div>
        <div class="detail-grid__meta">
            @if ($page->is_published)
                <span class="badge badge--success">Tayang</span>
            @else
                <span class="badge badge--muted">Draf</span>
            @endif

            @if ($page->show_in_footer)
                <span class="badge">Tampil di footer</span>
            @endif

            @if ($page->is_system ?? false)
                <span class="badge badge--warning">Halaman sistem</span>
            @endif
        </div>
    </div>

    <dl class="detail-list">
        <dt>Urutan</dt>
        <dd>{{ $page->sort_order ?? 0 }}</dd>

        <dt>Dibuat</dt>
        <dd>{{ $page->created_at?->translatedFormat('j M Y H:i') }}</dd>

        <dt>Diperbarui</dt>
        <dd>{{ $page->updated_at?->translatedFormat('j M Y H:i') }}</dd>
    </dl>

    <h3>Konten</h3>
    <div class="prose">
        {!! $page->html() !!}
    </div>
</div>
@endsection