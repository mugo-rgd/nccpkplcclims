@extends('layouts.app')

@section('title', 'Equipment Types and Pricing')

@section('content')
@php
    $currentUser = auth()->user();
@endphp

<div class="container-fluid equipment-type-page">

    {{-- ===================================================== --}}
    {{-- PAGE HEADER                                           --}}
    {{-- ===================================================== --}}
    <div class="card page-header-card border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-tags me-1"></i>
                        LABORATORY CONFIGURATION
                    </span>

                    <h2 class="fw-bold mb-2">
                        Equipment Types and Pricing
                    </h2>

                    <p class="mb-0 text-white-50">
                        Manage public laboratory equipment categories and
                        their calibration or testing rates.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('laboratory-configuration.index'))
                        <a
                            href="{{ route('laboratory-configuration.index') }}"
                            class="btn btn-light"
                        >
                            <i class="fas fa-arrow-left me-1"></i>
                            Configuration
                        </a>
                    @endif

                    @can('create_equipment_types')
                        @if(Route::has('equipment-types.create'))
                            <a
                                href="{{ route('equipment-types.create') }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-plus-circle me-1"></i>
                                Add Equipment Type
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- FLASH MESSAGES                                        --}}
    {{-- ===================================================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- STATISTICS                                            --}}
    {{-- ===================================================== --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-layer-group"></i>
                    </div>

                    <div>
                        <small class="text-muted">Total Types</small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['total'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-calculator"></i>
                    </div>

                    <div>
                        <small class="text-muted">Average Rate</small>

                        <h5 class="fw-bold mb-0 text-success">
                            KES {{
                                number_format(
                                    $statistics['average_rate'] ?? 0,
                                    2
                                )
                            }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-arrow-down"></i>
                    </div>

                    <div>
                        <small class="text-muted">Minimum Rate</small>

                        <h5 class="fw-bold mb-0">
                            KES {{
                                number_format(
                                    $statistics['minimum_rate'] ?? 0,
                                    2
                                )
                            }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-arrow-up"></i>
                    </div>

                    <div>
                        <small class="text-muted">Maximum Rate</small>

                        <h5 class="fw-bold mb-0">
                            KES {{
                                number_format(
                                    $statistics['maximum_rate'] ?? 0,
                                    2
                                )
                            }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- SEARCH                                                --}}
    {{-- ===================================================== --}}
    <div class="card table-card border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-list text-primary me-2"></i>
                        Equipment Type Pricing List
                    </h5>

                    <small class="text-muted">
                        Published rates displayed on the public landing page.
                    </small>
                </div>

                <form
                    action="{{ route('equipment-types.index') }}"
                    method="GET"
                    class="d-flex gap-2"
                >
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="search"
                            name="search"
                            value="{{ $search ?? request('search') }}"
                            class="form-control"
                            placeholder="Search name or rate"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>

                    @if(!empty($search))
                        <a
                            href="{{ route('equipment-types.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Equipment Type</th>
                            <th class="text-end">Published Rate</th>
                            <th class="text-center">Calibrations</th>
                            <th class="text-center" style="width: 210px;">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($equipmentTypes as $equipmentType)
                            <tr>
                                <td class="text-muted">
                                    {{
                                        $equipmentTypes->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="type-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="fas fa-tags"></i>
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $equipmentType->name }}
                                            </strong>

                                            <small class="d-block text-muted">
                                                Updated
                                                {{
                                                    $equipmentType->updated_at
                                                        ?->diffForHumans()
                                                    ?? 'recently'
                                                }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <span class="rate-value">
                                        {{ $equipmentType->formatted_rate }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        {{
                                            number_format(
                                                $equipmentType
                                                    ->calibrations_count
                                                ?? 0
                                            )
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @can('view_equipment_types')
                                            @if(Route::has('equipment-types.show'))
                                                <a
                                                    href="{{
                                                        route(
                                                            'equipment-types.show',
                                                            $equipmentType
                                                        )
                                                    }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                        @endcan

                                        @can('edit_equipment_types')
                                            @if(Route::has('equipment-types.edit'))
                                                <a
                                                    href="{{
                                                        route(
                                                            'equipment-types.edit',
                                                            $equipmentType
                                                        )
                                                    }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        @endcan

                                        @can('delete_equipment_types')
                                            @if(Route::has('equipment-types.destroy'))
                                                <form
                                                    action="{{
                                                        route(
                                                            'equipment-types.destroy',
                                                            $equipmentType
                                                        )
                                                    }}"
                                                    method="POST"
                                                    onsubmit="
                                                        return confirm(
                                                            'Delete this equipment type?'
                                                        );
                                                    "
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        {{
                                                            ($equipmentType
                                                                ->calibrations_count
                                                                ?? 0) > 0
                                                                    ? 'disabled'
                                                                    : ''
                                                        }}
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-tags fa-3x mb-3"></i>

                                        <h5>No equipment types found</h5>

                                        <p class="mb-3">
                                            No pricing records match the
                                            selected search.
                                        </p>

                                        @can('create_equipment_types')
                                            @if(Route::has('equipment-types.create'))
                                                <a
                                                    href="{{ route('equipment-types.create') }}"
                                                    class="btn btn-primary"
                                                >
                                                    <i class="fas fa-plus me-1"></i>
                                                    Add First Equipment Type
                                                </a>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($equipmentTypes->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Showing
                        {{ $equipmentTypes->firstItem() }}
                        to
                        {{ $equipmentTypes->lastItem() }}
                        of
                        {{ $equipmentTypes->total() }}
                        records
                    </small>

                    {{ $equipmentTypes->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .equipment-type-page {
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

    .stat-card,
    .table-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .stat-icon,
    .type-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        font-size: 1.3rem;
    }

    .type-icon {
        width: 42px;
        height: 42px;
    }

    .rate-value {
        color: #198754;
        font-size: 1rem;
        font-weight: 700;
    }

    .table-card thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.82rem;
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

        .table-card .card-header form {
            width: 100%;
            flex-wrap: wrap;
        }

        .table-card .card-header .input-group {
            flex: 1 1 100%;
        }
    }
</style>
@endpush
