<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons.
     */
    public function index(Request $request)
    {
        $coupons = Coupon::orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Coupons/Index', [
            'coupons' => $coupons,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        return Inertia::render('Admin/Coupons/Create');
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code',
            ],
            'type' => [
                'required',
                'in:FLAT,PERCENT',
            ],
            'value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'min_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
            'is_active' => [
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate percent value max 100
        if ($request->type === 'PERCENT' && $request->value > 100) {
            return back()->withErrors([
                'value' => 'Percent value cannot exceed 100',
            ])->withInput();
        }

        Coupon::create([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_order_amount' => $request->min_order_amount,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    /**
     * Show the form for editing a coupon.
     */
    public function edit(Coupon $coupon, Request $request)
    {
        // Check if coupon has been used in any project with payment
        $hasUsage = $coupon->projects()->whereNotNull('booking_paid_at')->exists();

        return Inertia::render('Admin/Coupons/Edit', [
            'coupon' => $coupon,
            'hasUsage' => $hasUsage,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        // Check if coupon has been used - prevent editing
        $hasUsage = $coupon->projects()->exists();

        if ($hasUsage) {
            return back()->withErrors([
                'error' => 'Cannot edit coupon that has been used in projects',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code,' . $coupon->id,
            ],
            'type' => [
                'required',
                'in:FLAT,PERCENT',
            ],
            'value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'min_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
            'is_active' => [
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate percent value max 100
        if ($request->type === 'PERCENT' && $request->value > 100) {
            return back()->withErrors([
                'value' => 'Percent value cannot exceed 100',
            ])->withInput();
        }

        $coupon->update([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_order_amount' => $request->min_order_amount,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully');
    }

    /**
     * Toggle coupon active status.
     */
    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update([
            'is_active' => !$coupon->is_active,
        ]);

        $status = $coupon->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Coupon {$status} successfully");
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        // Check if coupon has been used
        $hasUsage = $coupon->projects()->exists();

        if ($hasUsage) {
            return back()->withErrors([
                'error' => 'Cannot delete coupon that has been used in projects. Deactivate it instead.',
            ]);
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully');
    }
}