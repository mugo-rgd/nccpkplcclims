@extends('layouts.app')

@section('title', 'Quality Achievements')

@section('content')

<div class="container-fluid quality-achievements-page">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER                                               --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="page-title-icon">
                <i class="fas fa-chart-line"></i>
            </div>

            <div>
                <h1 class="page-title mb-1">
                    Quality Achievements
                </h1>

                <p class="page-subtitle mb-0">
                    Track and monitor laboratory performance against quality objectives.
                </p>
            </div>

        </div>


        @can('create_achievements')

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="{{ route('quality.achievements.create') }}"
                    class="btn btn-primary header-action-btn"
                >
                    <i class="fas fa-plus me-1"></i>
                    Record Achievement
                </a>

                <a
                    href="{{ route('quality.achievements.bulk') }}"
                    class="btn btn-success header-action-btn"
                >
                    <i class="fas fa-upload me-1"></i>
                    Bulk Entry
                </a>

            </div>

        @endcan

    </div>


    {{-- ========================================================= --}}
    {{-- FILTERS                                                   --}}
    {{-- ========================================================= --}}

    <div class="card achievement-card filter-card mb-4">

        <div class="card-header achievement-card-header">

            <div>
                <h5 class="section-title mb-1">
                    <i class="fas fa-filter text-primary me-2"></i>
                    Filter Achievements
                </h5>

                <small class="text-muted">
                    Find achievement records by objective, financial year or quarter.
                </small>
            </div>

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="{{ route('quality.achievements.index') }}"
                class="row g-3 align-items-end"
            >

                {{-- Objective --}}
                <div class="col-xl-5 col-lg-5 col-md-6">

                    <label
                        for="objective_id"
                        class="form-label filter-label"
                    >
                        Quality Objective
                    </label>

                    <select
                        name="objective_id"
                        id="objective_id"
                        class="form-select"
                    >

                        <option value="">
                            All Objectives
                        </option>

                        @foreach($objectives as $id => $description)

                            <option
                                value="{{ $id }}"
                                @selected(
                                    (string) request('objective_id')
                                    === (string) $id
                                )
                            >
                                {{ $description }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- FYR --}}
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="fyr_period_id"
                        class="form-label filter-label"
                    >
                        Financial Year
                    </label>

                    <select
                        name="fyr_period_id"
                        id="fyr_period_id"
                        class="form-select"
                    >

                        <option value="">
                            All Financial Years
                        </option>

                        @foreach($fyrPeriods as $fyr)

                            <option
                                value="{{ $fyr->id }}"
                                @selected(
                                    (string) request('fyr_period_id')
                                    === (string) $fyr->id
                                )
                            >
                                {{ $fyr->display_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Quarter --}}
                <div class="col-xl-2 col-lg-2 col-md-6">

                    <label
                        for="quarter"
                        class="form-label filter-label"
                    >
                        Quarter
                    </label>

                    <select
                        name="quarter"
                        id="quarter"
                        class="form-select"
                    >

                        <option value="">
                            All Quarters
                        </option>

                        @foreach($quarters as $quarter)

                            <option
                                value="{{ $quarter }}"
                                @selected(
                                    request('quarter') == $quarter
                                )
                            >
                                {{ $quarter }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Buttons --}}
                <div class="col-xl-2 col-lg-2 col-md-6">

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-grow-1"
                        >
                            <i class="fas fa-filter me-1"></i>
                            Filter
                        </button>

                        <a
                            href="{{ route('quality.achievements.index') }}"
                            class="btn btn-outline-secondary"
                            title="Reset filters"
                        >
                            <i class="fas fa-undo"></i>
                        </a>

                    </div>

                </div>

            </form>


            {{-- ================================================= --}}
            {{-- ACTIVE FILTERS                                    --}}
            {{-- ================================================= --}}

            @if(
                request()->filled('objective_id')
                || request()->filled('fyr_period_id')
                || request()->filled('quarter')
            )

                <div class="active-filters mt-3">

                    <span class="active-filter-title">
                        <i class="fas fa-filter me-1"></i>
                        Active filters:
                    </span>


                    @if(request()->filled('objective_id'))

                        <span class="filter-chip">
                            Objective selected
                        </span>

                    @endif


                    @if(request()->filled('fyr_period_id'))

                        @php
                            $selectedFyr = collect($fyrPeriods)
                                ->firstWhere(
                                    'id',
                                    request('fyr_period_id')
                                );
                        @endphp

                        <span class="filter-chip">
                            FYR:
                            <strong>
                                {{
                                    $selectedFyr?->display_name
                                    ?? request('fyr_period_id')
                                }}
                            </strong>
                        </span>

                    @endif


                    @if(request()->filled('quarter'))

                        <span class="filter-chip">
                            Quarter:
                            <strong>
                                {{ request('quarter') }}
                            </strong>
                        </span>

                    @endif


                    <a
                        href="{{ route('quality.achievements.index') }}"
                        class="clear-filter-link"
                    >
                        Clear all
                    </a>

                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- ACHIEVEMENTS LIST                                         --}}
    {{-- ========================================================= --}}

    <div class="card achievement-card mb-4">

        <div class="card-header achievement-card-header">

            <div>

                <h5 class="section-title mb-1">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Achievements List
                </h5>

                <small class="text-muted">
                    Compare recorded achievement against the objective target.
                </small>

            </div>


            <span class="total-badge">

                <i class="fas fa-chart-bar me-1"></i>

                {{ $achievements->total() }}

                {{
                    $achievements->total() === 1
                        ? 'Record'
                        : 'Records'
                }}

            </span>

        </div>


        <div class="card-body p-0">

            @if($achievements->count() > 0)

                <div class="table-responsive">

                    <table class="table achievements-table align-middle mb-0">

                        <thead>

                            <tr>

                                <th>
                                    Objective
                                </th>

                                <th>
                                    Quarter
                                </th>

                                <th>
                                    Achievement
                                </th>

                                <th>
                                    Target
                                </th>

                                <th style="min-width: 200px;">
                                    Performance
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Collected By
                                </th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($achievements as $achievement)

                                @php

                                    $percentage = (float) (
                                        $achievement
                                            ->achievement_percentage
                                        ?? 0
                                    );

                                    $progressPercentage =
                                        min(
                                            max($percentage, 0),
                                            100
                                        );

                                    $performanceClass =
                                        $percentage >= 95
                                            ? 'success'
                                            : (
                                                $percentage >= 80
                                                    ? 'warning'
                                                    : 'danger'
                                            );

                                    $statusClass = match(
                                        $achievement->status
                                    ) {
                                        'On Track' => 'success',
                                        'At Risk' => 'warning',
                                        'Not Met' => 'danger',
                                        default => 'secondary',
                                    };

                                    $statusIcon = match(
                                        $achievement->status
                                    ) {
                                        'On Track' =>
                                            'fa-check-circle',

                                        'At Risk' =>
                                            'fa-exclamation-triangle',

                                        'Not Met' =>
                                            'fa-times-circle',

                                        default =>
                                            'fa-clock',
                                    };

                                @endphp


                                <tr>

                                    {{-- ================================= --}}
                                    {{-- OBJECTIVE                         --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <div class="objective-cell">

                                            <div class="objective-icon">
                                                <i class="fas fa-bullseye"></i>
                                            </div>

                                            <div>

                                                <div class="objective-number">
                                                    {{
                                                        $achievement
                                                            ->objective
                                                            ?->objective_id
                                                        ?? 'N/A'
                                                    }}
                                                </div>

                                                <div
                                                    class="objective-description"
                                                    data-bs-toggle="tooltip"
                                                    title="{{
                                                        $achievement
                                                            ->objective
                                                            ?->description
                                                        ?? ''
                                                    }}"
                                                >

                                                    {{
                                                        Str::limit(
                                                            $achievement
                                                                ->objective
                                                                ?->description
                                                            ?? '',
                                                            50
                                                        )
                                                    }}

                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- QUARTER                           --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <span class="quarter-badge">

                                            <i class="far fa-calendar-alt me-1"></i>

                                            {{
                                                $achievement
                                                    ->quarter_label
                                            }}

                                        </span>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- ACHIEVEMENT                       --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <div class="achievement-value">

                                            <strong>
                                                {{
                                                    $achievement
                                                        ->actual_achievement
                                                }}
                                            </strong>

                                            @if(
                                                !empty(
                                                    $achievement
                                                        ->objective
                                                        ?->unit_of_measure
                                                )
                                            )

                                                <span>
                                                    {{
                                                        $achievement
                                                            ->objective
                                                            ?->unit_of_measure
                                                    }}
                                                </span>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- TARGET                            --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <div class="target-value">

                                            <strong>
                                                {{
                                                    $achievement
                                                        ->objective
                                                        ?->target_value
                                                    ?? 'N/A'
                                                }}
                                            </strong>

                                            @if(
                                                !empty(
                                                    $achievement
                                                        ->objective
                                                        ?->unit_of_measure
                                                )
                                            )

                                                <span>
                                                    {{
                                                        $achievement
                                                            ->objective
                                                            ?->unit_of_measure
                                                    }}
                                                </span>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- PERFORMANCE                       --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <div class="performance-wrapper">

                                            <div
                                                class="
                                                    d-flex
                                                    justify-content-between
                                                    align-items-center
                                                    mb-1
                                                "
                                            >

                                                <small class="text-muted">
                                                    Achievement
                                                </small>

                                                <strong
                                                    class="
                                                        performance-value
                                                        text-{{ $performanceClass }}
                                                    "
                                                >
                                                    {{
                                                        round(
                                                            $percentage
                                                        )
                                                    }}%
                                                </strong>

                                            </div>


                                            <div class="progress performance-progress">

                                                <div
                                                    class="
                                                        progress-bar
                                                        bg-{{ $performanceClass }}
                                                    "
                                                    role="progressbar"
                                                    style="
                                                        width:
                                                        {{ $progressPercentage }}%;
                                                    "
                                                    aria-valuenow="{{ $percentage }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                ></div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- STATUS                            --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <span
                                            class="
                                                achievement-status
                                                status-{{ $statusClass }}
                                            "
                                        >

                                            <i
                                                class="
                                                    fas
                                                    {{ $statusIcon }}
                                                "
                                            ></i>

                                            {{
                                                $achievement->status
                                                ?: 'Pending'
                                            }}

                                        </span>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- COLLECTED BY                      --}}
                                    {{-- ================================= --}}

                                    <td>

                                        <div class="collector-info">

                                            <span class="collector-avatar">
                                                <i class="fas fa-user"></i>
                                            </span>

                                            <span>
                                                {{
                                                    $achievement
                                                        ->collectedBy
                                                        ?->name
                                                    ?? 'N/A'
                                                }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- ================================= --}}
                                    {{-- ACTIONS                           --}}
                                    {{-- ================================= --}}

                                    <td class="text-end">

                                        <div
                                            class="
                                                d-inline-flex
                                                align-items-center
                                                gap-1
                                            "
                                        >

                                            @can('edit_achievements')

                                                <a
                                                    href="{{ route(
                                                        'quality.achievements.edit',
                                                        $achievement->id
                                                    ) }}"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-outline-warning
                                                        action-button
                                                    "
                                                    data-bs-toggle="tooltip"
                                                    title="Edit Achievement"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                            @endcan


                                            @can('delete_achievements')

                                                <button
                                                    type="button"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-outline-danger
                                                        action-button
                                                    "
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $achievement->id }}"
                                                    title="Delete Achievement"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                            @endcan

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- ================================================= --}}
                {{-- PAGINATION                                        --}}
                {{-- ================================================= --}}

                <div
                    class="
                        achievement-pagination
                        d-flex
                        justify-content-between
                        align-items-center
                        flex-wrap
                        gap-3
                    "
                >

                    <div class="pagination-summary">

                        Showing

                        <strong>
                            {{ $achievements->firstItem() ?? 0 }}
                        </strong>

                        to

                        <strong>
                            {{ $achievements->lastItem() ?? 0 }}
                        </strong>

                        of

                        <strong>
                            {{ $achievements->total() }}
                        </strong>

                        entries

                    </div>


                    <div>

                        {{
                            $achievements
                                ->appends(
                                    request()->query()
                                )
                                ->links(
                                    
                                )
                        }}

                    </div>

                </div>

            @else

                {{-- ================================================= --}}
                {{-- EMPTY STATE                                      --}}
                {{-- ================================================= --}}

                <div class="empty-state">

                    <div class="empty-state-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>

                    <h4>
                        No achievements found
                    </h4>


                    @if(
                        request()->filled('objective_id')
                        || request()->filled('fyr_period_id')
                        || request()->filled('quarter')
                    )

                        <p>
                            No achievement records match your selected
                            filters. Try changing the filters or reset them
                            to view all records.
                        </p>

                        <a
                            href="{{ route(
                                'quality.achievements.index'
                            ) }}"
                            class="btn btn-outline-primary"
                        >
                            <i class="fas fa-undo me-1"></i>
                            Reset Filters
                        </a>

                    @else

                        <p>
                            No achievements have been recorded yet.
                            Record an achievement to begin tracking
                            performance against your quality objectives.
                        </p>


                        @can('create_achievements')

                            <a
                                href="{{ route(
                                    'quality.achievements.create'
                                ) }}"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-plus me-1"></i>
                                Record First Achievement
                            </a>

                        @endcan

                    @endif

                </div>

            @endif

        </div>

    </div>

