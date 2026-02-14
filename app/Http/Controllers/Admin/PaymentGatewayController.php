<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = [
            'phonepe' => [
                'name' => 'PhonePe',
                'enabled' => env('PHONEPE_ENABLED'),
                'merchant_id' => env('PHONEPE_MERCHANT_ID') ? '****' . substr(env('PHONEPE_MERCHANT_ID'), -4) : null,
                'environment' => env('PHONEPE_ENVIRONMENT', 'sandbox'),
            ],
            'ccavenue' => [
                'name' => 'CCAvenue',
                'enabled' => env('CCAUTH_ENABLED'),
                'merchant_id' => env('CCAUTH_MERCHANT_ID') ? '****' . substr(env('CCAUTH_MERCHANT_ID'), -4) : null,
                'environment' => env('CCAUTH_ENVIRONMENT', 'sandbox'),
            ],
        ];

        return inertia('Admin/PaymentGateways/Index', [
            'gateways' => $gateways,
            'currentGateway' => config('paymentgateways.default'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gateway' => 'required|in:phonepe,ccavenue',
            'phonepe_enabled' => 'nullable|boolean',
            'phonepe_merchant_id' => 'nullable|string',
            'phonepe_salt_key' => 'nullable|string',
            'phonepe_salt_index' => 'nullable|integer|min:1',
            'phonepe_environment' => 'nullable|in:sandbox,production',
            'ccavenue_enabled' => 'nullable|boolean',
            'ccavenue_merchant_id' => 'nullable|string',
            'ccavenue_working_key' => 'nullable|string',
            'ccavenue_access_code' => 'nullable|string',
            'ccavenue_environment' => 'nullable|in:sandbox,production',
        ]);

        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        $envContent = preg_replace('/PAYMENT_GATEWAY=.*/', 'PAYMENT_GATEWAY=' . $validated['gateway'], $envContent);

        $envContent = preg_replace('/PHONEPE_ENABLED=.*/', 'PHONEPE_ENABLED=' . ($validated['phonepe_enabled'] ? 'true' : 'false'), $envContent);
        if (!empty($validated['phonepe_merchant_id'])) {
            $envContent = preg_replace('/PHONEPE_MERCHANT_ID=.*/', 'PHONEPE_MERCHANT_ID=' . $validated['phonepe_merchant_id'], $envContent);
        }
        if (!empty($validated['phonepe_salt_key'])) {
            $envContent = preg_replace('/PHONEPE_SALT_KEY=.*/', 'PHONEPE_SALT_KEY=' . $validated['phonepe_salt_key'], $envContent);
        }
        $envContent = preg_replace('/PHONEPE_SALT_INDEX=.*/', 'PHONEPE_SALT_INDEX=' . ($validated['phonepe_salt_index'] ?? 1), $envContent);
        $envContent = preg_replace('/PHONEPE_ENVIRONMENT=.*/', 'PHONEPE_ENVIRONMENT=' . ($validated['phonepe_environment'] ?? 'sandbox'), $envContent);

        $envContent = preg_replace('/CCAUTH_ENABLED=.*/', 'CCAUTH_ENABLED=' . ($validated['ccavenue_enabled'] ? 'true' : 'false'), $envContent);
        if (!empty($validated['ccavenue_merchant_id'])) {
            $envContent = preg_replace('/CCAUTH_MERCHANT_ID=.*/', 'CCAUTH_MERCHANT_ID=' . $validated['ccavenue_merchant_id'], $envContent);
        }
        if (!empty($validated['ccavenue_working_key'])) {
            $envContent = preg_replace('/CCAUTH_WORKING_KEY=.*/', 'CCAUTH_WORKING_KEY=' . $validated['ccavenue_working_key'], $envContent);
        }
        if (!empty($validated['ccavenue_access_code'])) {
            $envContent = preg_replace('/CCAUTH_ACCESS_CODE=.*/', 'CCAUTH_ACCESS_CODE=' . $validated['ccavenue_access_code'], $envContent);
        }
        $envContent = preg_replace('/CCAUTH_ENVIRONMENT=.*/', 'CCAUTH_ENVIRONMENT=' . ($validated['ccavenue_environment'] ?? 'sandbox'), $envContent);

        File::put($envPath, $envContent);

        Artisan::call('config:clear');

        return redirect()->back()->with('success', 'Payment gateway settings updated successfully');
    }
}