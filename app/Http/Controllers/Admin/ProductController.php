<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterProduct;
use App\Models\MasterSurface;
use App\Models\Tier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterProduct::with(['surfaces', 'tier'])->withCount('systems');
        
        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->brand) {
            $query->where('brand', $request->brand);
        }
        if ($request->tier_id) {
            $query->where('tier_id', $request->tier_id);
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $query->get(),
            'brands' => MasterProduct::distinct()->pluck('brand'),
            'tiers' => Tier::all(['id', 'name']),
            'filters' => $request->only(['search', 'brand', 'tier_id'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'surfaces' => MasterSurface::all(['id', 'name']),
            'tiers' => Tier::all(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'tier_id' => 'nullable|exists:tiers,id',
            'surface_ids' => 'nullable|array'
        ]);

        $product = MasterProduct::create($request->only('name', 'brand', 'tier_id'));

        if ($request->has('surface_ids')) {
            $product->surfaces()->sync($request->surface_ids);
        }

        // Save Systems
        if ($request->has('systems')) {
            foreach ($request->systems as $system) {
                if (!empty($system['system_name'])) {
                    $product->systems()->create([
                        'system_name' => $system['system_name'],
                        'process_remarks' => $system['process_remarks'] ?? '',
                        'base_rate' => $system['base_rate'] ?? 0,
                        'warranty_months' => $system['warranty_months'] ?? 0
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product = MasterProduct::with(['surfaces', 'systems'])->findOrFail($id);
        
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'surfaces' => MasterSurface::all(['id', 'name']),
            'tiers' => Tier::all(['id', 'name'])
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'tier_id' => 'nullable|exists:tiers,id',
            'surface_ids' => 'nullable|array'
        ]);

        $product = MasterProduct::findOrFail($id);
        $product->update($request->only('name', 'brand', 'tier_id'));
        
        if ($request->has('surface_ids')) {
            $product->surfaces()->sync($request->surface_ids);
        } else {
            $product->surfaces()->sync([]);
        }

        // Update systems
        $product->systems()->delete();
        if ($request->has('systems')) {
            foreach ($request->systems as $system) {
                if (!empty($system['system_name'])) {
                    $product->systems()->create([
                        'system_name' => $system['system_name'],
                        'process_remarks' => $system['process_remarks'] ?? '',
                        'base_rate' => $system['base_rate'] ?? 0,
                        'warranty_months' => $system['warranty_months'] ?? 0
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = MasterProduct::findOrFail($id);
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}