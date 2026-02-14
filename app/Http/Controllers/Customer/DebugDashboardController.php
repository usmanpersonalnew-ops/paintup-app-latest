<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DebugDashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $projects = Project::where('phone', $customer->phone)
            ->with(['rooms.items.surface', 'rooms.items.product', 'rooms.services.masterService'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Return just the first room's services for debugging
        $debugData = [];
        foreach ($projects as $project) {
            foreach ($project->rooms as $room) {
                $debugData[] = [
                    'project_id' => $project->id,
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'services' => $room->services->map(function($s) {
                        return [
                            'id' => $s->id,
                            'quantity' => $s->quantity,
                            'rate' => $s->rate,
                            'amount' => $s->amount,
                        ];
                    }),
                ];
            }
        }
        
        return response()->json([
            'customer_phone' => $customer->phone,
            'debug_data' => $debugData,
        ]);
    }
}