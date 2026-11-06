@extends('layouts.app')

@section('title', 'Dashboard — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        .pf-wrap, .pf-wrap *, .pf-wrap *::before, .pf-wrap *::after { box-sizing: border-box; }
        .pf-wrap {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .pf-wrap a { text-decoration: none; }

        .pf-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 36px 40px 28px;
            background: #fff;
        }
        .pf-title { margin: 0; font-size: 32px; line-height: 40px; font-weight: 500; color: #26222f; }
        .pf-lead { margin: 6px 0 0; font-size: 15px; color: #6f6a72; }
        .pf-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 14px; }
        .pf-meta-item { font-size: 12px; color: #888; }

        .pf-bar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            margin-top: 16px; padding: 12px 16px;
            background: #fafafa; border: 1px solid #eee;
        }
        .pf-bar-label { font-size: 13px; color: #555; }
        .pf-bar-label strong { color: #26222f; font-weight: 700; }
        .pf-wa {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; background: #43A047; color: #fff;
            font: 700 12px/18px 'Roboto', system-ui, sans-serif;
        }
        .pf-wa:hover { background: #388E3C; }

        .pf-progress { margin-top: 16px; max-width: 420px; }
        .pf-progress-top { display: flex; justify-content: space-between; font-size: 12px; color: #888; }
        .pf-progress-track { margin-top: 6px; height: 8px; background: #eee; overflow: hidden; }
        .pf-progress-fill { height: 100%; background: #FFC107; }
        .pf-progress-note { margin: 6px 0 0; font-size: 12px; color: #888; }

        .pf-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .pf-stat {
            padding: 20px;
            background: #fff;
        }
        .pf-label { margin: 0; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #888; }
        .pf-stat-value { margin: 8px 0 0; font-size: 22px; font-weight: 700; color: #26222f; }

        .pf-tabs {
            display: flex; flex-wrap: wrap; gap: 0;
            margin-top: 28px;
            border-bottom: 1px solid #eee;
            background: #fff;
            padding: 0 20px;
        }
        .pf-tab {
            display: inline-block; padding: 14px 18px;
            font-size: 14px; color: #888;
            border-bottom: 2px solid transparent;
        }
        .pf-tab:hover { color: #26222f; }
        .pf-tab.is-on { color: #26222f; font-weight: 700; border-bottom-color: #26222f; }

        .pf-sect { margin-top: 28px; }
        .pf-sect-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .pf-sect-h { margin: 0; font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #26222f; }
        .pf-card { background: #fff; padding: 20px; }
        .pf-empty { text-align: center; color: #888; font-size: 14px; }
        .pf-list { display: flex; flex-direction: column; gap: 10px; }
        .pf-row {
            display: flex; flex-wrap: wrap; justify-content: space-between;
