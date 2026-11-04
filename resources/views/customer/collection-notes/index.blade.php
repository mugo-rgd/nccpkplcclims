@extends('layouts.app')

@section('title', 'Collection Notes')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Collection note availability
    |--------------------------------------------------------------------------
    |
    | Once the calibration reaches completed status,
    | the collection note remains accessible afterwards.
    |--------------------------------------------------------------------------
    */

    $collectionNoteStatuses = [
        'completed',
        'certified',
    ];
@endphp

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-hand-holding text-primary me-2"></i>
            Collection Notes
        </h4>

        <small class="text-muted">
            View collection notes issued after completion of your calibration work.
        </small>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>Collection Number</th>
                        <th>Calibration</th>
                        <th>Equipment</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($collectionNotes as $note)

                        @php
                            $currentStatus =
                                (string) (
                                    $note->status
                                    ?? ''
                                );

                            /*
                             * Collection note becomes available only after
                             * completion and remains available when certified.
                             */
                            $canViewCollectionNote =
                                filled($note->collection_number)
                                && in_array(
                                    $currentStatus,
                                    $collectionNoteStatuses,
                                    true
                                );

                            /*
                             * Prefer the stored PDF.
                             */
                            $hasStoredCollectionNote =
                                filled(
                                    $note->collection_note_path
                                );

                            $statusClass = match (
                                $currentStatus
                            ) {
                                'completed',
                                'certified' =>
                                    'success',

                                'testing',
                                'in_progress' =>
                                    'primary',

                                'assigned' =>
                                    'info',

                                'pending',
                                'pending_payment' =>
                                    'warning',

                                'rejected' =>
                                    'danger',

                                default =>
                                    'secondary',
                            };
                        @endphp

                        <tr>

                            {{-- Collection Number --}}
                            <td>
                                <strong>
                                    {{
                                        $note->collection_number
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

                                    {{-- View collection note --}}
                                    @if($canViewCollectionNote)

                                        @if($hasStoredCollectionNote)

                                            <a
                                                href="{{ asset(
                                                    'storage/'
                                                    . $note->collection_note_path
                                                ) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-success"
                                                title="View Collection Note"
                                            >
                                                <i class="fas fa-hand-holding"></i>
                                            </a>

                                        @else

                                            <a
                                                href="{{ route(
                                                    'calibrations.collection-note',
                                                    $note
                                                ) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-success"
                                                title="Generate / View Collection Note"
                                            >
                                                <i class="fas fa-hand-holding"></i>
                                            </a>

                                        @endif

                                    @else

                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            disabled
                                            title="Collection note becomes available after the calibration is completed."
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
                                        fa-hand-holding
                                        fa-3x
                                        text-muted
                                        mb-3
                                        d-block
                                    "
                                ></i>

                                <p class="text-muted mb-0">
                                    No collection notes found.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@if($collectionNotes->hasPages())
    <div class="mt-3">
        {{
            $collectionNotes
                ->appends(
                    request()->query()
                )
                ->links(
                    
                )
        }}
    </div>
@endif

@endsection
