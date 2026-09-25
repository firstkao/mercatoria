@if ($record->blocked_at || $record->deletion_count >= $limit)
    <span class="badge badge--danger">Diblokir</span>
@elseif ($record->deletion_count > 0)
    <span class="badge badge--warn">Diawasi</span>
@else
    <span class="badge">Normal</span>
@endif