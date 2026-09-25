<dialog id="{{ $id }}" class="legal-dialog" aria-labelledby="{{ $id }}-title">
    <div class="legal-dialog__header">
        <h2 id="{{ $id }}-title">{{ $title }}</h2>
        <form method="dialog">
            <button type="submit" class="link-button" aria-label="Tutup">Tutup</button>
        </form>
    </div>
    <div class="prose legal-dialog__body">
        {!! $html !!}
    </div>
    <p class="legal-dialog__footer">
        <a href="{{ route('legal.show', $page) }}" target="_blank" rel="noopener">Buka di halaman baru</a>
    </p>
</dialog>