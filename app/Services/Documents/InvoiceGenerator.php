<?php

namespace App\Services\Documents;

use App\Models\Project;
use App\Models\MilestonePayment;
use App\Models\ProjectRoom;
use Illuminate\Support\Facades\DB;

class InvoiceGenerator
{
    /**
     * Generate invoice data for a project
     */
    public function generate(Project $project): array
    {
        // Get settings using raw query to avoid memory issues
        $settings = $this->getGstSettings();

        // Calculate invoice number
        $invoiceNumber = $this->generateInvoiceNumber($project, $settings);

        // Calculate totals using direct project attributes (avoiding accessor methods)
        $totals = $this->calculateTotals($project);

        // Get milestone payments
        $milestonePayments = $this->getMilestonePayments($project);

        // Get room-wise breakdown using manual queries
        $roomBreakdown = $this->getRoomBreakdown($project);

        // Calculate GST breakdown
        $gstBreakdown = $this->calculateGstBreakdown($totals);

        // Get bank details
        $bankDetails = $this->getBankDetails($settings);

        return [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->format('d/m/Y'),
            'settings' => $settings,
            'project' => $project,
            'totals' => $totals,
            'milestone_payments' => $milestonePayments,
            'room_breakdown' => $roomBreakdown,
            'gst_breakdown' => $gstBreakdown,
            'bank_details' => $bankDetails,
            'amount_in_words' => $this->numberToWords($totals['grand_total']),
        ];
    }

    /**
     * Calculate GST breakdown (CGST/SGST split)
     */
    protected function calculateGstBreakdown(array $totals): array
    {
        $gstRate = 18; // 18% GST
        $halfRate = $gstRate / 2; // 9% each for CGST and SGST

        $taxableValue = $totals['base_total'];
        $cgstAmount = round($taxableValue * ($halfRate / 100), 2);
        $sgstAmount = round($taxableValue * ($halfRate / 100), 2);
        $totalGst = $cgstAmount + $sgstAmount;

        return [
            'gst_rate' => $gstRate,
            'cgst_rate' => $halfRate,
            'sgst_rate' => $halfRate,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'total_gst' => $totalGst,
            'taxable_value' => $taxableValue,
        ];
    }

    /**
     * Get bank details from settings
     */
    protected function getBankDetails(array $settings): array
    {
        return [
            'bank_name' => $settings['bank_name'] ?? '',
            'account_number' => $settings['account_number'] ?? '',
            'ifsc_code' => $settings['ifsc_code'] ?? '',
            'branch' => $settings['branch'] ?? '',
        ];
    }

    /**
     * Convert number to words (Indian Rupees)
     */
    protected function numberToWords(float $number): string
    {
        $number = round($number, 2);
        $integerPart = floor($number);
        $decimalPart = round(($number - $integerPart) * 100);

        $words = $this->convertToWords($integerPart);
        $result = $words . ' Rupees';

        if ($decimalPart > 0) {
            $decimalWords = $this->convertToWords($decimalPart);
            $result .= ' and ' . $decimalWords . ' Paise';
        }

        return $result . ' Only';
    }

