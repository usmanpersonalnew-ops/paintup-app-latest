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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #1f2937;

            min-height: 100vh;
            padding: 20px;
        }
        .warranty-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: #1e293b;
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }
        .company-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-container {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .company-details h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .company-details p {
            font-size: 13px;
            opacity: 0.9;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .doc-title .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section {
            padding: 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        .info-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s ease;
        }
        .info-card:hover {
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }
        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }
        .dates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .date-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #bae6fd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .date-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
        }
        .date-label {
            font-size: 11px;
            font-weight: 600;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .date-value {
            font-size: 18px;
            font-weight: 700;
            color: #0c4a6e;
        }
        .warranty-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .warranty-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .warranty-table th {
            padding: 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .warranty-table th:last-child {
            text-align: center;
        }
        .warranty-table tbody tr {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s ease;
        }
        .warranty-table tbody tr:hover {
            background: #f9fafb;
        }
        .warranty-table tbody tr:last-child {
            border-bottom: none;
        }
        .warranty-table td {
            padding: 16px;
            font-size: 14px;
            color: #374151;
        }
        .warranty-table .text-center {
            text-align: center;
        }
        .warranty-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        .disclaimer {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            font-size: 13px;
            color: #92400e;
            line-height: 1.7;
        }
        .disclaimer-title {
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .cert-number {
            font-weight: 700;
            color: #667eea;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .warranty-container {
                box-shadow: none;
                border-radius: 0;
            }
            .header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .header {
                padding: 20px;
            }
            .header-top {
                flex-direction: column;
            }
            .doc-title {
                text-align: left;
            }
            .company-details h1 {
                font-size: 20px;
            }
            .doc-title h2 {
                font-size: 22px;
            }
            .section {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .dates-grid {
                grid-template-columns: 1fr;
            }
            .warranty-table {
                font-size: 12px;
            }
            .warranty-table th,
            .warranty-table td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="warranty-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="company-info">
                    @if(!empty($branding->logo_path))
                        <div class="logo-container">
                            <img src="{{ Storage::url($branding->logo_path) }}" alt="Logo">
                        </div>
                    @endif
                    <div class="company-details">
                        <h1>{{ $branding->company_name ?? 'PaintUp' }}</h1>
                        <p>Professional Painting Services</p>
                    </div>
                </div>
                <div class="doc-title">
                    <h2>WARRANTY CERTIFICATE</h2>
                    <span class="badge">Official Document</span>
                </div>
            </div>
        </div>

        <!-- Project Info -->
        <div class="section">
            <div class="section-title">📋 Project Information</div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Customer Name</div>
                    <div class="info-value">{{ $project->client_name }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Project Location</div>
                    <div class="info-value">{{ $project->location }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Project ID</div>
                    <div class="info-value">#{{ $project->id }}</div>
                </div>
            </div>
        </div>

        <!-- Key Dates -->
        <div class="section">
            <div class="section-title">📅 Certificate Details</div>
            <div class="dates-grid">
                <div class="date-card">
                    <div class="date-label">Issue Date</div>
                    <div class="date-value">{{ $issue_date ?? '—' }}</div>
                </div>
                <div class="date-card">
                    <div class="date-label">Valid Till</div>
                    <div class="date-value">{{ $valid_till ?? '—' }}</div>
                </div>
                <div class="date-card">
                    <div class="date-label">Certificate Number</div>
                    <div class="date-value">{{ $certificate_number }}</div>
                </div>
            </div>
        </div>

        <!-- Surface-wise Warranty -->
        @if(!empty($warranty_items))
            <div class="section">
                <div class="section-title">🛡️ Warranty Coverage Details</div>
                <table class="warranty-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Surface</th>
                            <th style="width: 30%;">Product</th>
                            <th style="width: 30%;">System</th>
                            <th style="width: 15%;" class="text-center">Warranty Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranty_items as $item)
                            <tr>
                                <td><strong>{{ $item['surface'] }}</strong></td>
                                <td>{{ $item['product'] }}</td>
                                <td>{{ $item['system'] }}</td>
                                <td class="text-center">
                                    @if($item['warranty_months'] > 0)
                                        <span class="warranty-badge">{{ $item['warranty_months'] }} Months</span>
                                    @else
                                        <span style="color: #9ca3af;">—</span>
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
            <div class="disclaimer-title">
                <span>⚠️</span>
                <span>Warranty Terms & Conditions</span>
            </div>
            <p>
                This warranty covers peeling, flaking, and blistering of painted surfaces under normal conditions.
                Structural cracks, water seepage, humidity damage, or damage caused by third-party alterations are not covered.
                This is a system-generated certificate and does not require a physical signature.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                @if(!empty($branding->support_email))
                    <span>For warranty claims: <strong>{{ $branding->support_email }}</strong></span>
                @endif
                <span class="cert-number">Certificate #{{ $certificate_number }}</span>
            </div>
        </div>
    </div>
</body>
</html>
