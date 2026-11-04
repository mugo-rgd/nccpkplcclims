<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPLC Meter Laboratory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .welcome-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
        }
        .welcome-card h1 {
            font-size: 48px;
            font-weight: 700;
            color: #333;
        }
        .welcome-card .subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .features {
            text-align: left;
            margin: 30px 0;
        }
        .features li {
            padding: 8px 0;
            list-style: none;
        }
        .features li:before {
            content: " ";
        }
    </style>
</head>
<body>
    <div class="welcome-card">
        <div style="font-size: 80px;"></div>
        <h1>KPLC Meter Lab</h1>
        <p class="subtitle">Kenya Power Meter Laboratory Management System</p>

        <ul class="features">
            <li>Meter Calibration & Testing</li>
            <li>Certificate Generation</li>
            <li>Customer Management</li>
            <li>Equipment Tracking</li>
            <li>Comprehensive Reports</li>
        </ul>

        <div class="d-grid gap-3">
            @if(auth()->check())
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                    Login to System
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                    Create Account
                </a>
            @endif
        </div>

        @auth
            <p class="mt-3 text-muted small">
                Logged in as <strong>{{ auth()->user()->name }}</strong>
            </p>
        @endauth
    </div>
</body>
</html>