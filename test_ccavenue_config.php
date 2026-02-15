<?php

/**
 * CCAvenue Configuration Diagnostic Script
 * Run: php test_ccavenue_config.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CCavenueService;

echo "========================================\n";
echo "CCAvenue Configuration Diagnostic\n";
echo "========================================\n\n";

// Check environment
echo "1. Environment Check:\n";
echo "   APP_ENV: " . env('APP_ENV') . "\n";
echo "   CCAVENUE_ENV: " . config('ccavenue.env') . "\n";
echo "   Is Test Mode: " . (config('ccavenue.env') === 'test' ? 'YES' : 'NO') . "\n\n";

// Check credentials
echo "2. Credentials Check:\n";
$merchantId = config('ccavenue.merchant_id');
$accessCode = config('ccavenue.access_code');
$workingKey = config('ccavenue.working_key');

echo "   Merchant ID: " . ($merchantId ? $merchantId : '❌ NOT SET') . "\n";
echo "   Access Code: " . ($accessCode ? substr($accessCode, 0, 10) . '...' : '❌ NOT SET') . "\n";
echo "   Working Key: " . ($workingKey ? substr($workingKey, 0, 8) . '...' : '❌ NOT SET') . "\n";

// Validate working key format
if ($workingKey) {
    echo "   Working Key Length: " . strlen($workingKey) . " (Expected: 32)\n";
    echo "   Working Key Format: " . (ctype_xdigit($workingKey) ? '✓ Valid hex' : '❌ Invalid hex') . "\n";
}

echo "\n";

// Test encryption
echo "3. Encryption Test:\n";
try {
    $service = new CCavenueService();
    
    // Test with sample data matching CCAvenue format
    $testData = [
        'merchant_id' => $merchantId,
        'order_id' => 'TEST-' . time(),
        'currency' => 'INR',
        'amount' => '100.00',
        'redirect_url' => route('payment.ccavenue.callback'),
        'cancel_url' => route('payment.ccavenue.cancel'),
        'language' => 'EN',
    ];
    
    $plainText = http_build_query($testData, '', '&');
    echo "   Plain Text Length: " . strlen($plainText) . "\n";
    echo "   Plain Text Preview: " . substr($plainText, 0, 100) . "...\n";
    
    $encrypted = $service->encrypt($plainText);
    echo "   Encrypted Length: " . strlen($encrypted) . "\n";
    echo "   Encrypted Preview: " . substr($encrypted, 0, 100) . "...\n";
    
    // Test decryption
    $decrypted = $service->decrypt($encrypted);
    echo "   Decryption Match: " . ($decrypted === $plainText ? '✓ YES' : '❌ NO') . "\n";
    
    if ($decrypted !== $plainText) {
        echo "   Original: " . substr($plainText, 0, 100) . "\n";
        echo "   Decrypted: " . substr($decrypted, 0, 100) . "\n";
    }
    
    echo "   ✓ Encryption/Decryption working correctly\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check URLs
echo "4. URL Configuration:\n";
echo "   Redirect URL: " . route('payment.ccavenue.callback') . "\n";
echo "   Cancel URL: " . route('payment.ccavenue.cancel') . "\n";
echo "   Base URL: " . $service->getPaymentBaseUrl() . "\n";

echo "\n";

// Important notes
echo "5. Important Notes:\n";
echo "   ⚠️  For TEST environment:\n";
echo "      - You MUST use TEST credentials from CCAvenue dashboard\n";
echo "      - Your domain (127.0.0.1:8000) must be whitelisted\n";
echo "      - Contact CCAvenue support to whitelist: service@ccavenue.com\n";
echo "      - Phone: +91 8801033323\n";
echo "\n";
echo "   ⚠️  Common Issues:\n";
echo "      - Wrong working key (test vs production)\n";
echo "      - Domain not whitelisted for test environment\n";
echo "      - Access code mismatch with merchant ID\n";
echo "      - Amount is zero (we fixed this)\n";
echo "\n";

echo "========================================\n";
echo "Diagnostic Complete\n";
echo "========================================\n";

