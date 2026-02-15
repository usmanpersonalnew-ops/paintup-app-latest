<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Quote;
use App\Services\Msg91WhatsappService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class SummaryController extends Controller
{
    /**
     * Show the quote summary (Screen E).
     */
    public function show(Project $project)
    {
        // Load zones (which are ProjectRoom objects) with items and services
        $project->load(['zones.items', 'zones.services', 'zones.media', 'coupon', 'quote']);

        $paintTotal = $project->zones->sum(function ($zone) {
            return $zone->items->sum('amount');
        });

        $serviceTotal = $project->zones->sum(function ($zone) {
            return $zone->services->sum('amount');
        });

        $subtotal = $paintTotal + $serviceTotal;

        return inertia('Supervisor/Projects/Summary', [
            'project' => $project,
            'paintTotal' => $paintTotal,
            'serviceTotal' => $serviceTotal,
            'subtotal' => $subtotal,
            'initialNotes' => $project->quote->notes ?? '',
        ]);
    }

    /**
     * Save notes without finalizing (just update notes field).
     */
    public function saveNotes(Request $request, Project $project)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        // Fix null quote bug - create quote if it doesn't exist
        if (!$project->quote) {
            $quote = Quote::create([
                'project_id' => $project->id,
                'status' => 'DRAFT',
            ]);
            $project->refresh();
        }

        // Update only notes
        $project->quote->update([
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Notes saved successfully!');
    }

    /**
     * Finalize the quote and generate PDF.
     */
    public function finalize(Request $request, Project $project)
    {
        $validated = $request->validate([
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $project->load(['zones.items', 'zones.services']);

        $paintTotal = $project->zones->sum(function ($zone) {
            return $zone->items->sum('amount');
        });

        $serviceTotal = $project->zones->sum(function ($zone) {
            return $zone->services->sum('amount');
        });

        $subtotal = $paintTotal + $serviceTotal;
        $discount = $validated['discount_amount'] ?? 0;
        $taxPercent = $validated['tax_percent'] ?? 18;

        $taxableAmount = $subtotal - $discount;
        $taxAmount = $taxableAmount * ($taxPercent / 100);
        $grandTotal = $taxableAmount + $taxAmount;

        // Fix null quote bug - create quote if it doesn't exist
        if (!$project->quote) {
            $quote = Quote::create([
                'project_id' => $project->id,
                'status' => 'DRAFT',
            ]);
            $project->refresh();
        }

        // Update quote with totals
        $project->quote->update([
            'discount_amount' => $discount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'notes' => $validated['notes'] ?? null,
            'status' => 'FINALIZED',
        ]);

        // Generate PDF
        return $this->generatePdf($project);
    }

    /**
     * Generate PDF quote.
     */
    public function generatePdf(Project $project)
    {
        $project->load(['zones.items', 'zones.services.masterService', 'zones.media', 'quote', 'coupon']);

        $paintTotal = $project->zones->sum(function ($zone) {
            return $zone->items->sum('amount');
        });

        $serviceTotal = $project->zones->sum(function ($zone) {
            // Ensure custom_name is populated from masterService for display
            foreach ($zone->services as $service) {
                if (empty($service->custom_name) && $service->masterService) {
                    $service->custom_name = $service->masterService->name;
                }
            }
            return $zone->services->sum('amount');
        });

        $subtotal = $paintTotal + $serviceTotal;

        // Use coupon discount if applied, otherwise use quote discount
        $discount = $project->discount_amount ?? $project->quote->discount_amount ?? 0;

        $taxPercent = $project->quote->tax_percent ?? 18;
        $taxAmount = $project->quote->tax_amount ?? 0;
        $grandTotal = $project->quote->grand_total ?? $subtotal;
        $notes = $project->quote->notes ?? '';

        $pdf = Pdf::loadView('pdf.quote', compact('project', 'paintTotal', 'serviceTotal', 'subtotal', 'discount', 'taxPercent', 'taxAmount', 'grandTotal', 'notes'));

        return $pdf->download('quote-' . $project->id . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Send WhatsApp message to customer with login link using MSG91 template.
     */
    public function sendWhatsAppMessage(Project $project)
    {
        \Log::info('sendWhatsAppMessage called for project', ['project_id' => $project->id]);

        $customerName = $project->client_name;
        $customerPhone = $project->phone;

        // Use customer name from customer table, or project client_name, or fallback

        if (!$customerPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Customer phone number not found.'
            ], 422);
        }

        $msg91Service = new Msg91WhatsappService();

        $sent = $msg91Service->sendQuoteSharedMessage(
            $customerPhone,
            $customerName
        );

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent to customer.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send WhatsApp message.'
        ], 500);
    }
}
