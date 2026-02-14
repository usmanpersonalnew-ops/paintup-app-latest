<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $settings = Setting::getGstSettings();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'gst_rate' => 'required|numeric|min:0|max:100',
            'gst_number' => 'required|string|max:50',
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
        ]);

        // Update each setting
        Setting::set('gst_rate', $validated['gst_rate'], 'number', 'gst');
        Setting::set('gst_number', $validated['gst_number'], 'string', 'gst');
        Setting::set('company_name', $validated['company_name'], 'string', 'company');
        Setting::set('company_address', $validated['company_address'], 'string', 'company');
        Setting::set('company_phone', $validated['company_phone'], 'string', 'company');
        Setting::set('company_email', $validated['company_email'], 'email', 'company');

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
