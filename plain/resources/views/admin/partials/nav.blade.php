@foreach ($navigation as $group)
    <div class="nav-group">
        @if ($group['label'])
            <p class="nav-group__label">{{ $group['label'] }}</p>
        @endif
        @foreach ($group['items'] as $item)
            <a href="{{ route($item['route']) }}" @class(['nav-link', 'is-active' => request()->routeIs(...(array) $item['active'])])>
                @include('admin.partials.icon', ['name' => $item['icon']])
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
@endforeach