<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Documents\InvoiceGenerator;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerInvoiceController extends Controller
{
    /**
     * View GST Invoice (only after full payment)
     */
    public function view(Project $project, InvoiceGenerator $invoiceGenerator)
    {
        $customer = auth('customer')->user();

        // Verify this project belongs to the customer (by phone)
        if (!$customer || $project->phone !== $customer->phone) {
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

    /**
     * Download GST Invoice as PDF (only after full payment)
     */
    public function download(Project $project, InvoiceGenerator $invoiceGenerator)
    {
        $customer = auth('customer')->user();

        // Verify this project belongs to the customer (by phone)
        if (!$customer || $project->phone !== $customer->phone) {
            abort(403, 'You do not have permission to download this invoice.');
        }

        // Check if project is fully paid
        if (!$project->isFullyPaid()) {
            abort(403, 'Invoice is available only after full payment.');
        }

        // Generate invoice data using service
        $invoiceData = $invoiceGenerator->generate($project);

        // Generate PDF using PDF-specific view (without download button)
        $pdf = Pdf::loadView('customer.invoice-pdf', $invoiceData)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 5)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 5);

        // Generate filename
        $filename = 'GST_Invoice_' . $invoiceData['invoice_number'] . '.pdf';

        return $pdf->download($filename);
    }
}
