<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminProjectWorkController extends Controller
{
    /**
     * Update work status for a project (Admin has full access)
     */
    public function updateStatus(Request $request, Project $project)
    {
        $request->validate([
            'work_status' => [
                'required',
                'string',
                'in:PENDING,ASSIGNED,IN_PROGRESS,ON_HOLD,COMPLETED,CLOSED'
            ],
        ]);

        $oldStatus = $project->work_status;
        $newStatus = $request->work_status;

        // Admin can update to any status
        $updateData = [
            'work_status' => $newStatus,
        ];

        // Set timestamps based on status change
        if ($newStatus === Project::WORK_STATUS_IN_PROGRESS && $oldStatus !== Project::WORK_STATUS_IN_PROGRESS) {
            $updateData['work_started_at'] = now();
        }

        if ($newStatus === Project::WORK_STATUS_COMPLETED && $oldStatus !== Project::WORK_STATUS_COMPLETED) {
            $updateData['work_completed_at'] = now();
        }

        // If moving away from IN_PROGRESS, clear work_started_at
        if ($oldStatus === Project::WORK_STATUS_IN_PROGRESS && $newStatus !== Project::WORK_STATUS_IN_PROGRESS) {
            // Keep the timestamp for audit purposes
        }

        $project->update($updateData);

        Log::info('Admin updated work status', [
            'project_id' => $project->id,
            'admin_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return back()->with('success', "Work status updated from '{$oldStatus}' to '{$newStatus}'");
    }
}