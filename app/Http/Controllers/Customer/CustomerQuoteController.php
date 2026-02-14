<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerQuoteController extends Controller
{
    /**
     * Show quote to authenticated customer (uses project ID, verifies ownership)
     */
    public function show($projectId)
    {
        // Get authenticated customer
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('customer.login');
        }

        // Find project and verify ownership by phone number
        $project = Project::where('id', $projectId)
            ->where('phone', $customer->phone) // Only show customer's own projects
            ->with(['rooms.items.surface', 'rooms.items.product', 'rooms.items.system', 'rooms.services.masterService'])
            ->first();

        if (!$project) {
            abort(404, 'Quote not found or access denied');
        }

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
                    }
                    else {
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

        // Check if customer is logged in
        $isLoggedIn = Auth::guard('customer')->check();
        $customer = $isLoggedIn ? Auth::guard('customer')->user() : null;

        return Inertia::render('Customer/QuoteView', [
            'customer' => $customer,
            'project' => $project,
            'totals' => [
                'paint' => $totalPaintAmount,
                'services' => $totalServiceAmount,
                'base_total' => $baseTotal,
                'booking_amount' => $bookingAmount,
                'mid_payment_amount' => $midPaymentAmount,
                'final_payment_amount' => $finalPaymentAmount,
            ],
            'isLoggedIn' => $isLoggedIn,
        ]);
    }
}
