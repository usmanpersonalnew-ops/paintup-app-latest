<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Notifications\UserCredentialsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'phone', 'role', 'status', 'last_login_at', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

        public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:ADMIN,SUPERVISOR',
            'password' => 'required|string|min:8|confirmed',
            'send_credentials' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => 'ACTIVE',
        ]);

        // Assign role using Spatie
        $user->assignRole($validated['role']);

        // Send credentials email if requested
        if ($request->boolean('send_credentials')) {
            $loginUrl = route('login');
            $user->notify(new UserCredentialsNotification(
                $user->email,
                $validated['password'],
                $loginUrl
            ));
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();

        // Get user's role from Spatie
        $userRole = $user->roles->first()?->name ?? '';

        // Prevent editing self
        $canEditRole = $currentUser->id !== $user->id;
        $canEditStatus = $currentUser->id !== $user->id;

        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $userRole, // From Spatie
                'status' => $user->status,
            ],
            'permissions' => [
                'can_edit_role' => $canEditRole,
                'can_edit_status' => $canEditStatus,
                'is_self' => $currentUser->id === $user->id,
            ],
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ];

        // Only allow role change if not editing self
        if ($currentUser->id !== $user->id) {
            $rules['role'] = 'required|string|in:ADMIN,SUPERVISOR';
        }

        // Only allow status change if not editing self
        if ($currentUser->id !== $user->id) {
            $rules['status'] = 'required|string|in:ACTIVE,INACTIVE';
        }

        // Add password validation only if password is provided
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        // Add status if allowed and provided
        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        // Add password if provided
        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update role using Spatie if allowed and provided
        if ($currentUser->id !== $user->id && isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Activate the specified user.
     */
    public function activate(Request $request, User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->activate();

        return back()->with('success', 'User activated successfully.');
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(Request $request, User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->deactivate();

        return back()->with('success', 'User deactivated successfully.');
    }

    /**
     * Reset password for the specified user.
     */
    public function resetPassword(Request $request, User $user)
    {
        // Generate new temporary password
        $temporaryPassword = Str::random(12);

        $user->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        // Send password reset email
        $loginUrl = route('login');
        $user->notify(new PasswordResetNotification(
            $user->email,
            $temporaryPassword,
            $loginUrl
        ));

        return back()->with('success', 'Password reset successfully. New credentials sent to user email.');
    }

    /**
     * Remove the specified user (soft delete).
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Prevent deleting self
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting other admins
        if ($user->role === 'ADMIN') {
            return back()->with('error', 'Admin accounts cannot be deleted. Please deactivate instead.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}