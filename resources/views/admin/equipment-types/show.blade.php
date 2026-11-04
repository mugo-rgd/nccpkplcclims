@extends('layouts.app')

@section('title', 'Equipment Type Details')

@section('content')
<div class="container-fluid equipment-type-show-page">

    {{-- Header --}}
    <div class="card page-header-card border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-tags me-1"></i>
                        PRICING DETAILS
                    </span>

                    <h2 class="fw-bold mb-2">
                        {{ $equipmentType->name }}
                    </h2>

                    <p class="mb-0 text-white-50">
                        Equipment type information, published rate and
                        calibration usage.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('edit_equipment_types')
                        @if(Route::has('equipment-types.edit'))
                            <a
                                href="{{ route(
                                    'equipment-types.edit',
                                    $equipmentType
                                ) }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                        @endif
                    @endcan

                    <a
                        href="{{ route('equipment-types.index') }}"
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

        {{-- Main details --}}
        <div class="col-lg-5">
            <div class="card detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-circle-info text-primary me-2"></i>
                        Pricing Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="detail-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <i class="fas fa-tags fa-3x"></i>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">
                            Equipment Type
                        </span>

                        <strong class="detail-value">
                            {{ $equipmentType->name }}
                        </strong>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">
                            Published Rate
                        </span>

                        <strong class="detail-value text-success">
                            {{ $equipmentType->formatted_rate }}
                        </strong>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">
                            Calibration Records
                        </span>

                        <strong class="detail-value">
                            {{ number_format(
                                $equipmentType->calibrations_count ?? 0
                            ) }}
                        </strong>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">
                            Created
                        </span>

                        <strong class="detail-value">
                            {{ $equipmentType->created_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">
                            Last Updated
                        </span>

                        <strong class="detail-value">
                            {{ $equipmentType->updated_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>
                </div>

                @can('delete_equipment_types')
                    <div class="card-footer bg-white py-3">
                        @if(
                            ($equipmentType->calibrations_count ?? 0) === 0
                            && Route::has('equipment-types.destroy')
                        )
                            <form
                                action="{{ route(
                                    'equipment-types.destroy',
                                    $equipmentType
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        'Delete this equipment type permanently?'
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
                                    Delete Equipment Type
                                </button>
                            </form>
                        @else
                            <div class="alert alert-secondary mb-0 small">
                                <i class="fas fa-lock me-1"></i>
                                This record cannot be deleted because it is
                                already used by calibration records.
                            </div>
                        @endif
                    </div>
                @endcan
            </div>
        </div>

        {{-- Recent calibrations --}}
        <div class="col-lg-7">
            <div class="card detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                            Recent Calibrations
                        </h5>

                        <span class="badge bg-secondary">
                            {{ number_format(
                                $equipmentType->calibrations_count ?? 0
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
                                    <th>Calibration</th>
                                    <th>Customer</th>
                                    <th>Equipment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($recentCalibrations as $calibration)
                                    @php
                                        $status = strtolower(
                                            (string) (
                                                $calibration->status
                                                ?? 'pending'
                                            )
                                        );

                                        $statusColor = match ($status) {
                                            'completed',
                                            'certified' => 'success',

                                            'testing',
                                            'quoted' => 'info',

                                            'assigned',
                                            'in_progress' => 'primary',

                                            'paid' => 'success',

                                            'rejected' => 'danger',

                                            default => 'secondary',
                                        };
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>
                                                #{{ $calibration->id }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $calibration
                                                ->customer
                                                ?->company_name
                                                ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $calibration->equipment_name
                                                ?? 'N/A' }}
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $statusColor }}">
                                                {{ ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $status
                                                    )
                                                ) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $calibration->created_at
                                                ?->format('d/m/Y')
                                                ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-flask-vial fa-3x mb-3"></i>

                                                <h6>
                                                    No calibrations found
                                                </h6>

                                                <p class="mb-0">
                                                    This equipment type has not
                                                    yet been used in a
                                                    calibration record.
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
    .equipment-type-show-page {
        padding-bottom: 2rem;
    }

    .page-header-card {
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

    .detail-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .detail-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        border-radius: 50%;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 0;
        border-bottom: 1px solid #edf0f4;
    }

    .detail-row:last-child {
        border-bottom: 0;
    }

    .detail-label {
        color: #6c757d;
    }

    .detail-value {
        text-align: right;
    }

    .detail-card thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .empty-state {
        padding: 3rem 1rem;
        color: #6c757d;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .page-header-card .card-body {
            padding: 1.5rem !important;
        }

        .detail-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-value {
            text-align: left;
        }
    }
</style>
@endpush
