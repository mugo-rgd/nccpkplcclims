<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1a237e;
            font-size: 22px;
            margin: 0;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
        }
        .content {
            margin: 20px 0;
        }
        .content p {
            color: #333;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: #1a237e;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover {
            background: #0d1450;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 KPLC Meter Lab</h1>
            <p>Password Reset Request</p>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            <p>We received a request to reset your password for your KPLC Meter Laboratory account.</p>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $resetLink }}" class="btn">Reset Password</a>
            </div>

            <div class="warning">
                <strong>⚠️ Important:</strong> This link will expire in 24 hours.
                <br>
                <span style="font-size: 12px;">If you did not request this, please ignore this email.</span>
            </div>

            <p style="font-size: 13px; color: #666;">
                <strong>Password Requirements:</strong>
                <br>
                • Minimum 8 characters
                <br>
                • At least 1 capital letter (A-Z)
                <br>
                • At least 1 special character (!@#$%^&*(),.?":{}|<>)
            </p>

            <p style="font-size: 13px; color: #666; margin-top: 20px;">
                If you're having trouble clicking the button, copy and paste this link into your browser:
                <br>
                <span style="word-break: break-all; font-size: 12px; color: #1a237e;">
                    {{ $resetLink }}
                </span>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} KPLC Meter Central Laboratory. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
