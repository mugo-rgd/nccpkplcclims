@extends('layouts.app')

@section('title', 'Quality Reports')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Quick Statistics
    |--------------------------------------------------------------------------
    |
    | Kept here to preserve your current functionality.
    | Ideally, these should later be moved to the controller.
    |
    */

    $totalObjectives =
        \App\Models\QualityObjective::count();

    $activeObjectives =
        \App\Models\QualityObjective::where(
            'status',
            'active'
        )->count();

    $totalCARs =
        \App\Models\CorrectiveAction::count();

    $openCARs =
        \App\Models\CorrectiveAction::whereIn(
            'status',
            [
                'Open',
                'In Progress',
            ]
        )->count();

    $achievements =
        \App\Models\QuarterlyAchievement::count();
@endphp


<div class="container-fluid quality-reports-page">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER                                               --}}
    {{-- ========================================================= --}}

    <div
        class="
            d-flex
            justify-content-between
            align-items-start
            flex-wrap
            gap-3
            mb-4
        "
    >

        <div class="d-flex align-items-center gap-3">

            <div class="page-title-icon">
                <i class="fas fa-file-alt"></i>
            </div>

            <div>

                <h1 class="page-title mb-1">
                    Quality Reports
                </h1>

                <p class="page-subtitle mb-0">
                    Generate, review and access laboratory quality management reports.
                </p>

            </div>

        </div>


        @if($currentFyr)

            <span class="current-period-badge">

                <i class="fas fa-calendar-alt me-1"></i>

                {{ $currentFyr->display_name }}

                @if($currentFyr->is_active)
                    <span class="ms-1">
                        Current
                    </span>
                @endif

            </span>

        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- QUICK STATS                                               --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">

        {{-- Total Objectives --}}
        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat-card stat-primary h-100">

                <div class="stat-card-content">

                    <div>

                        <div class="stat-label">
                            Total Objectives
                        </div>

                        <div class="stat-value">
                            {{ $totalObjectives }}
                        </div>

                        <div class="stat-note">
                            All quality objectives
                        </div>

                    </div>


                    <div class="stat-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Active Objectives --}}
        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat-card stat-success h-100">

                <div class="stat-card-content">

                    <div>

                        <div class="stat-label">
                            Active Objectives
                        </div>

                        <div class="stat-value">
                            {{ $activeObjectives }}
                        </div>

                        <div class="stat-note">
                            Currently active
                        </div>

                    </div>


                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Total CARs --}}
        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat-card stat-warning h-100">

                <div class="stat-card-content">

                    <div>

                        <div class="stat-label">
                            Total CARs
                        </div>

                        <div class="stat-value">
                            {{ $totalCARs }}
                        </div>

                        <div class="stat-note">
                            Corrective actions raised
                        </div>

                    </div>


                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Open CARs --}}
        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat-card stat-danger h-100">

                <div class="stat-card-content">

                    <div>

                        <div class="stat-label">
                            Open CARs
                        </div>

                        <div class="stat-value">
                            {{ $openCARs }}
                        </div>

                        <div class="stat-note">
                            Open or in progress
                        </div>

                    </div>


                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Achievements --}}
        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat-card stat-info h-100">

                <div class="stat-card-content">

                    <div>

                        <div class="stat-label">
                            Achievements
                        </div>

                        <div class="stat-value">
                            {{ $achievements }}
                        </div>

                        <div class="stat-note">
                            Performance entries recorded
                        </div>

                    </div>


                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- REPORT CONTENT                                            --}}
    {{-- ========================================================= --}}

    <div class="row g-4">

        {{-- ===================================================== --}}
        {{-- GENERATE REPORT                                      --}}
        {{-- ===================================================== --}}

        <div class="col-xl-7 col-lg-7">

            <div class="card report-card h-100">

                <div class="card-header report-card-header">

                    <div>

                        <h5 class="section-title mb-1">

                            <i class="fas fa-file-export text-primary me-2"></i>

                            Generate Quality Report

                        </h5>

                        <small class="text-muted">
                            Select the reporting period and filters for the report.
                        </small>

                    </div>

                </div>


                <div class="card-body">

                    <form
                        action="{{ route(
                            'quality.reports.generate'
                        ) }}"
                        method="POST"
                        target="_blank"
                    >

                        @csrf


                        {{-- ========================================= --}}
                        {{-- FYR                                      --}}
                        {{-- ========================================= --}}

                        <div class="report-form-group">

                            <label
                                for="fyr"
                                class="report-label"
                            >
                                Financial Year / Reporting Period

                                <span class="text-danger">
                                    *
                                </span>
                            </label>


                            <select
                                name="fyr"
                                id="fyr"
                                class="form-select report-select"
                                required
                            >

                                @foreach($fyrPeriods as $fyr)

                                    <option
                                        value="{{ $fyr->fyr }}"
                                        @selected(
                                            $currentFyr
                                            && $currentFyr->fyr == $fyr->fyr
                                        )
                                    >

                                        {{ $fyr->display_name }}

                                        @if($fyr->is_active)
                                            — Current
                                        @endif

                                    </option>

                                @endforeach

                            </select>


                            <small class="form-help">
                                Select the reporting period to include in the report.
                            </small>

                        </div>


                        {{-- ========================================= --}}
                        {{-- SECTION                                  --}}
                        {{-- ========================================= --}}

                        <div class="report-form-group">

                            <label
                                for="section"
                                class="report-label"
                            >
                                Section
                            </label>


                            <select
                                name="section"
                                id="section"
                                class="form-select report-select"
                            >

                                @foreach($sections as $section)

                                    <option
                                        value="{{ $section }}"
                                    >
                                        {{ $section }}
                                    </option>

                                @endforeach

                            </select>


                            <small class="form-help">
                                Choose the laboratory section to include.
                            </small>

                        </div>


                        {{-- ========================================= --}}
                        {{-- STATUS                                   --}}
                        {{-- ========================================= --}}

                        <div class="report-form-group">

                            <label
                                for="status"
                                class="report-label"
                            >
                                Status Filter
                            </label>


                            <select
                                name="status"
                                id="status"
                                class="form-select report-select"
                            >

                                @foreach($statuses as $status)

                                    <option
                                        value="{{ $status }}"
                                    >
                                        {{ $status }}
                                    </option>

                                @endforeach

                            </select>


                            <small class="form-help">
                                Filter the report by the selected quality status.
                            </small>

                        </div>


                        {{-- ========================================= --}}
                        {{-- FORMAT                                   --}}
                        {{-- ========================================= --}}

                        <div class="report-form-group mb-4">

                            <label class="report-label">
                                Report Format
                            </label>


                            <div class="report-format-grid">

                                <label class="report-format-option">

                                    <input
                                        type="radio"
                                        name="format"
                                        value="html"
                                        checked
                                    >

                                    <span class="format-icon format-html">
                                        <i class="fas fa-desktop"></i>
                                    </span>

                                    <span class="format-content">

                                        <strong>
                                            View Online
                                        </strong>

                                        <small>
                                            Open the report in your browser.
                                        </small>

                                    </span>

                                </label>


                                <label class="report-format-option">

                                    <input
                                        type="radio"
                                        name="format"
                                        value="pdf"
                                    >

                                    <span class="format-icon format-pdf">
                                        <i class="fas fa-file-pdf"></i>
                                    </span>

                                    <span class="format-content">

                                        <strong>
                                            PDF
                                        </strong>

                                        <small>
                                            Generate a printable PDF report.
                                        </small>

                                    </span>

                                </label>

                            </div>

                        </div>


                        {{-- ========================================= --}}
                        {{-- SUBMIT                                   --}}
                        {{-- ========================================= --}}

                        <button
                            type="submit"
                            class="btn btn-primary generate-report-btn"
                        >

                            <i class="fas fa-file-export me-2"></i>

                            Generate Report

                        </button>

                    </form>

                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- REPORT SHORTCUTS                                     --}}
        {{-- ===================================================== --}}

        <div class="col-xl-5 col-lg-5">

            <div class="card report-card h-100">

                <div class="card-header report-card-header">

                    <div>

                        <h5 class="section-title mb-1">

                            <i class="fas fa-layer-group text-info me-2"></i>

                            Report Shortcuts

                        </h5>

                        <small class="text-muted">
                            Quickly access common quality management reports.
                        </small>

                    </div>

                </div>


                <div class="card-body p-0">

                    {{-- Objectives --}}
                    <a
                        href="{{ route(
                            'quality.objectives.index'
                        ) }}"
                        class="report-link-card"
                    >

                        <span class="report-link-icon link-primary">
                            <i class="fas fa-bullseye"></i>
                        </span>


                        <span class="report-link-content">

                            <strong>
                                Objectives Summary
                            </strong>

                            <small>
                                Review all laboratory quality objectives.
                            </small>

                        </span>


                        <span class="report-link-action">

                            View

                            <i class="fas fa-chevron-right"></i>

                        </span>

                    </a>


                    {{-- Achievements --}}
                    <a
                        href="{{ route(
                            'quality.achievements.index'
                        ) }}"
                        class="report-link-card"
                    >

                        <span class="report-link-icon link-success">
                            <i class="fas fa-chart-line"></i>
                        </span>


                        <span class="report-link-content">

                            <strong>
                                Achievements Report
                            </strong>

                            <small>
                                Track actual performance against targets.
                            </small>

                        </span>


                        <span class="report-link-action">

                            View

                            <i class="fas fa-chevron-right"></i>

                        </span>

                    </a>


                    {{-- Corrective Actions --}}
                    <a
                        href="{{ route(
                            'quality.corrective-actions.index'
                        ) }}"
                        class="report-link-card"
                    >

                        <span class="report-link-icon link-warning">
                            <i class="fas fa-tools"></i>
                        </span>


                        <span class="report-link-content">

                            <strong>
                                Corrective Actions Status
                            </strong>

                            <small>
                                Review corrective actions and their current status.
                            </small>

                        </span>


                        <span class="report-link-action">

                            View

                            <i class="fas fa-chevron-right"></i>

                        </span>

                    </a>


                    {{-- Optional Quick PDF --}}
                    @if(
                        Route::has(
                            'quality.reports.quick-pdf'
                        )
                        )

                       @can('generate_quality_reports')

                            <a
                                href="{{ route('quality.reports.quick-pdf') }}"
                                class="report-link-card"
                                target="_blank"
                            >

                            <span class="report-link-icon link-danger">
                                <i class="fas fa-file-pdf"></i>
                            </span>


                            <span class="report-link-content">

                                <strong>
                                    Full Quality Report
                                </strong>

                                <small>
                                    Generate the complete report for the current FYR.
                                </small>

                            </span>


                            <span class="report-link-action">

                                PDF

                                <i class="fas fa-external-link-alt"></i>

                            </span>

                         </a>
                        @endcan

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- REPORT INFORMATION                                        --}}
    {{-- ========================================================= --}}

    <div class="card report-info-card mt-4 mb-4">

        <div class="card-body">

            <div class="d-flex align-items-start gap-3">

                <div class="report-info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>


                <div>

                    <h6 class="mb-1">
                        About Quality Reports
                    </h6>

                    <p class="mb-0">

                        Reports provide a consolidated view of quality objectives,
                        recorded achievements and corrective actions for the
                        selected reporting period.

                        PDF reports can be used for printing, management review
                        and quality documentation.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    /* =========================================================
       PAGE
    ========================================================= */

    .quality-reports-page {
        padding-bottom: 2rem;
    }


    .page-title-icon {
        width: 52px;
        height: 52px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 52px;

        border-radius: 14px;

        color: #ffffff;

        background:
            linear-gradient(
                135deg,
                #0d6efd,
                #5138ee
            );

        font-size: 1.2rem;

        box-shadow:
            0 8px 20px
            rgba(13, 110, 253, 0.18);
    }


    .page-title {
        color: #172033;

        font-size: 1.65rem;
        font-weight: 800;
    }


    .page-subtitle {
        color: #6b7280;

        font-size: 0.88rem;
    }


    .current-period-badge {
        display: inline-flex;
        align-items: center;

        padding: 0.55rem 0.8rem;

        border-radius: 10px;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 0.75rem;
        font-weight: 700;
    }


    /* =========================================================
       STAT CARDS
    ========================================================= */

    .report-stat-card {
        position: relative;

        min-height: 125px;

        padding: 1rem;

        border: 1px solid #edf0f5;

        border-radius: 14px;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);

        overflow: hidden;
    }


    .report-stat-card::before {
        content: "";

        position: absolute;

        left: 0;
        top: 0;

        width: 4px;
        height: 100%;
    }


    .stat-primary::before {
        background: #0d6efd;
    }


    .stat-success::before {
        background: #198754;
    }


    .stat-warning::before {
        background: #ffc107;
    }


    .stat-danger::before {
        background: #dc3545;
    }


    .stat-info::before {
        background: #0dcaf0;
    }


    .stat-card-content {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 0.75rem;
    }


    .stat-label {
        color: #64748b;

        font-size: 0.68rem;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.03em;
    }


    .stat-value {
        margin-top: 0.25rem;

        color: #172033;

        font-size: 1.75rem;
        font-weight: 800;

        line-height: 1.1;
    }


    .stat-note {
        margin-top: 0.4rem;

        color: #94a3b8;

        font-size: 0.65rem;
    }


    .stat-icon {
        width: 42px;
        height: 42px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 42px;

        border-radius: 11px;

        font-size: 0.95rem;
    }


    .stat-primary .stat-icon {
        color: #0d6efd;
        background: #edf4ff;
    }


    .stat-success .stat-icon {
        color: #198754;
        background: #e9f8ef;
    }


    .stat-warning .stat-icon {
        color: #a87300;
        background: #fff5d6;
    }


    .stat-danger .stat-icon {
        color: #dc3545;
        background: #fdecec;
    }


    .stat-info .stat-icon {
        color: #087990;
        background: #e7f7fa;
    }


    /* =========================================================
       GENERAL REPORT CARD
    ========================================================= */

    .report-card {
        border: 1px solid #edf0f5;

        border-radius: 16px;

        overflow: hidden;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);
    }


    .report-card-header {
        min-height: 68px;

        display: flex;
        align-items: center;

        padding: 0.9rem 1.2rem;

        border-bottom: 1px solid #eef1f5;

        background: #ffffff;
    }


    .section-title {
        color: #172033;

        font-size: 0.98rem;
        font-weight: 800;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .report-card .card-body {
        padding: 1.2rem;
    }


    .report-form-group {
        margin-bottom: 1rem;
    }


    .report-label {
        display: block;

        margin-bottom: 0.4rem;

        color: #475569;

        font-size: 0.76rem;
        font-weight: 700;
    }


    .report-select {
        min-height: 43px;

        border-color: #dce4ee;

        border-radius: 9px;

        background: #f8fafc;

        font-size: 0.8rem;
    }


    .report-select:focus {
        border-color: #0d6efd;

        background: #ffffff;

        box-shadow:
            0 0 0
            0.18rem
            rgba(13, 110, 253, 0.08);
    }


    .form-help {
        display: block;

        margin-top: 0.35rem;

        color: #94a3b8;

        font-size: 0.66rem;
    }


    /* =========================================================
       FORMAT
    ========================================================= */

    .report-format-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 0.75rem;
    }


    .report-format-option {
        position: relative;

        display: flex;
        align-items: center;

        gap: 0.75rem;

        padding: 0.85rem;

        border: 1px solid #dce4ee;

        border-radius: 11px;

        background: #fafbfd;

        cursor: pointer;

        transition:
            border-color 0.15s ease,
            background 0.15s ease;
    }


    .report-format-option:hover {
        border-color: #adc9eb;

        background: #f5f9ff;
    }


    .report-format-option input {
        position: absolute;

        top: 0.6rem;
        right: 0.6rem;
    }


    .format-icon {
        width: 40px;
        height: 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 40px;

        border-radius: 10px;

        font-size: 0.9rem;
    }


    .format-html {
        color: #0d6efd;
        background: #edf4ff;
    }


    .format-pdf {
        color: #dc3545;
        background: #fdecec;
    }


    .format-content strong {
        display: block;

        color: #172033;

        font-size: 0.78rem;
    }


    .format-content small {
        display: block;

        margin-top: 0.15rem;

        color: #64748b;

        font-size: 0.64rem;
    }


    .generate-report-btn {
        width: 100%;

        min-height: 44px;

        border-radius: 9px;

        font-size: 0.82rem;
        font-weight: 700;
    }


    /* =========================================================
       REPORT LINKS
    ========================================================= */

    .report-link-card {
        display: flex;
        align-items: center;

        gap: 0.8rem;

        min-height: 86px;

        padding: 0.9rem 1.1rem;

        border-bottom: 1px solid #eef2f7;

        color: #172033;

        text-decoration: none;

        transition:
            background 0.15s ease,
            transform 0.15s ease;
    }


    .report-link-card:last-child {
        border-bottom: none;
    }


    .report-link-card:hover {
        color: #172033;

        background: #fbfdff;
    }


    .report-link-icon {
        width: 42px;
        height: 42px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 42px;

        border-radius: 11px;

        font-size: 0.9rem;
    }


    .link-primary {
        color: #0d6efd;
        background: #edf4ff;
    }


    .link-success {
        color: #198754;
        background: #e9f8ef;
    }


    .link-warning {
        color: #a87300;
        background: #fff5d6;
    }


    .link-danger {
        color: #dc3545;
        background: #fdecec;
    }


    .report-link-content {
        flex: 1;
    }


    .report-link-content strong {
        display: block;

        margin-bottom: 0.15rem;

        font-size: 0.78rem;
    }


    .report-link-content small {
        display: block;

        color: #64748b;

        font-size: 0.66rem;

        line-height: 1.35;
    }


    .report-link-action {
        display: inline-flex;
        align-items: center;

        gap: 0.35rem;

        color: #0d6efd;

        font-size: 0.68rem;
        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       INFO
    ========================================================= */

    .report-info-card {
        border: 1px solid #dce9f7;

        border-radius: 14px;

        background: #f7fbff;
    }


    .report-info-icon {
        width: 40px;
        height: 40px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 40px;

        border-radius: 10px;

        color: #0d6efd;

        background: #e8f2ff;
    }


    .report-info-card h6 {
        color: #172033;

        font-weight: 800;
    }


    .report-info-card p {
        color: #64748b;

        font-size: 0.75rem;

        line-height: 1.55;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 767.98px) {

        .page-title {
            font-size: 1.35rem;
        }


        .page-title-icon {
            width: 46px;
            height: 46px;

            flex-basis: 46px;
        }


        .report-format-grid {
            grid-template-columns: 1fr;
        }


        .report-link-card {
            align-items: flex-start;
        }

    }

</style>

@endpush
