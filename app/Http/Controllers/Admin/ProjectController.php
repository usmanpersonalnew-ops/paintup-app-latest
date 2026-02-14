<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects (Home Visits).
     */
    public function index(Request $request)
    {
        $query = \App\Models\Project::query();

        if ($request->search) {
            $query->where('client_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('location', 'like', '%'.$request->search.'%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $query->latest()->paginate(10),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        return Inertia::render('Admin/Projects/Create');
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'location' => 'required|string|max:500',
            'total_amount' => 'nullable|numeric',
        ]);

        // Calculate pricing breakdown
        $subtotal = $validated['total_amount'] ?? 0;
        $gstRate = 18; // Default GST rate
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $grandTotal = $subtotal + $gstAmount;

        // Calculate milestone amounts (40-40-20) from GRAND TOTAL
        $milestones = [
            'booking_amount' => $grandTotal > 0 ? round($grandTotal * 0.40, 2) : 0,
            'mid_payment_amount' => $grandTotal > 0 ? round($grandTotal * 0.40, 2) : 0,
            'final_payment_amount' => $grandTotal > 0 ? round($grandTotal * 0.20, 2) : 0,
        ];

        $project = Project::create([
            'client_name' => $validated['client_name'],
            'phone' => $validated['phone'],
            'location' => $validated['location'],
            'total_amount' => $subtotal,
            'subtotal' => $subtotal,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'grand_total' => $grandTotal,
            'booking_amount' => $milestones['booking_amount'],
            'mid_payment_amount' => $milestones['mid_payment_amount'],
            'final_payment_amount' => $milestones['final_payment_amount'],
            'status' => Project::STATUS_DRAFT,
            'booking_payment_status' => Project::PAYMENT_PENDING,
            'mid_payment_status' => Project::PAYMENT_PENDING,
            'final_payment_status' => Project::PAYMENT_PENDING,
        ]);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display project details (for Manage button).
     */
    public function show($id)
    {
        $project = Project::with(['rooms.items', 'rooms.services', 'coupon'])->findOrFail($id);

        // Calculate milestone totals (base + GST) for display
        $bookingTotal = ($project->booking_amount ?? 0) + ($project->booking_gst ?? 0);
        $midTotal = ($project->mid_amount ?? 0) + ($project->mid_gst ?? 0);
        $finalTotal = ($project->final_amount ?? 0) + ($project->final_gst ?? 0);

        // Add calculated totals to project for Vue component
        $project->booking_payment_amount = $bookingTotal;
        $project->mid_payment_amount = $midTotal;
        $project->final_payment_amount = $finalTotal;

        return Inertia::render('Admin/Projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Show the form for editing a project.
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);

        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'location' => 'required|string|max:500',
            'total_amount' => 'nullable|numeric',
            'status' => 'nullable|string|in:DRAFT,AWAITING_CASH_CONFIRMATION,CONFIRMED,IN_PROGRESS,COMPLETED',
        ]);

        // Recalculate pricing breakdown if total changed
        $subtotal = $validated['total_amount'] ?? $project->subtotal;
        $gstRate = 18; // Default GST rate
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $grandTotal = $subtotal + $gstAmount;

        // Recalculate milestone amounts from GRAND TOTAL
        $milestones = [
            'booking_amount' => $grandTotal > 0 ? round($grandTotal * 0.40, 2) : 0,
            'mid_payment_amount' => $grandTotal > 0 ? round($grandTotal * 0.40, 2) : 0,
            'final_payment_amount' => $grandTotal > 0 ? round($grandTotal * 0.20, 2) : 0,
        ];

        $project->update([
            'client_name' => $validated['client_name'],
            'phone' => $validated['phone'],
            'location' => $validated['location'],
            'total_amount' => $subtotal,
            'subtotal' => $subtotal,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'grand_total' => $grandTotal,
            'booking_amount' => $milestones['booking_amount'],
            'mid_payment_amount' => $milestones['mid_payment_amount'],
            'final_payment_amount' => $milestones['final_payment_amount'],
            'status' => $validated['status'] ?? $project->status,
        ]);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy($id)
    {
        try {
            // Check if projects table exists first
            $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='projects'");
            
            if (empty($tables)) {
                return redirect()->route('admin.projects.index')
                    ->with('error', 'Projects table not found.');
            }
            
            // Delete using query builder with bindings (safer)
            $affected = \DB::delete('DELETE FROM projects WHERE id = ?', [$id]);
            
            if ($affected > 0) {
                return redirect()->route('admin.projects.index')
                    ->with('success', 'Project deleted successfully.');
            } else {
                return redirect()->route('admin.projects.index')
                    ->with('error', 'Project not found.');
            }
        } catch (\Exception $e) {
            // If all else fails, try direct SQL execution
            try {
                $pdo = \DB::connection()->getPdo();
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('admin.projects.index')
                    ->with('success', 'Project deleted successfully.');
            } catch (\Exception $e2) {
                return redirect()->route('admin.projects.index')
                    ->with('error', 'Failed to delete project: ' . $e2->getMessage());
            }
        }
    }
}
