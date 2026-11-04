<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 5px;
            vertical-align: middle;
            border: none;
        }
        .header-logo {
            width: 100px;
            text-align: center;
        }
        .header-logo img {
            height: 70px;
            width: auto;
        }
        .header-logo .logo-placeholder {
            width: 70px;
            height: 70px;
            background: #1a237e;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
        }
        .header-title {
            text-align: center;
            padding-left: 20px;
        }
        .header-title h1 {
            margin: 0;
            font-size: 22px;
            color: #1a237e;
        }
        .header-title .subtitle {
            font-size: 13px;
            color: #0d47a1;
        }
        .header-title .quote-number {
            font-size: 16px;
            color: #0d47a1;
            font-weight: bold;
            margin-top: 3px;
        }
        .header-right {
            text-align: right;
            width: 180px;
        }
        .header-right .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        .header-right p {
            margin: 3px 0;
            font-size: 11px;
            color: #666;
        }
        .customer-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .customer-info table {
            width: 100%;
        }
        .customer-info td {
            padding: 3px 5px;
            border: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #1a237e;
            color: white;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .totals {
            float: right;
            width: 350px;
        }
        .totals table {
            border: 1px solid #ddd;
        }
        .totals td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .totals .total {
            font-weight: bold;
            font-size: 16px;
            background: #e8f5e9;
            border-top: 2px solid #2e7d32;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            width: 200px;
        }
        .signature hr {
            margin: 5px 0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0,0,0,0.05);
            z-index: -1;
            pointer-events: none;
        }
        .date-info {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">BATCH QUOTATION</div>

    <!-- Header with Logo and Title in Same Row -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @php
                        $logoPath = public_path('img/logo.png');
                        $logoExists = file_exists($logoPath);
                    @endphp
                    @if($logoExists)
                        <img src="{{ public_path('img/logo.png') }}" alt="{{ config('app.name') }}">
                    @else
                        <div class="logo-placeholder">MCL</div>
                    @endif
                </td>
                <td class="header-title">
                    <h1>{{ config('app.name', 'Meter Central Laboratory') }}</h1>
                    <div class="subtitle">Calibration Services</div>
                    <div class="quote-number">BATCH QUOTATION</div>
                    <div class="date-info">Quote #: {{ $quotation->quote_number }} | Date: {{ now()->format('Y-m-d') }}</div>
                </td>
                <td class="header-right">
                    <p><span class="badge badge-success">BATCH</span></p>
                    <p><strong>Valid Until:</strong></p>
                    <p>{{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('Y-m-d') : 'N/A' }}</p>
                    <p style="margin-top: 5px; font-size: 10px; color: #999;">
                        Batch ID: {{ str_replace('BATCH-', '', $quotation->quote_reference ?? 'N/A') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Customer Information -->
    <div class="customer-info">
        <table>
            <tr>
                <td><strong>Customer:</strong></td>
                <td>{{ $quotation->customer->company_name ?? 'N/A' }}</td>
                <td><strong>Contact:</strong></td>
                <td>{{ $quotation->customer->contact_person ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td>{{ $quotation->customer->phone ?? 'N/A' }}</td>
                <td><strong>Email:</strong></td>
                <td>{{ $quotation->customer->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td colspan="3">{{ $quotation->customer->address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Equipment List -->
    <div class="section-title">Equipment List</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Equipment Name</th>
                <th width="15%">Serial Number</th>
                <th width="15%">Manufacturer</th>
                <th width="15%">Model</th>
                <th width="15%" class="text-right">Amount (KSh)</th>
            </tr>
        </thead>
        <tbody>
    @forelse($items as $item)
        @php
            $quantity = (float) ($item['quantity'] ?? 1);

            $unitPrice = (float) (
                $item['unit_price']
                ?? $item['price']
                ?? 0
            );

            $itemTotal = isset($item['total'])
                ? (float) $item['total']
                : $quantity * $unitPrice;
        @endphp

        <tr>
            <td>{{ $loop->iteration }}</td>

            <td>
                {{ $item['description'] ?? $item['name'] ?? 'Item' }}
            </td>

            <td class="text-right">
                {{ number_format($quantity, 2) }}
            </td>

            <td class="text-right">
                {{ $quotation->currency_code }}
                {{ number_format($unitPrice, 2) }}
            </td>

            <td class="text-right">
                {{ $quotation->currency_code }}
                {{ number_format($itemTotal, 2) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">
                No quotation items found.
            </td>
        </tr>
    @endforelse
</tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td><strong>Total Equipment</strong></td>
                <td class="text-right"><strong>{{ count($items) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Subtotal</strong></td>
                <td class="text-right">KSh {{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Tax ({{ $quotation->tax_rate }}%)</strong></td>
                <td class="text-right">KSh {{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Amount</strong></td>
                <td class="text-right"><strong>KSh {{ number_format($quotation->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Terms & Conditions -->
    <div class="section-title">Terms & Conditions</div>
    <p>{{ $quotation->terms ?? 'Payment due within 30 days.' }}</p>

    @if($quotation->notes)
        <div class="section-title">Notes</div>
        <p>{{ $quotation->notes }}</p>
    @endif

    <!-- Signature Section -->
    <div class="signature">
        <div>
            <p><strong>Prepared By:</strong></p>
            <hr>
            <p style="font-size: 10px; color: #666;">Signature & Date</p>
        </div>
        <div>
            <p><strong>Authorized By:</strong></p>
            <hr>
            <p style="font-size: 10px; color: #666;">Signature & Date</p>
        </div>
        <div>
            <p><strong>Customer Approval:</strong></p>
            <hr>
            <p style="font-size: 10px; color: #666;">Signature & Date</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This quotation is valid for 30 days from the date of issue.</p>
        <p>{{ config('app.name') }} - Meter Central Laboratory</p>
        <p style="margin-top: 5px; font-size: 8px; color: #999;">
            {{ config('app.name') }} | Tel: +254 20 123 4567 | Email: info@kplc.co.ke
        </p>
    </div>
</body>
</html>
