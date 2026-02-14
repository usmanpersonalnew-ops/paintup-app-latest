<?php

/**
 * Database Schema Fix Script
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking projects table schema...\n\n";

$columns = DB::select('PRAGMA table_info(projects)');
$columnNames = array_column($columns, 'name');

echo "Current columns:\n";
foreach ($columns as $col) {
    echo "  - {$col->name} ({$col->type})\n";
}

echo "\nChecking for missing columns...\n";

$requiredColumns = [
    'base_total',
    'gst_rate',
    'booking_amount',
    'booking_gst',
    'booking_total',
    'mid_amount',
    'mid_gst',
    'mid_total',
    'final_amount',
    'final_gst',
    'final_total',
    'booking_status',
    'mid_status',
    'final_status',
    'booking_reference',
    'mid_reference',
    'final_reference',
    'booking_paid_at',
    'mid_paid_at',
    'final_paid_at',
    'coupon_id',
    'coupon_code',
    'discount_amount',
];

$missingColumns = array_diff($requiredColumns, $columnNames);

if (empty($missingColumns)) {
    echo "All required columns exist!\n";
} else {
    echo "Missing columns: " . implode(', ', $missingColumns) . "\n";
    
    echo "\nAdding missing columns...\n";
    
    foreach ($missingColumns as $column) {
        if (in_array($column, ['base_total', 'booking_amount', 'booking_gst', 'booking_total', 'mid_amount', 'mid_gst', 'mid_total', 'final_amount', 'final_gst', 'final_total', 'discount_amount'])) {
            DB::statement("ALTER TABLE projects ADD COLUMN {$column} DECIMAL(12, 2) DEFAULT 0");
            echo "  - Added {$column} (DECIMAL)\n";
        } elseif (in_array($column, ['gst_rate'])) {
            DB::statement("ALTER TABLE projects ADD COLUMN {$column} DECIMAL(5, 2) DEFAULT 18");
            echo "  - Added {$column} (DECIMAL)\n";
        } elseif (in_array($column, ['booking_status', 'mid_status', 'final_status', 'booking_reference', 'mid_reference', 'final_reference', 'coupon_code'])) {
            DB::statement("ALTER TABLE projects ADD COLUMN {$column} VARCHAR(255) DEFAULT 'PENDING'");
            echo "  - Added {$column} (VARCHAR)\n";
        } elseif (in_array($column, ['coupon_id'])) {
            DB::statement("ALTER TABLE projects ADD COLUMN {$column} INTEGER DEFAULT NULL");
            echo "  - Added {$column} (INTEGER)\n";
        } elseif (in_array($column, ['booking_paid_at', 'mid_paid_at', 'final_paid_at'])) {
            DB::statement("ALTER TABLE projects ADD COLUMN {$column} DATETIME DEFAULT NULL");
            echo "  - Added {$column} (DATETIME)\n";
        }
    }
    
    echo "\nDone!\n";
}

echo "\nVerifying all columns now exist...\n";
$columns = DB::select('PRAGMA table_info(projects)');
$columnNames = array_column($columns, 'name');

$missingColumns = array_diff($requiredColumns, $columnNames);
if (empty($missingColumns)) {
    echo "SUCCESS: All required columns now exist!\n";
} else {
    echo "Still missing: " . implode(', ', $missingColumns) . "\n";
}