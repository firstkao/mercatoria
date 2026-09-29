@extends('admin.layouts.app', ['title' => 'Pesan: ' . $message->subject, 'back' => route('admin.contact.index')])

@section('content')
    <div class="panel stack">
        <div class="panel__head">
            <h2>{{ $message->subject }}</h2>
            <span class="badge">{{ $message->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</span>
        </div>

        <dl class="deflist">
            <div><dt>Nama</dt><dd>{{ $message->name }}</dd></div>
            <div><dt>Email</dt><dd><a href="mailto:{{ $message->email }}" class="link">{{ $message->email }}</a></dd></div>
            @if ($message->whatsapp)
                <div><dt>WhatsApp</dt><dd><a href="https://wa.me/{{ preg_replace('/\D/', '', $message->whatsapp) }}" target="_blank" rel="noopener" class="link">{{ $message->whatsapp }}</a></dd></div>
            @endif
            <div><dt>IP</dt><dd class="mono small">{{ $message->ip_address }}</dd></div>
            @if ($message->user_id)
                <div><dt>Akun</dt><dd><a href="{{ route('admin.users.show', $message->user_id) }}" class="link">Lihat pengguna</a></dd></div>
            @else
                <div><dt>Akun</dt><dd class="muted">Guest</dd></div>
            @endif
        </dl>
    </div>

    <div class="panel">
        <h3>Pesan</h3>
        <div style="background:var(--bg);padding:16px;border-radius:8px;white-space:pre-wrap;">{{ $message->message }}</div>
    </div>

    <div class="panel stack" style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}" class="btn btn--primary">Balas via Email</a>
        @if ($message->whatsapp)
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $message->whatsapp) }}" target="_blank" rel="noopener" class="btn">Balas via WhatsApp</a>
        @endif
    </div>
@endsection