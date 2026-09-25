<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Renders the owner-maintained Markdown pages in resources/content.
 */
class LegalContent
{
    public const PAGES = [
        'syarat-dan-ketentuan' => 'Syarat & Ketentuan',
        'kebijakan-privasi' => 'Kebijakan Privasi',
        'faq' => 'Tanya Jawab Umum',
        'reseller' => 'Reseller',

    ];

    public static function html(string $page): string
    {
        $markdown = file_get_contents(resource_path("content/{$page}.md"));

        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}