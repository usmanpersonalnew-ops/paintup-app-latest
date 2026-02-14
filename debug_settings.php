<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Settings Table Data ===\n\n";

$settings = DB::table('settings')->get();

foreach ($settings as $row) {
    echo "Row ID: {$row->id}\n";
    echo "  company_name: " . ($row->company_name ?: 'NULL') . "\n";
    echo "  logo_path: " . ($row->logo_path ?: 'NULL') . "\n";
    echo "  gst_number: " . ($row->gst_number ?: 'NULL') . "\n";
    echo "  address: " . ($row->address ?: 'NULL') . "\n";
    echo "  invoice_prefix: " . ($row->invoice_prefix ?: 'NULL') . "\n";
    echo "  support_email: " . ($row->support_email ?: 'NULL') . "\n";
    echo "  support_whatsapp: " . ($row->support_whatsapp ?: 'NULL') . "\n";
    echo "  footer_text: " . ($row->footer_text ?: 'NULL') . "\n";
    echo "  primary_color: " . ($row->primary_color ?: 'NULL') . "\n";
    echo "  secondary_color: " . ($row->secondary_color ?: 'NULL') . "\n";
    echo "\n";
}

echo "Total rows: " . $settings->count() . "\n";