<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mercatoria.id'],
            [
                'name' => 'Admin Mercatoria',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'tgl_lahir' => '1990-01-01',
                'no_hp' => '6281234567890',
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Selatan',
                'kecamatan' => 'Kebayoran Baru',
                'alamat' => 'Kantor Mercatoria',
                'kode_pos' => '12120',
                'email_verified_at' => now(),
            ]
        );
    }
}
