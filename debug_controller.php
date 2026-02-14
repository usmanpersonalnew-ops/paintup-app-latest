<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;

// Get all projects with their phone numbers
$projects = Project::with(['rooms.items', 'rooms.services'])->get();

echo "All projects:\n";
foreach ($projects as $project) {
    echo "ID: {$project->id}, Phone: {$project->phone}\n";
}

// Get first project
$firstProject = Project::with(['rooms.items', 'rooms.services'])->first();

echo "\nJSON output for first project:\n";
echo json_encode([
    'id' => $firstProject->id,
    'base_total' => $firstProject->base_total,
    'total_amount' => $firstProject->total_amount,
    'booking_amount' => $firstProject->booking_amount,
    'mid_amount' => $firstProject->mid_amount,
    'final_amount' => $firstProject->final_amount,
], JSON_PRETTY_PRINT);
