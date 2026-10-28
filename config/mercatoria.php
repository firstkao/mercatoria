<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ongkir & Harga (PRD 20 / 47)
    |--------------------------------------------------------------------------
    */
    'shipping' => [
        'cn_btm_per_kg' => env('SHIPPING_CN_BTM_PER_KG', 25000),
        'btm_jkt_per_kg' => env('SHIPPING_BTM_JKT_PER_KG', 60000),
        'default_weight_gram' => env('SHIPPING_DEFAULT_WEIGHT', 0),
        'local_cn_mode' => 'per_variant_customer_pays_directly',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kontrol Email (PRD 23 — pause_customer_emails)
    |--------------------------------------------------------------------------
    */
    'mail_control' => [
        'pause_customer_emails' => env('MAIL_PAUSE_CUSTOMER_EMAILS', false),
        'always_allow_templates' => [
            'admin_otp',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proteksi Gambar (PRD 8.10)
    |--------------------------------------------------------------------------
    */
    'image_protection' => [
        'enabled' => env('IMAGE_PROTECTION_ENABLED', true),
        'watermark_enabled' => env('IMAGE_WATERMARK_ENABLED', true),
        'watermark_text' => env('IMAGE_WATERMARK_TEXT', 'MERCATORIA · mercatoria.id'),
        'watermark_opacity' => env('IMAGE_WATERMARK_OPACITY', 25),
        'watermark_mode' => 'diagonal_tiled',
        'private_originals_disk' => 'local',
    ],

];
