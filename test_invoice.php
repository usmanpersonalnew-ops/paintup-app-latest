<?php
// Test InvoiceGenerator service
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get a project with minimal fields
$projectId = 1;
$p = \App\Models\Project::select([
    'id', 'name', 'customer_name', 'customer_phone', 'address',
    'booking_amount', 'mid_amount', 'final_amount',
    'booking_gst', 'mid_gst', 'final_gst',
    'total_amount', 'base_total'
])->find($projectId);

if (!$p) {
    echo "Project not found!\n";
    exit(1);
}

echo "Project loaded: ID=" . $p->id . "\n";

// Test InvoiceGenerator
$ig = new \App\Services\Documents\InvoiceGenerator();
$d = $ig->generate($p);

echo "Invoice Number: " . $d['invoice_number'] . "\n";
echo "Invoice Date: " . $d['invoice_date'] . "\n";
echo "Base Total: " . $d['totals']['base_total'] . "\n";
echo "Grand Total: " . $d['totals']['grand_total'] . "\n";
echo "Rooms: " . count($d['room_breakdown']) . "\n";
echo "Company: " . $d['settings']['company_name'] . "\n";
echo "GST Number: " . $d['settings']['gst_number'] . "\n";

echo "\nInvoiceGenerator test successful!\n";
