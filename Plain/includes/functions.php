<?php
/** Helper umum yang dipakai di banyak halaman. */

function format_rupiah(int $angka): string
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function make_slug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}
