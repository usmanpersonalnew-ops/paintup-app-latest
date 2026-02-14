<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PaintUp',
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'invoice_prefix' => 'INV',
            ]
        );

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $settings = Setting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PaintUp',
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'invoice_prefix' => 'INV',
            ]
        );

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'support_whatsapp' => 'nullable|string|max:20',
            'support_email' => 'nullable|email|max:255',
            'footer_text' => 'nullable|string|max:500',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'invoice_prefix' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo upload
            if ($request->hasFile('logo_path')) {
                // Delete old logo if exists
                if ($settings->logo_path && Storage::exists('public/' . $settings->logo_path)) {
                    Storage::delete('public/' . $settings->logo_path);
                }

                $logo = $request->file('logo_path');
                $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('public/settings', $logoName);
                $validated['logo_path'] = 'settings/' . $logoName;
            }

            $settings->update($validated);
            DB::commit();

            return redirect()->route('admin.settings')->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
