<?php

namespace App\Http\Controllers;

use App\Models\PreorderPage;
use Illuminate\View\View;

class PreorderPageController extends Controller
{
    public function show(): View
    {
        $page = PreorderPage::current();

        abort_unless($page, 404, 'Belum ada halaman Pre-Order yang aktif.');

        return view('preorder.show', [
            'page' => $page,
            'banners' => $page->activeBanners()->get(),
            'title' => $page->title,
            'metaDescription' => \Illuminate\Support\Str::limit(strip_tags($page->content ?? ''), 155),
        ]);
    }
}