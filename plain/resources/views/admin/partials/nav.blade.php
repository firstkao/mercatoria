@php($badges = $adminBadges ?? [])

@foreach ($navigation as $group)
    <div class="nav-group">
        @if ($group['label'])
            <p class="nav-group__label">{{ $group['label'] }}</p>
        @endif
        @foreach ($group['items'] as $item)
            @php($badgeCount = isset($item['badge']) ? ($badges[$item['badge']] ?? 0) : 0)
            <a href="{{ route($item['route']) }}" @class(['nav-link', 'is-active' => request()->routeIs(...(array) $item['active'])])>
                @include('admin.partials.icon', ['name' => $item['icon']])
                <span class="nav-link__label">{{ $item['label'] }}</span>
                @if ($badgeCount > 0)
                    <span class="nav-link__badge">{{ $badgeCount > 99 ? '99+' : $badgeCount }}</span>
                @endif
            </a>
        @endforeach
    </div>
@endforeach