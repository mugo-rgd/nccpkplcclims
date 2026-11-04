@extends('layouts.app')

@section('title', 'Delivery Notes')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Delivery note availability
    |--------------------------------------------------------------------------
    |
    | Once available from pending status, it remains accessible
    | throughout the later calibration workflow.
    |--------------------------------------------------------------------------
    */

    $deliveryNoteStatuses = [
        'pending',
        'quoted',
        'pending_payment',
        'paid',
        'assigned',
        'in_progress',
        'testing',
        'completed',
        'certified',
    ];
@endphp

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-truck-loading text-primary me-2"></i>
            Delivery Notes
        </h4>

        <small class="text-muted">
            View delivery notes issued for your calibration requests.
        </small>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>Delivery Number</th>
                        <th>Calibration</th>
                        <th>Equipment</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($deliveryNotes as $note)

                        @php
                            $currentStatus =
                                (string) (
                                    $note->status
                                    ?? ''
                                );

                            /*
                             * Delivery note is accessible once:
                             *
                             * - delivery_number exists
                             * - calibration has reached pending or later
                             */
                            $canViewDeliveryNote =
                                filled($note->delivery_number)
                                && in_array(
                                    $currentStatus,
                                    $deliveryNoteStatuses,
                                    true
                                );

                            /*
                             * Prefer the stored PDF where available.
                             */
                            $hasStoredDeliveryNote =
                                filled(
                                    $note->delivery_note_path
                                );

                            $statusClass = match (
                                $currentStatus
                            ) {
                                'pending',
                                'pending_payment' =>
                                    'warning',

                                'quoted' =>
                                    'info',

                                'paid',
                                'completed',
                                'certified' =>
                                    'success',

                                'assigned',
                                'in_progress',
                                'testing' =>
                                    'primary',

                                'rejected' =>
                                    'danger',

                                default =>
                                    'secondary',
                            };
                        @endphp

                        <tr>

                            {{-- Delivery Number --}}
                            <td>
                                <strong>
                                    {{
                                        $note->delivery_number
                                        ?? 'N/A'
                                    }}
                                </strong>
                            </td>

                            {{-- Calibration --}}
                            <td>
                                <span
                                    class="
                                        badge
                                        bg-light
                                        text-dark
                                        border
                                    "
                                >
                                    #{{ $note->id }}
                                </span>
                            </td>

                            {{-- Equipment --}}
                            <td>
                                {{
                                    $note->equipment_name
                                    ?? 'N/A'
                                }}
                            </td>

                            {{-- Serial Number --}}
                            <td>
                                <code>
                                    {{
                                        $note->serial_number
                                        ?? 'N/A'
                                    }}
                                </code>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge bg-{{ $statusClass }}">
                                    {{
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $currentStatus
                                                ?: 'N/A'
                                            )
                                        )
                                    }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td>
                                {{
                                    $note->created_at
                                        ?->format('d/m/Y')
                                    ?? 'N/A'
                                }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">

                                <div
                                    class="btn-group btn-group-sm"
                                    role="group"
                                >

                                    {{-- View calibration --}}
                                    <a
                                        href="{{ route(
                                            'customer.calibrations.show',
                                            $note->id
                                        ) }}"
                                        class="btn btn-outline-info"
                                        title="View Calibration"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- View Delivery Note --}}
                                    @if($canViewDeliveryNote)

                                        @if($hasStoredDeliveryNote)

                                            <a
                                                href="{{ asset(
                                                    'storage/'
                                                    . $note->delivery_note_path
                                                ) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-primary"
                                                title="View Delivery Note"
                                            >
                                                <i class="fas fa-truck-loading"></i>
                                            </a>

                                        @else

                                            <a
                                                href="{{ route(
                                                    'calibrations.delivery-note',
                                                    $note
                                                ) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-primary"
                                                title="Generate / View Delivery Note"
                                            >
                                                <i class="fas fa-truck-loading"></i>
                                            </a>

                                        @endif

                                    @else

                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            disabled
                                            title="Delivery note is not yet available."
                                        >
                                            <i class="fas fa-lock"></i>
                                        </button>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-5"
                            >
                                <i
                                    class="
                                        fas
                                        fa-truck-loading
                                        fa-3x
                                        text-muted
                                        mb-3
                                        d-block
                                    "
                                ></i>

                                <p class="text-muted mb-0">
                                    No delivery notes found.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@if($deliveryNotes->hasPages())
    <div class="mt-3">
        {{
            $deliveryNotes
                ->appends(
                    request()->query()
                )
                ->links(
                    
                )
        }}
    </div>
@endif

@endsection
