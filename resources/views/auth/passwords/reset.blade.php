<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KPLC Meter Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 480px;
        }
        .reset-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-logo h3 {
            color: #333;
            font-weight: 600;
        }
        .reset-logo p {
            color: #666;
            font-size: 14px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 100%;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .password-requirements ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        .password-requirements li {
            margin: 2px 0;
        }
        .requirement-met {
            color: #28a745;
        }
        .requirement-unmet {
            color: #dc3545;
        }
        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .toggle-password:hover {
            color: #333;
        }
        .password-container {
            position: relative;
        }
        .password-container input {
            padding-right: 40px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-logo">
            <h3>🔐 KPLC Meter Lab</h3>
            <p>Create New Password</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" value="{{ $email }}" disabled>
            </div>

            <div class="mb-3 password-container">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror"
                       id="password" name="password" required
                       placeholder="Enter new password">
                <span class="toggle-password" onclick="togglePassword('password')">
                    <i class="fas fa-eye" id="password-icon"></i>
                </span>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 password-container">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control"
                       id="password_confirmation" name="password_confirmation" required
                       placeholder="Confirm new password">
                <span class="toggle-password" onclick="togglePassword('password_confirmation')">
                    <i class="fas fa-eye" id="password-confirm-icon"></i>
                </span>
            </div>

            <div class="password-requirements mb-3">
                <small><strong>Password Requirements:</strong></small>
                <ul>
                    <li id="req-length"><i class="fas fa-circle text-secondary me-1" style="font-size: 6px;"></i> Minimum 8 characters</li>
                    <li id="req-capital"><i class="fas fa-circle text-secondary me-1" style="font-size: 6px;"></i> At least 1 capital letter (A-Z)</li>
                    <li id="req-special"><i class="fas fa-circle text-secondary me-1" style="font-size: 6px;"></i> At least 1 special character (!@#$%^&*(),.?":{}|<>)</li>
                </ul>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Reset Password
            </button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('login') }}" class="text-decoration-none text-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;

            // Check length (min 8)
            const lengthReq = document.getElementById('req-length');
            if (password.length >= 8) {
                lengthReq.className = 'requirement-met';
                lengthReq.innerHTML = '<i class="fas fa-check-circle me-1"></i> Minimum 8 characters';
            } else {
                lengthReq.className = 'requirement-unmet';
                lengthReq.innerHTML = '<i class="fas fa-times-circle me-1"></i> Minimum 8 characters';
            }

            // Check capital letter
            const capitalReq = document.getElementById('req-capital');
            if (/[A-Z]/.test(password)) {
                capitalReq.className = 'requirement-met';
                capitalReq.innerHTML = '<i class="fas fa-check-circle me-1"></i> At least 1 capital letter (A-Z)';
            } else {
                capitalReq.className = 'requirement-unmet';
                capitalReq.innerHTML = '<i class="fas fa-times-circle me-1"></i> At least 1 capital letter (A-Z)';
            }

            // Check special character
            const specialReq = document.getElementById('req-special');
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                specialReq.className = 'requirement-met';
                specialReq.innerHTML = '<i class="fas fa-check-circle me-1"></i> At least 1 special character (!@#$%^&*(),.?":{}|<>)';
            } else {
                specialReq.className = 'requirement-unmet';
                specialReq.innerHTML = '<i class="fas fa-times-circle me-1"></i> At least 1 special character (!@#$%^&*(),.?":{}|<>)';
            }
        });
    </script>
</body>
</html>
