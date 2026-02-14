<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Before Fix ===\n";
$items = DB::table('quote_items')
    ->select('id', 'master_system_id', 'master_product_id')
    ->limit(10)
    ->get();
foreach ($items as $i) {
    echo "QuoteItem ID: $i->id, master_system_id: $i->master_system_id\n";
}

// Update all quote_items with master_system_id 1-5 to use system ID 8
$updated = DB::table('quote_items')
    ->whereIn('master_system_id', [1, 2, 3, 4, 5])
    ->update(['master_system_id' => 8]);

echo "\n=== Updated $updated rows ===\n";

echo "\n=== After Fix ===\n";
$items = DB::table('quote_items')
    ->select('id', 'master_system_id', 'master_product_id')
    ->limit(10)
    ->get();
foreach ($items as $i) {
    echo "QuoteItem ID: $i->id, master_system_id: $i->master_system_id\n";
}
