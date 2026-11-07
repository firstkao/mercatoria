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
                'name' => 'miHoYo旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => '原神旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => 'bilibili旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => 'goodsmile官方旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => '光与夜之恋旗舰店',
                'min_belanja_rmb' => 200,
                'ongkir_rmb' => 10,
            ],
            [
                'name' => '叠纸心意旗舰店',
                'min_belanja_rmb' => 100,
                'ongkir_rmb' => 7,
            ],
            [
                'name' => '重返未来1999旗舰店',
                'min_belanja_rmb' => 200,
                'ongkir_rmb' => 8,
            ],
            [
                'name' => '无期迷途采购办',
                'min_belanja_rmb' => 90,
                'ongkir_rmb' =>  5,
            ],
            [
                'name' => 'BilibiliGoods旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => '世界之外旗舰店',
                'min_belanja_rmb' => 100,
                'ongkir_rmb' => 10,
            ],
            [
                'name' => '绝区零三Z官方旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => '萌果酱谷子咖啡',
                'min_belanja_rmb' => 200,
                'ongkir_rmb' => 10,
            ],
            [
                'name' => 'apexinnovation旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
            [
                'name' => '时空中的绘旅人千翊专卖店',
                'min_belanja_rmb' => 100,
                'ongkir_rmb' => 7,
            ],
            [
                'name' => 'KAKACODE',
                'min_belanja_rmb' => 100,
                'ongkir_rmb' => 7,
            ],
            [
                'name' => '明日方舟终末地旗舰店',
                'min_belanja_rmb' => 0,
                'ongkir_rmb' => 0,
            ],
             [
                'name' => '恋与深空旗舰店',
                'min_belanja_rmb' => 100,
                'ongkir_rmb' => 7,
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
