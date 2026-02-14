<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CustomerProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = Auth::guard('customer')->user();

        return Inertia::render('Customer/Profile', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
        ]);

        return redirect()->route('customer.profile')
            ->with('success', 'Profile updated successfully!');
    }
}