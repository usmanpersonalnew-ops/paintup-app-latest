<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();

        return Inertia::render('Supervisor/Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        return Inertia::render('Supervisor/Projects/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        $userId = auth()->id();

        Project::create([
            'client_name' => $request->client_name,
            'location' => $request->location,
            'phone' => $request->phone,
            'status' => 'NEW',
            'supervisor_id' => $userId,
        ]);

        return redirect()->route('supervisor.projects.index');
    }

    public function show($id)
    {
        $project = Project::with(['rooms.items', 'rooms.services'])->findOrFail($id);

        // Calculate totals for each room
        $project->rooms->each(function ($room) {
            $room->items_count = $room->items->count();
            $room->services_count = $room->services->count();
            $room->total_items_count = $room->items_count + $room->services_count;
            $room->total_amount = $room->items->sum('amount') + $room->services->sum('amount');
        });

        return Inertia::render('Supervisor/Projects/Show', [
            'project' => $project
        ]);
    }
}
