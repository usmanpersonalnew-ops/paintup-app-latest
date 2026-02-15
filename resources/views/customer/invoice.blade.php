<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $invoice_number }}</title>
    @php
    use Illuminate\Support\Facades\Storage;
    $branding = \App\Models\Setting::getSettings();

    // Get logo for PDF (DomPDF works best with base64 encoded images)
    $logoDataUri = null;
    $logoUrl = null;
    if (!empty($branding->logo_path)) {
        // Try to get absolute file path
        $logoPath = storage_path('app/public/' . $branding->logo_path);
        if (!file_exists($logoPath)) {
            $logoPath = public_path('storage/' . $branding->logo_path);
        }

        // If file exists, encode as base64 for PDF
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $imageInfo = getimagesize($logoPath);
            $mimeType = $imageInfo['mime'] ?? 'image/png';
            $logoDataUri = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        // Also get URL for web view
        $logoUrl = Storage::url($branding->logo_path);
    }

    // Get signature for PDF (DomPDF works best with base64 encoded images)
    $signatureDataUri = null;
    $signatureUrl = null;
    if (!empty($branding->signature_path)) {
        // Try to get absolute file path
        $signaturePath = storage_path('app/public/' . $branding->signature_path);
        if (!file_exists($signaturePath)) {
            $signaturePath = public_path('storage/' . $branding->signature_path);
        }

        // If file exists, encode as base64 for PDF
        if (file_exists($signaturePath)) {
            $imageData = file_get_contents($signaturePath);
            $imageInfo = getimagesize($signaturePath);
            $mimeType = $imageInfo['mime'] ?? 'image/png';
            $signatureDataUri = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        // Also get URL for web view
        $signatureUrl = Storage::url($branding->signature_path);
    }
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }
        .invoice-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .company-left {

            padding: 12px 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .company-logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .company-details {
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
        }
        .company-details p {
            margin-bottom: 4px;
        }
        .company-details p:last-child {
            margin-bottom: 0;
        }
        .invoice-right {


            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .invoice-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            text-align: center;
        }
        .invoice-meta-info {


            padding: 10px 15px;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .invoice-meta-info p {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .invoice-meta-info p:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
        .invoice-meta-info strong {
            font-weight: 700;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 120px;
        }
        .invoice-meta-info .meta-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 13px;
            text-align: right;
        }
        .section-header {
            background: #f5f5f5;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #000;
            border-bottom: none;
        }
        .bill-to-section {
            margin-bottom: 20px;
        }
        .bill-to-box {
            border: 1px solid #000;
            padding: 12px;
        }
        .bill-to-box .name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .bill-to-box .details {
            font-size: 11px;
            line-height: 1.5;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
            font-size: 9px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            font-size: 9px;
            word-wrap: break-word;
        }
        .invoice-table th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }
        .invoice-table .text-center {
            text-align: center;
        }
        .invoice-table .text-right {
            text-align: right;
        }
        .summary-section {
            margin-top: 0;
        }
        .summary-table {
            width: 350px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            font-size: 11px;
        }
        .summary-table .text-right {
            text-align: right;
        }
        .summary-table .total-row {
            background: #f5f5f5;
            font-weight: bold;
        }
        .amount-in-words {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #000;
            font-size: 12px;
            font-weight: bold;
        }
        .footer-section {
            margin-top: 25px;
            border-top: 2px solid #000;
            padding-top: 15px;
        }
        .bank-details {
            margin-bottom: 20px;
        }
        .bank-details h4 {
            font-size: 12px;
            margin-bottom: 8px;
        }
        .bank-details p {
            font-size: 11px;
            line-height: 1.5;
        }
        .declaration {
            font-size: 10px;
            color: #333;
            margin-bottom: 20px;
            font-style: italic;
        }
        .signatory-section {
            display: flex;
            justify-content: flex-end;
        }
        .signatory-box {
            width: 220px;
            text-align: center;
        }
        .signatory-line {
            border-top: 1px solid #000;

            padding-top: 5px;
            font-size: 11px;
        }
        .signatory-box .for-text {
            font-size: 10px;
            color: #333;
            margin-bottom: 5px;
        }
        .signature-image {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 10px;
            object-fit: contain;
        }
        @media print {
            body {
                background: #fff;
            }
            .invoice-container {
                margin: 0;
                padding: 5px;
            }
            .download-button {
                display: none !important;
                visibility: hidden !important;
            }
            .invoice-table {
                font-size: 8px;
            }
            .invoice-table th,
            .invoice-table td {
                padding: 3px 2px;
                font-size: 8px;
            }
            @page {
                margin: 0.3cm;
                size: A4 landscape;
            }
        }
        /* Download button - icon style, above invoice header */
        .download-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #2563eb;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.2s;
        }
        .download-button:hover {
            background: #1d4ed8;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .download-button svg {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Download Button - Icon style, above invoice header -->
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="{{ route('customer.project.invoice.download', $project->id) }}" class="download-button" title="Download PDF">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </a>
        </div>

        <!-- Header Section -->
        <div class="invoice-header">
            <div class="company-left">
                @if(!empty($logoDataUri))
                    {{-- Use base64 encoded image (works for both PDF and web) --}}
                    <img src="{{ $logoDataUri }}" alt="Logo" class="company-logo">
                @elseif(!empty($logoUrl))
                    {{-- Fallback to URL if base64 not available --}}
                    <img src="{{ $logoUrl }}" alt="Logo" class="company-logo">
                @endif
                <div class="company-name">{{ $branding->company_name }}</div>
                <div class="company-details">
                    @if(!empty($branding->address))
                        <p><strong>Address:</strong> {{ $branding->address }}</p>
                    @endif
                    @if(!empty($branding->gst_number))
                        <p><strong>GSTIN:</strong> {{ $branding->gst_number }}</p>
                    @endif
                    @if(!empty($branding->support_email))
                        <p><strong>Email:</strong> {{ $branding->support_email }}</p>
                    @endif
                    @if(!empty($branding->support_whatsapp))
                        <p><strong>Phone:</strong> {{ $branding->support_whatsapp }}</p>
                    @endif
                </div>
            </div>
            <div class="invoice-right">
                <div class="invoice-title">TAX INVOICE</div>
                <div class="invoice-meta-info">
                    @php
                        $placeOfSupply = $project->location ?? 'Mumbai, Maharashtra';
                        // Determine state code based on location
                        $stateCode = '27';
                        $stateName = 'Maharashtra';
                        $locationLower = strtolower($placeOfSupply);
                        if (strpos($locationLower, 'ahmedabad') !== false || strpos($locationLower, 'gujarat') !== false || strpos($locationLower, 'gandhinagar') !== false || strpos($locationLower, 'surat') !== false || strpos($locationLower, 'vadodara') !== false) {
                            $stateCode = '24';
                            $stateName = 'Gujarat';
                        } elseif (strpos($locationLower, 'mumbai') !== false || strpos($locationLower, 'pune') !== false || strpos($locationLower, 'nagpur') !== false || strpos($locationLower, 'maharashtra') !== false) {
                            $stateCode = '27';
                            $stateName = 'Maharashtra';
                        } elseif (strpos($locationLower, 'delhi') !== false || strpos($locationLower, 'new delhi') !== false) {
                            $stateCode = '07';
                            $stateName = 'Delhi';
                        } elseif (strpos($locationLower, 'bangalore') !== false || strpos($locationLower, 'bengaluru') !== false || strpos($locationLower, 'karnataka') !== false) {
                            $stateCode = '29';
                            $stateName = 'Karnataka';
                        }
                    @endphp
                    <p>
                        <strong>Invoice No:</strong>
                        <span class="meta-value">{{ $invoice_number }}</span>
                    </p>
                    <p>
                        <strong>Invoice Date:</strong>
                        <span class="meta-value">{{ $invoice_date }}</span>
                    </p>
                    <p>
                        <strong>Place of Supply:</strong>
                        <span class="meta-value">{{ $placeOfSupply }}</span>
                    </p>
                    <p>
                        <strong>State Code:</strong>
                        <span class="meta-value">{{ $stateCode }} ({{ $stateName }})</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Bill To Section -->
        <div class="bill-to-section">
            <div class="section-header">Bill To</div>
            <div class="bill-to-box">
                <div class="name">{{ $project->client_name }}</div>
                <div class="details">
                    {{ $project->location }}<br>
                    Phone: {{ $project->phone }}<br>
                    @if(!empty($project->gstin))
                        GSTIN: {{ $project->gstin }}
                    @endif
                </div>
            </div>
        </div>

        <!-- GST Compliant Item Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 3%;">Sr</th>
                    <th style="width: 25%;">Description</th>
                    <th class="text-center" style="width: 6%;">HSN</th>
                    <th class="text-right" style="width: 5%;">Qty</th>
                    <th class="text-right" style="width: 7%;">Rate</th>
                    <th class="text-right" style="width: 8%;">Taxable</th>
                    <th class="text-center" style="width: 4%;">CGST%</th>
                    <th class="text-right" style="width: 7%;">CGST</th>
                    <th class="text-center" style="width: 4%;">SGST%</th>
                    <th class="text-right" style="width: 7%;">SGST</th>
                    <th class="text-right" style="width: 8%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $srNo = 1; ?>
                @if(!empty($room_breakdown))
                    @foreach($room_breakdown as $room)
                        @if(!empty($room['items']))
                            @foreach($room['items'] as $item)
                                <?php
                                $itemTotal = $item['amount'];
                                $cgst = round($itemTotal * 0.09, 2);
                                $sgst = round($itemTotal * 0.09, 2);
                                $lineTotal = $itemTotal + $cgst + $sgst;
                                ?>
                                <tr>
                                    <td class="text-center">{{ $srNo++ }}</td>
                                    <td>{{ $item['surface'] }} - {{ $item['product'] }} ({{ $item['system'] }})</td>
                                    <td class="text-center">9954</td>
                                    <td class="text-right">{{ number_format($item['qty'], 2) }}</td>
                                    <td class="text-right">{{ number_format($item['rate'], 2) }}</td>
                                    <td class="text-right">{{ number_format($itemTotal, 2) }}</td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right">{{ number_format($cgst, 2) }}</td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right">{{ number_format($sgst, 2) }}</td>
                                    <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                        @if(!empty($room['services']))
                            @foreach($room['services'] as $service)
                                <?php
                                $serviceTotal = $service['amount'];
                                $cgst = round($serviceTotal * 0.09, 2);
                                $sgst = round($serviceTotal * 0.09, 2);
                                $lineTotal = $serviceTotal + $cgst + $sgst;
                                ?>
                                <tr>
                                    <td class="text-center">{{ $srNo++ }}</td>
                                    <td>{{ $service['name'] }}</td>
                                    <td class="text-center">9954</td>
                                    <td class="text-right">{{ number_format($service['quantity'], 2) }}</td>
                                    <td class="text-right">{{ number_format($service['rate'], 2) }}</td>
                                    <td class="text-right">{{ number_format($serviceTotal, 2) }}</td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right">{{ number_format($cgst, 2) }}</td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right">{{ number_format($sgst, 2) }}</td>
                                    <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td class="text-center">1</td>
                        <td>Painting & Allied Services</td>
                        <td class="text-center">9954</td>
                        <td class="text-right">1</td>
                        <td class="text-right">{{ number_format($totals['base_total'], 2) }}</td>
                        <td class="text-right">{{ number_format($totals['base_total'], 2) }}</td>
                        <td class="text-center">9%</td>
                        <td class="text-right">{{ number_format($gst_breakdown['cgst_amount'] ?? 0, 2) }}</td>
                        <td class="text-center">9%</td>
                        <td class="text-right">{{ number_format($gst_breakdown['sgst_amount'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($totals['grand_total'], 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Tax Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td>Total Taxable Value</td>
                    <td class="text-right">₹ {{ number_format($totals['base_total'], 2) }}</td>
                </tr>
                <tr>
                    <td>CGST @ 9%</td>
                    <td class="text-right">₹ {{ number_format($gst_breakdown['cgst_amount'] ?? ($totals['total_gst'] / 2), 2) }}</td>
                </tr>
                <tr>
                    <td>SGST @ 9%</td>
                    <td class="text-right">₹ {{ number_format($gst_breakdown['sgst_amount'] ?? ($totals['total_gst'] / 2), 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Grand Total</td>
                    <td class="text-right">₹ {{ number_format($totals['grand_total'], 2) }}</td>
                </tr>
            </table>

            <div class="amount-in-words">
                Amount in Words: {{ $amount_in_words ?? 'Rupees Zero Only' }}
            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="bank-details">
                <h4>Bank Details:</h4>
                <p>
                    @if(!empty($bank_details['bank_name']))
                        Bank Name: {{ $bank_details['bank_name'] }}<br>
                    @endif
                    @if(!empty($bank_details['account_number']))
                        Account Number: {{ $bank_details['account_number'] }}<br>
                    @endif
                    @if(!empty($bank_details['ifsc_code']))
                        IFSC Code: {{ $bank_details['ifsc_code'] }}<br>
                    @endif
                    @if(!empty($bank_details['branch']))
                        Branch: {{ $bank_details['branch'] }}
                    @endif
                </p>
            </div>

            <div class="declaration">
                This is a system generated invoice and does not require physical signature.
            </div>

            <div class="signatory-section">
                <div class="signatory-box">
                    <div class="for-text">For {{ $branding->company_name }}</div>
                    @if(!empty($signatureDataUri))
                        {{-- Use base64 encoded image (works for both PDF and web) --}}
                        <img src="{{ $signatureDataUri }}" alt="Signature" class="signature-image">
                    @elseif(!empty($signatureUrl))
                        {{-- Fallback to URL if base64 not available --}}
                        <img src="{{ $signatureUrl }}" alt="Signature" class="signature-image">
                    @endif
                    <div class="signatory-line">
                        <br>
                        Authorized Signatory
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
