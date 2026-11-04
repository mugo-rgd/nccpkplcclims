<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta
        name="description"
        content="KPLC Meter Central Laboratory - energy meter calibration, testing, traceability and certification services."
    >
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>KPLC Meter Central Laboratory</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

    <style>
        :root {
            --kplc-blue: #0057b8;
            --kplc-blue-light: #0d73e8;
            --kplc-blue-dark: #06366f;
            --kplc-blue-deep: #031d40;
            --kplc-yellow: #ffd200;
            --kplc-yellow-dark: #e5b900;
            --kplc-sky: #eef6ff;
            --kplc-text: #17233a;
            --kplc-muted: #64748b;
            --kplc-border: #d8e3f0;
            --kplc-white: #ffffff;
            --kplc-green: #159447;
            --shadow-soft: 0 18px 45px rgba(3, 42, 92, 0.12);
            --shadow-strong: 0 25px 70px rgba(3, 28, 64, 0.24);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--kplc-text);
            background: #ffffff;
        }

        body.menu-open, body.modal-open { overflow: hidden; }

        img { display: block; width: 100%; }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }

        .page-shell { overflow: hidden; }

        .container {
            width: min(1420px, calc(100% - 32px));
            margin: 0 auto;
        }

        /* HEADER */
        .site-header {
            position: fixed;
            inset: 0 0 auto;
            z-index: 1000;
            background: rgba(0, 79, 171, 0.97);
            border-bottom: 3px solid var(--kplc-yellow);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.16);
            backdrop-filter: blur(10px);
        }

        .navbar {
            min-height: 82px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 1 auto;
            min-width: 0;
            color: #fff;
            font-weight: 900;
            white-space: nowrap;
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 58px;
            overflow: hidden;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 0 5px rgba(255,255,255,0.12), 0 0 22px rgba(255,255,255,0.6);
        }

        .brand-mark img { width: 50px; height: 50px; object-fit: contain; }
        .brand-name { display: block; font-size: 1.08rem; letter-spacing: -0.01em; }
        .brand-name span { color: var(--kplc-yellow); }

        .brand-subtitle {
            display: block;
            margin-top: 3px;
            color: rgba(255,255,255,0.74);
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1 1 auto;
            min-width: 0;
        }

        .nav-links a {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 14px 9px;
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 3px;
            width: 0;
            height: 3px;
            border-radius: 999px;
            background: var(--kplc-yellow);
            transform: translateX(-50%);
            transition: width 0.22s ease;
        }

        .nav-links a:hover, .nav-links a.active { color: var(--kplc-yellow); }
        .nav-links a:hover::after, .nav-links a.active::after { width: 66%; }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
            margin-left: auto;
        }

        .btn {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 18px;
            border: 0;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 900;
            white-space: nowrap;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .btn:hover { transform: translateY(-2px); }
        .nav-actions .btn { min-width: 108px; padding: 0 17px; }

        .btn-outline {
            border: 2px solid rgba(255,255,255,0.95);
            color: #fff;
            background: transparent;
        }
        .btn-outline:hover { color: var(--kplc-blue-dark); background: #fff; }

        .btn-yellow {
            color: var(--kplc-blue-dark);
            background: var(--kplc-yellow);
            box-shadow: 0 13px 28px rgba(255,210,0,0.26);
        }
        .nav-actions .btn-yellow {
            min-width: 124px;
            box-shadow: 0 10px 24px rgba(255,210,0,0.38);
        }
        .btn-yellow:hover { background: #ffde3f; }

        .btn-blue {
            color: #fff;
            background: linear-gradient(135deg, #0d73e8, var(--kplc-blue));
            box-shadow: 0 14px 30px rgba(0,87,184,0.3);
        }

        .btn-white {
            color: var(--kplc-blue-dark);
            background: #fff;
            box-shadow: 0 14px 30px rgba(0,0,0,0.14);
        }

        .btn-green {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.65rem 1.2rem;
            border: 2px solid #198754;
            border-radius: 10px;
            background: #198754;
            color: #fff !important;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-green:hover {
            background: #157347;
            border-color: #157347;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(25,135,84,0.25);
        }

        .menu-toggle {
            width: 46px;
            height: 46px;
            display: none;
            align-items: center;
            justify-content: center;
            flex: 0 0 46px;
            border: 0;
            border-radius: 12px;
            color: #fff;
            background: rgba(255,255,255,0.14);
            font-size: 1.25rem;
            cursor: pointer;
        }

        .mobile-menu { display: none; padding: 0 20px 20px; }
        .mobile-menu.open { display: block; }

        .mobile-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 8px;
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.14);
            font-weight: 700;
        }

        /* VIDEO SHOWCASE */
        .video-showcase {
            position: relative;
            padding: 100px 0 40px;
            background: linear-gradient(135deg, #02162f 0%, #0a2a5a 100%);
            overflow: hidden;
            margin-top: 82px;
        }

        .video-showcase::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background:
                radial-gradient(circle at 20% 50%, rgba(255,210,0,0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(0,87,184,0.05) 0%, transparent 50%);
            animation: floatBackground 20s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes floatBackground {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(5%, -5%) rotate(3deg); }
            66% { transform: translate(-5%, 5%) rotate(-3deg); }
        }

        .video-showcase .container { position: relative; z-index: 1; }

        .video-showcase-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .video-column { display: flex; flex-direction: column; }

        .video-column .section-heading {
            text-align: left;
            margin-bottom: 16px;
            animation: fadeInUp 1s ease forwards;
        }

        .video-column .section-heading .section-kicker {
            color: var(--kplc-yellow);
            font-size: 0.65rem;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,210,0,0.1);
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,210,0,0.15);
        }

        .video-column .section-heading h2 {
            color: #fff;
            font-size: clamp(1.4rem, 2.8vw, 2.2rem);
            font-weight: 900;
            margin: 8px 0 4px;
            letter-spacing: -0.02em;
            text-align: left;
        }
        .video-column .section-heading h2 span { color: var(--kplc-yellow); }

        .video-column .section-heading p {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            margin: 4px 0 0;
            line-height: 1.5;
            text-align: left;
        }

        .video-wrapper {
            max-width: 100%;
            margin: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            background: #000;
            position: relative;
        }

        .video-container {
            position: relative;
            width: 100%;
            padding-top: 100%;
            background: #000;
            cursor: pointer;
        }

        .video-container video {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
        }

        .video-controls {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 16px 18px 10px;
            background: linear-gradient(180deg, transparent, rgba(0,0,0,0.85));
        }

        .video-controls-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .video-control-left { display: flex; align-items: center; gap: 10px; }

        .play-btn {
            width: 32px; height: 32px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: #fff;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .play-btn:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }

        .video-info {
            display: flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,0.6); font-size: 0.65rem;
        }

        .video-info .status-dot {
            width: 4px; height: 4px;
            border-radius: 50%; background: #22c55e;
            animation: pulse 2s infinite;
        }
        .video-info .status-dot.paused { background: #f59e0b; animation: none; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }

        .video-control-right { display: flex; align-items: center; gap: 8px; }

        .btn-login-video {
            padding: 5px 14px;
            background: var(--kplc-yellow);
            color: #02162f;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.65rem;
            display: inline-flex; align-items: center; gap: 4px;
            transition: all 0.3s ease;
        }
        .btn-login-video:hover { transform: scale(1.05); box-shadow: 0 4px 16px rgba(255,210,0,0.3); }

        .video-quote-section {
            margin-top: 16px;
            text-align: center;
            animation: fadeInUp 1s ease 0.3s both;
        }

        .btn-quote {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--kplc-yellow), #f0b800);
            color: #02162f;
            border: none;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(255,210,0,0.3);
            position: relative;
            overflow: hidden;
        }
        .btn-quote:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 40px rgba(255,210,0,0.5);
        }
        .btn-quote:active { transform: scale(0.95); }

        .btn-quote .quote-badge {
            background: rgba(2,22,47,0.15);
            padding: 2px 12px;
            border-radius: 999px;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
        }

        .quote-subtext {
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            margin-top: 8px;
        }
        .quote-subtext i { margin-right: 4px; color: var(--kplc-yellow); }

        .ads-column { display: flex; flex-direction: column; }

        .ads-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .ad-card {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            padding: 20px 18px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        .ad-card:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }

        .ad-card .ad-icon {
            width: 48px; height: 48px;
            margin: 0 auto 12px;
            background: rgba(255,210,0,0.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            color: var(--kplc-yellow);
        }

        .ad-card h4 { color: #fff; font-size: 0.9rem; font-weight: 800; margin: 0 0 6px; }
        .ad-card p { color: rgba(255,255,255,0.7); font-size: 0.75rem; line-height: 1.4; margin: 0 0 10px; }

        .ad-card .ad-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 999px;
            background: rgba(255,210,0,0.15);
            color: var(--kplc-yellow);
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .ad-card:nth-child(1) { border-color: rgba(255,210,0,0.2); }
        .ad-card:nth-child(2) { border-color: rgba(0,200,150,0.2); }
        .ad-card:nth-child(2) .ad-icon { background: rgba(0,200,150,0.15); color: #00c896; }
        .ad-card:nth-child(3) { border-color: rgba(100,150,255,0.2); }
        .ad-card:nth-child(3) .ad-icon { background: rgba(100,150,255,0.15); color: #6496ff; }
        .ad-card:nth-child(4) { border-color: rgba(255,100,100,0.2); }
        .ad-card:nth-child(4) .ad-icon { background: rgba(255,100,100,0.15); color: #ff6464; }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
            padding: 14px 16px;
            background: rgba(255,255,255,0.05);
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.06);
        }

        .stat-item { text-align: center; }
        .stat-number { display: block; color: #fff; font-size: 1.1rem; font-weight: 900; }
        .stat-label {
            display: block;
            color: rgba(255,255,255,0.5);
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        .trust-indicators {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
            animation: fadeInUp 1s ease 0.5s both;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.5);
            font-size: 0.65rem;
            padding: 4px 12px;
            background: rgba(255,255,255,0.05);
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .trust-item i { color: var(--kplc-yellow); font-size: 0.7rem; }

        .scroll-indicator { text-align: center; margin-top: 16px; animation: bounce 2s infinite; }
        .scroll-indicator a {
            color: rgba(255,255,255,0.2);
            font-size: 0.6rem;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            transition: color 0.3s ease;
        }
        .scroll-indicator a:hover { color: rgba(255,255,255,0.4); }
        .scroll-indicator i { font-size: 0.8rem; animation: bounceArrow 2s infinite; }

        @keyframes bounceArrow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(5px); }
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* QUOTE MODAL */
        .quote-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 12px;
            background: rgba(0,0,0,0.82);
            backdrop-filter: blur(8px);
        }
        .quote-modal-overlay.active { display: flex !important; }

        .quote-modal {
            width: 100%;
            max-width: 820px;
            max-height: calc(100vh - 24px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.45);
        }

                .quote-modal-header {
            position: relative;
            flex: 0 0 auto;
            padding: 20px 22px 15px; /* Increased top/bottom padding */
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 20px 20px 0 0; /* Keeps the top rounded */
        }

        .quote-modal-header .modal-icon {
            width: 42px;
            height: 42px;
            margin: 0 auto 5px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            color: #02162f;
            background: linear-gradient(135deg, var(--kplc-yellow), #f0b800);
            font-size: 1.1rem;
        }

        .quote-modal-header h2 {
            margin: 0;
            color: var(--kplc-blue-dark);
            font-size: 1.15rem;
            font-weight: 900;
        }
        .quote-modal-header p {
            margin: 3px 0 0;
            color: var(--kplc-muted);
            font-size: 0.74rem;
        }

        .quote-modal-close {
            position: absolute;
            top: 9px; right: 12px;
            width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            border: 0; border-radius: 50%;
            color: #64748b;
            background: transparent;
            cursor: pointer;
        }
        .quote-modal-close:hover { background: #f1f5f9; color: #1f2937; }

        .quote-form { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }

        .quote-modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px 22px 8px;
        }
        .quote-modal-body::-webkit-scrollbar { width: 5px; }
        .quote-modal-body::-webkit-scrollbar-thumb { background: #9dbbda; border-radius: 999px; }

        .quote-modal-body .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px 12px;
        }
        .quote-modal-body .form-grid .full-width { grid-column: 1 / -1; }

        .quote-modal .form-group { margin: 0; }
        .quote-modal .form-group label {
            display: block;
            margin-bottom: 2px;
            color: #334155;
            font-size: 0.7rem;
            font-weight: 800;
        }
        .quote-modal .form-group label .required { color: #dc3545; }

        .quote-modal .form-group input,
        .quote-modal .form-group select,
        .quote-modal .form-group textarea {
            width: 100%;
            min-height: 34px;
            padding: 5px 9px;
            border: 1.5px solid #dce4ee;
            border-radius: 7px;
            color: #1f2937;
            background: #f8fafc;
            font-size: 0.76rem;
            outline: none;
        }
        .quote-modal .form-group input:focus,
        .quote-modal .form-group select:focus,
        .quote-modal .form-group textarea:focus {
            border-color: var(--kplc-blue);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,87,184,0.08);
        }

        /*         /* MULTI-ITEM ROW */
        .quote-items { display: flex; flex-direction: column; gap: 12px; }

        .quote-item {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            align-items: start;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            position: relative;
        }

        /* Form group inside the item */
        .quote-item .item-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .quote-item .item-group label {
            font-size: 0.68rem;
            font-weight: 700;
            color: #475569;
            margin-left: 2px;
        }

        .quote-item select,
        .quote-item input[type="text"],
        .quote-item input[type="number"] {
            min-height: 38px;
            padding: 6px 10px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.78rem;
            background: #fff;
            width: 100%;
            min-width: 0;
            transition: border-color 0.2s;
        }

        .quote-item select:focus,
        .quote-item input:focus {
            border-color: var(--kplc-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,87,184,0.1);
        }

        .quote-item .remove-item {
            width: 38px; height: 38px;
            border: 0; border-radius: 6px;
            background: #fee2e2;
            color: #b91c1c;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .quote-item .remove-item:hover { background: #fecaca; }

        /* Adjust grid layout for the remove button */
        .quote-item .item-remove-wrapper {
            display: flex;
            align-items: flex-end;
            height: 100%;
            justify-content: flex-end;
        }

        .btn-add-item {
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border: 1.5px dashed #0057b8;
            border-radius: 8px;
            background: #eef6ff;
            color: #0057b8;
            font-size: 0.75rem;
            font-weight: 800;
            cursor: pointer;
        }
        .btn-add-item:hover { background: #dbeafe; }

        /* AI ASSISTANT */
        .ai-assistant {
            grid-column: 1 / -1;
            margin-top: 2px;
            padding: 7px 10px;
            border: 1px solid #dce4ee;
            border-radius: 9px;
            background: #f8fafc;
        }
        .ai-assistant-header { display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
        .ai-assistant-header .ai-icon {
            width: 22px; height: 22px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            color: #fff;
            background: linear-gradient(135deg, var(--kplc-blue), var(--kplc-blue-dark));
            font-size: 0.55rem;
        }
        .ai-assistant-header h4 { margin: 0; color: var(--kplc-blue-dark); font-size: 0.7rem; font-weight: 900; }
        .ai-assistant-header .ai-badge {
            padding: 1px 6px;
            border-radius: 999px;
            background: var(--kplc-yellow);
            color: #02162f;
            font-size: 0.43rem;
            font-weight: 900;
        }

        .ai-messages { max-height: 90px; overflow-y: auto; margin-bottom: 4px; }
        .ai-message { display: flex; gap: 5px; margin-bottom: 3px; }
        .ai-message .avatar {
            width: 20px; height: 20px;
            flex: 0 0 20px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            font-size: 0.42rem;
            font-weight: 800;
        }
        .ai-message .avatar.ai { color: #fff; background: var(--kplc-blue); }
        .ai-message .avatar.user { color: #1f2937; background: #e2e8f0; }
        .ai-message .bubble {
            max-width: 90%;
            padding: 4px 8px;
            border-radius: 7px;
            background: #fff;
            color: #1f2937;
            font-size: 0.65rem;
            line-height: 1.3;
        }
        .ai-message.user { justify-content: flex-end; }
        .ai-message.user .bubble { color: #fff; background: var(--kplc-blue); }

        .ai-input-group { display: flex; gap: 5px; }
        .ai-input-group input {
            flex: 1;
            min-width: 0;
            min-height: 30px;
            padding: 4px 8px;
            border: 1.5px solid #dce4ee;
            border-radius: 6px;
            background: #fff;
            font-size: 0.66rem;
        }
        .ai-input-group button {
            width: 38px;
            flex: 0 0 38px;
            border: 0;
            border-radius: 6px;
            color: #fff;
            background: var(--kplc-blue);
            cursor: pointer;
        }

        .quote-modal-footer {
            flex: 0 0 auto;
            padding: 8px 22px 9px;
            border-top: 1px solid #dce4ee;
            background: #fff;
            box-shadow: 0 -5px 14px rgba(15,23,42,0.05);
        }

        .btn-submit-quote {
            width: 100%;
            min-height: 46px;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 18px;
            border: 0;
            border-radius: 10px;
            color: #fff !important;
            background: linear-gradient(135deg, var(--kplc-blue-dark), var(--kplc-blue-light));
            font-size: 0.92rem;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(0,87,184,0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .btn-submit-quote i, .btn-submit-quote span { color: #fff !important; }
        .btn-submit-quote:hover {
            background: linear-gradient(135deg, var(--kplc-blue), var(--kplc-blue-light));
            transform: translateY(-2px);
            box-shadow: 0 9px 24px rgba(0,87,184,0.38);
        }
        .btn-submit-quote:active { transform: translateY(0); }
        .btn-submit-quote:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

        .quote-modal-footer .secure-text {
            margin-top: 5px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.62rem;
        }

        /* HERO */
        .hero {
            position: relative;
            min-height: 600px;
            display: flex;
            align-items: center;
            padding: 80px 0 60px;
            color: #fff;
            background:
                linear-gradient(90deg, rgba(2,25,60,0.94) 0%, rgba(3,47,101,0.82) 48%, rgba(1,30,69,0.58) 100%),
                url("https://www.sdes.co.in/images/lab/meter-bench-wide.jpg") center center / cover no-repeat;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 77% 31%, rgba(255,210,0,0.2), transparent 30%),
                linear-gradient(180deg, transparent 64%, rgba(2,23,53,0.48));
        }

        .hero-grid {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
            align-items: center;
            gap: 50px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            color: var(--kplc-yellow);
            font-size: 0.85rem;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .hero h1 {
            max-width: 880px;
            margin: 0;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            line-height: 1;
            letter-spacing: -0.045em;
        }
        .hero h1 span { color: var(--kplc-yellow); }

        .hero-summary {
            max-width: 800px;
            margin: 20px 0 24px;
            color: rgba(255,255,255,0.88);
            font-size: clamp(0.95rem, 1.4vw, 1.1rem);
            line-height: 1.6;
        }

        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }

        .hero-trust {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
        }
        .hero-trust span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.78);
            font-size: 0.8rem;
            font-weight: 700;
        }
        .hero-trust i { color: var(--kplc-yellow); }

        .hero-card {
            padding: 24px;
            border: 1px solid rgba(255,255,255,0.24);
            border-radius: 20px;
            background: rgba(4,39,84,0.63);
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(14px);
        }

        .hero-card-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            padding: 5px 10px;
            border-radius: 999px;
            color: var(--kplc-blue-dark);
            background: var(--kplc-yellow);
            font-size: 0.65rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .hero-card h2 { margin: 4px 0 14px; font-size: 1.2rem; }

        .capability {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.16);
        }
        .capability:last-child { border-bottom: 0; }

        .capability-icon {
            width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
            flex: 0 0 36px;
            border-radius: 10px;
            color: var(--kplc-blue-dark);
            background: #fff;
            font-size: 0.85rem;
        }
        .capability strong { display: block; margin-bottom: 3px; font-size: 0.9rem; }
        .capability small { color: rgba(255,255,255,0.72); line-height: 1.3; font-size: 0.75rem; }

        /* METRICS */
        .metrics { position: relative; z-index: 8; margin-top: -50px; }
        .metric-grid {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-radius: 18px;
            background: #fff;
            box-shadow: var(--shadow-soft);
        }
        .metric { padding: 8px 16px; text-align: center; border-right: 1px solid var(--kplc-border); }
        .metric:last-child { border-right: 0; }
        .metric strong { display: block; color: var(--kplc-blue); font-size: 1.5rem; font-weight: 900; }
        .metric span { display: block; margin-top: 4px; color: var(--kplc-muted); font-size: 0.75rem; font-weight: 800; }

        /* SECTIONS */
        .section { padding: 70px 0; }
        .section-soft { background: var(--kplc-sky); }
        .section-dark { color: #fff; background: var(--kplc-blue-deep); }

        .section-heading { max-width: 780px; margin: 0 auto 40px; text-align: center; }
        .section-kicker {
            display: inline-flex; align-items: center; gap: 8px;
            margin-bottom: 10px;
            color: var(--kplc-blue);
            font-size: 0.75rem; font-weight: 900;
            letter-spacing: 0.14em; text-transform: uppercase;
        }
        .section-dark .section-kicker { color: var(--kplc-yellow); }

        .section-heading h2 {
            margin: 0;
            color: var(--kplc-blue-dark);
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            line-height: 1.12;
            letter-spacing: -0.03em;
        }
        .section-dark .section-heading h2 { color: #fff; }
        .section-heading p { margin: 14px 0 0; color: var(--kplc-muted); font-size: 0.95rem; line-height: 1.6; }
        .section-dark .section-heading p { color: rgba(255,255,255,0.7); }

        /* SERVICES */
        .services-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 20px; }

        .service-card {
            position: relative;
            padding: 24px;
            overflow: hidden;
            border: 1px solid var(--kplc-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(4,47,103,0.08);
            transition: transform 0.24s ease, box-shadow 0.24s ease;
        }
        .service-card::after {
            content: "";
            position: absolute;
            right: -32px; bottom: -44px;
            width: 110px; height: 110px;
            border-radius: 50%;
            background: rgba(255,210,0,0.15);
        }
        .service-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(4,47,103,0.16); }

        .service-icon {
            width: 48px; height: 48px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, #0c72e5, var(--kplc-blue-dark));
            font-size: 1.1rem;
        }
        .service-card h3 { margin: 0 0 8px; color: var(--kplc-blue-dark); font-size: 1rem; }
        .service-card p { margin: 0; color: var(--kplc-muted); line-height: 1.5; font-size: 0.85rem; }
        .service-card a {
            position: relative; z-index: 2;
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 14px;
            color: var(--kplc-blue);
            font-size: 0.8rem; font-weight: 900;
        }
        .service-card .service-notes { margin-top: 10px; color: #53657c; font-size: 0.8rem; line-height: 1.5; }

        .public-service-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .public-service-meta span {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 8px;
            border-radius: 999px;
            color: #31527a;
            background: #eef6ff;
            font-size: 0.65rem;
            font-weight: 800;
        }

        /* PRICING */
        .pricing-shell { max-width: 1180px; margin: 0 auto; }
        .pricing-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .pricing-summary-card {
            padding: 18px;
            border: 1px solid var(--kplc-border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(4,47,103,0.08);
        }
        .pricing-summary-card i {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px;
            margin-bottom: 10px;
            border-radius: 11px;
            color: #fff;
            background: linear-gradient(135deg, #0c72e5, var(--kplc-blue-dark));
        }
        .pricing-summary-card strong { display: block; margin-bottom: 4px; color: var(--kplc-blue-dark); font-size: 0.95rem; }
        .pricing-summary-card span { color: var(--kplc-muted); font-size: 0.82rem; line-height: 1.5; }

        .pricing-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--kplc-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: var(--shadow-soft);
        }
        .pricing-table { width: 100%; min-width: 720px; border-collapse: collapse; }
        .pricing-table thead th {
            padding: 14px 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--kplc-blue), var(--kplc-blue-dark));
            font-size: 0.78rem;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .pricing-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #e9eff6;
            color: #334155;
            font-size: 0.85rem;
        }
        .pricing-table tbody tr:last-child td { border-bottom: 0; }
        .pricing-table tbody tr:hover { background: #f7fbff; }
        .pricing-table .price-index { width: 60px; color: var(--kplc-muted); font-weight: 800; text-align: center; }
        .pricing-table .price-name { font-weight: 800; color: var(--kplc-blue-dark); }
        .pricing-table .price-rate {
            width: 170px;
            color: var(--kplc-green);
            font-weight: 900;
            text-align: right;
            white-space: nowrap;
        }

        .pricing-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 16px;
            padding: 14px 16px;
            border-left: 4px solid var(--kplc-yellow);
            border-radius: 10px;
            color: #5b4b00;
            background: #fff9d7;
            line-height: 1.5;
            font-size: 0.85rem;
        }
        .pricing-note i { margin-top: 3px; color: var(--kplc-yellow-dark); }

        .public-empty-state {
            grid-column: 1 / -1;
            padding: 36px 20px;
            border: 1px dashed var(--kplc-border);
            border-radius: 16px;
            color: var(--kplc-muted);
            background: #fff;
            text-align: center;
        }
        .public-empty-state i { display: block; margin-bottom: 10px; color: var(--kplc-blue); font-size: 1.6rem; }

        /* EQUIPMENT */
        .equipment-layout {
            display: grid;
            grid-template-columns: 1.06fr 0.94fr;
            align-items: stretch;
            gap: 24px;
        }
        .equipment-feature {
            position: relative;
            min-height: 450px;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
        }
        .equipment-feature img { width: 100%; height: 100%; object-fit: cover; }
        .equipment-feature-overlay {
            position: absolute;
            inset: auto 0 0;
            padding: 60px 24px 20px;
            color: #fff;
            background: linear-gradient(180deg, transparent, rgba(2,24,56,0.94));
        }
        .equipment-feature-overlay span {
            display: inline-flex; align-items: center; gap: 6px;
            margin-bottom: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            color: var(--kplc-blue-dark);
            background: var(--kplc-yellow);
            font-size: 0.6rem; font-weight: 900;
        }
        .equipment-feature-overlay h3 { margin: 0 0 6px; font-size: 1.3rem; }
        .equipment-feature-overlay p { margin: 0; color: rgba(255,255,255,0.76); line-height: 1.4; font-size: 0.85rem; }

        .equipment-cards { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; }
        .equipment-card {
            overflow: hidden;
            border: 1px solid var(--kplc-border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(4,47,103,0.08);
        }
        .equipment-card img { height: 140px; object-fit: cover; }
        .equipment-card-content { padding: 14px; }
        .equipment-card h4 { margin: 0 0 4px; color: var(--kplc-blue-dark); font-size: 0.9rem; }
        .equipment-card p { margin: 0; color: var(--kplc-muted); font-size: 0.78rem; line-height: 1.4; }

        /* ABOUT */
        .about-grid {
            display: grid;
            grid-template-columns: 0.94fr 1.06fr;
            align-items: center;
            gap: 50px;
        }
        .about-collage { position: relative; min-height: 440px; }
        .about-main-photo {
            position: absolute;
            inset: 0 60px 60px 0;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: var(--shadow-strong);
        }
        .about-main-photo img { width: 100%; height: 100%; object-fit: cover; }
        .about-secondary-photo {
            position: absolute;
            right: 0; bottom: 0;
            width: 46%; height: 180px;
            overflow: hidden;
            border: 6px solid #fff;
            border-radius: 18px;
            box-shadow: var(--shadow-soft);
        }
        .about-secondary-photo img { width: 100%; height: 100%; object-fit: cover; }

        .about-copy h2 {
            margin: 0 0 14px;
            color: var(--kplc-blue-dark);
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            line-height: 1.12;
        }
        .about-copy > p { color: var(--kplc-muted); line-height: 1.6; font-size: 0.95rem; }

        .check-list { margin: 20px 0 24px; padding: 0; list-style: none; }
        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            color: #324158;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .check-list i { margin-top: 2px; color: var(--kplc-green); }

        /* WORKFLOW */
        .workflow-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
        .workflow-card {
            position: relative;
            min-height: 190px;
            padding: 22px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            background: rgba(255,255,255,0.07);
        }
        .workflow-card::before {
            content: "";
            position: absolute;
            top: 42px; left: 62px;
            width: 100%; height: 2px;
            background: linear-gradient(90deg, var(--kplc-yellow), transparent);
        }
        .workflow-card:last-child::before { display: none; }

        .workflow-number {
            position: relative;
            z-index: 2;
            width: 40px; height: 40px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            border-radius: 50%;
            color: var(--kplc-blue-dark);
            background: var(--kplc-yellow);
            font-size: 0.9rem; font-weight: 900;
        }
        .workflow-card h3 { margin: 0 0 6px; font-size: 0.95rem; }
        .workflow-card p { margin: 0; color: rgba(255,255,255,0.7); font-size: 0.8rem; line-height: 1.5; }

        /* GALLERY */
        .gallery-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr 0.75fr;
            grid-template-rows: 200px 200px;
            gap: 14px;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            background: #d8e4f0;
        }
        .gallery-item:first-child { grid-row: span 2; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
        .gallery-item:hover img { transform: scale(1.05); }
        .gallery-caption {
            position: absolute;
            inset: auto 0 0;
            padding: 30px 14px 12px;
            color: #fff;
            background: linear-gradient(180deg, transparent, rgba(2,24,56,0.9));
        }
        .gallery-caption strong { display: block; margin-bottom: 2px; font-size: 0.85rem; }
        .gallery-caption small { color: rgba(255,255,255,0.72); font-size: 0.7rem; }

        /* CTA */
        .cta {
            padding: 60px 0;
            color: #fff;
            background:
                linear-gradient(110deg, rgba(3,29,67,0.97), rgba(0,87,184,0.88)),
                url("https://www.sdes.co.in/images/lab/meter-mte-setup.jpg") center center / cover no-repeat;
        }
        .cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 30px; }
        .cta h2 {
            max-width: 760px;
            margin: 0 0 10px;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            line-height: 1.12;
        }
        .cta p { max-width: 760px; margin: 0; color: rgba(255,255,255,0.76); line-height: 1.5; font-size: 0.95rem; }

        /* FOOTER */
        .site-footer { padding: 50px 0 20px; color: rgba(255,255,255,0.73); background: #02162f; }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.35fr 0.9fr 0.9fr 1.15fr;
            gap: 36px;
            margin-bottom: 36px;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 900;
        }
        .footer-brand img {
            width: 44px; height: 44px;
            object-fit: contain;
            padding: 4px;
            border-radius: 50%;
            background: #fff;
        }
        .footer-column h3 { margin: 0 0 12px; color: #fff; font-size: 0.85rem; }
        .footer-column p, .footer-column a, .footer-column li {
            color: rgba(255,255,255,0.67);
            line-height: 1.6;
            font-size: 0.82rem;
        }
        .footer-column ul { margin: 0; padding: 0; list-style: none; }
        .footer-contact li { display: flex; gap: 8px; margin-bottom: 6px; }
        .footer-contact i { margin-top: 5px; color: var(--kplc-yellow); }
        .footer-bottom {
            padding-top: 18px;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            border-top: 1px solid rgba(255,255,255,0.12);
            font-size: 0.75rem;
        }
        .photo-credit { margin-top: 8px; color: rgba(255,255,255,0.45); font-size: 0.6rem; }

        /* RESPONSIVE */
        @media (max-width: 1360px) {
            .nav-links { display: none; }
            .menu-toggle { display: inline-flex; }
        }

        @media (max-width: 1120px) {
            .video-showcase-layout { grid-template-columns: 1fr; gap: 30px; }
            .video-column .section-heading,
            .video-column .section-heading h2,
            .video-column .section-heading p { text-align: center; }
            .video-column .section-heading p { margin: 4px auto 0; }
            .video-wrapper { max-width: 460px; margin: 0 auto; }
            .ads-grid { grid-template-columns: repeat(4, 1fr); }
            .hero-grid, .equipment-layout, .about-grid { grid-template-columns: 1fr; }
            .hero-card { max-width: 650px; }
            .services-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .pricing-summary { grid-template-columns: 1fr 1fr; }
            .workflow-grid { grid-template-columns: repeat(2, 1fr); }
            .workflow-card:nth-child(2)::before { display: none; }
            .equipment-feature { min-height: 400px; }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 790px) {
            .container { width: min(100% - 20px, 1420px); }
            .site-header { backdrop-filter: none; }
            .navbar { min-height: 64px; gap: 8px; }
            .brand { max-width: calc(100% - 48px); }
            .brand-mark { width: 42px; height: 42px; flex-basis: 42px; }
            .brand-mark img { width: 36px; height: 36px; }
            .brand-name { overflow: hidden; font-size: 0.85rem; text-overflow: ellipsis; }
            .brand-subtitle { display: none; }
            .nav-actions > .btn { display: none; }

            .video-showcase { padding: 80px 0 30px; margin-top: 56px; }
            .video-wrapper { max-width: 100%; border-radius: 12px; }
            .ads-grid { grid-template-columns: 1fr 1fr; }
            .ad-card { padding: 16px 14px; }
            .quick-stats { grid-template-columns: 1fr 1fr 1fr; padding: 10px 12px; }
            .stat-number { font-size: 0.9rem; }

            .hero { min-height: auto; padding: 100px 0 60px; }
            .hero h1 { font-size: clamp(2.2rem, 9vw, 3.2rem); }
            .metric-grid { grid-template-columns: repeat(2, 1fr); }
            .metric:nth-child(2) { border-right: 0; }
            .metric:nth-child(-n+2) { border-bottom: 1px solid var(--kplc-border); }

            .services-grid, .equipment-cards, .workflow-grid,
            .footer-grid, .pricing-summary { grid-template-columns: 1fr; }
            .workflow-card::before { display: none; }

            .section { padding: 50px 0; }

            .gallery-grid { grid-template-columns: 1fr 1fr; grid-template-rows: 260px 180px 180px; }
            .gallery-item:first-child { grid-column: span 2; grid-row: auto; }

            .about-collage { min-height: 380px; }
            .about-main-photo { inset: 0 30px 60px 0; }
            .about-secondary-photo { width: 52%; height: 160px; }

            .cta-inner, .footer-bottom { align-items: flex-start; flex-direction: column; }

            .quote-modal { max-width: 96%; max-height: calc(100vh - 20px); border-radius: 16px; }
            .quote-modal-body { padding: 10px 14px 6px; }
            .quote-modal-footer { padding: 9px 14px 10px; }
            .quote-modal-body .form-grid { grid-template-columns: 1fr; }
            .quote-modal-body .form-grid .full-width,
            .ai-assistant { grid-column: 1; }

            /* Stack item row vertically */
            .quote-item { grid-template-columns: 1fr 1fr; }
            .quote-item .item-equipment,
            .quote-item .item-equipment-name,
            .quote-item .item-service,
            .quote-item .item-qty { grid-column: span 1; }
            .quote-item .item-serial,
            .quote-item .item-rating,
            .quote-item .item-model,
            .quote-item .item-manufacturer { grid-column: span 2; }
            .quote-item .remove-item { grid-column: span 2; width: 100%; justify-self: stretch; }
        }

        @media (max-width: 560px) {
            .container, .site-header .container { width: min(100% - 16px, 1420px); }
            .navbar { gap: 6px; }
            .brand { max-width: calc(100% - 48px); gap: 8px; }
            .brand-mark { width: 36px; height: 36px; flex-basis: 36px; }
            .brand-mark img { width: 31px; height: 31px; }
            .brand-name { font-size: 0.76rem; }
            .menu-toggle { width: 40px; height: 40px; flex-basis: 40px; border-radius: 10px; }
            .mobile-menu { padding-bottom: 10px; max-height: calc(100vh - 64px); overflow-y: auto; }
            .mobile-menu a { min-height: 46px; font-size: 0.88rem; }

            .video-showcase { padding: 42px 0 22px; margin-top: 56px; }
            .video-column .section-heading h2 { font-size: 1.2rem; }
            .video-column .section-heading p { font-size: 0.76rem; }
            .video-container { padding-top: 75%; }
            .video-controls { padding: 10px 10px 8px; }
            .video-controls-inner { flex-direction: column; align-items: stretch; gap: 7px; }
            .video-control-left, .video-control-right { width: 100%; justify-content: space-between; }
            .video-info { font-size: 0.55rem; gap: 4px; }
            .btn-login-video { padding: 4px 10px; font-size: 0.55rem; }

            .ads-grid { grid-template-columns: minmax(0,1fr); gap: 10px; }
            .ad-card { padding: 14px 12px; }
            .ad-card .ad-icon { width: 32px; height: 32px; font-size: 0.9rem; margin-bottom: 8px; }
            .ad-card h4 { font-size: 0.7rem; }
            .ad-card p { font-size: 0.6rem; margin-bottom: 6px; }
            .ad-card .ad-badge { font-size: 0.5rem; padding: 1px 8px; }

            .quick-stats { grid-template-columns: repeat(3, minmax(0,1fr)); gap: 5px; padding: 8px; }
            .stat-number { font-size: 0.82rem; }
            .stat-label { font-size: 0.48rem; }

            .btn-quote { width: 100%; justify-content: center; padding: 12px 20px; font-size: 0.85rem; }

            .trust-indicators { gap: 6px; margin-top: 12px; }
            .trust-item { font-size: 0.56rem; padding: 3px 8px; }

            .hero { padding: 48px 0 42px; }
            .hero h1 { font-size: clamp(1.9rem, 11vw, 2.6rem); }
            .hero-summary { margin-top: 16px; font-size: 0.95rem; }
            .hero-actions { flex-direction: column; }
            .hero-actions .btn { width: 100%; }
            .hero-card { padding: 18px; border-radius: 16px; }

            .metric-grid { padding: 10px; }
            .metric { padding: 10px 6px; }
            .metric strong { font-size: 1.15rem; }

            .service-card { padding: 18px; }
            .gallery-grid { grid-template-columns: minmax(0,1fr); grid-template-rows: repeat(5, 190px); }
            .gallery-item:first-child { grid-column: auto; }
            .equipment-feature { min-height: 320px; }
            .about-collage { min-height: 320px; }
            .about-main-photo { inset: 0 18px 55px 0; }
            .about-secondary-photo { width: 58%; height: 140px; }
            .footer-bottom { font-size: 0.7rem; }

            /* Full-screen modal on phones */
            .quote-modal-overlay { padding: 0; }
            .quote-modal {
                width: 100%;
                max-width: none;
                height: 100dvh;
                max-height: 100dvh;
                border-radius: 0;
            }
            .quote-modal-header { padding: 10px 42px 8px 12px; }
            .quote-modal-body { padding: 9px 12px 7px; -webkit-overflow-scrolling: touch; }
            .quote-modal-body .form-grid { grid-template-columns: minmax(0,1fr); }
            .quote-modal-footer { padding: 8px 12px 10px; }
            .btn-submit-quote { min-height: 44px; }

            /* Stack every item field vertically */
            .quote-item { grid-template-columns: 1fr; }
            .quote-item .item-equipment,
            .quote-item .item-equipment-name,
            .quote-item .item-serial,
            .quote-item .item-manufacturer,
            .quote-item .item-model,
            .quote-item .item-rating,
            .quote-item .item-service,
            .quote-item .item-qty,
            .quote-item .remove-item { grid-column: auto; }
            .quote-item .item-qty { max-width: none; }
            .quote-item .remove-item { width: 100%; justify-self: stretch; }

            .quote-item .remove-item { width: 100%; }
            .quote-item .item-remove-wrapper { justify-content: stretch; }
        }

        @media (max-width: 380px) {
            .brand-name { font-size: 0.70rem; }
            .brand-mark { width: 34px; height: 34px; flex-basis: 34px; }
            .brand-mark img { width: 29px; height: 29px; }
            .hero h1 { font-size: 1.8rem; }
            .quick-stats { grid-template-columns: 1fr; }
            .metric-grid { grid-template-columns: 1fr; }
            .metric { border-right: 0 !important; border-bottom: 1px solid var(--kplc-border); }
            .metric:last-child { border-bottom: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>

<body>
<div class="page-shell">

    
    <header class="site-header">
        <div class="container">
            <div class="navbar">
                <a href="#home" class="brand">
                    <span class="brand-mark">
                        <img src="<?php echo e(asset('img/logo.png')); ?>" alt="Kenya Power logo">
                    </span>
                    <span>
                        <span class="brand-name">KPLC <span>Meter Central Laboratory</span></span>
                        <span class="brand-subtitle">Calibration · Testing · Certification</span>
                    </span>
                </a>

                <nav class="nav-links" aria-label="Primary navigation">
                    <a href="#home" class="active"><i class="fas fa-house"></i> Home</a>
                    <a href="#about"><i class="fas fa-circle-info"></i> About</a>
                    <a href="#services"><i class="fas fa-gears"></i> Services</a>
                    <a href="#pricing"><i class="fas fa-tags"></i> Pricing</a>
                    <a href="#equipment"><i class="fas fa-flask-vial"></i> Equipment</a>
                    <a href="#workflow"><i class="fas fa-list-check"></i> Workflow</a>
                    <a href="#contact"><i class="fas fa-envelope"></i> Contact</a>
                </nav>

                <div class="nav-actions">
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline">
                            <i class="fas fa-gauge-high"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="#pricing" class="btn btn-green">
                            <i class="fas fa-tags"></i> Pricing
                        </a>
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-outline">
                            <i class="fas fa-right-to-bracket"></i> Login
                        </a>
                        <?php if(Route::has('register')): ?>
                            <a href="<?php echo e(route('register')); ?>" class="btn btn-yellow">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <button type="button" class="menu-toggle" id="menuToggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobileMenu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <div class="mobile-menu" id="mobileMenu">
                <a href="#home"><i class="fas fa-house"></i> Home</a>
                <a href="#about"><i class="fas fa-circle-info"></i> About</a>
                <a href="#services"><i class="fas fa-gears"></i> Services</a>
                <a href="#pricing"><i class="fas fa-tags"></i> Pricing</a>
                <a href="#equipment"><i class="fas fa-flask-vial"></i> Equipment</a>
                <a href="#workflow"><i class="fas fa-list-check"></i> Workflow</a>
                <a href="#contact"><i class="fas fa-envelope"></i> Contact</a>
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-gauge-high"></i> Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>"><i class="fas fa-right-to-bracket"></i> Login</a>
                    <?php if(Route::has('register')): ?>
                        <a href="<?php echo e(route('register')); ?>"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        
        <section class="video-showcase" id="video-section">
            <div class="container">
                <div class="video-showcase-layout">
                    <div class="video-column">
                        <div class="section-heading">
                            <span class="section-kicker">
                                <i class="fas fa-play-circle"></i> Laboratory in Action
                            </span>
                            <h2>Music & <span>Motional</span> Meter Laboratory</h2>
                            <p>Watch our state-of-the-art meter calibration and testing laboratory in operation</p>
                        </div>

                        <div class="video-wrapper">
                            <div class="video-container" id="videoContainer">
                                <?php
                                    $videoPath = public_path('videos/music-motional-lab.mp4');
                                    $videoExists = file_exists($videoPath);
                                    $videoUrl = $videoExists ? asset('videos/music-motional-lab.mp4') : 'https://www.w3schools.com/html/mov_bbb.mp4';
                                ?>

                                <video
                                    id="homepageVideo"
                                    autoplay muted loop playsinline preload="auto"
                                    poster="<?php echo e(asset('img/video-poster.jpg')); ?>"
                                >
                                    <?php if($videoExists): ?>
                                        <source src="<?php echo e($videoUrl); ?>" type="video/mp4">
                                    <?php endif; ?>
                                    <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                                    <source src="https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_1mb.mp4" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>

                                <div class="video-controls">
                                    <div class="video-controls-inner">
                                        <div class="video-control-left">
                                            <button id="playPauseBtn" class="play-btn" aria-label="Play or pause video">
                                                <i class="fas fa-pause" id="playIcon"></i>
                                            </button>
                                            <div class="video-info">
                                                <span class="status-dot" id="statusDot"></span>
                                                <span id="videoStatus">Playing</span>
                                                <span style="opacity:0.3;">•</span>
                                                <span>🎵 Music & Motion</span>
                                            </div>
                                        </div>
                                        <div class="video-control-right">
                                            <span class="mute-indicator" style="color:rgba(255,255,255,0.3);font-size:0.55rem;">
                                                <i class="fas fa-volume-mute"></i>
                                            </span>
                                            <a href="<?php echo e(route('login')); ?>" class="btn-login-video">
                                                <i class="fas fa-right-to-bracket"></i> Login
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="video-quote-section">
                            <button class="btn-quote" onclick="openQuoteModal()">
                                <i class="fas fa-file-invoice-dollar"></i>
                                Request a Free Quote
                                <span class="quote-badge">AI-Powered</span>
                            </button>
                            <p class="quote-subtext">
                                <i class="fas fa-robot"></i> Our AI assistant will help prepare your quote instantly
                            </p>
                        </div>
                    </div>

                    <div class="ads-column">
                        <div class="ads-grid">
                            <div class="ad-card">
                                <div class="ad-icon"><i class="fas fa-certificate"></i></div>
                                <h4>ISO/IEC 17025</h4>
                                <p>Internationally recognized laboratory competence for testing and calibration</p>
                                <div class="ad-badge">Accredited</div>
                            </div>
                            <div class="ad-card">
                                <div class="ad-icon"><i class="fas fa-ruler"></i></div>
                                <h4>Traceable Measurements</h4>
                                <p>All results traceable to international standards with full uncertainty</p>
                                <div class="ad-badge">Precision</div>
                            </div>
                            <div class="ad-card">
                                <div class="ad-icon"><i class="fas fa-file-pdf"></i></div>
                                <h4>Digital Certificates</h4>
                                <p>Secure PDF certificates with digital signatures and test reports</p>
                                <div class="ad-badge">Instant Access</div>
                            </div>
                            <div class="ad-card">
                                <div class="ad-icon"><i class="fas fa-clock"></i></div>
                                <h4>24/7 Portal Access</h4>
                                <p>View, download and manage your calibration certificates anytime</p>
                                <div class="ad-badge">Always On</div>
                            </div>
                        </div>

                        <div class="quick-stats">
                            <div class="stat-item">
                                <span class="stat-number">500+</span>
                                <span class="stat-label">Clients Served</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">98%</span>
                                <span class="stat-label">Satisfaction Rate</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">24hr</span>
                                <span class="stat-label">Quote Turnaround</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="trust-indicators">
                    <div class="trust-item"><i class="fas fa-circle-check"></i> ISO/IEC 17025</div>
                    <div class="trust-item"><i class="fas fa-circle-check"></i> Traceable Measurements</div>
                    <div class="trust-item"><i class="fas fa-circle-check"></i> Digital Certificates</div>
                    <div class="trust-item"><i class="fas fa-circle-check"></i> 24/7 Access</div>
                </div>

                <div class="scroll-indicator">
                    <a href="#home">
                        <i class="fas fa-chevron-down"></i>
                        <span>Scroll to explore</span>
                    </a>
                </div>
            </div>
        </section>

        
        <section class="hero" id="home">
            <div class="container hero-grid">
                <div>
                    <div class="hero-eyebrow"><i class="fas fa-gauge-high"></i> Welcome to</div>
                    <h1>Meter Central <span>Laboratory</span></h1>
                    <p class="hero-summary">
                        Supporting trusted electricity measurement through precision energy meter testing, calibration,
                        metrological traceability, controlled review, and secure digital certification.
                    </p>
                    <div class="hero-actions">
                        <a href="#services" class="btn btn-yellow">Explore Laboratory Services <i class="fas fa-arrow-right"></i></a>
                        <?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-white">Open Dashboard <i class="fas fa-gauge-high"></i></a>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="btn btn-white">Access Laboratory Portal <i class="fas fa-arrow-right-to-bracket"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-trust">
                        <span><i class="fas fa-circle-check"></i> Controlled laboratory testing</span>
                        <span><i class="fas fa-circle-check"></i> Traceable measurement results</span>
                        <span><i class="fas fa-circle-check"></i> Digital certificate workflow</span>
                        <span><i class="fas fa-circle-check"></i> Published laboratory pricing</span>
                    </div>
                </div>

                <aside class="hero-card">
                    <span class="hero-card-label"><i class="fas fa-microscope"></i> Laboratory capabilities</span>
                    <h2>Precision testing for modern energy meters</h2>
                    <div class="capability">
                        <span class="capability-icon"><i class="fas fa-gauge-high"></i></span>
                        <div>
                            <strong>Meter Accuracy Testing</strong>
                            <small>Single-phase, three-phase, whole-current, CT- and PT-operated meters.</small>
                        </div>
                    </div>
                    <div class="capability">
                        <span class="capability-icon"><i class="fas fa-wave-square"></i></span>
                        <div>
                            <strong>Uncertainty Evaluation</strong>
                            <small>Trial readings, repeatability, error and expanded uncertainty calculations.</small>
                        </div>
                    </div>
                    <div class="capability">
                        <span class="capability-icon"><i class="fas fa-certificate"></i></span>
                        <div>
                            <strong>Controlled Certification</strong>
                            <small>Generation, checking, approval, signatures, issue and secure customer access.</small>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        
        <section class="metrics">
            <div class="container">
                <div class="metric-grid">
                    <div class="metric"><strong>1P &amp; 3P</strong><span>Energy meter testing</span></div>
                    <div class="metric"><strong>10</strong><span>Mandatory trial readings</span></div>
                    <div class="metric"><strong>95%</strong><span>Confidence-level reporting</span></div>
                    <div class="metric"><strong>24/7</strong><span>Customer certificate access</span></div>
                </div>
            </div>
        </section>

        
        <section class="section" id="services">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker"><i class="fas fa-gauge-high"></i> Laboratory services</span>
                    <h2>Reliable testing, calibration and certification</h2>
                    <p>The laboratory supports electricity metering, industrial measurement and customer equipment through structured technical workflows and controlled laboratory procedures.</p>
                </div>

                <div class="services-grid">
                    <?php $__empty_1 = true; $__currentLoopData = $serviceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $serviceCode = strtoupper((string) ($service->serv_code ?? ''));
                            $serviceIcon = match (true) {
                                str_starts_with($serviceCode, 'EM') => 'fa-gauge-high',
                                str_starts_with($serviceCode, 'CB') => 'fa-shield-halved',
                                str_starts_with($serviceCode, 'ET') => 'fa-bolt',
                                str_starts_with($serviceCode, 'CA') => 'fa-sliders',
                                default => 'fa-screwdriver-wrench',
                            };
                        ?>
                        <article class="service-card">
                            <span class="service-icon"><i class="fas <?php echo e($serviceIcon); ?>"></i></span>
                            <h3><?php echo e($service->serv_name); ?></h3>
                            <p class="service-notes"><?php echo e($service->notes); ?></p>
                            <div class="public-service-meta">
                                <span><i class="fas fa-hashtag"></i> <?php echo e($service->serv_code); ?></span>
                                <span><i class="fas fa-book-open"></i> <?php echo e($service->serv_method); ?></span>
                            </div>
                            <a href="#contact">Enquire about this service <i class="fas fa-arrow-right"></i></a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="public-empty-state">
                            <i class="fas fa-flask-vial"></i>
                            <strong>No public laboratory services are currently listed.</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        
        <section class="section section-soft" id="pricing">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker"><i class="fas fa-tags"></i> Public laboratory pricing</span>
                    <h2>Indicative calibration and testing charges</h2>
                    <p>View the current published rate for each equipment category. These prices are available to visitors before login and are loaded directly from the laboratory database.</p>
                </div>

                <div class="pricing-shell">
                    <div class="pricing-summary">
                        <article class="pricing-summary-card">
                            <i class="fas fa-database"></i>
                            <strong>Live database rates</strong>
                            <span>Prices are read directly from the active equipment-type catalogue.</span>
                        </article>
                        <article class="pricing-summary-card">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <strong>Formal quotation issued</strong>
                            <span>The final payable amount is confirmed through an official laboratory quotation.</span>
                        </article>
                        <article class="pricing-summary-card">
                            <i class="fas fa-circle-info"></i>
                            <strong>Special work assessed separately</strong>
                            <span>Repairs, transport, accessories and non-standard tests may attract additional charges.</span>
                        </article>
                    </div>

                    <div class="pricing-table-wrap">
                        <table class="pricing-table">
                            <thead>
                                <tr>
                                    <th class="price-index">#</th>
                                    <th>Equipment Category</th>
                                    <th class="price-rate">Published Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $equipmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $equipmentType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="price-index"><?php echo e($loop->iteration); ?></td>
                                        <td class="price-name"><?php echo e($equipmentType->name); ?></td>
                                        <td class="price-rate">KES <?php echo e(number_format((float) $equipmentType->rate, 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="public-empty-state">
                                                <i class="fas fa-tags"></i>
                                                No public pricing records are currently available.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pricing-note">
                        <i class="fas fa-circle-info"></i>
                        <div>
                            <strong>Pricing notice:</strong>
                            The published amount is an indicative base calibration or testing rate. The final quotation may
                            vary depending on quantity, equipment condition, requested scope, repairs, accessories, transport,
                            taxes and other approved requirements.
                        </div>
                    </div>

                    <div style="margin-top:24px;text-align:center;">
                        <?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-blue">Open Dashboard <i class="fas fa-gauge-high"></i></a>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="btn btn-blue">Login to Submit a Request <i class="fas fa-right-to-bracket"></i></a>
                            <?php if(Route::has('register')): ?>
                                <a href="<?php echo e(route('register')); ?>" class="btn btn-yellow" style="margin-left:10px;">Create Customer Account <i class="fas fa-user-plus"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        
        <section class="section" id="equipment">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker"><i class="fas fa-flask-vial"></i> Equipment and facilities</span>
                    <h2>Professional meter calibration infrastructure</h2>
                    <p>Modern meter test benches, reference standards, environmental controls and diagnostic workstations support accurate and repeatable testing.</p>
                </div>

                <div class="equipment-layout">
                    <article class="equipment-feature">
                        <img src="https://www.sdes.co.in/images/lab/meter-bench-technician.jpg" alt="Technician operating an energy meter calibration bench" loading="lazy">
                        <div class="equipment-feature-overlay">
                            <span><i class="fas fa-user-gear"></i> Technical operations</span>
                            <h3>Energy Meter Calibration Bench</h3>
                            <p>Technician-controlled test setup supporting accuracy, starting current, creep, repeatability and register verification procedures.</p>
                        </div>
                    </article>

                    <div class="equipment-cards">
                        <article class="equipment-card">
                            <img src="https://www.sdes.co.in/images/lab/meter-k2006-mt3000.jpg" alt="Reference standard and meter calibrator" loading="lazy">
                            <div class="equipment-card-content">
                                <h4>Reference Standards</h4>
                                <p>High-accuracy reference equipment for traceable electrical measurements.</p>
                            </div>
                        </article>
                        <article class="equipment-card">
                            <img src="https://www.sdes.co.in/images/lab/meter-mte-setup.jpg" alt="Energy meters mounted on calibration test equipment" loading="lazy">
                            <div class="equipment-card-content">
                                <h4>Meter Test Setup</h4>
                                <p>Multiple meter positions with controlled voltage, current and power-factor settings.</p>
                            </div>
                        </article>
                        <article class="equipment-card">
                            <img src="https://www.sdes.co.in/images/lab/meter-bench-wide.jpg" alt="Wide view of an energy meter calibration laboratory" loading="lazy">
                            <div class="equipment-card-content">
                                <h4>Laboratory Test Area</h4>
                                <p>Organized workspaces supporting safe, repeatable and documented test activities.</p>
                            </div>
                        </article>
                        <article class="equipment-card">
                            <img src="https://www.sdes.co.in/images/onsite/substation-team.jpg" alt="Technical team conducting metering work at a substation" loading="lazy">
                            <div class="equipment-card-content">
                                <h4>On-Site Metering Support</h4>
                                <p>Technical support for specialized field testing and commissioning activities.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        
        <section class="section" id="about">
            <div class="container about-grid">
                <div class="about-collage">
                    <div class="about-main-photo">
                        <img src="https://www.sdes.co.in/images/lab/meter-bench-wide.jpg" alt="Energy meter calibration laboratory" loading="lazy">
                    </div>
                    <div class="about-secondary-photo">
                        <img src="https://www.sdes.co.in/images/lab/meter-k2006-mt3000.jpg" alt="Electrical reference calibration instruments" loading="lazy">
                    </div>
                </div>

                <div class="about-copy">
                    <span class="section-kicker"><i class="fas fa-circle-info"></i> About the laboratory</span>
                    <h2>Supporting confidence in electricity measurement</h2>
                    <p>Meter Central Laboratory provides structured energy meter testing and calibration services designed to support accurate billing, technical compliance, traceable results and reliable customer service.</p>
                    <p>The digital laboratory platform manages the full lifecycle from customer request and job-card creation through testing, independent checking, approval, certificate issuance and customer access.</p>
                    <ul class="check-list">
                        <li><i class="fas fa-circle-check"></i> Controlled test procedures and recorded conditions</li>
                        <li><i class="fas fa-circle-check"></i> Role-based generation, checking and approval</li>
                        <li><i class="fas fa-circle-check"></i> Traceability details and reference-equipment records</li>
                        <li><i class="fas fa-circle-check"></i> Secure PDF certificates and attached test reports</li>
                    </ul>
                    <a href="#contact" class="btn btn-blue">Contact the Laboratory <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        
        <section class="section section-dark" id="workflow">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker"><i class="fas fa-list-check"></i> Digital workflow</span>
                    <h2>From equipment receipt to certificate delivery</h2>
                    <p>Each calibration follows a controlled workflow with recorded responsibility and clear segregation of duties.</p>
                </div>

                <div class="workflow-grid">
                    <article class="workflow-card">
                        <span class="workflow-number">1</span>
                        <h3>Receive and Register</h3>
                        <p>Customer and equipment details are captured, services selected and a calibration request created.</p>
                    </article>
                    <article class="workflow-card">
                        <span class="workflow-number">2</span>
                        <h3>Assign and Test</h3>
                        <p>An authorized technician performs the required tests and records laboratory readings and observations.</p>
                    </article>
                    <article class="workflow-card">
                        <span class="workflow-number">3</span>
                        <h3>Check and Approve</h3>
                        <p>Independent users review and approve the certificate under role and segregation-of-duty controls.</p>
                    </article>
                    <article class="workflow-card">
                        <span class="workflow-number">4</span>
                        <h3>Issue and Deliver</h3>
                        <p>The approved certificate and test report are issued and made available securely to the customer.</p>
                    </article>
                </div>
            </div>
        </section>

        
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker"><i class="fas fa-images"></i> Laboratory gallery</span>
                    <h2>Energy meter calibration in practice</h2>
                    <p>A visual overview of meter test benches, reference instruments, laboratory facilities and field support.</p>
                </div>

                <div class="gallery-grid">
                    <article class="gallery-item">
                        <img src="https://www.sdes.co.in/images/lab/meter-bench-technician.jpg" alt="Technician operating meter calibration equipment" loading="lazy">
                        <div class="gallery-caption"><strong>Calibration Operations</strong><small>Technician-operated energy meter test bench</small></div>
                    </article>
                    <article class="gallery-item">
                        <img src="https://www.sdes.co.in/images/lab/meter-k2006-mt3000.jpg" alt="Reference meter calibration instruments" loading="lazy">
                        <div class="gallery-caption"><strong>Reference Equipment</strong><small>High-accuracy calibration instrumentation</small></div>
                    </article>
                    <article class="gallery-item">
                        <img src="https://www.sdes.co.in/images/lab/meter-mte-setup.jpg" alt="Energy meters connected to test equipment" loading="lazy">
                        <div class="gallery-caption"><strong>Meter Test Setup</strong><small>Connected meters under controlled testing</small></div>
                    </article>
                    <article class="gallery-item">
                        <img src="https://www.sdes.co.in/images/onsite/substation-team.jpg" alt="Technical metering team at a substation" loading="lazy">
                        <div class="gallery-caption"><strong>Field Services</strong><small>Specialized site testing and commissioning</small></div>
                    </article>
                    <article class="gallery-item">
                        <img src="https://www.sdes.co.in/images/onsite/solar-site.jpg" alt="On-site meter calibration at an energy facility" loading="lazy">
                        <div class="gallery-caption"><strong>On-Site Verification</strong><small>Metering support at energy installations</small></div>
                    </article>
                </div>
            </div>
        </section>

        
        <section class="cta" id="contact">
            <div class="container cta-inner">
                <div>
                    <h2>Need energy meter testing or calibration services?</h2>
                    <p>Access the laboratory portal to submit requests, track calibration progress and securely download approved certificates and test reports.</p>
                </div>
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-yellow">Open Dashboard <i class="fas fa-arrow-right"></i></a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-yellow">Access the Portal <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="footer-brand">
                        <img src="<?php echo e(asset('img/logo.png')); ?>" alt="Kenya Power logo">
                        <span>KPLC Meter Central Laboratory</span>
                    </div>
                    <p>Reliable energy meter calibration, testing, metrological traceability and digital certification.</p>
                    <p class="photo-credit">Laboratory photographs are externally hosted reference images. Replace them with approved KPLC photographs before public production deployment.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#about">About the Laboratory</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#equipment">Equipment</a></li>
                        <li><a href="#workflow">Workflow</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul>
                        <li>Energy Meter Calibration</li>
                        <li>Accuracy Testing</li>
                        <li>Register Verification</li>
                        <li>Certificate Issuance</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul class="footer-contact">
                        <li><i class="fas fa-location-dot"></i><span>P.O. Box 30099-00100, Nairobi, Kenya</span></li>
                        <li><i class="fas fa-envelope"></i><span>meterlab@kplc.co.ke</span></li>
                        <li><i class="fas fa-phone"></i><span>+254 20 3201000</span></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo e(date('Y')); ?> KPLC Meter Central Laboratory. All rights reserved.</span>
                <span>Meter Calibration Laboratory Management System</span>
            </div>
        </div>
    </footer>

    
    <div class="quote-modal-overlay" id="quoteModal" aria-hidden="true">
        <div class="quote-modal" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">

            <div class="quote-modal-header">
                <button type="button" class="quote-modal-close" id="closeQuoteModal" aria-label="Close quote form">
                    <i class="fas fa-times"></i>
                </button>
                <div class="modal-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h2 id="quoteModalTitle">Request a Calibration Quote</h2>
                <p>Fill in your details and our AI assistant will help prepare a custom quote</p>
            </div>

            <form id="quoteForm" class="quote-form" method="POST">
                <div class="quote-modal-body">
                    <div class="form-grid">

                        <div class="form-group full-width">
                            <label for="quoteName">
                                Full Name <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="quoteName"
                                name="name"
                                autocomplete="name"
                                placeholder="Enter your full name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="quoteEmail">
                                Email <span class="required">*</span>
                            </label>
                            <input
                                type="email"
                                id="quoteEmail"
                                name="email"
                                autocomplete="email"
                                placeholder="example@email.com"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="quotePhone">
                                Phone <span class="required">*</span>
                            </label>
                            <input
                                type="tel"
                                id="quotePhone"
                                name="phone"
                                autocomplete="tel"
                                placeholder="+254 700 000 000"
                                required
                            >
                        </div>

                        <div class="form-group full-width">
                            <label for="quoteCompany">Company</label>
                            <input
                                type="text"
                                id="quoteCompany"
                                name="company"
                                autocomplete="organization"
                                placeholder="Company name"
                            >
                        </div>

                        <div class="form-group full-width">
                            <label>
                                Equipment &amp; Services Requested <span class="required">*</span>
                            </label>
                            <div id="quoteItems" class="quote-items"></div>
                            <button type="button" id="addQuoteItem" class="btn-add-item">
                                <i class="fas fa-plus"></i> Add another equipment
                            </button>
                        </div>

                        <div class="form-group full-width">
                            <label for="quoteRequirements">Requirements</label>
                            <input
                                type="text"
                                id="quoteRequirements"
                                name="requirements"
                                placeholder="Any special requirements..."
                            >
                        </div>

                        <div class="ai-assistant">
                            <div class="ai-assistant-header">
                                <div class="ai-icon"><i class="fas fa-robot"></i></div>
                                <h4>AI Quote Assistant</h4>
                                <span class="ai-badge">LIVE</span>
                            </div>
                            <div class="ai-messages" id="aiMessages">
                                <div class="ai-message">
                                    <div class="avatar ai">AI</div>
                                    <div class="bubble">
                                        Select the equipment, service, and quantity — I'll show you a running estimate.
                                    </div>
                                </div>
                            </div>
                            <div class="ai-input-group">
                                <input
                                    type="text"
                                    id="aiChatInput"
                                    placeholder="Ask about calibration..."
                                    autocomplete="off"
                                >
                                <button type="button" id="aiSendButton" aria-label="Send message">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="quote-modal-footer">
                    <button type="submit" class="btn-submit-quote" id="submitQuoteBtn">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Submit Quote Request</span>
                    </button>
                    <div class="secure-text">
                        <i class="fas fa-lock"></i>
                        Your information is secure and will only be used to prepare your quotation.
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       ELEMENTS
    ====================================================== */
    const quoteModal       = document.getElementById('quoteModal');
    const quoteForm        = document.getElementById('quoteForm');
    const closeQuoteButton = document.getElementById('closeQuoteModal');

    const aiInput      = document.getElementById('aiChatInput');
    const aiSendButton = document.getElementById('aiSendButton');
    const aiMessages   = document.getElementById('aiMessages');

    const itemsContainer = document.getElementById('quoteItems');
    const addBtn         = document.getElementById('addQuoteItem');
    const submitButton   = document.getElementById('submitQuoteBtn');

    const EQUIPMENT_TYPES = <?php echo json_encode($equipmentTypes ?? [], 15, 512) ?>;
    const SERVICE_TYPES   = <?php echo json_encode($serviceTypes ?? [], 15, 512) ?>;

    /* =====================================================
       MODAL OPEN/CLOSE
    ====================================================== */
    window.openQuoteModal = function () {
        if (!quoteModal) return;
        quoteModal.classList.add('active');
        quoteModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        setTimeout(() => {
            document.getElementById('quoteName')?.focus();
        }, 100);
    };

    window.closeQuoteModal = function () {
        if (!quoteModal) return;
        quoteModal.classList.remove('active');
        quoteModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    };

    closeQuoteButton?.addEventListener('click', () => window.closeQuoteModal());

    quoteModal?.addEventListener('click', (event) => {
        if (event.target === quoteModal) window.closeQuoteModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && quoteModal?.classList.contains('active')) {
            window.closeQuoteModal();
        }
    });

    /* =====================================================
       AI CHAT (local estimate reply)
    ====================================================== */
    function sendAIMessage() {
        if (!aiInput || !aiMessages) return;

        const message = aiInput.value.trim();
        if (!message) return;

        const userMsg = document.createElement('div');
        userMsg.className = 'ai-message user';
        userMsg.innerHTML = `<div class="bubble"></div><div class="avatar user">YOU</div>`;
        userMsg.querySelector('.bubble').textContent = message;
        aiMessages.appendChild(userMsg);

        aiInput.value = '';
        aiMessages.scrollTop = aiMessages.scrollHeight;

        setTimeout(() => {
            const { total, count } = computeEstimate();

            const aiMsg = document.createElement('div');
            aiMsg.className = 'ai-message';
            aiMsg.innerHTML = `<div class="avatar ai">AI</div><div class="bubble"></div>`;

            aiMsg.querySelector('.bubble').textContent = count
                ? `You currently have ${count} item(s) selected. Estimated base total: KES ${total.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}.`
                : 'I can help you estimate the calibration cost. Add at least one equipment and service above, and I will total it for you.';

            aiMessages.appendChild(aiMsg);
            aiMessages.scrollTop = aiMessages.scrollHeight;
        }, 400);
    }

    aiSendButton?.addEventListener('click', (event) => {
        event.preventDefault();
        sendAIMessage();
    });

    aiInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            sendAIMessage();
        }
    });

    /* =====================================================
       ESTIMATE HELPER
    ====================================================== */
    function computeEstimate() {
        let total = 0;
        let count = 0;

        if (!itemsContainer) return { total, count };

        itemsContainer.querySelectorAll('.quote-item').forEach((row) => {
            const eqSel = row.querySelector('.item-equipment');
            const qty   = parseInt(row.querySelector('.item-qty').value || '0', 10);
            if (!eqSel || !eqSel.value || qty < 1) return;

            const rate = parseFloat(eqSel.options[eqSel.selectedIndex].dataset.rate || '0');
            total += rate * qty;
            count += qty;
        });

        return { total, count };
    }

    function updateQuoteEstimate() {
        if (!aiMessages || !itemsContainer) return;

        let total = 0;
        const lines = [];

        itemsContainer.querySelectorAll('.quote-item').forEach((row) => {
            const eqSel  = row.querySelector('.item-equipment');
            const svSel  = row.querySelector('.item-service');
            const qty    = parseInt(row.querySelector('.item-qty').value || '0', 10);
            if (!eqSel || !svSel || !eqSel.value || !svSel.value || qty < 1) return;

            const opt    = eqSel.options[eqSel.selectedIndex];
            const rate   = parseFloat(opt.dataset.rate || '0');
            const name   = opt.dataset.name || opt.textContent.trim();
            const svName = svSel.options[svSel.selectedIndex].textContent.trim();
            const sub    = rate * qty;

            total += sub;
            lines.push(`• ${qty} × ${escapeHtml(name)} — ${escapeHtml(svName)} = <strong>KES ${sub.toLocaleString('en-KE', {minimumFractionDigits: 2})}</strong>`);
        });

        if (!lines.length) {
            aiMessages.innerHTML = `
                <div class="ai-message">
                    <div class="avatar ai">AI</div>
                    <div class="bubble">Add at least one equipment + service to see an estimate.</div>
                </div>`;
            return;
        }

        aiMessages.innerHTML = `
            <div class="ai-message">
                <div class="avatar ai">AI</div>
                <div class="bubble">
                    ${lines.join('<br>')}
                    <br><br>
                    Estimated total: <strong>KES ${total.toLocaleString('en-KE', {minimumFractionDigits: 2})}</strong>
                    <br><small>(base rates only — final quote confirmed by the lab)</small>
                </div>
            </div>`;
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (m) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[m]));
    }

    /* =====================================================
       MULTI-ITEM ROW BUILDER
    ====================================================== */
           function createItemRow() {
        const row = document.createElement('div');
        row.className = 'quote-item';

        const eqOptions = EQUIPMENT_TYPES.map((e) => {
            return `<option value="${e.id}" data-rate="${e.rate}" data-name="${escapeHtml(e.name)}">${escapeHtml(e.name)}</option>`;
        }).join('');

        const svOptions = SERVICE_TYPES.map((s) => (
            `<option value="${s.id}">${escapeHtml(s.serv_name)} (${escapeHtml(s.serv_code)})</option>`
        )).join('');

        row.innerHTML = `
            <!-- Equipment Type -->
            <div class="item-group">
                <label>Equipment Type <span style="color:red">*</span></label>
                <select class="item-equipment" required>
                    <option value="">Select type…</option>
                    ${eqOptions}
                </select>
            </div>

            <!-- Equipment Name -->
            <div class="item-group">
                <label>Equipment Name <span style="color:red">*</span></label>
                <input type="text" class="item-equipment-name" placeholder="e.g. 3-Phase Meter" required>
            </div>

            <!-- Serial Number -->
            <div class="item-group">
                <label>Serial Number <span style="color:red">*</span></label>
                <input type="text" class="item-serial" placeholder="e.g. SN-123456" required>
            </div>

            <!-- Manufacturer -->
            <div class="item-group">
                <label>Manufacturer</label>
                <input type="text" class="item-manufacturer" placeholder="e.g. Conlog">
            </div>

            <!-- Model -->
            <div class="item-group">
                <label>Model</label>
                <input type="text" class="item-model" placeholder="e.g. TC Conlog">
            </div>

            <!-- Rating -->
            <div class="item-group">
                <label>Rating</label>
                <input type="text" class="item-rating" placeholder="e.g. 230V 10-100A">
            </div>

            <!-- Service Type -->
            <div class="item-group">
                <label>Service Type <span style="color:red">*</span></label>
                <select class="item-service" required>
                    <option value="">Select service…</option>
                    ${svOptions}
                </select>
            </div>

            <!-- Quantity -->
            <div class="item-group">
                <label>Quantity <span style="color:red">*</span></label>
                <input type="number" class="item-qty" min="1" step="1" value="1" required>
            </div>

            <!-- Remove Button -->
            <div class="item-remove-wrapper">
                <button type="button" class="remove-item" aria-label="Remove item" title="Remove this equipment">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        row.querySelector('.remove-item').addEventListener('click', () => {
            if (itemsContainer.children.length > 1) {
                row.remove();
                updateQuoteEstimate();
            }
        });

        row.querySelector('.item-equipment').addEventListener('change', (e) => {
            const opt = e.target.options[e.target.selectedIndex];
            const nameInput = row.querySelector('.item-equipment-name');
            if (nameInput && !nameInput.value.trim()) {
                nameInput.value = opt?.dataset.name || '';
            }
            updateQuoteEstimate();
        });

        row.querySelector('.item-service').addEventListener('change', updateQuoteEstimate);
        row.querySelector('.item-qty').addEventListener('input', updateQuoteEstimate);

        return row;
    }

    function addItemRow() {
        if (!itemsContainer) return;
        itemsContainer.appendChild(createItemRow());
        updateQuoteEstimate();
    }

    if (itemsContainer) {
        addItemRow();
        addBtn?.addEventListener('click', addItemRow);
    }

    /* =====================================================
       SUBMIT
    ====================================================== */
    if (!quoteForm) return;

    quoteForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!quoteForm.checkValidity()) {
            quoteForm.reportValidity();
            return;
        }

        if (!itemsContainer) {
            alert('The quote form is not fully loaded. Please refresh and try again.');
            return;
        }

        const items = [];
        itemsContainer.querySelectorAll('.quote-item').forEach((row) => {
            const eqId   = row.querySelector('.item-equipment').value;
            const svId   = row.querySelector('.item-service').value;
            const qty    = parseInt(row.querySelector('.item-qty').value || '1', 10);
            const name   = row.querySelector('.item-equipment-name').value.trim();
            const serial = row.querySelector('.item-serial').value.trim();
            const manuf  = row.querySelector('.item-manufacturer').value.trim();
            const model  = row.querySelector('.item-model').value.trim();
            const rating = row.querySelector('.item-rating').value.trim();

            if (eqId && svId && qty >= 1 && name && serial) {
                items.push({
                    equipment_type_id: parseInt(eqId, 10),
                    service_type_id:   parseInt(svId, 10),
                    quantity:          qty,
                    equipment_name:    name,
                    serial_number:     serial,
                    manufacturer:      manuf || null,
                    model:             model || null,
                    rating:            rating || null,
                });
            }
        });

        if (!items.length) {
            alert('Please fill in at least one equipment row (equipment type, name, serial, service, quantity).');
            return;
        }

        const payload = {
            name:         document.getElementById('quoteName').value.trim(),
            email:        document.getElementById('quoteEmail').value.trim(),
            phone:        document.getElementById('quotePhone').value.trim(),
            company:      document.getElementById('quoteCompany').value.trim() || null,
            requirements: document.getElementById('quoteRequirements').value.trim() || null,
            items:        items,
        };

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Submitting…</span>`;
        }

        try {
            const response = await fetch("<?php echo e(route('public.quote-request.store')); ?>", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            let data = {};
            try { data = await response.json(); } catch (_) {}

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    throw new Error(Object.values(data.errors).flat().join('\n'));
                }
                throw new Error(data.message || 'Unable to submit quotation request.');
            }

            alert(
                (data.message || 'Quotation request submitted successfully.') +
                (data.reference_number ? '\n\nReference: ' + data.reference_number : '')
            );

            quoteForm.reset();
            itemsContainer.innerHTML = '';
            addItemRow();
            window.closeQuoteModal();

        } catch (error) {
            console.error('Quote request error:', error);
            alert(error.message || 'Unable to submit quotation request.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = `<i class="fas fa-file-invoice-dollar"></i><span>Submit Quote Request</span>`;
            }
        }
    });

    /* =====================================================
       MOBILE MENU
    ====================================================== */
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    if (menuToggle && mobileMenu) {
        const icon = menuToggle.querySelector('i');

        function setMenuState(open) {
            mobileMenu.classList.toggle('open', open);
            document.body.classList.toggle('menu-open', open);
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuToggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');

            if (icon) {
                icon.classList.toggle('fa-bars', !open);
                icon.classList.toggle('fa-xmark', open);
            }
        }

        menuToggle.addEventListener('click', () => {
            setMenuState(!mobileMenu.classList.contains('open'));
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMenuState(false));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setMenuState(false);
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 1360) setMenuState(false);
        });
    }

});
</script>

</body>
</html>
<?php /**PATH G:\project\ncckplclims\resources\views/welcome.blade.php ENDPATH**/ ?>