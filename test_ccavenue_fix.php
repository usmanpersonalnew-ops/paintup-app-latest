<?php

require 'vendor/autoload.php';

use App\Services\CCavenueService;

echo "Testing CCavenue encryption fix...\n";

$ccavenue = new CCavenueService();
$testData = 'merchant_id=123&order_id=TEST123&amount=1000';

try {
    $encrypted = $ccavenue->encrypt($testData);
    echo "Encrypted: " . $encrypted . "\n";

    $decrypted = $ccavenue->decrypt($encrypted);
    echo "Decrypted: " . $decrypted . "\n";

    if ($decrypted === $testData) {
        echo "✓ SUCCESS: Encryption/Decryption works correctly!\n";
    } else {
        echo "✗ FAILED: Decrypted data doesn't match original\n";
    }
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
