// ─── Bootstrap (axios, dll) ────────────────
import './bootstrap';

// ─── Alpine.js + Plugins ───────────────────
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import persist from '@alpinejs/persist';
import collapse from '@alpinejs/collapse';

Alpine.plugin(focus);
Alpine.plugin(persist);
Alpine.plugin(collapse);

import { initImageProtection } from './image-protection';

// ─── Expose ke window (biar bisa dipakai inline) ──
window.Alpine = Alpine;

Alpine.start();
initImageProtection();

// ─── Global Helpers ────────────────────────
window.formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

window.formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

// ─── Toast Helper ──────────────────────────
window.showToast = (message, type = 'info') => {
    // Dispatch custom event yang bisa di-handle Alpine component
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message, type }
    }));
};

console.log('Mercatoria JS loaded ✅');
