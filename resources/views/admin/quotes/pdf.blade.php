<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation</title>
    <style>
    @page {
        margin: 30px;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #333333;
        line-height: 1.4;
    }

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .header-table {
        width: 100%;
        margin: 0 0 10px;
        padding-bottom: 8px;
        border-collapse: collapse;
        border-bottom: 2px solid #1a237e;
    }

    .header-table td {
        border: none;
        padding: 5px;
    }

    .header-table .logo-cell {
        width: 18%;
        text-align: center;
        vertical-align: middle;
    }

    .header-table .logo-cell img {
        display: block;
        width: auto;
        height: 65px;
        max-height: 65px;
        margin: 0 auto;
    }

    .header-table .title-cell {
        width: 64%;
        padding: 5px;
        text-align: center;
        vertical-align: middle;
    }

    .header-table .title-cell h1 {
        margin: 0;
        color: #1a237e;
        font-size: 18px;
        letter-spacing: 1px;
    }

    .header-table .title-cell h2 {
        margin: 3px 0;
        color: #333333;
        font-size: 15px;
    }

    .header-table .title-cell p {
        margin: 2px 0;
        color: #555555;
        font-size: 10px;
    }

    .header-table .qrcode-cell {
        width: 18%;
        padding: 5px;
        text-align: center;
        vertical-align: middle;
    }

    .header-table .qrcode-cell img {
        display: block;
        width: 65px;
        height: 65px;
        margin: 0 auto;
    }

    /*
    |--------------------------------------------------------------------------
    | Quote number
    |--------------------------------------------------------------------------
    */

    .quote-number {
        margin: 8px 0;
        padding: 8px 15px;
        border-radius: 4px;
        background: #f5f5f5;
        font-size: 13px;
        font-weight: bold;
        text-align: right;
    }

    .quote-number .valid-until {
        color: #d32f2f;
    }

    .quote-number small {
        color: #555555;
        font-weight: normal;
    }

    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    */

    .section {
        margin: 8px 0;
    }

    .section-title {
        padding: 4px 10px;
        border-radius: 3px;
        background: #1a237e;
        color: #ffffff;
        font-size: 11px;
        font-weight: bold;
    }

    .section-content {
        padding: 8px 10px;
        border: 1px solid #dddddd;
        border-top: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Information rows
    |--------------------------------------------------------------------------
    */

    .row {
        display: table;
        width: 100%;
        margin: 2px 0;
    }

    .col-label {
        display: table-cell;
        width: 30%;
        color: #333333;
        font-size: 11px;
        font-weight: bold;
    }

    .col-value {
        display: table-cell;
        width: 70%;
        color: #555555;
        font-size: 11px;
    }

    .col-value strong {
        color: #1a237e;
    }

    /*
    |--------------------------------------------------------------------------
    | Main document tables
    |--------------------------------------------------------------------------
    */

    table {
        width: 100%;
        margin: 5px 0;
        border-collapse: collapse;
    }

    table th {
        padding: 5px 8px;
        border: 1px solid #1a237e;
        background: #1a237e;
        color: #ffffff;
        font-size: 10px;
        font-weight: bold;
        text-align: left;
    }

    table td {
        padding: 5px 8px;
        border: 1px solid #dddddd;
        font-size: 10px;
    }

    .total-row {
        background: #f5f5f5;
        font-weight: bold;
    }

    .total-row td {
        border-top: 2px solid #1a237e;
    }

    .amount {
        color: #1a237e;
        font-size: 15px;
        font-weight: bold;
    }

    /*
    |--------------------------------------------------------------------------
    | Service code
    |--------------------------------------------------------------------------
    */

    .service-code {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 3px;
        background: #1a237e;
        color: #ffffff;
        font-size: 9px;
        font-weight: bold;
    }

    /*
    |--------------------------------------------------------------------------
    | On-site costs
    |--------------------------------------------------------------------------
    */

    .on-site-section {
        margin-top: 10px;
        padding: 10px 15px;
        border-left: 4px solid #ffc107;
        border-radius: 4px;
        background: #fff3cd;
    }

    .on-site-section h4 {
        margin: 0 0 5px;
        color: #856404;
        font-size: 12px;
    }

    .on-site-section ul {
        margin: 5px 0 0 20px;
        color: #856404;
        font-size: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Payment and terms row
    |--------------------------------------------------------------------------
    */

    .bottom-section-table {
        width: 100%;
        margin: 10px 0 0;
        border-collapse: collapse;
        table-layout: fixed;
        page-break-inside: avoid;
    }

    .bottom-section-table > tbody > tr > td {
        border: none;
        padding: 0;
        vertical-align: top;
    }

    .bottom-section-table .payment-column {
        width: 34%;
        padding-right: 8px;
    }

    .bottom-section-table .terms-column {
        width: 66%;
        padding-left: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | M-PESA payment box
    |--------------------------------------------------------------------------
    */

    .mpesa-payment-box {
        width: 100%;
        padding: 8px;
        border: 1px solid #159447;
        border-radius: 4px;
        background: #f8fff9;
        text-align: center;
        page-break-inside: avoid;
    }

    .mpesa-payment-heading {
        margin: -8px -8px 7px;
        padding: 5px;
        background: #159447;
        color: #ffffff;
        font-size: 10px;
        font-weight: bold;
        text-align: center;
    }

    .mpesa-payment-qr {
        display: block;
        width: 105px;
        height: 105px;
        margin: 0 auto 6px;
    }

    .mpesa-payment-text {
        margin: 4px 0 7px;
        color: #333333;
        font-size: 7.5px;
        line-height: 1.35;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | Payment details table
    |--------------------------------------------------------------------------
    */

    .payment-details-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .payment-details-table td {
        padding: 3px 4px;
        border: 1px solid #cfd8d3;
        font-size: 7.5px;
        line-height: 1.3;
        vertical-align: middle;
    }

    .payment-details-table .label {
        width: 38%;
        background: #edf7f0;
        color: #333333;
        font-weight: bold;
        text-align: left;
    }

    .payment-details-table .value {
        width: 62%;
        color: #111111;
        font-weight: bold;
        overflow-wrap: break-word;
        word-wrap: break-word;
    }

    /*
    |--------------------------------------------------------------------------
    | Terms and conditions
    |--------------------------------------------------------------------------
    */

    .terms {
        min-height: 175px;
        margin: 0;
        padding: 9px 12px;
        border: 1px solid #cccccc;
        border-radius: 4px;
        background: #f8f9fa;
        font-size: 9px;
        page-break-inside: avoid;
    }

    .terms h4 {
        margin: 0 0 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #cccccc;
        color: #1a237e;
        font-size: 11px;
    }

    .terms ul {
        margin: 4px 0 0;
        padding-left: 16px;
        color: #555555;
    }

    .terms li {
        margin-bottom: 4px;
        font-size: 9px;
        line-height: 1.35;
    }

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */

    .footer {
        margin-top: 15px;
        padding-top: 8px;
        border-top: 1px solid #dddddd;
        color: #888888;
        font-size: 9px;
        text-align: center;
        page-break-inside: avoid;
    }

    .footer p {
        margin: 2px 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Utilities
    |--------------------------------------------------------------------------
    */

    .text-muted {
        color: #6c757d;
        font-style: italic;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .fw-bold {
        font-weight: bold;
    }

    .mt-1 {
        margin-top: 5px;
    }

    .mt-2 {
        margin-top: 10px;
    }

    .mb-1 {
        margin-bottom: 5px;
    }

    .mb-2 {
        margin-bottom: 10px;
    }

    .page-break {
        page-break-before: always;
    }

    @media print {
        .quote-number {
            background: #f5f5f5;
        }

        .on-site-section {
            background: #fff3cd;
        }

        .mpesa-payment-box {
            background: #f8fff9;
        }

        .terms {
            background: #f8f9fa;
        }
    }

    /*
|--------------------------------------------------------------------------
| Customer and equipment cards
|--------------------------------------------------------------------------
*/

.details-layout {
    width: 100%;
    margin: 8px 0;
    border-collapse: collapse;
    table-layout: fixed;
    page-break-inside: avoid;
}

.details-layout > tbody > tr > td {
    width: 50%;
    padding: 0;
    border: none;
    vertical-align: top;
}

.customer-details-column {
    padding-right: 5px !important;
}

.equipment-details-column {
    padding-left: 5px !important;
}

.details-card {
    width: 100%;
    border: 1px solid #d7d7d7;
    border-radius: 3px;
    background: #ffffff;
    page-break-inside: avoid;
}

.details-card-title {
    padding: 5px 8px;
    background: #1a237e;
    color: #ffffff;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: 0.2px;
}

.details-card-body {
    padding: 6px 8px;
}

.details-data-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
    table-layout: fixed;
}

.details-data-table td {
    padding: 2px 3px;
    border: none;
    font-size: 9px;
    line-height: 1.3;
    vertical-align: top;
}

.details-data-table .details-label {
    width: 34%;
    color: #333333;
    font-weight: bold;
}

.details-data-table .details-value {
    width: 66%;
    color: #555555;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.details-data-table .details-value strong {
    color: #1a237e;
}
</style>
</head>
<body>

    <!-- ============================================ -->
    <!-- HEADER WITH LOGO AND TEXT ALIGNED           -->
    <!-- ============================================ -->
    <table class="header-table">
        <tr>
            <!-- Logo Column -->
            <td class="logo-cell">
                @php
                    // Get logo as base64
                    $logoBase64 = '';
                    $logoPaths = [
                        public_path('img/logo.png'),
                        public_path('img/logo.jpg'),
                        public_path('images/logo.png'),
                        public_path('logo.png'),
                        public_path('img/logo.jpeg'),
                    ];

                    foreach ($logoPaths as $path) {
                        if (file_exists($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
                            break;
                        }
                    }
                @endphp

                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="MCL Logo">
                @else
                    <div style="font-size: 22px; font-weight: bold; color: #1a237e;">MCL</div>
                @endif
            </td>

            <!-- Title Column -->
            <td class="title-cell">
                <h1>METER CENTRAL LABORATORY (MCL)</h1>
                <h2>QUOTATION</h2>
                <p>Kenya Power & Lighting Company | ISO 17025 Accredited</p>
            </td>

            <!-- QR Code Column -->
            <!-- QR Code Column -->
<td class="qrcode-cell">
    @php
        $qrBase64 = null;

        try {

            /*
             * Generates:
             * http://ncckplims.test/calibrations/82/quotation
             */
            $qrUrl = route('calibrations.quotation', [
                'calibration' => $calibration->id,
            ]);

            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(180)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($qrUrl);

            $qrBase64 = base64_encode($qrSvg);

        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error(
                'Quotation QR generation failed',
                [
                    'calibration_id' => $calibration->id ?? null,
                    'error' => $e->getMessage(),
                ]
            );

            $qrBase64 = null;
        }
    @endphp

    @if($qrBase64)
        <img
            src="data:image/svg+xml;base64,{{ $qrBase64 }}"
            alt="Quotation QR Code"
            style="width:65px;height:65px;"
        >

        <div style="
            margin-top:2px;
            font-size:7px;
            color:#555;
            text-align:center;
        ">
            Scan to view
        </div>
    @else
        <div style="
            font-size:7px;
            color:#999;
            text-align:center;
        ">
            QR unavailable
        </div>
    @endif
</td>
        </tr>
    </table>

    <!-- ============================================ -->
    <!-- QUOTE NUMBER                                 -->
    <!-- ============================================ -->
   @php
    $quoteDate =
        $quote?->created_at
        ?? $calibration->quote_sent_at
        ?? $calibration->created_at
        ?? now();

    $validUntil =
        $quote?->valid_until
        ?? $quoteDate->copy()->addDays(30);
@endphp

<div class="quote-number">
    Quote #:
    <strong>
        {{ $quoteNumber ?? $quote?->quote_number ?? 'N/A' }}
    </strong>

    <br>

    <small>
        Date:
        {{ $quoteDate->format('d/m/Y') }}
    </small>

    &nbsp;|&nbsp;

    <small class="valid-until">
        Valid Until:
        {{ $validUntil->format('d/m/Y') }}
    </small>
</div>
<!-- ============================================ -->
<!-- CUSTOMER AND EQUIPMENT DETAILS              -->
<!-- ============================================ -->

<table class="details-layout">
    <tbody>
        <tr>
            <!-- CUSTOMER DETAILS -->
            <td class="details-column customer-details-column">
                <div class="details-card">
                    <div class="details-card-title">
                        CUSTOMER DETAILS
                    </div>

                    <div class="details-card-body">
                        <table class="details-data-table">
                            <tbody>
                                <tr>
                                    <td class="details-label">
                                        Company
                                    </td>

                                    <td class="details-value">
                                        <strong>
                                            {{ $customer->company_name ?? 'N/A' }}
                                        </strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Address
                                    </td>

                                    <td class="details-value">
                                        {{ $customer->address ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Contact
                                    </td>

                                    <td class="details-value">
                                        {{ $customer->contact_person ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Phone
                                    </td>

                                    <td class="details-value">
                                        {{
                                            $customer->phone
                                            ?? $customer->phone_number
                                            ?? 'N/A'
                                        }}
                                    </td>
                                </tr>

                                @if(!empty($customer->email))
                                    <tr>
                                        <td class="details-label">
                                            Email
                                        </td>

                                        <td class="details-value">
                                            {{ $customer->email }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>

            <!-- EQUIPMENT DETAILS -->
            <td class="details-column equipment-details-column">
                <div class="details-card">
                    <div class="details-card-title">
                        EQUIPMENT DETAILS
                    </div>

                    <div class="details-card-body">
                        <table class="details-data-table">
                            <tbody>
                                <tr>
                                    <td class="details-label">
                                        Equipment
                                    </td>

                                    <td class="details-value">
                                        <strong>
                                            {{ $calibration->equipment_name ?? 'N/A' }}
                                        </strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Serial No.
                                    </td>

                                    <td class="details-value">
                                        {{ $calibration->serial_number ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Manufacturer
                                    </td>

                                    <td class="details-value">
                                        {{ $calibration->manufacturer ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Model
                                    </td>

                                    <td class="details-value">
                                        {{ $calibration->model ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Type
                                    </td>

                                    <td class="details-value">
                                        {{
                                            $equipmentTypeName
                                            ?? $calibration->equipment_type
                                            ?? 'N/A'
                                        }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Accuracy
                                    </td>

                                    <td class="details-value">
                                        {{ $calibration->accuracy_class ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="details-label">
                                        Ratings
                                    </td>

                                    <td class="details-value">
                                        {{ $calibration->ratings ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>

    <!-- ============================================ -->
    <!-- SERVICE TYPES                                -->
    <!-- ============================================ -->
    <div class="section">
        <div class="section-title">SERVICE TYPES</div>
        <div class="section-content">
            @if($serviceTypes && $serviceTypes->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th width="15%">Code</th>
                            <th width="40%">Service Name</th>
                            <th width="45%">Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceTypes as $service)
                            <tr>
                                <td><span class="service-code">{{ $service->serv_code ?? 'N/A' }}</span></td>
                                <td>{{ $service->serv_name ?? 'N/A' }}</td>
                                <td style="font-size: 9px;">{{ $service->serv_method ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted text-center">No service types specified.</p>
            @endif
        </div>
    </div>

    <!-- ============================================ -->
    <!-- COST BREAKDOWN                               -->
    <!-- ============================================ -->
    <div class="section">
        <div class="section-title">COST BREAKDOWN</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th style="width:30%;">Item</th>
                        <th style="width:50%;">Description</th>
                        <th style="width:20%; text-align:right;">Amount (KSh)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Equipment Testing -->
                    <tr>
                        <td>Equipment Testing/Calibration</td>
                        <td>{{ $calibration->equipment_name ?? 'Calibration Service' }}</td>
                        <td style="text-align:right;">{{ number_format($equipmentRate ?? 0, 2) }}</td>
                    </tr>

                    <!-- On-Site Costs -->
                    @if($isOnSite)
                        @if($perDiem > 0)
                            <tr>
                                <td><strong>Per Diem</strong></td>
                                <td>{{ $numberOfStaff ?? 1 }} staff × {{ $travelDays ?? 1 }} days @ KSh {{ number_format($perDiem / ($numberOfStaff * $travelDays), 2) }} /day</td>
                                <td style="text-align:right;">{{ number_format($perDiem, 2) }}</td>
                            </tr>
                        @endif

                        @if($transport > 0)
                            <tr>
                                <td><strong>Transport</strong></td>
                                <td>{{ $siteDistance ?? 0 }} KM round trip @ KSh 50/KM</td>
                                <td style="text-align:right;">{{ number_format($transport, 2) }}</td>
                            </tr>
                        @endif

                        @if($otherCosts > 0 && !empty($otherCostsDescription))
                            <tr>
                                <td><strong>Other Costs</strong></td>
                                <td>{{ $otherCostsDescription }}</td>
                                <td style="text-align:right;">{{ number_format($otherCosts, 2) }}</td>
                            </tr>
                        @endif
                    @endif

                    <!-- Subtotal -->
                    <tr class="total-row">
                        <td colspan="2"><strong>Subtotal</strong></td>
                        <td style="text-align:right;"><strong>{{ number_format($subtotal, 2) }}</strong></td>
                    </tr>

                    <!-- VAT -->
                    <tr>
                        <td colspan="2">VAT (16%)</td>
                        <td style="text-align:right;">{{ number_format($tax, 2) }}</td>
                    </tr>

                    <!-- Total -->
                    <tr class="total-row">
                        <td colspan="2"><strong>Total</strong></td>
                        <td style="text-align:right;">
                            <strong class="amount">KSh {{ number_format($total, 2) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- ON-SITE NOTES (if applicable)               -->
    <!-- ============================================ -->
    @if($isOnSite)
        <div class="on-site-section">
            <h4>📋 On-Site Calibration Notes</h4>
            <ul>
                <li>Staff accommodation and meals are included in per diem.</li>
                <li>Transport covers round trip from MCL to customer site.</li>
                <li>Additional costs may apply for extended stays.</li>
                <li>Customer to provide access to the equipment and work area.</li>
            </ul>
        </div>
    @endif

<!-- ============================================ -->
<!-- PAYMENT AND TERMS                            -->
<!-- ============================================ -->

<table class="bottom-section-table">
    <tbody>
        <tr>
            <td class="payment-column">
                @if(!empty($paymentQrCode))
                    <div class="mpesa-payment-box">

    <div class="mpesa-payment-heading">
        PAY WITH M-PESA
    </div>

    <img
        src="data:image/svg+xml;base64,{{ $paymentQrCode }}"
        alt="M-PESA QR Code"
        class="mpesa-payment-qr"
    >

    <div class="mpesa-payment-text">
        <strong>Option 1:</strong><br>
        Scan the QR Code using your M-PESA App or phone camera.

        <br><br>

        <strong>OR</strong>

        <br><br>

        <strong>Option 2:</strong><br>
        Pay manually using the details below.
    </div>

    <table class="payment-details-table">
        <tr>
            <td class="label">
                Paybill
            </td>

            <td class="value">
                883380
            </td>
        </tr>

        <tr>
            <td class="label">
                Account
            </td>

            <td class="value">
                {{ $accountReference }}
            </td>
        </tr>

        <tr>
            <td class="label">
                Amount
            </td>

            <td class="value">
                {{ $quote->currency ?? 'KES' }}
                {{ number_format($balanceDue,2) }}
            </td>
        </tr>
    </table>

</div>
                @endif
            </td>

            <td class="terms-column">
                <div class="terms">
                    <h4>Terms &amp; Conditions</h4>

                    <ul>
                        <li>
                            Payment must be made before
                            calibration/testing commences.
                        </li>

                        <li>
                            Quotation is valid for 30 days from
                            the date of issue.
                        </li>

                        <li>
                            Equipment must be in good condition
                            for testing.
                        </li>

                        <li>
                            Results are based on the equipment
                            as received.
                        </li>

                        <li>
                            Turnaround time is 5–7 working days.
                        </li>

                        <li>
                            This quotation is valid only for the
                            equipment specified.
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
    </tbody>
</table>

    <!-- ============================================ -->
    <!-- FOOTER                                       -->
    <!-- ============================================ -->
    <div class="footer">
        <p>This is a system-generated quotation. For inquiries, contact Meter Central Laboratory.</p>
        <p>Meter Central Laboratory - Kenya Power & Lighting Company</p>
        <p style="font-size: 8px; color: #aaa; margin-top: 3px;">
            {{ $quoteNumber ?? 'N/A' }} | {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

</body>
</html>
