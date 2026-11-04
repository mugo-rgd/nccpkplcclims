<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ strtolower($type ?? 'delivery') === 'delivery' ? 'Delivery Note' : 'Collection Note' }}</title>

    <style>
    @page {
        size: A4 portrait;
        margin: 15px 20px;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: "DejaVu Sans", Arial, sans-serif;
        font-size: 10px;
        line-height: 1.3;
        color: #292929;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    /* HEADER */
    .header-table {
        width: 100%;
        margin: 0 0 10px;
        padding-bottom: 5px;
        border-bottom: 2px solid #1a237e;
    }

    .header-table td {
        border: none;
        padding: 3px;
        vertical-align: middle;
    }

    .logo-cell, .qrcode-cell {
        width: 15%;
        text-align: center;
    }

    .title-cell {
        width: 70%;
        text-align: center;
    }

    .logo-cell img {
        height: 55px;
        max-width: 100px;
    }

    .qrcode-cell img {
        width: 55px;
        height: 55px;
    }

    .title-cell h1 {
        margin: 0;
        color: #1a237e;
        font-size: 15px;
    }

    .title-cell h2 {
        margin: 2px 0;
        color: #222;
        font-size: 12px;
    }

    .title-cell p {
        margin: 0;
        color: #555;
        font-size: 8px;
    }

    /* DOCUMENT INFO BOX */
    .document-info {
        width: 100%;
        margin: 5px 0 10px;
        background: #f4f6f8;
        border-left: 4px solid {{ strtolower($type ?? 'delivery') === 'delivery' ? '#28a745' : '#007bff' }};
    }

    .document-info td {
        padding: 5px 8px;
        border: none;
        vertical-align: middle;
    }

    .type-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
        color: {{ strtolower($type ?? 'delivery') === 'delivery' ? '#155724' : '#004085' }};
        background: {{ strtolower($type ?? 'delivery') === 'delivery' ? '#d4edda' : '#cce5ff' }};
    }

    .document-number {
        font-size: 10px;
        font-weight: bold;
        color: #1a237e;
    }

    .document-meta {
        font-size: 8px;
        color: #555;
    }

    /* CUSTOMER / EQUIPMENT GRID */
    .details-grid {
        width: 100%;
        margin: 0 0 10px;
    }

    .details-grid > tbody > tr > td {
        width: 49%;
        padding: 0;
        vertical-align: top;
        border: none;
    }

    .details-grid .gap {
        width: 2%;
    }

    .section-title {
        padding: 4px 8px;
        background: #1a237e;
        color: #fff;
        font-size: 10px;
        font-weight: bold;
    }

    .section-content {
        padding: 6px 8px;
        border: 1px solid #d8dce2;
        border-top: none;
        background: #fafafa;
        min-height: 110px;
    }

    .details-table td {
        padding: 2px 0;
        border: none;
        font-size: 9px;
        vertical-align: top;
    }

    .details-table .label {
        width: 35%;
        font-weight: bold;
        color: #1a237e;
    }

    .details-table .value {
        width: 65%;
        color: #444;
    }

    /* CHECKLIST */
    .checklist-table {
        margin: 0;
    }

    .checklist-table td {
        width: 50%;
        padding: 4px;
        border: none;
        font-size: 9px;
        vertical-align: top;
    }

    .checkbox {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 12px;
    }

    /* SIGNATURES */
    .signature-wrapper {
        margin-top: 10px;
        border-top: 1px solid #ddd;
        padding-top: 5px;
    }

    .signature-table {
        width: 100%;
    }

    .signature-table > tbody > tr > td {
        width: 48%;
        padding: 5px;
        border: none;
        vertical-align: top;
    }

    .signature-table .gap {
        width: 4%;
    }

    .sig-heading {
        margin: 0 0 4px;
        padding-bottom: 2px;
        border-bottom: 1px solid #1a237e;
        color: #1a237e;
        font-size: 10px;
        font-weight: bold;
    }

    .sig-subtext {
        font-size: 8px;
        color: #666;
        margin-bottom: 8px;
    }

    .sig-row {
        margin-bottom: 6px;
        font-size: 9px;
    }

    .sig-row .label {
        display: inline-block;
        width: 25%;
        font-weight: bold;
    }

    .sig-row .line {
        display: inline-block;
        width: 70%;
        border-bottom: 1px solid #222;
        height: 15px;
        vertical-align: bottom;
    }

    .sig-row .value {
        display: inline-block;
        width: 70%;
        font-weight: bold;
    }

    .sig-image {
        max-width: 120px;
        max-height: 30px;
    }

    /* TERMS */
    .terms-box {
        margin-top: 10px;
        padding: 6px 8px;
        border: 1px solid #ddd;
        background: #fcfcfc;
        font-size: 8px;
    }

    .terms-box h4 {
        margin: 0 0 4px;
        color: #1a237e;
        font-size: 9px;
    }

    .terms-box ul {
        margin: 0;
        padding-left: 15px;
    }

    /* FOOTER */
    .footer {
        margin-top: 10px;
        padding-top: 4px;
        border-top: 1px solid #ddd;
        font-size: 7px;
        text-align: center;
        color: #888;
    }
    </style>
