<?php

// Automatically set to 'test' if APP_ENV is 'local'
// If CCAVENUE_ENV is explicitly set, use that; otherwise, use 'test' if APP_ENV is 'local'
$appEnv = env('APP_ENV', 'production');
$ccavenueEnv = env('CCAVENUE_ENV');

// If APP_ENV is 'local', force test mode (unless CCAVENUE_ENV is explicitly set to 'live')
if ($appEnv === 'local' && $ccavenueEnv === null) {
    $ccavenueEnv = 'test';
} elseif ($ccavenueEnv === null) {
    // Default to 'test' for safety if not explicitly set
    $ccavenueEnv = 'test';
}

return [
    'merchant_id' => env('CCAVENUE_MERCHANT_ID', '4423329'),
    'access_code' => env('CCAVENUE_ACCESS_CODE', 'AVQX88NB08AH83XQHA'),
    'working_key' => env('CCAVENUE_WORKING_KEY', 'B10F609D3BADEE85655DF0C132988837'),
    'redirect_url' => env('CCAVENUE_REDIRECT_URL', 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction'),
    'env' => $ccavenueEnv, // 'test' or 'live' - automatically 'test' if APP_ENV=local
];
