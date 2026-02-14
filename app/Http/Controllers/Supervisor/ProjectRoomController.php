<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectRoom;
use App\Models\QuoteItem;
use App\Models\QuoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectRoomController extends Controller
{
    public function index(Project $project)
    {
        // Load the rooms relationship so the View can see them
        return Inertia::render('Supervisor/Projects/Show', [
            'project' => $project->load('rooms')
        ]);
    }

    // Screen A: Create Zone Page
    public function create(Project $project)
    {
        return Inertia::render('Supervisor/Zones/Create', [
            'project' => $project
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:INTERIOR,EXTERIOR',
            'length' => 'nullable|numeric',
            'breadth' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);
        
        ProjectRoom::create([
            'project_id' => $projectId,
            'name' => $request->name,
            'type' => $request->type ?? 'INTERIOR',
            'length' => $request->length,
            'breadth' => $request->breadth,
            'height' => $request->height,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, ProjectRoom $projectRoom)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'nullable|in:INTERIOR,EXTERIOR',
            'length' => 'nullable|numeric',
            'breadth' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        $projectRoom->update($request->all());

        return redirect()->back();
    }

    /**
     * Duplicate a zone with its paint items and services.
     */
    public function duplicate(Request $request, ProjectRoom $projectRoom)
    {
        // Load relationships
        $projectRoom->load(['items', 'services']);
        
        // Create new room with copied name
        $newRoom = ProjectRoom::create([
            'project_id' => $projectRoom->project_id,
            'name' => $projectRoom->name . ' (Copy)',
            'type' => $projectRoom->type,
            'length' => $projectRoom->length,
            'breadth' => $projectRoom->breadth,
            'height' => $projectRoom->height,
        ]);

        // Copy paint items
        foreach ($projectRoom->items as $item) {
            QuoteItem::create([
                'project_room_id' => $newRoom->id,
                'surface_id' => $item->surface_id,
                'master_product_id' => $item->master_product_id,
                'master_system_id' => $item->master_system_id,
                'qty' => $item->qty,
                'rate' => $item->rate,
                'amount' => $item->amount,
                'system_rate' => $item->system_rate,
                'measurement_mode' => $item->measurement_mode,
                'pricing_mode' => $item->pricing_mode,
                'deductions' => $item->deductions,
                'color_code' => $item->color_code,
                'description' => $item->description,
                'manual_price' => $item->manual_price,
                'gross_qty' => $item->gross_qty,
                'net_qty' => $item->net_qty,
                'manual_length' => $item->manual_length,
                'manual_breadth' => $item->manual_breadth,
                'manual_height' => $item->manual_height,
                'manual_area' => $item->manual_area,
            ]);
        }

        // Copy services
        foreach ($projectRoom->services as $service) {
            QuoteService::create([
                'project_room_id' => $newRoom->id,
                'master_service_id' => $service->master_service_id,
                'custom_name' => $service->custom_name,
                'unit_type' => $service->unit_type,
                'quantity' => $service->quantity,
                'length' => $service->length,
                'breadth' => $service->breadth,
                'count' => $service->count,
                'rate' => $service->rate,
                'amount' => $service->amount,
                'photo_url' => $service->photo_url,
            ]);
        }

        return redirect()->route('supervisor.zones.show', $newRoom->id)->with('success', 'Zone duplicated successfully.');
    }
}
