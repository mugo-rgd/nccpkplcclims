<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>Certificate Ready for Approval</title>
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
            border-bottom: 4px solid #198754;
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
                Certificate Approval Required
            </p>
        </div>

        <div style="
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        ">
            <p>Dear Laboratory Manager,</p>

            <p>
                A calibration certificate has been checked by the
                supervisor and is ready for your approval.
            </p>

            <div style="
                margin: 22px 0;
                padding: 18px;
                background-color: #f8fafc;
                border: 1px solid #dce5f0;
                border-left: 4px solid #198754;
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

                <p style="margin: 0 0 8px;">
                    <strong>Checked By:</strong>
                    {{ $certificate->checkedBy?->name ?? 'Supervisor' }}
                </p>

                <p style="margin: 0;">
                    <strong>Checked At:</strong>
                    {{ $certificate->checked_at
                        ? \Carbon\Carbon::parse(
                            $certificate->checked_at
                        )->format('d M Y H:i')
                        : 'N/A' }}
                </p>
            </div>

            <p>
                Please review the certificate and approve or reject it
                through the Meter Central Laboratory System.
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
                        background-color: #198754;
                        border-radius: 5px;
                        font-weight: bold;
                        text-decoration: none;
                    "
                >
                    Review for Approval
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
