jQuery(function ($) {
  'use strict';

  /* ============================================================
     HELPER: Alert Modal
     ============================================================ */
  function alertModal(title, desc, type) {
    if (window.MercatoriaModal) {
      return window.MercatoriaModal.confirm({
        title: title,
        desc: desc || '',
        confirmText: 'OK',
        cancelText: 'Tutup',
        type: type || 'warning',
        primary: true,
      });
    } else {
      alert(title + (desc ? '\n\n' + desc : ''));
      return Promise.resolve(true);
    }
  }

  /* ============================================================
     GALLERY
     ============================================================ */
  $(document).on('click', '[data-gallery-thumb]', function () {
    const url = $(this).data('img-url');
    if (!url) return;
    const $main = $('[data-gallery-main]');
    $main.find('img').attr('src', url).attr('srcset', '').attr('sizes', '');
    $main.find('.gallery-placeholder').replaceWith('<img src="' + url + '" class="gallery-main-img">');
    $('[data-gallery-thumb]').removeClass('is-active');
    $(this).addClass('is-active');
  });

  /* ============================================================
     TABS
     ============================================================ */
  $(document).on('click', '[data-single-tab]', function () {
    const tab = $(this).data('single-tab');
    if (!tab) return;
    $('[data-single-tab]').removeClass('is-active');
    $(this).addClass('is-active');
    $('[data-single-panel]').removeClass('is-active');
    $('[data-single-panel="' + tab + '"]').addClass('is-active');
  });

  /* ============================================================
     QTY
     ============================================================ */
  $(document).on('click', '[data-qty-minus]', function (e) {
    e.preventDefault();
    const $input = $(this).siblings('[data-qty-input]');
    const v = parseInt($input.val(), 10) || 1;
    $input.val(Math.max(1, v - 1));
  });

  $(document).on('click', '[data-qty-plus]', function (e) {
    e.preventDefault();
    const $input = $(this).siblings('[data-qty-input]');
    const v = parseInt($input.val(), 10) || 1;
    $input.val(v + 1);
  });

  /* ============================================================
     VARIAN + KOIN
     ============================================================ */
  const variations = window.MERC_VARIATIONS || [];
  const hasVariasi = window.MERC_HAS_VARIASI === true;
  const settings   = window.MERCATORIA_PRICING || null;
  const koinRate   = window.MERC_KOIN_RATE || 100;
  const koinOn     = window.MERC_KOIN_ENABLED === true;

  function rp(n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); }

  function calcPrice(yuan, berat) {
    if (!settings || !yuan) return 0;
    const base = yuan * settings.kurs_yuan;
    const mk = base * (settings.markup_persen / 100);
    const sh = (berat || 0) * settings.biaya_per_gram;
    const step = Math.max(1, parseInt(settings.pembulatan, 10) || 1000);
    return Math.ceil((base + mk + sh) / step) * step;
  }

  function getSelectedAttrs() {
    const sel = {};
    $('.variation-pill.is-active').each(function () {
      const name = $(this).data('attr-name');
      const val = $(this).data('value');
      if (name && val !== undefined) {
        sel[name] = String(val);
      }
    });
    return sel;
  }

  function findVariation(selected) {
    return variations.find(function (v) {
      const komb = v.kombinasi || {};
      return Object.keys(selected).every(function (k) {
        return komb[k] === selected[k];
      });
    });
  }

  function setAddBtnOOS(isOOS) {
    const $btn = $('[data-add-to-cart]');
    if (!$btn.length) return;

    if (isOOS) {
      if (!$btn.hasClass('is-oos-static')) {
        $btn.data('original-text', $btn.find('span').text());
      }
      $btn.prop('disabled', true).addClass('is-oos is-oos-static');
      $btn.find('span').text('Stok Habis');
    } else {
      if ($btn.hasClass('is-oos-static')) {
        $btn.prop('disabled', false).removeClass('is-oos is-oos-static');
        $btn.find('span').text($btn.data('original-text') || 'Tambah ke Keranjang');
      }
    }
  }

  function updateKoin(harga) {
    if (!koinOn) return;
    const koin = Math.floor(harga / koinRate);
    $('[data-koin-value]').text(koin.toLocaleString('id-ID'));
  }

  function updateFromSelection() {
    const selected = getSelectedAttrs();
    const $price = $('[data-single-price]');
    const $stock = $('[data-var-stock]');

    const totalGroups = $('[data-attr-group]').length;
    if (Object.keys(selected).length < totalGroups) {
      $stock.text('');
      return;
    }

    const matched = findVariation(selected);

    if (!matched) {
      $price.text('Kombinasi tidak tersedia');
      $stock.html('<span style="color:#ef4444;">Varian tidak ditemukan</span>');
      setAddBtnOOS(true);
      return;
    }

    const harga = calcPrice(matched.harga_modal_yuan, matched.berat_gram);
    $price.text(rp(harga));
    updateKoin(harga);

    const varInactive = matched.status !== 'active';
    const varOOS = matched.stok !== '' && matched.stok !== null && parseInt(matched.stok, 10) === 0;

    let stokHtml = '';
    if (varInactive) {
      stokHtml = '<span style="color:#ef4444;">Varian ini tidak dijual</span>';
    } else if (varOOS) {
      stokHtml = '<span style="color:#ef4444;">Stok habis</span>';
    } else if (matched.stok === '' || matched.stok === null) {
      stokHtml = '';
    } else {
      stokHtml = '<span style="color:#10b981;">Stok: ' + matched.stok + '</span>';
    }
    $stock.html(stokHtml);

    if (matched.gambar_url) {
      $('[data-gallery-main]').find('img').attr('src', matched.gambar_url).attr('srcset', '').attr('sizes', '');
    }

    setAddBtnOOS(varInactive || varOOS);
  }

  $(document).on('click', '.variation-pill', function (e) {
    e.preventDefault();
    if ($(this).hasClass('is-disabled')) return;

    const $group = $(this).closest('[data-attr-group]');

    if ($(this).hasClass('is-active')) {
      $(this).removeClass('is-active');
      updateFromSelection();
      return;
    }

    $group.find('.variation-pill').removeClass('is-active');
    $(this).addClass('is-active');
    updateFromSelection();
  });

  /* ============================================================
     ADD TO CART
     ============================================================ */
  $(document).on('click', '[data-add-to-cart]', function (e) {
    e.preventDefault();

    const $btn = $(this);
    if ($btn.prop('disabled') || $btn.hasClass('is-oos')) return;

    const productId = $btn.data('product-id');
    const qty = parseInt($('[data-qty-input]').first().val(), 10) || 1;

    let variationIndex = null;

    if (hasVariasi) {
      const selected = getSelectedAttrs();
      const totalGroups = $('[data-attr-group]').length;

      if (Object.keys(selected).length < totalGroups) {
        alertModal('Pilih Varian Dulu', 'Lo harus pilih semua varian sebelum tambah ke keranjang.', 'warning');
        return;
      }

      const matched = findVariation(selected);
      if (!matched) {
        alertModal('Kombinasi Tidak Tersedia', 'Kombinasi varian ini tidak ada. Coba pilih kombinasi lain.', 'warning');
        return;
      }

      if (matched.status !== 'active') {
        alertModal('Varian Tidak Dijual', 'Varian ini sedang tidak dijual.', 'warning');
        return;
      }

      const varOOS = matched.stok !== '' && matched.stok !== null && parseInt(matched.stok, 10) === 0;
      if (varOOS) {
        alertModal('Stok Habis', 'Stok varian ini habis. Coba pilih varian lain.', 'danger');
        return;
      }

      variationIndex = variations.indexOf(matched);
    }

    const payload = {
      product_id: productId,
      qty: qty,
      variation_index: variationIndex,
    };

    $(document).trigger('merc:add-to-cart', [ payload ]);

    const $label = $btn.find('span');
    const original = $label.text();
    $label.text('✓ Ditambahkan');
    $btn.addClass('is-added');

    setTimeout(function () {
      $label.text(original);
      $btn.removeClass('is-added');
    }, 1500);
  });

});