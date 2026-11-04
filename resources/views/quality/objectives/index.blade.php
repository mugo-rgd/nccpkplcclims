@extends('layouts.app')

@section('title', 'Quality Objectives')

@section('content')

<div class="container-fluid quality-objectives-page">

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
                <i class="fas fa-bullseye"></i>
            </div>

            <div>

                <h1 class="page-title mb-1">
                    Quality Objectives
                </h1>

                <p class="page-subtitle mb-0">
                    Manage, monitor and track laboratory quality objectives.
                </p>

            </div>

        </div>


        @can('create_quality_objectives')

            <a
                href="{{ route('quality.objectives.create') }}"
                class="btn btn-primary create-objective-btn"
            >
                <i class="fas fa-plus me-1"></i>
                New Objective
            </a>

        @endcan

    </div>


    {{-- ========================================================= --}}
    {{-- FILTERS                                                   --}}
    {{-- ========================================================= --}}

    <div class="card objectives-card filter-card mb-4">

        <div class="card-header objectives-card-header">

            <div>

                <h5 class="card-section-title mb-1">
                    <i class="fas fa-filter text-primary me-2"></i>
                    Filter Objectives
                </h5>

                <small class="text-muted">
                    Narrow the list by section, financial year, status or keyword.
                </small>

            </div>

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="{{ route('quality.objectives.index') }}"
                class="row g-3 align-items-end"
            >

                {{-- Section --}}
                <div class="col-xl-3 col-md-6">

                    <label
                        for="section"
                        class="form-label filter-label"
                    >
                        Section
                    </label>

                    <select
                        name="section"
                        id="section"
                        class="form-select"
                    >
                        <option value="">
                            All Sections
                        </option>

                        @foreach($sections as $section)

                            <option
                                value="{{ $section }}"
                                @selected(
                                    request('section') == $section
                                )
                            >
                                {{ $section }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- FYR --}}
                <div class="col-xl-3 col-md-6">

                    <label
                        for="fyr"
                        class="form-label filter-label"
                    >
                        Financial Year
                    </label>

                    <select
                        name="fyr"
                        id="fyr"
                        class="form-select"
                    >

                        <option value="">
                            All FYR
                        </option>

                        @foreach($fyrPeriods as $fyr)

                            <option
                                value="{{ $fyr->fyr }}"
                                @selected(
                                    request('fyr') == $fyr->fyr
                                )
                            >
                                {{ $fyr->display_name }}

                                @if($fyr->is_active)
                                    — Current
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Status --}}
                <div class="col-xl-2 col-md-6">

                    <label
                        for="status"
                        class="form-label filter-label"
                    >
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-select"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        @foreach($statuses as $status)

                            <option
                                value="{{ $status }}"
                                @selected(
                                    request('status') == $status
                                )
                            >
                                {{
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $status
                                        )
                                    )
                                }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Search --}}
                <div class="col-xl-3 col-md-6">

                    <label
                        for="search"
                        class="form-label filter-label"
                    >
                        Search
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="ID, description, owner..."
                        >

                    </div>

                </div>


                {{-- Buttons --}}
                <div class="col-xl-1 col-md-12">

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-grow-1"
                            title="Apply Filters"
                        >
                            <i class="fas fa-search"></i>
                        </button>

                        <a
                            href="{{ route(
                                'quality.objectives.index'
                            ) }}"
                            class="btn btn-outline-secondary"
                            title="Reset Filters"
                        >
                            <i class="fas fa-undo"></i>
                        </a>

                    </div>

                </div>

            </form>


            {{-- Active filters --}}
            @if(
                request()->filled('section')
                || request()->filled('fyr')
                || request()->filled('status')
                || request()->filled('search')
            )

                <div class="active-filter-bar mt-3">

                    <span class="active-filter-label">
                        <i class="fas fa-filter me-1"></i>
                        Active filters:
                    </span>

                    @if(request()->filled('section'))
                        <span class="filter-chip">
                            Section:
                            <strong>
                                {{ request('section') }}
                            </strong>
                        </span>
                    @endif


                    @if(request()->filled('fyr'))
                        <span class="filter-chip">
                            FYR:
                            <strong>
                                {{ request('fyr') }}
                            </strong>
                        </span>
                    @endif


                    @if(request()->filled('status'))
                        <span class="filter-chip">
                            Status:
                            <strong>
                                {{
                                    ucfirst(
                                        request('status')
                                    )
                                }}
                            </strong>
                        </span>
                    @endif


                    @if(request()->filled('search'))
                        <span class="filter-chip">
                            Search:
                            <strong>
                                "{{ request('search') }}"
                            </strong>
                        </span>
                    @endif

                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- OBJECTIVES LIST                                           --}}
    {{-- ========================================================= --}}

    <div class="card objectives-card mb-4">

        <div class="card-header objectives-card-header">

            <div>

                <h5 class="card-section-title mb-1">
                    <i class="fas fa-list text-primary me-2"></i>
                    Objectives List
                </h5>

                <small class="text-muted">
                    Review target performance, achievement status and ownership.
                </small>

            </div>


            <span class="total-count-badge">
                <i class="fas fa-bullseye me-1"></i>

                {{ $objectives->total() }}

                {{ $objectives->total() === 1
                    ? 'Objective'
                    : 'Objectives' }}
            </span>

        </div>


        <div class="card-body p-0">

            @if($objectives->count() > 0)

                <div class="table-responsive">

                    <table
                        class="
                            table
                            objectives-table
                            align-middle
                            mb-0
                        "
                    >

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Section
                                </th>

                                <th>
                                    Target
                                </th>

                                <th>
                                    FYR
                                </th>

                                <th style="min-width:190px;">
                                    Achievement
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Owner
                                </th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($objectives as $objective)

                                @php
                                    $latest =
                                        $objective
                                            ->latest_achievement;

                                    $percentage =
                                        (float) (
                                            $objective
                                                ->achievement_percentage
                                            ?? 0
                                        );

                                    $achievementClass =
                                        $percentage >= 95
                                            ? 'success'
                                            : (
                                                $percentage >= 80
                                                    ? 'warning'
                                                    : 'danger'
                                            );
                                @endphp


                                <tr>

                                    {{-- ID --}}
                                    <td>

                                        <span class="objective-id">
                                            {{ $objective->objective_id }}
                                        </span>

                                    </td>


                                    {{-- Description --}}
                                    <td>

                                        <div class="objective-description">

                                            <span
                                                data-bs-toggle="tooltip"
                                                title="{{ $objective->description }}"
                                            >
                                                {{
                                                    Str::limit(
                                                        $objective->description,
                                                        70
                                                    )
                                                }}
                                            </span>


                                            @if($objective->iso17025_clause)

                                                <div class="mt-1">

                                                    <span class="iso-badge">

                                                        <i class="fas fa-certificate me-1"></i>

                                                        ISO
                                                        {{
                                                            $objective
                                                                ->iso17025_clause
                                                        }}

                                                    </span>

                                                </div>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- Section --}}
                                    <td>

                                        <span class="section-badge">
                                            {{ $objective->section }}
                                        </span>

                                    </td>


                                    {{-- Target --}}
                                    <td>

                                        <div class="target-value">

                                            <strong>
                                                {{
                                                    $objective
                                                        ->target_value
                                                }}
                                            </strong>

                                            <span>
                                                {{
                                                    $objective
                                                        ->unit_of_measure
                                                }}
                                            </span>

                                        </div>


                                        @if(
                                            $objective
                                                ->baseline_value
                                            !== null
                                        )

                                            <small class="text-muted">

                                                Baseline:

                                                {{
                                                    $objective
                                                        ->baseline_value
                                                }}

                                            </small>

                                        @endif

                                    </td>


                                    {{-- FYR --}}
                                    <td>

                                        <span class="fyr-badge">
                                            {{ $objective->target_fyr }}
                                        </span>

                                    </td>


                                    {{-- Achievement --}}
                                    <td>

                                        @if($latest)

                                            <div class="achievement-wrapper">

                                                <div
                                                    class="
                                                        d-flex
                                                        justify-content-between
                                                        align-items-center
                                                        mb-1
                                                    "
                                                >

                                                    <span class="achievement-actual">

                                                        {{
                                                            $latest
                                                                ->actual_achievement
                                                        }}

                                                    </span>

                                                    <span
                                                        class="
                                                            achievement-percentage
                                                            text-{{ $achievementClass }}
                                                        "
                                                    >

                                                        {{
                                                            round(
                                                                $percentage
                                                            )
                                                        }}%

                                                    </span>

                                                </div>


                                                <div class="progress achievement-progress">

                                                    <div
                                                        class="
                                                            progress-bar
                                                            bg-{{ $achievementClass }}
                                                        "
                                                        role="progressbar"
                                                        style="
                                                            width:
                                                            {{
                                                                min(
                                                                    $percentage,
                                                                    100
                                                                )
                                                            }}%;
                                                        "
                                                        aria-valuenow="{{ $percentage }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100"
                                                    ></div>

                                                </div>

                                            </div>

                                        @else

                                            <span class="not-tracked">

                                                <i class="far fa-clock me-1"></i>

                                                Not tracked

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Status --}}
                                    <td>

                                        @if($objective->status === 'active')

                                            <span class="status-badge status-active">

                                                <i class="fas fa-check-circle"></i>

                                                Active

                                            </span>

                                        @else

                                            <span class="status-badge status-inactive">

                                                <i class="fas fa-pause-circle"></i>

                                                Inactive

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Owner --}}
                                    <td>

                                        <div class="owner-info">

                                            <span class="owner-avatar">
                                                <i class="fas fa-user"></i>
                                            </span>

                                            <span>
                                                {{
                                                    $objective
                                                        ->owner
                                                        ?->name
                                                    ?? 'Unassigned'
                                                }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- Actions --}}
                                    <td class="text-end">

                                        <div
                                            class="
                                                d-inline-flex
                                                align-items-center
                                                gap-1
                                            "
                                        >

                                            {{-- View --}}
                                            <a
                                                href="{{ route(
                                                    'quality.objectives.show',
                                                    $objective->id
                                                ) }}"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-info
                                                    objective-action-btn
                                                "
                                                data-bs-toggle="tooltip"
                                                title="View Objective"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>


                                            {{-- Edit --}}
                                            @can('edit_quality_objectives')

                                                <a
                                                    href="{{ route(
                                                        'quality.objectives.edit',
                                                        $objective->id
                                                    ) }}"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-outline-warning
                                                        objective-action-btn
                                                    "
                                                    data-bs-toggle="tooltip"
                                                    title="Edit Objective"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                            @endcan


                                            {{-- Delete --}}
                                            @can('delete_quality_objectives')

                                                <button
                                                    type="button"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-outline-danger
                                                        objective-action-btn
                                                    "
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $objective->id }}"
                                                    title="Delete Objective"
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


                {{-- Pagination --}}
                <div class="objectives-pagination">

                    {{
                        $objectives
                            ->appends(
                                request()->query()
                            )
                            ->links(
                                
                            )
                    }}

                </div>

            @else

                {{-- ================================================= --}}
                {{-- EMPTY STATE                                      --}}
                {{-- ================================================= --}}

                <div class="empty-state">

                    <div class="empty-state-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>

                    <h4>
                        No objectives found
                    </h4>

                    @if(
                        request()->filled('section')
                        || request()->filled('fyr')
                        || request()->filled('status')
                        || request()->filled('search')
                    )

                        <p>
                            No quality objectives match the selected filters.
                            Try changing or resetting your filters.
                        </p>

                        <a
                            href="{{ route(
                                'quality.objectives.index'
                            ) }}"
                            class="btn btn-outline-primary"
                        >
                            <i class="fas fa-undo me-1"></i>
                            Reset Filters
                        </a>

                    @else

                        <p>
                            No quality objectives have been created yet.
                        </p>


                        @can('create_quality_objectives')

                            <a
                                href="{{ route(
                                    'quality.objectives.create'
                                ) }}"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-plus me-1"></i>
                                Create First Objective
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
{{-- Keep them outside the table to avoid table/modal issues.      --}}
{{-- ============================================================= --}}

