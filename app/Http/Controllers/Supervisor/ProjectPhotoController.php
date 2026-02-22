<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPhoto;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProjectPhotoController extends Controller
{
    /**
     * Show project photos page
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

        return Inertia::render('Supervisor/ProjectPhotos', [
            'project' => $project,
            'photos' => $photos,
        ]);
    }

    /**
     * Upload photos to Google Drive
     */
    public function store(Request $request, Project $project)
    {
        Log::info('=== PHOTO UPLOAD DEBUG START ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Project ID: ' . $project->id);
        
        // Check if files are present
        $allInputs = $request->all();
        Log::info('All inputs keys: ' . implode(', ', array_keys($allInputs)));
        Log::info('Files count: ' . count($request->file('photos') ?? []));
        Log::info('Has photos input: ' . ($request->has('photos') ? 'yes' : 'no'));
        
        // Validate
        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'stage' => 'required|in:before,in-progress,after',
        ], [
            'photos.required' => 'Please select at least one photo to upload.',
            'photos.min' => 'Please select at least one photo to upload.',
            'photos.*.image' => 'Each file must be an image (JPEG, PNG, JPG or GIF).',
            'photos.*.mimes' => 'Each file must be JPEG, PNG, JPG or GIF.',
            'photos.*.max' => 'Each image must be 10MB or smaller.',
        ]);
        
        Log::info('Validation passed');
        Log::info('Stage: ' . $request->input('stage'));
        Log::info('Photos count: ' . count($request->file('photos')));

        $uploadedPhotos = [];
        $stage = $request->input('stage', 'before');

        try {
            $driveService = new GoogleDriveService();
            Log::info('GoogleDriveService initialized');

            foreach ($request->file('photos') as $index => $photo) {
                Log::info("Processing photo {$index}: " . $photo->getClientOriginalName());
                
                // Upload to Google Drive with stage
                Log::info('Uploading to Google Drive...');
                $driveResult = $driveService->uploadUploadedFile($photo, $project->id, $stage);
                Log::info('Google Drive upload complete. File ID: ' . $driveResult['id']);

                // Save to database
                Log::info('Saving to database...');
                $projectPhoto = ProjectPhoto::create([
                    'project_id' => $project->id,
                    'uploaded_by_type' => 'App\Models\User',
                    'uploaded_by_id' => Auth::id(),
                    'google_drive_file_id' => $driveResult['id'],
                    'google_drive_link' => $driveResult['link'],
                    'file_name' => $photo->getClientOriginalName(),
                    'stage' => $stage,
                ]);
                Log::info('Database record created. Photo ID: ' . $projectPhoto->id);

                $uploadedPhotos[] = $projectPhoto;
            }

            Log::info('=== PHOTO UPLOAD DEBUG END (SUCCESS) ===');
            return back()->with('success', count($uploadedPhotos) . ' photo(s) uploaded successfully!');

        } catch (\Exception $e) {
            Log::error('=== PHOTO UPLOAD DEBUG END (ERROR) ===');
            Log::error('Photo upload failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->with('error', 'Failed to upload photos: ' . $e->getMessage());
        }
    }

    /**
     * Delete a photo
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