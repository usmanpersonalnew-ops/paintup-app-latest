<?php

/**
 * Payment Flow End-to-End Test Script
 * Tests: Admin -> Supervisor -> Customer -> Payment
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Project;
use App\Models\ProjectRoom;
use App\Models\QuoteItem;
use App\Models\QuoteService;
use App\Models\Customer;
use App\Models\MilestonePayment;
use App\Models\MasterSurface;
use App\Models\MasterProduct;
use App\Models\MasterService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PaintUp Payment Flow End-to-End Test ===\n\n";

// Test 1: Create Test Data
echo "1. Creating test data...\n";

$testPhone = '7021759374';

// Find or create customer
$customer = Customer::where('phone', $testPhone)->first();
if (!$customer) {
    $customer = Customer::create([
        'phone' => $testPhone,
        'name' => 'Test Customer',
        'email' => 'test@example.com',
    ]);
    echo "   - Created customer: {$customer->name}\n";
} else {
    echo "   - Found customer: {$customer->name}\n";
}

// Find master data
$surface = MasterSurface::first();
$product = MasterProduct::first();
$service = MasterService::first();

if (!$surface || !$product || !$service) {
    echo "   ERROR: Master data not found. Please run seeders first.\n";
    exit(1);
}

echo "   - Master surface: {$surface->name}\n";
echo "   - Master product: {$product->name}\n";
echo "   - Master service: {$service->name}\n";

// Test 2: Create Project
echo "\n2. Creating test project...\n";

$project = Project::where('phone', $testPhone)->first();
if (!$project) {
    $project = Project::create([
        'client_name' => 'Test Customer',
        'location' => 'Test Location',
        'phone' => $testPhone,
        'status' => 'NEW',
        'public_token' => \Illuminate\Support\Str::random(32),
    ]);
    echo "   - Created project ID: {$project->id}\n";
} else {
    echo "   - Found project ID: {$project->id}\n";
}

// Test 3: Create Room with Items and Services
echo "\n3. Creating room with items and services...\n";

$room = ProjectRoom::where('project_id', $project->id)->first();
if (!$room) {
    $room = ProjectRoom::create([
        'project_id' => $project->id,
        'name' => 'Living Room',
    ]);
    echo "   - Created room: {$room->name}\n";
} else {
    echo "   - Found room: {$room->name}\n";
}

// Create painting item
$item = QuoteItem::where('project_room_id', $room->id)->first();
if (!$item) {
    $item = QuoteItem::create([
        'project_room_id' => $room->id,
        'surface_id' => $surface->id,
        'product_id' => $product->id,
        'length' => 10,
        'height' => 8,
        'quantity' => 1,
        'amount' => 18000, // 10 * 8 * 225 (product rate)
    ]);
    echo "   - Created painting item: Amount = {$item->amount}\n";
} else {
    echo "   - Found painting item: Amount = {$item->amount}\n";
}

// Create service
$quoteService = QuoteService::where('project_room_id', $room->id)->first();
if (!$quoteService) {
    $quoteService = QuoteService::create([
        'project_room_id' => $room->id,
        'master_service_id' => $service->id,
        'quantity' => 1,
        'amount' => 3000,
    ]);
    echo "   - Created service: Amount = {$quoteService->amount}\n";
} else {
    echo "   - Found service: Amount = {$quoteService->amount}\n";
}

// Test 4: Calculate Totals
echo "\n4. Calculating totals...\n";

$totalPaintAmount = $room->items->sum('amount');
$totalServiceAmount = $room->services->sum('amount');
$discountAmount = $project->discount_amount ?? 0;
$baseTotal = $totalPaintAmount + $totalServiceAmount - $discountAmount;
$gstRate = 0;
$gstAmount = round($baseTotal * ($gstRate / 100), 2);
$totalWithGst = $baseTotal + $gstAmount;

echo "   - Painting Total: {$totalPaintAmount}\n";
echo "   - Services Total: {$totalServiceAmount}\n";
echo "   - Discount: {$discountAmount}\n";
echo "   - Base Total (Excl GST): {$baseTotal}\n";
echo "   - GST @{$gstRate}%: {$gstAmount}\n";
echo "   - Total (Incl GST): {$totalWithGst}\n";

// Test 5: Calculate Milestones
echo "\n5. Calculating milestones (40-40-20)...\n";

$bookingBase = round($baseTotal * 0.40, 2);
$midBase = round($baseTotal * 0.40, 2);
$finalBase = round($baseTotal * 0.20, 2);

$bookingGst = round($bookingBase * ($gstRate / 100), 2);
$midGst = round($midBase * ($gstRate / 100), 2);
$finalGst = round($finalBase * ($gstRate / 100), 2);

$bookingTotal = $bookingBase + $bookingGst;
$midTotal = $midBase + $midGst;
$finalTotal = $finalBase + $finalGst;

echo "   - Booking (40%): Base = {$bookingBase}, GST = {$bookingGst}, Total = {$bookingTotal}\n";
echo "   - Mid (40%): Base = {$midBase}, GST = {$midGst}, Total = {$midTotal}\n";
echo "   - Final (20%): Base = {$finalBase}, GST = {$finalGst}, Total = {$finalTotal}\n";

// Test 6: Save to Project
echo "\n6. Saving to project...\n";

$project->total_amount = $totalPaintAmount + $totalServiceAmount;
$project->base_total = $baseTotal;
$project->gst_rate = $gstRate;
$project->booking_amount = $bookingBase;
$project->mid_amount = $midBase;
$project->final_amount = $finalBase;
$project->booking_status = 'PENDING';
$project->mid_status = 'PENDING';
$project->final_status = 'PENDING';
$project->save();

echo "   - Project saved successfully\n";

// Test 7: Create Booking Payment
echo "\n7. Creating booking payment...\n";

$existingPayment = MilestonePayment::where('project_id', $project->id)
    ->where('milestone_name', 'booking')
    ->first();

if (!$existingPayment) {
    $payment = MilestonePayment::create([
        'project_id' => $project->id,
        'milestone_name' => 'booking',
        'base_amount' => $bookingBase,
        'gst_amount' => $bookingGst,
        'total_amount' => $bookingTotal,
        'payment_status' => 'PAID',
        'payment_method' => 'ONLINE',
        'payment_reference' => 'ONLINE-TEST-' . strtoupper(uniqid()),
        'paid_at' => now(),
    ]);
    echo "   - Created booking payment: {$payment->payment_reference}\n";
} else {
    echo "   - Found existing booking payment: {$existingPayment->payment_reference}\n";
    $payment = $existingPayment;
}

// Update project status
$project->booking_status = 'PAID';
$project->booking_amount = $bookingBase;
$project->booking_gst = $bookingGst;
$project->booking_total = $bookingTotal;
$project->status = 'ACCEPTED';
$project->save();

echo "   - Project booking status updated to PAID\n";

// Test 8: Create Mid Payment
echo "\n8. Creating mid payment...\n";

$midPayment = MilestonePayment::where('project_id', $project->id)
    ->where('milestone_name', 'mid')
    ->first();

if (!$midPayment) {
    $midPayment = MilestonePayment::create([
        'project_id' => $project->id,
        'milestone_name' => 'mid',
        'base_amount' => $midBase,
        'gst_amount' => $midGst,
        'total_amount' => $midTotal,
        'payment_status' => 'PAID',
        'payment_method' => 'ONLINE',
        'payment_reference' => 'MID-ONLINE-' . strtoupper(uniqid()),
        'paid_at' => now(),
    ]);
    echo "   - Created mid payment: {$midPayment->payment_reference}\n";
} else {
    echo "   - Found existing mid payment: {$midPayment->payment_reference}\n";
}

$project->mid_status = 'PAID';
$project->mid_amount = $midBase;
$project->mid_gst = $midGst;
$project->mid_total = $midTotal;
$project->save();

echo "   - Project mid status updated to PAID\n";

// Test 9: Create Final Payment
echo "\n9. Creating final payment...\n";

$finalPayment = MilestonePayment::where('project_id', $project->id)
    ->where('milestone_name', 'final')
    ->first();

if (!$finalPayment) {
    $finalPayment = MilestonePayment::create([
        'project_id' => $project->id,
        'milestone_name' => 'final',
        'base_amount' => $finalBase,
        'gst_amount' => $finalGst,
        'total_amount' => $finalTotal,
        'payment_status' => 'PAID',
        'payment_method' => 'ONLINE',
        'payment_reference' => 'FINAL-ONLINE-' . strtoupper(uniqid()),
        'paid_at' => now(),
    ]);
    echo "   - Created final payment: {$finalPayment->payment_reference}\n";
} else {
    echo "   - Found existing final payment: {$finalPayment->payment_reference}\n";
}

$project->final_status = 'PAID';
$project->final_amount = $finalBase;
$project->final_gst = $finalGst;
$project->final_total = $finalTotal;
$project->status = 'COMPLETED';
$project->save();

echo "   - Project final status updated to PAID\n";

// Test 10: Verify All Payments
echo "\n10. Verifying all payments...\n";

$payments = MilestonePayment::where('project_id', $project->id)->get();
$totalPaid = $payments->sum('total_amount');

echo "   - Total payments: {$payments->count()}\n";
echo "   - Total paid: {$totalPaid}\n";
echo "   - Expected total: {$totalWithGst}\n";

if (abs($totalPaid - $totalWithGst) < 0.01) {
    echo "   - STATUS: PASS - Payment totals match!\n";
} else {
    echo "   - STATUS: FAIL - Payment totals don't match!\n";
}

// Test 11: Summary
echo "\n=== TEST SUMMARY ===\n";
echo "Project ID: {$project->id}\n";
echo "Customer: {$customer->name} ({$customer->phone})\n";
echo "Room: {$room->name}\n";
echo "Painting Item: {$item->amount}\n";
echo "Service: {$quoteService->amount}\n";
echo "Base Total: {$baseTotal}\n";
echo "GST Amount: {$gstAmount}\n";
echo "Total with GST: {$totalWithGst}\n";
echo "Milestones:\n";
echo "   - Booking: {$bookingBase} + {$bookingGst} = {$bookingTotal}\n";
echo "   - Mid: {$midBase} + {$midGst} = {$midTotal}\n";
echo "   - Final: {$finalBase} + {$finalGst} = {$finalTotal}\n";
echo "All Payments: {$totalPaid}\n";
echo "Status: {$project->status}\n";

echo "\n=== END OF TEST ===\n";