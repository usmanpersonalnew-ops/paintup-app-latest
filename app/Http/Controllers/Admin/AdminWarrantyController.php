<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Documents\WarrantyGenerator;
use Illuminate\Http\Request;

class AdminWarrantyController extends Controller
{
    /**
     * View Warranty for Admin
     */
    public function view(Project $project, WarrantyGenerator $warrantyGenerator)
    {
        // Generate warranty data using service
        $warrantyData = $warrantyGenerator->generate($project);

        return view('customer.warranty', $warrantyData);
    }
}