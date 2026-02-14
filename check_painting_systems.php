<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Force output buffering
ob_start();

$count = DB::table('master_painting_systems')->count();
echo "Total painting systems: $count\n\n";

$all = DB::table('master_painting_systems')->select('id', 'system_name', 'process_remarks')->get();
foreach ($all as $s) {
    echo "ID: $s->id, Name: $s->system_name, Remarks: " . ($s->process_remarks ?: 'NULL') . "\n";
}

$output = ob_get_clean();
file_put_contents('db_output.txt', $output);
echo $output;
