<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calibration Certificate</title>
    <style>
        @page {
            margin: 20px 25px;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.3;
        }

        /*==================================================
= HEADER (USED BY BOTH PAGE 1 & PAGE 2)
==================================================*/

.header-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    border-bottom:2px solid #1a237e;
    margin-bottom:6px;
}

.header-table tr{
    height:82px;
}

.header-table td{
    vertical-align:middle;
    border:none;
    padding:4px;
}

.header-table .logo-cell{
    width:18%;
    text-align:left;
}

.header-table .title-cell{
    width:64%;
    text-align:center;
}

.header-table .qrcode-cell{
    width:18%;
    text-align:center;
}

.header-table .logo-cell img{
    width:auto;
    height:50px;
    max-height:50px;
}

.header-table .title-cell h1{
    margin:0;
    font-size:16px;
    color:#1a237e;
    letter-spacing:1px;
}

.header-table .title-cell h2{
    margin:2px 0;
    font-size:13px;
}

.header-table .title-cell p{
    margin:1px 0;
    font-size:10px;
}

.header-table .cert-number{
    font-size:11px;
    font-weight:bold;
}

/* QR IMAGE */
.header-table .qrcode-cell img{
    width:65px;
    height:65px;
    display:block;
    margin:0 auto;
}

        /* Sections */
        .section {
            margin: 3px 0;
        }

        .section-title {
            background: #1a237e;
            color: white;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 11px;
            border-radius: 2px;
        }

        .section-content {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-top: none;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0;
        }

        table th {
            background: #f0f0f0;
            border: 1px solid #ddd;
            padding: 2px 5px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        table td {
            border: 1px solid #ddd;
            padding: 2px 5px;
            font-size: 10px;
        }

        .table-borderless td {
            border: none;
            padding: 1px 3px;
        }

        .table-borderless td:first-child {
            font-weight: bold;
            width: 25%;
        }

        /* Results */
        .badge-pass {
            background: #d4edda;
            color: #155724;
            padding: 1px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }

        .badge-fail {
            background: #f8d7da;
            color: #721c24;
            padding: 1px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }

        .status-badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .status-generated { background: #fff3cd; color: #856404; }
        .status-checked { background: #cce5ff; color: #004085; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-sent { background: #d1ecf1; color: #0c5460; }
        .status-issued { background: #d4edda; color: #155724; }

        /* Signatures */
        .signature-section {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
        }

        .signature-box {
            display: inline-block;
            width: 23%;
            margin-right: 2%;
            text-align: center;
            vertical-align: top;
        }

        .signature-box p {
            margin: 1px 0;
            font-size: 10px;
        }

        .signature-box .sig-label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 18px;
            margin-top: 2px;
        }

        .signature-image {
            max-height: 30px;
            max-width: 80px;
        }

        /* Footer */
        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .footer p {
            margin: 1px 0;
        }

        .footer .footer-grid {
            display: table;
            width: 100%;
            margin-top: 3px;
        }

        .footer .footer-grid .col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            font-size: 9px;
            color: #666;
            padding: 0 5px;
        }

        .footer .footer-grid .col-left {
            text-align: left;
        }

        .footer .footer-grid .col-right {
            text-align: right;
        }

        /* Utilities */
        .text-muted { color: #6c757d; }
        .text-center { text-align: center; }
        .page-break { page-break-before: always; }

        /* Test Results Page Styles */
        .results-header {
            text-align: center;
            margin-bottom: 6px;
        }

        .results-header h2 {
            color: #1a237e;
            margin: 0;
            font-size: 14px;
        }

        .results-header p {
            color: #555;
            font-size: 10px;
            margin: 1px 0;
        }

        /* Sample Certificate Style Table */
        .cert-results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .cert-results-table th {
            background: #1a237e;
            color: white;
            padding: 3px 4px;
            text-align: center;
            font-size: 8.5px;
            font-weight: bold;
            border: 1px solid #1a237e;
        }

        .cert-results-table td {
            padding: 2px 4px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 8.5px;
        }

        .cert-results-table .test-title {
            text-align: left;
            font-weight: bold;
            background: #f8f9fa;
            font-size: 8.5px;
        }

        .cert-results-table .applied-value {
            font-size: 8px;
            color: #333;
        }

        .cert-results-table .error-pass {
            color: #155724;
            font-weight: bold;
        }

        .cert-results-table .error-fail {
            color: #721c24;
            font-weight: bold;
        }

        .cert-results-table .pass-badge {
            background: #d4edda;
            color: #155724;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }

        .cert-results-table .fail-badge {
            background: #f8d7da;
            color: #721c24;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }

        .cert-results-table .row-alt {
            background: #f9f9f9;
        }

        .cert-results-table .register-row {
            background: #f0f4ff;
            font-size: 8px;
            color: #555;
        }

        .cert-results-table .trial-row {
            background: #f5f5f5;
            font-size: 7.5px;
            color: #666;
        }

        .cert-results-table .col-test-title { width: 18%; }
        .cert-results-table .col-applied { width: 28%; }
        .cert-results-table .col-error { width: 14%; }
        .cert-results-table .col-uncertainty { width: 14%; }
        .cert-results-table .col-result { width: 10%; }

        .comments-section {
            margin-top: 5px;
            padding: 4px 8px;
            background: #f8f9fa;
            border-radius: 3px;
            border-left: 3px solid #1a237e;
            font-size: 9px;
            font-style: italic;
            color: #555;
        }

        .overall-result-box {
            margin-top: 5px;
            padding: 4px 10px;
            border-radius: 3px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid;
        }

        .overall-pass {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .overall-fail {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .machine-details {
            margin-top: 4px;
            padding: 3px 8px;
            background: #e8eaf6;
            border-radius: 3px;
            font-size: 9px;
            color: #555;
        }

        .test-report-attachment {
            background: #e3f2fd;
            border-left: 3px solid #0d47a1;
            padding: 3px 8px;
            margin: 3px 0;
            border-radius: 3px;
            font-size: 9px;
        }

        .compact-table td {
            padding: 1px 4px;
            font-size: 10px;
        }

        /* Footer grid for page 1 */
        .footer-signatures {
            margin-top: 5px;
            padding: 5px 0;
            border-top: 1px solid #ddd;
        }

        .footer-signatures .sig-row {
            display: table;
            width: 100%;
            margin: 2px 0;
        }

        .footer-signatures .sig-row .sig-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            font-size: 9px;
            color: #333;
            padding: 2px 0;
        }

        .footer-signatures .sig-row .sig-item strong {
            display: block;
            font-size: 8px;
            color: #666;
            font-weight: normal;
        }

        .footer-signatures .sig-row .sig-item .name {
            font-weight: bold;
            font-size: 10px;
        }

        .footer-signatures .signature-image-wrapper {
            height: 26px;
            margin: 1px 0;
            text-align: center;
        }

        .footer-signatures .signature-image {
            display: inline-block;
            width: auto;
            max-width: 75px;
            height: auto;
            max-height: 25px;
        }

        .footer-signatures .designation {
            display: block;
            margin-top: 1px;
            color: #777;
            font-size: 7px;
            line-height: 1.1;
        }

        .footer-signatures .signature-date {
            display: block;
            margin-top: 1px;
            color: #999;
            font-size: 7px;
        }

        .footer-status {
            margin-top: 3px;
            padding: 3px 0;
            text-align: center;
            font-size: 9px;
            color: #555;
        }

        /* Traceability Table */
        .traceability-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 3px;
        }

        .traceability-table th {
            background: #1a237e;
            color: white;
            padding: 2px 4px;
            text-align: center;
            font-size: 8px;
            border: 1px solid #1a237e;
        }

        .traceability-table td {
            padding: 2px 4px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        .traceability-table .label-cell {
            font-weight: bold;
            background: #f8f9fa;
            text-align: left;
        }

        .traceability-title {
            font-weight: bold;
            font-size: 9px;
            margin-top: 3px;
            margin-bottom: 2px;
            color: #1a237e;
        }

    </style>
</head>
<body>
    <?php
    $calibration = $certificate->calibration;
    $calibrationItem = $certificate->calibrationItem;

    if ($certificate->calibration_item_id && !$calibrationItem) {
        throw new \RuntimeException(
            'Certificate equipment item could not be loaded.'
        );
    }

    $equipmentSource = $calibrationItem ?? $calibration;
?>

    <!-- ============================================ -->
    <!-- PAGE 1: CERTIFICATE INFORMATION             -->
    <!-- ============================================ -->
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <?php
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
                ?>

                <?php if($logoBase64): ?>
                    <img src="<?php echo e($logoBase64); ?>" alt="MCL Logo">
                <?php else: ?>
                    <div style="font-size: 18px; font-weight: bold; color: #1a237e;">MCL</div>
                <?php endif; ?>
            </td>
            <td class="title-cell">
                <h1>METER CENTRAL LABORATORY</h1>
                <h2>Calibration Certificate</h2>
                <p>P.O. BOX 30099 - 00100, NAIROBI</p>
                <p class="cert-number">Certificate No: <strong><?php echo e($certificate->certificate_number ?? 'N/A'); ?></strong></p>
                <p style="font-size: 9px; color: #777; margin: 0;">Reference: <?php echo e($certificate->reference_number ?? 'N/A'); ?></p>
            </td>

    <?php
    $qrBase64 = null;
    $qrUrl = null;

    try {
        /*
         * Generates:
         * http://ncckplclims.test/certificates/8/preview
         */
        $qrUrl = route('certificates.preview', [
            'certificate' => $certificate->id,
        ]);

        /*
         * Generate SVG instead of PNG.
         * SVG does not require the Imagick PHP extension.
         */
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(120)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($qrUrl);

        $qrBase64 = base64_encode($qrSvg);
    } catch (\Throwable $e) {
        /*
         * Temporarily log the actual error instead of silently hiding it.
         */
        \Illuminate\Support\Facades\Log::error(
            'Certificate QR generation failed',
            [
                'certificate_id' => $certificate->id ?? null,
                'error' => $e->getMessage(),
            ]
        );

        $qrBase64 = null;
    }
?>

    <td class="qrcode-cell">
    <?php if($qrBase64): ?>
        <img
            src="data:image/svg+xml;base64,<?php echo e($qrBase64); ?>"
            alt="Certificate verification QR code"
            style="width: 65px; height: 65px;"
        >

        <div style="
            margin-top: 2px;
            font-size: 7px;
            line-height: 1.1;
            text-align: center;
            color: #555;
        ">
            Scan to verify
        </div>
    <?php else: ?>
        <div style="font-size: 7px; color: #999;">
            QR unavailable
        </div>
    <?php endif; ?>
</td>
        </tr>
    </table>

    <!-- CUSTOMER DETAILS -->
    <div class="section">
        <div class="section-title">CUSTOMER DETAILS</div>
        <div class="section-content">
            <?php
                $customer = $certificate->customer ?? ($calibration->customer ?? null);
            ?>
            <table class="table-borderless" style="font-size: 10px;">
                <tr>
                    <td style="width: 18%;">Company Name:</td>
                    <td style="width: 32%;"><strong><?php echo e($customer->company_name ?? $customer->name ?? 'N/A'); ?></strong></td>
                    <td style="width: 18%;">Contact Person:</td>
                    <td style="width: 32%;"><?php echo e($customer->contact_person ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td><?php echo e($customer->address ?? 'N/A'); ?></td>
                    <td>Phone:</td>
                    <td><?php echo e($customer->phone ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td colspan="3"><?php echo e($customer->email ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- EQUIPMENT DETAILS -->
    <div class="section">
        <div class="section-title">EQUIPMENT DETAILS</div>
        <div class="section-content">
            <table class="compact-table">
                <tr>
                    <td width="12%"><strong>Equipment:</strong></td>
                    <td width="23%"><?php echo e($equipmentSource?->equipment_name ?? $certificate->equipment_name ?? 'N/A'); ?></td>
                    <td width="10%"><strong>Serial #:</strong></td>
                    <td width="20%"><?php echo e($equipmentSource?->serial_number ?? $certificate->serial_number ?? 'N/A'); ?></td>
                    <td width="10%"><strong>Model:</strong></td>
                    <td width="25%"><?php echo e($equipmentSource?->model ?? $certificate->model ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td><strong>Manufacturer:</strong></td>
                    <td><?php echo e($equipmentSource?->manufacturer ?? $certificate->manufacturer ?? 'N/A'); ?></td>
                    <td><strong>Type:</strong></td>
                    <td><?php echo e($equipmentSource?->equipment_type ?? $certificate->equipment_type ?? 'N/A'); ?></td>
                    <td><strong>Accuracy:</strong></td>
                    <td><?php echo e($equipmentSource?->accuracy_class ?? $certificate->accuracy_class ?? 'N/A'); ?></td>

                </tr>
                <tr>
                    <td><strong>Ratings:</strong></td>
                    <td colspan="5"><?php echo e($equipmentSource?->ratings ?? $certificate->ratings ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- SERVICE DETAILS -->
    <div class="section">
        <div class="section-title">SERVICE DETAILS</div>
        <div class="section-content">
            <table style="font-size: 10px;">
                <tr>
                    <td width="12%"><strong>Service:</strong></td>
                    <td width="38%"><?php echo e($certificate->service_name ?? 'N/A'); ?></td>
                    <td width="12%"><strong>Method:</strong></td>
                    <td width="38%"><?php echo e($certificate->service_method ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td><strong>Notes:</strong></td>
                    <td colspan="3"><?php echo e($certificate->service_notes ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
    </div>

   <!-- ============================================ -->
<!-- TEST CONDITIONS / ENVIRONMENTAL CONDITIONS   -->
<!-- ============================================ -->

<div class="section">

    <div class="section-title">
        TEST CONDITIONS / ENVIRONMENTAL CONDITIONS
    </div>

    <div
        class="section-content"
        style="padding: 3px 5px;"
    >

        <?php
    $conditionsSource = $calibrationItem ?? $calibration;

    $temperatureStart = $conditionsSource?->temperature_start;
    $temperatureEnd = $conditionsSource?->temperature_end;

    $humidityStart = $conditionsSource?->humidity_start;
    $humidityEnd = $conditionsSource?->humidity_end;

    $supplyVoltage = $conditionsSource?->supply_voltage;
    $supplyFrequency = $conditionsSource?->supply_frequency;

    $parametersRecordedBy = $conditionsSource?->testParametersRecordedBy;
    $parametersRecordedAt = $conditionsSource?->test_parameters_recorded_at;
?>


        <table
            style="
                width: 100%;
                border-collapse: collapse;
                font-size: 9px;
                margin: 0;
            "
        >

            <thead>

                <tr>

                    <th
                        style="
                            width: 20%;
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        CONDITION
                    </th>


                    <th
                        style="
                            width: 15%;
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        START
                    </th>


                    <th
                        style="
                            width: 15%;
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        END
                    </th>


                    <th
                        style="
                            width: 15%;
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        VALUE
                    </th>


                    <th
                        style="
                            width: 10%;
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        UNIT
                    </th>


                    <th
                        style="
                            width: 25%;
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        REMARK
                    </th>

                </tr>

            </thead>


            <tbody>

                
                
                

                <tr>

                    <td
                        style="
                            padding: 3px 4px;
                            font-weight: bold;
                        "
                    >
                        Temperature
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        <?php echo e(!is_null($temperatureStart)
                                ? number_format(
                                    (float) $temperatureStart,
                                    1
                                )
                                : 'N/A'); ?>

                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        <?php echo e(!is_null($temperatureEnd)
                                ? number_format(
                                    (float) $temperatureEnd,
                                    1
                                )
                                : 'N/A'); ?>

                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        <?php if(
                            !is_null($temperatureStart)
                            && !is_null($temperatureEnd)
                        ): ?>

                            <?php echo e(number_format(
                                    (float) $temperatureStart,
                                    1
                                )); ?>

                            -
                            <?php echo e(number_format(
                                    (float) $temperatureEnd,
                                    1
                                )); ?>


                        <?php else: ?>

                            N/A

                        <?php endif; ?>
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        °C
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        Environmental
                    </td>

                </tr>


                
                
                

                <tr>

                    <td
                        style="
                            padding: 3px 4px;
                            font-weight: bold;
                        "
                    >
                        Relative Humidity
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        <?php echo e(!is_null($humidityStart)
                                ? number_format(
                                    (float) $humidityStart,
                                    1
                                )
                                : 'N/A'); ?>

                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        <?php echo e(!is_null($humidityEnd)
                                ? number_format(
                                    (float) $humidityEnd,
                                    1
                                )
                                : 'N/A'); ?>

                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        <?php if(
                            !is_null($humidityStart)
                            && !is_null($humidityEnd)
                        ): ?>

                            <?php echo e(number_format(
                                    (float) $humidityStart,
                                    1
                                )); ?>

                            -
                            <?php echo e(number_format(
                                    (float) $humidityEnd,
                                    1
                                )); ?>


                        <?php else: ?>

                            N/A

                        <?php endif; ?>
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        %
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        Environmental
                    </td>

                </tr>


                
                
                

                <tr>

                    <td
                        style="
                            padding: 3px 4px;
                            font-weight: bold;
                        "
                    >
                        Supply Voltage
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                            color: #999;
                        "
                    >
                        -
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                            color: #999;
                        "
                    >
                        -
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                            font-weight: bold;
                        "
                    >
                        <?php echo e(!is_null($supplyVoltage)
                                ? number_format(
                                    (float) $supplyVoltage,
                                    0
                                )
                                : 'N/A'); ?>

                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        V
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        Supply Condition
                    </td>

                </tr>


                
                
                

                <tr>

                    <td
                        style="
                            padding: 3px 4px;
                            font-weight: bold;
                        "
                    >
                        Supply Frequency
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                            color: #999;
                        "
                    >
                        -
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                            color: #999;
                        "
                    >
                        -
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                            font-weight: bold;
                        "
                    >
                        <?php echo e(!is_null($supplyFrequency)
                                ? number_format(
                                    (float) $supplyFrequency,
                                    0
                                )
                                : 'N/A'); ?>

                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        Hz
                    </td>


                    <td
                        style="
                            padding: 3px 4px;
                            text-align: center;
                        "
                    >
                        Supply Condition
                    </td>

                </tr>

            </tbody>

        </table>


        
        
        

        <div
            style="
                margin-top: 4px;
                padding: 3px 6px;
                background: #f8f9fa;
                border-left: 3px solid #1a237e;
                font-size: 8.5px;
                line-height: 1.4;
            "
        >

            <strong>Environmental Conditions:</strong>

            Temperature:

            <strong>
                <?php if(
                    !is_null($temperatureStart)
                    && !is_null($temperatureEnd)
                ): ?>

                    <?php echo e(number_format(
                            (float) $temperatureStart,
                            1
                        )); ?>

                    -
                    <?php echo e(number_format(
                            (float) $temperatureEnd,
                            1
                        )); ?>

                    °C

                <?php else: ?>

                    N/A

                <?php endif; ?>
            </strong>


            &nbsp;&nbsp;|&nbsp;&nbsp;


            Relative Humidity:

            <strong>
                <?php if(
                    !is_null($humidityStart)
                    && !is_null($humidityEnd)
                ): ?>

                    <?php echo e(number_format(
                            (float) $humidityStart,
                            1
                        )); ?>

                    -
                    <?php echo e(number_format(
                            (float) $humidityEnd,
                            1
                        )); ?>

                    %

                <?php else: ?>

                    N/A

                <?php endif; ?>
            </strong>


            &nbsp;&nbsp;|&nbsp;&nbsp;


            Supply:

            <strong>

                <?php echo e(!is_null($supplyVoltage)
                        ? number_format(
                            (float) $supplyVoltage,
                            0
                        )
                        : 'N/A'); ?>


                V

                @

                <?php echo e(!is_null($supplyFrequency)
                        ? number_format(
                            (float) $supplyFrequency,
                            0
                        )
                        : 'N/A'); ?>


                Hz

            </strong>

        </div>


        
        
        

        <?php if(
            $parametersRecordedBy
            || $parametersRecordedAt
        ): ?>

            <div
                style="
                    margin-top: 3px;
                    text-align: right;
                    font-size: 7.5px;
                    color: #777;
                "
            >

                <strong>Conditions Recorded By:</strong>

                <?php echo e($parametersRecordedBy?->name
                    ?? 'N/A'); ?>


                <?php if($parametersRecordedAt): ?>

                    &nbsp;|&nbsp;

                    <strong>Date:</strong>

                    <?php echo e(\Carbon\Carbon::parse(
                            $parametersRecordedAt
                        )->format(
                            'd/m/Y H:i'
                        )); ?>


                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

</div>



<?php
    /*
    |--------------------------------------------------------------------------
    | Determine whether this calibration is for a Meter
    |--------------------------------------------------------------------------
    |
    | Prefer the equipmentType relationship because equipment_type_id
    | itself is only an ID and does not tell the Blade what type it is.
    |
    */

    $equipmentTypeName =
        $equipmentSource?->equipmentType?->name
        ?? $equipmentSource?->equipment_type
        ?? '';

    $isMeter =
        str_contains(
            strtolower(trim($equipmentTypeName)),
            'meter'
        );
?>


<?php if($isMeter): ?>

    <div style="margin-top: 3px;">

        <table
            style="
                width: 100%;
                font-size: 9px;
                margin: 0;
                border-collapse: collapse;
            "
        >

            <thead>

                <tr>

                    <th
                        colspan="4"
                        style="
                            text-align: left;
                            padding: 3px 5px;
                            background: #e8eaf6;
                            color: #1a237e;
                            font-size: 9px;
                            font-weight: bold;
                        "
                    >
                        METER TEST CONFIGURATION
                    </th>

                </tr>

                <tr>

                    <th
                        style="
                            width: 25%;
                            text-align: center;
                        "
                    >
                        Current
                    </th>

                    <th
                        style="
                            width: 25%;
                            text-align: center;
                        "
                    >
                        Meter Constant
                    </th>

                    <th
                        style="
                            width: 25%;
                            text-align: center;
                        "
                    >
                        Accuracy Class
                    </th>

                    <th
                        style="
                            width: 25%;
                            text-align: center;
                        "
                    >
                        Wiring
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td style="text-align: center;">

                        <?php echo e($certificate->current
                            ?? 'N/A'); ?>


                    </td>


                    <td style="text-align: center;">

                        <?php echo e($certificate->equipment_constant
                            ?? 'N/A'); ?>


                    </td>


                    <td style="text-align: center;">

                        <?php echo e($certificate->equipment_class
                            ?? $calibration->accuracy_class
                            ?? 'N/A'); ?>


                    </td>


                    <td style="text-align: center;">

                        <?php echo e($certificate->wiring
                            ?? 'N/A'); ?>


                    </td>

                </tr>

            </tbody>

        </table>

    </div>

<?php endif; ?>

    <!-- STATEMENT OF CONFORMITY & METROLOGICAL TRACEABILITY -->
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; padding-right: 4px;">
            <div class="section">
                <div class="section-title" style="font-size: 10px;">STATEMENT OF CONFORMITY</div>
                <div class="section-content" style="padding: 3px 6px;">
                    <p style="font-size: 10px; margin: 0; text-align: justify;"><?php echo e($certificate->statement_conformity ?: 'Pending supervisor review — no conformity decision recorded.'); ?></p>
                </div>
            </div>
        </div>
        <div style="display: table-cell; width: 50%; padding-left: 4px;">
            <div class="section">
                <div class="section-title" style="font-size: 10px;">METROLOGICAL TRACEABILITY</div>
                <div class="section-content" style="padding: 3px 6px;">
                    <p style="font-size: 10px; margin: 0; text-align: justify;"><?php echo e($certificate->traceability_information ?: 'Pending supervisor review — metrological traceability not confirmed.'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- TRACEABILITY OF REFERENCE INSTRUMENTS -->
<?php
    $referenceInstruments = collect($referenceInstruments ?? []);
    // Supports direct controller previews that do not pass the service data.
    if ($referenceInstruments->isEmpty() && $calibrationItem) {
        $referenceInstruments = collect($calibrationItem->referenceInstruments);
        if ($calibrationItem->laboratoryEquipment && !$referenceInstruments->contains('id', $calibrationItem->laboratoryEquipment->id)) {
            $referenceInstruments->push($calibrationItem->laboratoryEquipment);
        }
    }
?>
<div class="section" style="margin-top: 2px;">
    <div class="section-title" style="font-size: 9px; background: #0d47a1;">TRACEABILITY OF REFERENCE INSTRUMENTS</div>
    <div class="section-content" style="padding: 4px 6px;">
        <table class="traceability-table">
            <thead><tr>
                <th>Instrument Description</th><th>Serial No.</th><th>Certificate No.</th>
                <th>Calibration Date</th><th>Calibration Due</th><th>Accuracy Class</th>
            </tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $referenceInstruments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instrument): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $pivot = $instrument->pivot ?? null;
                    $certNo = $pivot?->certificate_number ?: $instrument->reference_standard_cert_no ?: $instrument->traceability_certificate_no;
                    $calDate = $pivot?->calibration_date ?: $instrument->reference_standard_cal_date;
                    $dueDate = $pivot?->calibration_due_date ?: $instrument->reference_standard_due_date;
                    $accuracy = $pivot?->accuracy_class ?: $instrument->reference_standard_accuracy;
                    $isExpired = $dueDate && \Carbon\Carbon::parse($dueDate)->lt(
                        $calibrationItem?->testing_completed_at ?? $calibrationItem?->completed_at ?? $calibration?->completed_at ?? now()
                    );
                ?>
                <tr>
                    <td class="label-cell"><?php echo e($instrument->name ?: $instrument->description ?: 'Not recorded'); ?></td>
                    <td><?php echo e($instrument->serial_number ?: 'Not recorded'); ?></td>
                    <td><?php echo e($certNo ?: 'Not recorded'); ?></td>
                    <td><?php echo e($calDate ? \Carbon\Carbon::parse($calDate)->format('d/m/Y') : 'Not recorded'); ?></td>
                    <td><?php echo e($dueDate ? \Carbon\Carbon::parse($dueDate)->format('d/m/Y') : 'Not recorded'); ?><?php echo e($isExpired ? ' (Expired at test)' : ''); ?></td>
                    <td><?php echo e($accuracy ?: 'Not recorded'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="text-align:center;">No reference instruments recorded for this calibration item.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <p style="font-size:8px;margin:3px 0 0;">Reference instrument details are listed for verification. Traceability must be confirmed against valid calibration records before certificate approval.</p>
    </div>
</div>

    <!-- FOOTER PAGE 1 -->
    <div class="footer">
        <!-- Signatures Grid -->
       <?php
    /**
     * Resolve a user's signature for HTML/PDF output.
     *
     * signature_path example:
     * signatures/1783884001_patracia_ngaanga_signature.png
     */
    $resolveSignature = function ($user) {
        if (
            !$user
            || !$user->use_auto_signature
            || empty($user->signature_path)
        ) {
            return null;
        }

        $relativePath = ltrim($user->signature_path, '/');

        /*
         * Files stored through the public disk normally exist under:
         * storage/app/public/signatures/filename.png
         */
        $absolutePath = storage_path('app/public/' . $relativePath);

        if (!file_exists($absolutePath)) {
            return null;
        }

        /*
         * Convert the image to Base64.
         * This is more reliable for Dompdf than asset() URLs.
         */
        $mimeType = mime_content_type($absolutePath) ?: 'image/png';
        $imageData = base64_encode(file_get_contents($absolutePath));

        return "data:{$mimeType};base64,{$imageData}";
    };

    $generatedSignature = $resolveSignature($certificate->generatedBy);
    $checkedSignature   = $resolveSignature($certificate->checkedBy);
    $approvedSignature  = $resolveSignature($certificate->approvedBy);
    $issuedSignature    = $resolveSignature($certificate->issuedBy);
?>

<div class="footer-signatures">
    <div class="sig-row">
        <div class="sig-item">
            <strong>Generated By</strong>
            <div class="signature-image-wrapper">
                <?php if($generatedSignature): ?>
                    <img src="<?php echo e($generatedSignature); ?>" class="signature-image" alt="Generated By Signature">
                <?php endif; ?>
            </div>
            <span class="name"><?php echo e($certificate->generatedBy?->name ?? 'Not Generated'); ?></span>
            <?php if($certificate->generatedBy?->designation): ?>
                <span class="designation"><?php echo e($certificate->generatedBy->designation); ?></span>
            <?php endif; ?>
            <span class="signature-date">
                <?php echo e($certificate->generated_at ? \Carbon\Carbon::parse($certificate->generated_at)->format('d M Y') : ''); ?>

            </span>
        </div>

        <div class="sig-item">
            <strong>Checked By</strong>
            <div class="signature-image-wrapper">
                <?php if($checkedSignature): ?>
                    <img src="<?php echo e($checkedSignature); ?>" class="signature-image" alt="Checked By Signature">
                <?php endif; ?>
            </div>
            <span class="name"><?php echo e($certificate->checkedBy?->name ?? 'Not Checked'); ?></span>
            <?php if($certificate->checkedBy?->designation): ?>
                <span class="designation"><?php echo e($certificate->checkedBy->designation); ?></span>
            <?php endif; ?>
            <span class="signature-date">
                <?php echo e($certificate->checked_at ? \Carbon\Carbon::parse($certificate->checked_at)->format('d M Y') : ''); ?>

            </span>
        </div>

        <div class="sig-item">
            <strong>Approved By</strong>
            <div class="signature-image-wrapper">
                <?php if($approvedSignature): ?>
                    <img src="<?php echo e($approvedSignature); ?>" class="signature-image" alt="Approved By Signature">
                <?php endif; ?>
            </div>
            <span class="name"><?php echo e($certificate->approvedBy?->name ?? 'Not Approved'); ?></span>
            <?php if($certificate->approvedBy?->designation): ?>
                <span class="designation"><?php echo e($certificate->approvedBy->designation); ?></span>
            <?php endif; ?>
            <span class="signature-date">
                <?php echo e($certificate->approved_at ? \Carbon\Carbon::parse($certificate->approved_at)->format('d M Y') : ''); ?>

            </span>
        </div>

        <div class="sig-item">
            <strong>Issued By</strong>
            <div class="signature-image-wrapper">
                <?php if($issuedSignature): ?>
                    <img src="<?php echo e($issuedSignature); ?>" class="signature-image" alt="Issued By Signature">
                <?php endif; ?>
            </div>
            <span class="name"><?php echo e($certificate->issuedBy?->name ?? 'Not Issued'); ?></span>
            <?php if($certificate->issuedBy?->designation): ?>
                <span class="designation"><?php echo e($certificate->issuedBy->designation); ?></span>
            <?php endif; ?>
            <span class="signature-date">
                <?php echo e($certificate->issued_date ? \Carbon\Carbon::parse($certificate->issued_date)->format('d M Y') : ''); ?>

            </span>
        </div>
    </div>
</div>

        <!-- Dates and Status -->
        <div class="footer-status">
            <strong>Date of Issue:</strong> <?php echo e($certificate->issued_date ? \Carbon\Carbon::parse($certificate->issued_date)->format('d/m/Y') : 'Not issued'); ?>

            &nbsp;|&nbsp;
            <strong>Next Calibration:</strong> <?php echo e($certificate->expiry_date ? \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') : 'Not specified'); ?>

            &nbsp;|&nbsp;
            <strong>Certificate Status:</strong>
            <span class="status-badge status-<?php echo e($certificate->status ?? 'generated'); ?>">
                <?php echo e($certificate->status_label ?? ucfirst($certificate->status ?? 'Generated')); ?>

            </span>
        </div>

        <!-- Footer Note -->
        <p style="font-size: 8px; color: #999; margin-top: 3px;">
            This certificate is issued by Meter Central Laboratory. Unauthorized reproduction is prohibited.
        </p>
        <p style="font-size: 8px; color: #aaa; margin-top: 1px;">
            Page 1 of 2 | <?php echo e($certificate->certificate_number ?? 'N/A'); ?> | <?php echo e(now()->format('Y-m-d H:i:s')); ?>

        </p>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 2: TEST RESULTS                        -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <!-- HEADER PAGE 2 -->
<table class="header-table">
    <tr>
        <!-- LOGO -->
        <td class="logo-cell">
            <?php if($logoBase64): ?>
                <img src="<?php echo e($logoBase64); ?>" alt="MCL Logo">
            <?php else: ?>
                <div style="
                    font-size: 18px;
                    font-weight: bold;
                    color: #1a237e;
                ">
                    MCL
                </div>
            <?php endif; ?>
        </td>

        <!-- TITLE -->
        <td class="title-cell">
            <h1>METER CENTRAL LABORATORY</h1>
<h2>Calibration Certificate - Test Results</h2>
<p>P.O. BOX 30099 - 00100, NAIROBI</p>

        <p class="cert-number">
            Certificate No:
            <strong><?php echo e($certificate->certificate_number); ?></strong>
        </p>

        <p style="font-size:9px;color:#777;margin:0;">
            Reference: <?php echo e($certificate->reference_number); ?>

        </p>
        </td>

        <!-- QR CODE -->
        <td class="qrcode-cell">
            <?php if($qrBase64): ?>
                <img
                    src="data:image/svg+xml;base64,<?php echo e($qrBase64); ?>"
                    alt="Certificate verification QR code"
                    style="width: 65px; height: 65px;"
                >

                <div style="
                    margin-top: 2px;
                    font-size: 7px;
                    line-height: 1.1;
                    text-align: center;
                    color: #555;
                ">
                    Scan to verify
                </div>
            <?php else: ?>
                <div style="
                    font-size: 7px;
                    color: #999;
                    text-align: center;
                ">
                    QR unavailable
                </div>
            <?php endif; ?>
        </td>
    </tr>
</table>

    <div class="results-header">
        <p style="font-size: 10px; margin: 1px 0;">
            <strong>Certificate No:</strong> <?php echo e($certificate->certificate_number ?? 'N/A'); ?> &nbsp;|&nbsp;
            <strong>Equipment:</strong> <?php echo e($equipmentSource?->equipment_name ?? $certificate->equipment_name ?? 'N/A'); ?> &nbsp;|&nbsp;
            <strong>Serial No:</strong> <?php echo e($equipmentSource?->serial_number ?? $certificate->serial_number ?? 'N/A'); ?>

        </p>
        <p style="font-size: 9px; margin: 1px 0; color: #555;">
            Test Date: <?php echo e($certificate->test_date ? \Carbon\Carbon::parse($certificate->test_date)->format('d M Y') : 'N/A'); ?>

            <?php if($certificate->test_engineer): ?> | Engineer: <?php echo e($certificate->test_engineer); ?> <?php endif; ?>
            <?php if($certificate->machine_used): ?> | Machine: <?php echo e($certificate->machine_used); ?> <?php endif; ?>
        </p>
    </div>

  <?php
    if ($calibrationItem) {
        $testResults = $calibrationItem->testResults()
            ->get()
            ->map(fn ($result) => $result->toArray());
    } else {
        $testResults = collect(
            $certificate->test_results ?? []
        );
    }

    $referenceVoltage = $supplyVoltage;
    $frequency = $supplyFrequency;
    $nominalCurrent = $certificate->nominal_current ?? null;
?>

    <?php if($testResults && $testResults->count() > 0): ?>
        <!-- Test Results Table -->
        <table class="cert-results-table">
            <thead>
                <tr>
                    <th class="col-test-title">TEST TITLE</th>
                    <th class="col-applied">
                        APPLIED VALUE<br>
                        <span style="font-size: 6.5px; font-weight: normal;">
                            Ib = BASE CURRENT<br>Vr = REFERENCE VOLTAGE
                        </span>
                    </th>
                    <th class="col-error">ERROR (%)</th>
                    <th class="col-uncertainty">UNCERTAINTY</th>
                    <th class="col-result">PASS/FAIL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $hasRegisterTest = false;
                    $registerData = null;
                    $trialRows = [];
                ?>

                <?php $__currentLoopData = $testResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $result = (object) $result;
                        $testTitle = $result->test_name ?? $result->parameter ?? 'N/A';
                        $testType = strtolower((string) ($result->test_type ?? ''));
                        $isRegisterTest = str_contains($testType, 'register');
                        $assessment = null;
                        $recordedAssessment = $result->is_within_tolerance ?? null;
                        if ($recordedAssessment !== null) {
                            $assessment = filter_var($recordedAssessment, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        }
                        if ($assessment === null) {
                            $label = strtoupper(trim((string) ($result->result ?? '')));
                            if ($label === 'PASS') $assessment = true;
                            elseif ($label === 'FAIL') $assessment = false;
                        }
                        $errorPercentage = $result->error_percentage ?? null;
                        $uncertainty = $result->uncertainty ?? null;
                        $appliedStr = trim((string) ($result->applied_value ?? ''));
                        $extras = array_filter([
                            $result->power_factor ?? null,
                            $result->voltage ?? null,
                            $result->frequency ?? null,
                        ], fn ($v) => $v !== null && $v !== '');
                        if ($extras) $appliedStr .= ($appliedStr !== '' ? '; ' : '') . implode('; ', $extras);
                        if ($appliedStr === '') $appliedStr = 'N/A';
                        if ($isRegisterTest) {
                            $hasRegisterTest = true;
                            $registerData = $result;
                            continue;
                        }
                        $trialDataArray = $result->trial_data ?? [];
                        if (is_string($trialDataArray)) $trialDataArray = json_decode($trialDataArray, true) ?: [];
                        if (!is_array($trialDataArray)) $trialDataArray = [];
                        $numericTrials = array_values(array_filter($trialDataArray,
                            fn ($v) => $v !== null && $v !== '' && is_numeric($v)));
                        if ($numericTrials) {
                            $trialRows[] = [
                                'data' => $numericTrials,
                                'mean' => array_sum($numericTrials) / count($numericTrials),
                                'min' => min($numericTrials),
                                'max' => max($numericTrials),
                            ];
                        }
                    ?>
                    <tr class="<?php echo e($loop->index % 2 === 0 ? 'row-alt' : ''); ?>">
                        <td class="test-title"><?php echo e($testTitle); ?></td>
                        <td class="applied-value"><?php echo e($appliedStr); ?></td>
                        <td>
                            <?php if($errorPercentage !== null && is_numeric($errorPercentage)): ?>
                                <span class="<?php echo e($assessment === true ? 'error-pass' : ($assessment === false ? 'error-fail' : '')); ?>">
                                    <?php echo e(number_format((float) $errorPercentage, 4)); ?>

                                </span>
                            <?php else: ?> N/A <?php endif; ?>
                        </td>
                        <td>
                            <?php if($uncertainty !== null && is_numeric($uncertainty)): ?>
                                ±<?php echo e(number_format((float) $uncertainty, 6)); ?>

                            <?php else: ?> N/A <?php endif; ?>
                        </td>
                        <td>
                            <?php if($assessment === true): ?>
                                <span class="pass-badge">PASS</span>
                            <?php elseif($assessment === false): ?>
                                <span class="fail-badge">FAIL</span>
                            <?php else: ?>
                                <span style="color:#856404;font-weight:bold;">NOT EVALUATED</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <!-- Register Test Row -->
                <?php if($hasRegisterTest && $registerData): ?>
                    <tr class="register-row">
                        <td colspan="5" style="padding: 3px 6px;">
                            <table style="width: 100%; border: none; margin: 0;">
                                <tr>
                                    <td style="border: none; font-weight: bold; width: 15%;">REGISTER TEST</td>
                                    <td style="border: none; width: 30%;">
                                        <?php echo e($registerData->applied_value ?? 'N/A'); ?>

                                    </td>
                                    <td style="border: none; width: 22%;">
                                        <strong>START:</strong> <?php echo e($registerData->start_reading ?? 'N/A'); ?> kWh
                                    </td>
                                    <td style="border: none; width: 33%;">
                                        <strong>STOP:</strong> <?php echo e($registerData->stop_reading ?? 'N/A'); ?> kWh
                                    </td>
                                </tr>
                                <?php if(isset($registerData->energy_difference) || isset($registerData->dial_error)): ?>
                                    <tr>
                                        <td style="border: none;"></td>
                                        <td style="border: none; font-size: 8px; color: #666;" colspan="3">
                                            <?php if(isset($registerData->energy_difference)): ?>
                                                Energy Diff: <?php echo e($registerData->energy_difference); ?> kWh
                                            <?php endif; ?>
                                            <?php if(isset($registerData->dial_error)): ?>
                                                | Dial Error: <?php echo e(number_format($registerData->dial_error, 4)); ?>%
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- Trial Data Rows -->
                <?php $__currentLoopData = $trialRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="trial-row">
                        <td colspan="5" style="padding: 2px 6px;">
                            <strong>Trial Data:</strong>
                            <?php $__currentLoopData = $trial['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e(number_format((float) $value, 4)); ?><?php if($index < count($trial['data']) - 1): ?>, <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            | Mean: <?php echo e(number_format($trial['mean'], 4)); ?>

                            | Min: <?php echo e(number_format($trial['min'], 4)); ?>

                            | Max: <?php echo e(number_format($trial['max'], 4)); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <!-- Overall Result: report recorded assessment, do not calculate tolerance -->
        <?php
            $assessments = $testResults->map(function ($result) {
                $value = data_get($result, 'is_within_tolerance');
                if ($value !== null) {
                    $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($parsed !== null) return $parsed;
                }
                $label = strtoupper(trim((string) data_get($result, 'result', '')));
                return $label === 'PASS' ? true : ($label === 'FAIL' ? false : null);
            });
            $overallResult = $assessments->containsStrict(false) ? 'FAIL'
                : ($assessments->isNotEmpty() && !$assessments->containsStrict(null) ? 'PASS' : 'NOT EVALUATED');
        ?>
        <div class="overall-result-box <?php echo e($overallResult === 'PASS' ? 'overall-pass' : ($overallResult === 'FAIL' ? 'overall-fail' : '')); ?>">
            OVERALL RESULT: <?php echo e($overallResult); ?>

        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 15px; color: #999; font-size: 11px;">
            <p>No detailed test results available for this certificate.</p>
        </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="comments-section">
        <strong>COMMENTS:</strong>
        <span style="font-size: 9px;"><?php echo e($certificate->test_comments ?: 'No additional comments recorded.'); ?></span>
    </div>

    <!-- Machine Details -->
    <?php if(!empty($certificate->machine_used) || !empty($certificate->test_engineer)): ?>
        <div class="machine-details">
            <strong>Test Machine:</strong> <?php echo e($certificate->machine_used ?? 'N/A'); ?> |
            <strong>Serial:</strong> <?php echo e($certificate->machine_serial ?? 'N/A'); ?> |
            <strong>Engineer:</strong> <?php echo e($certificate->test_engineer ?? 'N/A'); ?>

        </div>
    <?php endif; ?>

    <!-- Test Report Attachment -->
    <?php
        $reportItem = $calibrationItem ?? $certificate->calibrationItem ?? null;
        $reportPath = $reportItem?->test_report_path ?? $certificate->test_report_attachment_path;
        $reportName = $reportItem?->test_report_original_name ?? $certificate->test_report_original_name ?? 'Test Machine Report';
    ?>
    <?php if(filled($reportPath)): ?>
        <div class="test-report-attachment">
            <strong>Test Machine Report Attached:</strong> <?php echo e($reportName); ?>

        </div>
    <?php endif; ?>

    <!-- FOOTER PAGE 2 -->
    <div class="footer">
        <p style="font-size: 8px; color: #999;">This certificate is issued by Meter Central Laboratory.</p>
        <p style="font-size: 8px; color: #aaa; margin-top: 1px;">
            Page 2 of 2 | <?php echo e($certificate->certificate_number ?? 'N/A'); ?> | <?php echo e(now()->format('Y-m-d H:i:s')); ?>

        </p>
    </div>

</body>
</html>
<?php /**PATH G:\project\ncckplclims\resources\views/admin/certificates/certificate-pdf.blade.php ENDPATH**/ ?>