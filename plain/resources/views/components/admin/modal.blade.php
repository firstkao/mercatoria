{{--
    Modal standar.
    Pakai:
      <x-admin.modal id="hapus-produk" title="Hapus produk?">
          <p>Yakin ingin menghapus?</p>
          <x-slot:footer>
              <x-admin.button data-modal-close>Batal</x-admin.button>
              <x-admin.button variant="danger" type="submit" form="form-hapus">Hapus</x-admin.button>
          </x-slot:footer>
      </x-admin.modal>
    Buka/tutup via JS: elemen [data-modal] di-toggle dengan menghapus atribut hidden.
--}}
@props([
    'id',
    'title' => null,
    'open' => false,
])

<div class="modal" id="{{ $id }}" @unless ($open) hidden @endunless data-modal>
    <button type="button" class="modal__backdrop" data-modal-close aria-label="Tutup"></button>
    <div class="modal__panel" role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif>
        <div class="modal__head">
            <h2>{{ $title }}</h2>
            <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">&times;</button>
        </div>
        <div class="modal__body">{{ $slot }}</div>
        @isset($footer)
            <div class="modal__foot">{{ $footer }}</div>
        @endisset
    </div>
</div>
