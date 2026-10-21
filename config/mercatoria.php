<?php

return [
    'store' => [
        'name' => 'Mercatoria',
        'domain' => env('APP_URL', 'trial.mercatoria.id'),
        'admin_url' => '/office',
        'cs_email' => 'cs@mercatoria.id',
        'security_email' => 'security@mercatoria.id',
        'info_email' => 'info@mercatoria.id',
        'wa_admin' => env('STORE_WA_ADMIN', '6281234567890'),
        'social' => [
            'instagram' => env('STORE_SOCIAL_INSTAGRAM', 'https://www.instagram.com/mercatoria.id'),
            'facebook' => env('STORE_SOCIAL_FACEBOOK', 'https://www.facebook.com/mercatoria.id'),
            'x' => env('STORE_SOCIAL_X', 'https://x.com/mercatoria_id'),
            'threads' => env('STORE_SOCIAL_THREADS', 'https://www.threads.net/@mercatoria.id'),
        ],
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
        'local_cn_mode' => 'per_variant_customer_pays_directly',
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
        'mvp_to_ex_days' => 0,
        'ex_to_ons_days' => 30,
        'ons_to_blocked_days' => 60,
        'h7_warning_days_before' => 7,
        'max_unblock_count' => 2,
        'unblock_grace_days' => 7,
        'spammer_ip_ttl_days' => 30,
    ],

    'mail_control' => [
        'pause_customer_emails' => env('MAIL_PAUSE_CUSTOMER_EMAILS', false),
        'always_allow_templates' => ['admin_otp'],
    ],

    'image_protection' => [
        'enabled' => env('IMAGE_PROTECTION_ENABLED', true),
        'watermark_enabled' => env('IMAGE_WATERMARK_ENABLED', true),
        'watermark_text' => env('IMAGE_WATERMARK_TEXT', 'MERCATORIA · mercatoria.id'),
        'watermark_opacity' => env('IMAGE_WATERMARK_OPACITY', 25),
        'watermark_mode' => 'diagonal_tiled',
        'private_originals_disk' => 'local',
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
