{{-- Style bersama halaman auth (login, register, lupa/reset password, verifikasi email).
     Simpan di: resources/views/frontend/partials/_auth-style.blade.php
     CSS biasa (tidak bergantung build Tailwind), selaras dengan beranda & halaman produk. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    /* Background & font = beranda (hanya aktif di halaman yang meng-include file ini) */
    body { background: #f7f7f7; font-family: 'Roboto', system-ui, sans-serif; }

    .au, .au *, .au *::before, .au *::after { box-sizing: border-box; }
    .au { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
    .au a { text-decoration: none; }

    .au-card { background: #fff; padding: 36px 40px 40px; }
    .au-eyebrow { margin: 0; font-size: 11px; line-height: 16px; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; color: #888; }
    .au-title { margin: 8px 0 0; font-size: 28px; line-height: 34px; font-weight: 700; color: #000; }
    .au-lead { margin: 10px 0 0; font-size: 14px; line-height: 21px; color: #555; }
    .au-lead strong { color: #000; font-weight: 700; }
    .au-hr { height: 0; margin: 24px 0 0; border: 0; border-top: 1px solid #e1e1e1; }

    .au-form { display: flex; flex-direction: column; gap: 20px; margin-top: 24px; }
    .au-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }

    .au-label { display: block; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
    .au-label i { font-style: normal; color: #e53935; }
    .au-label small { font-size: 11px; font-weight: 400; letter-spacing: 0; text-transform: none; color: #888; }

    .au-input { display: block; width: 100%; height: 42px; margin-top: 8px; padding: 0 14px; border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
                font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #000; outline: 0; transition: border-color .15s; }
    textarea.au-input { height: auto; padding: 10px 14px; resize: vertical; }
    .au-input::placeholder { color: #aaa; }
    .au-input:focus { border-color: #000; }
    .au-input:disabled { opacity: .5; cursor: not-allowed; background: #fafafa; }
    .au-input[readonly] { background: #f7f7f7; color: #777; }

    .au-err { margin: 6px 0 0; font-size: 13px; line-height: 18px; color: #e53935; }

    .au-pw { position: relative; }
    .au-pw .au-input { padding-right: 44px; }
    .au-eye { position: absolute; top: 8px; right: 0; width: 44px; height: 42px; display: flex; align-items: center; justify-content: center;
              border: 0; background: none; color: #999; cursor: pointer; }
    .au-eye:hover { color: #000; }
    .au-eye .on { display: none; }
    .au-eye[data-shown="1"] .on { display: block; }
    .au-eye[data-shown="1"] .off { display: none; }

    .au-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .au-check { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; line-height: 20px; color: #333; }
    .au-check input { flex: none; width: 16px; height: 16px; margin: 2px 0 0; accent-color: #000; }

    .au-link { color: #02a1ea; }
    .au-link:hover { text-decoration: underline; }
    .au-center { margin: 0; text-align: center; font-size: 14px; line-height: 21px; color: #555; }

    .au-btn { display: block; width: 100%; height: 46px; padding: 0; border: 1px solid #000; border-radius: 0; background: #000; color: #fff; text-align: center;
              font: 700 13px/44px 'Roboto', system-ui, sans-serif; letter-spacing: .1em; text-transform: uppercase; cursor: pointer; transition: background .15s, border-color .15s; }
    .au-btn:hover { background: #222; border-color: #222; }
    .au-btn.is-ghost { background: #fff; color: #000; border-color: #e1e1e1; }
    .au-btn.is-ghost:hover { border-color: #000; background: #fff; }
    .au-btn.is-sm { display: inline-block; width: auto; height: 34px; padding: 0 16px; font-size: 12px; line-height: 32px; letter-spacing: .04em; text-transform: none; }

    .au-note { display: flex; align-items: flex-start; gap: 10px; margin-top: 20px; padding: 14px 16px; background: #f7f7f7; font-size: 13px; line-height: 20px; color: #555; }
    .au-note svg, .au-note .au-ico { flex: none; margin-top: 1px; color: #888; }
    .au-note b { color: #000; font-weight: 700; }
    .au-note-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
    .au-terms { display: flex; flex-direction: column; gap: 12px; padding: 16px; background: #f7f7f7; }

    @media (max-width: 560px) {
        .au-card { padding: 28px 20px 32px; }
        .au-title { font-size: 24px; line-height: 30px; }
        .au-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
    // Toggle tampilkan/sembunyikan kata sandi: <button class="au-eye" data-pw-toggle="id-input">
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-pw-toggle]');
        if (!btn) return;
        var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.dataset.shown = show ? '1' : '0';
        btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
</script>
