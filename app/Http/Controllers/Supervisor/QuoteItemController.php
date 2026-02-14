<?php
namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ProjectRoom;
use App\Models\QuoteItem;
use App\Models\MasterSurface;
use App\Models\MasterPaintingSystem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuoteItemController extends Controller
{
    /**
     * Screen C: Add Paint Item - GET
     * Route: GET /supervisor/zones/{projectRoom}/paint
     */
    public function create(ProjectRoom $projectRoom)
    {
        // Load surfaces with their products and systems
        $surfaces = MasterSurface::with(['products' => function($q) {
            $q->with('systems');
        }])->get();

        // Harden: Add explicit has_products flag to each surface
        // This ensures frontend can deterministically handle surfaces with/without products
        $surfaces = $surfaces->map(function ($surface) {
            $hasProducts = $surface->products->isNotEmpty();
            
            return [
                'id' => $surface->id,
                'name' => $surface->name,
                'category' => $surface->category,
                'unit_type' => $surface->unit_type,
                'has_products' => $hasProducts,
                'products' => $surface->products->values(),
            ];
        });

        return Inertia::render('Supervisor/Paint/Create', [
            'zone' => $projectRoom,
            'surfaces' => $surfaces
        ]);
    }

    /**
     * Screen C: Add Paint Item - POST
     * Route: POST /supervisor/zones/{projectRoom}/paint
     */
    public function store(Request $request, ProjectRoom $projectRoom)
    {
        $validated = $request->validate([
            'surface_id' => 'required|exists:master_surfaces,id',
            'product_id' => 'required|exists:master_products,id',
            'system_id' => 'required|exists:master_painting_systems,id',
            'unit_type' => 'required|in:AREA,LINEAR,COUNT,LUMPSUM',
            'measurement_source' => 'required|in:ROOM_DEFAULT,MANUAL',
            'pricing_mode' => 'nullable|in:CALCULATED,LUMPSUM',
            'lumpsum_amount' => 'nullable|numeric|min:0',
            'manual_deduction_sqft' => 'nullable|numeric|min:0',
        ]);

        // Get the painting system for rate calculation
        $system = MasterPaintingSystem::findOrFail($request->system_id);

        // Calculate quantities based on measurement source
        $grossQty = $this->calculateGrossQty($request, $projectRoom);
        $deductions = $this->parseDeductions($request->deductions);
        
        // Add manual deduction to total
        $manualDeduction = $request->manual_deduction_sqft ?? 0;
        $netQty = $grossQty - $deductions['total_area'] - $manualDeduction;

        // Calculate amount based on pricing mode
        $pricingMode = $request->pricing_mode ?? ($request->unit_type === 'LUMPSUM' ? 'LUMPSUM' : 'CALCULATED');
        $amount = $pricingMode === 'LUMPSUM'
            ? ($request->lumpsum_amount ?? 0)
            : ($netQty * $system->base_rate);

        QuoteItem::create([
            'project_room_id' => $projectRoom->id,
            'surface_id' => $request->surface_id,
            'master_product_id' => $request->product_id,
            'master_system_id' => $request->system_id,
            'qty' => $netQty,
            'rate' => $system->base_rate,
            'amount' => $amount,
            'system_rate' => $system->base_rate,
            'measurement_mode' => $request->measurement_source,
            'pricing_mode' => $pricingMode,
            'deductions' => $deductions['json'],
            'color_code' => $request->color ?? null,
            'description' => $request->description ?? null,
            'manual_price' => $pricingMode === 'LUMPSUM' ? ($request->lumpsum_amount ?? 0) : 0,
            'gross_qty' => $grossQty,
            'net_qty' => $netQty,
            // Manual measurement fields
            'manual_length' => $request->measurement_source === 'MANUAL' ? ($request->length ?? 0) : 0,
            'manual_breadth' => $request->measurement_source === 'MANUAL' ? ($request->breadth ?? 0) : 0,
            'manual_height' => $request->measurement_source === 'MANUAL' ? ($request->height ?? 0) : 0,
            'manual_area' => $request->measurement_source === 'MANUAL' ? ($request->direct_area ?? 0) : 0,
        ]);

        return redirect()->route('supervisor.zones.show', $projectRoom);
    }

    /**
     * Screen C: Edit Paint Item - GET
     * Route: GET /supervisor/zones/{projectRoom}/paint/{item}/edit
     */
    public function edit(ProjectRoom $projectRoom, QuoteItem $item)
    {
        // Load surfaces with their products and systems
        $surfaces = MasterSurface::with(['products' => function($q) {
            $q->with('systems');
        }])->get();

        // Harden: Add explicit has_products flag to each surface
        $surfaces = $surfaces->map(function ($surface) {
            $hasProducts = $surface->products->isNotEmpty();
            
            return [
                'id' => $surface->id,
                'name' => $surface->name,
                'category' => $surface->category,
                'unit_type' => $surface->unit_type,
                'has_products' => $hasProducts,
                'products' => $surface->products->values(),
            ];
        });

        // Load item relationships
        $item->load(['surface', 'product', 'system']);

        return Inertia::render('Supervisor/Paint/Create', [
            'zone' => $projectRoom,
            'surfaces' => $surfaces,
            'editItem' => $item,
        ]);
    }

    /**
     * Screen C: Update Paint Item - PUT
     * Route: PUT /supervisor/zones/{projectRoom}/paint/{item}
     */
    public function update(Request $request, ProjectRoom $projectRoom, QuoteItem $item)
    {
        $validated = $request->validate([
            'surface_id' => 'required|exists:master_surfaces,id',
            'product_id' => 'required|exists:master_products,id',
            'system_id' => 'required|exists:master_painting_systems,id',
            'unit_type' => 'required|in:AREA,LINEAR,COUNT,LUMPSUM',
            'measurement_source' => 'required|in:ROOM_DEFAULT,MANUAL',
            'pricing_mode' => 'nullable|in:CALCULATED,LUMPSUM',
            'lumpsum_amount' => 'nullable|numeric|min:0',
            'manual_deduction_sqft' => 'nullable|numeric|min:0',
        ]);

        // Get the painting system for rate calculation
        $system = MasterPaintingSystem::findOrFail($request->system_id);

        // Calculate quantities based on measurement source
        $grossQty = $this->calculateGrossQty($request, $projectRoom);
        $deductions = $this->parseDeductions($request->deductions);
        
        // Add manual deduction to total
        $manualDeduction = $request->manual_deduction_sqft ?? 0;
        $netQty = $grossQty - $deductions['total_area'] - $manualDeduction;

        // Calculate amount based on pricing mode
        $pricingMode = $request->pricing_mode ?? ($request->unit_type === 'LUMPSUM' ? 'LUMPSUM' : 'CALCULATED');
        $amount = $pricingMode === 'LUMPSUM'
            ? ($request->lumpsum_amount ?? 0)
            : ($netQty * $system->base_rate);

        // Update the item
        $item->update([
            'surface_id' => $request->surface_id,
            'master_product_id' => $request->product_id,
            'master_system_id' => $request->system_id,
            'qty' => $netQty,
            'rate' => $system->base_rate,
            'amount' => $amount,
            'system_rate' => $system->base_rate,
            'measurement_mode' => $request->measurement_source,
            'pricing_mode' => $pricingMode,
            'deductions' => $deductions['json'],
            'color_code' => $request->color ?? null,
            'description' => $request->description ?? null,
            'manual_price' => $pricingMode === 'LUMPSUM' ? ($request->lumpsum_amount ?? 0) : 0,
            'gross_qty' => $grossQty,
            'net_qty' => $netQty,
            'manual_length' => $request->measurement_source === 'MANUAL' ? ($request->length ?? 0) : 0,
            'manual_breadth' => $request->measurement_source === 'MANUAL' ? ($request->breadth ?? 0) : 0,
            'manual_height' => $request->measurement_source === 'MANUAL' ? ($request->height ?? 0) : 0,
            'manual_area' => $request->measurement_source === 'MANUAL' ? ($request->direct_area ?? 0) : 0,
        ]);

        return redirect()->route('supervisor.zones.show', $projectRoom)->with('success', 'Paint item updated successfully.');
    }

    /**
     * Get painting systems for a product
     * Route: GET /supervisor/products/{product}/systems
     */
    public function getSystems(\App\Models\MasterProduct $product)
    {
        return response()->json($product->systems);
    }

    /**
     * Calculate gross quantity based on unit_type and measurement source
     */
    private function calculateGrossQty(Request $request, ProjectRoom $projectRoom): float
    {
        $unitType = $request->unit_type;
        $measurementSource = $request->measurement_source;

        // Use room default dimensions if ROOM_DEFAULT
        if ($measurementSource === 'ROOM_DEFAULT') {
            return match($unitType) {
                'AREA' => $this->calculateRoomArea($projectRoom),
                'LINEAR' => $projectRoom->length ?? 0,
                'COUNT' => 1,
                'LUMPSUM' => 1,
                default => 0,
            };
        }

        // Manual measurements
        return match($unitType) {
            'AREA' => $request->manual_area_mode === 'DIRECT'
                ? ($request->direct_area ?? 0)
                : (($request->length ?? 0) * ($request->height ?? 0)),
            'LINEAR' => $request->length ?? 0,
            'COUNT' => $request->quantity ?? 1,
            'LUMPSUM' => 1,
            default => 0,
        };
    }

    /**
     * Calculate room area from zone dimensions (length × width × height for walls)
     * For walls: typically 4 walls, but we use simple L x H for now
     */
    private function calculateRoomArea(ProjectRoom $projectRoom): float
    {
        $length = $projectRoom->length ?? 0;
        $width = $projectRoom->breadth ?? 0;
        $height = $projectRoom->height ?? 0;

        // Default: calculate wall area (perimeter × height)
        // Perimeter = 2 × (L + W)
        if ($length > 0 && $width > 0 && $height > 0) {
            return 2 * ($length + $width) * $height;
        }

        // Fallback: use length × height
        if ($length > 0 && $height > 0) {
            return $length * $height;
        }

        return 0;
    }

    /**
     * Parse deductions from request
     */
    private function parseDeductions(mixed $deductions): array
    {
        if (empty($deductions)) {
            return ['total_area' => 0, 'json' => null];
        }

        // If already an array
        if (is_array($deductions)) {
            $totalArea = collect($deductions)->sum('area');
            return [
                'total_area' => $totalArea,
                'json' => json_encode($deductions)
            ];
        }

        // If JSON string
        if (is_string($deductions)) {
            $decoded = json_decode($deductions, true);
            $totalArea = collect($decoded)->sum('area');
            return [
                'total_area' => $totalArea,
                'json' => $deductions
            ];
        }

        return ['total_area' => 0, 'json' => null];
    }
}
