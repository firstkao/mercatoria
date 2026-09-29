/**
 * Bulk action helper untuk admin.
 *
 * Cara pakai:
 * 1. Bungkus form dengan [data-bulk-form]
 * 2. Checkbox per baris: [data-bulk-item] dengan name="ids[]"
 * 3. Checkbox header: [data-bulk-toggle-all]
 * 4. Toolbar: [data-bulk-toolbar] dengan [data-bulk-count] untuk display
 * 5. Tombol aksi: [data-bulk-action="publish"]
 * 6. Hidden input action: [data-bulk-action-input]
 */
(function () {
    function initBulkForm(form) {
        var toggleAll = form.querySelector('[data-bulk-toggle-all]');
        var items = form.querySelectorAll('[data-bulk-item]');
        var toolbar = form.querySelector('[data-bulk-toolbar]');
        var countEl = form.querySelector('[data-bulk-count]');
        var actionInput = form.querySelector('[data-bulk-action-input]');

        function updateCount() {
            var checked = form.querySelectorAll('[data-bulk-item]:checked').length;
            if (countEl) countEl.textContent = checked;
            if (toolbar) toolbar.classList.toggle('is-active', checked > 0);
            if (toggleAll) {
                var total = items.length;
                toggleAll.checked = total > 0 && checked === total;
                toggleAll.indeterminate = checked > 0 && checked < total;
            }
        }

        if (toggleAll) {
            toggleAll.addEventListener('change', function () {
                items.forEach(function (item) {
                    item.checked = toggleAll.checked;
                });
                updateCount();
            });
        }

        items.forEach(function (item) {
            item.addEventListener('change', updateCount);
        });

        // Action buttons
        form.querySelectorAll('[data-bulk-action]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var action = btn.dataset.bulkAction;
                var confirmMsg = btn.dataset.bulkConfirm;
                var checked = form.querySelectorAll('[data-bulk-item]:checked').length;

                if (checked === 0) {
                    alert('Pilih minimal 1 item dulu.');
                    return;
                }

                if (confirmMsg && ! confirm(confirmMsg.replace('{count}', checked))) {
                    return;
                }

                if (actionInput) actionInput.value = action;
                form.submit();
            });
        });

        updateState();
        function updateState() { updateCount(); }
    }

    document.querySelectorAll('[data-bulk-form]').forEach(initBulkForm);
})();