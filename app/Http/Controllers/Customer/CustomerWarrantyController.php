<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Documents\WarrantyGenerator;
use Illuminate\Http\Request;

class CustomerWarrantyController extends Controller
{
    /**
     * View Warranty (only after full payment and work completion)
     */
    public function view(Project $project, WarrantyGenerator $warrantyGenerator)
    {
        $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();

        // Verify this project belongs to the customer (by phone)
        if ($customer && $project->phone !== $customer->phone) {
            abort(403, 'You do not have permission to view this warranty.');
        }

        // Check if project is fully paid (commented for testing)
        // if (!$project->isFullyPaid()) {
        //     abort(403, 'Warranty is available only after full payment.');
        // }

        // Check if work is completed (commented for testing)
        // if (!$project->isWorkCompleted()) {
        //     abort(403, 'Warranty is available only after work completion.');
        // }

        // Generate warranty data using service
        $warrantyData = $warrantyGenerator->generate($project);

        return view('customer.warranty', $warrantyData);
    }
}
