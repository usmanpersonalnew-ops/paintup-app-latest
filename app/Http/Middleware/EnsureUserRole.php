<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            // If trying to access admin routes, redirect to admin login
            if ($role === 'admin') {
                return redirect('/login');
            }
            // If trying to access supervisor routes, redirect to supervisor login
            if ($role === 'supervisor') {
                return redirect('/supervisor/login');
            }
            return redirect('/login');
        }

        $userRole = $request->user()->role;

        // Supervisor trying to access admin routes - BLOCK and redirect to supervisor dashboard
        if ($role === 'admin' && $userRole === 'SUPERVISOR') {
            return redirect('/supervisor/dashboard');
        }

        // Admin trying to access supervisor routes - BLOCK and redirect to admin dashboard
        if ($role === 'supervisor' && $userRole === 'ADMIN') {
            return redirect('/admin/dashboard');
        }

        // Check if user has the required role
        $expectedRole = strtoupper($role);
        if ($userRole !== $expectedRole) {
            // User doesn't have the required role - redirect to their appropriate dashboard
            if ($userRole === 'SUPERVISOR') {
                return redirect('/supervisor/dashboard');
            }
            if ($userRole === 'ADMIN') {
                return redirect('/admin/dashboard');
            }
            // Fallback for other roles
            return redirect('/login');
        }

        return $next($request);
    }
}