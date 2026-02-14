<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GST Invoice - {{ $invoice_number }}</title>
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
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        .company-details {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #333;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .invoice-info-box {
            width: 48%;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .invoice-info-box h4 {
            background: #f3f4f6;
            padding: 5px 10px;
            margin: -10px -10px 10px -10px;
            font-size: 12px;
        }
        .customer-details {
            margin-bottom: 20px;
        }
        .customer-details h4 {
            background: #f3f4f6;
            padding: 5px 10px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals td {
            border: none;
            padding: 5px 10px;
        }
        .totals .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
        }
        .amount-in-words {
            margin-top: 20px;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .gst-breakup {
            margin-top: 20px;
        }
        .gst-table {
            width: 300px;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $company_details['name'] }}</div>
            <div class="company-details">
                {{ $company_details['address'] }}<br>
                GSTIN: {{ $company_details['gst_number'] }} | Phone: {{ $company_details['phone'] }} | Email: {{ $company_details['email'] }}
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">GST INVOICE</div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="invoice-info-box">
                <h4>Invoice Details</h4>
                <p><strong>Invoice No:</strong> {{ $invoice_number }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice_date }}</p>
                <p><strong>Project ID:</strong> #{{ $project->id }}</p>
            </div>
            <div class="invoice-info-box">
                <h4>Project Details</h4>
                <p><strong>Project:</strong> {{ $project->client_name }}</p>
                <p><strong>Location:</strong> {{ $project->location }}</p>
                <p><strong>Phone:</strong> {{ $project->phone }}</p>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="customer-details">
            <h4>Bill To (Customer)</h4>
            <p><strong>Name:</strong> {{ $customer->name ?? $project->client_name }}</p>
            <p><strong>Address:</strong> {{ $project->location }}</p>
            <p><strong>Phone:</strong> {{ $project->phone }}</p>
        </div>

        <!-- Service Value Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center">Description</th>
                    <th class="text-right">Service Value (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Painting & Allied Services</td>
                    <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Service Value</strong></td>
                    <td class="text-right"><strong>{{ number_format($subtotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- GST Breakup -->
        <table class="gst-table">
            <tr>
                <td>Service Value</td>
                <td class="text-right">{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr>
                <td><strong>Taxable Value</strong></td>
                <td class="text-right"><strong>{{ number_format($subtotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td>CGST @ {{ $gst_rate / 2 }}%</td>
                <td class="text-right">{{ number_format($cgst_amount, 2) }}</td>
            </tr>
            <tr>
                <td>SGST @ {{ $gst_rate / 2 }}%</td>
                <td class="text-right">{{ number_format($sgst_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Invoice Amount</td>
                <td class="text-right">{{ number_format($grand_total, 2) }}</td>
            </tr>
        </table>

        <!-- Amount in Words -->
        <div class="amount-in-words">
            <strong>Amount in Words:</strong> {{ $amount_in_words }}
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signature-box">
                <p>Prepared By</p>
                <div class="signature-line">
                    <p>PaintUp</p>
                </div>
            </div>
            <div class="signature-box">
                <p>Authorized Signatory</p>
                <div class="signature-line">
                    <p>{{ $company_details['name'] }}</p>
                </div>
            </div>
        </div>

        <!-- Terms -->
        <div style="margin-top: 20px; font-size: 10px; color: #666;">
            <p><strong>Terms & Conditions:</strong></p>
            <p>1. This is a computer-generated invoice.</p>
            <p>2. Payment to be made as per agreed terms.</p>
            <p>3. Subject to Mumbai jurisdiction.</p>
        </div>
    </div>
</body>
</html>
