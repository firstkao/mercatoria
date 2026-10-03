{{-- ===================== MODAL ALERT (pengganti window.alert) ===================== --}}
{{-- Modal tersembunyi by default; dipanggil via helper global showModal('pesan', 'error'|'success'). --}}
{{-- Juga otomatis muncul saat ada session flash 'success'/'error' (lihat layouts/app.blade.php). --}}
<div id="modalAlert" class="modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="modalAlertTitle">
    <div class="modal-content" style="background:#fff;color:#1a1a2e;border-radius:14px;padding:24px 22px 18px;max-width:360px;width:calc(100% - 40px);box-shadow:0 20px 60px rgba(0,0,0,.35);text-align:center;">
        <div id="modalAlertIcon" style="font-size:34px;line-height:1;margin-bottom:10px;">⚠️</div>
        <h3 id="modalAlertTitle" style="margin:0 0 8px;font-size:17px;">Perhatian</h3>
        <p id="modalAlertMsg" style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#444;"></p>
        <button type="button" id="modalAlertOk"
                style="background:#1a1a2e;color:#fff;border:0;border-radius:8px;padding:9px 28px;font-size:14px;cursor:pointer;">
            Oke
        </button>
    </div>
</div>

<style>
.modal-overlay{position:fixed;inset:0;z-index:9999;background:rgba(10,10,25,.55);backdrop-filter:blur(3px);
    display:flex;align-items:center;justify-content:center;padding:20px;}
.modal-overlay[style*="display:none"]{display:none!important;}
</style>

<script>
(function () {
    var overlay = document.getElementById('modalAlert');
    if (!overlay) return;
    var msgEl = document.getElementById('modalAlertMsg');
    var iconEl = document.getElementById('modalAlertIcon');
    var titleEl = document.getElementById('modalAlertTitle');
    var okBtn = document.getElementById('modalAlertOk');

    // Helper global: showModal('Pesan', 'error' | 'success')
    window.showModal = function (message, type) {
        type = type || 'error';
        msgEl.textContent = message || '';
        iconEl.textContent = type === 'success' ? '✅' : '⚠️';
        titleEl.textContent = type === 'success' ? 'Berhasil' : 'Perhatian';
        overlay.style.display = 'flex';
        okBtn.focus();
    };

    function closeModal() { overlay.style.display = 'none'; }

    okBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.style.display !== 'none') closeModal();
    });

    // Auto-tampilkan modal untuk session flash 'success' saja.
    // Flash 'error' TIDAK dimodal lagi (permintaan user: tidak ada popup/modal
    // saat kuota habis) — error tetap tampil sebagai alert inline di view.
    @if (session('success'))
        window.addEventListener('DOMContentLoaded', function () {
            showModal(@json(session('success')), 'success');
        });
    @endif
})();
</script>
