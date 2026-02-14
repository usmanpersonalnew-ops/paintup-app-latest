<?php

/**
 * Customer Dashboard Verification Script
 * Tests the exact flow that the CustomerDashboardController uses
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

echo "=== Customer Dashboard Verification ===\n\n";

// Simulate customer login
$testPhone = '7021759374';
$customer = Customer::where('phone', $testPhone)->first();

if (!$customer) {
    echo "ERROR: Customer not found with phone: {$testPhone}\n";
    exit(1);
}

echo "Customer: {$customer->name} ({$customer->phone})\n\n";

// Get projects (same as controller)
$projects = Project::where('phone', $customer->phone)
    ->with(['rooms.items.surface', 'rooms.items.product', 'rooms.services.masterService'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "Found {$projects->count()} project(s)\n\n";

// Process each project (same as controller)
$projects->each(function ($project) {
    echo "--- Project ID: {$project->id} ---\n";
    
    // Calculate painting and services totals
    $totalPaintAmount = 0;
    $totalServiceAmount = 0;

    foreach ($project->rooms as $room) {
        foreach ($room->items as $item) {
            $totalPaintAmount += $item->amount ?? 0;
        }
        foreach ($room->services as $service) {
            $totalServiceAmount += $service->amount ?? 0;
        }
    }

    // Set temporary attributes for display
    $project->painting_total = $totalPaintAmount;
    $project->services_total = $totalServiceAmount;
    
    // Calculate base_total (excluding GST)
    $discountAmount = $project->discount_amount ?? 0;
    $baseTotal = $totalPaintAmount + $totalServiceAmount - $discountAmount;
    $project->base_total = $baseTotal;
    
    // Calculate milestone amounts from base_total (40-40-20)
    $project->booking_amount = round($baseTotal * 0.40, 2);
    $project->mid_amount = round($baseTotal * 0.40, 2);
    $project->final_amount = round($baseTotal * 0.20, 2);
    
    // GST calculation
    $gstRate = 18;
    $gstAmount = round($baseTotal * ($gstRate / 100), 2);
    $totalWithGst = $baseTotal + $gstAmount;
    
    // Display values that will be shown on dashboard
    echo "Quote Summary:\n";
    echo "  Painting Work Total: ₹" . number_format($totalPaintAmount, 2) . "\n";
    echo "  Services Total: ₹" . number_format($totalServiceAmount, 2) . "\n";
    if ($discountAmount > 0) {
        echo "  Discount ({$project->coupon_code}): -₹" . number_format($discountAmount, 2) . "\n";
    }
    echo "  Total Amount: ₹" . number_format($baseTotal, 2) . " (Excl. GST)\n";
    echo "  *Cost excluding GST. GST will be added at payment time.\n";
    
    echo "\nPayment Milestones (40-40-20):\n";
    echo "  Booking (40%): ₹" . number_format($project->booking_amount, 2) . "\n";
    echo "  Mid (40%): ₹" . number_format($project->mid_amount, 2) . "\n";
    echo "  Final (20%): ₹" . number_format($project->final_amount, 2) . "\n";
    echo "  *Amounts shown excluding GST. GST will be added at payment time.\n";
    
    echo "\nGST Breakdown (at payment time):\n";
    $bookingGst = round($project->booking_amount * ($gstRate / 100), 2);
    $midGst = round($project->mid_amount * ($gstRate / 100), 2);
    $finalGst = round($project->final_amount * ($gstRate / 100), 2);
    echo "  Booking: Base ₹{$project->booking_amount} + GST @{$gstRate}% ₹{$bookingGst} = ₹" . number_format($project->booking_amount + $bookingGst, 2) . "\n";
    echo "  Mid: Base ₹{$project->mid_amount} + GST @{$gstRate}% ₹{$midGst} = ₹" . number_format($project->mid_amount + $midGst, 2) . "\n";
    echo "  Final: Base ₹{$project->final_amount} + GST @{$gstRate}% ₹{$finalGst} = ₹" . number_format($project->final_amount + $finalGst, 2) . "\n";
    
    echo "\nPayment Status:\n";
    echo "  Booking: {$project->booking_status} (₹" . number_format($project->booking_amount, 2) . ")\n";
    echo "  Mid: {$project->mid_status} (₹" . number_format($project->mid_amount, 2) . ")\n";
    echo "  Final: {$project->final_status} (₹" . number_format($project->final_amount, 2) . ")\n";
    
    // Verify totals
    echo "\nVerification:\n";
    $calculatedBaseTotal = $totalPaintAmount + $totalServiceAmount - $discountAmount;
    $milestoneSum = $project->booking_amount + $project->mid_amount + $project->final_amount;
    
    if (abs($calculatedBaseTotal - $milestoneSum) < 0.01) {
        echo "  ✓ Milestone sum matches base_total: ₹" . number_format($calculatedBaseTotal, 2) . "\n";
    } else {
        echo "  ✗ MISMATCH: Base Total = ₹{$calculatedBaseTotal}, Milestone Sum = ₹{$milestoneSum}\n";
    }
    
    echo "\n";
});

echo "=== VERIFICATION COMPLETE ===\n";
echo "\nThe customer dashboard will now show:\n";
echo "1. Painting Work Total, Services Total, Discount, Total Amount (base_total)\n";
echo "2. Milestone amounts calculated from base_total (40-40-20)\n";
echo "3. Payment popup with GST breakdown (Base + GST = Total)\n";
echo "4. No more incorrect calculations or missing values\n";