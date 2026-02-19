<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Verify supervisor is authenticated and has SUPERVISOR role
     */
    private function verifySupervisor(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'SUPERVISOR') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        return $user;
    }

    /**
     * Confirm cash booking payment received from customer
     * POST /supervisor/project/{id}/confirm-cash-booking
     */
    public function confirmCashBooking(Request $request, Project $project)
    {
        $user = $this->verifySupervisor($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Verify this is a cash payment
        if ($project->payment_method !== 'CASH') {
            return response()->json(['success' => false, 'message' => 'This is not a cash payment project'], 400);
        }

        // Verify booking is pending cash confirmation
        if ($project->booking_status !== 'AWAITING_CONFIRMATION') {
            return response()->json(['success' => false, 'message' => 'Booking is not pending cash confirmation'], 400);
        }

        // Confirm the cash payment
        $project->booking_status = 'PAID';
        $project->booking_paid_at = now();
        $project->cash_confirmed_by = $user->id;
        $project->cash_confirmed_at = now();
        $project->mid_status = 'PENDING';
        $project->save();

        // Update milestone payment record
        \App\Models\MilestonePayment::where('project_id', $project->id)
            ->where('milestone_name', 'booking')
            ->where('payment_status', 'AWAITING_CONFIRMATION')
            ->update([
                'payment_status' => \App\Models\MilestonePayment::STATUS_PAID,
                'paid_at' => now(),
            ]);

        // Refresh project data
        $project->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Cash booking payment confirmed successfully',
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

    /**
     * Confirm cash payment for a milestone
     * POST /supervisor/project/{project}/confirm-cash
     */
    public function confirmCashPayment(Request $request, Project $project)
    {
        $request->validate([
            'milestone' => 'required|in:booking,mid,final',
        ]);

        $user = $this->verifySupervisor($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        if ($project->payment_method !== 'CASH') {
            return response()->json(['success' => false, 'message' => 'This is not a cash payment project'], 400);
        }

        $milestone = $request->milestone;

        // Handle booking cash confirmation
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
            if ($project->status === 'AWAITING_CASH_CONFIRMATION') {
                $project->status = 'CONFIRMED';
            }
            $project->save();

            // Update milestone payment record
            \App\Models\MilestonePayment::where('project_id', $project->id)
                ->where('milestone_name', 'booking')
                ->where('payment_status', 'AWAITING_CONFIRMATION')
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

        // Handle mid payment cash confirmation
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

        // Handle final payment cash confirmation
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
     * POST /supervisor/project/{project}/collect-mid
     */
    public function collectMidPayment(Request $request, Project $project)
    {
        $user = $this->verifySupervisor($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
                    'payment_method' => \App\Models\MilestonePayment::METHOD_ONLINE,
                    'payment_reference' => $project->mid_reference,
                    'paid_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Mid payment collected successfully',
            ]);
        } else {
            $project->mid_status = 'CASH_PENDING';
            $project->save();

            // Create milestone payment record with CASH_PENDING status
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
                    'payment_status' => 'AWAITING_CONFIRMATION',
                    'payment_method' => \App\Models\MilestonePayment::METHOD_CASH,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Mid payment marked as cash pending',
            ]);
        }
    }

    /**
     * Collect final payment
     * POST /supervisor/project/{project}/collect-final
     */
    public function collectFinalPayment(Request $request, Project $project)
    {
        $user = $this->verifySupervisor($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
}
