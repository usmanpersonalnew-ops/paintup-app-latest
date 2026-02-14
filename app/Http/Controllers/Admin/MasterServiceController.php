<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterServiceController extends Controller
{
    public function index()
    {
        $services = MasterService::all();

        return Inertia::render('Admin/Services/Index', [
            'services' => $services
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_type' => 'required|string|in:AREA,LINEAR,COUNT,LUMPSUM',
            'default_rate' => 'required|numeric|min:0',
            'is_repair' => 'boolean',
            'remarks' => 'nullable|string|max:1000'
        ]);

        MasterService::create([
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'default_rate' => $request->default_rate,
            'is_repair' => $request->is_repair ?? false,
            'remarks' => $request->remarks ?? null
        ]);

        return redirect()->route('admin.services.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_type' => 'required|string|in:AREA,LINEAR,COUNT,LUMPSUM',
            'default_rate' => 'required|numeric|min:0',
            'is_repair' => 'boolean',
            'remarks' => 'nullable|string|max:1000'
        ]);

        $service = MasterService::findOrFail($id);
        $service->update([
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'default_rate' => $request->default_rate,
            'is_repair' => $request->is_repair ?? false,
            'remarks' => $request->remarks ?? null
        ]);

        return redirect()->route('admin.services.index');
    }

    public function destroy($id)
    {
        $service = MasterService::findOrFail($id);
        $service->delete();

        return redirect()->route('admin.services.index');
    }
}