<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\LegalContent;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(string $page): View
    {
        $dbPage = Page::query()
            ->where('slug', $page)
            ->where('is_published', true)
            ->first();

        if ($dbPage) {
            return view('pages.legal', [
                'title' => $dbPage->title,
                'contentHtml' => $dbPage->html(),
                'metaDescription' => \Illuminate\Support\Str::limit(strip_tags($dbPage->content ?? ''), 155),
            ]);
        }

        abort_unless(array_key_exists($page, LegalContent::PAGES), 404);

        return view('pages.legal', [
            'title' => LegalContent::PAGES[$page],
            'contentHtml' => LegalContent::html($page),
        ]);
    }
}