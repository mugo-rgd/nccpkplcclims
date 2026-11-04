<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Calibration Quotation</title>
</head>

<body style="
    margin: 0;
    padding: 24px;
    background: #f4f6f9;
    font-family: Arial, Helvetica, sans-serif;
">
    <div style="
        max-width: 620px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    ">
        <div style="
            padding: 24px 30px;
            text-align: center;
            border-bottom: 3px solid #1a237e;
        ">
            <h2 style="margin: 0; color: #1a237e;">
                METER CENTRAL LABORATORY
            </h2>

            <p style="margin: 8px 0 0; color: #666;">
                Calibration Quotation
            </p>
        </div>

        <div style="
            padding: 30px;
            color: #333;
            line-height: 1.6;
        ">
            <p>
                Dear
                <strong>
                    {{ $calibration->customer?->company_name ?? 'Customer' }}
                </strong>,
            </p>

            <p>
                Please find your calibration quotation details below.
            </p>

            <div style="
                margin: 20px 0;
                padding: 18px;
                background: #f8fafc;
                border-left: 4px solid #1a237e;
                border-radius: 6px;
            ">
                <p style="margin: 0 0 8px;">
                    <strong>Quotation Number:</strong>

                    {{ $calibration->quotation_number
                        ?? 'Quotation #' . $calibration->id }}
                </p>

                <p style="margin: 0 0 8px;">
                    <strong>Equipment:</strong>

                    {{ $calibration->equipment_name ?? 'N/A' }}
                </p>

                <p style="margin: 0 0 8px;">
                    <strong>Serial Number:</strong>

                    {{ $calibration->serial_number ?? 'N/A' }}
                </p>

                <p style="margin: 0 0 8px;">
                    <strong>Total Amount:</strong>

                    KSh {{ number_format(
                        (float) (
                            $calibration->amount_due
                            ?? $calibration->quotation_amount
                            ?? $calibration->total_amount
                            ?? 0
                        ),
                        2
                    ) }}
                </p>

                <p style="margin: 0;">
                    <strong>Valid Until:</strong>

                    {{ $calibration->valid_until
                        ? \Carbon\Carbon::parse(
                            $calibration->valid_until
                        )->format('d M Y')
                        : 'N/A' }}
                </p>
            </div>

            <div style="text-align: center; margin: 28px 0;">
                <a
                    href="{{ route(
                        'calibrations.quotation',
                        $calibration
                    ) }}"
                    style="
                        display: inline-block;
                        padding: 13px 28px;
                        color: #ffffff;
                        background: #1a237e;
                        text-decoration: none;
                        border-radius: 5px;
                        font-weight: bold;
                    "
                >
                    View Quotation
                </a>
            </div>

            <p>
                For any clarification, please contact the
                Meter Central Laboratory.
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
            color: #888;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
        ">
            <p style="margin: 0 0 5px;">
                This is a system-generated email.
            </p>

            <p style="margin: 0;">
                &copy; {{ date('Y') }}
                Kenya Power & Lighting Company PLC
            </p>
        </div>
    </div>
</body>
</html>
