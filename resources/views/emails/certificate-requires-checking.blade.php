<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>Certificate Requires Checking</title>
</head>

<body style="
    margin: 0;
    padding: 24px;
    background-color: #f4f6f9;
    font-family: Arial, Helvetica, sans-serif;
">
    <div style="
        max-width: 620px;
        margin: 0 auto;
        overflow: hidden;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    ">
        <div style="
            padding: 24px 30px;
            text-align: center;
            border-bottom: 4px solid #1a237e;
        ">
            <h2 style="
                margin: 0;
                color: #1a237e;
                font-size: 22px;
            ">
                METER CENTRAL LABORATORY
            </h2>

            <p style="
                margin: 8px 0 0;
                color: #666666;
            ">
                Certificate Checking Required
            </p>
        </div>

        <div style="
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        ">
            <p>Dear Supervisor,</p>

            <p>
                A calibration certificate has been generated and
                requires your checking.
            </p>

            <div style="
                margin: 22px 0;
                padding: 18px;
                background-color: #f8fafc;
                border: 1px solid #dce5f0;
                border-left: 4px solid #1a237e;
                border-radius: 6px;
            ">
                <p style="margin: 0 0 8px;">
                    <strong>Certificate Number:</strong>
                    {{ $certificate->certificate_number }}
                </p>

                <p style="margin: 0 0 8px;">
                    <strong>Customer:</strong>
                    {{ $certificate->customer?->company_name
                        ?? $certificate->calibration?->customer?->company_name
                        ?? 'N/A' }}
                </p>

                <p style="margin: 0 0 8px;">
                    <strong>Equipment:</strong>
                    {{ $certificate->equipment_name
                        ?? $certificate->calibration?->equipment_name
                        ?? 'N/A' }}
                </p>

                <p style="margin: 0 0 8px;">
                    <strong>Serial Number:</strong>
                    {{ $certificate->serial_number
                        ?? $certificate->calibration?->serial_number
                        ?? 'N/A' }}
                </p>

                <p style="margin: 0;">
                    <strong>Generated At:</strong>
                    {{ $certificate->generated_at
                        ? \Carbon\Carbon::parse(
                            $certificate->generated_at
                        )->format('d M Y H:i')
                        : now()->format('d M Y H:i') }}
                </p>
            </div>

            <p>
                Please log in to the Meter Central Laboratory System,
                review the certificate and record your checking decision.
            </p>

            <div style="
                margin: 28px 0;
                text-align: center;
            ">
                <a
                    href="{{ route(
                        'certificates.show',
                        $certificate
                    ) }}"
                    style="
                        display: inline-block;
                        padding: 13px 30px;
                        color: #ffffff;
                        background-color: #1a237e;
                        border-radius: 5px;
                        font-weight: bold;
                        text-decoration: none;
                    "
                >
                    Review Certificate
                </a>
            </div>

            <p>
                Regards,<br>
                <strong>Meter Central Laboratory System</strong>
            </p>
        </div>

        <div style="
            padding: 18px 30px;
            color: #888888;
            background-color: #f8f9fa;
            border-top: 1px solid #dddddd;
            text-align: center;
            font-size: 12px;
        ">
            <p style="margin: 0 0 5px;">
                This is an automated notification.
            </p>

            <p style="margin: 0;">
                &copy; {{ date('Y') }}
                Kenya Power & Lighting Company PLC
            </p>
        </div>
    </div>
</body>
</html>
