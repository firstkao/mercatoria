jQuery(function ($) {
  'use strict';

  const cfg = window.MERCATORIA || {};
  const ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
  const nonce = cfg.nonce || '';

  /* ============================================================
     COPY TO CLIPBOARD
     ============================================================ */
  $(document).on('click', '[data-copy-btn]', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const $parent = $btn.closest('[data-copy-text]');
    const text = $parent.data('copy-text') || '';
    if (!text) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(String(text)).then(function () {
        const orig = $btn.text();
        $btn.text('✓ Copied');
        setTimeout(function () { $btn.text(orig); }, 1500);
      });
    } else {
      const $temp = $('<textarea>').val(text).appendTo('body').select();
      document.execCommand('copy');
      $temp.remove();
      const orig = $btn.text();
      $btn.text('✓ Copied');
      setTimeout(function () { $btn.text(orig); }, 1500);
    }
  });

  /* ============================================================
     SUBMIT CHECKOUT
     ============================================================ */
  $(document).on('submit', '#merc-checkout-form', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('.checkout-submit-btn, .checkout-submit-btn-m');
    const $btnLabel = $btn.find('span');
    const $notice = $form.find('[data-checkout-notice]');
    const originalText = $btnLabel.text();

    // Reset notice
    $notice.attr('hidden', true).removeClass('notice-success notice-error').text('');

    // Disable button
    $btn.prop('disabled', true);
    $btnLabel.text('Memproses…');

    // Kumpulin data
    const data = {
      action: 'merc_checkout_submit',
      nonce: nonce,
      customer_name:  $form.find('[name="customer_name"]').val(),
      customer_email: $form.find('[name="customer_email"]').val(),
      customer_phone: $form.find('[name="customer_phone"]').val(),
      address_line1:  $form.find('[name="address_line1"]').val(),
      address_line2:  $form.find('[name="address_line2"]').val(),
      city:           $form.find('[name="city"]').val(),
      province:       $form.find('[name="province"]').val(),
      postal_code:    $form.find('[name="postal_code"]').val(),
      notes:          $form.find('[name="notes"]').val(),
      payment_method: $form.find('[name="payment_method"]:checked').val() || '',
      website:        $form.find('[name="website"]').val() || '',
    };

    // Client-side validation dasar
    const required = ['customer_name', 'customer_email', 'customer_phone', 'address_line1', 'city', 'province'];
    for (let i = 0; i < required.length; i++) {
      if (!data[required[i]]) {
        $notice.removeAttr('hidden').addClass('notice notice-error').text('Field wajib belum lengkap.');
        $btn.prop('disabled', false);
        $btnLabel.text(originalText);
        return;
      }
    }
    if (!data.payment_method) {
      $notice.removeAttr('hidden').addClass('notice notice-error').text('Pilih metode pembayaran.');
      $btn.prop('disabled', false);
      $btnLabel.text(originalText);
      return;
    }

    $.ajax({
      url: ajaxUrl,
      type: 'POST',
      dataType: 'json',
      data: data,
    }).done(function (res) {
      if (res && res.success && res.data && res.data.redirect) {
        $btnLabel.text('✓ Berhasil');
        window.location.href = res.data.redirect;
      } else {
        const msg = (res && res.data && res.data.message) ? res.data.message : 'Gagal membuat order. Coba lagi.';
        $notice.removeAttr('hidden').addClass('notice notice-error').text(msg);
        $btn.prop('disabled', false);
        $btnLabel.text(originalText);
      }
    }).fail(function (xhr) {
      let msg = 'Terjadi kesalahan. Coba lagi.';
      if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
        msg = xhr.responseJSON.data.message;
      }
      $notice.removeAttr('hidden').addClass('notice notice-error').text(msg);
      $btn.prop('disabled', false);
      $btnLabel.text(originalText);
    });
  });

});