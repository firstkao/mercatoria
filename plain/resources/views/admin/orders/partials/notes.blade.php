<section class="panel stack" id="order-notes">
    <div class="panel__head">
        <h2 style="margin:0;">📝 Catatan Internal</h2>
        <span class="muted small">{{ $order->notes->count() }} catatan</span>
    </div>

    <p class="hint" style="margin-top:-8px;">Catatan ini hanya terlihat oleh admin. Cocok untuk log chat WA, keluhan, permintaan khusus, dsb.</p>

    {{-- Form tambah catatan --}}
    <form method="POST" action="{{ route('admin.orders.notes.store', $order) }}" class="stack" style="gap:8px;">
        @csrf

        <textarea name="body" rows="3" maxlength="2000" required
                  placeholder="Tulis catatan… (mis. buyer minta bubble wrap extra, sudah transfer sisa 50rb, dll)"
                  style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;font:inherit;resize:vertical;"></textarea>

        @include('admin.partials.error', ['name' => 'body'])

        <div style="display:flex;justify-content:space-between;align-items:center;">
            <label class="check check--small">
                <input type="checkbox" name="is_pinned" value="1">
                <span>Pin di atas</span>
            </label>
            <button type="submit" class="btn btn--primary btn--small">Tambah Catatan</button>
        </div>
    </form>

    {{-- List catatan --}}
    @if ($order->notes->isEmpty())
        <p class="muted small" style="text-align:center;padding:16px 0;">Belum ada catatan.</p>
    @else
        <ul class="notes-list">
            @foreach ($order->notes as $note)
                <li class="note {{ $note->is_pinned ? 'note--pinned' : '' }}">
                    <div class="note__head">
                        <div>
                            @if ($note->is_pinned)
                                <span class="badge small" style="background:#fef3c7;color:#92400e;">📌 Pin</span>
                            @endif
                            <strong>{{ $note->admin?->name ?? 'Admin' }}</strong>
                            <span class="muted small">· {{ $note->created_at->timezone('Asia/Jakarta')->diffForHumans() }}</span>
                        </div>
                        <div class="note__actions">
                            <form method="POST" action="{{ route('admin.orders.notes.toggle-pin', [$order, $note]) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="icon-btn" title="{{ $note->is_pinned ? 'Lepas pin' : 'Pin' }}" style="font-size:14px;">
                                    {{ $note->is_pinned ? '📌' : '📍' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.orders.notes.destroy', [$order, $note]) }}"
                                  onsubmit="return confirm('Hapus catatan ini?');"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" title="Hapus" style="font-size:14px;color:var(--danger);">×</button>
                            </form>
                        </div>
                    </div>
                    <p class="note__body">{{ $note->body }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</section>