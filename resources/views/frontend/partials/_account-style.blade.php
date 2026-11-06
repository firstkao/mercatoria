{{-- Style bersama halaman akun (dashboard, notifikasi, koin, voucher, undang teman, edit profil, lengkapi profil).
     Simpan di: resources/views/frontend/partials/_account-style.blade.php
     Turunan dari keranjang/checkout: box putih 1200px, judul 28px, section dengan header #fbfbfb. --}}
@include('frontend.partials._auth-style')

<style>
    body { background: #f7f7f7; }

    .ac-box, .ac-box *, .ac-box *::before, .ac-box *::after { box-sizing: border-box; }
    .ac-box {
        position: relative; left: 50%; transform: translateX(-50%);
        width: min(1200px, calc(100vw - 48px)); margin: 28px 0 28px;
        padding: 47px 58px 80px; background: #fff;
        font-family: 'Roboto', system-ui, sans-serif; font-size: 16px; line-height: 24px; color: #26222f; -webkit-font-smoothing: antialiased;
    }
    .ac-box.is-narrow { width: min(640px, calc(100vw - 48px)); }
    .ac-box a { text-decoration: none; color: inherit; }

    /* Kepala halaman */
    .ac-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .ac-title { margin: 0 0 0 -8px; font-size: 28px; line-height: 34px; font-weight: 500; color: #26222f; }
    .ac-lead { margin: 6px 0 0; font-size: 15px; line-height: 22px; color: #7a7a7a; }
    .ac-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 12px; font-size: 13px; line-height: 20px; color: #888; }

    /* Navigasi tab akun */
    .ac-tabs { display: flex; flex-wrap: wrap; gap: 0 30px; margin-top: 28px; border-bottom: 1px solid #ddd; }
    .ac-box .ac-tab { padding: 12px 0; margin-bottom: -1px; border-bottom: 2px solid transparent; font-size: 15px; line-height: 24px; color: #7a7a7a; transition: color .15s; }
    .ac-box .ac-tab:hover { color: #26222f; }
    .ac-box .ac-tab.is-on { color: #26222f; font-weight: 500; border-bottom-color: #26222f; }

    /* Notice (sama dengan keranjang/checkout) */
    .ac-note { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 24px; padding: 14px 23px; border-top: 3px solid #26222f; background: #f7f6f7; font-size: 15px; line-height: 22px; }
    .ac-note b { font-weight: 700; }

    /* Progress tier */
    .ac-progress { margin-top: 20px; max-width: 420px; }
    .ac-progress-top { display: flex; justify-content: space-between; gap: 12px; font-size: 13px; line-height: 20px; color: #7a7a7a; }
    .ac-progress-track { margin-top: 6px; height: 8px; background: #eee; overflow: hidden; }
    .ac-progress-fill { height: 100%; background: #FFC107; }
    .ac-progress-note { margin: 6px 0 0; font-size: 13px; line-height: 18px; color: #7a7a7a; }

    /* Kartu statistik */
    .ac-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; margin-top: 28px; }
    .ac-stats.is-1 { grid-template-columns: minmax(0, 1fr); }
    .ac-stats.is-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ac-stats.is-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ac-stat { padding: 18px 20px 20px; border: 1px solid #ddd; }
    .ac-stat-l { margin: 0; font-size: 13px; line-height: 18px; color: #7a7a7a; }
    .ac-stat-v { margin: 6px 0 0; font-size: 24px; line-height: 30px; font-weight: 500; color: #26222f; }
    .ac-stat-v.is-xl { font-size: 40px; line-height: 48px; }
    .ac-stat-v.is-sm { font-size: 17px; line-height: 26px; }
    .ac-stat-d { margin: 6px 0 0; font-size: 13px; line-height: 19px; color: #7a7a7a; }

    /* Section (header bar #fbfbfb) */
    .ac-sec { margin-top: 24px; border: 1px solid #ddd; }
    .ac-sec-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; height: 53px; padding: 0 20px; border-bottom: 1px solid #ddd; background: #fbfbfb; }
    .ac-sec-h h2 { margin: 0; font-size: 19px; line-height: 52px; font-weight: 400; color: #26222f; }
    .ac-body { padding: 20px; }
    .ac-body .au-form { margin-top: 0; }
    .ac-empty { padding: 44px 20px; text-align: center; font-size: 15px; color: #888; }
    .ac-box .ac-link { color: #02a1ea; font-size: 14px; }
    .ac-box .ac-link:hover { text-decoration: underline; }

    /* Baris daftar */
    .ac-rows { margin: 0; padding: 0; list-style: none; }
    .ac-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #ddd; }
    .ac-row:last-child { border-bottom: 0; }
    .ac-row-t { margin: 0; font-weight: 500; color: #26222f; }
    .ac-row-s { margin: 2px 0 0; font-size: 14px; line-height: 20px; color: #7a7a7a; }
    .ac-row-end { display: flex; align-items: center; gap: 14px; }

    /* Chip filter */
    .ac-chips { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 16px 20px; border-bottom: 1px solid #ddd; }
    .ac-chip { height: 36px; padding: 0 14px; border: 1px solid #ddd; border-radius: 0; background: #fff; font: 400 14px/34px 'Roboto', system-ui, sans-serif; color: #7a7a7a; cursor: pointer; transition: border-color .15s; }
    .ac-chip:hover { border-color: #999; }
    .ac-chip.is-on { border-color: #26222f; color: #26222f; font-weight: 500; box-shadow: inset 0 0 0 1px #26222f; }

    /* Toolbar & bulk (notifikasi) */
    .ac-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 16px 20px; border-bottom: 1px solid #ddd; }
    .ac-search { position: relative; flex: 1 1 220px; min-width: 200px; }
    .ac-search svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none; }
    .ac-input { display: block; width: 100%; height: 40px; padding: 0 14px; border: 1px solid #ddd; border-radius: 0; background: #fff; font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #26222f; outline: 0; transition: border-color .15s; }
    .ac-input.has-icon { padding-left: 38px; }
    .ac-input:focus { border-color: #26222f; }
    .ac-input::placeholder { color: #999; }
    select.ac-input { width: auto; cursor: pointer; }
    .ac-bulk { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px 20px; border-bottom: 1px solid #ddd; font-size: 14px; }
    .ac-check { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
    .ac-check input, .ac-row input[type="checkbox"] { width: 16px; height: 16px; margin: 0; accent-color: #26222f; }
    .ac-row.is-read { opacity: .6; }
    .ac-row-main { display: flex; align-items: flex-start; gap: 14px; flex: 1 1 0; min-width: 0; }
    .ac-row-main input { margin-top: 4px; flex: none; }
    .ac-row-t.is-unread { font-weight: 700; }

    /* Tabel */
    .ac-table-wrap { overflow-x: auto; }
    .ac-table { width: 100%; min-width: 560px; border-collapse: collapse; }
    .ac-table th { padding: 12px 20px 12px 0; border-bottom: 1px solid #ddd; text-align: left; font-size: 14px; line-height: 20px; font-weight: 500; color: #26222f; }
    .ac-table td { padding: 14px 20px 14px 0; border-bottom: 1px solid #ddd; font-size: 15px; line-height: 22px; color: #555; }
    .ac-table th:first-child, .ac-table td:first-child { padding-left: 20px; }
    .ac-table tbody tr:last-child td { border-bottom: 0; }
    .ac-pos { color: #43A047; font-weight: 500; }
    .ac-neg { color: #e53935; font-weight: 500; }

    /* Badge */
    .ac-badge { display: inline-block; padding: 3px 10px; border: 1px solid #e1e1e1; background: #f7f6f7; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #555; white-space: nowrap; }
    .ac-badge.is-ok { background: #43A047; border-color: #43A047; color: #fff; }
    .ac-badge.is-bad { background: #E53935; border-color: #E53935; color: #fff; }
    .ac-badge.is-wait { background: #FFF3E0; border-color: #FFF3E0; color: #8A6D3B; }

    /* Tombol */
    .ac-box .ac-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 40px; padding: 0 22px; border: 1px solid #26222f; border-radius: 2px; background: #26222f; color: #fff;
                      font: 500 14px/38px 'Roboto', system-ui, sans-serif; letter-spacing: .02em; text-transform: uppercase; cursor: pointer; transition: background .15s, border-color .15s; }
    .ac-box .ac-btn:hover { background: #3a3547; border-color: #3a3547; }
    .ac-box .ac-btn.is-ghost { background: #fff; color: #26222f; border-color: #ddd; }
    .ac-box .ac-btn.is-ghost:hover { border-color: #26222f; background: #fff; }
    .ac-box .ac-btn.is-danger { color: #e53935; border-color: rgba(229,57,53,.4); }
    .ac-box .ac-btn.is-danger:hover { border-color: #e53935; }
    .ac-box .ac-btn.is-sm { height: 34px; padding: 0 16px; font-size: 12px; line-height: 32px; }
    .ac-box .ac-btn.is-wa { background: #43A047; border-color: #43A047; }
    .ac-box .ac-btn.is-wa:hover { background: #388E3C; border-color: #388E3C; }
    .ac-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }

    /* Kode undangan */
    .ac-code { margin: 6px 0 0; font-size: 28px; line-height: 36px; font-weight: 500; letter-spacing: .16em; color: #26222f; }
    .ac-codelink { margin: 8px 0 0; font-size: 14px; line-height: 20px; color: #7a7a7a; word-break: break-all; }

    /* Form (memakai .au-* dari _auth-style, disamakan dengan checkout) */
    .ac-box .au-label { font-size: 14px; line-height: 20px; font-weight: 500; letter-spacing: 0; text-transform: none; color: #26222f; margin-bottom: 8px; }
    .ac-box .au-label small { font-size: 12px; color: #999; }
    .ac-box .au-input { height: 42px; margin-top: 0; font-size: 15px; }
    .ac-box textarea.au-input { height: auto; }
    .is-dim { opacity: .5; }
    .ac-grid3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }

    /* Modal tour */
    .ac-modal { width: calc(100% - 32px); max-width: 560px; margin: auto; padding: 0; border: 0; background: #fff; max-height: 90vh; overflow-y: auto; }
    .ac-modal::backdrop { background: rgba(0,0,0,.5); }
    .ac-modal-body { padding: 32px; }
    .ac-modal-title { margin: 0; font-size: 22px; line-height: 28px; font-weight: 500; color: #26222f; }
    .ac-ol { margin: 18px 0 0; padding: 0; list-style: none; }
    .ac-ol li { display: flex; gap: 10px; margin-bottom: 12px; font-size: 15px; line-height: 22px; color: #555; }
    .ac-ol b { color: #26222f; font-weight: 500; }
    .ac-num { color: #888; font-weight: 700; }
    .ac-modal-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; margin-top: 24px; }

    /* Paginasi */
    .ac-pager { margin-top: 24px; }
    .cgp-list { display: flex; flex-wrap: wrap; gap: 7px; margin: 0; padding: 0; list-style: none; }
    .cgp-b { display: flex; align-items: center; justify-content: center; min-width: 41px; height: 41px; padding: 0 6px; border: 1px solid #ddd; background: #fff; font-size: 16px; line-height: 1; color: #26222f; transition: border-color .15s; }
    a.cgp-b:hover { border-color: #26222f; }
    .cgp-b.is-on { background: #26222f; border-color: #26222f; color: #fff; }

    @media (max-width: 900px) { .ac-stats, .ac-stats.is-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ac-grid3 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .ac-box { padding: 28px 18px 48px; }
        .ac-tabs { gap: 0 20px; }
        .ac-title { font-size: 24px; line-height: 30px; }
    }
    @media (max-width: 560px) {
        .ac-stats, .ac-stats.is-2, .ac-stats.is-3 { grid-template-columns: 1fr; }
        .ac-grid3 { grid-template-columns: 1fr; }
        .ac-modal-body { padding: 24px 18px; }
        .ac-code { font-size: 20px; letter-spacing: .1em; }
    }
</style>
