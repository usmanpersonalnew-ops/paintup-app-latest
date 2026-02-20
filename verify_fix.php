<?php
/**
 * Verification script to check if booking_paid_at fix is in place
 */

$file = __DIR__ . '/app/Http/Controllers/Admin/CouponController.php';
$content = file_get_contents($file);

if (strpos($content, 'booking_payment_at') !== false) {
    echo "❌ ERROR: Still found 'booking_payment_at' in CouponController.php\n";
    echo "The fix has NOT been applied correctly.\n";
    exit(1);
}

if (strpos($content, 'booking_paid_at') !== false) {
    echo "✅ SUCCESS: Found 'booking_paid_at' in CouponController.php\n";
    echo "The fix is correctly applied.\n";
    
    // Show the line
    $lines = explode("\n", $content);
    foreach ($lines as $num => $line) {
        if (strpos($line, 'booking_paid_at') !== false) {
            echo "\nLine " . ($num + 1) . ": " . trim($line) . "\n";
        }
    }
    exit(0);
}

echo "⚠️  WARNING: Neither column name found in the file.\n";
exit(1);

