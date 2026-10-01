@extends('admin.layouts.app', ['title' => 'Pesan Kontak'])

@section('tabs')
    <a href="{{ route('admin.contact.index') }}" @class(['subtab', 'is-active' => ! $status])>Semua</a>
    <a href="{{ route('admin.contact.index', ['status' => 'unread']) }}" @class(['subtab', 'is-active' => $status === 'unread'])>
        Belum dibaca <span class="subtab__count">{{ $unreadCount }}</span>
    </a>
    <a href="{{ route('admin.contact.index', ['status' => 'read']) }}" @class(['subtab', 'is-active' => $status === 'read'])>
        Sudah dibaca
    </a>
@endsection

@section('content')
    @if ($messages->isEmpty())
        <div class="empty"><p>Belum ada pesan.</p></div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Pengirim</th>
                        <th>Subjek</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($messages as $message)
                        <tr>
                            <td style="width:16px;">
                                @unless ($message->read_at)
                                    <span class="status-dot status-dot--on" title="Belum dibaca"></span>
                                @endunless
                            </td>
                            <td>
                                <strong>{{ $message->name }}</strong>
                                <div class="muted small">{{ $message->email }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin.contact.show', $message) }}" class="table__title">{{ $message->subject }}</a>
                                <div class="muted small">{{ \Illuminate\Support\Str::limit($message->message, 60) }}</div>
                            </td>
                            <td class="muted nowrap small">{{ $message->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}</td>
                            <td class="nowrap">
                                <a href="{{ route('admin.contact.show', $message) }}" class="link">Lihat</a>
                                <form method="POST" action="{{ route('admin.contact.destroy', $message) }}"
                                      onsubmit="return confirm('Hapus pesan ini?');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link" style="color:var(--danger);">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        <ul class="cards only-mobile">
            @foreach($messages as $message)
                <li>
                    <a href="{{ route('admin.contact.show', $message) }}" class="card-row card-row--stack">
                        <div class="card-row__head">
                            <span class="card-row__title">{{ $message->subject }}</span>
                            @unless ($message->read_at)<span class="badge badge--warn">Baru</span>@endunless
                        </div>
                        <p class="card-row__meta">{{ $message->name }} &middot; {{ $message->email }}</p>
                        <p class="card-row__meta">{{ $message->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
        </div>

        @include('admin.partials.pagination', ['paginator' => $messages])
    @endif
@endsection