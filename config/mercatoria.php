<?php

return [
    'store' => [
        'name' => 'Mercatoria',
        'domain' => env('APP_URL', 'trial.mercatoria.id'),
        'admin_url' => '/office',
        'cs_email' => 'cs@mercatoria.id',
        'security_email' => 'security@mercatoria.id',
        'info_email' => 'info@mercatoria.id',
    ],

    'order' => [
        'number_prefix' => 'MER',
        'pickup_timer_days' => 7,
        'dp_percent' => 50,
        'rounding' => 5000,
    ],

    'shipping' => [
        'cn_btm_per_kg' => env('SHIPPING_CN_BTM_PER_KG', 25000),
        'btm_jkt_per_kg' => env('SHIPPING_BTM_JKT_PER_KG', 60000),
        'default_weight_gram' => env('SHIPPING_DEFAULT_WEIGHT', 0),
    ],

    'snapshot' => [
        'storage_path' => 'order-snapshots',
        'retention' => 'permanent',
        'image_max_size' => 200 * 1024,
        'delete_confirm_text' => 'HAPUS',
    ],

    'bell' => [
        'poll_interval' => 60,
        'max_dropdown' => 10,
        'retention_days' => 90,
        'toast_duration' => 5,
    ],

    'state' => [
        'crush_auto_delete_days' => 30,
        'view_limit' => 10,
        'mvp_to_ex_days' => 30,
        'ex_to_ons_days' => 60,
    ],

    'coin' => [
        'referrer_reward' => 3000,
        'referee_reward' => 1000,
        'order_percent' => 1,
        'share_reward' => 5,
        'max_discount_percent' => 5,
        'expiry_days' => 365,
        'birthday_bonus' => 5000,
    ],

    'share' => [
        'restrict_to_indonesia' => true,
    ],
];
