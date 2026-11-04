<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In | KPLC Meter Laboratory System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --kplc-blue: #163b75;
            --kplc-blue-dark: #0d2854;
            --kplc-blue-light: #2d65ad;
            --kplc-yellow: #f7c600;
            --surface: #ffffff;
            --surface-soft: #f7f9fc;
            --border: #d9e2ef;
            --text: #1f2937;
            --text-muted: #64748b;
            --danger: #dc3545;
            --success: #198754;
            --shadow: 0 24px 65px rgba(15, 23, 42, 0.18);
            --radius-lg: 20px;
            --radius-md: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            background: #000;
        }

        /* Video Background */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            background: #000;
            overflow: hidden;
        }

        .video-background video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
        }

        .video-background .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(247, 198, 0, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(22, 59, 117, 0.3) 0%, transparent 50%),
                linear-gradient(135deg, rgba(13, 40, 84, 0.85) 0%, rgba(22, 59, 117, 0.75) 100%);
        }

        .video-status {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.6rem;
            background: rgba(0, 0, 0, 0.4);
            padding: 4px 14px;
            border-radius: 999px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .video-status .pulse {
            width: 4px;
            height: 4px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }

        a {
            color: var(--kplc-blue);
        }

        /* Login - Compact */
        .login-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 16px;
        }

        .login-shell {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(260px, 0.7fr) minmax(320px, 0.8fr);
            overflow: hidden;
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 94vh;
        }

        /* Side Panel - Compact */
        .side-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 560px;
            padding: 30px 28px;
            background:
                linear-gradient(160deg, rgba(13, 40, 84, 0.98), rgba(22, 59, 117, 0.96)),
                var(--kplc-blue);
            color: #fff;
        }

        .side-panel::before,
        .side-panel::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
        }

        .side-panel::before {
            width: 200px;
            height: 200px;
            top: -100px;
            right: -80px;
        }

        .side-panel::after {
            width: 160px;
            height: 160px;
            bottom: -80px;
            left: -70px;
        }

        .brand,
        .welcome-content,
        .trust-note {
            position: relative;
            z-index: 1;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 44px;
            border-radius: 14px;
            background: var(--kplc-yellow);
            color: var(--kplc-blue-dark);
            font-size: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .brand h1 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .brand p {
            margin: 2px 0 0;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.7rem;
        }

        .welcome-content {
            max-width: 500px;
            padding: 10px 0;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            padding: 5px 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.65rem;
            font-weight: 700;
        }

        .welcome-content h2 {
            margin-bottom: 8px;
            font-size: clamp(1.3rem, 2.5vw, 1.8rem);
            font-weight: 800;
            line-height: 1.15;
        }

        .welcome-content > p {
            margin-bottom: 16px;
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .feature-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.78rem;
        }

        .feature-list i {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 22px;
            border-radius: 50%;
            background: rgba(247, 198, 0, 0.18);
            color: var(--kplc-yellow);
            font-size: 0.65rem;
        }

        .trust-note {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.7rem;
        }

        /* Form Panel - Compact */
        .form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 30px 32px 26px;
            background: transparent;
            overflow-y: auto;
        }

        .form-panel::-webkit-scrollbar {
            width: 3px;
        }

        .form-panel::-webkit-scrollbar-track {
            background: transparent;
        }

        .form-panel::-webkit-scrollbar-thumb {
            background: var(--kplc-blue-light);
            border-radius: 4px;
        }

        .mobile-brand {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .mobile-brand .brand-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 12px;
            font-size: 16px;
        }

        .mobile-brand h1 {
            margin: 0;
            color: var(--kplc-blue-dark);
            font-size: 0.85rem;
            font-weight: 800;
        }

        .mobile-brand p {
            margin: 1px 0 0;
            color: var(--text-muted);
            font-size: 0.65rem;
        }

        .form-heading {
            margin-bottom: 18px;
        }

        .form-heading h2 {
            margin: 0 0 4px;
            color: var(--kplc-blue-dark);
            font-size: clamp(1.2rem, 2vw, 1.6rem);
            font-weight: 800;
        }

        .form-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.78rem;
            line-height: 1.4;
        }

        .alert {
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.78rem;
            margin-bottom: 12px;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 16px;
        }

        .form-label {
            margin-bottom: 4px;
            color: #334155;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .required {
            color: var(--danger);
        }

        .field-wrapper {
            position: relative;
        }

        .field-icon {
            position: absolute;
            z-index: 2;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            font-size: 0.8rem;
        }

        .form-control {
            min-height: 40px;
            padding: 8px 38px 8px 36px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text);
            font-size: 0.85rem;
            backdrop-filter: blur(10px);
            width: 100%;
        }

        .form-control::placeholder {
            color: #a0aec0;
            font-size: 0.8rem;
        }

        .form-control:focus {
            border-color: var(--kplc-blue-light);
            box-shadow: 0 0 0 0.18rem rgba(45, 101, 173, 0.13);
            background: #ffffff;
        }

        .form-control.is-invalid {
            padding-right: 38px;
        }

        .password-toggle {
            position: absolute;
            z-index: 3;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: #64748b;
            font-size: 0.75rem;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            background: rgba(0, 0, 0, 0.05);
            color: var(--kplc-blue);
        }

        .invalid-feedback {
            margin-top: 4px;
            font-size: 0.7rem;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 12px 0 16px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 6px;
            min-height: auto;
            margin: 0;
            padding: 0;
        }

        .form-check-input {
            width: 14px;
            height: 14px;
            margin: 0;
            border: 1.5px solid #94a3b8;
            cursor: pointer;
            flex-shrink: 0;
        }

        .form-check-input:checked {
            background-color: var(--kplc-blue);
            border-color: var(--kplc-blue);
        }

        .form-check-label {
            color: #475569;
            font-size: 0.78rem;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--kplc-blue);
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            min-height: 42px;
            width: 100%;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--kplc-blue-dark), var(--kplc-blue-light));
            color: #fff;
            font-weight: 800;
            font-size: 0.85rem;
            box-shadow: 0 6px 20px rgba(22, 59, 117, 0.2);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            color: #fff;
            box-shadow: 0 8px 24px rgba(22, 59, 117, 0.3);
        }

        .btn-login:disabled {
            cursor: not-allowed;
            transform: none;
            opacity: 0.75;
        }

        .btn-login .button-loading .spinner-border {
            width: 0.9rem;
            height: 0.9rem;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 16px 0 12px;
            color: #94a3b8;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .register-card {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .register-card p {
            margin: 0 0 4px;
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .register-card a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--kplc-blue);
            font-size: 0.8rem;
            font-weight: 800;
            text-decoration: none;
        }

        .register-card a:hover {
            text-decoration: underline;
        }

        .security-note {
            margin-top: 14px;
            color: #94a3b8;
            text-align: center;
            font-size: 0.65rem;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .login-shell {
                max-width: 700px;
                grid-template-columns: 1fr;
                max-height: none;
            }

            .side-panel {
                min-height: auto;
                padding: 24px 28px;
            }

            .welcome-content {
                padding: 10px 0;
            }

            .feature-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .form-panel {
                padding: 28px 28px 22px;
                max-height: none;
            }

            .video-background video {
                opacity: 0.2;
            }
        }

        @media (max-width: 767.98px) {
            .login-page {
                align-items: flex-start;
                padding: 12px 10px;
            }

            .side-panel {
                display: none;
            }

            .form-panel {
                padding: 22px 18px 18px;
                background: rgba(255, 255, 255, 0.9);
                max-height: none;
            }

            .mobile-brand {
                display: flex;
            }

            .form-heading h2 {
                font-size: 1.1rem;
            }

            .video-status {
                bottom: 12px;
                font-size: 0.55rem;
                padding: 3px 10px;
            }
        }

        @media (max-width: 480px) {
            .login-page {
                padding: 0;
            }

            .login-shell {
                min-height: 100vh;
                border-radius: 0;
                box-shadow: none;
                background: rgba(255, 255, 255, 0.98);
            }

            .form-panel {
                justify-content: flex-start;
                padding: 18px 14px 14px;
                background: transparent;
            }

            .form-options {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            .video-status {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- Video Background -->
    <div class="video-background">
        @php
            $videoPath = public_path('videos/music-motional-lab.mp4');
            $videoExists = file_exists($videoPath);
            $videoUrl = $videoExists ? asset('videos/music-motional-lab.mp4') : null;
        @endphp

        <video
            id="loginVideo"
            autoplay
            muted
            loop
            playsinline
            preload="auto"
        >
            @if($videoExists)
                <source src="{{ $videoUrl }}" type="video/mp4">
            @endif

        </video>
        <div class="overlay"></div>
    </div>

    <!-- Video Status -->
    <div class="video-status">
        <span class="pulse" id="statusPulse"></span>
        <span>Laboratory</span>
        <span style="opacity:0.3;">•</span>
        <span id="videoStatusText" style="opacity:0.6;">Playing</span>
    </div>

    <!-- Login -->
    <main class="login-page">
        <div class="login-shell">

            <aside class="side-panel" aria-label="KPLC Meter Laboratory portal information">
                <div class="brand">
                    <div class="brand-icon" aria-hidden="true">
                        <i class="fa-solid fa-bolt"></i>
                    </div>

                    <div>
                        <h1>KPLC Meter Laboratory</h1>
                        <p>IESR Service Portal</p>
                    </div>
                </div>

                <div class="welcome-content">
                    <div class="welcome-badge">
                        <i class="fa-solid fa-flask-vial"></i>
                        Meter Calibration Laboratory
                    </div>

                    <h2>Welcome back to your laboratory portal.</h2>

                    <p>
                        Sign in to submit calibration requests, monitor service progress,
                        review quotations and access completed laboratory documents.
                    </p>

                    <ul class="feature-list">
                        <li>
                            <i class="fa-solid fa-list-check"></i>
                            <span>Track calibration requests</span>
                        </li>

                        <li>
                            <i class="fa-solid fa-certificate"></i>
                            <span>Access certificates and reports</span>
                        </li>

                        <li>
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span>Review quotations and invoices</span>
                        </li>

                        <li>
                            <i class="fa-solid fa-bell"></i>
                            <span>Receive service notifications</span>
                        </li>
                    </ul>
                </div>

                <div class="trust-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Secure access to the Kenya Power Meter Laboratory System.</span>
                </div>
            </aside>

            <section class="form-panel">
                <div class="mobile-brand">
                    <div class="brand-icon" aria-hidden="true">
                        <i class="fa-solid fa-bolt"></i>
                    </div>

                    <div>
                        <h1>KPLC Meter Laboratory</h1>
                        <p>Customer Service Portal</p>
                    </div>
                </div>

                <header class="form-heading">
                    <h2>Sign in to your account</h2>
                    <p>Enter your registered email address and password to continue.</p>
                </header>

                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>

                            <div>
                                <strong>Unable to sign in.</strong>

                                <ul class="mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form
                    id="loginForm"
                    method="POST"
                    action="{{ route('login') }}"
                    novalidate
                    autocomplete="off"
                >
                    @csrf

                    <div class="mb-2">
                        <label for="email" class="form-label">
                            Email address <span class="required">*</span>
                        </label>

                        <div class="field-wrapper">
                            <i class="fa-solid fa-envelope field-icon"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="name@example.com"
                                autocomplete="off"
                                inputmode="email"
                                required
                                autofocus
                            >
                        </div>

                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="password" class="form-label">
                            Password <span class="required">*</span>
                        </label>

                        <div class="field-wrapper">
                            <i class="fa-solid fa-lock field-icon"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password"
                                autocomplete="off"
                                required
                                spellcheck="false"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="password-toggle"
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>

                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                class="form-check-input"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >

                            <label for="remember" class="form-check-label">
                                Keep me signed in
                            </label>
                        </div>

                        @if(Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="forgot-link"
                            >
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" id="loginButton" class="btn btn-login">
                        <span class="button-default">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>
                            Sign in securely
                        </span>

                        <span class="button-loading d-none">
                            <span
                                class="spinner-border spinner-border-sm me-2"
                                aria-hidden="true"
                            ></span>
                            Signing in...
                        </span>
                    </button>
                </form>

                @if(Route::has('register'))
                    <div class="divider">New customer</div>

                    <div class="register-card">
                        <p>Do not have a customer account yet?</p>

                        <a href="{{ route('register') }}">
                            <i class="fa-solid fa-user-plus"></i>
                            Create customer account
                        </a>
                    </div>
                @endif

                <div class="security-note">
                    <i class="fa-solid fa-lock me-1"></i>
                    Secure and confidential access
                </div>
            </section>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Video Status
            const video = document.getElementById('loginVideo');
            const statusText = document.getElementById('videoStatusText');
            const statusPulse = document.getElementById('statusPulse');

            if (video && statusText) {
                if (!video.querySelector('source')) {
                    statusText.textContent = 'Background';
                    if (statusPulse) statusPulse.style.background = '#94a3b8';
                }
                video.addEventListener('play', () => {
                    statusText.textContent = 'Playing';
                    statusPulse.style.background = '#22c55e';
                });
                video.addEventListener('pause', () => {
                    statusText.textContent = 'Paused';
                    statusPulse.style.background = '#f59e0b';
                });
                video.addEventListener('error', () => {
                    statusText.textContent = 'Unavailable';
                    statusPulse.style.background = '#ef4444';
                });
            }

            // Login Scripts
            const form = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', () => {
                    const passwordIsVisible = passwordInput.type === 'text';

                    passwordInput.type = passwordIsVisible ? 'password' : 'text';

                    togglePassword.setAttribute(
                        'aria-label',
                        passwordIsVisible ? 'Show password' : 'Hide password'
                    );

                    togglePassword.setAttribute(
                        'aria-pressed',
                        String(!passwordIsVisible)
                    );

                    const icon = togglePassword.querySelector('i');

                    if (icon) {
                        icon.className = passwordIsVisible
                            ? 'fa-solid fa-eye'
                            : 'fa-solid fa-eye-slash';
                    }
                });
            }

            // Clear sensitive values when returning to this page through browser history.
            // Browser password managers can still choose to autofill afterwards.
            window.addEventListener('pageshow', (event) => {
                if (event.persisted && passwordInput) {
                    passwordInput.value = '';
                    passwordInput.type = 'password';
                    if (togglePassword) {
                        togglePassword.setAttribute('aria-label', 'Show password');
                        togglePassword.setAttribute('aria-pressed', 'false');
                        const icon = togglePassword.querySelector('i');
                        if (icon) icon.className = 'fa-solid fa-eye';
                    }
                    if (loginButton) {
                        loginButton.disabled = false;
                        loginButton.querySelector('.button-default')?.classList.remove('d-none');
                        loginButton.querySelector('.button-loading')?.classList.add('d-none');
                    }
                }
            });

            form.addEventListener('submit', (event) => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    form.classList.add('was-validated');

                    const firstInvalid = form.querySelector(':invalid');

                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }

                    return;
                }

                loginButton.disabled = true;
                loginButton.querySelector('.button-default').classList.add('d-none');
                loginButton.querySelector('.button-loading').classList.remove('d-none');
            });
        });
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
</body>
</html>