</div>


{{-- ============================================================= --}}
{{-- DELETE MODALS                                                 --}}
{{-- ============================================================= --}}

@can('delete_achievements')

    @foreach($achievements as $achievement)

        <div
            class="modal fade"
            id="deleteModal{{ $achievement->id }}"
            tabindex="-1"
            aria-labelledby="deleteModalLabel{{ $achievement->id }}"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete-modal">

                    <div class="modal-header border-0 pb-0">

                        <h5
                            class="modal-title"
                            id="deleteModalLabel{{ $achievement->id }}"
                        >
                            Delete Achievement
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>


                    <div class="modal-body text-center px-4 py-4">

                        <div class="delete-warning-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>


                        <h5 class="mt-3 mb-2">
                            Delete this achievement?
                        </h5>


                        <p class="text-muted mb-2">

                            You are about to delete the achievement
                            recorded for

                            <strong class="text-dark">
                                {{
                                    $achievement
                                        ->objective
                                        ?->objective_id
                                    ?? 'this objective'
                                }}
                            </strong>

                            for

                            <strong class="text-dark">
                                {{
                                    $achievement
                                        ->quarter_label
                                }}
                            </strong>.

                        </p>


                        <p class="small text-danger mb-0">

                            <i class="fas fa-info-circle me-1"></i>

                            This action cannot be undone.

                        </p>

                    </div>


                    <div
                        class="
                            modal-footer
                            border-0
                            justify-content-center
                            pt-0
                            pb-4
                        "
                    >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>


                        <form
                            action="{{ route(
                                'quality.achievements.destroy',
                                $achievement->id
                            ) }}"
                            method="POST"
                            class="d-inline"
                        >

                            @csrf
                            @method('DELETE')


                            <button
                                type="submit"
                                class="btn btn-danger"
                            >
                                <i class="fas fa-trash me-1"></i>
                                Delete Achievement
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    @endforeach

