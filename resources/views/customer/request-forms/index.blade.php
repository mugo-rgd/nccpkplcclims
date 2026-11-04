@extends('layouts.app')

@section('title', 'Request Forms')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Request form availability
    |--------------------------------------------------------------------------
    |
    | Request Form is available from pending status
    | and remains accessible throughout the workflow.
    |--------------------------------------------------------------------------
    */

    $requestFormStatuses = [
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
            <i class="fas fa-file-signature text-primary me-2"></i>
            Request Forms
        </h4>

        <small class="text-muted">
            View request forms submitted for your calibration requests.
        </small>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>Request ID</th>
                        <th>Equipment</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($requestForms as $form)

                        @php
                            $currentStatus =
                                (string) (
                                    $form->status
                                    ?? ''
                                );

                            /*
                             * Request Form remains accessible once the
                             * calibration is pending or has progressed
                             * beyond pending.
                             */
                            $canViewRequestForm =
                                in_array(
                                    $currentStatus,
                                    $requestFormStatuses,
                                    true
                                );

                            /*
                             * Prefer stored PDF.
                             */
                            $hasStoredRequestForm =
                                filled(
                                    $form->request_form_path
                                );

                            /*
                             * Status badge.
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
                        @endphp

                        <tr>

                            {{-- Request ID --}}
                            <td>
                                <strong>
                                    #{{ $form->id }}
                                </strong>
                            </td>

                            {{-- Equipment --}}
                            <td>
                                {{
                                    $form->equipment_name
                                    ?? 'N/A'
                                }}

                                @if($form->manufacturer)
                                    <small class="d-block text-muted">
                                        {{ $form->manufacturer }}
                                    </small>
                                @endif
                            </td>

                            {{-- Serial Number --}}
                            <td>
                                <code>
                                    {{
                                        $form->serial_number
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
                                    $form->created_at
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
                                            $form->id
                                        ) }}"
                                        class="btn btn-outline-info"
                                        title="View Calibration"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- View Request Form --}}
                                    @if($canViewRequestForm)

                                        @if($hasStoredRequestForm)

                                            <a
                                                href="{{ asset(
                                                    'storage/'
                                                    . $form->request_form_path
                                                ) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-primary"
                                                title="View Request Form"
                                            >
                                                <i class="fas fa-file-signature"></i>
                                            </a>

                                        @else

                                            <a
                                                href="{{ route(
                                                    'calibrations.request-form',
                                                    $form
                                                ) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-primary"
                                                title="Generate / View Request Form"
                                            >
                                                <i class="fas fa-file-signature"></i>
                                            </a>

                                        @endif

                                    @else

                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            disabled
                                            title="Request form is not available at this stage."
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
                                colspan="6"
                                class="text-center py-5"
                            >
                                <i
                                    class="
                                        fas
                                        fa-file-signature
                                        fa-3x
                                        text-muted
                                        mb-3
                                        d-block
                                    "
                                ></i>

                                <p class="text-muted mb-0">
                                    No request forms found.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@if($requestForms->hasPages())
    <div class="mt-3">
        {{
            $requestForms
                ->appends(
                    request()->query()
                )
                ->links(
                    
                )
        }}
    </div>
@endif

@endsection
