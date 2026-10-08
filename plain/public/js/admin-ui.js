/**
 * Admin UI helpers — MERCATORIA.
 *
 * Modal:
 *  - Buka  : tombol/elemen dengan [data-modal-open="ID-MODAL"]
 *  - Tutup : elemen [data-modal-close] di dalam modal, klik backdrop, atau tombol Esc
 *  - Modal  : <x-admin.modal id="..." title="..."> — dirender dengan atribut [data-modal] + hidden
 */
(function () {
    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        var focusable = modal.querySelector('input, select, textarea, button, a[href]');
        if (focusable) focusable.focus();
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-modal-open]');
        if (opener) {
            event.preventDefault();
            openModal(opener.getAttribute('data-modal-open'));
            return;
        }

        // Jangan preventDefault: tombol "Batal"/backdrop bertipe button tidak punya
        // aksi default, dan tombol submit harus tetap boleh mengirim form.
        var closer = event.target.closest('[data-modal-close]');
        if (closer) {
            closeModal(closer.closest('[data-modal]'));
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[data-modal]:not([hidden])').forEach(closeModal);
    });
})();