    /**
     * Helper to convert number to words
     */
    protected function convertToWords(int $number): string
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen'
        ];

        $tens = [
            0 => '', 1 => '', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty',
            5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $illions = [
            ['value' => 10000000, 'name' => 'Crore'],
            ['value' => 100000, 'name' => 'Lakh'],
            ['value' => 1000, 'name' => 'Thousand'],
            ['value' => 100, 'name' => 'Hundred'],
        ];

        if ($number == 0) {
            return 'Zero';
        }

        $result = '';

        foreach ($illions as $illion) {
            if ($number >= $illion['value']) {
                $count = intdiv($number, $illion['value']);
                if ($illion['value'] >= 100) {
                    $result .= $this->convertToWords($count) . ' ' . $illion['name'] . ' ';
                } else {
                    $result .= $ones[$count] . ' ' . $illion['name'] . ' ';
                }
                $number = $number % $illion['value'];
            }
        }

        if ($number >= 20) {
            $result .= $tens[intdiv($number, 10)] . ' ' . $ones[$number % 10];
        } else {
            $result .= $ones[$number];
        }

        return trim($result);
    }

    /**
     * Get GST settings from settings table
     */
    protected function getGstSettings(): array
    {
        $settings = DB::table('settings')->first();

        return [
            'company_name' => $settings->company_name ?? 'PaintUp',
            'logo_path' => $settings->logo_path ?? '',
            'gst_number' => $settings->gst_number ?? '',
            'address' => $settings->address ?? '',
            'invoice_prefix' => $settings->invoice_prefix ?? 'INV',
            'support_email' => $settings->support_email ?? '',
            'support_whatsapp' => $settings->support_whatsapp ?? '',
        ];
    }

    /**
     * Generate invoice number
     */
    protected function generateInvoiceNumber(Project $project, array $settings): string
    {
        $prefix = $settings['invoice_prefix'] ?? 'INV';
        $year = now()->format('Y');
        return $prefix . '-' . $project->id . '-' . $year;
    }

    /**
     * Calculate totals using direct project attributes (avoiding accessor methods)
     */
    protected function calculateTotals(Project $project): array
    {
        $bookingAmount = $project->booking_amount ?? 0;
        $midAmount = $project->mid_amount ?? 0;
        $finalAmount = $project->final_amount ?? 0;

        $bookingGst = $project->booking_gst ?? 0;
        $midGst = $project->mid_gst ?? 0;
        $finalGst = $project->final_gst ?? 0;

        $baseTotal = $bookingAmount + $midAmount + $finalAmount;
        $totalGst = $bookingGst + $midGst + $finalGst;
        $grandTotal = $baseTotal + $totalGst;

        return [
            'booking_amount' => $bookingAmount,
            'booking_gst' => $bookingGst,
            'booking_total' => $bookingAmount + $bookingGst,
            'mid_amount' => $midAmount,
            'mid_gst' => $midGst,
            'mid_total' => $midAmount + $midGst,
            'final_amount' => $finalAmount,
            'final_gst' => $finalGst,
            'final_total' => $finalAmount + $finalGst,
            'base_total' => $baseTotal,
            'total_gst' => $totalGst,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Get milestone payment history
     */
    protected function getMilestonePayments(Project $project): array
    {
        $payments = MilestonePayment::where('project_id', $project->id)
            ->where('payment_status', MilestonePayment::STATUS_PAID)
            ->orderBy('paid_at')
            ->get();

        return $payments->map(function ($payment) {
            return [
                'milestone' => $payment->milestone_type,
                'amount' => $payment->base_amount,
                'gst' => $payment->gst_amount,
                'total' => $payment->total_amount,
                'paid_at' => $payment->paid_at?->format('d M Y'),
            ];
        })->toArray();
    }

    /**
     * Get room-wise breakdown using manual queries (avoiding relationship loading)
     */
    protected function getRoomBreakdown(Project $project): array
    {
        $breakdown = [];

        // Get rooms using simple query (no relationships)
        $rooms = ProjectRoom::where('project_id', $project->id)
            ->select(['id', 'name'])
            ->get();

        foreach ($rooms as $room) {
            $roomData = [
                'name' => $room->name,
                'items' => [],
                'services' => [],
                'total' => 0,
            ];

            // Get paint items for this room using manual join
            // Note: QuoteItem uses 'project_room_id' column
            $items = DB::table('quote_items as qi')
                ->join('master_surfaces as ms', 'qi.surface_id', '=', 'ms.id')
                ->leftJoin('master_products as mp', 'qi.master_product_id', '=', 'mp.id')
                ->leftJoin('master_painting_systems as mps', 'qi.master_system_id', '=', 'mps.id')
                ->where('qi.project_room_id', $room->id)
                ->select([
                    'ms.name as surface_name',
                    'mp.name as product_name',
                    'mps.system_name as system_name',
                    'qi.qty',
                    'qi.rate',
                    'qi.amount'
                ])
                ->get();

            foreach ($items as $item) {
                $itemTotal = (float) ($item->amount ?? 0);
                $roomData['items'][] = [
                    'surface' => $item->surface_name ?? 'Unknown Surface',
                    'product' => $item->product_name ?? 'Unknown Product',
                    'system' => $item->system_name ?? 'Unknown System',
                    'qty' => (float) ($item->qty ?? 0),
                    'rate' => (float) ($item->rate ?? 0),
                    'amount' => $itemTotal,
                ];
                $roomData['total'] += $itemTotal;
            }

            // Get services for this room using manual join
            // Note: QuoteService uses 'project_room_id' column
            $services = DB::table('quote_services as qs')
                ->leftJoin('master_services as ms', 'qs.master_service_id', '=', 'ms.id')
                ->where('qs.project_room_id', $room->id)
                ->select([
                    'ms.name as service_name',
                    'qs.custom_name',
                    'qs.quantity',
                    'qs.rate',
                    'qs.amount'
                ])
                ->get();

            foreach ($services as $service) {
                $serviceTotal = (float) ($service->amount ?? 0);
                $roomData['services'][] = [
                    'name' => $service->service_name ?? $service->custom_name ?? 'Service',
                    'quantity' => (float) ($service->quantity ?? 0),
                    'rate' => (float) ($service->rate ?? 0),
                    'amount' => $serviceTotal,
                ];
                $roomData['total'] += $serviceTotal;
            }

            $breakdown[] = $roomData;
        }

        return $breakdown;
    }
}
