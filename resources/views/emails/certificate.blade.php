<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calibration Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #1a237e; padding-bottom: 15px; }
        .header h1 { color: #1a237e; margin: 0; }
        .content { padding: 20px 0; }
        .button { display: inline-block; background: #1a237e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>METER CENTRAL LABORATORY</h1>
            <p>Calibration Certificate</p>
        </div>

        <div class="content">
            <p>Dear {{ $certificate->customer->company_name ?? 'Customer' }},</p>

            <p>Please find attached the calibration certificate for your equipment.</p>

            <p><strong>Certificate Number:</strong> {{ $certificate->certificate_number }}</p>
            <p><strong>Equipment:</strong> {{ $certificate->equipment_name }}</p>
            <p><strong>Serial Number:</strong> {{ $certificate->serial_number }}</p>
            <p><strong>Issue Date:</strong> {{ $certificate->approved_at ? \Carbon\Carbon::parse($certificate->approved_at)->format('d M Y') : now()->format('d M Y') }}</p>

            <p>You can also download the certificate from the link below:</p>

            <p style="text-align: center;">
                <a href="{{ route('certificates.download', $certificate) }}" class="button">Download Certificate</a>
            </p>

            <p>If you have any questions, please contact us:</p>
            <p>
                <strong>Meter Central Laboratory</strong><br>
                P.O. BOX 30099 - 00100, NAIROBI<br>
                Phone: 0722 225 313<br>
                Email: mcl@kplc.co.ke
            </p>
        </div>

        <div class="footer">
            <p>This is a system-generated certificate. For inquiries, contact Meter Central Laboratory.</p>
            <p>© {{ date('Y') }} Kenya Power & Lighting Company</p>
        </div>
    </div>
</body>
</html>
