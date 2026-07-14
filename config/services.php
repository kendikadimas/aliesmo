<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'whatsapp' => [
        'number' => env('WHATSAPP_NUMBER', '6281234567890'),
    ],

    'rajaongkir' => [
        'api_key'     => env('RAJAONGKIR_API_KEY'),
        'base_url'    => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'origin_city' => env('RAJAONGKIR_ORIGIN_CITY', 570),        // city ID Pemalang (static fallback)
        'origin_id'   => env('RAJAONGKIR_ORIGIN_ID', 67550),        // subdistrict ID Sukorejo, Ulujami, Pemalang (akurasi tinggi)
        'daily_limit' => env('RAJAONGKIR_DAILY_LIMIT', 95),
    ],

    'biteship' => [
        'api_key'        => env('BITESHIP_API_KEY'),
        'base_url'       => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
        'origin_postal'  => env('BITESHIP_ORIGIN_POSTAL', '52371'),                          // kode pos Ulujami
        'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'IDNP10IDNC348IDND4071IDZ52371'), // area_id Ulujami, Pemalang
        'origin_phone'   => env('BITESHIP_ORIGIN_PHONE', '08138883345'),
        'origin_address' => env('BITESHIP_ORIGIN_ADDRESS', 'Ulujami, Pemalang, Jawa Tengah'),
        'webhook_secret' => env('BITESHIP_WEBHOOK_SECRET'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

];
