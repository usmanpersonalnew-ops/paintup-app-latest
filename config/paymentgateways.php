<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'phonepe'),

    'phonepe' => [
        'enabled' => env('PHONEPE_ENABLED', false) === true || env('PHONEPE_ENABLED', false) === 'true',
        // V2 credentials (required – get from PhonePe Business Dashboard > Developer Settings)
        'client_id' => env('PHONEPE_CLIENT_ID'),
        'client_secret' => env('PHONEPE_CLIENT_SECRET'),
        'client_version' => env('PHONEPE_CLIENT_VERSION', 1),
        'environment' => env('PHONEPE_ENVIRONMENT', 'sandbox'),
        // Legacy V1 (deprecated, kept for reference only)
        'merchant_id' => env('PHONEPE_MERCHANT_ID'),
        'salt_key' => env('PHONEPE_SALT_KEY'),
        'salt_index' => env('PHONEPE_SALT_INDEX', 1),
    ],

    'ccavenue' => [
        'enabled' => env('CCAVENUE_ENABLED', false) === true || env('CCAVENUE_ENABLED', false) === 'true',
        'merchant_id' => env('CCAVENUE_MERCHANT_ID'),
        'working_key' => env('CCAVENUE_WORKING_KEY'),
        'access_code' => env('CCAVENUE_ACCESS_CODE'),
        'environment' => env('CCAVENUE_ENVIRONMENT', 'production'),
    ],
];
