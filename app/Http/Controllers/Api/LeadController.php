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
        // Log incoming request for debugging
        Log::info('Lead submission received', [
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'pincode' => 'nullable|string|max:10',
            'whatsapp_enabled' => 'nullable|boolean',
            'whatsapp_update' => 'nullable|string|in:Yes,No', // Legacy support
            'construction_ongoing' => 'nullable|string|in:Yes,No',
            'property_type' => 'nullable|string|max:100',
            'visit_date' => 'nullable|date',
        ]);

        // Handle whatsapp_enabled (boolean) or whatsapp_update (Yes/No string)
        $whatsappEnabled = false;
        if (isset($validated['whatsapp_enabled'])) {
            $whatsappEnabled = (bool) $validated['whatsapp_enabled'];
        } elseif (isset($validated['whatsapp_update'])) {
            $whatsappEnabled = strtolower($validated['whatsapp_update']) === 'yes';
        }

        // Create inquiry
        try {
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

            Log::info('Lead/inquiry created successfully', [
                'inquiry_id' => $inquiry->id,
                'name' => $inquiry->name,
                'phone' => $inquiry->phone
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create inquiry', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            throw $e;
        }

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
