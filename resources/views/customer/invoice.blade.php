<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $invoice_number }}</title>
    @php
    use Illuminate\Support\Facades\Storage;
    $branding = \App\Models\Setting::getSettings();
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-left {
            max-width: 50%;
        }
        .company-logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .invoice-right {
            text-align: right;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 15px;
        }
        .invoice-meta-info {
            font-size: 11px;
            text-align: right;
            line-height: 1.6;
        }
        .invoice-meta-info strong {
            display: inline-block;
            width: 100px;
            text-align: left;
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
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
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
            margin-top: 40px;
            padding-top: 5px;
            font-size: 11px;
        }
        .signatory-box .for-text {
            font-size: 10px;
            color: #333;
            margin-bottom: 5px;
        }
        @media print {
            body {
                background: #fff;
            }
            .invoice-container {
                margin: 0;
                padding: 10px;
            }
            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="company-left">
                @if(!empty($branding->logo_path))
                    <img src="{{ Storage::url($branding->logo_path) }}" alt="Logo" class="company-logo">
                @endif
                <div class="company-name">{{ $branding->company_name }}</div>
                <div class="company-details">
                    {{ $branding->address ?? '' }}<br>
                    @if(!empty($branding->gst_number))
                        GSTIN: {{ $branding->gst_number }}<br>
                    @endif
                    @if(!empty($branding->support_email))
                        Email: {{ $branding->support_email }}<br>
                    @endif
                    @if(!empty($branding->support_whatsapp))
                        Phone: {{ $branding->support_whatsapp }}
                    @endif
                </div>
            </div>
            <div class="invoice-right">
                <div class="invoice-title">TAX INVOICE</div>
                <div class="invoice-meta-info">
                    <p><strong>Invoice No:</strong> {{ $invoice_number }}</p>
                    <p><strong>Invoice Date:</strong> {{ $invoice_date }}</p>
                    <p><strong>Place of Supply:</strong> {{ $project->location ?? 'Mumbai, Maharashtra' }}</p>
                    <p><strong>State Code:</strong> 27 (Maharashtra)</p>
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
                    <th class="text-center" style="width: 40px;">Sr No</th>
                    <th>Description</th>
                    <th class="text-center" style="width: 70px;">HSN/SAC</th>
                    <th class="text-right" style="width: 50px;">Qty</th>
                    <th class="text-right" style="width: 80px;">Rate (₹)</th>
                    <th class="text-right" style="width: 90px;">Taxable Value (₹)</th>
                    <th class="text-center" style="width: 50px;">CGST %</th>
                    <th class="text-right" style="width: 80px;">CGST (₹)</th>
                    <th class="text-center" style="width: 50px;">SGST %</th>
                    <th class="text-right" style="width: 80px;">SGST (₹)</th>
                    <th class="text-right" style="width: 90px;">Line Total (₹)</th>
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
