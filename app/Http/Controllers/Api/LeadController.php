<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Services\Msg91WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Store a new lead/inquiry from the website.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'pincode' => 'nullable|string|max:10',
            'whatsapp_update' => 'nullable|string|in:Yes,No',
            'construction_ongoing' => 'nullable|string|in:Yes,No',
            'property_type' => 'nullable|string|max:100',
            'visit_date' => 'nullable|date',
        ]);

        // Convert whatsapp_update "Yes"/"No" to boolean
        $whatsappEnabled = strtolower($validated['whatsapp_update'] ?? 'No') === 'yes';

        // Create inquiry
        $inquiry = Inquiry::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'whatsapp_enabled' => $whatsappEnabled,
            'construction_ongoing' => $validated['construction_ongoing'] ?? null,
            'property_type' => $validated['property_type'] ?? null,
            'visit_date' => $validated['visit_date'] ?? null,
            'source' => 'Website',
            'status' => Inquiry::STATUS_NEW,
        ]);

        // Send WhatsApp message if enabled
        if ($whatsappEnabled) {
            try {
                $msg91Service = new Msg91WhatsappService();
                $sent = $msg91Service->sendServiceIntro(
                    $validated['phone'],
                    $validated['name']
                );

                if ($sent) {
                    Log::info('WhatsApp service intro sent successfully', [
                        'inquiry_id' => $inquiry->id,
                        'phone' => $validated['phone']
                    ]);
                } else {
                    Log::warning('Failed to send WhatsApp service intro', [
                        'inquiry_id' => $inquiry->id,
                        'phone' => $validated['phone']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error sending WhatsApp service intro', [
                    'inquiry_id' => $inquiry->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the request if WhatsApp fails
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lead submitted successfully'
        ], 201);
    }
}
