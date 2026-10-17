<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Tentang Kami',
                'content' => '<h2>Tentang Mercatoria</h2><p>Mercatoria adalah marketplace merchandise IP Cina untuk penggemar di Indonesia.</p><p>Kami membantu kamu belanja merch resmi &amp; fanmade dari Tiongkok tanpa ribet.</p>',
            ],
            [
                'title' => 'FAQ',
                'content' => '<h2>Frequently Asked Questions</h2><h3>Bagaimana cara belanja?</h3><p>Klik produk, tambah ke cart, checkout, dan bayar.</p><h3>Berapa lama pengiriman?</h3><p>Estimasi 14-30 hari setelah pembayaran terverifikasi.</p>',
            ],
            [
                'title' => 'Cara Belanja',
                'content' => '<h2>Cara Belanja di Mercatoria</h2><ol><li>Daftar akun &amp; verifikasi email</li><li>Pilih produk dari katalog</li><li>Checkout dan pilih metode pembayaran</li><li>Upload bukti transfer</li><li>Tunggu verifikasi admin</li><li>Barang dikirim ke alamatmu</li></ol>',
            ],
            [
                'title' => 'Syarat & Ketentuan',
                'content' => '<h2>Syarat &amp; Ketentuan</h2><p>Dengan menggunakan layanan Mercatoria, kamu menyetujui syarat dan ketentuan berikut:</p><ul><li>Barang yang sudah dibeli tidak dapat dikembalikan</li><li>DP hangus jika tidak dilunasi dalam 7 hari setelah barang sampai WH Indonesia</li><li>Kami tidak bertanggung jawab atas kerusakan selama pengiriman</li></ul>',
            ],
            [
                'title' => 'Kebijakan Privasi',
                'content' => '<h2>Kebijakan Privasi</h2><p>Mercatoria menghormati privasi pengguna kami.</p><h3>Data yang Kami Kumpulkan</h3><ul><li>Nama, email, no HP, alamat</li><li>Data transaksi</li><li>Riwayat login</li></ul><h3>Penggunaan Data</h3><p>Data hanya digunakan untuk kepentingan transaksi dan peningkatan layanan.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => Str::slug($page['title'])],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'status' => 'publish',
                    'meta_title' => $page['title'] . ' — Mercatoria',
                    'meta_description' => substr(strip_tags($page['content']), 0, 160),
                ]
            );
        }
    }
}
