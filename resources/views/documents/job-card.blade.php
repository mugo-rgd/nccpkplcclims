<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Job Card - {{ $jobCardNumber ?? 'N/A' }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* HEADER */
        .header-table {
            margin: 0 0 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1a237e;
        }

        .header-table td {
            padding: 3px;
            border: none;
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

        /* JOB INFO BOX */
        .job-info {
            margin: 5px 0 10px;
            padding: 6px 10px;
            background: #f4f6f8;
            border-left: 4px solid #1a237e;
            text-align: right;
        }

        .job-number {
            color: #1a237e;
            font-size: 11px;
            font-weight: bold;
        }

        .job-meta {
            margin-top: 2px;
            font-size: 9px;
            color: #555;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-pending { color: #856404; background: #fff3cd; }
        .badge-checked { color: #155724; background: #d4edda; }

        /* SECTIONS */
        .section {
            margin: 6px 0;
        }

        .section-title {
            padding: 4px 8px;
            background: #1a237e;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-content {
            padding: 6px 9px;
            border: 1px solid #ddd;
            border-top: none;
        }

        /* DETAIL ROWS */
        .row {
            margin: 2px 0;
            font-size: 10px;
        }

        .row .label {
            display: inline-block;
            width: 30%;
            font-weight: bold;
            color: #1a237e;
            vertical-align: top;
        }

        .row .value {
            display: inline-block;
            width: 68%;
            color: #555;
            vertical-align: top;
        }

        /* EQUIPMENT TABLE */
        .detail-table th, .detail-table td {
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            vertical-align: middle;
        }

        .detail-table th {
            width: 20%;
            background: #f0f2f7;
            text-align: left;
        }

        .detail-table td {
            width: 30%;
        }

        .primary-value {
            color: #1a237e !important;
            font-weight: bold;
        }

        /* SERVICE TABLE */
        .service-table th, .service-table td {
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        .service-table th {
            background: #f0f2f7;
            text-align: left;
        }

        .service-code {
            display: inline-block;
            padding: 1px 6px;
            background: #1a237e;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
        }

        /* WORK AREA */
        .work-area {
            min-height: 55px;
            padding: 6px 8px;
            border: 1px solid #ddd;
            background: #fafafa;
            font-size: 10px;
            margin-bottom: 6px;
        }

        /* SIGNATURES */
        .signature-section {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #ddd;
        }

        .signature-table > tbody > tr > td {
            width: 48%;
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .signature-table .gap {
            width: 4%;
            padding: 0;
            border: none;
        }

        .sig-heading {
            margin: 0 0 5px;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #1a237e;
            color: #1a237e;
            font-size: 10px;
            font-weight: bold;
        }

        .sig-row {
            margin: 4px 0;
            font-size: 9px;
        }

        .sig-row .label {
            display: inline-block;
            width: 30%;
            font-weight: bold;
        }

        .sig-row .line {
            display: inline-block;
            width: 68%;
            border-bottom: 1px solid #222;
            height: 15px;
            vertical-align: bottom;
        }

        .sig-row .value {
            display: inline-block;
            width: 68%;
            font-weight: bold;
            font-size: 9px;
        }

        .sig-image {
            max-width: 120px;
            max-height: 30px;
        }

        /* FOOTER */
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
            font-size: 7.5px;
            text-align: center;
            color: #888;
        }
    </style>
</head>

<body>
@php
    // 1. Logo Base64
    $logoBase64 = null;
    foreach ([public_path('img/logo.png'), public_path('img/logo.jpg')] as $path) {
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
            break;
        }
    }

    // 2. QR Code
    $qrBase64 = null;
    try {
        $qrUrl = route('calibrations.job-card', ['calibration' => $calibration->id]);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate($qrUrl);
        $qrBase64 = base64_encode($qrSvg);
    } catch (\Throwable $e) {}

    // 3. Status Badge
    $jobCardStatus = strtolower((string) ($jobCard->status ?? 'pending'));
    $statusClass = in_array($jobCardStatus, ['completed', 'checked', 'approved'])
        ? 'badge-checked'
        : 'badge-pending';

    // 4. Dates
    $technicianDate = !empty($jobCard->completed_at)
        ? \Carbon\Carbon::parse($jobCard->completed_at)->format('d/m/Y')
        : 'Pending';

    $checkedDate = null;
    if (!empty($checkedAt)) {
        $checkedDate = $checkedAt instanceof \Carbon\Carbon ? $checkedAt : \Carbon\Carbon::parse($checkedAt);
    }

    // 5. Dynamic Services for all items
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
                @endif
            </td>
            <td class="title-cell">
                <h1>METER CENTRAL LABORATORY (MCL)</h1>
                <h2>JOB CARD</h2>
                <p>Kenya Power &amp; Lighting Company PLC</p>
            </td>
            <td class="qrcode-cell">
                @if($qrBase64)
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR">
                @endif
            </td>
        </tr>
    </table>

    <!-- JOB INFO -->
    <div class="job-info">
        <div class="job-number">Job Card #: {{ $jobCardNumber ?? 'N/A' }}</div>
        <div class="job-meta">
            Date: {{ $calibration->assigned_at ? \Carbon\Carbon::parse($calibration->assigned_at)->format('d M Y') : now()->format('d M Y') }} |
            Status: <span class="badge-status {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $jobCard->status ?? 'Pending')) }}</span>
        </div>
    </div>

    <!-- 1. CUSTOMER DETAILS -->
    <div class="section">
        <div class="section-title">1. CUSTOMER DETAILS</div>
        <div class="section-content">
            <div class="row">
                <span class="label">Company:</span>
                <span class="value"><strong>{{ $customer->company_name ?? 'N/A' }}</strong></span>
            </div>
            <div class="row">
                <span class="label">Contact Person:</span>
                <span class="value">{{ $customer->contact_person ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Phone:</span>
                <span class="value">{{ $customer->phone ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Address:</span>
                <span class="value">{{ $customer->address ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Email:</span>
                <span class="value">{{ $customer->email ?? 'N/A' }}</span>
            </div>
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
                        <th style="width: 30%;">Equipment Name</th>
                        <th style="width: 25%;">Serial Number</th>
                        <th style="width: 20%;">Manufacturer</th>
                        <th style="width: 20%;">Model</th>
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
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align: center; color: #888;">No equipment recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. SERVICE TYPES -->
    <div class="section">
        <div class="section-title">3. SERVICE TYPE(S)</div>
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

    <!-- 4. ASSIGNMENT -->
    <div class="section">
        <div class="section-title">4. ASSIGNMENT</div>
        <div class="section-content">
            <div class="row"><span class="label">Assigned To:</span><span class="value"><strong>{{ $technician?->name ?? 'Not Assigned' }}</strong></span></div>
            <div class="row"><span class="label">Staff Number:</span><span class="value">{{ $technician?->staff_number ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">Designation:</span><span class="value">{{ $technician?->designation ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">Assigned By:</span><span class="value">{{ $jobCard->allocatedBy?->name ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">Assigned Date:</span><span class="value">{{ $jobCard->allocated_at ? \Carbon\Carbon::parse($jobCard->allocated_at)->format('d/m/Y H:i') : 'N/A' }}</span></div>
        </div>
    </div>

    <!-- 5. WORK DONE -->
    <div class="section">
        <div class="section-title">5. WORK DONE</div>
        <div class="section-content">
            <div class="work-area">
                <strong>Description of work performed:</strong><br>
                @if(!empty($jobCard->work_done))
                    {{ $jobCard->work_done }}
                @else
                    <span style="color: #777; font-style: italic;">Work in progress...</span>
                @endif
            </div>
            <div class="row"><span class="label">Done By:</span><span class="value">{{ $technician?->name ?? 'Not Assigned' }}</span></div>
            <div class="row"><span class="label">Completion Date:</span><span class="value">{{ $jobCard->completed_at ? \Carbon\Carbon::parse($jobCard->completed_at)->format('d/m/Y H:i') : 'Pending' }}</span></div>
        </div>
    </div>

    <!-- 6. REMARKS -->
    <div class="section">
        <div class="section-title">6. REMARKS</div>
        <div class="section-content">
            <div class="work-area" style="min-height: 40px;">
                @if(!empty($jobCard->remarks))
                    {{ $jobCard->remarks }}
                @else
                    <span style="color: #777; font-style: italic;">No remarks.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- 7. VERIFICATION -->
    <div class="section">
        <div class="section-title">7. VERIFICATION</div>
        <div class="section-content">
            <div class="row"><span class="label">Checked By:</span><span class="value"><strong>{{ $checkedBy?->name ?? 'Not Yet Checked' }}</strong></span></div>
            <div class="row"><span class="label">Designation:</span><span class="value">{{ $checkedBy?->designation ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">Staff Number:</span><span class="value">{{ $checkedBy?->staff_number ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">Checked Date:</span><span class="value">{{ $checkedDate ? $checkedDate->format('d/m/Y H:i') : 'Not Yet Checked' }}</span></div>
            <div class="row"><span class="label">Verification Status:</span><span class="value"><span class="badge-status {{ $checkedBy && $checkedDate ? 'badge-checked' : 'badge-pending' }}">{{ $checkedBy && $checkedDate ? 'Checked' : 'Pending' }}</span></span></div>
        </div>
    </div>

    <!-- 8. SIGNATURES -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <!-- TECHNICIAN -->
                <td>
                    <div class="sig-heading">TECHNICIAN</div>
                    <div class="sig-row"><span class="label">Name:</span><span class="value">{{ $technician?->name ?? 'Not Assigned' }}</span></div>
                    <div class="sig-row"><span class="label">Designation:</span><span class="value">{{ $technician?->designation ?? 'N/A' }}</span></div>
                    <div class="sig-row">
                        <span class="label">Signature:</span>
                        <span class="line" style="text-align: center;">
                            @if(!empty($technicianSignature))
                                <img src="{{ $technicianSignature }}" class="sig-image" alt="Signature">
                            @endif
                        </span>
                    </div>
                    <div class="sig-row"><span class="label">Date:</span><span class="value">{{ $technicianDate }}</span></div>
                    <div class="sig-row"><span class="label">Staff No.:</span><span class="value">{{ $technician?->staff_number ?? 'N/A' }}</span></div>
                </td>

                <td class="gap"></td>

                <!-- SUPERVISOR -->
                <td>
                    <div class="sig-heading">SUPERVISOR / CHECKED BY</div>
                    <div class="sig-row"><span class="label">Name:</span><span class="value">{{ $checkedBy?->name ?? 'Not Yet Checked' }}</span></div>
                    <div class="sig-row"><span class="label">Designation:</span><span class="value">{{ $checkedBy?->designation ?? 'N/A' }}</span></div>
                    <div class="sig-row">
                        <span class="label">Signature:</span>
                        <span class="line" style="text-align: center;">
                            @if(!empty($checkerSignature))
                                <img src="{{ $checkerSignature }}" class="sig-image" alt="Signature">
                            @endif
                        </span>
                    </div>
                    <div class="sig-row"><span class="label">Date:</span><span class="value">{{ $checkedDate ? $checkedDate->format('d/m/Y') : 'Pending' }}</span></div>
                    <div class="sig-row"><span class="label">Staff No.:</span><span class="value">{{ $checkedBy?->staff_number ?? 'N/A' }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>Meter Central Laboratory — Kenya Power &amp; Lighting Company PLC</p>
        <p>{{ $jobCardNumber ?? 'N/A' }} | Generated {{ now()->format('d M Y H:i:s') }} | Powered by MCL LIMS</p>
    </div>

</body>
</html>
