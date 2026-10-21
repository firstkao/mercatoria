// Hanya mencegat klik-kanan dan drag pada gambar. Teks tetap bebas dipilih dan disalin.
const protectedSelector = 'img, canvas, .protected-image-wrap, .img-shield-overlay';

const isProtectedTarget = (event) => event.target instanceof Element && event.target.closest(protectedSelector) !== null;

export function initImageProtection() {
    if (document.documentElement.dataset.imageProtection !== '1') {
        return;
    }

    const warn = (event) => {
        if (!isProtectedTarget(event)) {
            return;
        }
        event.preventDefault();
        window.showToast?.('🔒 Gambar dilindungi hak cipta Mercatoria.', 'warning');
    };

    document.addEventListener('contextmenu', warn);
    document.addEventListener('dragstart', warn);
}
