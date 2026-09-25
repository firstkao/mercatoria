<span @class([
    'count-pill',
    'count-pill--ok' => $record->deletion_count === 0,
    'count-pill--warn' => $record->deletion_count > 0 && $record->deletion_count < $limit,
    'count-pill--danger' => $record->deletion_count >= $limit,
])>{{ $record->deletion_count }}/{{ $limit }}</span>