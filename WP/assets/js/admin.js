jQuery(function ($) {
  'use strict';

  /* TERM IMAGE PICKER */
  $(document).on('click', '#kategori_thumb_btn', function (e) {
    e.preventDefault();
    const frame = wp.media({ title: 'Pilih', button: { text: 'Pakai' }, multiple: false, library: { type: 'image' } });
    frame.on('select', function () {
      const att = frame.state().get('selection').first().toJSON();
      const url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
      $('#kategori_thumb_id').val(att.id);
      $('#kategori_thumb_preview').html('<img src="' + url + '" style="max-width:200px;border-radius:6px;">');
    });
    frame.open();
  });

  $(document).on('click', '#kategori_thumb_remove', function (e) {
    e.preventDefault();
    $('#kategori_thumb_id').val('');
    $('#kategori_thumb_preview').empty();
  });

  /* TABS */
  $(document).on('click', '.merc-tab-item', function (e) {
    e.preventDefault();
    if ($(this).hasClass('merc-tab-disabled')) return;
    const tab = $(this).data('merc-tab');
    if (!tab) return;
    $('.merc-tab-item').removeClass('is-active');
    $(this).addClass('is-active');
    $('.merc-panel').removeClass('is-active');
    $('.merc-panel[data-merc-panel="' + tab + '"]').addClass('is-active');
  });

  /* PRICE PREVIEW */
  const S = window.MERCATORIA_PRICING || null;

  function rp(n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); }

  function calc(yuan, berat) {
    if (!S || !yuan) return 0;
    const base = yuan * S.kurs_yuan;
    const mk = base * (S.markup_persen / 100);
    const sh = (berat || 0) * S.biaya_per_gram;
    const raw = base + mk + sh;
    const step = Math.max(1, parseInt(S.pembulatan, 10) || 1000);
    return Math.ceil(raw / step) * step;
  }

  function recalc() {
    if (!S) return;
    const $y = $('#harga_modal_yuan');
    const $b = $('#berat_gram');
    if (!$y.length) return;
    const y = parseFloat($y.val()) || 0;
    const b = parseFloat($b.val()) || 0;
    const $v = $('[data-merc-preview-value]');
    const $bb = $('[data-mb-base]'), $bm = $('[data-mb-markup]');
    const $bs = $('[data-mb-ship]'), $bt = $('[data-mb-total]');
    const $n = $('[data-merc-preview-note]');
    if (!y) {
      $v.text('Rp0');
      $bb.text('—'); $bm.text('—'); $bs.text('—'); $bt.text('—');
      $n.html('Isi <strong>Harga Modal</strong> dulu.');
      return;
    }
    const base = y * S.kurs_yuan;
    const mk = base * (S.markup_persen / 100);
    const sh = b * S.biaya_per_gram;
    $v.text(rp(calc(y, b)));
    $bb.text(rp(base));
    $bm.text('+' + rp(mk));
    $bs.text('+' + rp(sh));
    $bt.text(rp(calc(y, b)));
  }

  $(document).on('input change', '#harga_modal_yuan, #berat_gram', recalc);
  recalc();

  /* ATRIBUT REPEATER */
  let attrIdx = $('[data-merc-attrs] [data-attr-row]').length;

  function attrHtml(i) {
    return '<div class="merc-attr-row" data-attr-row>' +
      '<div class="merc-attr-fields">' +
      '<input type="text" name="_merc_attributes[' + i + '][nama]" placeholder="Nama atribut (contoh: Warna)" class="merc-attr-nama">' +
      '<input type="text" name="_merc_attributes[' + i + '][values]" placeholder="Value, pisah pakai | (contoh: Merah | Biru)" class="merc-attr-values">' +
      '</div>' +
      '<button type="button" class="merc-variant-remove" data-merc-attr-remove>' +
      '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM19 4h-3.5l-1-1h-5l-1 1H5v2h14z"/></svg>' +
      '</button></div>';
  }

  $(document).on('click', '[data-merc-attr-add]', function (e) {
    e.preventDefault();
    $('[data-merc-attrs]').append(attrHtml(attrIdx));
    attrIdx++;
    $('[data-merc-attr-count]').text($('[data-merc-attrs] [data-attr-row]').length);
  });

  $(document).on('click', '[data-merc-attr-remove]', function (e) {
    e.preventDefault();
    $(this).closest('[data-attr-row]').remove();
    $('[data-merc-attr-count]').text($('[data-merc-attrs] [data-attr-row]').length);
  });

  /* SIMULATOR */
  const $sy = $('#sim_yuan');
  const $sr = $('#sim_result');
  if ($sy.length && $sr.length) {
    function sim() {
      const k = parseFloat($('#kurs_yuan').val()) || 0;
      const m = parseFloat($('#markup_persen').val()) || 0;
      const g = parseFloat($('#biaya_per_gram').val()) || 0;
      const b = parseInt($('#pembulatan').val(), 10) || 1000;
      const y = parseFloat($sy.val()) || 0;
      const w = parseFloat($('#sim_berat').val()) || 0;
      if (!y) { $sr.text('—'); return; }
      const t = Math.ceil((y * k * (1 + m / 100) + w * g) / b) * b;
      $sr.text('Rp' + t.toLocaleString('id-ID'));
    }
    $sy.add($('#sim_berat')).add('#kurs_yuan, #markup_persen, #biaya_per_gram, #pembulatan').on('input change', sim);
    sim();
  }

});