@extends('layouts.app')

@section('title', 'My Calibrations')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-microscope text-primary me-2"></i>
            My Calibrations
        </h4>

        <small class="text-muted">
            Track the progress of your calibration requests.
        </small>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Equipment</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Assigned Technician</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($calibrations as $cal)

                        @php
                            $currentStatus =
                                (string) (
                                    $cal->status
                                    ?? ''
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Status badge
                            |--------------------------------------------------------------------------
                            */

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

                            /*
                            |--------------------------------------------------------------------------
                            | Payment state
                            |--------------------------------------------------------------------------
                            */

                            $paymentStatus =
                                (string) (
                                    $cal->payment_status
                                    ?? 'pending'
                                );

                            $paymentClass = match (
                                $paymentStatus
                            ) {
                                'paid' =>
                                    'success',

                                'partial' =>
                                    'info',

                                'pending' =>
                                    'warning',

                                'overdue',
                                'failed' =>
                                    'danger',

                                'refunded' =>
                                    'secondary',

                                default =>
                                    'secondary',
                            };

                            /*
                            |--------------------------------------------------------------------------
                            | Document milestone helpers
                            |--------------------------------------------------------------------------
                            */

                            $canViewRequestForm =
                                in_array(
                                    $currentStatus,
                                    [
                                        'pending',
                                        'quoted',
                                        'pending_payment',
                                        'paid',
                                        'assigned',
                                        'in_progress',
                                        'testing',
                                        'completed',
                                        'certified',
                                    ],
                                    true
                                );

                            $canViewQuotation =
                                in_array(
                                    $currentStatus,
                                    [
                                        'quoted',
                                        'pending_payment',
                                        'paid',
                                        'assigned',
                                        'in_progress',
                                        'testing',
                                        'completed',
                                        'certified',
                                    ],
                                    true
                                );

                            $canViewJobCard =
                                filled($cal->performed_by)
                                && in_array(
                                    $currentStatus,
                                    [
                                        'assigned',
                                        'in_progress',
                                        'testing',
                                        'completed',
                                        'certified',
                                    ],
                                    true
                                );

                            $canViewCollectionNote =
                                in_array(
                                    $currentStatus,
                                    [
                                        'completed',
                                        'certified',
                                    ],
                                    true
                                );

                            $canViewDeliveryNote =
                                $cal->calibration_location !== 'on_site'
                                && in_array(
                                    $currentStatus,
                                    [
                                        'pending',
                                        'quoted',
                                        'pending_payment',
                                        'paid',
                                        'assigned',
                                        'in_progress',
                                        'testing',
                                        'completed',
                                        'certified',
                                    ],
                                    true
                                );
                        @endphp

                        <tr>

                            {{-- Calibration ID --}}
                            <td>
                                <strong>
                                    #{{ $cal->id }}
                                </strong>
                            </td>

                            {{-- Equipment --}}
                            <td>
                                {{
                                    $cal->equipment_name
                                    ?? 'N/A'
                                }}

                                @if($cal->manufacturer)
                                    <small class="d-block text-muted">
                                        {{ $cal->manufacturer }}
                                    </small>
                                @endif
                            </td>

                            {{-- Serial Number --}}
                            <td>
                                <code>
                                    {{
                                        $cal->serial_number
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

                            {{-- Payment --}}
                            <td>
                                @if($cal->requires_payment)

                                    <span class="badge bg-{{ $paymentClass }}">
                                        {{
                                            ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $paymentStatus
                                                )
                                            )
                                        }}
                                    </span>

                                    @if(
                                        (float) ($cal->amount_due ?? 0) > 0
                                    )
                                        <small class="d-block text-muted mt-1">
                                            KES
                                            {{
                                                number_format(
                                                    (float) $cal->amount_due,
                                                    2
                                                )
                                            }}
                                        </small>
                                    @endif

                                @else

                                    <span class="badge bg-secondary">
                                        Not Required
                                    </span>

                                @endif
                            </td>

                            {{-- Assigned Technician --}}
                            <td>
                                {{
                                    $cal->performedBy?->name
                                    ?? 'Not Assigned'
                                }}
                            </td>

                            {{-- Date --}}
                            <td>
                                {{
                                    $cal->created_at
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

                                    {{-- View Calibration --}}
                                    <a
                                        href="{{ route(
                                            'customer.calibrations.show',
                                            $cal
                                        ) }}"
                                        class="btn btn-outline-info"
                                        title="View Calibration"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Documents Dropdown --}}
                                    <button
                                        type="button"
                                        class="
                                            btn
                                            btn-outline-primary
                                            dropdown-toggle
                                        "
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="Documents"
                                    >
                                        <i class="fas fa-file-alt"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">

                                        {{-- Request Form --}}
                                        @if($canViewRequestForm)
                                            <li>
                                                @if(
                                                    filled(
                                                        $cal->request_form_path
                                                    )
                                                )
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ asset(
                                                            'storage/'
                                                            . $cal->request_form_path
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-file-signature text-primary me-2"></i>
                                                        Request Form
                                                    </a>
                                                @else
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ route(
                                                            'calibrations.request-form',
                                                            $cal
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-file-signature text-primary me-2"></i>
                                                        Request Form
                                                    </a>
                                                @endif
                                            </li>
                                        @endif


                                        {{-- Delivery Note --}}
                                        @if($canViewDeliveryNote)
                                            <li>
                                                @if(
                                                    filled(
                                                        $cal->delivery_note_path
                                                    )
                                                )
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ asset(
                                                            'storage/'
                                                            . $cal->delivery_note_path
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-truck-loading text-warning me-2"></i>
                                                        Delivery Note
                                                    </a>
                                                @else
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ route(
                                                            'calibrations.delivery-note',
                                                            $cal
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-truck-loading text-warning me-2"></i>
                                                        Delivery Note
                                                    </a>
                                                @endif
                                            </li>
                                        @endif


                                        {{-- Quotation --}}
                                        @if($canViewQuotation)
                                            <li>
                                                <a
                                                    class="dropdown-item"
                                                    href="{{ route(
                                                        'calibrations.quotation',
                                                        $cal
                                                    ) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <i class="fas fa-file-invoice-dollar text-success me-2"></i>
                                                    Quotation
                                                </a>
                                            </li>
                                        @endif


                                        {{-- Job Card --}}
                                        @if($canViewJobCard)
                                            <li>
                                                @if(
                                                    filled(
                                                        $cal->jobcard_form_path
                                                    )
                                                )
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ asset(
                                                            'storage/'
                                                            . $cal->jobcard_form_path
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-clipboard-list text-info me-2"></i>
                                                        Job Card
                                                    </a>
                                                @else
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ route(
                                                            'calibrations.job-card',
                                                            $cal
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-clipboard-list text-info me-2"></i>
                                                        Job Card
                                                    </a>
                                                @endif
                                            </li>
                                        @endif


                                        {{-- Collection Note --}}
                                        @if($canViewCollectionNote)
                                            <li>
                                                @if(
                                                    filled(
                                                        $cal->collection_note_path
                                                    )
                                                )
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ asset(
                                                            'storage/'
                                                            . $cal->collection_note_path
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-hand-holding text-success me-2"></i>
                                                        Collection Note
                                                    </a>
                                                @else
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ route(
                                                            'calibrations.collection-note',
                                                            $cal
                                                        ) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="fas fa-hand-holding text-success me-2"></i>
                                                        Collection Note
                                                    </a>
                                                @endif
                                            </li>
                                        @endif

                                    </ul>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                class="text-center py-5"
                            >
                                <i
                                    class="
                                        fas
                                        fa-microscope
                                        fa-3x
                                        text-muted
                                        mb-3
                                        d-block
                                    "
                                ></i>

                                <p class="text-muted mb-0">
                                    No calibrations found.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@if($calibrations->hasPages())
    <div class="mt-3">
        {{
            $calibrations
                ->appends(
                    request()->query()
                )
                ->links(
                    
                )
        }}
    </div>
@endif

@endsection
