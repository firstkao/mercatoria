(function () {
    var configEl = document.getElementById('dev-page-config');
    if (!configEl) return;
    var CONFIG = JSON.parse(configEl.textContent);
    var CSRF = CONFIG.csrf;
    var BASE = CONFIG.baseUrl;

    // ═══ AUTO-SLUG dari nama developer ═══
    var slugManual = false;
    var nameInput = document.getElementById('dev-name-input');
    var slugInput = document.getElementById('dev-slug-input');

    if (slugInput) {
        slugInput.addEventListener('input', function () { slugManual = true; });
    }
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function () {
            if (slugManual) return;
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        });
    }

    var list = document.getElementById('dev-list');
    if (!list) return;

    // ═══ SELECT ALL ═══
    var selectAll = document.getElementById('dev-select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            var checked = this.checked;
            list.querySelectorAll('.dev-checkbox').forEach(function (cb) { cb.checked = checked; });
        });
    }

    // ═══ BULK SAVE ═══
    var bulkSaveBtn = document.getElementById('dev-bulk-save');
    if (bulkSaveBtn) {
        bulkSaveBtn.addEventListener('click', function () {
            var rows = list.querySelectorAll('[data-dev-row]');
            var pending = [];

            rows.forEach(function (row) {
                var fields = {};
                var changed = false;

                row.querySelectorAll('[data-field]').forEach(function (el) {
                    if (el.type === 'file') {
                        if (el.files && el.files.length) changed = true;
                        return;
                    }
                    var val = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
                    if (val !== el.getAttribute('data-orig')) changed = true;
                    fields[el.getAttribute('data-field')] = val;
                });

                if (changed) {
                    pending.push({
                        row: row,
                        id: row.querySelector('.dev-checkbox').getAttribute('data-id'),
                        fields: fields
                    });
                }
            });

            if (!pending.length) { AppModal.alert('Tidak ada perubahan.', { type: 'info' }); return; }

            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
            var done = 0;

            pending.forEach(function (item) {
                var form = new FormData();
                form.append('_token', CSRF);
                form.append('_method', 'PUT');
                form.append('name', item.fields.name);
                form.append('slug', item.fields.slug || '');
                form.append('is_active', item.fields.is_active);

                var logoInput = item.row.querySelector('input[data-field="logo"]');
                if (logoInput && logoInput.files && logoInput.files.length) {
                    form.append('logo', logoInput.files[0]);
                }

                fetch(BASE + '/' + item.id, { method: 'POST', body: form })
                    .then(function (r) {
                        if (!r.ok) throw new Error('Server error');
                        return r.json().catch(function () { return {}; });
                    })
                    .then(function () {
                        item.row.querySelectorAll('[data-field]').forEach(function (el) {
                            if (el.type === 'file') { el.value = ''; return; }
                            el.setAttribute('data-orig', el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value);
                        });
                        var status = item.row.querySelector('.dev-status');
                        status.textContent = '\u2713 Tersimpan';
                        status.className = 'ml-auto text-xs text-[#43A047] dev-status';
                        setTimeout(function () { status.textContent = ''; }, 3000);
                    })
                    .catch(function () {
                        var status = item.row.querySelector('.dev-status');
                        status.textContent = '\u2717 Gagal';
                        status.className = 'ml-auto text-xs text-danger dev-status';
                    })
                    .finally(function () {
                        done++;
                        if (done === pending.length) {
                            btn.disabled = false;
                            btn.textContent = 'Simpan Semua';
                        }
                    });
            });
        });
    }

    // ═══ BULK DELETE ═══
    var bulkDeleteBtn = document.getElementById('dev-bulk-delete');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            var checked = Array.from(list.querySelectorAll('.dev-checkbox:checked'));
            if (!checked.length) { AppModal.alert('Pilih developer yang mau dihapus.', { type: 'warning' }); return; }

            var btn = this;
            AppModal.confirm('Hapus ' + checked.length + ' developer terpilih? Developer yang masih terpakai akan ditolak.', { title: 'Hapus Developer', confirmText: 'Hapus', danger: true }).then(function (ok) {
            if (!ok) return;
            btn.disabled = true;
            btn.textContent = 'Menghapus...';
            var done = 0;
            var gagal = 0;

            checked.forEach(function (cb) {
                var row = cb.closest('[data-dev-row]');
                var form = new FormData();
                form.append('_token', CSRF);
                form.append('_method', 'DELETE');

                fetch(BASE + '/' + cb.getAttribute('data-id'), { method: 'POST', body: form })
                    .then(function (r) {
                        if (!r.ok) {
                            return r.text().then(function (body) {
                                // Cek redirect error (ValidationException)
                                if (body.indexOf('masih terpakai') !== -1 || body.indexOf('masih terhubung') !== -1) {
                                    throw new Error('Masih terpakai');
                                }
                                throw new Error('Server error');
                            });
                        }
                        row.remove();
                    })
                    .catch(function () {
                        gagal++;
                        if (row) {
                            var status = row.querySelector('.dev-status');
                            status.textContent = '\u2717 Masih terpakai';
                            status.className = 'ml-auto text-xs text-danger dev-status';
                        }
                    })
                    .finally(function () {
                        done++;
                        if (done === checked.length) {
                            btn.disabled = false;
                            btn.textContent = 'Hapus Terpilih';
                            if (gagal > 0) {
                                AppModal.alert(gagal + ' developer tidak bisa dihapus (masih terpakai).', { type: 'error' });
                            }
                        }
                    });
            });
            });
        });
    }

    // ═══ INDIVIDUAL ROW: detect perubahan → highlight ═══
    list.addEventListener('input', function (e) {
        if (e.target.matches('[data-field]') && e.target.type !== 'file') {
            var val = e.target.type === 'checkbox' ? (e.target.checked ? '1' : '0') : e.target.value;
            var orig = e.target.getAttribute('data-orig');
            var row = e.target.closest('[data-dev-row]');
            if (row) {
                if (val !== orig) {
                    row.classList.add('border-ink/30');
                } else {
                    // Cek apakah ada field lain yang berubah
                    var anyChanged = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        if (el.type === 'file') {
                            if (el.files && el.files.length) anyChanged = true;
                            return;
                        }
                        var v = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
                        if (v !== el.getAttribute('data-orig')) anyChanged = true;
                    });
                    if (!anyChanged) row.classList.remove('border-ink/30');
                }
            }
        }
    });

    // change event untuk checkbox
    list.addEventListener('change', function (e) {
        if (e.target.matches('input[type="checkbox"][data-field]')) {
            var val = e.target.checked ? '1' : '0';
            var orig = e.target.getAttribute('data-orig');
            var row = e.target.closest('[data-dev-row]');
            if (row) {
                if (val !== orig) {
                    row.classList.add('border-ink/30');
                } else {
                    var anyChanged = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        if (el.type === 'file') {
                            if (el.files && el.files.length) anyChanged = true;
                            return;
                        }
                        var v = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
                        if (v !== el.getAttribute('data-orig')) anyChanged = true;
                    });
                    if (!anyChanged) row.classList.remove('border-ink/30');
                }
            }
        }
    });

})();
