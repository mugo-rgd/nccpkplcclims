<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>Calibration Certificate</title>
</head>

<body style="
    margin: 0;
    padding: 20px;
    background-color: #f4f6f9;
    font-family: Arial, Helvetica, sans-serif;
">
    <div style="
        max-width: 600px;
        margin: 0 auto;
        overflow: hidden;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    ">
        <div style="
            padding: 24px 30px;
            text-align: center;
            border-bottom: 3px solid #1a237e;
        ">
            <h1 style="
                margin: 0;
                color: #1a237e;
                font-size: 23px;
            ">
                METER CENTRAL LABORATORY
            </h1>

            <p style="
                margin: 8px 0 0;
                color: #666666;
                font-size: 15px;
            ">
                Calibration Certificate
            </p>
        </div>

        <div style="
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        ">
            <p>
                Dear
                <strong>
                    {{ $certificate->customer?->company_name ?? 'Customer' }}
                </strong>,
            </p>

            <p>
                Please find attached the calibration certificate
                for your equipment.
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
                    <strong>Issue Date:</strong>

                    {{ $certificate->approved_at
                        ? \Carbon\Carbon::parse(
                            $certificate->approved_at
                        )->format('d M Y')
                        : now()->format('d M Y') }}
                </p>
            </div>

            <p>
                You may also download the certificate using the
                button below:
            </p>

            <div style="
                margin: 28px 0;
                text-align: center;
            ">
                <a
                    href="{{ route(
                        'certificates.download',
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
                    Download Certificate
                </a>
            </div>

            <div style="
                margin-top: 25px;
                padding: 16px;
                background-color: #fff8e1;
                border-left: 4px solid #ffc107;
                border-radius: 5px;
                font-size: 13px;
            ">
                The attached certificate is an official calibration
                document. Please retain it for your records.
            </div>

            <p style="margin-top: 28px;">
                If you have any questions, please contact us:
            </p>

            <p>
                <strong>Meter Central Laboratory</strong><br>
                P.O. BOX 30099 - 00100, Nairobi<br>
                Phone: 0722 225 313<br>
                Email: mcl@kplc.co.ke
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
                This is a system-generated email.
                Please do not reply.
            </p>

            <p style="margin: 0;">
                &copy; {{ date('Y') }}
                Kenya Power & Lighting Company PLC
            </p>
        </div>
    </div>
</body>
</html>
