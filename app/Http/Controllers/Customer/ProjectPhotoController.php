<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectPhotoController extends Controller
{
    /**
     * Show project photos for authenticated customer (read-only)
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
            ->firstOrFail();
        
        $photos = $project->photos()->latest()->get();
        
        return inertia('Customer/ProjectPhotos', [
            'customer' => $customer,
            'project' => $project,
            'photos' => $photos,
        ]);
    }
}