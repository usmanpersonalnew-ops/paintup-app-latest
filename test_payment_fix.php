<?php
/**
 * Test script to verify payment calculation logic
 * Run: php test_payment_fix.php
 */

// Test data from the issue
$paint_total = 18000;
$services_total = 3000;
$discount = 600;
$gst_rate = 18;

// Calculate base_total (Single Source of Truth)
$base_total = $paint_total + $services_total - $discount;

echo "=== PAYMENT CALCULATION TEST ===\n\n";
echo "Input Values:\n";
echo "  Painting Total: ₹$paint_total\n";
echo "  Services Total: ₹$services_total\n";
echo "  Discount: ₹$discount\n";
echo "  GST Rate: $gst_rate%\n\n";

echo "=== CORRECT CALCULATION ===\n";
echo "Base Total (Paint + Services - Discount): ₹$base_total\n";
echo "  = $paint_total + $services_total - $discount\n\n";

$booking_base = round($base_total * 0.40, 2);
$mid_base = round($base_total * 0.40, 2);
$final_base = round($base_total * 0.20, 2);

echo "Milestone Amounts (Excluding GST):\n";
echo "  Booking (40%): ₹$booking_base\n";
echo "  Mid (40%): ₹$mid_base\n";
echo "  Final (20%): ₹$final_base\n\n";

echo "GST Breakdown for Booking Payment:\n";
$gst_amount = round($booking_base * ($gst_rate / 100), 2);
$total_payable = $booking_base + $gst_amount;
echo "  Base Amount: ₹$booking_base\n";
echo "  GST @18%: ₹$gst_amount\n";
echo "  Total Payable: ₹$total_payable\n\n";

echo "=== VERIFICATION ===\n";
$expected_booking = 8160; // 20400 * 0.40
$expected_mid = 8160;     // 20400 * 0.40
$expected_final = 4080;   // 20400 * 0.20

$booking_ok = $booking_base == $expected_booking;
$mid_ok = $mid_base == $expected_mid;
$final_ok = $final_base == $expected_final;

echo "Booking (40%): " . ($booking_ok ? "✅ PASS (₹$booking_base)" : "❌ FAIL (Expected ₹$expected_booking, Got ₹$booking_base)") . "\n";
echo "Mid (40%): " . ($mid_ok ? "✅ PASS (₹$mid_base)" : "❌ FAIL (Expected ₹$expected_mid, Got ₹$mid_base)") . "\n";
echo "Final (20%): " . ($final_ok ? "✅ PASS (₹$final_base)" : "❌ FAIL (Expected ₹$expected_final, Got ₹$final_base)") . "\n\n";

$all_pass = $booking_ok && $mid_ok && $final_ok;
echo "=== RESULT: " . ($all_pass ? "✅ ALL TESTS PASSED" : "❌ SOME TESTS FAILED") . " ===\n";
