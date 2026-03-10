<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\Msg91WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects (Home Visits).
     */
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->search) {
            $query->where('client_name', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%')
                ->orWhere('location', 'like', '%' . $request->search . '%');
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
        $supervisors = User::role('supervisor')
            ->where('status', 'ACTIVE')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Admin/Projects/Create', [
            'supervisors' => $supervisors,
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'required|numeric|min:1000000000|max:9999999999',
            'location' => 'required|string|max:500',
            'total_amount' => 'nullable|numeric',
            'supervisor_id' => 'nullable|integer|exists:users,id',
            'home_visit_date' => 'nullable|date',
            'home_visit_time' => 'nullable|string',
            'home_visit_supervisors' => 'nullable|array',
            'home_visit_supervisors.*' => 'integer|exists:users,id',
        ]);

        // Calculate pricing breakdown
        $subtotal = $validated['total_amount'] ?? 0;
        $gstRate = 0; // Default GST rate
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $grandTotal = $subtotal + $gstAmount;

        // Calculate milestone amounts (40-40-20) from GRAND TOTAL
        $milestones = [
            'booking_amount' => $grandTotal > 0 ? round($grandTotal * 0.40, 2) : 0,
            'mid_payment_amount' => $grandTotal > 0 ? round($grandTotal * 0.40, 2) : 0,
            'final_payment_amount' => $grandTotal > 0 ? round($grandTotal * 0.20, 2) : 0,
        ];

        $user = User::firstOrCreate([
            'phone' => $validated['phone'],
        ], [
            'name' => $validated['client_name'],
            'email' => $validated['email'] ?? null,
            'status' => 'ACTIVE',
            'password' => bcrypt('123456'), // Set a default password or generate one
        ]);

        Project::create([
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
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'home_visit_date' => $validated['home_visit_date'] ?? null,
            'home_visit_time' => $validated['home_visit_time'] ?? null,
            'home_visit_supervisors' => isset($validated['home_visit_supervisors']) ? json_encode($validated['home_visit_supervisors']) : null,
        ]);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display project details (for Manage button).
     */
    public function show($id)
    {
        $project = Project::with(['rooms.items.product', 'rooms.items.surface', 'rooms.items.system', 'rooms.services.masterService', 'coupon'])->findOrFail($id);

        // Calculate room totals if not already set
        foreach ($project->rooms as $room) {
            if (!$room->total_amount || $room->total_amount == 0) {
                $roomTotal = 0;
                foreach ($room->items as $item) {
                    $roomTotal += $item->amount ?? 0;
                }
                foreach ($room->services as $service) {
                    $roomTotal += $service->amount ?? 0;
                }
                $room->total_amount = $roomTotal;
            }
        }

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
        $supervisors = \App\Models\User::where('role', 'SUPERVISOR')
            ->where('status', 'ACTIVE')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
            'supervisors' => $supervisors,
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
            'status' => 'required|string|in:NEW,LEAD,PENDING,ACCEPTED,IN_PROGRESS,REJECTED,COMPLETED,DRAFT,AWAITING_CASH_CONFIRMATION,CONFIRMED',
            'supervisor_id' => 'nullable|integer|exists:users,id',
            'home_visit_date' => 'nullable|date',
            'home_visit_time' => 'nullable|string',
            'home_visit_supervisors' => 'nullable|array',
            'home_visit_supervisors.*' => 'integer|exists:users,id',
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
            'status' => $validated['status'],
            'supervisor_id' => $validated['supervisor_id'] ?? $project->supervisor_id,
            'home_visit_date' => $validated['home_visit_date'] ?? $project->home_visit_date,
            'home_visit_time' => $validated['home_visit_time'] ?? $project->home_visit_time,
            'home_visit_supervisors' => isset($validated['home_visit_supervisors']) ? json_encode($validated['home_visit_supervisors']) : $project->home_visit_supervisors,
        ]);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * View customer quote (same view as customer sees)
     */
    public function viewQuote($id)
    {
        // Find project - admin can view any project
        $project = Project::where('id', $id)
            ->with(['rooms.items.surface', 'rooms.items.product', 'rooms.items.system', 'rooms.services.masterService', 'quote'])
            ->firstOrFail();

        // Calculate totals - sum pre-computed amounts only
        $totalPaintAmount = 0;
        $totalServiceAmount = 0;

        foreach ($project->rooms as $room) {
            $roomTotal = 0;
            foreach ($room->items as $item) {
                $itemAmount = $item->amount ?? 0;
                $totalPaintAmount += $itemAmount;
                $roomTotal += $itemAmount;
            }
            foreach ($room->services as $service) {
                $serviceAmount = $service->amount ?? 0;
                $totalServiceAmount += $serviceAmount;
                $roomTotal += $serviceAmount;

                // Ensure service name is populated
                if (!$service->custom_name) {
                    // Try to get from masterService relationship first
                    if ($service->masterService) {
                        $service->custom_name = $service->masterService->name;
                    }
                    // If relationship is null but master_service_id exists, load it
                    elseif ($service->master_service_id) {
                        $masterService = \App\Models\MasterService::find($service->master_service_id);
                        if ($masterService) {
                            $service->custom_name = $masterService->name;
                        } else {
                            $service->custom_name = 'Additional Service';
                        }
                    } else {
                        $service->custom_name = 'Additional Service';
                    }
                }
            }
            $room->room_total = $roomTotal;
        }

        // Base total (without GST) - used for contract value and milestone calculations
        // Apply discount if coupon is used
        $discountAmount = $project->discount_amount ?? 0;
        $baseTotal = $totalPaintAmount + $totalServiceAmount - $discountAmount;

        // Milestone amounts (40-40-20) calculated from BASE TOTAL (GST excluded)
        $bookingAmount = round($baseTotal * 0.40, 2);
        $midPaymentAmount = round($baseTotal * 0.40, 2);
        $finalPaymentAmount = round($baseTotal * 0.20, 2);

        // Get notes from quote if available
        $notes = null;
        if ($project->quote) {
            $notes = $project->quote->notes ?? null;
        }

        // Admin viewing - no customer login required
        return Inertia::render('Customer/QuoteView', [
            'customer' => null,
            'project' => $project, // Quote relationship is already loaded above
            'totals' => [
                'paint' => $totalPaintAmount,
                'services' => $totalServiceAmount,
                'base_total' => $baseTotal,
                'booking_amount' => $bookingAmount,
                'mid_payment_amount' => $midPaymentAmount,
                'final_payment_amount' => $finalPaymentAmount,
            ],
            'isLoggedIn' => false, // Admin viewing, not customer
            'isAdminView' => true, // Flag to show admin-specific UI
            'notes' => $notes,
        ]);
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

    /**
     * Send WhatsApp message for home visit scheduled
     */
    public function sendWhatsAppMessage(Request $request, Project $project)
    {
        // Validate that project has required data
        if (!$project->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Project phone number is missing.'
            ], 400);
        }

        // Format date and time
        $visitDate = $project->home_visit_date
            ? \Carbon\Carbon::parse($project->home_visit_date)->format('d M Y')
            : 'Not scheduled';

        $visitTime = $project->home_visit_time
            ? \Carbon\Carbon::parse($project->home_visit_time)->format('h:i A')
            : 'Not scheduled';

        // Prepare template values for: "Hi {{1}}, your home visit is scheduled on {{2}} at {{3}}. Our supervisor will contact you shortly."
        // {{1}} = Customer Name, {{2}} = Date, {{3}} = Time
        $value1 = $project->client_name ?: 'Customer';
        $value2 = $visitDate;
        $value3 = $visitTime;

        try {
            $msg91Service = new Msg91WhatsappService();
            $sent = $msg91Service->sendHomeVisitScheduled(
                $project->phone,
                $value1,
                $value2,
                $value3
            );

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully to ' . $project->phone
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send WhatsApp message. Please check logs.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending WhatsApp message: ' . $e->getMessage()
            ], 500);
        }
    }
}