</head>

<body>
@php
    $documentType = strtolower($type ?? 'delivery');
    $isDelivery = $documentType === 'delivery';

    // Logo Base64
    $logoBase64 = null;
    foreach ([public_path('img/logo.png'), public_path('img/logo.jpg')] as $path) {
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
            break;
        }
    }

    // QR Base64
    $qrBase64 = null;
    try {
        $qrUrl = $isDelivery
            ? url('/calibrations/'.$calibration->id.'/delivery-note')
            : url('/calibrations/'.$calibration->id.'/collection-note');
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate($qrUrl);
        $qrBase64 = base64_encode($qrSvg);
    } catch (\Throwable $e) {}

    // Document Number
    $documentNumber = $deliveryNumber ?? 'N/A';

    // Dynamic Items
    $allItems = $calibration->items ?? collect();
@endphp

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div style="font-size:16px; font-weight:bold; color:#1a237e;">MCL</div>
                @endif
            </td>
            <td class="title-cell">
                <h1>METER CENTRAL LABORATORY (MCL)</h1>
                <h2>{{ $isDelivery ? 'DELIVERY NOTE' : 'COLLECTION NOTE' }}</h2>
                <p>Kenya Power &amp; Lighting Company PLC</p>
            </td>
            <td class="qrcode-cell">
                @if($qrBase64)
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR Code">
                @endif
            </td>
        </tr>
    </table>

    <!-- DOCUMENT INFO -->
    <table class="document-info">
        <tr>
            <td style="width: 20%;">
                <span class="type-badge">{{ $isDelivery ? 'DELIVERY' : 'COLLECTION' }}</span>
            </td>
            <td style="width: 80%; text-align: right;">
                <div>
                    <span class="document-number">
                        {{ $isDelivery ? 'Delivery Note No.:' : 'Collection Note No.:' }} {{ $documentNumber }}
                    </span>
                </div>
                <div class="document-meta">
                    Date: {{ $calibration->created_at->format('d/m/Y') }} | Status:
                    <strong>{{ $isDelivery ? 'Delivered' : 'Collected' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- CUSTOMER & EQUIPMENT DETAILS -->
    <table class="details-grid">
        <tr>
            <!-- Customer -->
            <td>
                <div class="section-title">CUSTOMER DETAILS</div>
                <div class="section-content">
                    <table class="details-table">
                        <tr>
                            <td class="label">Company:</td>
                            <td class="value"><strong>{{ $customer->company_name ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Contact:</td>
                            <td class="value">{{ $customer->contact_person ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Phone:</td>
                            <td class="value">{{ $customer->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Address:</td>
                            <td class="value">{{ $customer->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email:</td>
                            <td class="value">{{ $customer->email ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </td>

            <td class="gap"></td>

            <!-- Equipment (Loops all items) -->
            <td>
                <div class="section-title">EQUIPMENT DETAILS</div>
                <div class="section-content">
                    <table class="details-table">
                        @foreach($allItems as $index => $item)
                            <tr>
                                <td colspan="2" style="padding-top: 4px; border-bottom: 1px dashed #ccc;">
                                    <strong style="color: #1a237e;">EQUIPMENT {{ $index + 1 }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Name:</td>
                                <td class="value"><strong>{{ $item->equipment_name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Serial No.:</td>
                                <td class="value">{{ $item->serial_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Manufacturer:</td>
                                <td class="value">{{ $item->manufacturer ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Model:</td>
                                <td class="value">{{ $item->model ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Condition:</td>
                                <td class="value">{{ ucfirst($item->equipment_condition ?? 'N/A') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- CHECKLIST -->
    <div class="section-title">EQUIPMENT CHECKLIST</div>
    <div class="section-content" style="min-height: auto; margin-bottom: 10px;">
        <table class="checklist-table">
            <tr>
                <td><span class="checkbox">&#9744;</span> Equipment is in good condition</td>
                <td><span class="checkbox">&#9744;</span> All accessories included</td>
            </tr>
            <tr>
                <td><span class="checkbox">&#9744;</span> No visible damage</td>
                <td><span class="checkbox">&#9744;</span> Calibration certificate attached</td>
            </tr>
            <tr>
                <td><span class="checkbox">&#9744;</span> Equipment cleaned</td>
                <td><span class="checkbox">&#9744;</span> Properly packed</td>
            </tr>
        </table>
    </div>

    <!-- SIGNATURES -->
    <div class="signature-wrapper">
        <table class="signature-table">
            <tr>
                <!-- MCL SIDE -->
                <td>
                    <div class="sig-heading">MCL REPRESENTATIVE</div>
                    <div class="sig-subtext">I confirm that the equipment described above has been {{ $isDelivery ? 'received' : 'released' }}.</div>

                    <div class="sig-row">
                        <span class="label">Name:</span>
                        <span class="value">{{ $mclRepresentative?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Designation:</span>
                        <span class="value">{{ $mclRepresentative?->designation ?? 'N/A' }}</span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Signature:</span>
                        <span class="line" style="text-align: center;">
                            @if(!empty($mclSignature))
                                <img src="{{ $mclSignature }}" class="sig-image" alt="Signature">
                            @endif
                        </span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Date:</span>
                        <span class="value">{{ $calibration->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Staff No.:</span>
                        <span class="value">{{ $mclRepresentative?->staff_number ?? 'N/A' }}</span>
                    </div>
                </td>

                <td class="gap"></td>

                <!-- CUSTOMER SIDE -->
                <td>
                    <div class="sig-heading">{{ $isDelivery ? 'CUSTOMER / DELIVERER' : 'CUSTOMER / RECEIVER' }}</div>
                    <div class="sig-subtext">I confirm that the equipment has been {{ $isDelivery ? 'handed over' : 'received' }} in the condition stated above.</div>

                    <div class="sig-row">
                        <span class="label">Name:</span>
                        <span class="line"></span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Signature:</span>
                        <span class="line"></span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Date:</span>
                        <span class="line"></span>
                    </div>
                    <div class="sig-row">
                        <span class="label">{{ $isDelivery ? 'ID No.:' : 'Receipt No.:' }}</span>
                        <span class="line"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TERMS -->
    <div class="terms-box">
        <h4>TERMS &amp; CONDITIONS</h4>
        <ul>
            @if($isDelivery)
                <li>MCL confirms receipt of the equipment described above.</li>
                <li>MCL and the customer should inspect the equipment and confirm its condition before signing.</li>
                <li>Any discrepancy, missing accessory, or visible damage should be reported immediately.</li>
            @else
                <li>The equipment has been received by the customer after calibration or testing.</li>
                <li>Any damage or missing accessory must be recorded before acceptance.</li>
                <li>This document serves as proof of collection by the customer.</li>
            @endif
        </ul>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>This document confirms {{ $isDelivery ? 'delivery' : 'collection' }} of the equipment described above.</p>
        <p>{{ $documentNumber }} | Generated {{ now()->format('Y-m-d H:i:s') }} | Page 1 of 1</p>
    </div>

</body>
</html>
