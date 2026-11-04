@extends('layouts.app')

@section('title', 'Corrective Actions')

@section('content')

<div class="container-fluid corrective-actions-page">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER                                               --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="page-title-icon">
                <i class="fas fa-tools"></i>
            </div>

            <div>
                <h1 class="page-title mb-1">
                    Corrective Actions
                </h1>

                <p class="page-subtitle mb-0">
                    Manage and track corrective actions raised against quality issues.
                </p>
            </div>

        </div>


        @can('create_corrective_actions')

            <a
                href="{{ route('quality.corrective-actions.create') }}"
                class="btn btn-primary header-action-btn"
            >
                <i class="fas fa-plus me-1"></i>
                New Corrective Action
            </a>

        @endcan

    </div>


    {{-- ========================================================= --}}
    {{-- SUMMARY CARDS                                             --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">

        {{-- Total --}}
        <div class="col-xl-3 col-md-6">

            <div class="car-summary-card summary-primary h-100">

                <div class="summary-content">

                    <div>
                        <div class="summary-label">
                            Total CARs
                        </div>

                        <div class="summary-value">
                            {{ $correctiveActions->total() }}
                        </div>

                        <div class="summary-note">
                            All corrective actions
                        </div>
                    </div>


                    <div class="summary-icon">
                        <i class="fas fa-tasks"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Open --}}
        <div class="col-xl-3 col-md-6">

            <div class="car-summary-card summary-danger h-100">

                <div class="summary-content">

                    <div>
                        <div class="summary-label">
                            Open
                        </div>

                        <div class="summary-value">
                            {{
                                $correctiveActions
                                    ->where('status', 'Open')
                                    ->count()
                            }}
                        </div>

                        <div class="summary-note">
                            Awaiting action
                        </div>
                    </div>


                    <div class="summary-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- In Progress --}}
        <div class="col-xl-3 col-md-6">

            <div class="car-summary-card summary-warning h-100">

                <div class="summary-content">

                    <div>
                        <div class="summary-label">
                            In Progress
                        </div>

                        <div class="summary-value">
                            {{
                                $correctiveActions
                                    ->where('status', 'In Progress')
                                    ->count()
                            }}
                        </div>

                        <div class="summary-note">
                            Currently being worked on
                        </div>
                    </div>


                    <div class="summary-icon">
                        <i class="fas fa-spinner"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Closed --}}
        <div class="col-xl-3 col-md-6">

            <div class="car-summary-card summary-success h-100">

                <div class="summary-content">

                    <div>
                        <div class="summary-label">
                            Closed
                        </div>

                        <div class="summary-value">
                            {{
                                $correctiveActions
                                    ->where('status', 'Closed')
                                    ->count()
                            }}
                        </div>

                        <div class="summary-note">
                            Successfully completed
                        </div>
                    </div>


                    <div class="summary-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FILTERS                                                   --}}
    {{-- ========================================================= --}}

    <div class="card corrective-card filter-card mb-4">

        <div class="card-header corrective-card-header">

            <div>
                <h5 class="section-title mb-1">
                    <i class="fas fa-filter text-primary me-2"></i>
                    Filter Corrective Actions
                </h5>

                <small class="text-muted">
                    Narrow the list by status, objective or keyword.
                </small>
            </div>

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="{{ route('quality.corrective-actions.index') }}"
                class="row g-3 align-items-end"
            >

                {{-- Status --}}
                <div class="col-xl-3 col-md-6">

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
                                {{ $status }}
                            </option>

                        @endforeach
                    </select>

                </div>


                {{-- Objective --}}
                <div class="col-xl-4 col-md-6">

                    <label
                        for="objective_id"
                        class="form-label filter-label"
                    >
                        Objective
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
                            placeholder="CAR ID, issue, owner..."
                            value="{{ request('search') }}"
                        >

                    </div>

                </div>


                {{-- Buttons --}}
                <div class="col-xl-2 col-md-6">

                    <div class="d-flex gap-2">

                        <button
                            class="btn btn-primary flex-grow-1"
                            type="submit"
                        >
                            <i class="fas fa-search me-1"></i>
                            Search
                        </button>

                        <a
                            href="{{ route('quality.corrective-actions.index') }}"
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
                request()->filled('status')
                || request()->filled('objective_id')
                || request()->filled('search')
            )

                <div class="active-filters mt-3">

                    <span class="active-filter-title">
                        <i class="fas fa-filter me-1"></i>
                        Active filters:
                    </span>


                    @if(request()->filled('status'))

                        <span class="filter-chip">
                            Status:
                            <strong>
                                {{ request('status') }}
                            </strong>
                        </span>

                    @endif


                    @if(request()->filled('objective_id'))

                        <span class="filter-chip">
                            Objective selected
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


                    <a
                        href="{{ route('quality.corrective-actions.index') }}"
                        class="clear-filter-link"
                    >
                        Clear all
                    </a>

                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- CORRECTIVE ACTIONS TABLE                                  --}}
    {{-- ========================================================= --}}

    <div class="card corrective-card mb-4">

        <div class="card-header corrective-card-header">

            <div>
                <h5 class="section-title mb-1">
                    <i class="fas fa-list text-primary me-2"></i>
                    Corrective Actions List
                </h5>

                <small class="text-muted">
                    Review current corrective actions, deadlines and ownership.
                </small>
            </div>


            <span class="total-badge">

                <i class="fas fa-tools me-1"></i>

                {{ $correctiveActions->total() }}

                {{
                    $correctiveActions->total() === 1
                        ? 'CAR'
                        : 'CARs'
                }}

            </span>

        </div>


        <div class="card-body p-0">

            @if($correctiveActions->count() > 0)

                <div class="table-responsive">

                    <table class="table corrective-table align-middle mb-0">

                        <thead>
                            <tr>
                                <th>CAR ID</th>
                                <th>Objective</th>
                                <th>Issue</th>
                                <th>Status</th>
                                <th>Owner</th>
                                <th>Deadline</th>
                                <th>Time Remaining</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>


                        <tbody>

                            @foreach($correctiveActions as $car)

                                @php
                                    $daysLeft =
                                        now()->diffInDays(
                                            $car->deadline,
                                            false
                                        );

                                    $statusClass = match($car->status) {
                                        'Open' => 'danger',
                                        'In Progress' => 'warning',
                                        'Closed' => 'success',
                                        default => 'secondary',
                                    };

                                    $statusIcon = match($car->status) {
                                        'Open' => 'fa-exclamation-circle',
                                        'In Progress' => 'fa-spinner',
                                        'Closed' => 'fa-check-circle',
                                        default => 'fa-clock',
                                    };
                                @endphp


                                <tr>

                                    {{-- CAR ID --}}
                                    <td>
                                        <span class="car-id-badge">
                                            {{ $car->car_id }}
                                        </span>
                                    </td>


                                    {{-- Objective --}}
                                    <td>

                                        <div class="objective-cell">

                                            <div class="objective-icon">
                                                <i class="fas fa-bullseye"></i>
                                            </div>

                                            <div>

                                                <div class="objective-number">
                                                    {{
                                                        $car
                                                            ->objective
                                                            ?->objective_id
                                                        ?? 'N/A'
                                                    }}
                                                </div>

                                                <div class="objective-description">
                                                    {{
                                                        Str::limit(
                                                            $car
                                                                ->objective
                                                                ?->description
                                                            ?? '',
                                                            45
                                                        )
                                                    }}
                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Issue --}}
                                    <td>

                                        <div
                                            class="issue-description"
                                            data-bs-toggle="tooltip"
                                            title="{{ $car->issue_description }}"
                                        >
                                            {{
                                                Str::limit(
                                                    $car->issue_description,
                                                    60
                                                )
                                            }}
                                        </div>

                                    </td>


                                    {{-- Status --}}
                                    <td>

                                        <span
                                            class="
                                                car-status
                                                car-status-{{ $statusClass }}
                                            "
                                        >
                                            <i
                                                class="
                                                    fas
                                                    {{ $statusIcon }}
                                                "
                                            ></i>

                                            {{ $car->status }}
                                        </span>

                                    </td>


                                    {{-- Owner --}}
                                    <td>

                                        <div class="owner-info">

                                            <span class="owner-avatar">
                                                <i class="fas fa-user"></i>
                                            </span>

                                            <span>
                                                {{
                                                    $car
                                                        ->actionOwner
                                                        ?->name
                                                    ?? 'N/A'
                                                }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- Deadline --}}
                                    <td>

                                        <div class="deadline-info">

                                            <i class="far fa-calendar-alt"></i>

                                            {{
                                                $car
                                                    ->deadline
                                                    ?->format('d M Y')
                                                ?? 'N/A'
                                            }}

                                        </div>

                                    </td>


                                    {{-- Days Left --}}
                                    <td>

                                        @if($car->status !== 'Closed')

                                            @if($daysLeft < 0)

                                                <span class="deadline-badge deadline-overdue">

                                                    <i class="fas fa-exclamation-triangle"></i>

                                                    Overdue by
                                                    {{ abs($daysLeft) }}
                                                    {{
                                                        abs($daysLeft) === 1
                                                            ? 'day'
                                                            : 'days'
                                                    }}

                                                </span>

                                            @elseif($daysLeft === 0)

                                                <span class="deadline-badge deadline-warning">

                                                    <i class="fas fa-clock"></i>

                                                    Due today

                                                </span>

                                            @elseif($daysLeft <= 7)

                                                <span class="deadline-badge deadline-warning">

                                                    <i class="fas fa-clock"></i>

                                                    {{ $daysLeft }}
                                                    {{
                                                        $daysLeft === 1
                                                            ? 'day'
                                                            : 'days'
                                                    }}

                                                </span>

                                            @else

                                                <span class="deadline-badge deadline-info">

                                                    <i class="fas fa-calendar-check"></i>

                                                    {{ $daysLeft }} days

                                                </span>

                                            @endif

                                        @else

                                            <span class="deadline-badge deadline-complete">

                                                <i class="fas fa-check-circle"></i>

                                                Completed

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="text-end">

                                        <div class="d-inline-flex align-items-center gap-1">

                                            {{-- View --}}
                                            <a
                                                href="{{ route(
                                                    'quality.corrective-actions.show',
                                                    $car->id
                                                ) }}"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-info
                                                    action-button
                                                "
                                                data-bs-toggle="tooltip"
                                                title="View Corrective Action"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>


                                            {{-- Edit --}}
                                            @can('edit_corrective_actions')

                                                <a
                                                    href="{{ route(
                                                        'quality.corrective-actions.edit',
                                                        $car->id
                                                    ) }}"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-outline-warning
                                                        action-button
                                                    "
                                                    data-bs-toggle="tooltip"
                                                    title="Edit Corrective Action"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                            @endcan


                                            {{-- Delete --}}
                                            @can('delete_corrective_actions')

                                                <button
                                                    type="button"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-outline-danger
                                                        action-button
                                                    "
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $car->id }}"
                                                    title="Delete Corrective Action"
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
                        corrective-pagination
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
                            {{ $correctiveActions->firstItem() ?? 0 }}
                        </strong>

                        to

                        <strong>
                            {{ $correctiveActions->lastItem() ?? 0 }}
                        </strong>

                        of

                        <strong>
                            {{ $correctiveActions->total() }}
                        </strong>

                        entries

                    </div>


                    <div>
                        {{
                            $correctiveActions
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
                        <i class="fas fa-tools"></i>
                    </div>

                    <h4>
                        No corrective actions found
                    </h4>


                    @if(
                        request()->filled('status')
                        || request()->filled('objective_id')
                        || request()->filled('search')
                    )

                        <p>
                            No corrective actions match the selected filters.
                            Try changing or clearing your filters.
                        </p>

                        <a
                            href="{{ route(
                                'quality.corrective-actions.index'
                            ) }}"
                            class="btn btn-outline-primary"
                        >
                            <i class="fas fa-undo me-1"></i>
                            Reset Filters
                        </a>

                    @else

                        <p>
                            No corrective actions have been created yet.
                            Create one when a quality issue requires follow-up.
                        </p>


                        @can('create_corrective_actions')

                            <a
                                href="{{ route(
                                    'quality.corrective-actions.create'
                                ) }}"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-plus me-1"></i>
                                Create First CAR
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

