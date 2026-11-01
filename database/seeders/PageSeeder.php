<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'tentang-kami' => 'Tentang Kami',
            'faq' => 'FAQ',
            'cara-belanja' => 'Cara Belanja',
            'tnc' => 'Syarat & Ketentuan',
            'kebijakan-privasi' => 'Kebijakan Privasi',
        ];

        foreach ($pages as $slug => $title) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => '<p>'.$title.' Mercatoria.</p>', 'meta_description' => $title.' Mercatoria', 'status' => 'publish'],
            );
        }
    }
}
