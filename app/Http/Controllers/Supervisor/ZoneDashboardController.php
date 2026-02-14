<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ProjectRoom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZoneDashboardController extends Controller
{
    /**
     * Screen B: Zone Dashboard (Hub)
     * Route: /supervisor/zones/{projectRoom}
     */
    public function index(ProjectRoom $projectRoom)
    {
        \Log::info('RUNTIME_CHECK', ['time' => now()]);
        
        // Eager load relationships for paint items, services, media
        $projectRoom->load([
            'items.surface',
            'items.product',
            'items.system',
            'services.masterService',
            'media',
            'project',
        ]);

        // Ensure custom_name is populated from masterService for display
        foreach ($projectRoom->services as $service) {
            if (empty($service->custom_name) && $service->masterService) {
                $service->custom_name = $service->masterService->name;
            }
        }

        // Return zone with all related data for the dashboard
        return Inertia::render('Supervisor/Zones/Dashboard', [
            'zone' => $projectRoom,
        ]);
    }
}
