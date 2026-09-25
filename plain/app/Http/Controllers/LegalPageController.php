<?php

namespace App\Http\Controllers;

use App\Support\LegalContent;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(string $page): View
    {
        abort_unless(array_key_exists($page, LegalContent::PAGES), 404);

        return view('pages.legal', [
            'title' => LegalContent::PAGES[$page],
            'contentHtml' => LegalContent::html($page),
        ]);
    }
}