<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Confirm cash payment received from customer
     * Called by Admin or Supervisor after collecting cash
     */
    public function confirmCashPayment(Request $request, Project $project)
    {
        $request->validate([
            'milestone' => 'required|in:booking,mid,final',
        ]);

        $user = Auth::user();
        if (!$user || !in_array($user->role, ['ADMIN', 'SUPERVISOR'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        if ($project->payment_method !== 'CASH') {
            return response()->json(['success' => false, 'message' => 'This is not a cash payment project'], 400);
        }

        $milestone = $request->milestone;

        if ($milestone === 'booking') {
            if ($project->booking_status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'Booking payment already confirmed']);
            }

            $project->booking_status = 'PAID';
            $project->booking_paid_at = now();
            $project->cash_confirmed_by = $user->id;
            $project->cash_confirmed_at = now();
            $project->mid_status = 'PENDING';
            // Update project status from AWAITING_CASH_CONFIRMATION to CONFIRMED
            if ($project->status === Project::STATUS_AWAITING_CASH_CONFIRMATION) {
                $project->status = Project::STATUS_CONFIRMED;
            }
            $project->save();

            // Update milestone payment record
            \App\Models\MilestonePayment::where('project_id', $project->id)
                ->where('milestone_name', 'booking')
                ->whereIn('payment_status', ['AWAITING_CONFIRMATION', 'PENDING'])
                ->update([
                    'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                    'paid_at' => now(),
                ]);

            // Refresh project data
            $project->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Booking cash payment confirmed successfully',
                'project' => [
                    'id' => $project->id,
                    'booking_status' => $project->booking_status,
                    'booking_paid_at' => $project->booking_paid_at,
                    'mid_status' => $project->mid_status,
                    'final_status' => $project->final_status,
                    'status' => $project->status,
                ],
            ]);
        }

        if ($milestone === 'mid') {
            if ($project->booking_status !== 'PAID') {
                return response()->json(['success' => false, 'message' => 'Please confirm booking payment first']);
            }

            if ($project->mid_status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'Mid payment already confirmed']);
            }

            // Check if mid payment is awaiting confirmation (either AWAITING_CONFIRMATION or CASH_PENDING)
            if (!in_array($project->mid_status, ['AWAITING_CONFIRMATION', 'CASH_PENDING'])) {
                return response()->json(['success' => false, 'message' => 'Mid payment is not pending cash confirmation'], 400);
            }

            $project->mid_status = 'PAID';
            $project->mid_paid_at = now();
            $project->final_status = 'PENDING';
            $project->cash_confirmed_by = $user->id;
            $project->cash_confirmed_at = now();
            $project->save();

            // Update milestone payment record
            \App\Models\MilestonePayment::where('project_id', $project->id)
                ->where('milestone_name', 'mid')
                ->whereIn('payment_status', ['AWAITING_CONFIRMATION', 'PENDING'])
                ->update([
                    'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                    'paid_at' => now(),
                ]);

            // Refresh project data
            $project->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Mid payment cash confirmed successfully',
                'project' => [
                    'id' => $project->id,
                    'mid_status' => $project->mid_status,
                    'mid_paid_at' => $project->mid_paid_at,
                    'final_status' => $project->final_status,
                    'booking_status' => $project->booking_status,
                    'status' => $project->status,
                ],
            ]);
        }

        if ($milestone === 'final') {
            if ($project->mid_status !== 'PAID') {
                return response()->json(['success' => false, 'message' => 'Please confirm mid payment first']);
            }

            if ($project->final_status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'Final payment already confirmed']);
            }

            // Check if final payment is awaiting confirmation (either AWAITING_CONFIRMATION or CASH_PENDING)
            if (!in_array($project->final_status, ['AWAITING_CONFIRMATION', 'CASH_PENDING'])) {
                return response()->json(['success' => false, 'message' => 'Final payment is not pending cash confirmation'], 400);
            }

            $project->final_status = 'PAID';
            $project->final_paid_at = now();
            $project->status = 'COMPLETED';
            $project->cash_confirmed_by = $user->id;
            $project->cash_confirmed_at = now();
            $project->save();

            // Update milestone payment record
            \App\Models\MilestonePayment::where('project_id', $project->id)
                ->where('milestone_name', 'final')
                ->whereIn('payment_status', ['AWAITING_CONFIRMATION', 'PENDING'])
                ->update([
                    'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                    'paid_at' => now(),
                ]);

            // Refresh project data
            $project->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Final payment cash confirmed. Project completed!',
                'project' => [
                    'id' => $project->id,
                    'final_status' => $project->final_status,
                    'final_paid_at' => $project->final_paid_at,
                    'mid_status' => $project->mid_status,
                    'booking_status' => $project->booking_status,
                    'status' => $project->status,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid milestone specified'], 400);
    }

    /**
     * Collect mid payment
     */
    public function collectMidPayment(Request $request, Project $project)
    {
        $request->validate([
            'payment_method' => 'required|in:ONLINE,CASH',
        ]);

        if ($project->booking_status !== 'PAID') {
            return response()->json(['success' => false, 'message' => 'Please confirm booking payment first']);
        }

        if ($request->payment_method === 'ONLINE') {
            $project->mid_status = 'PAID';
            $project->mid_paid_at = now();
            $project->mid_reference = 'MID-ONLINE-' . strtoupper(uniqid());
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Mid payment collected successfully',
            ]);
        } else {
            $project->mid_status = 'CASH_PENDING';
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Mid payment marked as cash pending',
            ]);
        }
    }

    /**
     * Collect final payment
     */
    public function collectFinalPayment(Request $request, Project $project)
    {
        $request->validate([
            'payment_method' => 'required|in:ONLINE,CASH',
        ]);

        // Allow both PAID and CASH_PENDING status for mid payment
        if ($project->mid_status !== 'PAID' && $project->mid_status !== 'CASH_PENDING') {
            return response()->json(['success' => false, 'message' => 'Please collect mid payment first']);
        }

        if ($request->payment_method === 'ONLINE') {
            $project->final_status = 'PAID';
            $project->final_paid_at = now();
            $project->final_reference = 'FINAL-ONLINE-' . strtoupper(uniqid());
            $project->status = 'COMPLETED';
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Final payment collected. Project completed!',
            ]);
        } else {
            $project->final_status = 'CASH_PENDING';
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Final payment marked as cash pending',
            ]);
        }
    }

    /**
     * Admin manually mark booking as PAID
     */
    public function markBookingPaid(Request $request, Project $project)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Admin only'], 403);
        }

        $project->booking_status = 'PAID';
        $project->booking_paid_at = now();
        $project->booking_reference = 'ADMIN-MANUAL-' . strtoupper(uniqid());
        $project->mid_status = 'PENDING';
        $project->save();

        // Create or update milestone payment record
        $milestoneData = $project->calculateMilestoneWithGst('booking');
        \App\Models\MilestonePayment::updateOrCreate(
            [
                'project_id' => $project->id,
                'milestone_name' => 'booking',
            ],
            [
                'base_amount' => $milestoneData['base_amount'],
                'gst_amount' => $milestoneData['gst_amount'],
                'total_amount' => $milestoneData['total_amount'],
                'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                'payment_method' => \App\Models\MilestonePayment::METHOD_CASH,
                'payment_reference' => $project->booking_reference,
                'paid_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking marked as PAID manually',
        ]);
    }

    /**
     * Admin manually mark mid payment as PAID
     */
    public function markMidPaid(Request $request, Project $project)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Admin only'], 403);
        }

        $project->mid_status = 'PAID';
        $project->mid_paid_at = now();
        $project->mid_reference = 'ADMIN-MANUAL-' . strtoupper(uniqid());
        $project->final_status = 'PENDING';
        $project->save();

        // Create or update milestone payment record
        $milestoneData = $project->calculateMilestoneWithGst('mid');
        \App\Models\MilestonePayment::updateOrCreate(
            [
                'project_id' => $project->id,
                'milestone_name' => 'mid',
            ],
            [
                'base_amount' => $milestoneData['base_amount'],
                'gst_amount' => $milestoneData['gst_amount'],
                'total_amount' => $milestoneData['total_amount'],
                'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                'payment_method' => \App\Models\MilestonePayment::METHOD_CASH,
                'payment_reference' => $project->mid_reference,
                'paid_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Mid payment marked as PAID manually',
        ]);
    }

    /**
     * Admin manually mark final payment as PAID
     */
    public function markFinalPaid(Request $request, Project $project)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Admin only'], 403);
        }

        $project->final_status = 'PAID';
        $project->final_paid_at = now();
        $project->final_reference = 'ADMIN-MANUAL-' . strtoupper(uniqid());
        $project->status = 'COMPLETED';
        $project->save();

        // Create or update milestone payment record
        $milestoneData = $project->calculateMilestoneWithGst('final');
        \App\Models\MilestonePayment::updateOrCreate(
            [
                'project_id' => $project->id,
                'milestone_name' => 'final',
            ],
            [
                'base_amount' => $milestoneData['base_amount'],
                'gst_amount' => $milestoneData['gst_amount'],
                'total_amount' => $milestoneData['total_amount'],
                'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                'payment_method' => \App\Models\MilestonePayment::METHOD_CASH,
                'payment_reference' => $project->final_reference,
                'paid_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Final payment marked as PAID manually. Project completed!',
        ]);
    }

    /**
     * Show payment history page for admin (all payments)
     */
    public function paymentHistory()
    {
        // Get all projects
        $projects = Project::orderBy('created_at', 'desc')->get();

        // Calculate milestone amounts for each project
        $projects->each(function ($project) {
            $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
            $project->booking_amount = round($baseTotal * 0.40, 2);
            $project->mid_amount = round($baseTotal * 0.40, 2);
            $project->final_amount = round($baseTotal * 0.20, 2);
        });

        // Get all payments from database
        $payments = \App\Models\MilestonePayment::where('payment_status', 'PAID')
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(function ($payment) use ($projects) {
                $project = $projects->find($payment->project_id);
                // Get transaction ID - prefer tracking_id, then payment_reference, then bank_ref_no
                $transactionId = $payment->tracking_id
                    ?? $payment->payment_reference
                    ?? $payment->bank_ref_no
                    ?? '-';

                return [
                    'id' => $payment->id,
                    'project_id' => $payment->project_id,
                    'project_name' => $project ? $project->client_name : 'Unknown',
                    'customer_phone' => $project ? $project->phone : '-',
                    'milestone_name' => $payment->milestone_name,
                    'base_amount' => $payment->base_amount,
                    'gst_amount' => $payment->gst_amount,
                    'total_amount' => $payment->total_amount,
                    'payment_method' => $payment->payment_method,
                    'payment_status' => $payment->payment_status,
                    'paid_at' => $payment->paid_at,
                    'transaction_id' => $transactionId,
                    'payment_reference' => $payment->payment_reference,
                    'tracking_id' => $payment->tracking_id,
                    'bank_ref_no' => $payment->bank_ref_no,
                ];
            });

        return Inertia::render('Customer/PaymentHistory', [
            'isAdminView' => true,
            'projects' => $projects,
            'payments' => $payments,
        ]);
    }

    /**
     * Show payment history for a specific project
     */
    public function projectPaymentHistory(Project $project)
    {
        // Load project with cash confirmed by user relationship
        $project->load('cashConfirmedByUser');

        // Get all payments for this project
        $payments = \App\Models\MilestonePayment::where('project_id', $project->id)
            ->orderBy('paid_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payment) use ($project) {
                // Get transaction ID - prefer tracking_id, then payment_reference, then bank_ref_no
                $transactionId = $payment->tracking_id
                    ?? $payment->payment_reference
                    ?? $payment->bank_ref_no
                    ?? '-';

                // Get billing details for this milestone
                $billingDetail = \App\Models\BillingDetail::where('project_id', $payment->project_id)
                    ->where('milestone_type', $payment->milestone_name)
                    ->first();

                // Get cash confirmation info from project (for cash payments)
                $cashConfirmedBy = null;
                $cashConfirmedAt = null;
                $cashConfirmedByName = null;

                if ($payment->payment_method === 'CASH' && $payment->payment_status === 'PAID') {
                    // For booking milestone, check booking confirmation
                    if ($payment->milestone_name === 'booking' && $project->cash_confirmed_by) {
                        $cashConfirmedBy = $project->cash_confirmed_by;
                        $cashConfirmedAt = $project->cash_confirmed_at;
                        if ($project->cashConfirmedByUser) {
                            $cashConfirmedByName = $project->cashConfirmedByUser->name . ' (' . $project->cashConfirmedByUser->role . ')';
                        }
                    }
                    // For mid and final, check if they were paid (supervisor confirmed)
                    // We can show the cash_confirmed_by if it exists (usually for booking, but might be used for others)
                    elseif (($payment->milestone_name === 'mid' || $payment->milestone_name === 'final') && $project->cash_confirmed_by) {
                        // For mid/final, if project has cash_confirmed_by, show it
                        // In future, we might want separate tracking per milestone
                        $cashConfirmedBy = $project->cash_confirmed_by;
                        $cashConfirmedAt = $project->cash_confirmed_at;
                        if ($project->cashConfirmedByUser) {
                            $cashConfirmedByName = $project->cashConfirmedByUser->name . ' (' . $project->cashConfirmedByUser->role . ')';
                        }
                    }
                }

                return [
                    'id' => $payment->id,
                    'project_id' => $payment->project_id,
                    'milestone_name' => $payment->milestone_name,
                    'base_amount' => $payment->base_amount,
                    'gst_amount' => $payment->gst_amount,
                    'total_amount' => $payment->total_amount,
                    'payment_method' => $payment->payment_method,
                    'payment_status' => $payment->payment_status,
                    'paid_at' => $payment->paid_at,
                    'transaction_id' => $transactionId,
                    'payment_reference' => $payment->payment_reference,
                    'tracking_id' => $payment->tracking_id,
                    'bank_ref_no' => $payment->bank_ref_no,
                    'created_at' => $payment->created_at,
                    'billing_details' => $billingDetail ? [
                        'buying_type' => $billingDetail->buying_type,
                        'gstin' => $billingDetail->gstin,
                        'business_name' => $billingDetail->business_name,
                        'business_address' => $billingDetail->business_address,
                        'state' => $billingDetail->state,
                        'pincode' => $billingDetail->pincode,
                    ] : null,
                    'cash_confirmed_by' => $cashConfirmedBy,
                    'cash_confirmed_at' => $cashConfirmedAt,
                    'cash_confirmed_by_name' => $cashConfirmedByName,
                ];
            });

        // Calculate milestone amounts
        $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
        $gstRate = $project->gst_rate ?? 18;

        $bookingAmount = round($baseTotal * 0.40, 2);
        $midAmount = round($baseTotal * 0.40, 2);
        $finalAmount = round($baseTotal * 0.20, 2);

        return Inertia::render('Admin/Projects/PaymentHistory', [
            'project' => $project,
            'payments' => $payments,
            'milestoneAmounts' => [
                'booking' => [
                    'base' => $bookingAmount,
                    'gst' => round($bookingAmount * $gstRate / 100, 2),
                    'total' => round($bookingAmount * (1 + $gstRate / 100), 2),
                ],
                'mid' => [
                    'base' => $midAmount,
                    'gst' => round($midAmount * $gstRate / 100, 2),
                    'total' => round($midAmount * (1 + $gstRate / 100), 2),
                ],
                'final' => [
                    'base' => $finalAmount,
                    'gst' => round($finalAmount * $gstRate / 100, 2),
                    'total' => round($finalAmount * (1 + $gstRate / 100), 2),
                ],
            ],
        ]);
    }

    /**
     * Add new payment entry for a project
     */
    public function addPayment(Request $request, Project $project)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Admin only'], 403);
        }

        $validated = $request->validate([
            'milestone_name' => 'required|in:booking,mid,final',
            'base_amount' => 'required|numeric|min:0',
            'gst_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:ONLINE,CASH,UPI,CARD,NETBANKING,WALLET',
            'payment_status' => 'required|in:PENDING,PAID,AWAITING_CONFIRMATION',
            'payment_reference' => 'nullable|string|max:255',
            'tracking_id' => 'nullable|string|max:255',
            'bank_ref_no' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
            // Billing details
            'buying_type' => 'nullable|in:INDIVIDUAL,BUSINESS',
            'gstin' => 'nullable|string|size:15',
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|size:6',
        ]);

        // Validate GSTIN format if provided
        if (!empty($validated['gstin'])) {
            if (!\App\Models\BillingDetail::validateGstin($validated['gstin'])) {
                return back()->withErrors(['gstin' => 'Invalid GSTIN format. Expected format: 22AAAAA0000A1Z5']);
            }
        }

        // Calculate GST if not provided
        if (!isset($validated['gst_amount']) || $validated['gst_amount'] == 0) {
            $gstRate = $project->gst_rate ?? 18;
            $validated['gst_amount'] = round($validated['base_amount'] * $gstRate / 100, 2);
        }

        // Set paid_at to now if not provided and status is PAID
        if (!isset($validated['paid_at']) && $validated['payment_status'] === 'PAID') {
            $validated['paid_at'] = now();
        }

        // Create payment entry
        $payment = \App\Models\MilestonePayment::create([
            'project_id' => $project->id,
            'milestone_name' => $validated['milestone_name'],
            'base_amount' => $validated['base_amount'],
            'gst_amount' => $validated['gst_amount'],
            'total_amount' => $validated['total_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'tracking_id' => $validated['tracking_id'] ?? null,
            'bank_ref_no' => $validated['bank_ref_no'] ?? null,
            'paid_at' => $validated['paid_at'] ?? null,
        ]);

        // Save billing details if provided
        if (isset($validated['buying_type'])) {
            \App\Models\BillingDetail::updateOrCreate(
                ['project_id' => $project->id, 'milestone_type' => $validated['milestone_name']],
                [
                    'buying_type' => $validated['buying_type'],
                    'gstin' => $validated['gstin'] ?? null,
                    'business_name' => $validated['business_name'] ?? null,
                    'business_address' => $validated['business_address'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'pincode' => $validated['pincode'] ?? null,
                ]
            );
        }

        // Update project payment status if payment is marked as PAID
        if ($validated['payment_status'] === 'PAID') {
            if ($validated['milestone_name'] === 'booking') {
                $project->booking_status = 'PAID';
                $project->booking_paid_at = $validated['paid_at'] ?? now();
                $project->mid_status = 'PENDING';
            } elseif ($validated['milestone_name'] === 'mid') {
                $project->mid_status = 'PAID';
                $project->mid_paid_at = $validated['paid_at'] ?? now();
                $project->final_status = 'PENDING';
            } elseif ($validated['milestone_name'] === 'final') {
                $project->final_status = 'PAID';
                $project->final_paid_at = $validated['paid_at'] ?? now();
                $project->status = 'COMPLETED';
            }
            $project->save();
        }

        return redirect()->route('admin.projects.payment-history', $project->id)
            ->with('success', 'Payment entry added successfully');
    }
}
