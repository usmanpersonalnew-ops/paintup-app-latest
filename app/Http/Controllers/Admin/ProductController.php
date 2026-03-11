<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\MasterProduct;
use App\Models\MasterSurface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterProduct::with('surfaces')->withCount('systems'); // <--- THE FIX
        
        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->brand) {
            $query->where('brand', $request->brand);
        }
        if ($request->tier) {
            $query->where('tier', $request->tier);
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $query->get(),
            'brands' => MasterProduct::distinct()->pluck('brand'),
            'filters' => $request->only(['search', 'brand', 'tier'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'surfaces' => MasterSurface::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'brand' => 'required',
            'surface_ids' => 'array'
        ]);

        $product = MasterProduct::create($request->only('name', 'brand', 'tier'));

        if ($request->has('surface_ids')) {
            $product->surfaces()->sync($request->surface_ids);
        }

        // CRITICAL FIX: Save Systems
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

        return redirect()->route('admin.products.index');
    }

    public function edit($id) {
         $product = MasterProduct::with(['surfaces', 'systems'])->findOrFail($id);
         return Inertia::render('Admin/Products/Edit', [
             'product' => $product,
             'surfaces' => MasterSurface::all()
         ]);
     }

    public function update(Request $request, $id) {
         $product = MasterProduct::findOrFail($id);
         $product->update($request->only('name', 'brand', 'tier'));
         
         if ($request->has('surface_ids')) {
             $product->surfaces()->sync($request->surface_ids);
         }

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
         
         return redirect()->route('admin.products.index');
     }

    public function destroy($id)
    {
        $product = MasterProduct::findOrFail($id);
        $product->delete();
        return redirect()->route('admin.products.index');
    }
}