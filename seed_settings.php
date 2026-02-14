<?php
/**
 * Simple script to seed settings table
 * Run: php seed_settings.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Setting;

// Create settings table
try {
    DB::statement("
        CREATE TABLE IF NOT EXISTS settings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(255) DEFAULT 'PaintUp',
            logo_path VARCHAR(255) NULL,
            primary_color VARCHAR(20) DEFAULT '#2563eb',
            secondary_color VARCHAR(20) DEFAULT '#1e293b',
            support_whatsapp VARCHAR(20) NULL,
            support_email VARCHAR(255) NULL,
            footer_text VARCHAR(500) NULL,
            gst_number VARCHAR(50) NULL,
            address TEXT NULL,
            invoice_prefix VARCHAR(20) DEFAULT 'INV',
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Settings table created successfully\n";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}

// Insert default settings
try {
    $exists = DB::table('settings')->where('id', 1)->exists();
    if (!$exists) {
        DB::table('settings')->insert([
            'id' => 1,
            'company_name' => 'PaintUp',
            'primary_color' => '#2563eb',
            'secondary_color' => '#1e293b',
            'invoice_prefix' => 'INV',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ Default settings inserted\n";
    } else {
        echo "✓ Settings already exist\n";
    }
} catch (Exception $e) {
    echo "Error inserting settings: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";