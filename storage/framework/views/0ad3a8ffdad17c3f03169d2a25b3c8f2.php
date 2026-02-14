<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - <?php echo e($invoice_number); ?></title>
    <?php
    use Illuminate\Support\Facades\Storage;
    $branding = \App\Models\Setting::getSettings();
    ?>
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
                <?php if(!empty($branding->logo_path)): ?>
                    <img src="<?php echo e(Storage::url($branding->logo_path)); ?>" alt="Logo" class="company-logo">
                <?php endif; ?>
                <div class="company-name"><?php echo e($branding->company_name); ?></div>
                <div class="company-details">
                    <?php echo e($branding->address ?? ''); ?><br>
                    <?php if(!empty($branding->gst_number)): ?>
                        GSTIN: <?php echo e($branding->gst_number); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($branding->support_email)): ?>
                        Email: <?php echo e($branding->support_email); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($branding->support_whatsapp)): ?>
                        Phone: <?php echo e($branding->support_whatsapp); ?>

                    <?php endif; ?>
                </div>
            </div>
            <div class="invoice-right">
                <div class="invoice-title">TAX INVOICE</div>
                <div class="invoice-meta-info">
                    <p><strong>Invoice No:</strong> <?php echo e($invoice_number); ?></p>
                    <p><strong>Invoice Date:</strong> <?php echo e($invoice_date); ?></p>
                    <p><strong>Place of Supply:</strong> <?php echo e($project->location ?? 'Mumbai, Maharashtra'); ?></p>
                    <p><strong>State Code:</strong> 27 (Maharashtra)</p>
                </div>
            </div>
        </div>

        <!-- Bill To Section -->
        <div class="bill-to-section">
            <div class="section-header">Bill To</div>
            <div class="bill-to-box">
                <div class="name"><?php echo e($project->client_name); ?></div>
                <div class="details">
                    <?php echo e($project->location); ?><br>
                    Phone: <?php echo e($project->phone); ?><br>
                    <?php if(!empty($project->gstin)): ?>
                        GSTIN: <?php echo e($project->gstin); ?>

                    <?php endif; ?>
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
                <?php if(!empty($room_breakdown)): ?>
                    <?php $__currentLoopData = $room_breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!empty($room['items'])): ?>
                            <?php $__currentLoopData = $room['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                $itemTotal = $item['amount'];
                                $cgst = round($itemTotal * 0.09, 2);
                                $sgst = round($itemTotal * 0.09, 2);
                                $lineTotal = $itemTotal + $cgst + $sgst;
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo e($srNo++); ?></td>
                                    <td><?php echo e($item['surface']); ?> - <?php echo e($item['product']); ?> (<?php echo e($item['system']); ?>)</td>
                                    <td class="text-center">9954</td>
                                    <td class="text-right"><?php echo e(number_format($item['qty'], 2)); ?></td>
                                    <td class="text-right"><?php echo e(number_format($item['rate'], 2)); ?></td>
                                    <td class="text-right"><?php echo e(number_format($itemTotal, 2)); ?></td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right"><?php echo e(number_format($cgst, 2)); ?></td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right"><?php echo e(number_format($sgst, 2)); ?></td>
                                    <td class="text-right"><?php echo e(number_format($lineTotal, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                        <?php if(!empty($room['services'])): ?>
                            <?php $__currentLoopData = $room['services']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                $serviceTotal = $service['amount'];
                                $cgst = round($serviceTotal * 0.09, 2);
                                $sgst = round($serviceTotal * 0.09, 2);
                                $lineTotal = $serviceTotal + $cgst + $sgst;
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo e($srNo++); ?></td>
                                    <td><?php echo e($service['name']); ?></td>
                                    <td class="text-center">9954</td>
                                    <td class="text-right"><?php echo e(number_format($service['quantity'], 2)); ?></td>
                                    <td class="text-right"><?php echo e(number_format($service['rate'], 2)); ?></td>
                                    <td class="text-right"><?php echo e(number_format($serviceTotal, 2)); ?></td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right"><?php echo e(number_format($cgst, 2)); ?></td>
                                    <td class="text-center">9%</td>
                                    <td class="text-right"><?php echo e(number_format($sgst, 2)); ?></td>
                                    <td class="text-right"><?php echo e(number_format($lineTotal, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <tr>
                        <td class="text-center">1</td>
                        <td>Painting & Allied Services</td>
                        <td class="text-center">9954</td>
                        <td class="text-right">1</td>
                        <td class="text-right"><?php echo e(number_format($totals['base_total'], 2)); ?></td>
                        <td class="text-right"><?php echo e(number_format($totals['base_total'], 2)); ?></td>
                        <td class="text-center">9%</td>
                        <td class="text-right"><?php echo e(number_format($gst_breakdown['cgst_amount'] ?? 0, 2)); ?></td>
                        <td class="text-center">9%</td>
                        <td class="text-right"><?php echo e(number_format($gst_breakdown['sgst_amount'] ?? 0, 2)); ?></td>
                        <td class="text-right"><?php echo e(number_format($totals['grand_total'], 2)); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tax Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td>Total Taxable Value</td>
                    <td class="text-right">₹ <?php echo e(number_format($totals['base_total'], 2)); ?></td>
                </tr>
                <tr>
                    <td>CGST @ 9%</td>
                    <td class="text-right">₹ <?php echo e(number_format($gst_breakdown['cgst_amount'] ?? ($totals['total_gst'] / 2), 2)); ?></td>
                </tr>
                <tr>
                    <td>SGST @ 9%</td>
                    <td class="text-right">₹ <?php echo e(number_format($gst_breakdown['sgst_amount'] ?? ($totals['total_gst'] / 2), 2)); ?></td>
                </tr>
                <tr class="total-row">
                    <td>Grand Total</td>
                    <td class="text-right">₹ <?php echo e(number_format($totals['grand_total'], 2)); ?></td>
                </tr>
            </table>

            <div class="amount-in-words">
                Amount in Words: <?php echo e($amount_in_words ?? 'Rupees Zero Only'); ?>

            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="bank-details">
                <h4>Bank Details:</h4>
                <p>
                    <?php if(!empty($bank_details['bank_name'])): ?>
                        Bank Name: <?php echo e($bank_details['bank_name']); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($bank_details['account_number'])): ?>
                        Account Number: <?php echo e($bank_details['account_number']); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($bank_details['ifsc_code'])): ?>
                        IFSC Code: <?php echo e($bank_details['ifsc_code']); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($bank_details['branch'])): ?>
                        Branch: <?php echo e($bank_details['branch']); ?>

                    <?php endif; ?>
                </p>
            </div>

            <div class="declaration">
                This is a system generated invoice and does not require physical signature.
            </div>

            <div class="signatory-section">
                <div class="signatory-box">
                    <div class="for-text">For <?php echo e($branding->company_name); ?></div>
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
<?php /**PATH C:\laragon\www\paintup\resources\views/customer/invoice.blade.php ENDPATH**/ ?>