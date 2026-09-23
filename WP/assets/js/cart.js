jQuery(function ($) {
  'use strict';

  const cfg = window.MERCATORIA || {};
  const ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
  const nonce = cfg.nonce || '';

  function rp(n) {
    return 'Rp' + Math.round(n).toLocaleString('id-ID');
  }

  /* ============================================================
     RENDER DRAWER
     ============================================================ */
  function renderDrawer(cart) {
    const $items  = $('[data-cart-items]');
    const $footer = $('[data-cart-footer]');

    if (!$items.length) return;

    if (!cart.items || !cart.items.length) {
      $items.html('<div class="cart-empty"><p>Keranjang lo masih kosong.</p><a href="' + (cfg.homeUrl || '/') + 'produk/" class="btn btn-accent mt-3">Mulai belanja</a></div>');
      $footer.attr('hidden', true);
      return;
    }

    let html = '';
    cart.items.forEach(function (item) {
      const img = item.image ? '<img src="' + item.image + '" alt="">' : '<div class="drawer-item-img-placeholder"></div>';
      const variant = item.variation_label ? '<div class="drawer-variant">' + item.variation_label + '</div>' : '';
      html += '<div class="drawer-item" data-line-key="' + item.key + '">' +
        '<a href="' + item.permalink + '">' + img + '</a>' +
        '<div>' +
          '<a href="' + item.permalink + '" class="drawer-title">' + item.display_title + '</a>' +
          variant +
          '<div class="drawer-qty">' + item.qty + ' × ' + rp(item.price) + '</div>' +
          '<button type="button" class="drawer-remove" data-cart-remove="' + item.key + '">Hapus</button>' +
        '</div>' +
      '</div>';
    });

    $items.html(html);
    $footer.removeAttr('hidden');
    $('[data-cart-subtotal-footer]').text(rp(cart.subtotal));
  }

  /* ============================================================
     RENDER CART PAGE
     ============================================================ */
  function renderPage(cart) {
    const $page = $('[data-cart-page]');
    if (!$page.length) return;

    const $itemsCont = $page.find('[data-cart-page-items]');
    const $totals    = $page.find('[data-cart-page-totals]');

    if (!cart.items || !cart.items.length) {
      $itemsCont.html('<div class="cart-page-empty"><p>Keranjang lo masih kosong.</p><a href="' + (cfg.homeUrl || '/') + 'produk/" class="btn btn-accent mt-3">Mulai belanja</a></div>');
      $totals.attr('hidden', true);
      return;
    }

    let html = '';
    cart.items.forEach(function (item) {
      const img = item.image ? '<img src="' + item.image + '" alt="">' : '<div class="cart-row-img-placeholder"></div>';
      const variant = item.variation_label ? '<div class="cart-row-variant">' + item.variation_label + '</div>' : '';
      html += '<div class="cart-row" data-line-key="' + item.key + '">' +
        '<div class="cart-row-product">' +
          '<a href="' + item.permalink + '">' + img + '</a>' +
          '<div>' +
            '<a href="' + item.permalink + '" class="cart-row-title">' + item.display_title + '</a>' +
            variant +
            '<button type="button" class="cart-row-remove" data-cart-remove="' + item.key + '">Hapus</button>' +
          '</div>' +
        '</div>' +
        '<div class="cart-row-price">' + rp(item.price) + '</div>' +
        '<div class="cart-row-qty">' +
          '<div class="qty-control-sm">' +
            '<button type="button" data-cart-qty="' + item.key + '" data-dir="-1">−</button>' +
            '<input type="number" value="' + item.qty + '" min="1" readonly>' +
            '<button type="button" data-cart-qty="' + item.key + '" data-dir="1">+</button>' +
          '</div>' +
        '</div>' +
        '<div class="cart-row-subtotal">' + rp(item.subtotal) + '</div>' +
      '</div>';
    });

    $itemsCont.html(html);
    $totals.removeAttr('hidden');
    $page.find('[data-cart-page-subtotal]').text(rp(cart.subtotal));
    $page.find('[data-cart-page-ongkir]').text(rp(cart.ongkir));
    $page.find('[data-cart-page-total]').text(rp(cart.total));
    $page.find('[data-cart-page-ongkir-label]').text(cart.ongkir_label || '');
  }

  /* ============================================================
     UPDATE HEADER
     ============================================================ */
  function updateHeader(cart) {
    const $count = $('[data-cart-count]');
    const $sub   = $('[data-cart-subtotal]');

    $count.text(cart.count);
    $count.css('display', cart.count > 0 ? '' : 'none');
    $sub.text(rp(cart.subtotal));
  }

  function renderAll(cart) {
    renderDrawer(cart);
    renderPage(cart);
    updateHeader(cart);
  }

  /* ============================================================
     AJAX
     ============================================================ */
  function ajax(action, data) {
    return $.ajax({
      url: ajaxUrl,
      type: 'POST',
      dataType: 'json',
      data: $.extend({ action: action, nonce: nonce }, data || {}),
    });
  }

  function loadCart() {
    ajax('merc_cart_get').done(function (res) {
      if (res && res.success && res.data && res.data.cart) {
        renderAll(res.data.cart);
      }
    });
  }

  function addToCart(payload) {
    return ajax('merc_cart_add', payload).done(function (res) {
      if (res && res.success && res.data && res.data.cart) {
        renderAll(res.data.cart);
        if (window.MercatoriaUI && window.MercatoriaUI.open) {
          window.MercatoriaUI.open();
        }
      } else if (res && !res.success) {
        const msg = (res.data && res.data.message) ? res.data.message : 'Gagal menambahkan.';
        if (window.MercatoriaModal) {
          window.MercatoriaModal.confirm({
            title: 'Gagal Menambahkan',
            desc: msg,
            confirmText: 'OK',
            cancelText: 'Tutup',
            type: 'warning',
            primary: true,
          });
        } else {
          alert(msg);
        }
      }
    });
  }

  function updateQty(key, qty) {
    ajax('merc_cart_update', { key: key, qty: qty }).done(function (res) {
      if (res && res.success && res.data && res.data.cart) {
        renderAll(res.data.cart);
      }
    });
  }

  function removeLine(key) {
    ajax('merc_cart_remove', { key: key }).done(function (res) {
      if (res && res.success && res.data && res.data.cart) {
        renderAll(res.data.cart);
      }
    });
  }

  /* ============================================================
     EVENTS
     ============================================================ */
  $(document).on('merc:add-to-cart', function (e, payload) {
    if (!payload) return;
    addToCart(payload);
  });

  $(document).on('click', '[data-cart-qty]', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const key  = $btn.data('cart-qty');
    const dir  = parseInt($btn.data('dir'), 10) || 0;
    const $input = $btn.siblings('input');
    const cur  = parseInt($input.val(), 10) || 1;
    const next = Math.max(1, cur + dir);
    if (next === cur) return;
    $input.val(next);
    updateQty(key, next);
  });

  /* Remove — pakai modal custom */
  $(document).on('click', '[data-cart-remove]', function (e) {
    e.preventDefault();
    const key = $(this).data('cart-remove');
    if (!key) return;

    function doRemove() {
      removeLine(key);
    }

    if (window.MercatoriaModal) {
      window.MercatoriaModal.confirm({
        title: 'Hapus dari Keranjang?',
        desc: 'Produk ini akan dihapus dari keranjang lo.',
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        type: 'danger',
      }).then(function (ok) {
        if (ok) doRemove();
      });
    } else {
      if (confirm('Hapus produk ini?')) doRemove();
    }
  });

  /* ============================================================
     INIT
     ============================================================ */
  loadCart();

  window.MercatoriaCart = {
    load: loadCart,
    add: addToCart,
    render: renderAll,
  };
});