<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            return response()->json([
                'success' => true,
                'message' => 'Booking cash payment confirmed successfully',
            ]);
        }

        if ($milestone === 'mid') {
            if ($project->booking_status !== 'PAID') {
                return response()->json(['success' => false, 'message' => 'Please confirm booking payment first']);
            }

            if ($project->mid_status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'Mid payment already confirmed']);
            }

            $project->mid_status = 'PAID';
            $project->mid_paid_at = now();
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Mid payment cash confirmed successfully',
            ]);
        }

        if ($milestone === 'final') {
            if ($project->mid_status !== 'PAID') {
                return response()->json(['success' => false, 'message' => 'Please confirm mid payment first']);
            }

            if ($project->final_status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'Final payment already confirmed']);
            }

            $project->final_status = 'PAID';
            $project->final_paid_at = now();
            $project->status = 'COMPLETED';
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Final payment cash confirmed. Project completed!',
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

        if ($project->mid_status !== 'PAID') {
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

        return response()->json([
            'success' => true,
            'message' => 'Final payment marked as PAID manually. Project completed!',
        ]);
    }
}
