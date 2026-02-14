<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponApplyController extends Controller
{
    /**
     * Apply a coupon to a project
     */
    public function apply(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if payment already exists
        if ($project->hasPayment()) {
            return back()->withErrors([
                'error' => 'Cannot apply coupon after payment has been made',
            ]);
        }

        // Check if coupon already applied
        if ($project->coupon_code) {
            return back()->withErrors([
                'error' => 'A coupon has already been applied to this project',
            ]);
        }

        // Find and validate coupon
        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

        if (!$coupon) {
            return back()->withErrors([
                'coupon_code' => 'Invalid coupon code',
            ]);
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return back()->withErrors([
                'coupon_code' => 'This coupon is no longer active',
            ]);
        }

        // Check if coupon is expired
        if ($coupon->is_expired) {
            return back()->withErrors([
                'coupon_code' => 'This coupon has expired',
            ]);
        }

        // Load rooms with items and services to calculate actual subtotal
        $project->load(['rooms.items', 'rooms.services']);
        
        // Calculate subtotal from rooms (sum of items and services)
        $paintTotal = $project->rooms->sum(function ($room) {
            return $room->items->sum('amount');
        });
        
        $serviceTotal = $project->rooms->sum(function ($room) {
            return $room->services->sum('amount');
        });
        
        $subtotal = $paintTotal + $serviceTotal;
        
        // Calculate GST (18%) to get Grand Total
        $gstRate = 18;
        $gstAmount = $subtotal * ($gstRate / 100);
        $grandTotal = $subtotal + $gstAmount;

        // Debug logging
        \Log::info('Coupon calculation', [
            'project_id' => $project->id,
            'rooms_count' => $project->rooms->count(),
            'paintTotal' => $paintTotal,
            'serviceTotal' => $serviceTotal,
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'grand_total' => $grandTotal,
            'coupon_value' => $coupon->value,
            'coupon_type' => $coupon->type,
        ]);

        // Check minimum order requirement on GRAND TOTAL (including GST)
        if (!$coupon->meetsMinOrderRequirement($grandTotal)) {
            return back()->withErrors([
                'coupon_code' => 'Minimum order amount of Rs. ' . number_format($coupon->min_order_amount, 2) . ' required. Current order: Rs. ' . number_format($grandTotal, 2),
            ]);
        }

        // Calculate and apply discount on GRAND TOTAL
        $discount = $coupon->calculateDiscount($grandTotal);

        \Log::info('Coupon discount calculated', [
            'discount' => $discount,
            'grand_total' => $grandTotal,
        ]);

        $project->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount,
        ]);

        return redirect()->route('supervisor.summary', $project->id)
            ->with('success', "Coupon {$coupon->code} applied successfully! Discount: Rs. " . number_format($discount, 2));
    }

    /**
     * Remove a coupon from a project
     */
    public function remove(Request $request, Project $project)
    {
        // Check if payment already exists
        if ($project->hasPayment()) {
            return back()->withErrors([
                'error' => 'Cannot remove coupon after payment has been made',
            ]);
        }

        if (!$project->coupon_code) {
            return back()->withErrors([
                'error' => 'No coupon applied to this project',
            ]);
        }

        $couponCode = $project->coupon_code;

        $project->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'discount_amount' => 0,
        ]);

        return redirect()->route('supervisor.summary', $project->id)
            ->with('success', "Coupon {$couponCode} removed successfully");
    }
}
