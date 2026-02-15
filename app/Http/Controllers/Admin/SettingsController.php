<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Generate logo URL if logo exists - use full URL like middleware does
        $settingsArray = $settings->toArray();
        if ($settings->logo_path) {
            // Generate full URL using request's scheme and host
            $settingsArray['logo_url'] = request()->getSchemeAndHttpHost() . Storage::url($settings->logo_path);
        } else {
            $settingsArray['logo_url'] = null;
        }

        // Generate signature URL if signature exists
        if ($settings->signature_path) {
            $settingsArray['signature_url'] = request()->getSchemeAndHttpHost() . Storage::url($settings->signature_path);
        } else {
            $settingsArray['signature_url'] = null;
        }

        // Debug: Log what we're passing
        Log::info('Settings Controller - Passing to Inertia:', [
            'logo_path' => $settingsArray['logo_path'] ?? 'null',
            'logo_url' => $settingsArray['logo_url'] ?? 'null',
            'signature_path' => $settingsArray['signature_path'] ?? 'null',
            'signature_url' => $settingsArray['signature_url'] ?? 'null',
            'all_settings' => $settingsArray
        ]);

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settingsArray,
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
            'signature_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            // Handle logo upload - only if a new file is uploaded
            if ($request->hasFile('logo_path')) {
                // Delete old logo if exists
                if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                    Storage::disk('public')->delete($settings->logo_path);
                }

                $logo = $request->file('logo_path');
                $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();

                // Store the file using public disk
                $path = $logo->storeAs('settings', $logoName, 'public');

                // Verify file was stored
                $fullPath = storage_path('app/public/' . $path);
                if (!file_exists($fullPath)) {
                    throw new \Exception('Failed to store logo file. Path: ' . $fullPath);
                }

                // Set proper permissions (readable by web server)
                @chmod($fullPath, 0644);

                $validated['logo_path'] = $path; // Use the path returned by storeAs

                Log::info('Logo uploaded successfully:', [
                    'path' => $path,
                    'logo_path' => $validated['logo_path'],
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath),
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                    'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A'
                ]);
            } else {
                // If no new logo uploaded, preserve the existing logo_path
                // Remove logo_path from validated so it doesn't get overwritten
                unset($validated['logo_path']);
                Log::info('No new logo uploaded, preserving existing logo_path: ' . ($settings->logo_path ?? 'NULL'));
            }

            // Handle signature upload - only if a new file is uploaded
            if ($request->hasFile('signature_path')) {
                // Delete old signature if exists
                if ($settings->signature_path && Storage::disk('public')->exists($settings->signature_path)) {
                    Storage::disk('public')->delete($settings->signature_path);
                }

                $signature = $request->file('signature_path');
                $signatureName = 'signature-' . time() . '.' . $signature->getClientOriginalExtension();

                // Store the file using public disk
                $path = $signature->storeAs('settings', $signatureName, 'public');

                // Verify file was stored
                $fullPath = storage_path('app/public/' . $path);
                if (!file_exists($fullPath)) {
                    throw new \Exception('Failed to store signature file. Path: ' . $fullPath);
                }

                // Set proper permissions (readable by web server)
                @chmod($fullPath, 0644);

                $validated['signature_path'] = $path; // Use the path returned by storeAs

                Log::info('Signature uploaded successfully:', [
                    'path' => $path,
                    'signature_path' => $validated['signature_path'],
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath),
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                    'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A'
                ]);
            } else {
                // If no new signature uploaded, preserve the existing signature_path
                // Remove signature_path from validated so it doesn't get overwritten
                unset($validated['signature_path']);
                Log::info('No new signature uploaded, preserving existing signature_path: ' . ($settings->signature_path ?? 'NULL'));
            }

            $settings->update($validated);
            DB::commit();

            // Refresh settings to get updated logo_path
            $settings->refresh();

            // Log the updated settings for debugging
            Log::info('Settings updated:', [
                'logo_path' => $settings->logo_path,
                'all_data' => $settings->toArray()
            ]);

            // Generate logo URL for response - use full URL
            $settingsArray = $settings->toArray();
            if ($settings->logo_path) {
                // Generate full URL using request's scheme and host
                $settingsArray['logo_url'] = $request->getSchemeAndHttpHost() . Storage::url($settings->logo_path);
            } else {
                $settingsArray['logo_url'] = null;
            }

            // Generate signature URL for response
            if ($settings->signature_path) {
                $settingsArray['signature_url'] = $request->getSchemeAndHttpHost() . Storage::url($settings->signature_path);
            } else {
                $settingsArray['signature_url'] = null;
            }

            // For Inertia, we need to redirect and pass data through Inertia's share
            // Use Inertia redirect to pass the updated settings
            return Inertia::render('Admin/Settings/Index', [
                'settings' => $settingsArray,
            ])->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
