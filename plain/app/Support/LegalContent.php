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
        // Hanya halaman terdaftar; cegah path traversal / file tak dikenal.
        if (! array_key_exists($page, self::PAGES)) {
            return '';
        }

        $path = resource_path("content/{$page}.md");

        // File konten bisa hilang (mis. belum dibuat) — jangan biarkan
        // file_get_contents(false) lalu Str::markdown(false) melempar TypeError.
        if (! is_file($path)) {
            return '';
        }

        $markdown = file_get_contents($path);
        if ($markdown === false) {
            return '';
        }

        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}