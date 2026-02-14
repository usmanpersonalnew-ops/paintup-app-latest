<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Documents\InvoiceGenerator;
use Illuminate\Http\Request;

class CustomerInvoiceController extends Controller
{
    /**
     * View GST Invoice (only after full payment)
     */
    public function view(Project $project, InvoiceGenerator $invoiceGenerator)
    {
        $customer = auth()->user();

        // Verify this project belongs to the customer (by phone)
        if ($project->phone !== $customer->phone) {
            abort(403, 'You do not have permission to view this invoice.');
        }

        // Check if project is fully paid
        if (!$project->isFullyPaid()) {
            abort(403, 'Invoice is available only after full payment.');
        }

        // Generate invoice data using service
        $invoiceData = $invoiceGenerator->generate($project);

        return view('customer.invoice', $invoiceData);
    }
}