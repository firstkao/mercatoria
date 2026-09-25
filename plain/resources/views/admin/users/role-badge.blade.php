@if ($user->anonymized_at)
    <span class="badge">Data dihapus</span>
@elseif ($user->isSpammer())
    <span class="badge badge--muted">Spammer</span>
@else
    <span class="badge badge--on">Customer</span>
@endif