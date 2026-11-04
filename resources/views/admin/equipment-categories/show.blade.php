@extends('layouts.app')

@section('title', 'Equipment Category Details')

@section('content')
<div class="container-fluid equipment-category-show-page">

    <div class="card category-page-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-layer-group me-1"></i>
                        CATEGORY DETAILS
                    </span>

                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h2 class="fw-bold mb-0">
                            {{ $equipmentCategory->name }}
                        </h2>

                        <span class="badge bg-{{
                            $equipmentCategory->is_active
                                ? 'success'
                                : 'secondary'
                        }}">
                            {{
                                $equipmentCategory->is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </span>
                    </div>

                    <p class="mb-0 text-white-50">
                        {{ $equipmentCategory->code }}
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('edit_equipment_categories')
                        @if(Route::has('equipment-categories.edit'))
                            <a
                                href="{{ route(
                                    'equipment-categories.edit',
                                    $equipmentCategory
                                ) }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                        @endif
                    @endcan

                    <a
                        href="{{ route('equipment-categories.index') }}"
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

        {{-- Category details --}}
        <div class="col-lg-5">
            <div class="card category-detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-circle-info text-primary me-2"></i>
                        Category Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="category-detail-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <i class="fas fa-layer-group fa-3x"></i>
                    </div>

                    <div class="category-detail-row">
                        <span class="category-detail-label">
                            Category Name
                        </span>

                        <strong>
                            {{ $equipmentCategory->name }}
                        </strong>
                    </div>

                    <div class="category-detail-row">
                        <span class="category-detail-label">
                            Category Code
                        </span>

                        <span class="badge bg-primary">
                            {{ $equipmentCategory->code }}
                        </span>
                    </div>

                    <div class="category-detail-row">
                        <span class="category-detail-label">
                            Status
                        </span>

                        <span class="badge bg-{{
                            $equipmentCategory->is_active
                                ? 'success'
                                : 'secondary'
                        }}">
                            {{
                                $equipmentCategory->is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </span>
                    </div>

                    <div class="category-detail-row">
                        <span class="category-detail-label">
                            Equipment Records
                        </span>

                        <strong>
                            {{ number_format(
                                $equipmentCategory->equipment_count ?? 0
                            ) }}
                        </strong>
                    </div>

                    <div class="category-detail-row">
                        <span class="category-detail-label">
                            Created
                        </span>

                        <strong>
                            {{ $equipmentCategory->created_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="category-detail-row">
                        <span class="category-detail-label">
                            Last Updated
                        </span>

                        <strong>
                            {{ $equipmentCategory->updated_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold text-primary">
                            Description
                        </h6>

                        <div class="description-box">
                            @if(!empty($equipmentCategory->description))
                                {!! nl2br(
                                    e($equipmentCategory->description)
                                ) !!}
                            @else
                                <span class="text-muted">
                                    No category description has been provided.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white py-3">
                    @can('edit_equipment_categories')
                        @if(Route::has('equipment-categories.toggle-status'))
                            <form
                                action="{{ route(
                                    'equipment-categories.toggle-status',
                                    $equipmentCategory
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        '{{
                                            $equipmentCategory->is_active
                                                ? 'Deactivate this equipment category?'
                                                : 'Activate this equipment category?'
                                        }}'
                                    );
                                "
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-{{
                                        $equipmentCategory->is_active
                                            ? 'outline-warning'
                                            : 'success'
                                    }} w-100 mb-2"
                                >
                                    <i class="fas {{
                                        $equipmentCategory->is_active
                                            ? 'fa-pause'
                                            : 'fa-play'
                                    }} me-1"></i>

                                    {{
                                        $equipmentCategory->is_active
                                            ? 'Deactivate Category'
                                            : 'Activate Category'
                                    }}
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('delete_equipment_categories')
                        @if(
                            Route::has('equipment-categories.destroy')
                            && (
                                $equipmentCategory->equipment_count ?? 0
                            ) === 0
                        )
                            <form
                                action="{{ route(
                                    'equipment-categories.destroy',
                                    $equipmentCategory
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        'Delete this equipment category permanently?'
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
                                    Delete Category
                                </button>
                            </form>
                        @elseif(
                            (
                                $equipmentCategory->equipment_count ?? 0
                            ) > 0
                        )
                            <div class="alert alert-secondary small mb-0">
                                <i class="fas fa-lock me-1"></i>
                                This category cannot be deleted because
                                equipment records are assigned to it.
                            </div>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Equipment assigned to category --}}
        <div class="col-lg-7">
            <div class="card category-detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-tools text-primary me-2"></i>
                            Equipment in this Category
                        </h5>

                        <span class="badge bg-secondary">
                            {{ number_format(
                                $equipmentCategory->equipment_count ?? 0
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
                                    <th>Manufacturer</th>
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

                                        $manufacturerName =
                                            is_object(
                                                $equipment->manufacturer
                                                ?? null
                                            )
                                                ? (
                                                    $equipment
                                                        ->manufacturer
                                                        ?->name
                                                    ?? 'N/A'
                                                )
                                                : (
                                                    $equipment->manufacturer
                                                    ?? 'N/A'
                                                );
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $equipment->name
                                                    ?? 'Unnamed Equipment' }}
                                            </strong>

                                            @if(!empty($equipment->model))
                                                <small class="d-block text-muted">
                                                    Model:
                                                    {{ $equipment->model }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $equipment->equipment_number
                                                ?? $equipment->serial_number
                                                ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $manufacturerName }}
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
                                            <div class="category-empty-state">
                                                <i class="fas fa-tools fa-3x mb-3"></i>

                                                <h6>
                                                    No equipment assigned
                                                </h6>

                                                <p class="mb-0">
                                                    This category has not yet
                                                    been assigned to an
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
    .equipment-category-show-page {
        padding-bottom: 2rem;
    }

    .category-page-header {
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

    .category-detail-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .category-detail-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        border-radius: 50%;
    }

    .category-detail-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 0;
        border-bottom: 1px solid #edf0f4;
    }

    .category-detail-label {
        color: #6c757d;
    }

    .description-box {
        min-height: 95px;
        padding: 1rem;
        border: 1px solid #e4e8ed;
        border-radius: 0.65rem;
        background: #f8f9fa;
        line-height: 1.65;
    }

    .category-detail-card thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .category-empty-state {
        padding: 3rem 1rem;
        color: #6c757d;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .category-page-header .card-body {
            padding: 1.5rem !important;
        }

        .category-detail-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.25rem;
        }
    }
</style>
@endpush
