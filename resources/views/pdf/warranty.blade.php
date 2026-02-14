<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Warranty Certificate - {{ $project->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
        }
        .certificate-title {
            font-size: 22px;
            color: #1e40af;
            margin-top: 10px;
            font-weight: bold;
        }
        .project-info {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .project-info h3 {
            margin: 0 0 15px 0;
            color: #1e40af;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
        }
        .warranty-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .warranty-table th,
        .warranty-table td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        .warranty-table th {
            background: #1e40af;
            color: white;
            font-weight: bold;
        }
        .warranty-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .disclaimer {
            margin-top: 40px;
            padding: 20px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
        }
        .disclaimer h4 {
            margin: 0 0 10px 0;
            color: #dc2626;
        }
        .disclaimer p {
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
            color: #7f1d1d;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .generated-date {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PaintUp (Revamp Homes)</div>
        <div class="certificate-title">WARRANTY CERTIFICATE</div>
    </div>

    <div class="project-info">
        <h3>Project Details</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Project ID:</span> {{ $project->id }}
            </div>
            <div class="info-item">
                <span class="info-label">Customer Name:</span> {{ $customer['name'] ?? 'N/A' }}
            </div>
            <div class="info-item">
                <span class="info-label">Address:</span> {{ $customer['address'] ?? 'N/A' }}
            </div>
            <div class="info-item">
                <span class="info-label">Phone:</span> {{ $customer['phone'] ?? 'N/A' }}
            </div>
            <div class="info-item">
                <span class="info-label">Work Completed:</span> {{ $completed_at }}
            </div>
        </div>
    </div>

    <h3>Warranty Schedule</h3>
    <table class="warranty-table">
        <thead>
            <tr>
                <th>Area / Room</th>
                <th>Surface</th>
                <th>System Used</th>
                <th>Warranty Period</th>
                <th>Valid Until</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule as $item)
                <tr>
                    <td>{{ $item['area'] }}</td>
                    <td>{{ $item['surface'] }}</td>
                    <td>{{ $item['system'] }}</td>
                    <td>{{ $item['warranty_years'] }} Years</td>
                    <td>{{ $item['valid_until'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="disclaimer">
        <h4>Warranty Disclaimer</h4>
        <p>
            This warranty applies only to the surfaces and systems explicitly listed above.
            Any surface, area, or coating not mentioned in this schedule (including ceilings or economy-grade coatings) is not covered under warranty.
            Warranty does not cover damage due to seepage, structural cracks, external water leakage, misuse, or natural wear and tear.
        </p>
    </div>

    <div class="footer">
        <div>This is a computer-generated warranty certificate.</div>
        <div class="generated-date">Generated on: {{ $generated_at }}</div>
    </div>
</body>
</html>