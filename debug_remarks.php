<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Master Painting Systems ===\n";
$paintings = DB::table('master_painting_systems')->select('id', 'system_name', 'process_remarks')->get();
foreach ($paintings as $p) {
    echo "ID: {$p->id}, Name: {$p->system_name}, Remarks: " . ($p->process_remarks ?: 'NULL') . "\n";
}

echo "\n=== Master Services ===\n";
$services = DB::table('master_services')->select('id', 'name', 'remarks')->get();
foreach ($services as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Remarks: " . ($s->remarks ?: 'NULL') . "\n";
}

echo "\n=== Quote Items (sample) ===\n";
$items = DB::table('quote_items')
    ->select('id', 'master_system_id', 'master_product_id')
    ->limit(5)
    ->get();
foreach ($items as $i) {
    echo "ID: {$i->id}, master_system_id: {$i->master_system_id}, master_product_id: {$i->master_product_id}\n";
}

echo "\n=== Quote Services (sample) ===\n";
$services = DB::table('quote_services')
    ->select('id', 'master_service_id', 'custom_name')
    ->limit(5)
    ->get();
foreach ($services as $s) {
    echo "ID: {$s->id}, master_service_id: {$s->master_service_id}, custom_name: {$s->custom_name}\n";
}