<a href="{{ route('admin.identities.index') }}" @class(['subtab', 'is-active' => request()->routeIs('admin.identities.*')])>
    Email & WhatsApp <span class="subtab__count">{{ $blockedCount }} diblokir</span>
</a>
<a href="{{ route('admin.names.index') }}" @class(['subtab', 'is-active' => request()->routeIs('admin.names.*')])>
    Nama terlarang <span class="subtab__count">{{ $nameCount }}</span>
</a>