<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$project = App\Models\Project::find(3);
echo "Project 3:\n";
echo "coupon_code: " . $project->coupon_code . "\n";
echo "discount_amount: " . $project->discount_amount . "\n";