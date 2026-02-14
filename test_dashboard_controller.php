<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;

echo "=== Testing Dashboard Controller Logic ===\n\n";

// Get all projects for this customer (by phone number)
$customerPhone = '7021759374'; // Use existing phone
$projects = Project::where('phone', $customerPhone)
    ->with(['rooms.items.surface', 'rooms.items.product', 'rooms.services.masterService'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "Projects found: " . $projects->count() . "\n\n";

// Calculate totals and ensure public_token exists for each project
$projects->each(function ($project) {
    // Calculate painting and services totals from rooms
    $totalPaintAmount = 0;
    $totalServiceAmount = 0;

    foreach ($project->rooms as $room) {
        foreach ($room->items as $item) {
            // Calculate amount from qty * rate if amount is null
            $itemAmount = $item->amount ?? ($item->qty ?? 0) * ($item->rate ?? 0);
            $totalPaintAmount += $itemAmount;
        }
        foreach ($room->services as $service) {
            // Calculate amount from quantity * rate if amount is null
            $serviceAmount = $service->amount ?? ($service->quantity ?? 0) * ($service->rate ?? 0);
            $totalServiceAmount += $serviceAmount;
        }
    }

    echo "Project ID: {$project->id}\n";
    echo "  Total Paint Amount: {$totalPaintAmount}\n";
    echo "  Total Service Amount: {$totalServiceAmount}\n";
    echo "  Base Total: " . ($totalPaintAmount + $totalServiceAmount) . "\n\n";
    
    // Check each room's services
    foreach ($project->rooms as $roomIndex => $room) {
        echo "  Room: {$room->name}\n";
        echo "    Services:\n";
        foreach ($room->services as $service) {
            $serviceAmount = $service->amount ?? ($service->quantity ?? 0) * ($service->rate ?? 0);
            echo "      - ID: {$service->id}, amount: {$service->amount}, quantity: {$service->quantity}, rate: {$service->rate}, calculated: {$serviceAmount}\n";
        }
    }
    echo "\n";
});

// Check JSON serialization
echo "=== JSON Output Test ===\n";
$testProject = $projects->first();
$json = json_encode($testProject, JSON_PRETTY_PRINT);
echo substr($json, 0, 2000) . "\n\n";

// Check if services are in the JSON
if (strpos($json, 'services') !== false) {
    echo "✓ Services found in JSON output\n";
} else {
    echo "✗ Services NOT found in JSON output\n";
}

echo "\n=== Done ===\n";
