<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Generate GST Invoice PDF for a project
     * Only available after full payment
     */
    public function generateInvoice(Project $project)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['ADMIN', 'SUPERVISOR'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Check if all payments are completed
        if ($project->final_status !== 'PAID') {
            return response()->json([
                'success' => false, 
                'message' => 'Invoice can only be generated after full payment'
            ], 400);
        }

        // Calculate GST breakdown
        $subtotal = $project->total_amount;
        $gstRate = 18;
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $taxableAmount = $subtotal;
        $cgstAmount = round($gstAmount / 2, 2);
        $sgstAmount = $gstAmount - $cgstAmount;
        $grandTotal = $subtotal + $gstAmount;

        // Get customer details
        $customer = $project->customer;

        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($project->id, 5, '0', STR_PAD_LEFT);

        // Prepare data for PDF
        $data = [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->format('d/m/Y'),
            'project' => $project,
            'customer' => $customer,
            'company_details' => [
                'name' => 'PaintUp (Revamp Homes)',
                'address' => 'Mumbai, Maharashtra',
                'gst_number' => '27ABCDE1234F1Z5',
                'phone' => '+91 98765 43210',
                'email' => 'info@paintup.in',
            ],
            'subtotal' => $taxableAmount,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'grand_total' => $grandTotal,
            'amount_in_words' => $this->numberToWords($grandTotal),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.gst-invoice', $data);
        
        // Store invoice details in project
        $project->invoice_number = $invoiceNumber;
        $project->invoice_generated_at = now();
        $project->save();

        return $pdf->download("GST_Invoice_{$invoiceNumber}.pdf");
    }

    /**
     * View Invoice details (for admin preview)
     */
    public function viewInvoice(Project $project)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['ADMIN', 'SUPERVISOR'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        if ($project->final_status !== 'PAID') {
            return response()->json([
                'success' => false, 
                'message' => 'Invoice can only be viewed after full payment'
            ], 400);
        }

        // Calculate GST breakdown
        $subtotal = $project->total_amount;
        $gstRate = 18;
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $taxableAmount = $subtotal;
        $cgstAmount = round($gstAmount / 2, 2);
        $sgstAmount = $gstAmount - $cgstAmount;
        $grandTotal = $subtotal + $gstAmount;

        $customer = $project->customer;
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($project->id, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'invoice' => [
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->format('d/m/Y'),
                'project' => $project,
                'customer' => $customer,
                'company_details' => [
                    'name' => 'PaintUp (Revamp Homes)',
                    'address' => 'Mumbai, Maharashtra',
                    'gst_number' => '27ABCDE1234F1Z5',
                    'phone' => '+91 98765 43210',
                    'email' => 'info@paintup.in',
                ],
                'subtotal' => $taxableAmount,
                'gst_rate' => $gstRate,
                'gst_amount' => $gstAmount,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'grand_total' => $grandTotal,
                'amount_in_words' => $this->numberToWords($grandTotal),
            ],
        ]);
    }

    /**
     * Regenerate Invoice
     */
    public function regenerateInvoice(Project $project)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Admin only'], 403);
        }

        if ($project->final_status !== 'PAID') {
            return response()->json([
                'success' => false, 
                'message' => 'Invoice can only be regenerated after full payment'
            ], 400);
        }

        // Reset invoice details
        $project->invoice_generated_at = null;
        $project->save();

        return $this->generateInvoice($project);
    }

    /**
     * Convert number to words (Indian Rupees)
     */
    private function numberToWords($number)
    {
        $number = round($number);
        $words = '';
        
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        $lakhs = floor($number / 100000);
        $number = $number % 100000;
        
        if ($lakhs > 0) {
            $words .= $this->convertTwoDigit($lakhs) . ' Lakh ';
        }
        
        $thousands = floor($number / 1000);
        $number = $number % 1000;
        
        if ($thousands > 0) {
            $words .= $this->convertTwoDigit($thousands) . ' Thousand ';
        }
        
        $hundreds = floor($number / 100);
        $number = $number % 100;
        
        if ($hundreds > 0) {
            $words .= $ones[$hundreds] . ' Hundred ';
        }
        
        if ($number > 0) {
            $words .= $this->convertTwoDigit($number);
        }
        
        return trim($words) . ' Rupees Only';
    }
    
    private function convertTwoDigit($number)
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        if ($number < 20) {
            return $ones[$number];
        }
        
        return $tens[floor($number / 10)] . ' ' . $ones[$number % 10];
    }
}
