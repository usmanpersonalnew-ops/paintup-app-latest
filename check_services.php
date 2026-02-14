<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Quote Services ===\n";
$services = DB::table('quote_services')
    ->join('master_services', 'quote_services.master_service_id', '=', 'master_services.id')
    ->select('quote_services.id', 'quote_services.master_service_id', 'master_services.name', 'master_services.remarks')
    ->limit(10)
    ->get();
foreach ($services as $s) {
    echo "ID: $s->id, master_service_id: $s->master_service_id, Name: $s->name, Remarks: " . ($s->remarks ?: 'NULL') . "\n";
}
