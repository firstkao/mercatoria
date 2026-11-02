/**
 * AppModal — pengganti alert()/confirm()/prompt() native browser.
 *
 *   AppModal.alert('Pesan', { title: 'Judul', type: 'error|success|warning|info' })  -> Promise<void>
 *   AppModal.confirm('Pesan', { title, confirmText, cancelText, danger })            -> Promise<boolean>
 *   AppModal.prompt('Pesan', { title, placeholder, inputType, value })              -> Promise<string|null>
 *
 * Deklaratif: <form data-confirm="Pesan"> atau <button data-confirm="Pesan"> (opsional data-confirm-title,
 * data-confirm-text, data-confirm-danger="1"). Mendukung form submit, link, dan tombol Livewire (wire:click).
 */
(function () {
    if (window.AppModal) return;

    var STYLE = [
        '.am-backdrop{position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,.5)}',
        '.am-dialog{width:100%;max-width:28rem;max-height:90vh;overflow-y:auto;border:1px solid #d8d2c4;border-radius:1rem;background:#fff;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);color:#141b22}',
        '.am-head{display:flex;align-items:center;gap:1rem;padding:1.25rem 2rem;border-bottom:1px solid #d8d2c4}',
        '.am-icon{display:flex;width:2.5rem;height:2.5rem;flex-shrink:0;align-items:center;justify-content:center;border-radius:9999px;font-size:1.1rem}',
        '.am-title{margin:0;font-family:"Fraunces",ui-serif,Georgia,serif;font-size:1.125rem;font-weight:600}',
        '.am-body{padding:1.5rem 2rem;font-size:.875rem;line-height:1.6;color:rgba(20,27,34,.8);white-space:pre-line;word-break:break-word}',
        '.am-input{display:block;width:100%;margin-top:.75rem;padding:.5rem .75rem;border:1px solid #d8d2c4;border-radius:.5rem;font-size:.875rem}',
        '.am-input:focus{outline:2px solid #4fc3f7;outline-offset:0}',
        '.am-foot{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 2rem;border-top:1px solid #d8d2c4;background:rgba(242,239,232,.5)}',
        '.am-btn{padding:.5rem 1.25rem;border-radius:.5rem;border:1px solid #d8d2c4;background:#fff;font-size:.875rem;font-weight:600;cursor:pointer}',
        '.am-btn:hover{background:#f2efe8}',
        '.am-btn-primary{border-color:#141b22;background:#141b22;color:#fff}',
        '.am-btn-primary:hover{background:#2a3540}',
        '.am-btn-danger{border-color:#9e2b25;background:#9e2b25;color:#fff}',
        '.am-btn-danger:hover{background:#7f221d}',
        '@media (max-width:480px){.am-head,.am-body,.am-foot{padding-left:1.25rem;padding-right:1.25rem}}'
    ].join('');

    var TYPES = {
        info: { icon: 'bi-info-circle', bg: '#e0f2fe', fg: '#0369a1', title: 'Informasi' },
        success: { icon: 'bi-check-circle', bg: '#dcfce7', fg: '#16a34a', title: 'Berhasil' },
        warning: { icon: 'bi-exclamation-triangle', bg: '#fef3c7', fg: '#d97706', title: 'Perhatian' },
        error: { icon: 'bi-x-circle', bg: '#fee2e2', fg: '#dc2626', title: 'Terjadi Kesalahan' }
    };

    function injectStyle() {
        if (document.getElementById('app-modal-style')) return;
        var s = document.createElement('style');
        s.id = 'app-modal-style';
        s.textContent = STYLE;
        document.head.appendChild(s);
    }

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined) n.textContent = text;
        return n;
    }

    function open(opts) {
        injectStyle();
        return new Promise(function (resolve) {
            var type = TYPES[opts.type] || TYPES.info;
            var previousFocus = document.activeElement;

            var backdrop = el('div', 'am-backdrop');
            var dialog = el('div', 'am-dialog');
            dialog.setAttribute('role', opts.input ? 'dialog' : 'alertdialog');
            dialog.setAttribute('aria-modal', 'true');

            var head = el('div', 'am-head');
            var icon = el('div', 'am-icon');
            icon.style.background = type.bg;
            icon.style.color = type.fg;
            icon.appendChild(el('i', 'bi ' + type.icon));
            head.appendChild(icon);
            head.appendChild(el('h3', 'am-title', opts.title || type.title));

            var body = el('div', 'am-body', opts.message || '');
            var input = null;
            if (opts.input) {
                input = el('input', 'am-input');
                input.type = opts.inputType || 'text';
                input.placeholder = opts.placeholder || '';
                input.value = opts.value || '';
                body.appendChild(input);
            }

            var foot = el('div', 'am-foot');
            var cancelBtn = null;
            if (opts.cancelText) {
                cancelBtn = el('button', 'am-btn', opts.cancelText);
                cancelBtn.type = 'button';
                foot.appendChild(cancelBtn);
            }
            var okBtn = el('button', 'am-btn ' + (opts.danger ? 'am-btn-danger' : 'am-btn-primary'), opts.confirmText || 'OK');
            okBtn.type = 'button';
            foot.appendChild(okBtn);

            dialog.appendChild(head);
            dialog.appendChild(body);
            dialog.appendChild(foot);
            backdrop.appendChild(dialog);
            document.body.appendChild(backdrop);

            function close(result) {
                document.removeEventListener('keydown', onKey, true);
                backdrop.remove();
                if (previousFocus && previousFocus.focus) previousFocus.focus();
                resolve(result);
            }
            var dismissValue = opts.input ? null : false;
            var okValue = function () { return opts.input ? input.value : true; };

            function onKey(e) {
                if (e.key === 'Escape') { e.stopPropagation(); close(opts.cancelText ? dismissValue : okValue()); }
                if (e.key === 'Enter' && (opts.input || document.activeElement === okBtn)) { e.preventDefault(); close(okValue()); }
            }
            document.addEventListener('keydown', onKey, true);

            okBtn.addEventListener('click', function () { close(okValue()); });
            if (cancelBtn) cancelBtn.addEventListener('click', function () { close(dismissValue); });
            backdrop.addEventListener('mousedown', function (e) {
                if (e.target === backdrop) close(opts.cancelText ? dismissValue : okValue());
            });

            (input || okBtn).focus();
        });
    }

    window.AppModal = {
        alert: function (message, o) {
            o = o || {};
            return open({ message: message, title: o.title, type: o.type || 'info', confirmText: o.confirmText || 'OK' });
        },
        confirm: function (message, o) {
            o = o || {};
            return open({
                message: message,
                title: o.title || 'Konfirmasi',
                type: o.danger ? 'error' : 'warning',
                danger: !!o.danger,
                confirmText: o.confirmText || 'Ya, Lanjutkan',
                cancelText: o.cancelText || 'Batal'
            });
        },
        prompt: function (message, o) {
            o = o || {};
            return open({
                message: message,
                title: o.title || 'Input',
                type: 'info',
                input: true,
                inputType: o.inputType,
                placeholder: o.placeholder,
                value: o.value,
                confirmText: o.confirmText || 'Simpan',
                cancelText: o.cancelText || 'Batal'
            });
        }
    };

    // ── data-confirm: form submit & klik (termasuk tombol Livewire) ──
    var bypass = new WeakSet();

    function confirmOptions(node) {
        return {
            title: node.getAttribute('data-confirm-title') || undefined,
            confirmText: node.getAttribute('data-confirm-text') || undefined,
            danger: node.getAttribute('data-confirm-danger') === '1'
        };
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm') || bypass.has(form)) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        var submitter = e.submitter;
        window.AppModal.confirm(form.getAttribute('data-confirm'), confirmOptions(form)).then(function (ok) {
            if (!ok) return;
            bypass.add(form);
            if (form.requestSubmit) { form.requestSubmit(submitter || undefined); } else { form.submit(); }
            bypass.delete(form);
        });
    }, true);

    document.addEventListener('click', function (e) {
        var node = e.target.closest ? e.target.closest('[data-confirm]') : null;
        if (!node || node instanceof HTMLFormElement || bypass.has(node)) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        window.AppModal.confirm(node.getAttribute('data-confirm'), confirmOptions(node)).then(function (ok) {
            if (!ok) return;
            bypass.add(node);
            node.click();
            bypass.delete(node);
        });
    }, true);
})();
