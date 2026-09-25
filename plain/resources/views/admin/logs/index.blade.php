@extends('admin.layouts.app', ['title' => 'Log aktivitas'])

@section('tabs')
    <a href="{{ route('admin.logs.index') }}" @class(['subtab', 'is-active' => ! $isAdminTab])>Pembeli</a>
    <a href="{{ route('admin.logs.index', ['tab' => 'admin']) }}" @class(['subtab', 'is-active' => $isAdminTab])>Admin</a>
@endsection

@section('filters')
    <form method="GET" action="{{ route('admin.logs.index') }}" class="filters">
        @if ($isAdminTab)
            <input type="hidden" name="tab" value="admin">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isAdminTab ? 'Cari nama admin' : 'Cari nama, email, atau IP' }}" aria-label="Cari">
        <select name="aksi" aria-label="Aktivitas">
            <option value="">Semua aktivitas</option>
            @foreach ($labels as $value => $label)
                <option value="{{ $value }}" @selected($filters['aksi'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label class="date-field"><span>Dari</span><input type="date" name="dari" value="{{ $filters['dari'] }}"></label>
        <label class="date-field"><span>Sampai</span><input type="date" name="sampai" value="{{ $filters['sampai'] }}"></label>
        <button type="submit" class="btn">Terapkan</button>
        @if (array_filter($filters))
            <a href="{{ route('admin.logs.index', $isAdminTab ? ['tab' => 'admin'] : []) }}" class="link">Reset</a>
        @endif
    </form>
@endsection

@section('content')
    @if ($logs->isEmpty())
        <div class="empty"><p>Belum ada aktivitas{{ array_filter($filters) ? ' yang cocok' : '' }}.</p></div>
    @else
        @php($existingProducts = $isAdminTab ? collect() : \App\Models\Product::query()->whereIn('id', $logs->pluck('metadata.product_id')->filter())->get(['id', 'slug', 'name'])->keyBy('id'))
        <div class="panel panel--flush">
            <ul class="log-list">
                @foreach ($logs as $log)
                    @php($actor = $isAdminTab ? $log->admin : $log->user)
                    <li class="log">
                        <span class="log__icon log__icon--{{ $log->action }}">@include('admin.partials.icon', ['name' => $log->action === 'view_product' ? 'eye' : ($isAdminTab ? 'shield' : 'users')])</span>
                        <div class="log__time">
                            <strong>{{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</strong>
                            <span class="muted">{{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
                        </div>
                        <div class="log__actor">
                            @if ($isAdminTab)
                                <strong>{{ $actor?->name ?? 'Admin terhapus' }}</strong>
                            @elseif ($actor)
                                <a href="{{ route('admin.users.show', $actor) }}" class="table__title">{{ $actor->displayName() }}</a>
                                <span class="muted small">{{ $actor->role->label() }}</span>
                                <span class="muted small mono">{{ $log->ip_address }}</span>
                            @endif
                        </div>
                        <div class="log__body">
                            <span class="log__action">{{ $log->label() }}</span>
                            @php($currentProduct = $existingProducts->get($log->metadata['product_id'] ?? 0))
                            @foreach ($isAdminTab ? $log->details() : $log->details($currentProduct?->name) as $key => $value)
                                <span class="log__detail"><span class="muted">{{ ucfirst($key) }}:</span> <strong>{{ $value }}</strong></span>
                            @endforeach
                            @if ($currentProduct)
                                <a href="{{ route('admin.products.edit', $currentProduct) }}" class="link small">Buka produk di admin</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @include('admin.partials.pagination', ['paginator' => $logs])
    @endif
@endsection