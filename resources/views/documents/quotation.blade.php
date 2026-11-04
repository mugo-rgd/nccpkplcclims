<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation</title>
    <style>
    @page {
        margin: 15px 25px;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        font-size: 10px;
        color: #333333;
        line-height: 1.3;
    }

    /* HEADER */
    .header-table {
        width: 100%;
        margin: 0 0 8px;
        padding-bottom: 5px;
        border-collapse: collapse;
        border-bottom: 2px solid #1a237e;
    }

    .header-table td {
        border: none;
        padding: 2px;
    }

    .header-table .logo-cell {
        width: 20%;
        text-align: left;
        vertical-align: middle;
    }

    .header-table .logo-cell img {
        height: 45px;
    }

    .header-table .title-cell {
        width: 60%;
        text-align: center;
        vertical-align: middle;
    }

    .header-table .title-cell h1 {
        margin: 0;
        color: #1a237e;
        font-size: 15px;
    }

    .header-table .title-cell h2 {
        margin: 2px 0;
        color: #333333;
        font-size: 12px;
    }

    .header-table .title-cell p {
        margin: 0;
        color: #555555;
        font-size: 8px;
    }

    .header-table .qrcode-cell {
        width: 20%;
        text-align: right;
        vertical-align: middle;
    }

    .header-table .qrcode-cell img {
        width: 50px;
        height: 50px;
    }

    /* QUOTE NUMBER */
    .quote-number {
        margin: 5px 0;
        padding: 5px 10px;
        border-radius: 3px;
        background: #f5f5f5;
        font-size: 11px;
        font-weight: bold;
        text-align: right;
    }

    .quote-number .valid-until {
        color: #d32f2f;
    }

    /* SECTIONS */
    .section {
        margin: 5px 0;
    }

    .section-title {
        padding: 3px 8px;
        border-radius: 2px;
        background: #1a237e;
        color: #ffffff;
        font-size: 9px;
        font-weight: bold;
    }

    .section-content {
        padding: 6px 8px;
        border: 1px solid #dddddd;
        border-top: none;
    }

    /* MAIN TABLES */
    table {
        width: 100%;
        margin: 2px 0;
        border-collapse: collapse;
    }

    table th {
        padding: 3px 6px;
        border: 1px solid #1a237e;
        background: #1a237e;
        color: #ffffff;
        font-size: 9px;
        text-align: left;
    }

    table td {
        padding: 3px 6px;
        border: 1px solid #dddddd;
        font-size: 9px;
    }

    .total-row {
        background: #f5f5f5;
        font-weight: bold;
    }

    .total-row td {
        border-top: 1px solid #1a237e;
    }

    .amount {
        color: #1a237e;
        font-size: 12px;
        font-weight: bold;
    }

    /* SERVICE CODE */
    .service-code {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 2px;
        background: #1a237e;
        color: #ffffff;
        font-size: 8px;
        font-weight: bold;
    }

    /* COMPACT CUSTOMER & EQUIPMENT GRID */
    .details-grid {
        width: 100%;
        border-collapse: collapse;
        margin: 5px 0;
    }

    .details-grid td {
        border: none;
        padding: 0;
        vertical-align: top;
        width: 50%;
    }

    .details-box {
        border: 1px solid #d7d7d7;
        border-radius: 3px;
        background: #ffffff;
        margin-right: 4px;
    }

    .details-box.equipment-box {
        margin-right: 0;
        margin-left: 4px;
    }

    .details-header {
        padding: 4px 6px;
        background: #1a237e;
        color: #ffffff;
        font-size: 9px;
        font-weight: bold;
    }

    .details-body {
        padding: 4px 6px;
    }

    .details-body table {
        margin: 0;
    }

    .details-body table td {
        padding: 1px 2px;
        border: none;
        font-size: 8.5px;
    }

    .details-body table td.label {
        width: 30%;
        font-weight: bold;
        color: #333;
    }

    .details-body table td.value {
        width: 70%;
        color: #555;
    }

    .equipment-separator td {
        border-top: 1px dashed #ccc !important;
        padding-top: 4px !important;
    }

    /* PAYMENT & TERMS */
    .bottom-section-table {
        width: 100%;
        margin: 5px 0 0;
        border-collapse: collapse;
    }

    .bottom-section-table td {
        border: none;
        padding: 0;
        vertical-align: top;
    }

    .bottom-section-table .payment-column {
        width: 35%;
        padding-right: 8px;
    }

    .bottom-section-table .terms-column {
        width: 65%;
        padding-left: 8px;
    }

    .mpesa-payment-box {
        padding: 6px;
        border: 1px solid #159447;
        border-radius: 4px;
        background: #f8fff9;
        text-align: center;
    }

    .mpesa-payment-heading {
        margin: -6px -6px 4px;
        padding: 3px;
        background: #159447;
        color: #ffffff;
        font-size: 9px;
        font-weight: bold;
    }

    .mpesa-payment-qr {
        width: 80px;
        height: 80px;
        margin: 0 auto 3px;
    }

    .mpesa-payment-text {
        font-size: 7px;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .payment-details-table td {
        padding: 2px 3px;
        border: 1px solid #cfd8d3;
        font-size: 7.5px;
    }

    .payment-details-table .label {
        background: #edf7f0;
        font-weight: bold;
        width: 35%;
        text-align: left;
    }

    .terms {
        min-height: 130px;
        padding: 6px 8px;
        border: 1px solid #cccccc;
        border-radius: 4px;
        background: #f8f9fa;
        font-size: 8px;
    }

    .terms h4 {
        margin: 0 0 4px;
        padding-bottom: 2px;
        border-bottom: 1px solid #cccccc;
        color: #1a237e;
        font-size: 9px;
    }

    .terms ul {
        margin: 2px 0 0;
        padding-left: 12px;
    }

    .terms li {
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .footer {
        margin-top: 10px;
        padding-top: 5px;
        border-top: 1px solid #dddddd;
        color: #888888;
        font-size: 7.5px;
        text-align: center;
    }
    </style>
</head>
<body>
@php
    // All financial amounts are read from the issued quotation.
    $currency = strtoupper((string) ($quote->currency ?: 'KES'));
    $subtotal = (float) ($quote->subtotal ?? 0);
    $discount = (float) ($quote->discount_amount ?? 0);
    $taxRate = (float) ($quote->tax_rate ?? 0); // Percentage, e.g. 16.00
    $tax = (float) ($quote->tax_amount ?? 0);
    $total = (float) ($quote->total_amount ?? 0);
    $paid = (float) ($quote->amount_paid ?? 0);
    $balance = (float) ($quote->balance_due ?? max(0, $total - $paid));
    $quoteDate = $quote->created_at ?? now();
    $expiry = $quote->valid_until;
    $quoteLines = collect($quote->items_data ?? []);
    $calibrationItems = $calibration?->items ?? collect();
    $uniqueServices = collect();
    foreach ($calibrationItems as $calibrationItem) {
        $ids = $calibrationItem->service_types ?? [];
        if (is_string($ids)) $ids = json_decode($ids, true) ?: [];
        if (!is_array($ids)) continue;
        foreach ($ids as $id) {
            $service = \App\Models\KplcServiceType::find($id);
            if ($service) $uniqueServices->push($service);
        }
    }
    $uniqueServices = $uniqueServices->unique('id');
    $logoBase64 = null;
    foreach ([public_path('img/logo.png'), public_path('img/logo.jpg')] as $logoPath) {
        if (is_file($logoPath)) {
            $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime = $extension === 'png' ? 'image/png' : 'image/jpeg';
            $logoBase64 = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
            break;
        }
    }
@endphp
<table class="header-table">
    <tr>
        <td class="logo-cell">@if($logoBase64)<img src="{{ $logoBase64 }}" alt="KPLC Logo">@endif</td>
        <td class="title-cell">
            <h1>METER CENTRAL LABORATORY (MCL)</h1>
            <h2>QUOTATION</h2>
            <p>Kenya Power &amp; Lighting Company</p>
        </td>
        <td class="qrcode-cell"></td>
    </tr>
</table>
<div class="quote-number">
    Quote #: <strong>{{ $quote->quote_number }}</strong>
    &nbsp;|&nbsp; Date: {{ $quoteDate->format('d/m/Y') }}
    @if($expiry)
        &nbsp;|&nbsp; <span class="valid-until">Valid Until: {{ $expiry->format('d/m/Y') }}</span>
    @endif
</div>
<table class="details-grid">
    <tr>
        <td>
            <div class="details-box">
                <div class="details-header">CUSTOMER DETAILS</div>
                <div class="details-body">
                    <table>
                        <tr><td class="label">Company</td><td class="value"><strong>{{ $customer?->company_name ?? 'N/A' }}</strong></td></tr>
                        <tr><td class="label">Contact</td><td class="value">{{ $customer?->contact_person ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Phone</td><td class="value">{{ $customer?->phone ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Email</td><td class="value">{{ $customer?->email ?? 'N/A' }}</td></tr>
                        <tr><td class="label">KRA PIN</td><td class="value">{{ $customer?->tax_id ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
        </td>
        <td>
            <div class="details-box equipment-box">
                <div class="details-header">EQUIPMENT DETAILS</div>
                <div class="details-body">
                    <table>
                        @forelse($calibrationItems as $index => $item)
                            <tr class="{{ $index > 0 ? 'equipment-separator' : '' }}">
                                <td colspan="2"><strong>EQUIPMENT {{ $index + 1 }}</strong></td>
                            </tr>
                            <tr><td class="label">Name</td><td class="value">{{ $item->equipment_name ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Serial No.</td><td class="value">{{ $item->serial_number ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Manufacturer</td><td class="value">{{ $item->manufacturer ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Model</td><td class="value">{{ $item->model ?? 'N/A' }}</td></tr>
                        @empty
                            <tr><td colspan="2">See quotation line items below.</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>
@if($uniqueServices->isNotEmpty())
<div class="section">
    <div class="section-title">SERVICE TYPES</div>
    <div class="section-content">
        <table>
            <thead><tr><th width="15%">Code</th><th width="45%">Service Name</th><th width="40%">Method</th></tr></thead>
            <tbody>
                @foreach($uniqueServices as $service)
                    <tr><td>{{ $service->serv_code }}</td><td>{{ $service->serv_name }}</td><td>{{ $service->serv_method }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
<div class="section">
    <div class="section-title">COST BREAKDOWN</div>
    <div class="section-content">
        <table>
            <thead>
                <tr>
                    <th style="width:46%">Description</th>
                    <th style="width:12%;text-align:right">Qty</th>
                    <th style="width:20%;text-align:right">Unit Price</th>
                    <th style="width:22%;text-align:right">Amount ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quoteLines as $item)
                    @php
                        $quantity = (float) ($item['quantity'] ?? 1);
                        $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                        $lineAmount = round($quantity * $unitPrice, 2);
                        $description = $item['description'] ?? $item['name'] ?? $item['equipment_name'] ?? $item['type'] ?? 'Calibration service';
                    @endphp
                    <tr>
                        <td>{{ $description }}</td>
                        <td style="text-align:right">{{ number_format($quantity, 2) }}</td>
                        <td style="text-align:right">{{ number_format($unitPrice, 2) }}</td>
                        <td style="text-align:right">{{ number_format($lineAmount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Calibration / testing services (saved quotation subtotal)</td>
                        <td style="text-align:right">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforelse
                <tr class="total-row"><td colspan="3">Saved Subtotal</td><td style="text-align:right">{{ number_format($subtotal, 2) }}</td></tr>
                @if($discount > 0)
                    <tr><td colspan="3">Discount</td><td style="text-align:right">-{{ number_format($discount, 2) }}</td></tr>
                @endif
                <tr><td colspan="3">VAT ({{ number_format($taxRate, 2) }}%)</td><td style="text-align:right">{{ number_format($tax, 2) }}</td></tr>
                <tr class="total-row"><td colspan="3">TOTAL ({{ $currency }})</td><td style="text-align:right"><strong class="amount">{{ number_format($total, 2) }}</strong></td></tr>
                @if($paid > 0)
                    <tr><td colspan="3">Amount Paid</td><td style="text-align:right">{{ number_format($paid, 2) }}</td></tr>
                    <tr class="total-row"><td colspan="3">Balance Due</td><td style="text-align:right">{{ number_format($balance, 2) }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
<table class="bottom-section-table">
    <tr>
        <td class="payment-column">
            @if(!empty($paymentQrCode) && $balance > 0)
                <div class="mpesa-payment-box">
                    <div class="mpesa-payment-heading">PAY WITH M-PESA</div>
                    <img src="data:image/svg+xml;base64,{{ $paymentQrCode }}" alt="M-PESA payment QR code" class="mpesa-payment-qr">
                    <div class="mpesa-payment-text">Scan the QR code or pay using the details below.</div>
                    <table class="payment-details-table">
                        <tr><td class="label">Paybill</td><td>883380</td></tr>
                        <tr><td class="label">Account</td><td>{{ $accountReference ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Amount Due</td><td>{{ $currency }} {{ number_format($balance, 2) }}</td></tr>
                    </table>
                </div>
            @endif
        </td>
        <td class="terms-column">
            <div class="terms">
                <h4>Terms &amp; Conditions</h4>
                <ul>
                    <li>Payment must be made before calibration/testing commences, where applicable.</li>
                    <li>Quotation validity is as indicated above.</li>
                    <li>Equipment must be in good condition for testing.</li>
                    <li>Results are based on the equipment as received.</li>
                    <li>Turnaround time is subject to the agreed service schedule.</li>
                </ul>
                @if(filled($quote->terms))
                    <p>{{ $quote->terms }}</p>
                @endif
            </div>
        </td>
    </tr>
</table>
<div class="footer">
    <p>This is a system-generated quotation. For inquiries, contact Meter Central Laboratory.</p>
    <p>{{ $quote->quote_number }} | Generated {{ now()->format('Y-m-d H:i:s') }}</p>
</div>
</body>
</html>
