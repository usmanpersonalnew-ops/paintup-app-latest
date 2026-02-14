<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupervisorProjectWorkController extends Controller
{
    /**
     * Get allowed status transitions for supervisor
     */
    private function getAllowedTransitions(): array
    {
        return [
            Project::WORK_STATUS_ASSIGNED => Project::WORK_STATUS_IN_PROGRESS,
            Project::WORK_STATUS_IN_PROGRESS => Project::WORK_STATUS_ON_HOLD,
            Project::WORK_STATUS_ON_HOLD => Project::WORK_STATUS_IN_PROGRESS,
            Project::WORK_STATUS_IN_PROGRESS => Project::WORK_STATUS_COMPLETED,
        ];
    }

    /**
     * Update work status for a project (Supervisor has limited access)
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

        // Check if transition is allowed
        $allowedTransitions = $this->getAllowedTransitions();
        $allowedNextStatus = $allowedTransitions[$oldStatus] ?? 'N/A';
        
        // Supervisor can only transition from current status to allowed next status
        if (!isset($allowedTransitions[$oldStatus]) || $allowedTransitions[$oldStatus] !== $newStatus) {
            return back()->with('error', "Cannot transition from '{$oldStatus}' to '{$newStatus}'. Allowed transition: '{$oldStatus}' → '{$allowedNextStatus}'");
        }

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

        $project->update($updateData);

        Log::info('Supervisor updated work status', [
            'project_id' => $project->id,
            'supervisor_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return back()->with('success', "Work status updated to '{$newStatus}'");
    }

    /**
     * Get available actions for current status
     */
    public function getAvailableActions(Project $project): array
    {
        $currentStatus = $project->work_status;
        $allowedTransitions = $this->getAllowedTransitions();
        
        $actions = [];
        
        if (isset($allowedTransitions[$currentStatus])) {
            $nextStatus = $allowedTransitions[$currentStatus];
            
            $actions[] = [
                'status' => $nextStatus,
                'label' => $this->getActionLabel($nextStatus),
                'color' => $this->getActionColor($nextStatus),
            ];
        }

        return $actions;
    }

    /**
     * Get action label for status
     */
    private function getActionLabel(string $status): string
    {
        return match($status) {
            Project::WORK_STATUS_IN_PROGRESS => 'Start Work',
            Project::WORK_STATUS_ON_HOLD => 'Pause Work',
            Project::WORK_STATUS_COMPLETED => 'Mark Completed',
            default => ucfirst(strtolower(str_replace('_', ' ', $status))),
        };
    }

    /**
     * Get action color for status
     */
    private function getActionColor(string $status): string
    {
        return match($status) {
            Project::WORK_STATUS_IN_PROGRESS => 'green',
            Project::WORK_STATUS_ON_HOLD => 'yellow',
            Project::WORK_STATUS_COMPLETED => 'blue',
            default => 'gray',
        };
    }
}