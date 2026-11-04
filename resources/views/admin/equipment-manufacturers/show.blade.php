@extends('layouts.app')

@section('title', 'Equipment Manufacturer Details')

@section('content')
<div class="container-fluid equipment-manufacturer-show-page">

    <div class="card manufacturer-page-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-industry me-1"></i>
                        MANUFACTURER DETAILS
                    </span>

                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h2 class="fw-bold mb-0">
                            {{ $equipmentManufacturer->name }}
                        </h2>

                        <span class="badge bg-{{
                            $equipmentManufacturer->is_active
                                ? 'success'
                                : 'secondary'
                        }}">
                            {{
                                $equipmentManufacturer->is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </span>
                    </div>

                    <p class="mb-0 text-white-50">
                        {{ $equipmentManufacturer->code }}
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('edit_equipment_manufacturers')
                        @if(Route::has('equipment-manufacturers.edit'))
                            <a
                                href="{{ route(
                                    'equipment-manufacturers.edit',
                                    $equipmentManufacturer
                                ) }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                        @endif
                    @endcan

                    <a
                        href="{{ route('equipment-manufacturers.index') }}"
                        class="btn btn-light"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-circle-exclamation me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- Manufacturer information --}}
        <div class="col-lg-5">
            <div class="card manufacturer-detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-circle-info text-primary me-2"></i>
                        Manufacturer Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="manufacturer-detail-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <i class="fas fa-industry fa-3x"></i>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Manufacturer
                        </span>

                        <strong>
                            {{ $equipmentManufacturer->name }}
                        </strong>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Code
                        </span>

                        <span class="badge bg-primary">
                            {{ $equipmentManufacturer->code }}
                        </span>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Status
                        </span>

                        <span class="badge bg-{{
                            $equipmentManufacturer->is_active
                                ? 'success'
                                : 'secondary'
                        }}">
                            {{
                                $equipmentManufacturer->is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </span>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Equipment Records
                        </span>

                        <strong>
                            {{ number_format(
                                $equipmentManufacturer->equipment_count
                                ?? 0
                            ) }}
                        </strong>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Contact Person
                        </span>

                        <strong>
                            {{ $equipmentManufacturer->contact_person
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Contact Phone
                        </span>

                        <strong>
                            {{ $equipmentManufacturer->contact_phone
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Contact Email
                        </span>

                        <div class="text-end">
                            @if(!empty(
                                $equipmentManufacturer->contact_email
                            ))
                                <a
                                    href="mailto:{{
                                        $equipmentManufacturer
                                            ->contact_email
                                    }}"
                                >
                                    {{
                                        $equipmentManufacturer
                                            ->contact_email
                                    }}
                                </a>
                            @else
                                <strong>N/A</strong>
                            @endif
                        </div>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Website
                        </span>

                        <div class="text-end">
                            @if(!empty($equipmentManufacturer->website))
                                <a
                                    href="{{
                                        $equipmentManufacturer->website
                                    }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Visit Website
                                    <i class="fas fa-up-right-from-square ms-1"></i>
                                </a>
                            @else
                                <strong>N/A</strong>
                            @endif
                        </div>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Created
                        </span>

                        <strong>
                            {{ $equipmentManufacturer->created_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="manufacturer-detail-row">
                        <span class="manufacturer-detail-label">
                            Last Updated
                        </span>

                        <strong>
                            {{ $equipmentManufacturer->updated_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold text-primary">
                            Address
                        </h6>

                        <div class="manufacturer-address-box">
                            @if(!empty($equipmentManufacturer->address))
                                {!! nl2br(
                                    e($equipmentManufacturer->address)
                                ) !!}
                            @else
                                <span class="text-muted">
                                    No address has been provided.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white py-3">
                    @can('edit_equipment_manufacturers')
                        @if(Route::has(
                            'equipment-manufacturers.toggle-status'
                        ))
                            <form
                                action="{{ route(
                                    'equipment-manufacturers.toggle-status',
                                    $equipmentManufacturer
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        '{{
                                            $equipmentManufacturer
                                                ->is_active
                                                ? 'Deactivate this manufacturer?'
                                                : 'Activate this manufacturer?'
                                        }}'
                                    );
                                "
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-{{
                                        $equipmentManufacturer->is_active
                                            ? 'outline-warning'
                                            : 'success'
                                    }} w-100 mb-2"
                                >
                                    <i class="fas {{
                                        $equipmentManufacturer->is_active
                                            ? 'fa-pause'
                                            : 'fa-play'
                                    }} me-1"></i>

                                    {{
                                        $equipmentManufacturer->is_active
                                            ? 'Deactivate Manufacturer'
                                            : 'Activate Manufacturer'
                                    }}
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('delete_equipment_manufacturers')
                        @if(
                            Route::has(
                                'equipment-manufacturers.destroy'
                            )
                            && (
                                $equipmentManufacturer->equipment_count
                                ?? 0
                            ) === 0
                        )
                            <form
                                action="{{ route(
                                    'equipment-manufacturers.destroy',
                                    $equipmentManufacturer
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        'Delete this manufacturer permanently?'
                                    );
                                "
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger w-100"
                                >
                                    <i class="fas fa-trash me-1"></i>
                                    Delete Manufacturer
                                </button>
                            </form>
                        @elseif(
                            (
                                $equipmentManufacturer->equipment_count
                                ?? 0
                            ) > 0
                        )
                            <div class="alert alert-secondary small mb-0">
                                <i class="fas fa-lock me-1"></i>
                                This manufacturer cannot be deleted because
                                equipment records are assigned to it.
                            </div>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Equipment supplied by manufacturer --}}
        <div class="col-lg-7">
            <div class="card manufacturer-detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-tools text-primary me-2"></i>
                            Equipment by this Manufacturer
                        </h5>

                        <span class="badge bg-secondary">
                            {{ number_format(
                                $equipmentManufacturer->equipment_count
                                ?? 0
                            ) }}
                            total
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Number / Serial</th>
                                    <th>Model</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($recentEquipment as $equipment)
                                    @php
                                        $equipmentStatus = strtolower(
                                            (string) (
                                                $equipment->status
                                                ?? 'unknown'
                                            )
                                        );

                                        $equipmentStatusColor =
                                            match ($equipmentStatus) {
                                                'active' => 'success',
                                                'in_use' => 'warning',
                                                'under_repair' => 'danger',
                                                'retired' => 'secondary',
                                                default => 'secondary',
                                            };
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $equipment->name
                                                    ?? 'Unnamed Equipment' }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $equipment->equipment_number
                                                ?? $equipment->serial_number
                                                ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $equipment->model ?? 'N/A' }}
                                        </td>

                                        <td>
                                            <span class="badge bg-{{
                                                $equipmentStatusColor
                                            }}">
                                                {{ ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $equipmentStatus
                                                    )
                                                ) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $equipment->created_at
                                                ?->format('d/m/Y')
                                                ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="manufacturer-empty-state">
                                                <i class="fas fa-tools fa-3x mb-3"></i>

                                                <h6>
                                                    No equipment assigned
                                                </h6>

                                                <p class="mb-0">
                                                    This manufacturer has not
                                                    yet been assigned to any
                                                    equipment record.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .equipment-manufacturer-show-page {
        padding-bottom: 2rem;
    }

    .manufacturer-page-header {
        overflow: hidden;
        border-radius: 1rem;
        background:
            linear-gradient(
                135deg,
                #06366f 0%,
                #0057b8 60%,
                #031d40 100%
            );
        box-shadow: 0 12px 35px rgba(0, 87, 184, 0.22);
    }

    .manufacturer-detail-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .manufacturer-detail-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        border-radius: 50%;
    }

    .manufacturer-detail-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 0;
        border-bottom: 1px solid #edf0f4;
    }

    .manufacturer-detail-label {
        color: #6c757d;
    }

    .manufacturer-address-box {
        min-height: 95px;
        padding: 1rem;
        border: 1px solid #e4e8ed;
        border-radius: 0.65rem;
        background: #f8f9fa;
        line-height: 1.65;
    }

    .manufacturer-detail-card thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .manufacturer-empty-state {
        padding: 3rem 1rem;
        color: #6c757d;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .manufacturer-page-header .card-body {
            padding: 1.5rem !important;
        }

        .manufacturer-detail-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.25rem;
        }

        .manufacturer-detail-row .text-end {
            text-align: left !important;
        }
    }
</style>
@endpush
