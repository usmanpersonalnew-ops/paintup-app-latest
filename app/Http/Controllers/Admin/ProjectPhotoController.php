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
        $photos = $project->photos()->with('uploadedBy')->latest()->get();

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