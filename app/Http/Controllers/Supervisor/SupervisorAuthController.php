<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SupervisorAuthController extends Controller
{
    /**
     * Show the supervisor login form.
     */
    public function create()
    {
        return Inertia::render('Auth/SupervisorLogin', [
            'canResetPassword' => false,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(SupervisorLoginRequest $request)
    {
        $this->authenticate($request);

        $request->session()->regenerate();

        return redirect()->intended('/supervisor/projects');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(\Illuminate\Http\Request $request)
    {
        Auth::guard('supervisor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/supervisor/login');
    }

    /**
     * Attempt to authenticate the request's credentials.
     */
    protected function authenticate(FormRequest $request): void
    {
        $this->ensureIsNotRateLimited();

        // Use supervisor guard specifically
        if (! Auth::guard('supervisor')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(FormRequest $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(FormRequest $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}

class SupervisorLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}