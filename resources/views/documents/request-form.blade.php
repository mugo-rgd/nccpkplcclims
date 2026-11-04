<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Customer Request Form</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10px 15px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #333;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.25;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* HEADER */
        .header-table {
            margin-bottom: 5px;
            border-bottom: 2px solid #1a237e;
        }

        .header-table td {
            padding: 3px;
            border: 0;
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

        .logo-cell img, .qrcode-cell img {
            display: block;
            width: 60px;
            height: 60px;
            margin: 0 auto;
            object-fit: contain;
        }

        .title-cell h1 {
            margin: 0;
            color: #1a237e;
            font-size: 15px;
            line-height: 1.2;
        }

        .title-cell h2 {
            margin: 2px 0;
            color: #222;
            font-size: 11px;
            line-height: 1.2;
        }

        .title-cell p {
            margin: 1px 0 0;
            font-size: 9px;
        }

        /* SECTIONS */
        .section {
            margin: 3px 0;
            page-break-inside: avoid;
        }

        .section-title {
            padding: 3px 8px;
            border-radius: 2px;
            background: #1a237e;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-content {
            padding: 4px 7px;
            border: 1px solid #d8dce2;
            border-top: 0;
        }

        /* DETAIL TABLES */
        .detail-table th, .detail-table td {
            padding: 4px 6px;
            border: 1px solid #d8dce2;
            vertical-align: middle;
            font-size: 9px;
            line-height: 1.25;
        }

        .detail-table th {
            width: 20%;
            background: #f0f2f7;
            color: #333;
            font-weight: bold;
            text-align: left;
        }

        .detail-table td {
            width: 30%;
            background: #fff;
            color: #444;
        }

        .primary-value {
            color: #1a237e;
            font-weight: bold;
        }

        /* SERVICE TYPES */
        .service-table th, .service-table td {
            padding: 3px 5px;
            border: 1px solid #d8dce2;
            vertical-align: middle;
            font-size: 8px;
            line-height: 1.25;
        }

        .service-table th {
            background: #f0f2f7;
            color: #333;
            font-weight: bold;
            text-align: left;
        }

        .service-code {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            background: #1a237e;
            color: #fff;
            font-size: 7px;
            font-weight: bold;
        }

        /* SIGNATURES - Flattened for DomPDF */
        .signature-section {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #d8dce2;
            page-break-inside: avoid;
        }

        .signature-heading {
            margin-bottom: 5px;
            color: #1a237e;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }

        .signature-layout {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-layout > tbody > tr > td {
            width: 50%;
            padding: 6px;
            border: 1px solid #d8dce2;
            vertical-align: top;
        }

        .sig-label {
            display: block;
            margin-bottom: 2px;
            color: #1a237e;
            font-size: 9px;
            font-weight: bold;
        }

        .sig-description {
            margin: 0 0 4px;
            color: #666;
            font-size: 7px;
            font-style: italic;
        }

        .sig-row {
            margin-bottom: 4px;
            font-size: 8px;
        }

        .sig-row .label {
            display: inline-block;
            width: 25%;
            font-weight: bold;
        }

        .sig-row .line {
            display: inline-block;
            width: 70%;
            border-bottom: 1px solid #000;
            height: 15px;
            vertical-align: bottom;
        }

        .sig-row .value {
            display: inline-block;
            width: 70%;
            font-weight: bold;
            font-size: 8px;
        }

        .auto-signature-image {
            max-width: 110px;
            max-height: 28px;
        }

        /* TERMS & FOOTER */
        .terms-list {
            margin: 0;
            padding-left: 16px;
            font-size: 8px;
            line-height: 1.25;
        }

        .terms-list li {
            margin: 0 0 1px;
        }

        .footer {
            margin-top: 5px;
            padding-top: 3px;
            border-top: 1px solid #d8dce2;
            color: #888;
            font-size: 7px;
            line-height: 1.2;
            text-align: center;
        }
    </style>
</head>

<body>
@php
    // 1. Logo Base64
    $logoBase64 = null;
    foreach ([public_path('img/logo.png'), public_path('img/logo.jpg'), public_path('images/logo.png')] as $path) {
        if (is_file($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
            break;
        }
    }

    // 2. QR Base64
    $qrBase64 = null;
    try {
        if ($calibration?->id) {
            $qrUrl = route('calibrations.request-form', ['calibration' => $calibration->id]);
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate($qrUrl);
            $qrBase64 = base64_encode($qrSvg);
        }
    } catch (\Throwable $e) {}

    // 3. MCL Signature
    $mclUser = $mclRepresentative ?? auth()->user();
    $mclSignature = null;
    if ($mclUser && (bool) ($mclUser->use_auto_signature ?? false) && !empty($mclUser->signature_path)) {
        $sigPath = storage_path('app/public/'.ltrim($mclUser->signature_path, '/'));
        if (is_file($sigPath)) {
            $mime = mime_content_type($sigPath) ?: 'image/png';
            $mclSignature = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($sigPath));
        }
    }

    // 4. Dynamically fetch all services for all items
    $uniqueServices = collect();
    if (isset($calibration->items) && $calibration->items->count() > 0) {
        foreach ($calibration->items as $item) {
            if (!empty($item->service_types)) {
                $serviceIds = is_array($item->service_types) ? $item->service_types : json_decode($item->service_types, true);
                if (is_array($serviceIds)) {
                    foreach ($serviceIds as $sId) {
                        $s = \App\Models\KplcServiceType::find($sId);
                        if ($s) $uniqueServices->push($s);
                    }
                }
            }
        }
    }
    $uniqueServices = $uniqueServices->unique('id');
@endphp

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="MCL Logo">
                @else
                    <div style="color:#1a237e;font-size:16px;font-weight:bold;">MCL</div>
                @endif
            </td>
            <td class="title-cell">
                <h1>METER CENTRAL LABORATORY (MCL)</h1>
                <h2>TESTING / CALIBRATION / REPAIR REQUEST FORM</h2>
                <p>Date: <strong>{{ $calibration->created_at->format('d/m/Y') }}</strong> | Request No.: <strong>{{ $requestNumber ?? 'N/A' }}</strong></p>
            </td>
            <td class="qrcode-cell">
                @if($qrBase64)
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="Request form QR code">
                @endif
            </td>
        </tr>
    </table>

    <!-- 1. CUSTOMER DETAILS -->
    <div class="section">
        <div class="section-title">1. CUSTOMER DETAILS</div>
        <div class="section-content">
            <table class="detail-table">
                <tr>
                    <th>Company Name</th>
                    <td colspan="3" class="primary-value">{{ $customer?->company_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $customer?->address ?? 'N/A' }}</td>
                    <th>Contact Person</th>
                    <td>{{ $customer?->contact_person ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $customer?->phone ?? 'N/A' }}</td>
                    <th>Email</th>
                    <td>{{ $customer?->email ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 2. EQUIPMENT DETAILS -->
    <div class="section">
        <div class="section-title">2. EQUIPMENT DETAILS</div>
        <div class="section-content">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 25%;">Equipment Name</th>
                        <th style="width: 20%;">Serial Number</th>
                        <th style="width: 20%;">Manufacturer</th>
                        <th style="width: 15%;">Model</th>
                        <th style="width: 15%;">Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($calibration->items as $index => $item)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                            <td class="primary-value">{{ $item->equipment_name ?? 'N/A' }}</td>
                            <td>{{ $item->serial_number ?? 'N/A' }}</td>
                            <td>{{ $item->manufacturer ?? 'N/A' }}</td>
                            <td>{{ $item->model ?? 'N/A' }}</td>
                            <td>{{ ucfirst($item->equipment_condition ?? 'N/A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #888;">No equipment recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. SERVICE TYPES -->
    <div class="section">
        <div class="section-title">3. SERVICE TYPE(S) REQUESTED</div>
        <div class="section-content">
            @if($uniqueServices->count() > 0)
                <table class="service-table">
                    <thead>
                        <tr>
                            <th style="width:15%;">Code</th>
                            <th style="width:45%;">Service Name</th>
                            <th style="width:40%;">Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uniqueServices as $service)
                            <tr>
                                <td><span class="service-code">{{ $service->serv_code }}</span></td>
                                <td>{{ $service->serv_name }}</td>
                                <td>{{ $service->serv_method }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="text-align: center; color: #888; font-style: italic;">No service types specified.</p>
            @endif
        </div>
    </div>

    <!-- 4. CUSTOMER VERIFICATION -->
    @if($calibration->verification_of_item || $calibration->review_of_service || $calibration->validity_period_required)
        <div class="section">
            <div class="section-title">4. CUSTOMER VERIFICATION</div>
            <div class="section-content">
                <table class="detail-table">
                    @if($calibration->verification_of_item)
                        <tr>
                            <th>Verification of Item</th>
                            <td colspan="3">{{ $calibration->verification_of_item }}</td>
                        </tr>
                    @endif
                    @if($calibration->review_of_service)
                        <tr>
                            <th>Review of Service</th>
                            <td colspan="3">{{ $calibration->review_of_service }}</td>
                        </tr>
                    @endif
                    @if($calibration->validity_period_required)
                        <tr>
                            <th>Validity Period Required</th>
                            <td colspan="3">
                                <strong>{{ strtoupper($calibration->validity_period_required) }}</strong>
                                @if($calibration->validity_period)
                                    ({{ $calibration->validity_period }} year(s))
                                @endif
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    @endif

    <!-- 5. SIGNATURES -->
    <div class="signature-section">
        <div class="signature-heading">ACKNOWLEDGEMENT &amp; SIGNATURES</div>
        <table class="signature-layout">
            <tr>
                <!-- CUSTOMER SIDE -->
                <td>
                    <span class="sig-label">CUSTOMER / REPRESENTATIVE</span>
                    <p class="sig-description">I confirm that the information provided is accurate.</p>

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
                        <span class="label">Contact:</span>
                        <span class="line"></span>
                    </div>
                </td>

                <!-- MCL SIDE -->
                <td>
                    <span class="sig-label">MCL REPRESENTATIVE</span>
                    <p class="sig-description">Received and acknowledged on behalf of MCL.</p>

                    <div class="sig-row">
                        <span class="label">Name:</span>
                        <span class="value">{{ $mclUser?->name ?? 'Not Available' }}</span>
                    </div>
                    @if(!empty($mclUser?->designation))
                    <div class="sig-row">
                        <span class="label">Designation:</span>
                        <span class="value">{{ $mclUser->designation }}</span>
                    </div>
                    @endif
                    <div class="sig-row">
                        <span class="label">Signature:</span>
                        <span class="line" style="text-align: center;">
                            @if($mclSignature)
                                <img src="{{ $mclSignature }}" class="auto-signature-image" alt="Signature">
                            @endif
                        </span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Date:</span>
                        <span class="value">{{ $calibration->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="sig-row">
                        <span class="label">Staff No.:</span>
                        <span class="value">{{ $mclUser?->staff_number ?? 'N/A' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 6. TERMS -->
    <div class="section">
        <div class="section-title">6. TERMS &amp; CONDITIONS</div>
        <div class="section-content">
            <ol class="terms-list">
                <li>The equipment will be tested or calibrated according to the requested service type(s).</li>
                <li>Results are based on the condition of the equipment as received.</li>
                <li>The customer is responsible for ensuring the equipment is in a suitable condition for testing or calibration.</li>
                <li>Payment must be made according to the approved quotation.</li>
                <li>The normal turnaround time is five to seven working days unless otherwise agreed.</li>
                <li>The laboratory is not responsible for pre-existing defects or failures discovered during testing.</li>
            </ol>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>This form is a legally binding document. Please ensure all information is accurate.</p>
        <p>Meter Central Laboratory - Kenya Power &amp; Lighting Company PLC</p>
        <p>{{ $requestNumber ?? 'N/A' }} | {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
