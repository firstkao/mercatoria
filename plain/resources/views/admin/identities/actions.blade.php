<span class="row-actions">
    @if ($record->blocked_at || $record->deletion_count > 0)
        <form method="POST" action="{{ route('admin.identities.reset', $record) }}" onsubmit="return confirm('Reset {{ $record->value }}? Jatah daftar kembali penuh.');">
            @csrf
            <button type="submit" class="btn btn--small">Reset</button>
        </form>
    @endif
    @unless ($record->blocked_at)
        <form method="POST" action="{{ route('admin.identities.block', $record) }}" onsubmit="return confirm('Blokir {{ $record->value }}?');">
            @csrf
            <button type="submit" class="btn btn--small btn--danger-outline">Blokir</button>
        </form>
    @endunless
</span>