@endcan

@endsection


@push('styles')

<style>

    /* =========================================================
       PAGE
    ========================================================= */

    .quality-achievements-page {
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
                #198754
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


    .header-action-btn {
        min-height: 42px;

        display: inline-flex;
        align-items: center;

        border-radius: 9px;

        padding-left: 1rem;
        padding-right: 1rem;

        font-size: 0.82rem;
        font-weight: 600;
    }


    /* =========================================================
       CARDS
    ========================================================= */

    .achievement-card {
        border: 1px solid #edf0f5;

        border-radius: 16px;

        overflow: hidden;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);
    }


    .achievement-card-header {
        min-height: 68px;

        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: 1rem;

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
       FILTER
    ========================================================= */

    .filter-card .card-body {
        padding: 1rem 1.2rem;
    }


    .filter-label {
        margin-bottom: 0.35rem;

        color: #475569;

        font-size: 0.76rem;
        font-weight: 700;
    }


    .filter-card .form-select {
        min-height: 42px;

        border-color: #dce4ee;

        background: #f8fafc;

        font-size: 0.8rem;
    }


    .filter-card .form-select:focus {
        border-color: #0d6efd;

        background: #ffffff;

        box-shadow:
            0 0 0
            0.18rem
            rgba(13, 110, 253, 0.08);
    }


    .active-filters {
        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: 0.4rem;

        padding-top: 0.8rem;

        border-top: 1px solid #eef2f7;
    }


    .active-filter-title {
        color: #64748b;

        font-size: 0.7rem;
        font-weight: 700;
    }


    .filter-chip {
        padding: 0.3rem 0.55rem;

        border-radius: 999px;

        color: #31547c;

        background: #edf4ff;

        font-size: 0.68rem;
    }


    .clear-filter-link {
        margin-left: 0.3rem;

        color: #dc3545;

        font-size: 0.68rem;
        font-weight: 600;

        text-decoration: none;
    }


    /* =========================================================
       TOTAL BADGE
    ========================================================= */

    .total-badge {
        display: inline-flex;
        align-items: center;

        padding: 0.4rem 0.7rem;

        border-radius: 999px;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 0.7rem;
        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .achievements-table thead th {
        padding: 0.8rem 0.9rem;

        border-bottom: 1px solid #e5e7eb;

        color: #64748b;

        background: #f8fafc;

        font-size: 0.68rem;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.03em;

        white-space: nowrap;
    }


    .achievements-table tbody td {
        padding: 0.9rem;

        border-color: #eef2f7;

        color: #334155;

        font-size: 0.8rem;

        vertical-align: middle;
    }


    .achievements-table tbody tr:hover {
        background: #fbfdff;
    }


    /* =========================================================
       OBJECTIVE
    ========================================================= */

    .objective-cell {
        display: flex;
        align-items: center;

        gap: 0.65rem;

        min-width: 250px;
    }


    .objective-icon {
        width: 36px;
        height: 36px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 36px;

        border-radius: 9px;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 0.75rem;
    }


    .objective-number {
        margin-bottom: 0.15rem;

        color: #172033;

        font-size: 0.77rem;
        font-weight: 800;
    }


    .objective-description {
        max-width: 270px;

        color: #64748b;

        font-size: 0.7rem;

        white-space: normal;
    }


    /* =========================================================
       QUARTER
    ========================================================= */

    .quarter-badge {
        display: inline-flex;
        align-items: center;

        padding: 0.32rem 0.55rem;

        border-radius: 7px;

        color: #087990;

        background: #e7f7fa;

        font-size: 0.68rem;
        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       VALUES
    ========================================================= */

    .achievement-value,
    .target-value {
        display: flex;
        align-items: baseline;

        gap: 0.25rem;

        white-space: nowrap;
    }


    .achievement-value strong {
        color: #0d6efd;

        font-size: 0.88rem;
    }


    .target-value strong {
        color: #172033;

        font-size: 0.85rem;
    }


    .achievement-value span,
    .target-value span {
        color: #64748b;

        font-size: 0.66rem;
    }


    /* =========================================================
       PERFORMANCE
    ========================================================= */

    .performance-wrapper {
        min-width: 160px;
    }


    .performance-value {
        font-size: 0.72rem;
        font-weight: 800;
    }


    .performance-progress {
        height: 7px;

        border-radius: 999px;

        overflow: hidden;

        background: #edf2f7;
    }


    .performance-progress .progress-bar {
        border-radius: 999px;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .achievement-status {
        display: inline-flex;
        align-items: center;

        gap: 0.3rem;

        padding: 0.35rem 0.6rem;

        border-radius: 999px;

        font-size: 0.66rem;
        font-weight: 700;

        white-space: nowrap;
    }


    .status-success {
        color: #157347;
        background: #e9f8ef;
    }


    .status-warning {
        color: #8a6300;
        background: #fff4d6;
    }


    .status-danger {
        color: #b02a37;
        background: #fdebed;
    }


    .status-secondary {
        color: #6c757d;
        background: #eef1f4;
    }


    /* =========================================================
       COLLECTOR
    ========================================================= */

    .collector-info {
        display: flex;
        align-items: center;

        gap: 0.45rem;

        min-width: 130px;
    }


    .collector-avatar {
        width: 30px;
        height: 30px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 30px;

        border-radius: 50%;

        color: #64748b;

        background: #edf2f7;

        font-size: 0.65rem;
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .action-button {
        width: 32px;
        height: 32px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0;

        border-radius: 7px;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .achievement-pagination {
        padding: 1rem 1.2rem;

        border-top: 1px solid #eef2f7;
    }


    .pagination-summary {
        color: #64748b;

        font-size: 0.73rem;
    }


    .achievement-pagination nav {
        margin-bottom: 0;
    }


    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .empty-state {
        padding: 4rem 1rem;

        text-align: center;
    }


    .empty-state-icon {
        width: 70px;
        height: 70px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin: 0 auto 1rem;

        border-radius: 50%;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 1.6rem;
    }


    .empty-state h4 {
        color: #172033;

        font-size: 1.05rem;
        font-weight: 800;
    }


    .empty-state p {
        max-width: 500px;

        margin: 0 auto 1.25rem;

        color: #64748b;

        font-size: 0.82rem;
    }


    /* =========================================================
       DELETE MODAL
    ========================================================= */

    .delete-modal {
        border: none;

        border-radius: 16px;

        overflow: hidden;
    }


    .delete-warning-icon {
        width: 64px;
        height: 64px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin: 0 auto;

        border-radius: 50%;

        color: #dc3545;

        background: #fdecec;

        font-size: 1.5rem;
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


        .header-action-btn {
            flex: 1;

            justify-content: center;
        }


        .achievement-card-header {
            align-items: flex-start;

            flex-direction: column;
        }


        .objective-cell {
            min-width: 210px;
        }


        .achievement-pagination {
            align-items: flex-start !important;

            flex-direction: column;
        }

    }

</style>

@endpush


@push('scripts')

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
             * Initialize Bootstrap tooltips.
             *
             * Only elements explicitly using
             * data-bs-toggle="tooltip" are initialized.
             *
             * Delete buttons use data-bs-toggle="modal"
             * and therefore do not conflict with tooltips.
             */
            const tooltipElements =
                document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]'
                );


            tooltipElements.forEach(
                function (element) {

                    new bootstrap.Tooltip(
                        element
                    );

                }
            );

        }
    );

</script>

@endpush
