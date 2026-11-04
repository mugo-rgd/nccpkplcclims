<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Request Form</title>
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
        .header-title .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a237e;
        }
        .header-title .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 5px 0;
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
        }
        .signature-section table {
            border: none;
        }
        .signature-section td {
            border: none;
            padding: 5px 10px 5px 0;
            vertical-align: top;
        }
        .signature-section hr {
            margin: 5px 0;
            border: 1px solid #333;
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
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            background: #17a2b8;
            color: white;
            margin: 2px;
        }
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
                    <div class="org-name">{{ config('app.name', 'Meter Central Laboratory (MCL)') }}</div>
                    <div class="doc-title">TESTING / CALIBRATION / REPAIR REQUEST FORM</div>
                </td>
                <td class="header-right">
                    <div class="doc-ref"><strong>REV.1.2024.11.20</strong></div>
                    <div class="doc-ref"><strong>REF:</strong> KP1/13B.1//3/6RF/{{ str_replace('BATCH-', '', $batch_id) }}</div>
                    <div class="doc-ref"><strong>DATE:</strong> {{ $generated_at->format('Y-m-d') }}</div>
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
        <strong style="margin-left: 20px;">TEL:</strong> <span class="field-line" style="width: 20%;">{{ $customer->phone ?? 'N/A' }}</span>
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
    <div class="section-title">3. TYPE OF SERVICE REQUESTED, REVIEWS AND METHOD TO BE USED</div>
    <table>
        <thead>
            <tr>
                <th width="25%">SERVICE</th>
                <th width="20%">METHODS</th>
                <th width="40%">NOTES</th>
                <th width="15%">✔ As Applicable</th>
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

    <!-- Verification and Review -->
    <div class="section-title">Verification & Review</div>
    <div class="info-row">
        <strong>Verification of item:</strong>
        <span class="field-line" style="width: 70%;">{{ $total_items }} equipment items received for calibration</span>
    </div>
    <div class="info-row">
        <strong>Review of Service and discussions:</strong>
        <span class="field-line" style="width: 70%;">Batch calibration service requested for {{ $customer->company_name ?? 'customer' }}</span>
    </div>
    <div class="info-row">
        <strong>Indication of Validity of Calibration Period required?</strong>
        <span style="font-weight: bold; color: #28a745; margin-left: 10px;">YES</span>
        <span style="margin-left: 20px;">NO</span>
    </div>
    <div class="info-row">
        <strong>If 'YES' above, please indicate Validity period:</strong>
        <span class="field-line" style="width: 40%;">12 Months</span>
    </div>

    <!-- Acknowledgement -->
    <div class="section-title">Acknowledgement of Review</div>
    <div class="signature-section">
        <table>
            <tr>
                <td width="50%">
                    <strong>1) CUSTOMER / REPRESENTATIVE:</strong>
                    <span class="field-line" style="width: 60%;">____________________</span>
                    <br>
                    <strong>CONTACT:</strong>
                    <span class="field-line" style="width: 50%;">{{ $customer->phone ?? '____________________' }}</span>
                    <br><br>
                    <strong>SIGNATURE:</strong>
                    <span class="field-line" style="width: 40%;">____________________</span>
                    <br>
                    <strong>DATE:</strong>
                    <span class="field-line" style="width: 30%;">____________________</span>
                </td>
                <td width="50%">
                    <strong>2) MCL REPRESENTATIVE:</strong>
                    <span class="field-line" style="width: 60%;">{{ $generated_by }}</span>
                    <br>
                    <strong>STAFF NO.:</strong>
                    <span class="field-line" style="width: 40%;">____________________</span>
                    <br><br>
                    <strong>SIGNATURE:</strong>
                    <span class="field-line" style="width: 40%;">____________________</span>
                    <br>
                    <strong>DATE:</strong>
                    <span class="field-line" style="width: 30%;">{{ $generated_at->format('Y-m-d') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Equipment List -->
    <div class="section-title">Equipment List (Batch)</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Equipment Name</th>
                <th width="15%">Serial Number</th>
                <th width="15%">Manufacturer</th>
                <th width="15%">Model</th>
                <th width="15%">Equipment Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse($calibrations as $index => $cal)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $cal->equipment_name }}</td>
                <td>{{ $cal->serial_number }}</td>
                <td>{{ $cal->manufacturer ?? 'N/A' }}</td>
                <td>{{ $cal->model ?? 'N/A' }}</td>
                <td>{{ $cal->equipmentType->name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No equipment found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ config('app.name') }} - Meter Central Laboratory</p>
        <p>Batch Request Form #{{ str_replace('BATCH-', '', $batch_id) }} | Generated: {{ $generated_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