@can('delete_corrective_actions')

    @foreach($correctiveActions as $car)

        <div
            class="modal fade"
            id="deleteModal{{ $car->id }}"
            tabindex="-1"
            aria-labelledby="deleteModalLabel{{ $car->id }}"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete-modal">

                    <div class="modal-header border-0 pb-0">

                        <h5
                            class="modal-title"
                            id="deleteModalLabel{{ $car->id }}"
                        >
                            Delete Corrective Action
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
                            Delete this corrective action?
                        </h5>


                        <p class="text-muted mb-2">

                            You are about to delete

                            <strong class="text-dark">
                                {{ $car->car_id }}
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
                                'quality.corrective-actions.destroy',
                                $car->id
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
                                Delete Corrective Action
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

    .corrective-actions-page {
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
                #f59f00
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

        padding-left: 1rem;
        padding-right: 1rem;

        border-radius: 9px;

        font-size: 0.82rem;
        font-weight: 600;
    }


    /* =========================================================
       SUMMARY CARDS
    ========================================================= */

    .car-summary-card {
        position: relative;

        min-height: 145px;

        padding: 1.15rem;

        border: 1px solid #edf0f5;

        border-radius: 15px;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);

        overflow: hidden;
    }


    .car-summary-card::before {
        content: "";

        position: absolute;

        left: 0;
        top: 0;

        width: 4px;
        height: 100%;
    }


    .summary-primary::before {
        background: #0d6efd;
    }


    .summary-danger::before {
        background: #dc3545;
    }


    .summary-warning::before {
        background: #ffc107;
    }


    .summary-success::before {
        background: #198754;
    }


    .summary-content {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 1rem;
    }


    .summary-label {
        margin-bottom: 0.35rem;

        color: #64748b;

        font-size: 0.72rem;
        font-weight: 800;

        text-transform: uppercase;
        letter-spacing: 0.03em;
    }


    .summary-value {
        color: #172033;

        font-size: 1.9rem;
        font-weight: 800;

        line-height: 1.1;
    }


    .summary-note {
        margin-top: 0.5rem;

        color: #94a3b8;

        font-size: 0.7rem;
    }


    .summary-icon {
        width: 46px;
        height: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 46px;

        border-radius: 12px;

        font-size: 1rem;
    }


    .summary-primary .summary-icon {
        color: #0d6efd;
        background: #edf4ff;
    }


    .summary-danger .summary-icon {
        color: #dc3545;
        background: #fdecec;
    }


    .summary-warning .summary-icon {
        color: #a97300;
        background: #fff5d6;
    }


    .summary-success .summary-icon {
        color: #198754;
        background: #e9f8ef;
    }


    /* =========================================================
       CARDS
    ========================================================= */

    .corrective-card {
        border: 1px solid #edf0f5;

        border-radius: 16px;

        overflow: hidden;

        background: #ffffff;

        box-shadow:
            0 5px 18px
            rgba(15, 23, 42, 0.06);
    }


    .corrective-card-header {
        min-height: 68px;

        display: flex;
        align-items: center;
        justify-content: space-between;

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

        font-size: 0.8rem;
    }


    .filter-card .form-control,
    .filter-card .form-select {
        background: #f8fafc;
    }


    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #0d6efd;

        background: #ffffff;

        box-shadow:
            0 0 0
            0.18rem
            rgba(13, 110, 253, 0.08);
    }


    .filter-card .input-group-text {
        color: #64748b;

        background: #f1f5f9;
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
        color: #dc3545;

        font-size: 0.68rem;
        font-weight: 600;

        text-decoration: none;
    }


    /* =========================================================
       TOTAL
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

    .corrective-table thead th {
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


    .corrective-table tbody td {
        padding: 0.9rem;

        border-color: #eef2f7;

        color: #334155;

        font-size: 0.8rem;

        vertical-align: middle;
    }


    .corrective-table tbody tr:hover {
        background: #fbfdff;
    }


    /* =========================================================
       CAR ID
    ========================================================= */

    .car-id-badge {
        display: inline-flex;

        padding: 0.3rem 0.5rem;

        border-radius: 7px;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 0.72rem;
        font-weight: 800;

        white-space: nowrap;
    }


    /* =========================================================
       OBJECTIVE
    ========================================================= */

    .objective-cell {
        display: flex;
        align-items: center;

        gap: 0.6rem;

        min-width: 230px;
    }


    .objective-icon {
        width: 34px;
        height: 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 34px;

        border-radius: 9px;

        color: #0d6efd;

        background: #edf4ff;

        font-size: 0.7rem;
    }


    .objective-number {
        color: #172033;

        font-size: 0.75rem;
        font-weight: 800;
    }


    .objective-description {
        max-width: 240px;

        color: #64748b;

        font-size: 0.67rem;

        line-height: 1.35;
    }


    /* =========================================================
       ISSUE
    ========================================================= */

    .issue-description {
        min-width: 220px;
        max-width: 330px;

        color: #475569;

        line-height: 1.4;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .car-status {
        display: inline-flex;
        align-items: center;

        gap: 0.3rem;

        padding: 0.35rem 0.6rem;

        border-radius: 999px;

        font-size: 0.66rem;
        font-weight: 700;

        white-space: nowrap;
    }


    .car-status-danger {
        color: #b02a37;
        background: #fdebed;
    }


    .car-status-warning {
        color: #856404;
        background: #fff4d6;
    }


    .car-status-success {
        color: #157347;
        background: #e9f8ef;
    }


    .car-status-secondary {
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
       DEADLINE
    ========================================================= */

    .deadline-info {
        display: inline-flex;
        align-items: center;

        gap: 0.35rem;

        color: #475569;

        white-space: nowrap;
    }


    .deadline-badge {
        display: inline-flex;
        align-items: center;

        gap: 0.3rem;

        padding: 0.35rem 0.6rem;

        border-radius: 999px;

        font-size: 0.66rem;
        font-weight: 700;

        white-space: nowrap;
    }


    .deadline-overdue {
        color: #b02a37;
        background: #fdebed;
    }


    .deadline-warning {
        color: #856404;
        background: #fff4d6;
    }


    .deadline-info {
        color: #475569;
    }


    .deadline-badge.deadline-info {
        color: #087990;
        background: #e7f7fa;
    }


    .deadline-complete {
        color: #157347;
        background: #e9f8ef;
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

    .corrective-pagination {
        padding: 1rem 1.2rem;

        border-top: 1px solid #eef2f7;
    }


    .pagination-summary {
        color: #64748b;

        font-size: 0.73rem;
    }


    .corrective-pagination nav {
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

        font-size: 1.6rem;
    }


    .empty-state h4 {
        color: #172033;

        font-size: 1.05rem;
        font-weight: 800;
    }


    .empty-state p {
        max-width: 520px;

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
            width: 100%;

            justify-content: center;
        }


        .corrective-card-header {
            align-items: flex-start;

            flex-direction: column;
        }


        .corrective-pagination {
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
             * Bootstrap tooltips.
             *
             * Delete buttons intentionally use only
             * data-bs-toggle="modal".
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
