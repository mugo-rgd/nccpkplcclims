@extends('layouts.app')

@section('title', 'My Job Cards')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Job Card Accessibility
    |--------------------------------------------------------------------------
    |
    | Once a calibration reaches the assigned stage,
    | the Job Card remains accessible for all later stages.
    |
    */

    $jobCardStatuses = [
        'assigned',
        'in_progress',
        'testing',
        'completed',
        'certified',
    ];
@endphp

<div class="container-fluid px-0">

    <div class="card shadow-sm border-0">

        {{-- ========================================================= --}}
        {{-- HEADER                                                    --}}
        {{-- ========================================================= --}}

        <div
            class="
                card-header
                bg-white
                d-flex
                justify-content-between
                align-items-center
                flex-wrap
                gap-2
            "
        >
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-clipboard-list text-primary me-2"></i>
                    My Job Cards
                </h4>

                <small class="text-muted">
                    View job cards issued for your calibration requests.
                </small>
            </div>

            <span class="badge bg-primary">
                {{ $jobCards->total() }}
                {{ $jobCards->total() === 1 ? 'Job Card' : 'Job Cards' }}
            </span>
        </div>


        {{-- ========================================================= --}}
        {{-- TABLE                                                     --}}
        {{-- ========================================================= --}}

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="
                        table
                        table-striped
                        table-hover
                        align-middle
                        mb-0
                    "
                >

                    <thead class="table-dark">
                        <tr>
                            <th>Job Number</th>
                            <th>Calibration</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($jobCards as $jobCard)

                            @php
                                /*
                                |--------------------------------------------------------------------------
                                | Related Calibration
                                |--------------------------------------------------------------------------
                                */

                                $calibration =
                                    $jobCard->calibration;

                                $calibrationStatus =
                                    (string) (
                                        $calibration?->status
                                        ?? ''
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | Job Card Accessibility
                                |--------------------------------------------------------------------------
                                */

                                $canViewJobCard =
                                    $calibration
                                    && !empty($jobCard->id)
                                    && in_array(
                                        $calibrationStatus,
                                        $jobCardStatuses,
                                        true
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | Stored Job Card
                                |--------------------------------------------------------------------------
                                */

                                $hasStoredJobCard =
                                    $calibration
                                    && filled(
                                        $calibration->jobcard_form_path
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | Job Card Status Badge
                                |--------------------------------------------------------------------------
                                */

                                $jobStatus =
                                    (string) (
                                        $jobCard->status
                                        ?? ''
                                    );

                                $statusClass = match (
                                    $jobStatus
                                ) {
                                    'completed' =>
                                        'success',

                                    'allocated',
                                    'assigned' =>
                                        'info',

                                    'pending' =>
                                        'warning',

                                    'in_progress',
                                    'testing' =>
                                        'primary',

                                    'cancelled',
                                    'rejected' =>
                                        'danger',

                                    default =>
                                        'secondary',
                                };

                                /*
                                |--------------------------------------------------------------------------
                                | Calibration Status Badge
                                |--------------------------------------------------------------------------
                                */

                                $calibrationStatusClass = match (
                                    $calibrationStatus
                                ) {
                                    'completed',
                                    'certified',
                                    'paid' =>
                                        'success',

                                    'pending',
                                    'pending_payment' =>
                                        'warning',

                                    'quoted' =>
                                        'info',

                                    'assigned',
                                    'in_progress',
                                    'testing' =>
                                        'primary',

                                    'rejected' =>
                                        'danger',

                                    default =>
                                        'secondary',
                                };

                                /*
                                |--------------------------------------------------------------------------
                                | Assigned Technician
                                |--------------------------------------------------------------------------
                                */

                                $assignedTechnician =
                                    $jobCard->allocatedTo
                                    ?? $calibration?->performedBy;
                            @endphp


                            <tr>

                                {{-- ================================================= --}}
                                {{-- JOB NUMBER                                        --}}
                                {{-- ================================================= --}}

                                <td>
                                    <strong class="text-dark">
                                        {{
                                            $jobCard->job_number
                                            ?? $jobCard->job_card_number
                                            ?? 'N/A'
                                        }}
                                    </strong>
                                </td>


                                {{-- ================================================= --}}
                                {{-- CALIBRATION                                        --}}
                                {{-- ================================================= --}}

                                <td>

                                    @if($jobCard->calibration_id)

                                        <div>
                                            <span
                                                class="
                                                    badge
                                                    bg-light
                                                    text-dark
                                                    border
                                                "
                                            >
                                                #{{ $jobCard->calibration_id }}
                                            </span>
                                        </div>

                                        @if($calibrationStatus)
                                            <small class="d-block mt-1">
                                                <span
                                                    class="
                                                        badge
                                                        bg-{{ $calibrationStatusClass }}
                                                    "
                                                >
                                                    {{
                                                        ucfirst(
                                                            str_replace(
                                                                '_',
                                                                ' ',
                                                                $calibrationStatus
                                                            )
                                                        )
                                                    }}
                                                </span>
                                            </small>
                                        @endif

                                    @else

                                        <span class="text-muted">
                                            N/A
                                        </span>

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- EQUIPMENT                                          --}}
                                {{-- ================================================= --}}

                                <td>

                                    <strong>
                                        {{
                                            $calibration?->equipment_name
                                            ?? 'N/A'
                                        }}
                                    </strong>

                                    @if($calibration?->serial_number)

                                        <small class="d-block text-muted mt-1">
                                            Serial:
                                            <code>
                                                {{ $calibration->serial_number }}
                                            </code>
                                        </small>

                                    @endif

                                    @if($calibration?->manufacturer)

                                        <small class="d-block text-muted">
                                            {{ $calibration->manufacturer }}
                                        </small>

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- JOB CARD STATUS                                    --}}
                                {{-- ================================================= --}}

                                <td>

                                    <span
                                        class="
                                            badge
                                            bg-{{ $statusClass }}
                                        "
                                    >
                                        {{
                                            ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $jobStatus
                                                    ?: 'N/A'
                                                )
                                            )
                                        }}
                                    </span>

                                </td>


                                {{-- ================================================= --}}
                                {{-- ASSIGNED TECHNICIAN                                --}}
                                {{-- ================================================= --}}

                                <td>

                                    @if($assignedTechnician)

                                        <div class="d-flex align-items-center">

                                            <span
                                                class="
                                                    rounded-circle
                                                    bg-light
                                                    border
                                                    d-inline-flex
                                                    align-items-center
                                                    justify-content-center
                                                    me-2
                                                "
                                                style="
                                                    width:32px;
                                                    height:32px;
                                                "
                                            >
                                                <i
                                                    class="
                                                        fas
                                                        fa-user-cog
                                                        text-primary
                                                    "
                                                ></i>
                                            </span>

                                            <div>
                                                <strong>
                                                    {{
                                                        $assignedTechnician->name
                                                    }}
                                                </strong>

                                                @if(
                                                    $assignedTechnician->staff_number
                                                    ?? $assignedTechnician->employee_number
                                                )
                                                    <small
                                                        class="
                                                            d-block
                                                            text-muted
                                                        "
                                                    >
                                                        {{
                                                            $assignedTechnician->staff_number
                                                            ?? $assignedTechnician->employee_number
                                                        }}
                                                    </small>
                                                @endif
                                            </div>

                                        </div>

                                    @else

                                        <span class="text-muted">
                                            <i class="fas fa-user-clock me-1"></i>
                                            Not assigned
                                        </span>

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- DATE                                               --}}
                                {{-- ================================================= --}}

                                <td>

                                    {{
                                        $jobCard->created_at
                                            ?->format('d/m/Y')
                                        ?? 'N/A'
                                    }}

                                    @if($jobCard->created_at)
                                        <small class="d-block text-muted">
                                            {{
                                                $jobCard->created_at
                                                    ->format('H:i')
                                            }}
                                        </small>
                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- ACTIONS                                            --}}
                                {{-- ================================================= --}}

                                <td class="text-end">

                                    <div
                                        class="
                                            btn-group
                                            btn-group-sm
                                        "
                                        role="group"
                                    >

                                        {{-- ========================================= --}}
                                        {{-- VIEW CALIBRATION                          --}}
                                        {{-- ========================================= --}}

                                        @if($jobCard->calibration_id)

                                            <a
                                                href="{{ route(
                                                    'customer.calibrations.show',
                                                    $jobCard->calibration_id
                                                ) }}"
                                                class="
                                                    btn
                                                    btn-outline-info
                                                "
                                                title="View Calibration"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>

                                        @endif


                                        {{-- ========================================= --}}
                                        {{-- VIEW JOB CARD                             --}}
                                        {{-- ========================================= --}}

                                        @if($canViewJobCard)

                                            @if($hasStoredJobCard)

                                                {{-- Stored PDF --}}
                                                <a
                                                    href="{{ asset(
                                                        'storage/'
                                                        . $calibration
                                                            ->jobcard_form_path
                                                    ) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="
                                                        btn
                                                        btn-outline-primary
                                                    "
                                                    title="View Job Card"
                                                >
                                                    <i
                                                        class="
                                                            fas
                                                            fa-clipboard-list
                                                        "
                                                    ></i>
                                                </a>

                                            @else

                                                {{-- Generate once if not stored --}}
                                                <a
                                                    href="{{ route(
                                                        'calibrations.job-card',
                                                        $calibration
                                                    ) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="
                                                        btn
                                                        btn-outline-primary
                                                    "
                                                    title="Generate / View Job Card"
                                                >
                                                    <i
                                                        class="
                                                            fas
                                                            fa-clipboard-list
                                                        "
                                                    ></i>
                                                </a>

                                            @endif

                                        @else

                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-outline-secondary
                                                "
                                                disabled
                                                title="
                                                    Job Card becomes available
                                                    after technician assignment.
                                                "
                                            >
                                                <i class="fas fa-lock"></i>
                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            {{-- ===================================================== --}}
                            {{-- EMPTY STATE                                           --}}
                            {{-- ===================================================== --}}

                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-clipboard-list
                                            fa-3x
                                            text-muted
                                            mb-3
                                            d-block
                                        "
                                    ></i>

                                    <h5 class="text-muted">
                                        No Job Cards
                                    </h5>

                                    <p class="text-muted mb-0">
                                        No job cards are currently available
                                        for your calibration requests.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- PAGINATION                                                --}}
    {{-- ========================================================= --}}

    @if($jobCards->hasPages())

        <div class="mt-3">

            {{
                $jobCards
                    ->appends(
                        request()->query()
                    )
                    ->links(
                        
                    )
            }}

        </div>

    @endif

</div>

@endsection


@push('styles')
<style>
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }

    .table code {
        font-size: 0.82rem;
        color: #495057;
        background: #f8f9fa;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .card {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
    }

    .btn-group .btn {
        min-width: 38px;
    }

    @media (max-width: 767.98px) {
        .table {
            font-size: 0.88rem;
        }

        .card-header {
            padding: 0.85rem 1rem;
        }
    }
</style>
@endpush
