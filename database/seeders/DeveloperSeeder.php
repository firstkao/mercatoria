<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $developers = [
            [
                'name' => 'Aisno Games',
                'is_active' => true,
            ],
            [
                'name' => 'BluePoch',
                'is_active' => true,
            ],
            [
                'name' => 'HyperGryph',
                'is_active' => true,
            ],
            [
                'name' => 'Kuro Games',
                'is_active' => true,
            ],
            [
                'name' => 'Manjuu Game',
                'is_active' => true,
            ],
            [
                'name' => 'miHoYo',
                'is_active' => true,
            ],
            [
                'name' => 'NetEase Games',
                'is_active' => true,
            ],
            [
                'name' => 'Papergames',
                'is_active' => true,
            ],
            [
                'name' => 'Tencent Games',
                'is_active' => true,
            ],
            [
                'name' => 'Yongshi Technology',
                'is_active' => true,
            ],          
        ];

        foreach ($developers as $dev) {
            $slug = Str::slug($dev['name']);

            Developer::updateOrCreate(
                ['slug' => $slug], // Cek berdasarkan slug unik
                [
                    'name' => $dev['name'],
                    'slug' => $slug,
                    'logo_path' => null, // Bisa diisi path dummy jika ada
                    'is_active' => $dev['is_active'],
                ]
            );
        }
    }
}
