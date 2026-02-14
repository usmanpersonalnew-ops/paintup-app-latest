<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Role-based redirect after login - FORCE direct paths to avoid redirect loops
        $user = $request->user();
        
        if ($user) {
            if ($user->role === 'ADMIN') {
                return redirect()->intended('/admin/projects');
            } elseif ($user->role === 'SUPERVISOR') {
                return redirect()->intended('/supervisor/projects');
            } else {
                // Invalid role - logout and redirect to login with error
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Invalid user role. Please contact support.']);
            }
        }

        // Fallback - should not reach here if auth was successful
        return redirect()->route('login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
