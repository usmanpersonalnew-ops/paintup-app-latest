<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Documents\InvoiceGenerator;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    /**
     * View Invoice for Admin
     */
    public function view(Project $project, InvoiceGenerator $invoiceGenerator)
    {
        // Generate invoice data using service
        $invoiceData = $invoiceGenerator->generate($project);

        return view('customer.invoice', $invoiceData);
    }
}
