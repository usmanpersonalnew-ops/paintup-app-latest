<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display customers list
     */
    public function index()
    {
        $customers = User::role('customer')
            ->latest()
            ->paginate(2);

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return Inertia::render('Admin/Customers/Create');
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'ACTIVE',
        ]);

        $user->assignRole('customer');

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully');
    }

    /**
     * Show customer details
     */
    public function show(User $customer)
    {
        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(User $customer)
    {
        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Update customer
     */
    public function update(Request $request, User $customer)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'required',
        ]);

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status ?? 'ACTIVE'
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully');
    }

    /**
     * Delete customer
     */
    public function destroy(User $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully');
    }
}