<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerOtp;
use App\Services\Msg91WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    protected Msg91WhatsappService $msg91Service;

    public function __construct(Msg91WhatsappService $msg91Service)
    {
        $this->msg91Service = $msg91Service;
    }

    /**
     * Show OTP login form
     */
    public function showLogin(Request $request)
    {
        // If already logged in as customer, redirect to dashboard
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return Inertia::render('Customer/Login', [
            'phone' => $request->phone ?? '',
            'redirect_to' => $request->redirect_to ?? null,
        ]);
    }

    /**
     * Send OTP via MSG91 WhatsApp
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $phone = $this->formatPhoneNumber($request->phone);
        
        // Validate phone format (10 digit Indian number)
        if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 10-digit mobile number',
            ], 422);
        }

        // Clean up expired OTPs for this phone
        CustomerOtp::cleanExpired();

        // Check if a valid (not expired) OTP already exists for this phone
        $existingOtp = CustomerOtp::findValidOtp($phone);

        if ($existingOtp) {
            // Reuse the existing OTP - we need to verify it to get the actual OTP value
            // Since we store hash, we can't retrieve the OTP directly
            // Generate a new OTP in this case (or we could store OTP temporarily in session)
            // For simplicity, generate new OTP but log it
            $otp = CustomerOtp::generateOtp();
            Log::info('OTP regenerated for existing valid OTP', ['phone' => $phone]);
        } else {
            // Generate new 6-digit OTP
            $otp = CustomerOtp::generateOtp();
        }

        // Store OTP as hash in database
        CustomerOtp::create([
            'phone' => $phone,
            'otp_hash' => CustomerOtp::hashOtp($otp),
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send OTP via MSG91 WhatsApp
        Log::info('CUSTOMER AUTH: About to send OTP', ['phone' => $phone]);
        $sent = $this->msg91Service->sendOtp($phone, $otp);
        Log::info('CUSTOMER AUTH: OTP send result', ['sent' => $sent]);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }

        // NEVER return OTP in API response - for security
        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your WhatsApp',
        ]);
    }

    /**
     * Verify OTP and login/register customer
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'otp' => 'required|string|size:6',
        ]);

        $phone = $this->formatPhoneNumber($request->phone);
        $otp = $request->otp;

        // Find valid OTP record
        $otpRecord = CustomerOtp::findValidOtp($phone);

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP. Please request a new OTP.',
            ], 422);
        }

        // Verify OTP using hash comparison
        if (!$otpRecord->matches($otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }

        // Find or create customer (no quote check required - any phone can login)
        $customer = Customer::where('phone', $phone)->first();

        if (!$customer) {
            $customer = Customer::create([
                'name' => null,
                'phone' => $phone,
                'whatsapp_verified_at' => now(),
            ]);
        } else {
            // Update verification timestamp
            $customer->update(['whatsapp_verified_at' => now()]);
        }

        // Delete used OTP
        $otpRecord->delete();

        // Login the customer
        Auth::guard('customer')->login($customer);

        // Regenerate session
        $request->session()->regenerate();

        // Always redirect to dashboard after login
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect_to' => route('customer.dashboard'),
        ]);
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        // Check if customer is logged in
        if (!Auth::guard('customer')->check()) {
            return Inertia::location(route('customer.login'));
        }

        // Logout the customer
        Auth::guard('customer')->logout();
        
        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('customer.login'));
    }

    /**
     * Format phone number to 10 digit format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 91, remove it
        if (strlen($phone) === 12 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }

        return $phone;
    }
}