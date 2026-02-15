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
        
        $photos = $project->photos()->latest()->get()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'project_id' => $photo->project_id,
                'google_drive_file_id' => $photo->google_drive_file_id,
                'google_drive_link' => $photo->google_drive_link,
                'image_url' => $photo->image_url, // Use the accessor
                'file_name' => $photo->file_name,
                'description' => $photo->description,
                'stage' => $photo->stage,
                'created_at' => $photo->created_at,
                'updated_at' => $photo->updated_at,
            ];
        });
        
        return inertia('Customer/ProjectPhotos', [
            'customer' => $customer,
            'project' => $project,
            'photos' => $photos,
        ]);
    }
}