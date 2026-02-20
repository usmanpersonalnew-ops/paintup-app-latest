<?php
/**
 * Check database columns for projects table
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Database Connection Check ===\n";
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n\n";

    echo "=== Checking projects table columns ===\n";

    // Check for booking_paid_at
    $booking_paid_at = DB::select("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'projects'
        AND COLUMN_NAME = 'booking_paid_at'
    ");

    if (count($booking_paid_at) > 0) {
        echo "✅ booking_paid_at EXISTS\n";
        foreach ($booking_paid_at as $col) {
            echo "   Type: {$col->DATA_TYPE}, Nullable: {$col->IS_NULLABLE}\n";
        }
    } else {
        echo "❌ booking_paid_at DOES NOT EXIST\n";
    }

    // Check for booking_payment_at (wrong name)
    $booking_payment_at = DB::select("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'projects'
        AND COLUMN_NAME = 'booking_payment_at'
    ");

    if (count($booking_payment_at) > 0) {
        echo "⚠️  booking_payment_at EXISTS (wrong column name!)\n";
        foreach ($booking_payment_at as $col) {
            echo "   Type: {$col->DATA_TYPE}, Nullable: {$col->IS_NULLABLE}\n";
        }
    } else {
        echo "✅ booking_payment_at does not exist (correct - this is wrong name)\n";
    }

    echo "\n=== All payment-related columns in projects table ===\n";
    $allPaymentColumns = DB::select("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'projects'
        AND (COLUMN_NAME LIKE '%paid%' OR COLUMN_NAME LIKE '%payment%')
        ORDER BY COLUMN_NAME
    ");

    foreach ($allPaymentColumns as $col) {
        echo "  - {$col->COLUMN_NAME} ({$col->DATA_TYPE})\n";
    }

    echo "\n=== SQL to add booking_paid_at if missing ===\n";
    if (count($booking_paid_at) == 0) {
        echo "Run this SQL:\n";
        echo "ALTER TABLE `projects` ADD COLUMN `booking_paid_at` TIMESTAMP NULL DEFAULT NULL AFTER `final_reference`;\n";
    } else {
        echo "Column already exists, no action needed.\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

