(function () {
  'use strict';

  /* ============================================================
     BUAT MODAL DOM (sekali aja)
     ============================================================ */
  let modalBackdrop = null;
  let modalEl = null;
  let currentResolve = null;

  function ensureModal() {
    if (modalEl) return;

    modalBackdrop = document.createElement('div');
    modalBackdrop.className = 'merc-modal-backdrop';
    document.body.appendChild(modalBackdrop);

    modalEl = document.createElement('div');
    modalEl.className = 'merc-modal';
    modalEl.setAttribute('role', 'dialog');
    modalEl.setAttribute('aria-modal', 'true');
    modalEl.innerHTML =
      '<div class="merc-modal-body">' +
        '<div class="merc-modal-icon" data-modal-icon></div>' +
        '<div class="merc-modal-title" data-modal-title></div>' +
        '<div class="merc-modal-desc" data-modal-desc></div>' +
      '</div>' +
      '<div class="merc-modal-actions">' +
        '<button type="button" class="merc-modal-btn merc-modal-btn-cancel" data-modal-cancel>Batal</button>' +
        '<button type="button" class="merc-modal-btn merc-modal-btn-confirm" data-modal-confirm>Ya</button>' +
      '</div>';
    document.body.appendChild(modalEl);

    // Events
    modalBackdrop.addEventListener('click', closeModal);
    modalEl.querySelector('[data-modal-cancel]').addEventListener('click', function () {
      closeModal();
      if (currentResolve) { currentResolve(false); currentResolve = null; }
    });
    modalEl.querySelector('[data-modal-confirm]').addEventListener('click', function () {
      closeModal();
      if (currentResolve) { currentResolve(true); currentResolve = null; }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalEl.classList.contains('is-open')) {
        closeModal();
        if (currentResolve) { currentResolve(false); currentResolve = null; }
      }
    });
  }

  function closeModal() {
    if (!modalEl) return;
    modalEl.classList.remove('is-open');
    modalBackdrop.classList.remove('is-open');
    document.body.classList.remove('no-scroll');
  }

  const ICONS = {
    danger: '<svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM19 4h-3.5l-1-1h-5l-1 1H5v2h14z"/></svg>',
    warning: '<svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
    info: '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>'
  };

  /* ============================================================
     PUBLIC API — window.MercatoriaModal.confirm()
     ============================================================ */
  window.MercatoriaModal = {
    confirm: function (opts) {
      ensureModal();

      opts = opts || {};
      const title   = opts.title   || 'Yakin?';
      const desc    = opts.desc    || '';
      const confirmText = opts.confirmText || 'Ya, lanjutkan';
      const cancelText  = opts.cancelText  || 'Batal';
      const type    = opts.type    || 'danger'; // danger | warning | info
      const primary = !!opts.primary;

      const $icon = modalEl.querySelector('[data-modal-icon]');
      $icon.className = 'merc-modal-icon ' + (type === 'warning' ? 'is-warning' : type === 'info' ? 'is-info' : '');
      $icon.innerHTML = ICONS[type] || ICONS.danger;

      modalEl.querySelector('[data-modal-title]').textContent = title;
      modalEl.querySelector('[data-modal-desc]').textContent = desc;

      const $confirm = modalEl.querySelector('[data-modal-confirm]');
      $confirm.textContent = confirmText;
      $confirm.className = 'merc-modal-btn merc-modal-btn-confirm' + (primary ? ' is-primary' : '');

      modalEl.querySelector('[data-modal-cancel]').textContent = cancelText;

      // Open
      modalBackdrop.classList.add('is-open');
      modalEl.classList.add('is-open');
      document.body.classList.add('no-scroll');

      // Focus confirm button
      setTimeout(function () { $confirm.focus(); }, 100);

      return new Promise(function (resolve) {
        currentResolve = resolve;
      });
    },
  };
})();