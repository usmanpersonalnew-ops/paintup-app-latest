<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\QuoteService;
use App\Models\ProjectRoom;

echo "=== Testing Services Data ===\n\n";

// Get first project with rooms
$project = Project::with(['rooms.services'])->first();

if (!$project) {
    echo "No projects found!\n";
    exit;
}

echo "Project ID: {$project->id}\n";
echo "Project Phone: {$project->phone}\n\n";

foreach ($project->rooms as $roomIndex => $room) {
    echo "--- Room: {$room->name} (ID: {$room->id}) ---\n";
    
    echo "Services count: " . $room->services->count() . "\n";
    
    foreach ($room->services as $serviceIndex => $service) {
        echo "  Service #{$serviceIndex}:\n";
        echo "    ID: {$service->id}\n";
        echo "    master_service_id: {$service->master_service_id}\n";
        echo "    quantity: " . var_export($service->quantity, true) . "\n";
        echo "    rate: " . var_export($service->rate, true) . "\n";
        echo "    amount: " . var_export($service->amount, true) . "\n";
        
        // Calculate expected amount
        $calculatedAmount = ($service->quantity ?? 0) * ($service->rate ?? 0);
        echo "    calculated (qty*rate): {$calculatedAmount}\n\n";
    }
    
    echo "Items count: " . $room->items->count() . "\n";
    
    foreach ($room->items as $itemIndex => $item) {
        echo "  Item #{$itemIndex}:\n";
        echo "    ID: {$item->id}\n";
        echo "    qty: " . var_export($item->qty, true) . "\n";
        echo "    rate: " . var_export($item->rate, true) . "\n";
        echo "    amount: " . var_export($item->amount, true) . "\n";
        
        // Calculate expected amount
        $calculatedAmount = ($item->qty ?? 0) * ($item->rate ?? 0);
        echo "    calculated (qty*rate): {$calculatedAmount}\n\n";
    }
}

// Check raw database values
echo "\n=== Raw Database Query ===\n";
$rawServices = QuoteService::select('id', 'project_room_id', 'quantity', 'rate', 'amount')->limit(5)->get();
foreach ($rawServices as $service) {
    echo "Service ID: {$service->id}, room_id: {$service->project_room_id}, qty: {$service->quantity}, rate: {$service->rate}, amount: {$service->amount}\n";
}

echo "\n=== Done ===\n";
