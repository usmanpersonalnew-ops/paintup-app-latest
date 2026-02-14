<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Project;

// Get a project with rooms and services
$project = Project::with(['rooms.items.surface', 'rooms.items.product', 'rooms.items.system', 'rooms.services.masterService'])
    ->first();

if ($project) {
    foreach ($project->rooms as $room) {
        if ($room->services && $room->services->count() > 0) {
            echo "Room: {$room->name}\n";
            foreach ($room->services as $service) {
                echo "  - Service: {$service->custom_name} (master_service_id: {$service->master_service_id})\n";
                if ($service->masterService) {
                    echo "    - MasterService Name: {$service->masterService->name}\n";
                    echo "    - MasterService Remarks: " . ($service->masterService->remarks ?: 'NULL') . "\n";
                } else {
                    echo "    - MasterService: NULL\n";
                }
            }
        }
    }
} else {
    echo "No project found\n";
}
