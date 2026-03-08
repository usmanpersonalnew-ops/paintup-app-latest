<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    /**
     * Show customer dashboard with their projects and quotes
     */
    public function index()
    {
        // Explicitly check authentication (middleware should handle this, but double-check)
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = Auth::guard('customer')->user();

        // Get all projects for this customer (by phone number)
        $projects = Project::where('phone', $customer->phone)
            ->where('status', '!=', 'DRAFT')
            ->with(['rooms.items.surface', 'rooms.items.product', 'rooms.services.masterService'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals and ensure public_token exists for each project
        $projects->each(function ($project) {
            // Calculate painting and services totals from rooms
            $totalPaintAmount = 0;
            $totalServiceAmount = 0;

            foreach ($project->rooms as $room) {
                foreach ($room->items as $item) {
                    // Calculate amount from qty * rate if amount is null
                    $itemAmount = $item->amount ?? ($item->qty ?? 0) * ($item->rate ?? 0);
                    $totalPaintAmount += $itemAmount;
                }
                foreach ($room->services as $service) {
                    // Calculate amount from quantity * rate if amount is null
                    $serviceAmount = $service->amount ?? ($service->quantity ?? 0) * ($service->rate ?? 0);
                    $totalServiceAmount += $serviceAmount;
                }
            }

            // Calculate base_total (excluding GST)
            $discountAmount = $project->discount_amount ?? 0;
            $baseTotal = $totalPaintAmount + $totalServiceAmount - $discountAmount;

            // Calculate milestone amounts from base_total (40-40-20)
            $bookingAmount = round($baseTotal * 0.40, 2);
            $midAmount = round($baseTotal * 0.40, 2);
            $finalAmount = round($baseTotal * 0.20, 2);

            // Persist only database columns (NOT painting_total or services_total)
            $project->total_amount = $totalPaintAmount + $totalServiceAmount;
            $project->base_total = $baseTotal;
            $project->gst_rate = 18;
            $project->booking_amount = $bookingAmount;
            $project->mid_amount = $midAmount;
            $project->final_amount = $finalAmount;
            $project->save();

            // Ensure public_token exists (generate if missing)
            if (!$project->public_token) {
                $project->public_token = \Illuminate\Support\Str::random(32);
                $project->save();
            }
        });

        // Add computed milestone data for each project (Single Source of Truth)
        $projects->each(function ($project) {
            $baseTotal = $project->base_total ?? $project->total_amount ?? 0;

            // Calculate milestone amounts
            $bookingAmount = round($baseTotal * 0.40, 2);
            $midAmount = round($baseTotal * 0.40, 2);
            $finalAmount = round($baseTotal * 0.20, 2);

            // Determine next milestone and outstanding amount
            $nextMilestone = null;
            $outstandingAmount = 0;
            $allPaid = false;

            if ($project->booking_status !== 'PAID') {
                $nextMilestone = [
                    'name' => 'Booking (40%)',
                    'amount' => $bookingAmount,
                ];
                $outstandingAmount = $bookingAmount;
            } elseif ($project->mid_status !== 'PAID') {
                $nextMilestone = [
                    'name' => 'Mid Payment (40%)',
                    'amount' => $midAmount,
                ];
                $outstandingAmount = $midAmount;
            } elseif ($project->final_status !== 'PAID') {
                $nextMilestone = [
                    'name' => 'Final Payment (20%)',
                    'amount' => $finalAmount,
                ];
                $outstandingAmount = $finalAmount;
            } else {
                $allPaid = true;
                $outstandingAmount = 0;
            }

            $project->next_milestone = $nextMilestone;
            $project->outstanding_amount = $outstandingAmount;
            $project->all_paid = $allPaid;
            $project->booking_amount = $bookingAmount;
            $project->mid_amount = $midAmount;
            $project->final_amount = $finalAmount;
        });

        return Inertia::render('Customer/Dashboard', [
            'customer' => $customer,
            'projects' => $projects,
        ]);
    }

    /**
     * Show payment history page
     */
    public function paymentHistory()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = Auth::guard('customer')->user();

        // Get all projects for this customer
        $projects = Project::where('phone', $customer->phone)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate milestone amounts for each project
        $projects->each(function ($project) {
            $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
            $project->booking_amount = round($baseTotal * 0.40, 2);
            $project->mid_amount = round($baseTotal * 0.40, 2);
            $project->final_amount = round($baseTotal * 0.20, 2);
        });

        // Get actual payments from database
        $projectIds = $projects->pluck('id')->toArray();
        $payments = \App\Models\MilestonePayment::whereIn('project_id', $projectIds)
            ->where('payment_status', 'PAID')
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
            'customer' => $customer,
            'projects' => $projects,
            'payments' => $payments,
        ]);
    }

    /**
     * Show work progress page
     */
    public function workProgress()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = Auth::guard('customer')->user();

        // Get all projects for this customer
        $projects = Project::where('phone', $customer->phone)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate milestone amounts for each project
        $projects->each(function ($project) {
            $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
            $project->booking_amount = round($baseTotal * 0.40, 2);
            $project->mid_amount = round($baseTotal * 0.40, 2);
            $project->final_amount = round($baseTotal * 0.20, 2);
        });

        return Inertia::render('Customer/WorkProgress', [
            'customer' => $customer,
            'projects' => $projects,
        ]);
    }

}
