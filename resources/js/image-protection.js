// PRD 8.10 layer 2 — Image Shield: kunci klik-kanan / drag / save pada gambar
// produk, varian, dan deskripsi; teks biasa tetap bebas di-copy.
// Dipasang di halaman detail produk (PR UI) — idempotent, aman di-load dua kali.

(function () {
    function isImageTarget(el) {
        if (!el) return false;
        return el.tagName === 'IMG' ||
            el.tagName === 'CANVAS' ||
            (el.closest && el.closest('.protected-image-wrap') !== null);
    }

    // Klik kanan pada gambar dicegah.
    document.addEventListener('contextmenu', function (e) {
        if (isImageTarget(e.target)) {
            e.preventDefault();
        }
    });

    // Drag & drop gambar ke desktop/tab lain dicegah.
    document.addEventListener('dragstart', function (e) {
        if (isImageTarget(e.target)) {
            e.preventDefault();
        }
    });

    // Tandai seluruh gambar: draggable false + tanpa long-press save di HP.
    function markImages() {
        document.querySelectorAll('img').forEach(function (img) {
            img.setAttribute('draggable', 'false');
            img.style.webkitUserDrag = 'none';
            img.style.userSelect = 'none';
            img.style.webkitTouchCallout = 'none';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', markImages);
    } else {
        markImages();
    }
})();
