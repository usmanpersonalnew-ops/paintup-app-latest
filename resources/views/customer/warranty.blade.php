<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warranty Certificate - {{ $certificate_number }}</title>
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
            line-height: 1.5;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        .warranty-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }
        .company-tagline {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 8px 10px;
            border: 1px solid #000;
            border-bottom: none;
        }
        .info-box {
            border: 1px solid #000;
            padding: 12px;
        }
        .info-row {
            display: flex;
            margin-bottom: 6px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        .info-value {
            flex: 1;
        }
        .dates-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border: 1px solid #000;
        }
        .date-cell {
            padding: 12px;
            text-align: center;
            border-right: 1px solid #000;
        }
        .date-cell:last-child {
            border-right: none;
        }
        .date-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .date-value {
            font-size: 14px;
            font-weight: bold;
        }
        .warranty-table {
            width: 100%;
            border-collapse: collapse;
        }
        .warranty-table th,
        .warranty-table td {
            border: 1px solid #000;
            padding: 10px 12px;
            font-size: 11px;
        }
        .warranty-table th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }
        .warranty-table .text-center {
            text-align: center;
        }
        .warranty-table .text-right {
            text-align: right;
        }
        .warranty-value {
            font-weight: bold;
        }
        .disclaimer {
            font-size: 10px;
            color: #666;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-style: italic;
        }
        .footer {
            margin-top: 25px;
            text-align: right;
            font-size: 10px;
            color: #999;
        }
        @media print {
            body {
                padding: 0;
            }
            .warranty-container {
                margin: 0;
            }
            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="warranty-container">
        <!-- Header -->
        <div class="header">
            <div style="display: flex; align-items: center; gap: 15px;">
                @if(!empty($branding->logo_path))
                    <img src="{{ Storage::url($branding->logo_path) }}" alt="Logo" style="max-width: 80px; max-height: 50px;">
                @endif
                <div>
                    <div class="company-name">{{ $branding->company_name }}</div>
                    <div class="company-tagline">Professional Painting Services</div>
                </div>
            </div>
            <div class="doc-title">WARRANTY CERTIFICATE</div>
        </div>

        <!-- Project Info -->
        <div class="section">
            <div class="section-title">Project Information</div>
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value">{{ $project->client_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ $project->location }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project ID:</span>
                    <span class="info-value">#{{ $project->id }}</span>
                </div>
            </div>
        </div>

        <!-- Key Dates -->
        <div class="section">
            <div class="dates-grid">
                <div class="date-cell">
                    <div class="date-label">Issue Date</div>
                    <div class="date-value">{{ $issue_date ?? '—' }}</div>
                </div>
                <div class="date-cell">
                    <div class="date-label">Valid Till</div>
                    <div class="date-value">{{ $valid_till ?? '—' }}</div>
                </div>
                <div class="date-cell">
                    <div class="date-label">Certificate No.</div>
                    <div class="date-value">{{ $certificate_number }}</div>
                </div>
            </div>
        </div>

        <!-- Surface-wise Warranty -->
        @if(!empty($warranty_items))
            <div class="section">
                <div class="section-title">Surface-wise Warranty Details</div>
                <table class="warranty-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Surface</th>
                            <th style="width: 30%;">Product</th>
                            <th style="width: 30%;">System</th>
                            <th style="width: 15%;" class="text-center">Warranty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranty_items as $item)
                            <tr>
                                <td>{{ $item['surface'] }}</td>
                                <td>{{ $item['product'] }}</td>
                                <td>{{ $item['system'] }}</td>
                                <td class="text-center">
                                    @if($item['warranty_months'] > 0)
                                        <span class="warranty-value">{{ $item['warranty_months'] }} Months</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Disclaimer -->
        <div class="disclaimer">
            This warranty covers peeling, flaking, and blistering of painted surfaces under normal conditions.
            Structural cracks, water seepage, humidity damage, or damage caused by third-party alterations are not covered.
            This is a system-generated certificate and does not require a physical signature.
        </div>

        <!-- Footer -->
        <div class="footer">
            @if(!empty($branding->support_email))
                For warranty claims: {{ $branding->support_email }}
            @endif
            | {{ $certificate_number }}
        </div>
    </div>
</body>
</html>
