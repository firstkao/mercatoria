<a href="{{ route('admin.settings.pricing') }}" @class(['subtab', 'is-active' => request()->routeIs('admin.settings.pricing')])>Harga & kurs</a>
<a href="{{ route('admin.settings.tiers') }}" @class(['subtab', 'is-active' => request()->routeIs('admin.settings.tiers')])>Tier ongkir</a>
<a href="{{ route('admin.settings.marketplaces') }}" @class(['subtab', 'is-active' => request()->routeIs('admin.settings.marketplaces')])>Marketplace</a>
<a href="{{ route('admin.settings.display') }}" @class(['subtab', 'is-active' => request()->routeIs('admin.settings.display')])>Tampilan toko</a>
