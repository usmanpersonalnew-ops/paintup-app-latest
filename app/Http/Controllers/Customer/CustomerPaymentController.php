<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BillingDetail;
use App\Models\MilestonePayment;
use App\Models\Project;
use App\Models\Setting;
use App\Services\CCavenueService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CustomerPaymentController extends Controller
{
    protected $ccavenue;

    protected $paymentGateway;

    public function __construct(CCavenueService $ccavenue, PaymentGatewayService $paymentGateway)
    {
        $this->ccavenue = $ccavenue;
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Check if PhonePe is enabled
     */
    protected function isPhonePeEnabled(): bool
    {
        return env('PHONEPE_ENABLED', false) === true || env('PHONEPE_ENABLED', false) === 'true';
    }

    /**
     * Check if CCAvenue is enabled
     */
    protected function isCcavenueEnabled(): bool
    {
        return env('CCAVENUE_ENABLED', false) === true || env('CCAVENUE_ENABLED', false) === 'true';
    }

    /**
     * Check if any online payment gateway is enabled
     */
    protected function isOnlinePaymentEnabled(): bool
    {
        return $this->isPhonePeEnabled() || $this->isCcavenueEnabled();
    }

    /**
     * Get the active payment gateway (the one to use for this request)
     */
    protected function getActiveGateway(): string
    {
        $phonepe = $this->isPhonePeEnabled();
        $ccavenue = $this->isCcavenueEnabled();
        if ($phonepe && $ccavenue) {
            $default = env('PAYMENT_GATEWAY', 'phonepe');
            return in_array($default, ['phonepe', 'ccavenue'], true) ? $default : 'phonepe';
        }
        if ($phonepe) {
            return 'phonepe';
        }
        return 'ccavenue';
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
        // Check if any online payment gateway is enabled
        if (!$this->isOnlinePaymentEnabled()) {
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

        $gateway = $this->getActiveGateway();

        if ($gateway === 'phonepe') {
            $result = $this->paymentGateway->createPayment($project, 'booking', $bookingTotal);
            if (empty($result['success']) || empty($result['payment_url'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to initiate payment.',
                ], 400);
            }
            session([
                'payment_gateway' => 'phonepe',
                'payment_project_id' => $project->id,
                'payment_milestone' => 'booking',
                'payment_amount' => $bookingTotal,
                'payment_base_amount' => $bookingBase,
                'payment_gst_amount' => $bookingGst,
                'payment_transaction_id' => $result['transaction_id'] ?? null,
            ]);
            return response()->json([
                'success' => true,
                'payment_url' => $result['payment_url'],
                'order_id' => $result['transaction_id'] ?? null,
                'message' => 'Redirecting to payment gateway...',
            ]);
        }

        // CCAvenue flow
        $orderId = 'PAINTUP-' . $project->id . '-BOOKING-' . time();
        $customerName = $project->client_name;
        $customerEmail = $project->customer->email ?? 'customer@example.com';
        $customerPhone = $project->phone;

        $paymentUrl = $this->ccavenue->getPaymentUrl(
            $orderId,
            $bookingTotal,
            $customerName,
            $customerEmail,
            $customerPhone,
            'booking'
        );

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
    // Get base total
    $baseTotal = $project->base_total ?? $project->total_amount ?? 0;

    // GST rate
    $gstRate = $project->getGstRate();

    // Booking calculation (40%)
    $bookingBase  = round($baseTotal * 0.40, 2);
    $bookingGst   = round($bookingBase * ($gstRate / 100), 2);
    $bookingTotal = $bookingBase + $bookingGst;

    DB::transaction(function () use ($project, $baseTotal, $gstRate, $bookingBase, $bookingGst, $bookingTotal) {

        // Create milestone payment
        MilestonePayment::create([
            'project_id'        => $project->id,
            'milestone_name'    => 'booking',
            'base_amount'       => $bookingBase,
            'gst_amount'        => $bookingGst,
            'total_amount'      => $bookingTotal,
            'payment_status'    => MilestonePayment::STATUS_AWAITING_CONFIRMATION,
            'payment_method'    => MilestonePayment::METHOD_CASH,
            'payment_reference' => 'CASH-' . strtoupper(Str::random(10)),
        ]);

        // Update project
        $project->update([
            'base_total'      => $baseTotal,
            'gst_rate'        => 0,
            'booking_amount'  => $bookingBase,
            'booking_gst'     => $bookingGst,
            'booking_total'   => $bookingTotal,

            // IMPORTANT: use allowed ENUM values
            'booking_status'  => 'PENDING',
            'mid_status'      => 'PENDING',
            'final_status'    => 'PENDING',

            'payment_method'  => 'CASH',
            'status'          => 'PENDING',
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'Cash booking marked. Awaiting confirmation.',
        'project' => [
            'booking_status' => 'PENDING',
            'status'         => 'PENDING',
            'booking_amount' => $bookingBase,
            'booking_gst'    => $bookingGst,
            'booking_total'  => $bookingTotal,
        ]
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
            'billing_details' => 'nullable|array',
            'billing_details.buying_type' => 'nullable|in:INDIVIDUAL,BUSINESS',
            'billing_details.gstin' => 'nullable|string|size:15',
            'billing_details.business_name' => 'nullable|string|max:255',
            'billing_details.business_address' => 'nullable|string',
            'billing_details.state' => 'nullable|string|max:100',
            'billing_details.pincode' => 'nullable|string|size:6',
        ]);

        // Save billing details if provided
        if ($request->has('billing_details') && !empty($request->billing_details)) {
            $billingData = $request->billing_details;
            if (!empty($billingData['buying_type'])) {
                // Validate GSTIN format if provided
                if (!empty($billingData['gstin'])) {
                    if (!BillingDetail::validateGstin($billingData['gstin'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid GSTIN format',
                        ], 422);
                    }
                }

                BillingDetail::updateOrCreate(
                    ['project_id' => $project->id, 'milestone_type' => 'mid'],
                    [
                        'buying_type' => $billingData['buying_type'],
                        'gstin' => $billingData['gstin'] ?? null,
                        'business_name' => $billingData['business_name'] ?? null,
                        'business_address' => $billingData['business_address'] ?? null,
                        'state' => $billingData['state'] ?? null,
                        'pincode' => $billingData['pincode'] ?? null,
                    ]
                );
            }
        }

        // Recalculate totals if base_total is missing or zero
        if (empty($project->base_total) || $project->base_total == 0) {
            $project->load(['rooms.items', 'rooms.services']);
            $project->recalculateTotals();
            $project->refresh();
        }

        // Use the project's calculateMilestoneWithGst method for consistency
        $milestoneData = $project->calculateMilestoneWithGst('mid');
        $midBase = $milestoneData['base_amount'];
        $midGst = $milestoneData['gst_amount'];
        $midTotal = $milestoneData['total_amount'];

        if ($request->payment_method === 'ONLINE') {
            if (!$this->isOnlinePaymentEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online payment is currently disabled. Please contact support.',
                ], 400);
            }

            if ($midTotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot process payment: Project total is zero. Please ensure the project has items or services added.',
                ], 400);
            }

            $gateway = $this->getActiveGateway();

            if ($gateway === 'phonepe') {
                $result = $this->paymentGateway->createPayment($project, 'mid', $midTotal);
                if (empty($result['success']) || empty($result['payment_url'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['error'] ?? 'Failed to initiate payment.',
                    ], 400);
                }
                session([
                    'payment_gateway' => 'phonepe',
                    'payment_project_id' => $project->id,
                    'payment_milestone' => 'mid',
                    'payment_amount' => $midTotal,
                    'payment_base_amount' => $midBase,
                    'payment_gst_amount' => $midGst,
                    'payment_transaction_id' => $result['transaction_id'] ?? null,
                ]);
                return response()->json([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'order_id' => $result['transaction_id'] ?? null,
                    'message' => 'Redirecting to payment gateway...',
                ]);
            }

            $orderId = 'PAINTUP-' . $project->id . '-MID-' . time();
            $customerName = $project->client_name;
            $customerEmail = $project->customer->email ?? 'customer@example.com';
            $customerPhone = $project->phone;

            $paymentUrl = $this->ccavenue->getPaymentUrl(
                $orderId,
                $midTotal,
                $customerName,
                $customerEmail,
                $customerPhone,
                'mid'
            );

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
            DB::transaction(function () use ($project, $midBase, $midGst, $midTotal) {
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
            'billing_details' => 'nullable|array',
            'billing_details.buying_type' => 'nullable|in:INDIVIDUAL,BUSINESS',
            'billing_details.gstin' => 'nullable|string|size:15',
            'billing_details.business_name' => 'nullable|string|max:255',
            'billing_details.business_address' => 'nullable|string',
            'billing_details.state' => 'nullable|string|max:100',
            'billing_details.pincode' => 'nullable|string|size:6',
        ]);

        // Save billing details if provided
        if ($request->has('billing_details') && !empty($request->billing_details)) {
            $billingData = $request->billing_details;
            if (!empty($billingData['buying_type'])) {
                // For BUSINESS type, GSTIN is required
                if ($billingData['buying_type'] === 'BUSINESS') {
                    if (empty($billingData['gstin']) || !BillingDetail::validateGstin($billingData['gstin'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Valid GSTIN is required for business purchases',
                        ], 422);
                    }
                }

                BillingDetail::updateOrCreate(
                    ['project_id' => $project->id, 'milestone_type' => 'final'],
                    [
                        'buying_type' => $billingData['buying_type'],
                        'gstin' => $billingData['gstin'] ?? null,
                        'business_name' => $billingData['business_name'] ?? null,
                        'business_address' => $billingData['business_address'] ?? null,
                        'state' => $billingData['state'] ?? null,
                        'pincode' => $billingData['pincode'] ?? null,
                    ]
                );
            }
        }

        // Recalculate totals if base_total is missing or zero
        if (empty($project->base_total) || $project->base_total == 0) {
            $project->load(['rooms.items', 'rooms.services']);
            $project->recalculateTotals();
            $project->refresh();
        }

        // Use the project's calculateMilestoneWithGst method for consistency
        $milestoneData = $project->calculateMilestoneWithGst('final');
        $finalBase = $milestoneData['base_amount'];
        $finalGst = $milestoneData['gst_amount'];
        $finalTotal = $milestoneData['total_amount'];

        if ($request->payment_method === 'ONLINE') {
            if (!$this->isOnlinePaymentEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online payment is currently disabled. Please contact support.',
                ], 400);
            }

            if ($finalTotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot process payment: Project total is zero. Please ensure the project has items or services added.',
                ], 400);
            }

            $gateway = $this->getActiveGateway();

            if ($gateway === 'phonepe') {
                $result = $this->paymentGateway->createPayment($project, 'final', $finalTotal);
                if (empty($result['success']) || empty($result['payment_url'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['error'] ?? 'Failed to initiate payment.',
                    ], 400);
                }
                session([
                    'payment_gateway' => 'phonepe',
                    'payment_project_id' => $project->id,
                    'payment_milestone' => 'final',
                    'payment_amount' => $finalTotal,
                    'payment_base_amount' => $finalBase,
                    'payment_gst_amount' => $finalGst,
                    'payment_transaction_id' => $result['transaction_id'] ?? null,
                ]);
                return response()->json([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'order_id' => $result['transaction_id'] ?? null,
                    'message' => 'Redirecting to payment gateway...',
                ]);
            }

            $orderId = 'PAINTUP-' . $project->id . '-FINAL-' . time();
            $customerName = $project->client_name;
            $customerEmail = $project->customer->email ?? 'customer@example.com';
            $customerPhone = $project->phone;

            $paymentUrl = $this->ccavenue->getPaymentUrl(
                $orderId,
                $finalTotal,
                $customerName,
                $customerEmail,
                $customerPhone,
                'final'
            );

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
            DB::transaction(function () use ($project, $finalBase, $finalGst, $finalTotal) {
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
     * Online payment callback - PhonePe (or other gateways) redirect user here after payment
     * GET/POST /customer/payment/callback?gateway=phonepe
     */
    public function paymentCallback(Request $request)
    {
        $gateway = $request->query('gateway') ?? $request->input('gateway', '');

        if ($gateway === 'phonepe') {
            $projectId = session('payment_project_id');
            $milestone = session('payment_milestone');
            $merchantOrderId = session('payment_transaction_id');
            $totalAmount = session('payment_amount');
            $baseAmount = session('payment_base_amount');
            $gstAmount = session('payment_gst_amount');

            if (!$projectId || !$milestone) {
                Log::error('PhonePe callback: Missing session data');
                return redirect()->route('payment.failed')->with('error', 'Payment session expired');
            }

            // V2: verify via Order Status API (redirect does not send response body)
            $status = $this->paymentGateway->getPhonePeOrderStatus($merchantOrderId);
            $state = $status['state'] ?? null;
            if ($state === 'FAILED') {
                Log::warning('PhonePe callback: Payment failed', ['order' => $merchantOrderId]);
                return redirect()->route('payment.failed')->with('error', 'Payment failed or was cancelled.');
            }
            if ($state !== 'COMPLETED') {
                // PENDING or null – treat as not yet paid (user may have closed before paying)
                $verified = $this->paymentGateway->verifyCallback('phonepe', $request->all());
                if (!$verified) {
                    Log::warning('PhonePe callback: Payment not completed', ['order' => $merchantOrderId, 'state' => $state]);
                    return redirect()->route('payment.failed')->with('error', 'Payment was not completed. Please try again.');
                }
            }

            $project = Project::find($projectId);
            if (!$project) {
                Log::error('PhonePe callback: Project not found', ['project_id' => $projectId]);
                return redirect()->route('payment.failed')->with('error', 'Project not found');
            }

            session()->forget([
                'payment_gateway',
                'payment_project_id',
                'payment_milestone',
                'payment_amount',
                'payment_base_amount',
                'payment_gst_amount',
                'payment_transaction_id',
            ]);

            $now = now();
            DB::transaction(function () use ($project, $milestone, $merchantOrderId, $totalAmount, $baseAmount, $gstAmount, $now) {
                $existing = MilestonePayment::where('project_id', $project->id)
                    ->where('milestone_name', $milestone)
                    ->where('payment_reference', $merchantOrderId)
                    ->first();

                if (!$existing) {
                    MilestonePayment::create([
                        'project_id' => $project->id,
                        'milestone_name' => $milestone,
                        'base_amount' => $baseAmount,
                        'gst_amount' => $gstAmount,
                        'total_amount' => $totalAmount,
                        'payment_status' => MilestonePayment::STATUS_PAID,
                        'payment_method' => MilestonePayment::METHOD_ONLINE,
                        'payment_reference' => $merchantOrderId,
                        'paid_at' => $now,
                    ]);
                }

                switch ($milestone) {
                    case 'booking':
                        $project->booking_status = 'PAID';
                        $project->booking_paid_at = $now;
                        $project->booking_reference = $merchantOrderId;
                        $project->mid_status = 'PENDING';
                        $project->status = 'CONFIRMED';
                        break;
                    case 'mid':
                        $project->mid_status = 'PAID';
                        $project->mid_paid_at = $now;
                        $project->mid_reference = $merchantOrderId;
                        $project->final_status = 'PENDING';
                        break;
                    case 'final':
                        $project->final_status = 'PAID';
                        $project->final_paid_at = $now;
                        $project->final_reference = $merchantOrderId;
                        $project->status = 'COMPLETED';
                        break;
                }
                $project->save();
            });

            Log::info('PhonePe payment successful', ['project_id' => $project->id, 'milestone' => $milestone]);
            return redirect()->route('payment.success', $project->id)
                ->with('success', ucfirst($milestone) . ' payment received successfully.');
        }

        return redirect()->route('payment.failed')->with('error', 'Unknown payment gateway');
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

    /**
     * Show Cash Payment Success Page
     * GET /customer/payment/success/{project}/{milestone}
     */
    public function paymentSuccess(Project $project, string $milestone)
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

        // Calculate milestone amounts
        $milestoneData = $project->calculateMilestoneWithGst($milestone);

        return Inertia::render('Customer/PaymentSuccess', [
            'customer' => $customer,
            'project' => $project,
            'milestone' => $milestone,
            'paymentAmount' => $milestoneData['total_amount'],
            'paymentMethod' => 'CASH',
        ]);
    }
}
