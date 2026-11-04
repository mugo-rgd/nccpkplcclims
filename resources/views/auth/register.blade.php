<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Registration | KPLC Meter Laboratory System</title>

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
            --page-background: #eef3f9;
            --surface: #ffffff;
            --surface-soft: #f7f9fc;
            --border: #d9e2ef;
            --text: #1f2937;
            --text-muted: #64748b;
            --danger: #dc3545;
            --success: #198754;
            --shadow: 0 24px 65px rgba(15, 23, 42, 0.16);
            --radius-lg: 24px;
            --radius-md: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
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
            font-size: 0.65rem;
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

        /* Registration */
        a {
            color: var(--kplc-blue);
        }

        .registration-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 16px;
        }

        .registration-shell {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(240px, 0.7fr) minmax(0, 1.7fr);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 92vh;
        }

        /* Side Panel - Compact */
        .side-panel {
            position: relative;
            overflow: hidden;
            padding: 30px 28px;
            background:
                linear-gradient(160deg, rgba(13, 40, 84, 0.98), rgba(22, 59, 117, 0.96)),
                var(--kplc-blue);
            color: #fff;
            display: flex;
            flex-direction: column;
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
        .side-content,
        .trust-note {
            position: relative;
            z-index: 1;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.72);
        }

        .side-content h2 {
            margin-bottom: 8px;
            font-size: clamp(1.2rem, 2vw, 1.6rem);
            font-weight: 800;
            line-height: 1.15;
        }

        .side-content > p {
            margin-bottom: 16px;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.5;
            font-size: 0.85rem;
        }

        .benefit-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .benefit-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.82rem;
        }

        .benefit-list i {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(247, 198, 0, 0.18);
            color: var(--kplc-yellow);
            font-size: 0.7rem;
        }

        .trust-note {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.7rem;
        }

        /* Form Panel - Compact */
        .form-panel {
            padding: 28px 32px 24px;
            background: transparent;
            overflow-y: auto;
            max-height: 92vh;
        }

        .form-panel::-webkit-scrollbar {
            width: 4px;
        }

        .form-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .form-panel::-webkit-scrollbar-thumb {
            background: var(--kplc-blue-light);
            border-radius: 4px;
        }

        .form-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-heading h2 {
            margin: 0 0 4px;
            color: var(--kplc-blue-dark);
            font-size: clamp(1.2rem, 1.8vw, 1.5rem);
            font-weight: 800;
        }

        .form-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .login-link {
            white-space: nowrap;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .login-link a {
            font-weight: 700;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Step Indicator - Compact */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 16px;
            padding: 0 10px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .step-number {
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid #d9e2ef;
            color: #94a3b8;
            font-weight: 800;
            font-size: 0.65rem;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .step-item.active .step-number {
            background: var(--kplc-blue);
            border-color: var(--kplc-blue);
            color: #fff;
        }

        .step-item.completed .step-number {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .step-item .step-label {
            display: none;
        }

        .step-item.active .step-label,
        .step-item.completed .step-label {
            display: inline;
        }

        .step-connector {
            width: 28px;
            height: 2px;
            background: #d9e2ef;
            margin: 0 2px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .step-connector.completed {
            background: var(--success);
        }

        /* Form Steps */
        .form-step {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section {
            margin-bottom: 14px;
            padding: 16px 18px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .section-number {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 28px;
            border-radius: 50%;
            background: var(--kplc-blue);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .section-heading h3 {
            margin: 0;
            color: var(--kplc-blue-dark);
            font-size: 0.85rem;
            font-weight: 800;
        }

        .section-heading p {
            margin: 1px 0 0;
            color: var(--text-muted);
            font-size: 0.7rem;
        }

        .form-label {
            margin-bottom: 4px;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .required {
            color: var(--danger);
        }

        .optional {
            color: var(--text-muted);
            font-weight: 500;
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

        .textarea-icon {
            top: 14px;
            transform: none;
        }

        .form-control {
            min-height: 38px;
            padding: 8px 36px 8px 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text);
            font-size: 0.82rem;
        }

        textarea.form-control {
            min-height: 60px;
            resize: vertical;
        }

        .form-control::placeholder {
            color: #a0aec0;
            font-size: 0.78rem;
        }

        .form-control:focus {
            border-color: var(--kplc-blue-light);
            box-shadow: 0 0 0 0.18rem rgba(45, 101, 173, 0.13);
            background: #ffffff;
        }

        .form-control.is-invalid {
            padding-right: 36px;
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
            background: #edf2f7;
            color: var(--kplc-blue);
        }

        .form-text,
        .invalid-feedback,
        .validation-message {
            margin-top: 4px;
            font-size: 0.7rem;
        }

        .validation-message {
            display: none;
        }

        .validation-message.valid {
            display: block;
            color: var(--success);
        }

        .validation-message.invalid {
            display: block;
            color: var(--danger);
        }

        .password-meter {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
            margin-top: 6px;
        }

        .password-meter span {
            height: 3px;
            border-radius: 99px;
            background: #dbe3ee;
        }

        .password-meter[data-strength="1"] span:nth-child(-n + 1),
        .password-meter[data-strength="2"] span:nth-child(-n + 2),
        .password-meter[data-strength="3"] span:nth-child(-n + 3),
        .password-meter[data-strength="4"] span:nth-child(-n + 4) {
            background: var(--kplc-blue-light);
        }

        .terms-box {
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            margin-bottom: 14px;
        }

        .terms-box .form-check-label {
            font-size: 0.78rem;
        }

        .form-check-input:checked {
            background-color: var(--kplc-blue);
            border-color: var(--kplc-blue);
        }

        .form-check-input {
            width: 14px;
            height: 14px;
            margin-top: 2px;
        }

        .form-check-label {
            font-size: 0.78rem;
        }

        /* Step Navigation Buttons - Compact */
        .step-buttons {
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }

        .step-buttons .btn-prev {
            min-height: 40px;
            padding: 0 22px;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            background: transparent;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .step-buttons .btn-prev:hover {
            border-color: var(--kplc-blue);
            color: var(--kplc-blue);
            background: rgba(45, 101, 173, 0.05);
        }

        .step-buttons .btn-next,
        .step-buttons .btn-submit {
            flex: 1;
            min-height: 40px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--kplc-blue-dark), var(--kplc-blue-light));
            color: #fff;
            font-weight: 800;
            font-size: 0.82rem;
            box-shadow: 0 6px 18px rgba(22, 59, 117, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .step-buttons .btn-next:hover,
        .step-buttons .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(22, 59, 117, 0.3);
        }

        .step-buttons .btn-next:disabled,
        .step-buttons .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .step-buttons .btn-submit .spinner-border {
            width: 0.9rem;
            height: 0.9rem;
        }

        .mobile-login {
            display: none;
            margin-top: 14px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Alert */
        .alert {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 16px;
        }

        /* Row gap - compact */
        .row.g-3 {
            --bs-gutter-y: 0.6rem;
            --bs-gutter-x: 0.6rem;
        }

        .row.g-3 > * {
            padding-right: calc(var(--bs-gutter-x) * 0.5);
            padding-left: calc(var(--bs-gutter-x) * 0.5);
            margin-top: var(--bs-gutter-y);
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .registration-page {
                padding: 16px 12px;
                align-items: flex-start;
            }

            .registration-shell {
                max-width: 700px;
                grid-template-columns: 1fr;
                max-height: none;
            }

            .side-panel {
                padding: 24px;
            }

            .brand {
                margin-bottom: 18px;
            }

            .side-content h2 {
                font-size: 1.3rem;
            }

            .benefit-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .trust-note {
                margin-top: 20px;
            }

            .form-panel {
                padding: 24px 24px 20px;
                max-height: none;
            }

            .step-connector {
                width: 20px;
            }
        }

        @media (max-width: 767.98px) {
            .side-panel {
                display: none;
            }

            .form-panel {
                padding: 20px 16px 16px;
                max-height: none;
            }

            .form-heading {
                margin-bottom: 14px;
                flex-direction: column;
            }

            .form-heading .login-link {
                display: none;
            }

            .form-section {
                padding: 14px;
            }

            .mobile-login {
                display: block;
            }

            .step-item .step-label {
                display: none !important;
            }

            .step-connector {
                width: 14px;
            }

            .step-buttons {
                flex-wrap: wrap;
            }

            .step-buttons .btn-prev {
                width: 100%;
                order: 2;
            }

            .step-buttons .btn-next,
            .step-buttons .btn-submit {
                width: 100%;
                order: 1;
            }

            .registration-shell {
                max-height: none;
                border-radius: 16px;
            }
        }

        @media (max-width: 420px) {
            .registration-page {
                padding: 0;
            }

            .registration-shell {
                min-height: 100vh;
                border-radius: 0;
                box-shadow: none;
                background: rgba(255, 255, 255, 0.98);
            }

            .form-panel {
                padding: 16px 12px 14px;
            }

            .form-section {
                padding: 12px 10px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
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
            $videoUrl = $videoExists ? asset('videos/music-motional-lab.mp4') : 'https://www.w3schools.com/html/mov_bbb.mp4';
        @endphp

        <video
            id="registrationVideo"
            autoplay
            muted
            loop
            playsinline
            preload="auto"
        >
            @if($videoExists)
                <source src="{{ $videoUrl }}" type="video/mp4">
            @endif
            <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
            <source src="https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_1mb.mp4" type="video/mp4">
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

    <!-- Registration -->
    <main class="registration-page">
        <div class="registration-shell">

            <aside class="side-panel" aria-label="Registration benefits">
                <div class="brand">
                    <div class="brand-icon" aria-hidden="true">
                        <i class="fa-solid fa-bolt"></i>
                    </div>

                    <div>
                        <h1>KPLC Meter Laboratory</h1>
                        <p>Customer Service Portal</p>
                    </div>
                </div>

                <div class="side-content">
                    <h2>Manage your calibration services online.</h2>

                    <p>
                        Create a customer account to submit requests, monitor progress,
                        access laboratory documents and communicate with the Meter
                        Calibration Laboratory.
                    </p>

                    <ul class="benefit-list">
                        <li>
                            <i class="fa-solid fa-file-circle-plus"></i>
                            <span>Submit calibration requests</span>
                        </li>

                        <li>
                            <i class="fa-solid fa-list-check"></i>
                            <span>Track requests from start to finish</span>
                        </li>

                        <li>
                            <i class="fa-solid fa-certificate"></i>
                            <span>Download certificates and reports</span>
                        </li>

                        <li>
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span>View quotations and invoices</span>
                        </li>
                    </ul>
                </div>

                <div class="trust-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Your account information is securely protected.</span>
                </div>
            </aside>

            <section class="form-panel">
                <header class="form-heading">
                    <div>
                        <h2>Create customer account</h2>
                        <p>Complete the details below to access laboratory services.</p>
                    </div>

                    <div class="login-link">
                        Already registered?
                        <a href="{{ route('login') }}">Sign in</a>
                    </div>
                </header>

                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step-item active" id="step1Indicator">
                        <span class="step-number">1</span>
                        <span class="step-label">Account</span>
                    </div>
                    <span class="step-connector" id="connector1"></span>
                    <div class="step-item" id="step2Indicator">
                        <span class="step-number">2</span>
                        <span class="step-label">Company</span>
                    </div>
                </div>

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
                                <strong>Please correct the following:</strong>

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
                    id="registrationForm"
                    method="POST"
                    action="{{ route('register') }}"
                    enctype="multipart/form-data"
                    novalidate
                >
                    @csrf

                    <!-- STEP 1: ACCOUNT INFORMATION -->
                    <div class="form-step active" id="step1">
                        <div class="form-section">
                            <div class="section-heading">
                                <span class="section-number">1</span>

                                <div>
                                    <h3>Account information</h3>
                                    <p>Details you will use to sign in.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">
                                        Full name <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-user field-icon"></i>

                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}"
                                            placeholder="e.g. Paul Mugo"
                                            autocomplete="name"
                                            required
                                            autofocus
                                        >
                                    </div>

                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        Email <span class="required">*</span>
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
                                            autocomplete="email"
                                            inputmode="email"
                                            required
                                        >
                                    </div>

                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone_number" class="form-label">
                                        Phone <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-phone field-icon"></i>

                                        <input
                                            type="tel"
                                            id="phone_number"
                                            name="phone_number"
                                            class="form-control @error('phone_number') is-invalid @enderror"
                                            value="{{ old('phone_number') }}"
                                            placeholder="+254 712 345 678"
                                            autocomplete="tel"
                                            inputmode="tel"
                                            required
                                        >
                                    </div>

                                    @error('phone_number')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
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
                                            placeholder="Create a strong password"
                                            autocomplete="new-password"
                                            minlength="8"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="password-toggle"
                                            data-password-target="password"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>

                                    <div
                                        id="passwordMeter"
                                        class="password-meter"
                                        data-strength="0"
                                        aria-hidden="true"
                                    >
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>

                                    <div id="passwordHelp" class="form-text">
                                        Use at least 8 characters with letters and numbers.
                                    </div>

                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">
                                        Confirm password <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-lock field-icon"></i>

                                        <input
                                            type="password"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            class="form-control"
                                            placeholder="Re-enter your password"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="password-toggle"
                                            data-password-target="password_confirmation"
                                            aria-label="Show password confirmation"
                                            aria-pressed="false"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>

                                    <div
                                        id="passwordMatchMessage"
                                        class="validation-message"
                                        aria-live="polite"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1 Navigation -->
                        <div class="step-buttons">
                            <button type="button" class="btn-next" id="goToStep2">
                                Continue <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: COMPANY INFORMATION -->
                    <div class="form-step" id="step2">
                        <div class="form-section">
                            <div class="section-heading">
                                <span class="section-number">2</span>

                                <div>
                                    <h3>Company information</h3>
                                    <p>Organization details for service records.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label">
                                        Company name <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-building field-icon"></i>

                                        <input
                                            type="text"
                                            id="company_name"
                                            name="company_name"
                                            class="form-control @error('company_name') is-invalid @enderror"
                                            value="{{ old('company_name') }}"
                                            placeholder="Registered company name"
                                            autocomplete="organization"
                                            required
                                        >
                                    </div>

                                    @error('company_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_person" class="form-label">
                                        Contact person <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-user-tie field-icon"></i>

                                        <input
                                            type="text"
                                            id="contact_person"
                                            name="contact_person"
                                            class="form-control @error('contact_person') is-invalid @enderror"
                                            value="{{ old('contact_person') }}"
                                            placeholder="Primary contact name"
                                            required
                                        >
                                    </div>

                                    @error('contact_person')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="company_phone" class="form-label">
                                        Company phone <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-phone-volume field-icon"></i>

                                        <input
                                            type="tel"
                                            id="company_phone"
                                            name="company_phone"
                                            class="form-control @error('company_phone') is-invalid @enderror"
                                            value="{{ old('company_phone') }}"
                                            placeholder="+254 20 123 4567"
                                            autocomplete="organization-tel"
                                            inputmode="tel"
                                            required
                                        >
                                    </div>

                                    @error('company_phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="company_email" class="form-label">
                                        Company email <span class="optional">(optional)</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-at field-icon"></i>

                                        <input
                                            type="email"
                                            id="company_email"
                                            name="company_email"
                                            class="form-control @error('company_email') is-invalid @enderror"
                                            value="{{ old('company_email') }}"
                                            placeholder="accounts@company.com"
                                            autocomplete="organization-email"
                                            inputmode="email"
                                        >
                                    </div>

                                    @error('company_email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="tax_id" class="form-label">
                                        KRA PIN <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-file-invoice field-icon"></i>

                                        <input
                                            type="text"
                                            id="tax_id"
                                            name="tax_id"
                                            class="form-control @error('tax_id') is-invalid @enderror"
                                            value="{{ old('tax_id') }}"
                                            placeholder="e.g. A123456789B"
                                            maxlength="20"
                                            required
                                        >
                                    </div>

                                    @error('tax_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">
                                        Physical address <span class="required">*</span>
                                    </label>

                                    <div class="field-wrapper">
                                        <i class="fa-solid fa-location-dot field-icon textarea-icon"></i>

                                        <textarea
                                            id="address"
                                            name="address"
                                            class="form-control @error('address') is-invalid @enderror"
                                            placeholder="Building, street, town/city and county"
                                            autocomplete="street-address"
                                            required
                                        >{{ old('address') }}</textarea>
                                    </div>

                                    @error('address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <!-- TAX DOCUMENTS AND VAT EXEMPTION -->
                        <div class="form-section" id="taxDocumentsSection">
                            <div class="section-heading">
                                <span class="section-number"><i class="fa-solid fa-file-invoice"></i></span>
                                <div>
                                    <h3>Tax information and supporting documents</h3>
                                    <p>Provide your KRA certificate and request VAT exemption where applicable.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="kra_pin_certificate" class="form-label">
                                        KRA PIN certificate <span class="required">*</span>
                                    </label>
                                    <input type="file" id="kra_pin_certificate" name="kra_pin_certificate"
                                        class="form-control @error('kra_pin_certificate') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="form-text">PDF, JPG or PNG; maximum 5 MB. Documents are submitted for verification.</div>
                                    @error('kra_pin_certificate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label for="vat_exemption_requested" class="form-label">
                                        Are you requesting VAT exemption? <span class="required">*</span>
                                    </label>
                                    <select id="vat_exemption_requested" name="vat_exemption_requested"
                                        class="form-select @error('vat_exemption_requested') is-invalid @enderror" required>
                                        <option value="0" @selected(old('vat_exemption_requested', '0') == '0')>No — standard VAT applies</option>
                                        <option value="1" @selected(old('vat_exemption_requested') == '1')>Yes — submit exemption documents for review</option>
                                    </select>
                                    <div class="form-text">Exemption is not effective until verified and approved by the Lab Manager.</div>
                                    @error('vat_exemption_requested')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div id="vatExemptionFields" class="mt-3" hidden>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="vat_exemption_certificate_number" class="form-label">
                                            VAT exemption certificate number <span class="required">*</span>
                                        </label>
                                        <input type="text" id="vat_exemption_certificate_number"
                                            name="vat_exemption_certificate_number"
                                            class="form-control @error('vat_exemption_certificate_number') is-invalid @enderror"
                                            value="{{ old('vat_exemption_certificate_number') }}" maxlength="255">
                                        @error('vat_exemption_certificate_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="vat_exemption_expiry_date" class="form-label">
                                            Certificate expiry date <span class="required">*</span>
                                        </label>
                                        <input type="date" id="vat_exemption_expiry_date"
                                            name="vat_exemption_expiry_date"
                                            class="form-control @error('vat_exemption_expiry_date') is-invalid @enderror"
                                            value="{{ old('vat_exemption_expiry_date') }}"
                                            min="{{ now()->toDateString() }}">
                                        @error('vat_exemption_expiry_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="vat_exemption_certificate" class="form-label">
                                            VAT exemption certificate <span class="required">*</span>
                                        </label>
                                        <input type="file" id="vat_exemption_certificate"
                                            name="vat_exemption_certificate"
                                            class="form-control @error('vat_exemption_certificate') is-invalid @enderror"
                                            accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="form-text">PDF, JPG or PNG; maximum 5 MB.</div>
                                        @error('vat_exemption_certificate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="terms-box">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="terms"
                                    required
                                >

                                <label class="form-check-label" for="terms">
                                    I confirm that the information provided is accurate and
                                    I agree to use the portal responsibly.
                                </label>
                            </div>
                        </div>

                        <!-- Step 2 Navigation -->
                        <div class="step-buttons">
                            <button type="button" class="btn-prev" id="goToStep1">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back
                            </button>

                            <button type="submit" id="registerButton" class="btn-submit">
                                <span class="button-default">
                                    <i class="fa-solid fa-user-plus me-2"></i>
                                    Create account
                                </span>

                                <span class="button-loading d-none">
                                    <span
                                        class="spinner-border spinner-border-sm me-2"
                                        aria-hidden="true"
                                    ></span>
                                    Creating...
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="mobile-login">
                        Already have an account?
                        <a href="{{ route('login') }}" class="fw-bold">Sign in</a>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Video Status
            const video = document.getElementById('registrationVideo');
            const statusText = document.getElementById('videoStatusText');
            const statusPulse = document.getElementById('statusPulse');

            if (video && statusText) {
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

            // Step Navigation
            let currentStep = {{ $errors->hasAny(['company_name', 'contact_person', 'company_phone', 'company_email', 'tax_id', 'address', 'kra_pin_certificate', 'vat_exemption_requested', 'vat_exemption_certificate_number', 'vat_exemption_expiry_date', 'vat_exemption_certificate']) ? 2 : 1 }};
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const step1Indicator = document.getElementById('step1Indicator');
            const step2Indicator = document.getElementById('step2Indicator');
            const connector1 = document.getElementById('connector1');

            function goToStep(step) {
                step1.classList.remove('active');
                step2.classList.remove('active');

                if (step === 1) {
                    step1.classList.add('active');
                    currentStep = 1;
                } else {
                    step2.classList.add('active');
                    currentStep = 2;
                }

                step1Indicator.classList.remove('active', 'completed');
                step2Indicator.classList.remove('active', 'completed');
                connector1.classList.remove('completed');

                if (currentStep === 1) {
                    step1Indicator.classList.add('active');
                } else if (currentStep === 2) {
                    step1Indicator.classList.add('completed');
                    connector1.classList.add('completed');
                    step2Indicator.classList.add('active');
                }

                document.querySelector('.form-panel').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            goToStep(currentStep);

            // Step 1 -> Step 2
            document.getElementById('goToStep2').addEventListener('click', () => {
                const step1Inputs = step1.querySelectorAll('input[required]');
                let isValid = true;

                step1Inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                const password = document.getElementById('password');
                const passwordConfirm = document.getElementById('password_confirmation');
                if (password.value !== passwordConfirm.value && passwordConfirm.value) {
                    passwordConfirm.classList.add('is-invalid');
                    const msg = document.getElementById('passwordMatchMessage');
                    msg.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> Passwords do not match.';
                    msg.className = 'validation-message invalid';
                    isValid = false;
                }

                if (isValid) {
                    goToStep(2);
                } else {
                    const firstInvalid = step1.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                }
            });

            document.getElementById('goToStep1').addEventListener('click', () => {
                goToStep(1);
            });

            // Password Toggle
            document.querySelectorAll('[data-password-target]').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.dataset.passwordTarget);
                    if (!target) return;

                    const showing = target.type === 'text';
                    target.type = showing ? 'password' : 'text';

                    button.setAttribute('aria-pressed', String(!showing));
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.className = showing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
                    }
                });
            });

            // Password Strength
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            const passwordMeter = document.getElementById('passwordMeter');
            const passwordHelp = document.getElementById('passwordHelp');
            const passwordMatchMessage = document.getElementById('passwordMatchMessage');
            const taxIdInput = document.getElementById('tax_id');

            function calculatePasswordStrength(value) {
                let score = 0;
                if (value.length >= 8) score++;
                if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
                if (/\d/.test(value)) score++;
                if (/[^A-Za-z0-9]/.test(value)) score++;
                return score;
            }

            function updatePasswordStrength() {
                const value = password.value;
                const strength = calculatePasswordStrength(value);
                passwordMeter.dataset.strength = String(strength);
                const labels = [
                    'Use at least 8 characters with letters and numbers.',
                    'Password strength: weak.',
                    'Password strength: fair.',
                    'Password strength: good.',
                    'Password strength: strong.'
                ];
                passwordHelp.textContent = value ? labels[strength] : labels[0];
            }

            function updatePasswordMatch() {
                const confirmation = passwordConfirmation.value;
                if (!confirmation) {
                    passwordMatchMessage.textContent = '';
                    passwordMatchMessage.className = 'validation-message';
                    passwordConfirmation.setCustomValidity('');
                    return;
                }

                if (password.value === confirmation) {
                    passwordMatchMessage.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Passwords match.';
                    passwordMatchMessage.className = 'validation-message valid';
                    passwordConfirmation.setCustomValidity('');
                } else {
                    passwordMatchMessage.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> Passwords do not match.';
                    passwordMatchMessage.className = 'validation-message invalid';
                    passwordConfirmation.setCustomValidity('Passwords do not match.');
                }
            }

            password.addEventListener('input', () => {
                updatePasswordStrength();
                updatePasswordMatch();
            });

            passwordConfirmation.addEventListener('input', updatePasswordMatch);

            if (taxIdInput) {
                taxIdInput.addEventListener('input', () => {
                    taxIdInput.value = taxIdInput.value.toUpperCase().replace(/\s+/g, '');
                });
            }

            // VAT exemption: show and require certificate fields only when requested.
            const vatRequestSelect = document.getElementById('vat_exemption_requested');
            const vatFields = document.getElementById('vatExemptionFields');
            const vatDependentInputs = [
                document.getElementById('vat_exemption_certificate_number'),
                document.getElementById('vat_exemption_expiry_date'),
                document.getElementById('vat_exemption_certificate')
            ];

            function updateVatFields() {
                const requested = vatRequestSelect.value === '1';
                vatFields.hidden = !requested;
                vatDependentInputs.forEach(input => {
                    input.required = requested;
                    input.disabled = !requested;
                    if (!requested) input.classList.remove('is-invalid');
                });
            }

            vatRequestSelect.addEventListener('change', updateVatFields);
            updateVatFields();

            // Form Submission
            const form = document.getElementById('registrationForm');
            const submitButton = document.getElementById('registerButton');

            form.addEventListener('submit', (event) => {
                const allInputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;

                allInputs.forEach(input => {
                    if (input.disabled || input.id === 'terms') return;
                    if (!input.checkValidity()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                const terms = document.getElementById('terms');
                if (!terms.checked) {
                    terms.classList.add('is-invalid');
                    isValid = false;
                } else {
                    terms.classList.remove('is-invalid');
                }

                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.classList.add('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    event.preventDefault();
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        if (firstInvalid.closest('#step1')) {
                            goToStep(1);
                        } else {
                            goToStep(2);
                        }
                        setTimeout(() => {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                    }
                    return;
                }

                submitButton.disabled = true;
                submitButton.querySelector('.button-default').classList.add('d-none');
                submitButton.querySelector('.button-loading').classList.remove('d-none');
            });

            // Clear invalid on input
            document.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('input', () => {
                    input.classList.remove('is-invalid');
                });
            });

            document.getElementById('terms').addEventListener('change', function() {
                this.classList.remove('is-invalid');
            });
        });
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
</body>
</html>
