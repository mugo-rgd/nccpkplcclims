<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Job Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.6;
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
            height: 60px;
            width: auto;
        }
        .header-logo .logo-placeholder {
            width: 60px;
            height: 60px;
            background: #1a237e;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            margin: 0;
            font-size: 18px;
            color: #1a237e;
            text-decoration: underline;
        }
        .header-title .subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }
        .header-right {
            text-align: right;
            width: 180px;
        }
        .header-right .doc-ref {
            font-size: 10px;
            color: #333;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a237e;
            text-decoration: underline;
            margin: 15px 0 10px 0;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-row strong {
            display: inline-block;
            width: 120px;
        }
        .field-line {
            border-bottom: 1px solid #333;
            padding: 3px 5px;
            margin: 2px 0;
            min-height: 20px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
        }
        th {
            background: #1a237e;
            color: white;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #1a237e;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-section div {
            width: 30%;
        }
        .signature-section hr {
            margin: 5px 0;
            border: 1px solid #333;
        }
        .signature-section .label {
            font-size: 10px;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-pending { background: #ffc107; color: #333; }
        .badge-progress { background: #17a2b8; color: white; }
        .badge-completed { background: #28a745; color: white; }
    </style>
</head>
<body>
    <!-- Header with Logo -->
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
                    <h1>JOB CARD</h1>
                    <div class="subtitle">{{ config('app.name', 'Meter Central Laboratory') }}</div>
                </td>
                <td class="header-right">
                    <div class="doc-ref"><strong>REF:</strong> KP1/13B.1//3/6/JC/{{ str_replace('BATCH-', '', $batch_id) }}</div>
                    <div class="doc-ref"><strong>Date:</strong> {{ $generated_at->format('Y-m-d') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. CUSTOMER DETAILS -->
    <div class="section-title">1. CUSTOMER DETAILS</div>
    <div class="info-row">
        <strong>NAME:</strong> <span class="field-line" style="width: 60%;">{{ $customer->company_name ?? 'N/A' }}</span>
    </div>
    <div class="info-row">
        <strong>ADDRESS:</strong> <span class="field-line" style="width: 40%;">{{ $customer->address ?? 'N/A' }}</span>
        <strong style="margin-left: 20px;">TEL:</strong> <span class="field-line" style="width: 25%;">{{ $customer->phone ?? 'N/A' }}</span>
    </div>
    <div class="info-row">
        <strong>EMAIL:</strong> <span class="field-line" style="width: 60%;">{{ $customer->email ?? 'N/A' }}</span>
    </div>

    <!-- 2. DETAILS OF EQUIPMENT -->
    <div class="section-title">2. DETAILS OF EQUIPMENT UNDER TEST / REPAIR / CALIBRATION</div>
    <div class="info-row">
        <strong>MANUFACTURER:</strong> <span class="field-line" style="width: 30%;">Various</span>
        <strong>DESCRIPTION:</strong> <span class="field-line" style="width: 30%;">Batch of {{ $total_items }} equipment</span>
    </div>
    <div class="info-row">
        <strong>TYPE/MODEL:</strong> <span class="field-line" style="width: 30%;">Various</span>
        <strong>SERIAL NO:</strong> <span class="field-line" style="width: 30%;">See attached list</span>
        <strong>RATINGS:</strong> <span class="field-line" style="width: 20%;">Various</span>
    </div>

    <!-- 3. TYPE OF SERVICE -->
    <div class="section-title">3. TYPE OF SERVICE REQUESTED AND METHOD TO BE USED</div>
    <table>
        <thead>
            <tr>
                <th width="25%">SERVICE</th>
                <th width="20%">METHODS</th>
                <th width="40%">NOTES</th>
                <th width="15%">TICK AS APPROPRIATE</th>
            </tr>
        </thead>
        <tbody>
            @php
                $selectedServices = [];
                foreach($calibrations as $cal) {
                    if($cal->service_types) {
                        $services = is_array($cal->service_types) ? $cal->service_types : json_decode($cal->service_types, true);
                        if(is_array($services)) {
                            $selectedServices = array_merge($selectedServices, $services);
                        }
                    }
                }
                $selectedServices = array_unique($selectedServices);
            @endphp
            <tr>
                <td>Calibration/Testing of Energy meters</td>
                <td>IEC 62052-11</td>
                <td>Electricity metering equipment (AC) -- General requirements, tests, and test conditions, Part 11: Metering Equipment</td>
                <td class="text-center">
                    @if(in_array(1, $selectedServices) || in_array('1', $selectedServices))
                        <span style="font-size: 18px;">✓</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Calibration/Testing of Energy meters</td>
                <td>IEC 62058-31</td>
                <td>Electricity metering equipment (AC) --Acceptance inspection - Part 31: Particular requirements for static meters for active energy (classes 0.2S, 0.5S, 1 and 2), Clause 5.4 to 5.10</td>
                <td class="text-center">
                    @if(in_array(2, $selectedServices) || in_array('2', $selectedServices))
                        <span style="font-size: 18px;">✓</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Testing of low current Energy Transformers</td>
                <td>IEC 60044-1</td>
                <td>Instrument Transformers-Part 1: Current Transformers</td>
                <td class="text-center">
                    @if(in_array(4, $selectedServices) || in_array('4', $selectedServices))
                        <span style="font-size: 18px;">✓</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Testing of low current Energy Transformers</td>
                <td>IEC 61869-1&2</td>
                <td>Instrument transformers --Part 1: General requirements Part 2: Additional requirements for current transformers</td>
                <td class="text-center">
                    @if(in_array(5, $selectedServices) || in_array('5', $selectedServices))
                        <span style="font-size: 18px;">✓</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>AC/ DC testing and calibration</td>
                <td>Manufacturer's Method</td>
                <td>As described by the equipment manufacturer</td>
                <td class="text-center">
                    @if(in_array(6, $selectedServices) || in_array('6', $selectedServices))
                        <span style="font-size: 18px;">✓</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Others</td>
                <td>-</td>
                <td>Specify</td>
                <td class="text-center">
                    @if(in_array(7, $selectedServices) || in_array('7', $selectedServices))
                        <span style="font-size: 18px;">✓</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Proof of Payment -->
    <div class="info-row">
        <strong>PROOF OF PAYMENT (where applicable):</strong>
        @if($customer->isExternal() ?? false)
            <span style="font-weight: bold; color: #28a745;">✓ YES</span>
        @else
            <span style="font-weight: bold;">NO</span>
        @endif
    </div>
    <div class="info-row">
        <strong>DATE:</strong> <span class="field-line" style="width: 20%;">{{ now()->format('Y-m-d') }}</span>
        <strong style="margin-left: 30px;">PAYMENT REFERENCE:</strong> <span class="field-line" style="width: 20%;">BATCH-{{ str_replace('BATCH-', '', $batch_id) }}</span>
        <strong style="margin-left: 30px;">AMOUNT PAID:</strong> <span class="field-line" style="width: 15%;">To be confirmed</span>
    </div>
    <div class="info-row">
        <strong>JOB ALLOCATED TO:</strong> <span class="field-line" style="width: 25%;">{{ $calibrations->first()->performedBy->name ?? 'Not Assigned' }}</span>
        <strong style="margin-left: 20px;">BY:</strong> <span class="field-line" style="width: 20%;">{{ $generated_by }}</span>
        <strong style="margin-left: 20px;">DATE:</strong> <span class="field-line" style="width: 15%;">{{ now()->format('Y-m-d') }}</span>
    </div>

    <!-- Equipment List Table -->
    <div class="section-title">Equipment List in Batch</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Equipment Name</th>
                <th width="15%">Serial Number</th>
                <th width="15%">Manufacturer</th>
                <th width="15%">Technician</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($calibrations as $index => $cal)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $cal->equipment_name }}</td>
                <td>{{ $cal->serial_number }}</td>
                <td>{{ $cal->manufacturer ?? 'N/A' }}</td>
                <td>{{ $cal->performedBy->name ?? 'Unassigned' }}</td>
                <td>
                    @if($cal->status == 'completed')
                        <span class="badge badge-completed">✓ Completed</span>
                    @elseif(in_array($cal->status, ['assigned', 'in_progress', 'testing']))
                        <span class="badge badge-progress">⏳ In Progress</span>
                    @else
                        <span class="badge badge-pending">⏰ Pending</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No equipment found in this batch.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. DESCRIPTION OF WORK DONE -->
    <div class="section-title">4. DESCRIPTION OF WORK DONE</div>
    <div class="info-row"><strong>NATURE OF WORK DONE:</strong></div>
    <div style="border: 1px solid #ddd; padding: 10px; min-height: 60px; margin: 5px 0;">
        Batch calibration of {{ $total_items }} equipment items for {{ $customer->company_name ?? 'customer' }}.
        @if($completed > 0)
            {{ $completed }} items completed successfully.
        @endif
        @if($in_progress > 0)
            {{ $in_progress }} items in progress.
        @endif
        @if($pending > 0)
            {{ $pending }} items pending.
        @endif
    </div>
    <div class="info-row">
        <strong>DONE BY:</strong> <span class="field-line" style="width: 15%;">{{ $calibrations->first()->performedBy->name ?? '__________' }}</span>
        <strong>STAFF NO:</strong> <span class="field-line" style="width: 15%;">__________</span>
        <strong>SIGN:</strong> <span class="field-line" style="width: 15%;">__________</span>
        <strong>DATE:</strong> <span class="field-line" style="width: 15%;">__________</span>
    </div>

    <!-- REMARKS -->
    <div class="section-title">REMARKS</div>
    <div style="border: 1px solid #ddd; padding: 10px; min-height: 60px; margin: 5px 0;">
        Batch processing in progress. All equipment will be calibrated according to the selected standards.
    </div>
    <div class="info-row">
        <strong>CHECKED BY:</strong> <span class="field-line" style="width: 15%;">__________</span>
        <strong>STAFF NO:</strong> <span class="field-line" style="width: 15%;">__________</span>
        <strong>SIGN:</strong> <span class="field-line" style="width: 15%;">__________</span>
        <strong>DATE:</strong> <span class="field-line" style="width: 15%;">__________</span>
    </div>

    <!-- Certificate -->
    <div class="section-title">CERTIFICATE</div>
    <div class="info-row">
        <strong>REPORT/CERTIFICATE DONE BY:</strong> <span class="field-line" style="width: 25%;">__________</span>
        <strong>SIGN:</strong> <span class="field-line" style="width: 15%;">__________</span>
    </div>
    <div class="info-row">
        <strong>REPORT/CERTIFICATE NO.:</strong> <span class="field-line" style="width: 40%;">To be generated after completion</span>
    </div>

    <!-- Collection -->
    <div class="section-title">COLLECTION</div>
    <div class="info-row">
        <strong>COLLECTED BY:</strong> <span class="field-line" style="width: 25%;">__________</span>
        <strong>ID/STAFF NO:</strong> <span class="field-line" style="width: 20%;">__________</span>
    </div>
    <div class="info-row">
        <strong>SIGN:</strong> <span class="field-line" style="width: 20%;">__________</span>
        <strong>DATE:</strong> <span class="field-line" style="width: 20%;">__________</span>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} - Meter Central Laboratory</p>
        <p>Batch Job Card #{{ str_replace('BATCH-', '', $batch_id) }} | Generated: {{ $generated_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
