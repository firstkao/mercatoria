<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Official Store',
                'min_belanja_rmb' => 100,
                'ongkir_lokal_cn_percent' => 7,
            ],
            [
                'name' => 'Fanmade Studio',
                'min_belanja_rmb' => 0,
                'ongkir_lokal_cn_percent' => 0,
            ],
            [
                'name' => 'Toko Baju Collab',
                'min_belanja_rmb' => 150,
                'ongkir_lokal_cn_percent' => 10,
            ],
        ];

        foreach ($stores as $store) {
            Store::updateOrCreate(
                ['name' => $store['name']],
                $store
            );
        }
    }
}