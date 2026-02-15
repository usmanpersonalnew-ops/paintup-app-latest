<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BillingDetail;
use App\Models\MilestonePayment;
use App\Models\Project;
use App\Models\Setting;
use App\Services\CCavenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerPaymentController extends Controller
{
    protected $ccavenue;

    public function __construct(CCavenueService $ccavenue)
    {
        $this->ccavenue = $ccavenue;
    }

    /**
     * Check if CCAvenue is enabled
     */
    protected function isCcavenueEnabled(): bool
    {
        return env('CCAVENUE_ENABLED', false) === true || env('CCAVENUE_ENABLED', false) === 'true';
    }

    /**
     * Get milestone payment details with GST breakdown
     * GET /customer/project/{project}/milestone/{milestone}
     */
    public function getMilestoneDetails(Project $project, string $milestone)
    {
        $milestoneData = $project->calculateMilestoneWithGst($milestone);

        // Check if milestone payment already exists
        $existingPayment = MilestonePayment::where('project_id', $project->id)
            ->where('milestone_name', $milestone)
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => true,
                'milestone' => [
                    'name' => $milestone,
                    'base_amount' => $existingPayment->base_amount,
                    'gst_rate' => $existingPayment->gst_rate,
                    'gst_amount' => $existingPayment->gst_amount,
                    'total_amount' => $existingPayment->total_amount,
                    'payment_status' => $existingPayment->payment_status,
                    'payment_method' => $existingPayment->payment_method,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'milestone' => [
                'name' => $milestone,
                'base_amount' => $milestoneData['base_amount'],
                'gst_rate' => $milestoneData['gst_rate'],
                'gst_amount' => $milestoneData['gst_amount'],
                'total_amount' => $milestoneData['total_amount'],
                'payment_status' => 'PENDING',
            ],
        ]);
    }

    /**
     * Online Booking Payment - initiates CCAvenue payment
     * POST /customer/project/{project}/booking/online
     */
    public function onlineBooking(Request $request, Project $project)
    {
        // Check if CCAvenue is enabled
        if (!$this->isCcavenueEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Online payment is currently disabled. Please contact support.',
            ], 400);
        }

        // Recalculate totals if base_total is missing or zero
        if (empty($project->base_total) || $project->base_total == 0) {
            $project->load(['rooms.items', 'rooms.services']);
            $project->recalculateTotals();
            $project->refresh();
        }

        // Calculate pricing from BASE TOTAL only
        $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
        $gstRate = $project->getGstRate();

        // Calculate milestone amounts from base total
        $bookingBase = round($baseTotal * 0.40, 2);
        $bookingGst = round($bookingBase * ($gstRate / 100), 2);
        $bookingTotal = $bookingBase + $bookingGst;

        // Validate that amount is greater than zero
        if ($bookingTotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process payment: Project total is zero. Please ensure the project has items or services added.',
            ], 400);
        }

        // Generate order ID for CCAvenue
        $orderId = 'PAINTUP-' . $project->id . '-BOOKING-' . time();

        // Get customer info
        $customerName = $project->client_name;
        $customerEmail = $project->customer->email ?? 'customer@example.com';
        $customerPhone = $project->phone;

        // Generate CCAvenue payment URL
        $paymentUrl = $this->ccavenue->getPaymentUrl(
            $orderId,
            $bookingTotal,
            $customerName,
            $customerEmail,
            $customerPhone,
            'booking'
        );

        // Store payment info in session for callback
        session([
            'ccavenue_order_id' => $orderId,
            'ccavenue_project_id' => $project->id,
            'ccavenue_milestone' => 'booking',
            'ccavenue_amount' => $bookingTotal,
            'ccavenue_base_amount' => $bookingBase,
            'ccavenue_gst_amount' => $bookingGst,
        ]);

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
            'order_id' => $orderId,
            'message' => 'Redirecting to payment gateway...',
        ]);
    }

    /**
     * Cash Booking Payment
     * POST /customer/project/{project}/booking/cash
     * Customer marks intent to pay cash - requires supervisor confirmation
     */
    public function cashBooking(Request $request, Project $project)
    {
        $request->validate([]);

        // Calculate pricing from BASE TOTAL only
        $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
        $gstRate = $project->getGstRate();

        // Calculate milestone amounts from base total
        $bookingBase = round($baseTotal * 0.40, 2);
        $bookingGst = round($bookingBase * ($gstRate / 100), 2);
        $bookingTotal = $bookingBase + $bookingGst;

        DB::transaction(function () use ($project, $baseTotal, $bookingBase, $bookingGst, $bookingTotal, $gstRate) {
            // Create milestone payment record with AWAITING_CONFIRMATION status
            MilestonePayment::create([
                'project_id' => $project->id,
                'milestone_name' => 'booking',
                'base_amount' => $bookingBase,
                'gst_amount' => $bookingGst,
                'total_amount' => $bookingTotal,
                'payment_status' => MilestonePayment::STATUS_AWAITING_CONFIRMATION,
                'payment_method' => MilestonePayment::METHOD_CASH,
                'payment_reference' => 'CASH-' . strtoupper(uniqid()),
            ]);

            // Update project
            $project->base_total = $baseTotal;
            $project->gst_rate = $gstRate;
            $project->booking_amount = $bookingBase;
            $project->booking_gst = $bookingGst;
            $project->booking_total = $bookingTotal;
            $project->booking_status = 'AWAITING_CONFIRMATION';
            $project->mid_status = 'PENDING';
            $project->final_status = 'PENDING';
            $project->payment_method = 'CASH';
            $project->status = 'AWAITING_CASH_CONFIRMATION';
            $project->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Cash booking marked. Awaiting supervisor confirmation.',
            'project' => [
                'booking_status' => 'AWAITING_CONFIRMATION',
                'status' => 'AWAITING_CASH_CONFIRMATION',
                'booking_amount' => $bookingBase,
                'booking_gst' => $bookingGst,
                'booking_total' => $bookingTotal,
            ],
        ]);
    }

    /**
     * Pay Mid Payment - initiates CCAvenue for online
     * POST /customer/project/{project}/mid-payment
     */
    public function payMidPayment(Request $request, Project $project)
    {
        $request->validate([
            'payment_method' => 'required|in:ONLINE,CASH',
        ]);

        // Recalculate totals if base_total is missing or zero
        if (empty($project->base_total) || $project->base_total == 0) {
            $project->load(['rooms.items', 'rooms.services']);
            $project->recalculateTotals();
            $project->refresh();
        }

        $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
        $gstRate = $project->getGstRate();

        $midBase = round($baseTotal * 0.40, 2);
        $midGst = round($midBase * ($gstRate / 100), 2);
        $midTotal = $midBase + $midGst;

        if ($request->payment_method === 'ONLINE') {
            // Check if CCAvenue is enabled
            if (!$this->isCcavenueEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online payment is currently disabled. Please contact support.',
                ], 400);
            }

            // Validate that amount is greater than zero
            if ($midTotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot process payment: Project total is zero. Please ensure the project has items or services added.',
                ], 400);
            }

            // Generate order ID for CCAvenue
            $orderId = 'PAINTUP-' . $project->id . '-MID-' . time();

            // Get customer info
            $customerName = $project->client_name;
            $customerEmail = $project->customer->email ?? 'customer@example.com';
            $customerPhone = $project->phone;

            // Generate CCAvenue payment URL
            $paymentUrl = $this->ccavenue->getPaymentUrl(
                $orderId,
                $midTotal,
                $customerName,
                $customerEmail,
                $customerPhone,
                'mid'
            );

            // Store payment info in session for callback
            session([
                'ccavenue_order_id' => $orderId,
                'ccavenue_project_id' => $project->id,
                'ccavenue_milestone' => 'mid',
                'ccavenue_amount' => $midTotal,
                'ccavenue_base_amount' => $midBase,
                'ccavenue_gst_amount' => $midGst,
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'order_id' => $orderId,
                'message' => 'Redirecting to payment gateway...',
            ]);
        } else {
            DB::transaction(function () use ($project, $midBase, $midGst, $midTotal, $gstRate) {
                MilestonePayment::create([
                    'project_id' => $project->id,
                    'milestone_name' => 'mid',
                    'base_amount' => $midBase,
                    'gst_amount' => $midGst,
                    'total_amount' => $midTotal,
                    'payment_status' => MilestonePayment::STATUS_AWAITING_CONFIRMATION,
                    'payment_method' => MilestonePayment::METHOD_CASH,
                    'payment_reference' => 'MID-CASH-' . strtoupper(uniqid()),
                ]);

                $project->mid_amount = $midBase;
                $project->mid_gst = $midGst;
                $project->mid_total = $midTotal;
                $project->mid_status = 'AWAITING_CONFIRMATION';
                $project->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Mid payment recorded. Supervisor will collect cash.',
            ]);
        }
    }

    /**
     * Pay Final Payment - initiates CCAvenue for online
     * POST /customer/project/{project}/final-payment
     */
    public function payFinalPayment(Request $request, Project $project)
    {
        $request->validate([
            'payment_method' => 'required|in:ONLINE,CASH',
        ]);

        // Recalculate totals if base_total is missing or zero
        if (empty($project->base_total) || $project->base_total == 0) {
            $project->load(['rooms.items', 'rooms.services']);
            $project->recalculateTotals();
            $project->refresh();
        }

        $baseTotal = $project->base_total ?? $project->total_amount ?? 0;
        $gstRate = $project->getGstRate();

        $finalBase = round($baseTotal * 0.20, 2);
        $finalGst = round($finalBase * ($gstRate / 100), 2);
        $finalTotal = $finalBase + $finalGst;

        if ($request->payment_method === 'ONLINE') {
            // Check if CCAvenue is enabled
            if (!$this->isCcavenueEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online payment is currently disabled. Please contact support.',
                ], 400);
            }

            // Validate that amount is greater than zero
            if ($finalTotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot process payment: Project total is zero. Please ensure the project has items or services added.',
                ], 400);
            }

            // Generate order ID for CCAvenue
            $orderId = 'PAINTUP-' . $project->id . '-FINAL-' . time();

            // Get customer info
            $customerName = $project->client_name;
            $customerEmail = $project->customer->email ?? 'customer@example.com';
            $customerPhone = $project->phone;

            // Generate CCAvenue payment URL
            $paymentUrl = $this->ccavenue->getPaymentUrl(
                $orderId,
                $finalTotal,
                $customerName,
                $customerEmail,
                $customerPhone,
                'final'
            );

            // Store payment info in session for callback
            session([
                'ccavenue_order_id' => $orderId,
                'ccavenue_project_id' => $project->id,
                'ccavenue_milestone' => 'final',
                'ccavenue_amount' => $finalTotal,
                'ccavenue_base_amount' => $finalBase,
                'ccavenue_gst_amount' => $finalGst,
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'order_id' => $orderId,
                'message' => 'Redirecting to payment gateway...',
            ]);
        } else {
            DB::transaction(function () use ($project, $finalBase, $finalGst, $finalTotal, $gstRate) {
                MilestonePayment::create([
                    'project_id' => $project->id,
                    'milestone_name' => 'final',
                    'base_amount' => $finalBase,
                    'gst_amount' => $finalGst,
                    'total_amount' => $finalTotal,
                    'payment_status' => MilestonePayment::STATUS_AWAITING_CONFIRMATION,
                    'payment_method' => MilestonePayment::METHOD_CASH,
                    'payment_reference' => 'FINAL-CASH-' . strtoupper(uniqid()),
                ]);

                $project->final_amount = $finalBase;
                $project->final_gst = $finalGst;
                $project->final_total = $finalTotal;
                $project->final_status = 'AWAITING_CONFIRMATION';
                $project->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Final payment recorded. Supervisor will collect cash.',
            ]);
        }
    }

    /**
     * Show Payment Page (Dedicated Checkout)
     * GET /customer/payment/{project}/{milestone}
     */
    public function showPaymentPage(Project $project, string $milestone)
    {
        // Check authentication
        if (!\Illuminate\Support\Facades\Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();

        // Verify this project belongs to the customer
        if ($project->phone !== $customer->phone) {
            abort(403, 'You do not have access to this project.');
        }

        // Validate milestone
        $validMilestones = ['booking', 'mid', 'final'];
        if (!in_array($milestone, $validMilestones)) {
            abort(404, 'Invalid milestone');
        }

        // Check if milestone is payable
        if ($milestone === 'booking' && $project->booking_status === 'PAID') {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Booking payment already completed');
        }
        if ($milestone === 'mid' && $project->mid_status === 'PAID') {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Mid payment already completed');
        }
        if ($milestone === 'final' && $project->final_status === 'PAID') {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Final payment already completed');
        }

        // Check sequence: booking must be paid before mid, mid before final
        if ($milestone === 'mid' && $project->booking_status !== 'PAID') {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Please complete booking payment first');
        }
        if ($milestone === 'final' && $project->mid_status !== 'PAID') {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Please complete mid payment first');
        }

        // Calculate milestone amounts
        $milestoneData = $project->calculateMilestoneWithGst($milestone);

        // Get existing billing details
        $billingDetails = BillingDetail::where('project_id', $project->id)
            ->where('milestone_type', $milestone)
            ->first();

        return Inertia::render('Customer/Payment', [
            'customer' => $customer,
            'project' => $project,
            'milestone' => [
                'name' => $milestone,
                'base_amount' => $milestoneData['base_amount'],
                'gst_rate' => $milestoneData['gst_rate'],
                'gst_amount' => $milestoneData['gst_amount'],
                'total_amount' => $milestoneData['total_amount'],
            ],
            'billingDetails' => $billingDetails,
        ]);
    }

    /**
     * Save Billing Details
     * POST /customer/project/{project}/billing-details
     */
    public function saveBillingDetails(Request $request, Project $project)
    {
        $validated = $request->validate([
            'milestone_type' => 'required|in:booking,mid,final',
            'buying_type' => 'required|in:INDIVIDUAL,BUSINESS',
            'gstin' => 'nullable|required_if:buying_type,BUSINESS|string|size:15',
            'business_name' => 'nullable|required_if:buying_type,BUSINESS|string|max:255',
            'business_address' => 'nullable|required_if:buying_type,BUSINESS|string',
            'state' => 'nullable|required_if:buying_type,BUSINESS|string|max:100',
            'pincode' => 'nullable|required_if:buying_type,BUSINESS|string|size:6',
        ]);

        // Validate GSTIN format if provided
        if (!empty($validated['gstin'])) {
            if (!BillingDetail::validateGstin($validated['gstin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GSTIN format',
                ], 422);
            }
        }

        // Upsert billing details
        BillingDetail::updateOrCreate(
            ['project_id' => $project->id, 'milestone_type' => $validated['milestone_type']],
            [
                'buying_type' => $validated['buying_type'],
                'gstin' => $validated['gstin'] ?? null,
                'business_name' => $validated['business_name'] ?? null,
                'business_address' => $validated['business_address'] ?? null,
                'state' => $validated['state'] ?? null,
                'pincode' => $validated['pincode'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Billing details saved successfully',
        ]);
    }
}
