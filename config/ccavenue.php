<?php

return [
    'merchant_id' => env('CCAVENUE_MERCHANT_ID', '4423329'),
    'access_code' => env('CCAVENUE_ACCESS_CODE', 'AVQX88NB08AH83XQHA'),
    'working_key' => env('CCAVENUE_WORKING_KEY', 'B10F609D3BADEE85655DF0C132988837'),
    'redirect_url' => env('CCAVENUE_REDIRECT_URL', 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction'),
    'env' => env('CCAVENUE_ENV', 'test'), // 'test' or 'live'
];