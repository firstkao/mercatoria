<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mercatoria.id'],
            [
                'name' => 'Admin Mercatoria',
                'password' => Hash::make('ChangeMe2026!'),
                'no_hp' => '6281219683709',
                'role' => 'admin',
                'email_verified_at' => now(),
                'referral_code' => 'ADMIN001',
            ]
        );
    }
}