@can('delete_quality_objectives')

    @foreach($objectives as $objective)

        <div
            class="modal fade"
            id="deleteModal{{ $objective->id }}"
            tabindex="-1"
            aria-labelledby="deleteModalLabel{{ $objective->id }}"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete-modal">

                    <div class="modal-header border-0 pb-0">

                        <h5
                            class="modal-title"
                            id="deleteModalLabel{{ $objective->id }}"
                        >
                            Delete Quality Objective
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


                        <h5 class="mt-3">
                            Are you sure?
                        </h5>

                        <p class="text-muted mb-2">

                            You are about to delete objective

                            <strong class="text-dark">
                                {{ $objective->objective_id }}
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
                                'quality.objectives.destroy',
                                $objective->id
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
                                Delete Objective
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

    .quality-objectives-page {
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
                #5145cd
            );

        font-size: 1.25rem;

        box-shadow:
            0 8px 20px
            rgba(13, 110, 253, 0.2);
    }


    .page-title {
        color: #172033;

        font-size: 1.65rem;
        font-weight: 800;
    }


    .page-subtitle {
        color: #6b7280;

        font-size: 0.9rem;
    }


    .create-objective-btn {
        min-height: 42px;

        display: inline-flex;

        align-items: center;

        border-radius: 10px;

        padding-left: 1rem;
        padding-right: 1rem;

        font-weight: 600;
    }


    /* =========================================================
       CARDS
    ========================================================= */

    .objectives-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;

        overflow: hidden;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);
    }


    .objectives-card-header {
        min-height: 68px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 1rem;

        padding: 0.9rem 1.2rem;

        border-bottom: 1px solid #eef1f5;

        background: #ffffff;
    }


    .card-section-title {
        color: #172033;

        font-size: 0.98rem;
        font-weight: 800;
    }


    /* =========================================================
       FILTERS
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


    .filter-card .form-select,
    .filter-card .form-control,
    .filter-card .input-group-text {
        min-height: 42px;

        border-color: #dce4ee;

        font-size: 0.82rem;
    }


    .filter-card .form-select,
    .filter-card .form-control {
        background-color: #f8fafc;
    }


    .filter-card .form-select:focus,
    .filter-card .form-control:focus {
        border-color: #0d6efd;

        background-color: #ffffff;

        box-shadow:
            0 0 0
            0.18rem
            rgba(13, 110, 253, 0.08);
    }


    .filter-card .input-group-text {
        color: #64748b;

        background: #f1f5f9;
    }


    .active-filter-bar {
        display: flex;

        align-items: center;
        flex-wrap: wrap;

        gap: 0.4rem;

        padding-top: 0.8rem;

        border-top: 1px solid #eef2f7;
    }


    .active-filter-label {
        margin-right: 0.2rem;

        color: #64748b;

        font-size: 0.72rem;
        font-weight: 700;
    }


    .filter-chip {
        display: inline-flex;

        align-items: center;

        gap: 0.2rem;

        padding: 0.3rem 0.55rem;

        border-radius: 999px;

        color: #31547c;

        background: #edf4ff;

        font-size: 0.68rem;
    }


    /* =========================================================
       TOTAL BADGE
    ========================================================= */

    .total-count-badge {
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

    .objectives-table thead th {
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


    .objectives-table tbody td {
        padding: 0.9rem;

        border-color: #eef2f7;

        color: #334155;

        font-size: 0.8rem;

        vertical-align: middle;
    }


    .objectives-table tbody tr {
        transition:
            background 0.15s ease;
    }


    .objectives-table tbody tr:hover {
        background: #fbfdff;
    }


    /* =========================================================
       OBJECTIVE ID
    ========================================================= */

    .objective-id {
        display: inline-flex;

        padding: 0.3rem 0.5rem;

        border-radius: 7px;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 0.75rem;
        font-weight: 800;

        white-space: nowrap;
    }


    .objective-description {
        min-width: 230px;

        line-height: 1.4;
    }


    /* =========================================================
       ISO / SECTION / FYR
    ========================================================= */

    .iso-badge {
        display: inline-flex;

        align-items: center;

        padding: 0.2rem 0.4rem;

        border-radius: 5px;

        color: #6b7280;

        background: #f1f5f9;

        font-size: 0.62rem;
    }


    .section-badge {
        display: inline-flex;

        padding: 0.3rem 0.55rem;

        border-radius: 7px;

        color: #495057;

        background: #eef2f6;

        font-size: 0.68rem;
        font-weight: 700;

        white-space: nowrap;
    }


    .fyr-badge {
        display: inline-flex;

        padding: 0.3rem 0.55rem;

        border-radius: 7px;

        color: #5c45a8;

        background: #f2efff;

        font-size: 0.68rem;
        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       TARGET
    ========================================================= */

    .target-value {
        display: flex;

        align-items: baseline;

        gap: 0.25rem;

        white-space: nowrap;
    }


    .target-value strong {
        color: #172033;

        font-size: 0.85rem;
    }


    .target-value span {
        color: #64748b;

        font-size: 0.68rem;
    }


    /* =========================================================
       ACHIEVEMENT
    ========================================================= */

    .achievement-wrapper {
        min-width: 150px;
    }


    .achievement-actual {
        color: #334155;

        font-size: 0.75rem;
        font-weight: 700;
    }


    .achievement-percentage {
        font-size: 0.72rem;
        font-weight: 800;
    }


    .achievement-progress {
        height: 7px;

        border-radius: 999px;

        background: #edf2f7;

        overflow: hidden;
    }


    .achievement-progress .progress-bar {
        border-radius: 999px;
    }


    .not-tracked {
        display: inline-flex;

        align-items: center;

        color: #94a3b8;

        font-size: 0.72rem;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .status-badge {
        display: inline-flex;

        align-items: center;

        gap: 0.3rem;

        padding: 0.35rem 0.6rem;

        border-radius: 999px;

        font-size: 0.67rem;
        font-weight: 700;

        white-space: nowrap;
    }


    .status-active {
        color: #157347;

        background: #e9f8ef;
    }


    .status-inactive {
        color: #6c757d;

        background: #eef1f4;
    }


    /* =========================================================
       OWNER
    ========================================================= */

    .owner-info {
        display: flex;

        align-items: center;

        gap: 0.45rem;

        min-width: 130px;
    }


    .owner-avatar {
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
       ACTION BUTTONS
    ========================================================= */

    .objective-action-btn {
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

    .objectives-pagination {
        padding: 1rem 1.2rem;

        border-top: 1px solid #eef2f7;

        background: #ffffff;
    }


    .objectives-pagination nav {
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

        margin: 0 auto 1rem;

        display: flex;

        align-items: center;
        justify-content: center;

        border-radius: 50%;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 1.7rem;
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

        font-size: 0.83rem;
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

        margin: 0 auto;

        display: flex;

        align-items: center;
        justify-content: center;

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


        .create-objective-btn {
            width: 100%;

            justify-content: center;
        }


        .objectives-card-header {
            align-items: flex-start;

            flex-direction: column;
        }


        .objective-description {
            min-width: 180px;
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
             */
            const tooltipTriggerList =
                [].slice.call(
                    document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'
                    )
                );


            tooltipTriggerList.forEach(
                function (tooltipTriggerEl) {

                    new bootstrap.Tooltip(
                        tooltipTriggerEl
                    );

                }
            );

        }
    );

</script>

@endpush
