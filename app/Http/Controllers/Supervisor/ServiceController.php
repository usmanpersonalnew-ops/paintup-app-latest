<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\MasterService;
use App\Models\QuoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;


class ServiceController extends Controller
{
    /**
     * Show the form for creating a new service (Screen D).
     */
    public function create(\App\Models\ProjectRoom $projectRoom)
    {
        $projectRoom->load(['services']);
        $services = \App\Models\MasterService::all();
        
        return Inertia::render('Supervisor/Zones/Service', [
            'zone' => $projectRoom,
            'services' => $services,
        ]);
    }

    public function store(Request $request, \App\Models\ProjectRoom $projectRoom)
    {
        \Log::info('Service Store Request:', $request->all());

        $validated = $request->validate([
            'master_service_id' => 'nullable|exists:master_services,id',
            'custom_name' => 'nullable|string|max:255',
            'unit_type' => 'required|string|in:AREA,LINEAR,COUNT,LUMPSUM',
            'qty' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'breadth' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:1',
            'rate' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
        ]);

        \Log::info('Validation passed:', $validated);

        // If master_service_id is provided, get the service name from master_services
        $customName = $validated['custom_name'] ?? null;
        if (empty($customName) && !empty($validated['master_service_id'])) {
            $masterService = MasterService::find($validated['master_service_id']);
            if ($masterService) {
                $customName = $masterService->name;
            }
        }

        // Create quote service (photo_url removed - photos now uploaded via Project → Photos)
        $quoteService = QuoteService::create([
            'project_room_id' => $projectRoom->id,
            'master_service_id' => $validated['master_service_id'],
            'custom_name' => $customName,
            'unit_type' => $validated['unit_type'],
            'quantity' => $validated['qty'],
            'length' => $validated['length'] ?? null,
            'breadth' => $validated['breadth'] ?? null,
            'count' => $validated['count'] ?? null,
            'rate' => $validated['rate'],
            'amount' => $validated['amount'],
            'remarks' => $validated['remarks'] ?? null,
            'photo_url' => null, // Photos now uploaded via Project → Photos
        ]);

        \Log::info('QuoteService created:', ['id' => $quoteService->id]);

        return redirect()->route('supervisor.zones.show', $projectRoom)->with('success', 'Service added successfully.');
    }

    /**
     * Store service with FormData (for Zones/Show.vue modal)
     * Route: POST /zones/{projectRoom}/services
     */
    public function storeFormData(Request $request, \App\Models\ProjectRoom $projectRoom)
    {
        \Log::info('Service StoreFormData Request:', $request->all());

        $validated = $request->validate([
            'master_service_id' => 'nullable|exists:master_services,id',
            'custom_name' => 'nullable|string|max:255',
            'unit_type' => 'required|string|in:AREA,LINEAR,COUNT,LUMPSUM',
            'quantity' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
        ]);

        \Log::info('StoreFormData Validation passed:', $validated);

        // If master_service_id is provided, get the service name from master_services
        $customName = $validated['custom_name'] ?? null;
        if (empty($customName) && !empty($validated['master_service_id'])) {
            $masterService = MasterService::find($validated['master_service_id']);
            if ($masterService) {
                $customName = $masterService->name;
            }
        }

        // Calculate amount
        $amount = $validated['quantity'] * $validated['rate'];

        // Create quote service
        $quoteService = QuoteService::create([
            'project_room_id' => $projectRoom->id,
            'master_service_id' => $validated['master_service_id'],
            'custom_name' => $customName,
            'unit_type' => $validated['unit_type'],
            'quantity' => $validated['quantity'],
            'rate' => $validated['rate'],
            'amount' => $amount,
            'remarks' => $validated['remarks'] ?? null,
            'photo_url' => null, // Photos now uploaded via Project → Photos
        ]);

        \Log::info('QuoteService created via StoreFormData:', ['id' => $quoteService->id]);

        return response()->json(['success' => true, 'message' => 'Service added successfully.']);
    }

    /**
     * Show the form for editing a service.
     */
    public function edit(\App\Models\ProjectRoom $projectRoom, QuoteService $quoteService)
    {
        $projectRoom->load(['services']);
        $services = \App\Models\MasterService::all();
        $quoteService->load(['masterService']);
        
        return Inertia::render('Supervisor/Zones/Service', [
            'zone' => $projectRoom,
            'services' => $services,
            'editService' => $quoteService,
        ]);
    }

    /**
     * Update a service.
     */
    public function update(Request $request, \App\Models\ProjectRoom $projectRoom, QuoteService $quoteService)
    {
        $validated = $request->validate([
            'master_service_id' => 'nullable|exists:master_services,id',
            'custom_name' => 'nullable|string|max:255',
            'unit_type' => 'required|string|in:AREA,LINEAR,COUNT,LUMPSUM',
            'qty' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'breadth' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:1',
            'rate' => 'required|numeric|min:0',
        ]);

        // Update the service (photo_url removed - photos now uploaded via Project → Photos)
        $quoteService->update([
            'master_service_id' => $validated['master_service_id'],
            'custom_name' => $validated['custom_name'] ?? null,
            'unit_type' => $validated['unit_type'],
            'quantity' => $validated['qty'],
            'length' => $validated['length'] ?? null,
            'breadth' => $validated['breadth'] ?? null,
            'count' => $validated['count'] ?? null,
            'rate' => $validated['rate'],
            'amount' => $validated['amount'],
            'photo_url' => null, // Photos now uploaded via Project → Photos
        ]);

        return redirect()->route('supervisor.zones.show', $projectRoom)->with('success', 'Service updated successfully.');
    }
}
