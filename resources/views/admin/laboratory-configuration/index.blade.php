@extends('layouts.app')

@section('title', 'Laboratory Configuration')

@section('content')
@php
    $currentUser = auth()->user();
@endphp

<div class="container-fluid configuration-page">

    {{-- ===================================================== --}}
    {{-- PAGE HEADER                                           --}}
    {{-- ===================================================== --}}
    <div class="card configuration-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fas fa-sliders-h me-1"></i>
                            MASTER DATA
                        </span>
                    </div>

                    <h2 class="fw-bold mb-2">
                        <i class="fas fa-gears me-2"></i>
                        Laboratory Configuration
                    </h2>

                    <p class="mb-0 text-white-50">
                        Manage laboratory services, equipment pricing,
                        equipment categories and approved manufacturers.
                    </p>
                </div>

                <div class="col-lg-4 text-center d-none d-lg-block">
                    <div class="configuration-header-icon">
                        <i class="fas fa-screwdriver-wrench fa-4x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- SUMMARY CARDS                                         --}}
    {{-- ===================================================== --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card configuration-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="configuration-stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-flask-vial"></i>
                        </div>

                        <div>
                            <p class="text-muted mb-1">
                                Laboratory Services
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ number_format($serviceTypeCount ?? 0) }}
                            </h3>
                        </div>
                    </div>

                    <small class="text-success d-block mt-3">
                        <i class="fas fa-circle-check me-1"></i>
                        {{ number_format($activeServiceTypeCount ?? 0) }}
                        active services
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card configuration-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="configuration-stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-tags"></i>
                        </div>

                        <div>
                            <p class="text-muted mb-1">
                                Equipment Pricing
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ number_format($equipmentTypeCount ?? 0) }}
                            </h3>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-3">
                        Published equipment categories and rates
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card configuration-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="configuration-stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-layer-group"></i>
                        </div>

                        <div>
                            <p class="text-muted mb-1">
                                Equipment Categories
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ number_format($equipmentCategoryCount ?? 0) }}
                            </h3>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-3">
                        Classification used during registration
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card configuration-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="configuration-stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-industry"></i>
                        </div>

                        <div>
                            <p class="text-muted mb-1">
                                Manufacturers
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ number_format(
                                    $equipmentManufacturerCount ?? 0
                                ) }}
                            </h3>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-3">
                        Approved equipment manufacturers
                    </small>
                </div>
            </div>
        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CONFIGURATION MODULES                                 --}}
    {{-- ===================================================== --}}
    <div class="row g-4 mb-4">

        @can('view_laboratory_configuration_audit')
            @if(Route::has('laboratory-configuration.audit'))
                <div class="col-12">
                    <div class="configuration-toolbar">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <i class="fas fa-shield-halved text-primary me-2"></i>
                                Configuration Administration
                            </h5>

                            <small class="text-muted">
                                Review master-data changes and equipment configuration status.
                            </small>
                        </div>

                        <a
                            href="{{ route('laboratory-configuration.audit') }}"
                            class="btn btn-primary audit-trail-button"
                        >
                            <i class="fas fa-clock-rotate-left me-1"></i>
                            Audit Trail
                        </a>
                    </div>
                </div>
            @endif
        @endcan

        @if(($unconfiguredEquipmentCount ?? 0) > 0)
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-start gap-3 mb-0">
                    <i class="fas fa-triangle-exclamation fa-lg mt-1"></i>

                    <div class="flex-grow-1">
                        <strong>
                            Equipment configuration incomplete
                        </strong>

                        <div class="mt-1">
                            {{ number_format($unconfiguredEquipmentCount) }}
                            equipment record(s) are missing a category,
                            manufacturer or pricing type.
                        </div>
                    </div>

                    @if(Route::has('equipment.index'))
                        <a
                            href="{{ route('equipment.index', [
                                'configuration_status' => 'incomplete',
                            ]) }}"
                            class="btn btn-sm btn-warning"
                        >
                            Review Equipment
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Laboratory Services --}}
        <div class="col-xl-6">
            <div class="card configuration-module-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="module-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-flask-vial fa-2x"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">
                                Laboratory Services
                            </h5>

                            <p class="text-muted mb-3">
                                Manage service codes, service names,
                                applicable methods, descriptions and
                                publication status.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                @can('view_service_types')
                                    @if(Route::has('service-types.index'))
                                        <a
                                            href="{{ route('service-types.index') }}"
                                            class="btn btn-outline-primary"
                                        >
                                            <i class="fas fa-list me-1"></i>
                                            View Services
                                        </a>
                                    @endif
                                @endcan

                                @can('create_service_types')
                                    @if(Route::has('service-types.create'))
                                        <a
                                            href="{{ route('service-types.create') }}"
                                            class="btn btn-primary"
                                        >
                                            <i class="fas fa-plus me-1"></i>
                                            Add Service
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Equipment Pricing --}}
        <div class="col-xl-6">
            <div class="card configuration-module-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="module-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">
                                Equipment Types and Pricing
                            </h5>

                            <p class="text-muted mb-3">
                                Maintain equipment type names and published
                                laboratory calibration or testing rates.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                @can('view_equipment_types')
                                    @if(Route::has('equipment-types.index'))
                                        <a
                                            href="{{ route('equipment-types.index') }}"
                                            class="btn btn-outline-success"
                                        >
                                            <i class="fas fa-list me-1"></i>
                                            View Pricing
                                        </a>
                                    @endif
                                @endcan

                                @can('create_equipment_types')
                                    @if(Route::has('equipment-types.create'))
                                        <a
                                            href="{{ route('equipment-types.create') }}"
                                            class="btn btn-success"
                                        >
                                            <i class="fas fa-plus me-1"></i>
                                            Add Equipment Type
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Equipment Categories --}}
        <div class="col-xl-6">
            <div class="card configuration-module-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="module-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-layer-group fa-2x"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">
                                Equipment Categories
                            </h5>

                            <p class="text-muted mb-3">
                                Maintain the classification groups used when
                                registering and organizing laboratory equipment.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                @can('view_equipment_categories')
                                    @if(Route::has('equipment-categories.index'))
                                        <a
                                            href="{{ route('equipment-categories.index') }}"
                                            class="btn btn-outline-warning"
                                        >
                                            <i class="fas fa-list me-1"></i>
                                            View Categories
                                        </a>
                                    @endif
                                @endcan

                                @can('create_equipment_categories')
                                    @if(Route::has('equipment-categories.create'))
                                        <a
                                            href="{{ route('equipment-categories.create') }}"
                                            class="btn btn-warning"
                                        >
                                            <i class="fas fa-plus me-1"></i>
                                            Add Category
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Equipment Manufacturers --}}
        <div class="col-xl-6">
            <div class="card configuration-module-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="module-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-industry fa-2x"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">
                                Equipment Manufacturers
                            </h5>

                            <p class="text-muted mb-3">
                                Maintain approved manufacturer names and
                                reference details used during equipment
                                registration.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                @can('view_equipment_manufacturers')
                                    @if(Route::has('equipment-manufacturers.index'))
                                        <a
                                            href="{{ route('equipment-manufacturers.index') }}"
                                            class="btn btn-outline-info"
                                        >
                                            <i class="fas fa-list me-1"></i>
                                            View Manufacturers
                                        </a>
                                    @endif
                                @endcan

                                @can('create_equipment_manufacturers')
                                    @if(Route::has('equipment-manufacturers.create'))
                                        <a
                                            href="{{ route('equipment-manufacturers.create') }}"
                                            class="btn btn-info"
                                        >
                                            <i class="fas fa-plus me-1"></i>
                                            Add Manufacturer
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- RECENT CONFIGURATION RECORDS                          --}}
    {{-- ===================================================== --}}
    <div class="row g-4">

        <div class="col-lg-6">
            <div class="card configuration-table-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                        Recently Updated Services
                    </h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($recentServiceTypes ?? [] as $service)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $service->serv_code }}
                                            </span>
                                        </td>

                                        <td>
                                            <strong>
                                                {{ $service->serv_name }}
                                            </strong>

                                            <small class="d-block text-muted">
                                                {{ $service->serv_method }}
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge bg-{{
                                                $service->status
                                                    ? 'success'
                                                    : 'secondary'
                                            }}">
                                                {{
                                                    $service->status
                                                        ? 'Active'
                                                        : 'Inactive'
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="text-center text-muted py-4"
                                        >
                                            No service records available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card configuration-table-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-tags text-success me-2"></i>
                        Recently Updated Pricing
                    </h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Equipment Type</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($recentEquipmentTypes ?? [] as $equipmentType)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $equipmentType->name }}
                                            </strong>
                                        </td>

                                        <td class="text-end text-success fw-bold">
                                            {{ $equipmentType->formatted_rate }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="2"
                                            class="text-center text-muted py-4"
                                        >
                                            No pricing records available.
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
    .configuration-page {
        padding-bottom: 2rem;
    }

    .configuration-header {
        overflow: hidden;
        border-radius: 1rem;
        background:
            linear-gradient(
                135deg,
                #06366f 0%,
                #0057b8 55%,
                #031d40 100%
            );
        box-shadow: 0 12px 35px rgba(0, 87, 184, 0.22);
    }

    .configuration-header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 135px;
        height: 135px;
        border-radius: 50%;
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
    }

    .configuration-stat-card,
    .configuration-module-card,
    .configuration-table-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.07);
    }

    .configuration-stat-card,
    .configuration-module-card {
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .configuration-stat-card:hover,
    .configuration-module-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 13px 32px rgba(15, 23, 42, 0.12);
    }

    .configuration-stat-icon,
    .module-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .configuration-stat-icon {
        width: 58px;
        height: 58px;
        font-size: 1.35rem;
    }

    .module-icon {
        width: 72px;
        height: 72px;
    }

    .configuration-module-card p {
        line-height: 1.6;
    }

    .configuration-table-card .table th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.82rem;
        text-transform: uppercase;
    }

    .configuration-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border: 1px solid #dfe5ec;
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.07);
    }

    .audit-trail-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 145px;
        color: #ffffff !important;
        font-weight: 600;
        white-space: nowrap;
    }

    .audit-trail-button:hover,
    .audit-trail-button:focus {
        color: #ffffff !important;
    }

    @media (max-width: 767.98px) {
        .configuration-header .card-body {
            padding: 1.5rem !important;
        }

        .module-icon {
            width: 58px;
            height: 58px;
        }

        .configuration-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .audit-trail-button {
            width: 100%;
        }

        .alert.d-flex {
            flex-direction: column;
        }

        .alert.d-flex .btn {
            width: 100%;
        }
    }
</style>
@endpush
