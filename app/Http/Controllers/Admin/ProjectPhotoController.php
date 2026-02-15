<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPhoto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectPhotoController extends Controller
{
    /**
     * Show project photos for admin
     */
    public function index(Project $project)
    {
        $photos = $project->photos()->with('uploadedBy')->latest()->get()->map(function ($photo) {
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
                'uploaded_by' => $photo->uploadedBy,
            ];
        });

        return Inertia::render('Admin/ProjectPhotos', [
            'project' => $project,
            'photos' => $photos,
        ]);
    }

    /**
     * Delete a photo (admin only)
     */
    public function destroy(Project $project, ProjectPhoto $photo)
    {
        // Verify photo belongs to project
        if ($photo->project_id !== $project->id) {
            abort(403);
        }

        // Delete from database
        $photo->delete();

        return back()->with('success', 'Photo deleted successfully!');
    }
}