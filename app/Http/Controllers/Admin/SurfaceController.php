<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSurface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurfaceController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Surfaces/Index', [
            'surfaces' => MasterSurface::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Surfaces/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit_type' => 'required|string',
            'remarks' => 'nullable|string|max:1000',
        ]);

        MasterSurface::create($request->all());
        return redirect()->route('admin.surfaces.index');
    }

    public function edit($id)
    {
        $surface = MasterSurface::findOrFail($id);
        return Inertia::render('Admin/Surfaces/Edit', [
            'surface' => $surface
        ]);
    }

    public function update(Request $request, $id)
    {
        $surface = MasterSurface::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit_type' => 'required|string',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $surface->update($request->all());
        return redirect()->route('admin.surfaces.index');
    }
}
