@extends('layouts.app')

@section('title', 'Quality Dashboard')

@section('content')

@php
    $overallAchievement =
        (float) ($summary['avg_achievement'] ?? 0);

    $completionRate =
        (float) ($summary['completion_rate'] ?? 0);

    $trackedObjectives =
        (int) ($summary['tracked'] ?? 0);

    $totalObjectives =
        (int) ($summary['total'] ?? 0);

    $onTrack =
        (int) ($summary['on_track'] ?? 0);

    $atRisk =
        (int) ($summary['at_risk'] ?? 0);

    $notMet =
        (int) ($summary['not_met'] ?? 0);

    $carTotal =
        (int) ($carStatus['total'] ?? 0);

    $carOpen =
        (int) ($carStatus['open'] ?? 0);

    $carClosed =
        (int) ($carStatus['closed'] ?? 0);

    $carOverdue =
        (int) ($carStatus['overdue'] ?? 0);

    $closureRate =
        (float) ($carStatus['closure_rate'] ?? 0);
@endphp


<div class="container-fluid quality-dashboard-page">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER                                               --}}
    {{-- ========================================================= --}}

    <div class="quality-page-header mb-4">

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

            <div>

                <div class="page-title-icon">
                    <i class="fas fa-award"></i>
                </div>

                <div>
                    <h1 class="quality-page-title mb-1">
                        Quality Management Dashboard
                    </h1>

                    <p class="quality-page-subtitle mb-0">
                        Monitor quality objectives, performance,
                        achievements and corrective actions.
                    </p>
                </div>

            </div>


            <div class="d-flex align-items-center flex-wrap gap-2">

                @if($currentFyr)

                    <span class="context-badge context-badge-primary">

                        <i class="fas fa-calendar-alt"></i>

                        <span>
                            {{ $currentFyr->display_name }}
                        </span>

                    </span>

                @endif


                @if($currentQuarter)

                    <span class="context-badge context-badge-success">

                        <i class="fas fa-clock"></i>

                        <span>
                            {{ $currentQuarter['label'] ?? 'N/A' }}
                        </span>

                    </span>

                @endif

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FYR FILTER                                                --}}
    {{-- ========================================================= --}}

    <div class="card quality-card filter-card mb-4">

        <div class="card-body">

            <div class="row align-items-end g-3">

                <div class="col-lg-5 col-md-7">

                    <label
                        for="fyrSelector"
                        class="form-label filter-label"
                    >
                        <i class="fas fa-calendar-check me-1"></i>
                        Financial Year / Reporting Period
                    </label>

                    <select
                        id="fyrSelector"
                        class="form-select quality-select"
                        onchange="loadDashboardData(this.value)"
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

                                @if($fyr->is_closed)
                                    — Closed
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-lg-7 col-md-5">

                    <div class="d-flex justify-content-md-end">

                        <button
                            type="button"
                            class="btn btn-outline-primary refresh-button"
                            onclick="refreshData()"
                        >
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh Dashboard
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- KPI CARDS                                                 --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">

        {{-- Overall Achievement --}}
        <div class="col-xl-3 col-md-6">

            <div class="quality-kpi-card quality-kpi-primary h-100">

                <div class="kpi-card-content">

                    <div>

                        <div class="kpi-label">
                            Overall Achievement
                        </div>

                        <div class="kpi-value">
                            {{ number_format($overallAchievement, 1) }}%
                        </div>

                        <div class="kpi-note">
                            {{ $trackedObjectives }}
                            of
                            {{ $totalObjectives }}
                            objectives tracked
                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>

                </div>


                <div class="kpi-progress">

                    <div
                        class="kpi-progress-bar"
                        style="width: {{ min($completionRate, 100) }}%;"
                    ></div>

                </div>

                <small class="kpi-progress-label">
                    {{ number_format($completionRate, 1) }}%
                    reporting completion
                </small>

            </div>

        </div>


        {{-- On Track --}}
        <div class="col-xl-3 col-md-6">

            <div class="quality-kpi-card quality-kpi-success h-100">

                <div class="kpi-card-content">

                    <div>

                        <div class="kpi-label">
                            On Track
                        </div>

                        <div class="kpi-value">
                            {{ $onTrack }}
                        </div>

                        <div class="kpi-note">
                            Objectives meeting expected performance
                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- At Risk --}}
        <div class="col-xl-3 col-md-6">

            <div class="quality-kpi-card quality-kpi-warning h-100">

                <div class="kpi-card-content">

                    <div>

                        <div class="kpi-label">
                            At Risk
                        </div>

                        <div class="kpi-value">
                            {{ $atRisk }}
                        </div>

                        <div class="kpi-note">
                            Objectives requiring closer monitoring
                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Not Met --}}
        <div class="col-xl-3 col-md-6">

            <div class="quality-kpi-card quality-kpi-danger h-100">

                <div class="kpi-card-content">

                    <div>

                        <div class="kpi-label">
                            Not Met
                        </div>

                        <div class="kpi-value">
                            {{ $notMet }}
                        </div>

                        <div class="kpi-note">
                            Objectives below the required target
                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- CHARTS                                                    --}}
    {{-- ========================================================= --}}

    <div class="row g-4 mb-4">

        {{-- Trend Analysis --}}
        <div class="col-xl-8 col-lg-7">

            <div class="card quality-card h-100">

                <div class="card-header quality-card-header">

                    <div>

                        <h5 class="quality-card-title mb-1">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Trend Analysis
                        </h5>

                        <small class="text-muted">
                            Performance trend against the expected target.
                        </small>

                    </div>


                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        onclick="refreshChart()"
                        title="Refresh chart"
                    >
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>

                </div>


                <div class="card-body">

                    <div class="chart-container chart-container-large">
                        <canvas id="trendChart"></canvas>
                    </div>

                </div>

            </div>

        </div>


        {{-- Section Performance --}}
        <div class="col-xl-4 col-lg-5">

            <div class="card quality-card h-100">

                <div class="card-header quality-card-header">

                    <div>

                        <h5 class="quality-card-title mb-1">
                            <i class="fas fa-chart-pie text-info me-2"></i>
                            Section Performance
                        </h5>

                        <small class="text-muted">
                            Average performance by quality section.
                        </small>

                    </div>

                </div>


                <div class="card-body">

                    <div class="chart-container chart-container-small">
                        <canvas id="sectionChart"></canvas>
                    </div>


                    @if(!empty($sectionPerformance))

                        <div class="section-legend mt-3">

                            @foreach($sectionPerformance as $section => $data)

                                <span class="section-legend-item">

                                    <i
                                        class="fas fa-circle"
                                        style="
                                            color:
                                            @if($section == 'MCL')
                                                #4e73df
                                            @elseif($section == 'Instruments')
                                                #1cc88a
                                            @else
                                                #f6c23e
                                            @endif
                                        "
                                    ></i>

                                    {{ $section }}

                                </span>

                            @endforeach

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- AT-RISK OBJECTIVES                                        --}}
    {{-- ========================================================= --}}

    <div class="card quality-card mb-4">

        <div class="card-header quality-card-header">

            <div>

                <h5 class="quality-card-title mb-1">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Objectives Requiring Attention
                </h5>

                <small class="text-muted">
                    Review objectives that are at risk or have not met their targets.
                </small>

            </div>


            @if($atRiskObjectives->count() > 0)

                <span class="attention-badge">

                    <i class="fas fa-bell me-1"></i>

                    {{ $atRiskObjectives->count() }}
                    {{ $atRiskObjectives->count() === 1 ? 'item' : 'items' }}

                </span>

            @endif

        </div>


        <div class="card-body p-0">

            @if($atRiskObjectives->count() > 0)

                <div class="table-responsive">

                    <table class="table quality-table align-middle mb-0">

                        <thead>

                            <tr>
                                <th>Objective</th>
                                <th>Description</th>
                                <th>Section</th>
                                <th>Target</th>
                                <th>Actual</th>
                                <th style="min-width:180px;">
                                    Achievement
                                </th>
                                <th>Status</th>
                                <th>Owner</th>
                                <th class="text-end">
                                    Actions
                                </th>
                            </tr>

                        </thead>


                        <tbody>

                            @foreach($atRiskObjectives as $objective)

                                @php
                                    $objectivePercentage =
                                        (float) (
                                            $objective['percentage']
                                            ?? 0
                                        );

                                    $isAtRisk =
                                        ($objective['status'] ?? '')
                                        === 'At Risk';
                                @endphp

                                <tr>

                                    <td>

                                        <span class="objective-code">
                                            {{ $objective['objective_id'] }}
                                        </span>

                                    </td>


                                    <td>

                                        <div class="objective-description">
                                            {{
                                                Str::limit(
                                                    $objective['description'],
                                                    65
                                                )
                                            }}
                                        </div>

                                    </td>


                                    <td>

                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">

                                            {{ $objective['section'] }}

                                        </span>

                                    </td>


                                    <td class="fw-semibold">
                                        {{ $objective['target'] }}
                                    </td>


                                    <td class="fw-semibold">
                                        {{ $objective['actual'] ?? 'N/A' }}
                                    </td>


                                    <td>

                                        <div class="d-flex justify-content-between mb-1">

                                            <small class="text-muted">
                                                Progress
                                            </small>

                                            <small class="fw-bold">
                                                {{ $objectivePercentage }}%
                                            </small>

                                        </div>

                                        <div class="progress achievement-progress">

                                            <div
                                                class="
                                                    progress-bar
                                                    {{
                                                        $isAtRisk
                                                            ? 'bg-warning'
                                                            : 'bg-danger'
                                                    }}
                                                "
                                                role="progressbar"
                                                style="
                                                    width:
                                                    {{ min($objectivePercentage, 100) }}%;
                                                "
                                                aria-valuenow="{{ $objectivePercentage }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            ></div>

                                        </div>

                                    </td>


                                    <td>

                                        @if($isAtRisk)

                                            <span class="status-pill status-pill-warning">

                                                <i class="fas fa-exclamation-circle"></i>

                                                At Risk

                                            </span>

                                        @else

                                            <span class="status-pill status-pill-danger">

                                                <i class="fas fa-times-circle"></i>

                                                Not Met

                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        <div class="owner-cell">

                                            <span class="owner-avatar">
                                                <i class="fas fa-user"></i>
                                            </span>

                                            <span>
                                                {{ $objective['owner'] }}
                                            </span>

                                        </div>

                                    </td>


                                    <td class="text-end">

                                        <div class="d-inline-flex gap-1">

                                            @can('create_corrective_actions')

                                                <a
                                                    href="{{ route(
                                                        'quality.corrective-actions.create',
                                                        [
                                                            'objective_id' =>
                                                                $objective['id'],
                                                        ]
                                                    ) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Create corrective action"
                                                >
                                                    <i class="fas fa-plus"></i>
                                                    CAR
                                                </a>

                                            @endcan


                                            @can('view_quality_objectives')

                                                <a
                                                    href="{{ route(
                                                        'quality.objectives.show',
                                                        $objective['id']
                                                    ) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View objective"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                            @endcan

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="friendly-empty-state">

                    <div class="friendly-empty-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <h4>
                        Everything looks good
                    </h4>

                    <p>
                        There are currently no at-risk or unmet objectives
                        for the selected reporting period.
                    </p>

                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- CORRECTIVE ACTION STATUS                                  --}}
    {{-- ========================================================= --}}

    <div class="card quality-card mb-4">

        <div class="card-header quality-card-header">

            <div>

                <h5 class="quality-card-title mb-1">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    Corrective Action Status
                </h5>

                <small class="text-muted">
                    Current status of corrective action requests.
                </small>

            </div>

        </div>


        <div class="card-body">

            <div class="row g-3">

                {{-- Total --}}
                <div class="col-xl-3 col-md-6">

                    <div class="car-stat-card">

                        <div class="car-stat-icon car-stat-primary">
                            <i class="fas fa-clipboard-list"></i>
                        </div>

                        <div>

                            <div class="car-stat-label">
                                Total CARs
                            </div>

                            <div class="car-stat-value">
                                {{ $carTotal }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Open --}}
                <div class="col-xl-3 col-md-6">

                    <div class="car-stat-card">

                        <div class="car-stat-icon car-stat-warning">
                            <i class="fas fa-folder-open"></i>
                        </div>

                        <div>

                            <div class="car-stat-label">
                                Open
                            </div>

                            <div class="car-stat-value">
                                {{ $carOpen }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Closed --}}
                <div class="col-xl-3 col-md-6">

                    <div class="car-stat-card">

                        <div class="car-stat-icon car-stat-success">
                            <i class="fas fa-check"></i>
                        </div>

                        <div>

                            <div class="car-stat-label">
                                Closed
                            </div>

                            <div class="car-stat-value">
                                {{ $carClosed }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Overdue --}}
                <div class="col-xl-3 col-md-6">

                    <div class="car-stat-card">

                        <div class="car-stat-icon car-stat-danger">
                            <i class="fas fa-clock"></i>
                        </div>

                        <div>

                            <div class="car-stat-label">
                                Overdue
                            </div>

                            <div class="car-stat-value">
                                {{ $carOverdue }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="closure-progress-section mt-4">

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <div>

                        <strong>
                            CAR Closure Rate
                        </strong>

                        <small class="text-muted d-block">
                            Percentage of corrective actions successfully closed.
                        </small>

                    </div>

                    <span class="closure-rate-value">
                        {{ number_format($closureRate, 1) }}%
                    </span>

                </div>


                <div class="progress closure-progress">

                    <div
                        class="progress-bar bg-success"
                        role="progressbar"
                        style="
                            width:
                            {{ min($closureRate, 100) }}%;
                        "
                        aria-valuenow="{{ $closureRate }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    ></div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- QUICK ACTIONS                                             --}}
    {{-- ========================================================= --}}

    <div class="card quality-card mb-4">

        <div class="card-header quality-card-header">

            <div>

                <h5 class="quality-card-title mb-1">
                    <i class="fas fa-bolt text-warning me-2"></i>
                    Quick Actions
                </h5>

                <small class="text-muted">
                    Common quality management tasks.
                </small>

            </div>

        </div>


        <div class="card-body">

            <div class="row g-3">

                {{-- New Objective --}}
                @can('create_quality_objectives')

                    <div class="col-xl-3 col-md-6">

                        <a
                            href="{{ route(
                                'quality.objectives.create'
                            ) }}"
                            class="quick-action-card quick-action-primary"
                        >

                            <span class="quick-action-icon">
                                <i class="fas fa-bullseye"></i>
                            </span>

                            <span>

                                <strong>
                                    New Objective
                                </strong>

                                <small>
                                    Create a new quality objective
                                </small>

                            </span>

                            <i class="fas fa-chevron-right quick-action-arrow"></i>

                        </a>

                    </div>

                @endcan


                {{-- Achievement --}}
                @can('create_achievements')

                    <div class="col-xl-3 col-md-6">

                        <a
                            href="{{ route(
                                'quality.achievements.create'
                            ) }}"
                            class="quick-action-card quick-action-success"
                        >

                            <span class="quick-action-icon">
                                <i class="fas fa-chart-line"></i>
                            </span>

                            <span>

                                <strong>
                                    Record Achievement
                                </strong>

                                <small>
                                    Capture objective performance
                                </small>

                            </span>

                            <i class="fas fa-chevron-right quick-action-arrow"></i>

                        </a>

                    </div>

                @endcan


                {{-- Corrective Action --}}
                @can('create_corrective_actions')

                    <div class="col-xl-3 col-md-6">

                        <a
                            href="{{ route(
                                'quality.corrective-actions.create'
                            ) }}"
                            class="quick-action-card quick-action-warning"
                        >

                            <span class="quick-action-icon">
                                <i class="fas fa-tools"></i>
                            </span>

                            <span>

                                <strong>
                                    Create CAR
                                </strong>

                                <small>
                                    Start a corrective action
                                </small>

                            </span>

                            <i class="fas fa-chevron-right quick-action-arrow"></i>

                        </a>

                    </div>

                @endcan


                {{-- Reports --}}
                @can('view_quality_reports')

                    <div class="col-xl-3 col-md-6">

                        <a
                            href="{{ route(
                                'quality.reports.index'
                            ) }}"
                            class="quick-action-card quick-action-info"
                        >

                            <span class="quick-action-icon">
                                <i class="fas fa-file-alt"></i>
                            </span>

                            <span>

                                <strong>
                                    Generate Report
                                </strong>

                                <small>
                                    View and export quality reports
                                </small>

                            </span>

                            <i class="fas fa-chevron-right quick-action-arrow"></i>

                        </a>

                    </div>

                @endcan

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

    .quality-dashboard-page {
        padding-bottom: 2rem;
    }


    .quality-page-header {
        padding: 0.25rem 0;
    }


    .quality-page-header > .d-flex > div:first-child {
        display: flex;
        align-items: center;
        gap: 1rem;
    }


    .page-title-icon {
        width: 54px;
        height: 54px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 54px;

        border-radius: 14px;

        color: #ffffff;

        background:
            linear-gradient(
                135deg,
                #0d6efd,
                #5138ee
            );

        font-size: 1.35rem;

        box-shadow:
            0 8px 20px
            rgba(13, 110, 253, 0.2);
    }


    .quality-page-title {
        color: #172033;

        font-size: 1.7rem;
        font-weight: 800;
    }


    .quality-page-subtitle {
        color: #6b7280;

        font-size: 0.95rem;
    }


    .context-badge {
        display: inline-flex;
        align-items: center;

        gap: 0.45rem;

        padding: 0.55rem 0.85rem;

        border-radius: 10px;

        font-size: 0.78rem;
        font-weight: 700;
    }


    .context-badge-primary {
        color: #0b5ed7;
        background: #e9f2ff;
    }


    .context-badge-success {
        color: #198754;
        background: #e8f7ef;
    }


    /* =========================================================
       GENERAL CARD
    ========================================================= */

    .quality-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;

        overflow: hidden;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);
    }


    .quality-card-header {
        min-height: 72px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 1rem;

        padding: 1rem 1.25rem;

        background: #ffffff;

        border-bottom: 1px solid #eef1f5;
    }


    .quality-card-title {
        color: #172033;

        font-size: 1rem;
        font-weight: 800;
    }


    /* =========================================================
       FILTER
    ========================================================= */

    .filter-card .card-body {
        padding: 1rem 1.25rem;
    }


    .filter-label {
        margin-bottom: 0.4rem;

        color: #374151;

        font-size: 0.82rem;
        font-weight: 700;
    }


    .quality-select {
        min-height: 44px;

        border-color: #d8e0ea;

        border-radius: 10px;

        background-color: #f8fafc;
    }


    .quality-select:focus {
        border-color: #0d6efd;

        box-shadow:
            0 0 0
            0.2rem
            rgba(13, 110, 253, 0.1);
    }


    .refresh-button {
        min-height: 44px;

        border-radius: 10px;

        font-weight: 600;
    }


    /* =========================================================
       KPI CARDS
    ========================================================= */

    .quality-kpi-card {
        position: relative;

        min-height: 170px;

        padding: 1.25rem;

        border: 1px solid #edf0f5;

        border-radius: 16px;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);

        overflow: hidden;
    }


    .quality-kpi-card::before {
        content: "";

        position: absolute;

        top: 0;
        left: 0;

        width: 4px;
        height: 100%;
    }


    .quality-kpi-primary::before {
        background: #0d6efd;
    }


    .quality-kpi-success::before {
        background: #198754;
    }


    .quality-kpi-warning::before {
        background: #ffc107;
    }


    .quality-kpi-danger::before {
        background: #dc3545;
    }


    .kpi-card-content {
        display: flex;

        align-items: flex-start;
        justify-content: space-between;

        gap: 1rem;
    }


    .kpi-label {
        margin-bottom: 0.4rem;

        color: #64748b;

        font-size: 0.76rem;
        font-weight: 800;

        letter-spacing: 0.03em;

        text-transform: uppercase;
    }


    .kpi-value {
        color: #172033;

        font-size: 2rem;
        font-weight: 800;

        line-height: 1.1;
    }


    .kpi-note {
        margin-top: 0.55rem;

        color: #94a3b8;

        font-size: 0.76rem;

        line-height: 1.4;
    }


    .kpi-icon {
        width: 48px;
        height: 48px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 48px;

        border-radius: 14px;

        font-size: 1.15rem;
    }


    .quality-kpi-primary .kpi-icon {
        color: #0d6efd;
        background: #eaf2ff;
    }


    .quality-kpi-success .kpi-icon {
        color: #198754;
        background: #e9f8ef;
    }


    .quality-kpi-warning .kpi-icon {
        color: #b88000;
        background: #fff6d8;
    }


    .quality-kpi-danger .kpi-icon {
        color: #dc3545;
        background: #fdecec;
    }


    .kpi-progress {
        height: 6px;

        margin-top: 1rem;

        border-radius: 999px;

        background: #edf2f7;

        overflow: hidden;
    }


    .kpi-progress-bar {
        height: 100%;

        border-radius: 999px;

        background: #0d6efd;
    }


    .kpi-progress-label {
        display: block;

        margin-top: 0.4rem;

        color: #64748b;

        font-size: 0.68rem;
    }


    /* =========================================================
       CHARTS
    ========================================================= */

    .chart-container {
        position: relative;

        width: 100%;
    }


    .chart-container-large {
        height: 310px;
    }


    .chart-container-small {
        height: 260px;
    }


    .section-legend {
        display: flex;

        justify-content: center;
        flex-wrap: wrap;

        gap: 0.75rem;
    }


    .section-legend-item {
        display: inline-flex;
        align-items: center;

        gap: 0.3rem;

        color: #64748b;

        font-size: 0.75rem;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .quality-table thead th {
        padding: 0.85rem 1rem;

        border-bottom: 1px solid #e5e7eb;

        color: #475569;

        background: #f8fafc;

        font-size: 0.72rem;
        font-weight: 800;

        text-transform: uppercase;

        white-space: nowrap;
    }


    .quality-table tbody td {
        padding: 0.9rem 1rem;

        color: #334155;

        border-color: #eef2f7;

        font-size: 0.82rem;
    }


    .quality-table tbody tr:hover {
        background: #fbfdff;
    }


    .objective-code {
        display: inline-flex;

        padding: 0.3rem 0.55rem;

        border-radius: 7px;

        color: #0d6efd;

        background: #edf4ff;

        font-weight: 800;
    }


    .objective-description {
        min-width: 180px;

        color: #334155;
    }


    .achievement-progress {
        height: 7px;

        border-radius: 999px;

        background: #edf2f7;
    }


    .achievement-progress .progress-bar {
        border-radius: 999px;
    }


    .status-pill {
        display: inline-flex;

        align-items: center;

        gap: 0.3rem;

        padding: 0.35rem 0.6rem;

        border-radius: 999px;

        font-size: 0.7rem;
        font-weight: 700;

        white-space: nowrap;
    }


    .status-pill-warning {
        color: #805b00;
        background: #fff3cd;
    }


    .status-pill-danger {
        color: #a9232f;
        background: #fce8ea;
    }


    .owner-cell {
        display: flex;

        align-items: center;

        gap: 0.4rem;

        min-width: 120px;
    }


    .owner-avatar {
        width: 28px;
        height: 28px;

        display: inline-flex;

        align-items: center;
        justify-content: center;

        flex: 0 0 28px;

        border-radius: 50%;

        color: #64748b;

        background: #edf2f7;

        font-size: 0.65rem;
    }


    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .friendly-empty-state {
        padding: 3rem 1rem;

        text-align: center;
    }


    .friendly-empty-icon {
        width: 64px;
        height: 64px;

        margin: 0 auto 1rem;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        color: #198754;

        background: #eaf8f0;

        font-size: 1.8rem;
    }


    .friendly-empty-state h4 {
        color: #172033;

        font-size: 1.05rem;
        font-weight: 800;
    }


    .friendly-empty-state p {
        max-width: 500px;

        margin: 0 auto;

        color: #64748b;

        font-size: 0.85rem;
    }


    /* =========================================================
       CAR STATUS
    ========================================================= */

    .car-stat-card {
        min-height: 95px;

        display: flex;

        align-items: center;

        gap: 1rem;

        padding: 1rem;

        border: 1px solid #edf0f5;

        border-radius: 12px;

        background: #fafbfd;
    }


    .car-stat-icon {
        width: 46px;
        height: 46px;

        display: flex;

        align-items: center;
        justify-content: center;

        flex: 0 0 46px;

        border-radius: 12px;

        font-size: 1rem;
    }


    .car-stat-primary {
        color: #0d6efd;
        background: #eaf2ff;
    }


    .car-stat-warning {
        color: #b88000;
        background: #fff6d8;
    }


    .car-stat-success {
        color: #198754;
        background: #e9f8ef;
    }


    .car-stat-danger {
        color: #dc3545;
        background: #fdecec;
    }


    .car-stat-label {
        color: #64748b;

        font-size: 0.75rem;
        font-weight: 700;
    }


    .car-stat-value {
        color: #172033;

        font-size: 1.65rem;
        font-weight: 800;
    }


    .closure-progress-section {
        padding-top: 1rem;

        border-top: 1px solid #eef2f7;
    }


    .closure-rate-value {
        color: #198754;

        font-size: 1.1rem;
        font-weight: 800;
    }


    .closure-progress {
        height: 8px;

        border-radius: 999px;
    }


    .closure-progress .progress-bar {
        border-radius: 999px;
    }


    /* =========================================================
       QUICK ACTIONS
    ========================================================= */

    .quick-action-card {
        min-height: 92px;

        display: flex;

        align-items: center;

        gap: 0.8rem;

        padding: 1rem;

        border: 1px solid #e6ebf1;

        border-radius: 12px;

        color: #172033;

        background: #ffffff;

        text-decoration: none;

        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease,
            border-color 0.18s ease;
    }


    .quick-action-card:hover {
        color: #172033;

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px
            rgba(15, 23, 42, 0.08);
    }


    .quick-action-card > span:nth-child(2) {
        flex: 1;
    }


    .quick-action-card strong {
        display: block;

        margin-bottom: 0.15rem;

        font-size: 0.84rem;
    }


    .quick-action-card small {
        display: block;

        color: #7b8797;

        font-size: 0.7rem;

        line-height: 1.35;
    }


    .quick-action-icon {
        width: 42px;
        height: 42px;

        display: inline-flex;

        align-items: center;
        justify-content: center;

        flex: 0 0 42px;

        border-radius: 11px;
    }


    .quick-action-primary .quick-action-icon {
        color: #0d6efd;

        background: #eaf2ff;
    }


    .quick-action-success .quick-action-icon {
        color: #198754;

        background: #e9f8ef;
    }


    .quick-action-warning .quick-action-icon {
        color: #b88000;

        background: #fff6d8;
    }


    .quick-action-info .quick-action-icon {
        color: #0aa2c0;

        background: #e6f7fa;
    }


    .quick-action-primary:hover {
        border-color: #b8d3fc;
    }


    .quick-action-success:hover {
        border-color: #b7dfc9;
    }


    .quick-action-warning:hover {
        border-color: #f4da83;
    }


    .quick-action-info:hover {
        border-color: #a9dfe8;
    }


    .quick-action-arrow {
        color: #a0aec0;

        font-size: 0.7rem;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 767.98px) {

        .quality-page-title {
            font-size: 1.35rem;
        }


        .page-title-icon {
            width: 46px;
            height: 46px;

            flex-basis: 46px;
        }


        .quality-card-header {
            align-items: flex-start;

            flex-direction: column;
        }


        .chart-container-large {
            height: 260px;
        }


        .chart-container-small {
            height: 240px;
        }

    }

</style>

@endpush


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    let trendChart = null;
    let sectionChart = null;


    document.addEventListener(
        'DOMContentLoaded',
        function () {
            initCharts();
        }
    );


    /* =========================================================
       INITIALIZE CHARTS
    ========================================================= */

    function initCharts() {

        /*
         * Trend Chart
         */

        const trendCanvas =
            document.getElementById(
                'trendChart'
            );

        if (trendCanvas) {

            const trendCtx =
                trendCanvas.getContext('2d');

            const trendData =
                @json($trendData);

            const labels =
                Object.keys(
                    trendData
                );

            const achievementData =
                labels.map(
                    key =>
                        trendData[key]
                            .avg_achievement
                        || 0
                );

            const targetData =
                labels.map(
                    key =>
                        trendData[key]
                            .target
                        || 95
                );


            trendChart =
                new Chart(
                    trendCtx,
                    {
                        type: 'line',

                        data: {
                            labels: labels,

                            datasets: [
                                {
                                    label:
                                        'Achievement %',

                                    data:
                                        achievementData,

                                    borderColor:
                                        'rgb(59, 130, 246)',

                                    backgroundColor:
                                        'rgba(59, 130, 246, 0.1)',

                                    tension:
                                        0.3,

                                    fill:
                                        true
                                },

                                {
                                    label:
                                        'Target (95%)',

                                    data:
                                        targetData,

                                    borderColor:
                                        'rgb(239, 68, 68)',

                                    borderDash:
                                        [5, 5],

                                    fill:
                                        false,

                                    borderWidth:
                                        2
                                }
                            ]
                        },

                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,

                            plugins: {

                                legend: {
                                    position:
                                        'top',
                                },

                                tooltip: {

                                    callbacks: {

                                        label:
                                            function (
                                                context
                                            ) {

                                                return (
                                                    context
                                                        .dataset
                                                        .label
                                                    + ': '
                                                    + context
                                                        .parsed
                                                        .y
                                                    + '%'
                                                );

                                            }

                                    }

                                }

                            },

                            scales: {

                                y: {

                                    beginAtZero:
                                        true,

                                    max:
                                        100,

                                    ticks: {

                                        callback:
                                            function (
                                                value
                                            ) {

                                                return (
                                                    value
                                                    + '%'
                                                );

                                            }

                                    }

                                }

                            }

                        }

                    }
                );

        }


        /*
         * Section Performance Chart
         */

        const sectionCanvas =
            document.getElementById(
                'sectionChart'
            );

        if (sectionCanvas) {

            const sectionCtx =
                sectionCanvas.getContext(
                    '2d'
                );

            const sectionData =
                @json($sectionPerformance);

            const sectionLabels =
                Object.keys(
                    sectionData
                );

            const sectionValues =
                sectionLabels.map(
                    key =>
                        sectionData[key]
                            .avg_achievement
                        || 0
                );

            const colors = [
                '#4e73df',
                '#1cc88a',
                '#f6c23e',
            ];


            sectionChart =
                new Chart(
                    sectionCtx,
                    {
                        type:
                            'doughnut',

                        data: {

                            labels:
                                sectionLabels,

                            datasets: [
                                {
                                    data:
                                        sectionValues,

                                    backgroundColor:
                                        colors.map(
                                            color =>
                                                color
                                                + '80'
                                        ),

                                    borderColor:
                                        colors,

                                    borderWidth:
                                        2
                                }
                            ]

                        },

                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,

                            plugins: {

                                legend: {
                                    position:
                                        'bottom',
                                },

                                tooltip: {

                                    callbacks: {

                                        label:
                                            function (
                                                context
                                            ) {

                                                const section =
                                                    context
                                                        .label;

                                                const data =
                                                    sectionData[
                                                        section
                                                    ];

                                                return [
                                                    section
                                                    + ': '
                                                    + context
                                                        .parsed
                                                    + '%',

                                                    'On Track: '
                                                    + data
                                                        .on_track,

                                                    'At Risk: '
                                                    + data
                                                        .at_risk,

                                                    'Not Met: '
                                                    + data
                                                        .not_met,
                                                ];

                                            }

                                    }

                                }

                            }

                        }

                    }
                );

        }

    }


    /* =========================================================
       LOAD DASHBOARD DATA
    ========================================================= */

    function loadDashboardData(fyr) {

        fetch(
            '{{ route("quality.dashboard.data") }}'
            + '?fyr='
            + encodeURIComponent(fyr)
        )
            .then(
                response => {

                    if (!response.ok) {
                        throw new Error(
                            'Unable to load dashboard data.'
                        );
                    }

                    return response.json();

                }
            )
            .then(
                data => {

                    updateSummary(
                        data.summary
                    );

                    updateCharts(
                        data.sections
                    );

                    updateAtRiskTable(
                        data.at_risk
                    );

                }
            )
            .catch(
                error => {

                    console.error(
                        'Error loading dashboard data:',
                        error
                    );

                    if (
                        typeof toastr
                        !== 'undefined'
                    ) {

                        toastr.error(
                            'Failed to load dashboard data'
                        );

                    }

                }
            );

    }


    /* =========================================================
       UPDATE SUMMARY
    ========================================================= */

    function updateSummary(summary) {

        const primaryCard =
            document.querySelector(
                '.quality-kpi-primary'
            );

        const successCard =
            document.querySelector(
                '.quality-kpi-success'
            );

        const warningCard =
            document.querySelector(
                '.quality-kpi-warning'
            );

        const dangerCard =
            document.querySelector(
                '.quality-kpi-danger'
            );


        if (primaryCard) {

            const value =
                primaryCard.querySelector(
                    '.kpi-value'
                );

            const note =
                primaryCard.querySelector(
                    '.kpi-note'
                );

            const progress =
                primaryCard.querySelector(
                    '.kpi-progress-bar'
                );

            const progressLabel =
                primaryCard.querySelector(
                    '.kpi-progress-label'
                );


            if (value) {

                value.textContent =
                    (
                        summary.avg_achievement
                        || 0
                    )
                    + '%';

            }


            if (note) {

                note.textContent =
                    (
                        summary.tracked
                        || 0
                    )
                    + ' of '
                    + (
                        summary.total
                        || 0
                    )
                    + ' objectives tracked';

            }


            if (progress) {

                progress.style.width =
                    Math.min(
                        summary.completion_rate
                        || 0,
                        100
                    )
                    + '%';

            }


            if (progressLabel) {

                progressLabel.textContent =
                    (
                        summary.completion_rate
                        || 0
                    )
                    + '% reporting completion';

            }

        }


        if (successCard) {

            const value =
                successCard.querySelector(
                    '.kpi-value'
                );

            if (value) {

                value.textContent =
                    summary.on_track
                    || 0;

            }

        }


        if (warningCard) {

            const value =
                warningCard.querySelector(
                    '.kpi-value'
                );

            if (value) {

                value.textContent =
                    summary.at_risk
                    || 0;

            }

        }


        if (dangerCard) {

            const value =
                dangerCard.querySelector(
                    '.kpi-value'
                );

            if (value) {

                value.textContent =
                    summary.not_met
                    || 0;

            }

        }

    }


    /* =========================================================
       UPDATE SECTION CHART
    ========================================================= */

    function updateCharts(sections) {

        if (!sectionChart) {
            return;
        }


        const labels =
            Object.keys(
                sections
                || {}
            );


        const values =
            labels.map(
                key =>
                    sections[key]
                        .avg_achievement
                    || 0
            );


        sectionChart.data.labels =
            labels;

        sectionChart
            .data
            .datasets[0]
            .data =
                values;

        sectionChart.update();

    }


    /* =========================================================
       UPDATE AT-RISK TABLE
       Existing AJAX functionality retained.
    ========================================================= */

    function updateAtRiskTable(objectives) {

        const tableBody =
            document.querySelector(
                '.quality-table tbody'
            );


        /*
         * If the table currently does not exist because
         * the server rendered the empty state, leave the
         * existing behaviour unchanged rather than
         * rebuilding the entire card.
         */

        if (!tableBody) {
            return;
        }


        if (
            objectives
            && objectives.length > 0
        ) {

            let html = '';


            objectives.forEach(
                obj => {

                    const percentage =
                        obj.percentage
                        || 0;

                    const statusClass =
                        obj.status
                        === 'At Risk'
                            ? 'status-pill-warning'
                            : 'status-pill-danger';

                    const progressClass =
                        obj.status
                        === 'At Risk'
                            ? 'bg-warning'
                            : 'bg-danger';


                    html += `
                        <tr>

                            <td>
                                <span class="objective-code">
                                    ${obj.objective_id}
                                </span>
                            </td>

                            <td>
                                <div class="objective-description">
                                    ${
                                        (
                                            obj.description
                                            || ''
                                        )
                                        .substring(
                                            0,
                                            65
                                        )
                                    }...
                                </div>
                            </td>

                            <td>
                                <span class="
                                    badge
                                    bg-secondary-subtle
                                    text-secondary-emphasis
                                ">
                                    ${obj.section}
                                </span>
                            </td>

                            <td class="fw-semibold">
                                ${obj.target}
                            </td>

                            <td class="fw-semibold">
                                ${obj.actual || 'N/A'}
                            </td>

                            <td>

                                <div class="
                                    d-flex
                                    justify-content-between
                                    mb-1
                                ">

                                    <small class="text-muted">
                                        Progress
                                    </small>

                                    <small class="fw-bold">
                                        ${percentage}%
                                    </small>

                                </div>

                                <div class="
                                    progress
                                    achievement-progress
                                ">

                                    <div
                                        class="
                                            progress-bar
                                            ${progressClass}
                                        "
                                        role="progressbar"
                                        style="
                                            width:
                                            ${Math.min(
                                                percentage,
                                                100
                                            )}%;
                                        "
                                    ></div>

                                </div>

                            </td>

                            <td>

                                <span
                                    class="
                                        status-pill
                                        ${statusClass}
                                    "
                                >

                                    ${
                                        obj.status
                                        === 'At Risk'
                                            ? '<i class="fas fa-exclamation-circle"></i>'
                                            : '<i class="fas fa-times-circle"></i>'
                                    }

                                    ${obj.status}

                                </span>

                            </td>

                            <td>

                                <div class="owner-cell">

                                    <span class="owner-avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <span>
                                        ${obj.owner}
                                    </span>

                                </div>

                            </td>

                            <td class="text-end">

                                <a
                                    href="/quality/corrective-actions/create?objective_id=${obj.id}"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                    "
                                >
                                    <i class="fas fa-plus"></i>
                                    CAR
                                </a>

                                <a
                                    href="/quality/objectives/${obj.id}"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-info
                                    "
                                >
                                    <i class="fas fa-eye"></i>
                                </a>

                            </td>

                        </tr>
                    `;

                }
            );


            tableBody.innerHTML =
                html;

        } else {

            tableBody.innerHTML = `
                <tr>

                    <td
                        colspan="9"
                        class="text-center py-4"
                    >

                        <i
                            class="
                                fas
                                fa-check-circle
                                text-success
                                me-1
                            "
                        ></i>

                        All objectives are on track.

                    </td>

                </tr>
            `;

        }

    }


    /* =========================================================
       REFRESH
    ========================================================= */

    function refreshData() {

        const selector =
            document.getElementById(
                'fyrSelector'
            );

        if (!selector) {
            return;
        }


        loadDashboardData(
            selector.value
        );

    }


    function refreshChart() {

        const selector =
            document.getElementById(
                'fyrSelector'
            );

        if (!selector) {
            return;
        }


        loadDashboardData(
            selector.value
        );

    }

</script>

@endpush
