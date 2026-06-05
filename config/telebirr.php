<?php
// config/telebirr.php

return [
    'env'              => env('TELEBIRR_ENV', 'sandbox'),

    'fabric_app_id'    => env('TELEBIRR_FABRIC_APP_ID'),
    'merchant_app_id'  => env('TELEBIRR_MERCHANT_APP_ID'),
    'merchant_code'    => env('TELEBIRR_MERCHANT_CODE'),
    'app_secret'       => env('TELEBIRR_APP_SECRET'),

    'private_key'      => env('TELEBIRR_PRIVATE_KEY'),
    'public_key'       => env('TELEBIRR_PUBLIC_KEY', null),

    'notify_url'       => env('TELEBIRR_NOTIFY_URL'),
    'return_url'       => env('TELEBIRR_RETURN_URL'),

    'signature_padding' => env('TELEBIRR_SIGNATURE_PADDING', 'pss'),
    'ssl_verify'        => env('TELEBIRR_SSL_VERIFY', true),
];