<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;

$projects = Project::with('rooms.items', 'rooms.services')->get();

foreach ($projects as $project) {
    $totalPaintAmount = $project->rooms->sum(function ($room) {
        return $room->items->sum('amount');
    });
    $totalServiceAmount = $project->rooms->sum(function ($room) {
        return $room->services->sum('amount');
    });

    echo "Project ID: {$project->id}\n";
    echo "  base_total: {$project->base_total}\n";
    echo "  total_amount: {$project->total_amount}\n";
    echo "  booking_amount: {$project->booking_amount}\n";
    echo "  painting_total (calculated): {$totalPaintAmount}\n";
    echo "  services_total (calculated): {$totalServiceAmount}\n";
    echo "  rooms count: {$project->rooms->count()}\n";
    echo "\n";
}