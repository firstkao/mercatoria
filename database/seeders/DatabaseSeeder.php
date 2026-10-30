<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingsSeeder::class,
            StoreSeeder::class,
            MerchCategorySeeder::class,
            PaymentMethodSeeder::class,
            EmailTemplateSeeder::class,
            IndonesiaRegionsSeeder::class,
        ]);
    }
}
