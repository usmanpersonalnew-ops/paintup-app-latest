<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quote - {{ $project->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1e40af; }
        .quote-title { font-size: 18px; color: #666; margin-top: 10px; }
        .project-info { margin-bottom: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px; }
        .project-info h3 { margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        th { background: #f9fafb; font-weight: bold; }
        .totals { margin-top: 20px; text-align: right; }
        .totals table { width: 300px; margin-left: auto; }
        .totals td { text-align: right; }
        .grand-total { font-size: 18px; font-weight: bold; color: #1e40af; }
        .notes { margin-top: 30px; padding: 15px; background: #fef3c7; border-radius: 8px; }
        .footer { margin-top: 40px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PaintUp (Revamp Homes)</div>
        <div class="quote-title">QUOTATION</div>
        <div>Quote #: Q-{{ $project->id }}-{{ date('Ymd') }}</div>
        <div>Date: {{ date('d/m/Y') }}</div>
    </div>

    <div class="project-info">
        <h3>Project Details</h3>
        <p><strong>Project:</strong> {{ $project->name ?? 'N/A' }}</p>
        <p><strong>Client:</strong> {{ $project->client_name ?? 'N/A' }}</p>
        <p><strong>Address:</strong> {{ $project->address ?? 'N/A' }}</p>
    </div>

    <h3>Zone Breakdown</h3>
    @foreach($project->zones as $zone)
        <div style="margin-bottom: 20px; page-break-inside: avoid;">
            <h4>Zone: {{ $zone->name }}</h4>
            
            @if($zone->items && $zone->items->count() > 0)
                <h5>Painting Work</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Surface</th>
                            <th>Product</th>
                            <th>System (Remarks)</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zone->items as $item)
                            <tr>
                                <td>{{ $item->surface->name ?? 'N/A' }}</td>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>{{ $item->system->system_name ?? 'N/A' }}{{ $item->system->process_remarks ? ' (' . $item->system->process_remarks . ')' : '' }}</td>
                                <td>{{ number_format($item->qty, 2) }}</td>
                                <td>₹{{ number_format($item->rate, 2) }}</td>
                                <td>₹{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if($zone->services && $zone->services->count() > 0)
                <h5>Additional Services / Add-ons</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zone->services as $service)
                            <tr>
                                <td>{{ $service->masterService->name ?? $service->custom_name }}</td>
                                <td>{{ number_format($service->quantity, 2) }}</td>
                                <td>₹{{ number_format($service->rate, 2) }}</td>
                                <td>₹{{ number_format($service->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <div class="totals">
        <table>
            <tr>
                <td>Painting Work Total:</td>
                <td>₹{{ number_format($paintTotal, 2) }}</td>
            </tr>
            <tr>
                <td>Additional Services Total:</td>
                <td>₹{{ number_format($serviceTotal, 2) }}</td>
            </tr>
            <tr>
                <td>Subtotal:</td>
                <td>₹{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($project->coupon_code)
            <tr>
                <td>Coupon ({{ $project->coupon_code }}):</td>
                <td>-₹{{ number_format($project->discount_amount, 2) }}</td>
            </tr>
            @elseif($discount > 0)
            <tr>
                <td>Discount:</td>
                <td>-₹{{ number_format($discount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Tax ({{ $taxPercent }}%):</td>
                <td>₹{{ number_format($taxAmount, 2) }}</td>
            </tr>
            <tr>
                <td class="grand-total">GRAND TOTAL:</td>
                <td class="grand-total">₹{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($notes)
        <div class="notes">
            <strong>Notes / Exclusions:</strong><br>
            {{ $notes }}
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated quotation. Thank you for choosing PaintUp!</p>
        <p>Mumbai-based Professional Painting Services</p>
    </div>
</body>
</html>