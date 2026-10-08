<x-admin.card class="stack" id="order-notes">
    <div class="panel__head">
        <h2 class="m-0">📝 Catatan Internal</h2>
        <span class="muted small">{{ $order->notes->count() }} catatan</span>
    </div>

    <p class="hint hint--pull">Catatan ini hanya terlihat oleh admin. Cocok untuk log chat WA, keluhan, permintaan khusus, dsb.</p>

    {{-- Form tambah catatan --}}
    <form method="POST" action="{{ route('admin.orders.notes.store', $order) }}" class="stack stack--tight">
        @csrf

        <textarea name="body" rows="3" maxlength="2000" required class="textarea"
                  placeholder="Tulis catatan… (mis. buyer minta bubble wrap extra, sudah transfer sisa 50rb, dll)"></textarea>

        @include('admin.partials.error', ['name' => 'body'])

        <div class="form-actions--between">
            <label class="check">
                <input type="checkbox" name="is_pinned" value="1">
                <span>Pin di atas</span>
            </label>
            <x-admin.button variant="primary" size="sm" type="submit">Tambah Catatan</x-admin.button>
        </div>
    </form>

    {{-- List catatan --}}
    @if ($order->notes->isEmpty())
        <p class="muted small text-center p-4">Belum ada catatan.</p>
    @else
        <ul class="notes-list">
            @foreach ($order->notes as $note)
                <li class="note {{ $note->is_pinned ? 'note--pinned' : '' }}">
                    <div class="note__head">
                        <div>
                            @if ($note->is_pinned)
                                <x-admin.badge variant="warn" class="small">📌 Pin</x-admin.badge>
                            @endif
                            <strong>{{ $note->admin?->name ?? 'Admin' }}</strong>
                            <span class="muted small">· {{ $note->created_at->timezone('Asia/Jakarta')->diffForHumans() }}</span>
                        </div>
                        <div class="note__actions">
                            <form method="POST" action="{{ route('admin.orders.notes.toggle-pin', [$order, $note]) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="icon-btn fs-body" title="{{ $note->is_pinned ? 'Lepas pin' : 'Pin' }}">
                                    {{ $note->is_pinned ? '📌' : '📍' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.orders.notes.destroy', [$order, $note]) }}"
                                  onsubmit="return confirm('Hapus catatan ini?');"
                                  class="inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn fs-body text-danger" title="Hapus">×</button>
                            </form>
                        </div>
                    </div>
                    <p class="note__body">{{ $note->body }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</x-admin.card>
