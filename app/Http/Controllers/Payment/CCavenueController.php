<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\MilestonePayment;
use App\Models\Project;
use App\Services\CCavenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CCavenueController extends Controller
{
    protected $ccavenue;

    public function __construct(CCavenueService $ccavenue)
    {
        $this->ccavenue = $ccavenue;
    }

    /**
     * Initiate payment for a project milestone
     */
    public function initiate(Request $request, Project $project, string $milestone)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = $request->amount;

        // Validate milestone
        $validMilestones = ['booking', 'mid', 'final'];
        if (!in_array($milestone, $validMilestones)) {
            return response()->json(['success' => false, 'message' => 'Invalid milestone'], 400);
        }

        // Check if milestone is payable
        if ($milestone === 'booking' && $project->booking_status === 'PAID') {
            return response()->json(['success' => false, 'message' => 'Booking already paid'], 400);
        }
        if ($milestone === 'mid' && $project->mid_status === 'PAID') {
            return response()->json(['success' => false, 'message' => 'Mid payment already paid'], 400);
        }
        if ($milestone === 'final' && $project->final_status === 'PAID') {
            return response()->json(['success' => false, 'message' => 'Final payment already paid'], 400);
        }

        // Generate order ID
        $orderId = 'PAINTUP-' . $project->id . '-' . strtoupper($milestone) . '-' . time();

        // Get customer info
        $customerName = $project->client_name;
        $customerEmail = $project->customer->email ?? 'customer@example.com';
        $customerPhone = $project->phone;

        // Generate payment URL
        $paymentUrl = $this->ccavenue->getPaymentUrl(
            $orderId,
            $amount,
            $customerName,
            $customerEmail,
            $customerPhone,
            $milestone
        );

        // Store payment info in session
        session([
            'ccavenue_order_id' => $orderId,
            'ccavenue_project_id' => $project->id,
            'ccavenue_milestone' => $milestone,
            'ccavenue_amount' => $amount,
        ]);

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
            'order_id' => $orderId,
        ]);
    }

    /**
     * CCavenue callback URL - called after payment
     */
    public function callback(Request $request)
    {
        $encResponse = $request->encResp;
        $orderId = $request->orderId;

        Log::info('CCavenue callback received', [
            'order_id' => $orderId,
            'enc_resp' => substr($encResponse ?? '', 0, 100) . '...',
        ]);

        if (!$encResponse) {
            Log::error('CCavenue callback: No encResp received');
            return redirect()->route('payment.failed')->with('error', 'No response from payment gateway');
        }

        try {
            // Decrypt the response
            $decryptedResponse = $this->ccavenue->decrypt($encResponse);
            parse_str($decryptedResponse, $responseParams);

            Log::info('CCavenue decrypted response', $responseParams);

            $orderId = $responseParams['order_id'] ?? null;
            $trackingId = $responseParams['tracking_id'] ?? null;
            $bankRefNo = $responseParams['bank_ref_no'] ?? null;
            $paymentStatus = $responseParams['order_status'] ?? null;
            $failureMessage = $responseParams['failure_message'] ?? null;
            $paymentMode = $responseParams['payment_mode'] ?? null;
            $cardName = $responseParams['card_name'] ?? null;

            // Get payment info from session
            $projectId = session('ccavenue_project_id');
            $milestone = session('ccavenue_milestone');
            $amount = session('ccavenue_amount');

            if (!$projectId || !$milestone) {
                Log::error('CCavenue callback: Missing session data');
                return redirect()->route('payment.failed')->with('error', 'Payment session expired');
            }

            $project = Project::find($projectId);

            if (!$project) {
                Log::error('CCavenue callback: Project not found', ['project_id' => $projectId]);
                return redirect()->route('payment.failed')->with('error', 'Project not found');
            }

            // Clear session data
            session()->forget([
                'ccavenue_order_id',
                'ccavenue_project_id',
                'ccavenue_milestone',
                'ccavenue_amount',
                'ccavenue_base_amount',
                'ccavenue_gst_amount',
            ]);

            if ($paymentStatus === 'Success') {
                // Update project payment status
                $this->updatePaymentStatus($project, $milestone, $orderId, $trackingId, $bankRefNo, $paymentMode, $cardName);

                Log::info('CCavenue payment successful', [
                    'project_id' => $project->id,
                    'milestone' => $milestone,
                    'order_id' => $orderId,
                ]);

                return redirect()->route('payment.success', $project->id)
                    ->with('success', 'Payment successful! ' . ucfirst($milestone) . ' payment has been received.');
            } else {
                // Payment failed
                Log::warning('CCavenue payment failed', [
                    'project_id' => $project->id,
                    'milestone' => $milestone,
                    'order_id' => $orderId,
                    'message' => $failureMessage,
                ]);

                return redirect()->route('payment.failed')
                    ->with('error', 'Payment failed: ' . ($failureMessage ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('CCavenue callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }

    /**
     * Cancel URL - called when user cancels payment
     */
    public function cancel(Request $request)
    {
        Log::info('CCavenue payment cancelled', [
            'order_id' => $request->orderId,
        ]);

        return redirect()->route('payment.failed')
            ->with('error', 'Payment was cancelled. Please try again.');
    }

    /**
     * Update payment status in database and create MilestonePayment record
     */
    protected function updatePaymentStatus(Project $project, string $milestone, string $orderId, string $trackingId = null, string $bankRefNo = null, string $paymentMode = null, string $cardName = null)
    {
        $now = now();

        // Get amounts from session or calculate
        $baseAmount = session('ccavenue_base_amount', 0);
        $gstAmount = session('ccavenue_gst_amount', 0);
        $totalAmount = session('ccavenue_amount', 0);

        DB::transaction(function () use ($project, $milestone, $orderId, $trackingId, $bankRefNo, $paymentMode, $cardName, $now, $baseAmount, $gstAmount, $totalAmount) {

            // Check if payment already exists for this order ID to prevent duplicates
            $existingPayment = MilestonePayment::where('project_id', $project->id)
                ->where('milestone_name', $milestone)
                ->where('payment_reference', $orderId)
                ->first();

            if ($existingPayment) {
                // Payment already processed, just update project status if needed
                Log::info('CCavenue: Payment already exists, skipping duplicate creation', [
                    'payment_id' => $existingPayment->id,
                    'order_id' => $orderId,
                ]);
            } else {
                // Create MilestonePayment record
                MilestonePayment::create([
                    'project_id' => $project->id,
                    'milestone_name' => $milestone,
                    'base_amount' => $baseAmount,
                    'gst_amount' => $gstAmount,
                    'total_amount' => $totalAmount,
                    'payment_status' => MilestonePayment::STATUS_PAID,
                    'payment_method' => MilestonePayment::METHOD_ONLINE,
                    'payment_reference' => $orderId,
                    'tracking_id' => $trackingId,
                    'bank_ref_no' => $bankRefNo,
                    'payment_mode' => $paymentMode,
                    'card_name' => $cardName,
                    'paid_at' => $now,
                ]);
            }

            // Update project status
            switch ($milestone) {
                case 'booking':
                    $project->booking_status = 'PAID';
                    $project->booking_paid_at = $now;
                    $project->booking_reference = $orderId;
                    $project->booking_tracking_id = $trackingId;
                    $project->booking_bank_ref = $bankRefNo;
                    $project->booking_payment_mode = $paymentMode;
                    $project->mid_status = 'PENDING';
                    $project->status = 'CONFIRMED';
                    break;

                case 'mid':
                    $project->mid_status = 'PAID';
                    $project->mid_paid_at = $now;
                    $project->mid_reference = $orderId;
                    $project->mid_tracking_id = $trackingId;
                    $project->mid_bank_ref = $bankRefNo;
                    $project->mid_payment_mode = $paymentMode;
                    $project->final_status = 'PENDING';
                    break;

                case 'final':
                    $project->final_status = 'PAID';
                    $project->final_paid_at = $now;
                    $project->final_reference = $orderId;
                    $project->final_tracking_id = $trackingId;
                    $project->final_bank_ref = $bankRefNo;
                    $project->final_payment_mode = $paymentMode;
                    $project->status = 'COMPLETED';
                    break;
            }

            $project->save();
        });
    }

    /**
     * Payment success page – used after CCAvenue or PhonePe payment.
     * Redirects to customer dashboard with success message.
     */
    public function success(Project $project)
    {
        $message = session('success', 'Payment received successfully.');

        if (auth()->guard('customer')->check()) {
            return redirect()->route('customer.dashboard')->with('success', $message);
        }

        return redirect()->route('customer.login')->with('success', $message);
    }

    /**
     * Payment failed page – used when payment fails or is cancelled.
     */
    public function failed()
    {
        $error = session('error', 'Payment could not be completed.');

        if (auth()->guard('customer')->check()) {
            return redirect()->route('customer.dashboard')->with('error', $error);
        }

        return redirect()->route('customer.login')->with('error', $error);
    }

    /**
     * Get payment configuration for frontend
     */
    public function getConfig()
    {
        return response()->json([
            'merchant_id' => $this->ccavenue->getMerchantId(),
            'access_code' => $this->ccavenue->getAccessCode(),
            'is_test_mode' => $this->ccavenue->isTestMode(),
        ]);
    }
}
