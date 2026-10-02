<?php

namespace App\Http\Controllers;

use App\Models\PreorderPage;
use Illuminate\View\View;

class PreorderPageController extends Controller
{
    public function show(): View
    {
        // Menu "Pre-order Baru" selalu tampil di header (global). Kalau belum ada
        // halaman PO aktif, jangan 404 — tampilkan empty-state yang rapi.
        $page = PreorderPage::current();

        return view('preorder.show', [
            'page' => $page,
            'banners' => $page?->activeBanners()->get() ?? collect(),
            'title' => $page?->title ?? 'Pre-order Baru',
            'metaDescription' => \Illuminate\Support\Str::limit(strip_tags((string) ($page?->content ?? '')), 155) ?: 'Informasi pre-order terbaru MERCATORIA.',
        ]);
    }
}