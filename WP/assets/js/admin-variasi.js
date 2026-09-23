jQuery(function ($) {
  'use strict';

  const S = window.MERCATORIA_PRICING || null;
  if (!S) return;

  function rp(n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); }

  function calc(yuan, berat) {
    if (!yuan) return 0;
    const base = yuan * S.kurs_yuan;
    const mk = base * (S.markup_persen / 100);
    const sh = (berat || 0) * S.biaya_per_gram;
    const step = Math.max(1, parseInt(S.pembulatan, 10) || 1000);
    return Math.ceil((base + mk + sh) / step) * step;
  }

  function readAttrs() {
    const attrs = [];
    $('[data-merc-attrs] [data-attr-row]').each(function () {
      const n = $(this).find('.merc-attr-nama').val().trim();
      const v = $(this).find('.merc-attr-values').val().trim();
      if (!n || !v) return;
      const vals = v.split('|').map(function (x) { return x.trim(); }).filter(function (x) { return x; });
      if (!vals.length) return;
      attrs.push({ nama: n, values: vals });
    });
    return attrs;
  }

  function cartesian(attrs) {
    if (!attrs.length) return [];
    return attrs.reduce(function (acc, attr) {
      const out = [];
      acc.forEach(function (combo) {
        attr.values.forEach(function (val) {
          const c = Object.assign({}, combo);
          c[attr.nama] = val;
          out.push(c);
        });
      });
      return out;
    }, [{}]);
  }

  function buildRow(idx, combo, label, prev) {
    const yuan = prev.yuan || '';
    const berat = prev.berat || '';
    const sku = prev.sku || '';
    const gid = prev.gambar_id || 0;
    const gurl = prev.gambar_url || '';
    const stok = prev.stok || '';
    const status = prev.status || 'active';
    const auto = (yuan) ? calc(parseFloat(yuan), parseFloat(berat || 0)) : 0;
    const autoTxt = auto ? '→ ' + rp(auto) : '→ —';
    const img = gurl ? '<img src="' + gurl + '" alt="">' : '<span class="merc-img-placeholder">🖼</span>';

    return '<div class="merc-variation-row" data-variation-row data-status="' + status + '">' +
      '<div class="merc-var-header">' +
      '<div class="merc-var-label">' +
      '<span class="merc-var-dot ' + (status === 'active' ? 'is-on' : 'is-off') + '" data-merc-var-status-toggle></span>' +
      '<strong>' + label + '</strong></div>' +
      '<div class="merc-var-actions">' +
      '<button type="button" class="merc-var-toggle-details" data-merc-var-toggle-details><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg></button>' +
      '<button type="button" class="merc-variant-remove" data-merc-var-remove><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM19 4h-3.5l-1-1h-5l-1 1H5v2h14z"/></svg></button>' +
      '</div></div>' +
      '<div class="merc-var-body">' +
      '<input type="hidden" name="_merc_variations[' + idx + '][status]" value="' + status + '" data-merc-var-status-input>' +
      '<input type="hidden" name="_merc_variations[' + idx + '][kombinasi]" value=\'' + JSON.stringify(combo) + '\'>' +
      '<input type="hidden" name="_merc_variations[' + idx + '][kombinasi_cache]" value="' + label + '">' +
      '<div class="merc-var-grid">' +
      '<div class="merc-var-field merc-var-field-img"><label>Gambar</label>' +
      '<div class="merc-var-img-box">' +
      '<div class="merc-var-img-preview" data-merc-var-img-preview>' + img + '</div>' +
      '<input type="hidden" name="_merc_variations[' + idx + '][gambar_id]" value="' + gid + '" data-merc-var-img-id>' +
      '<div class="merc-var-img-actions">' +
      '<button type="button" class="merc-btn-mini" data-merc-var-img-pick>Pilih</button>' +
      '<button type="button" class="merc-btn-mini merc-btn-mini-danger" data-merc-var-img-remove>×</button>' +
      '</div></div></div>' +
      '<div class="merc-var-field"><label>Harga Modal (¥)</label>' +
      '<div class="input-prefix"><span>¥</span>' +
      '<input type="number" name="_merc_variations[' + idx + '][harga_modal_yuan]" value="' + yuan + '" step="0.01" min="0" placeholder="0.00" class="merc-var-yuan">' +
      '</div>' +
      '<div class="merc-var-auto-price" data-merc-var-price-preview>' + autoTxt + '</div></div>' +
      '<div class="merc-var-field"><label>Berat</label>' +
      '<div class="input-suffix">' +
      '<input type="number" name="_merc_variations[' + idx + '][berat_gram]" value="' + berat + '" step="1" min="0" placeholder="0" class="merc-var-berat">' +
      '<span>gram</span></div></div>' +
      '<div class="merc-var-field"><label>SKU</label>' +
      '<input type="text" name="_merc_variations[' + idx + '][sku]" value="' + sku + '" placeholder="MRC-001-RED" class="merc-var-sku"></div>' +
      '<div class="merc-var-field"><label>Stok</label>' +
      '<input type="number" name="_merc_variations[' + idx + '][stok]" value="' + stok + '" step="1" min="0" placeholder="∞" class="merc-var-stok"></div>' +
      '</div></div></div>';
  }

  $(document).on('click', '[data-merc-generate-variasi]', function (e) {
    e.preventDefault();
    const attrs = readAttrs();
    if (!attrs.length) { alert('Isi minimal 1 atribut dulu.'); return; }
    const combos = cartesian(attrs);
    if (!combos.length) { alert('Gagal generate.'); return; }

    const existing = $('[data-merc-variations] [data-variation-row]').length;
    if (existing && !confirm('Ada ' + existing + ' variasi existing. Data dengan kombinasi sama akan dipertahankan. Lanjut?')) return;

    const map = {};
    $('[data-merc-variations] [data-variation-row]').each(function () {
      const k = $(this).find('input[name$="[kombinasi_cache]"]').val();
      if (!k) return;
      map[k] = {
        yuan: $(this).find('input[name$="[harga_modal_yuan]"]').val(),
        berat: $(this).find('input[name$="[berat_gram]"]').val(),
        sku: $(this).find('input[name$="[sku]"]').val(),
        gambar_id: $(this).find('input[name$="[gambar_id]"]').val(),
        gambar_url: $(this).find('.merc-var-img-preview img').attr('src') || '',
        stok: $(this).find('input[name$="[stok]"]').val(),
        status: $(this).find('input[name$="[status]"]').val() || 'active'
      };
    });

    const $c = $('[data-merc-variations]');
    if (!$c.length) { alert('Container gak ketemu.'); return; }
    $c.empty();

    combos.forEach(function (combo, idx) {
      const label = Object.keys(combo).map(function (k) { return k + ': ' + combo[k]; }).join(' · ');
      $c.append(buildRow(idx, combo, label, map[label] || {}));
    });

    $('[data-merc-variasi-empty]').hide();
    updateBadge();
    updateRange();
  });

  /* VAR ROW ACTIONS */
  $(document).on('click', '.merc-var-header', function (e) {
    if ($(e.target).closest('button, .merc-var-dot').length) return;
    $(this).closest('[data-variation-row]').toggleClass('is-expanded');
  });

  $(document).on('click', '[data-merc-var-toggle-details]', function (e) {
    e.preventDefault();
    $(this).closest('[data-variation-row]').toggleClass('is-expanded');
  });

  $(document).on('click', '[data-merc-var-status-toggle]', function (e) {
    e.preventDefault();
    const $r = $(this).closest('[data-variation-row]');
    const $d = $(this);
    const isOn = $d.hasClass('is-on');
    if (isOn) {
      $d.removeClass('is-on').addClass('is-off');
      $r.find('[data-merc-var-status-input]').val('inactive');
      $r.attr('data-status', 'inactive');
    } else {
      $d.removeClass('is-off').addClass('is-on');
      $r.find('[data-merc-var-status-input]').val('active');
      $r.attr('data-status', 'active');
    }
    updateRange();
  });

  $(document).on('click', '[data-merc-var-remove]', function (e) {
    e.preventDefault();
    if (!confirm('Hapus varian ini?')) return;
    $(this).closest('[data-variation-row]').remove();
    updateBadge();
    updateRange();
  });

  $(document).on('click', '[data-merc-clear-variasi]', function (e) {
    e.preventDefault();
    if (!confirm('Hapus SEMUA variasi?')) return;
    $('[data-merc-variations]').empty();
    updateBadge();
    updateRange();
  });

  /* IMAGE PICKER */
  $(document).on('click', '[data-merc-var-img-pick]', function (e) {
    e.preventDefault();
    const $r = $(this).closest('[data-variation-row]');
    const f = wp.media({ title: 'Pilih Gambar', button: { text: 'Pakai' }, multiple: false, library: { type: 'image' } });
    f.on('select', function () {
      const a = f.state().get('selection').first().toJSON();
      const u = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
      $r.find('[data-merc-var-img-id]').val(a.id);
      $r.find('[data-merc-var-img-preview]').html('<img src="' + u + '" alt="">');
    });
    f.open();
  });

  $(document).on('click', '[data-merc-var-img-remove]', function (e) {
    e.preventDefault();
    const $r = $(this).closest('[data-variation-row]');
    $r.find('[data-merc-var-img-id]').val('');
    $r.find('[data-merc-var-img-preview]').html('<span class="merc-img-placeholder">🖼</span>');
  });

  /* AUTO PRICE + RANGE */
  function updateAuto($r) {
    const y = parseFloat($r.find('.merc-var-yuan').val()) || 0;
    const b = parseFloat($r.find('.merc-var-berat').val()) || 0;
    const $l = $r.find('[data-merc-var-price-preview]');
    $l.text(y ? '→ ' + rp(calc(y, b)) : '→ —');
  }

  function updateRange() {
    const ps = [];
    $('[data-variation-row]').each(function () {
      if ($(this).attr('data-status') !== 'active') return;
      const y = parseFloat($(this).find('.merc-var-yuan').val()) || 0;
      const b = parseFloat($(this).find('.merc-var-berat').val()) || 0;
      if (!y) return;
      ps.push(calc(y, b));
    });
    const $r = $('[data-merc-variant-range]');
    if (!$r.length) return;
    if (!ps.length) { $r.text('—'); return; }
    const mn = Math.min.apply(null, ps);
    const mx = Math.max.apply(null, ps);
    $r.text(mn === mx ? rp(mn) : rp(mn) + ' – ' + rp(mx));
  }

  $(document).on('input change', '.merc-var-yuan, .merc-var-berat', function () {
    updateAuto($(this).closest('[data-variation-row]'));
    updateRange();
  });

  function updateBadge() {
    $('[data-merc-var-count]').text($('[data-variation-row]').length);
  }

  /* BULK EDIT */
  $(document).on('click', '[data-merc-bulk-apply]', function (e) {
    e.preventDefault();
    const y = $('[data-merc-bulk-yuan]').val();
    const b = $('[data-merc-bulk-berat]').val();
    const s = $('[data-merc-bulk-sku]').val();
    const t = $('[data-merc-bulk-stok]').val();
    let n = 0;
    $('[data-variation-row]').each(function () {
      const $r = $(this);
      if (y !== '') $r.find('.merc-var-yuan').val(y);
      if (b !== '') $r.find('.merc-var-berat').val(b);
      if (t !== '') $r.find('.merc-var-stok').val(t);
      if (s !== '') {
        const cur = $r.find('.merc-var-sku').val() || '';
        const suf = cur.replace(/^MRC-\d+-/i, '').replace(/[^a-z0-9]/gi, '');
        $r.find('.merc-var-sku').val(s + suf);
      }
      updateAuto($r);
      n++;
    });
    updateRange();
    alert('Bulk edit: ' + n + ' varian.');
  });

  $(document).on('click', '[data-merc-bulk-reset]', function (e) {
    e.preventDefault();
    $('[data-merc-bulk-yuan], [data-merc-bulk-berat], [data-merc-bulk-sku], [data-merc-bulk-stok]').val('');
  });

  updateBadge();
  updateRange();
});