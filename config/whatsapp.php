<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | The active WhatsApp driver. Override via the `whatsapp_driver` setting
    | (Admin → Settings → WhatsApp tab), or via env. Supported: "meta", "log".
    |
    */
    'default' => env('WHATSAPP_DRIVER', 'log'),

    'drivers' => [
        'meta' => [
            'phone_number_id' => env('META_PHONE_NUMBER_ID'),
            'access_token'    => env('META_ACCESS_TOKEN'),
            'api_version'     => env('META_API_VERSION', 'v19.0'),
            'endpoint'        => 'https://graph.facebook.com',
        ],
    ],
];
