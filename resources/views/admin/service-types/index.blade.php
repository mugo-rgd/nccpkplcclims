@extends('layouts.app')

@section('title', 'Laboratory Services')

@section('content')
<div class="container-fluid service-types-page">

    {{-- ===================================================== --}}
    {{-- PAGE HEADER                                           --}}
    {{-- ===================================================== --}}
    <div class="card service-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-flask-vial me-1"></i>
                        LABORATORY CONFIGURATION
                    </span>

                    <h2 class="fw-bold mb-2">
                        Laboratory Services
                    </h2>

                    <p class="mb-0 text-white-50">
                        Manage service codes, service descriptions, applicable
                        methods and public availability.
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

                    @can('create_service_types')
                        @if(Route::has('service-types.create'))
                            <a
                                href="{{ route('service-types.create') }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-plus-circle me-1"></i>
                                Add Service
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
                aria-label="Close"
            ></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-circle-exclamation me-2"></i>
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>
        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- STATISTICS                                            --}}
    {{-- ===================================================== --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-list"></i>
                    </div>

                    <div>
                        <small class="text-muted">
                            Total Services
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['total'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-circle-check"></i>
                    </div>

                    <div>
                        <small class="text-muted">
                            Active Services
                        </small>

                        <h3 class="fw-bold mb-0 text-success">
                            {{ number_format($statistics['active'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                        <i class="fas fa-circle-pause"></i>
                    </div>

                    <div>
                        <small class="text-muted">
                            Inactive Services
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['inactive'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TABLE AND FILTERS                                     --}}
    {{-- ===================================================== --}}
    <div class="card table-card border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-flask-vial text-primary me-2"></i>
                        Service Catalogue
                    </h5>

                    <small class="text-muted">
                        Active services appear on the public laboratory page.
                    </small>
                </div>

                <form
                    action="{{ route('service-types.index') }}"
                    method="GET"
                    class="service-filter-form"
                >
                    <div class="input-group search-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="search"
                            name="search"
                            value="{{ $search ?? request('search') }}"
                            class="form-control"
                            placeholder="Search code, name or method"
                        >
                    </div>

                    <select
                        name="status"
                        class="form-select status-filter"
                    >
                        <option value="">All statuses</option>

                        <option
                            value="active"
                            {{ ($status ?? '') === 'active'
                                ? 'selected'
                                : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            {{ ($status ?? '') === 'inactive'
                                ? 'selected'
                                : '' }}
                        >
                            Inactive
                        </option>
                    </select>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>
                        Filter
                    </button>

                    @if(!empty($search) || !empty($status))
                        <a
                            href="{{ route('service-types.index') }}"
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
                            <th style="width:70px;">#</th>
                            <th style="width:130px;">Code</th>
                            <th>Service</th>
                            <th>Method / Standard</th>
                            <th class="text-center">Status</th>
                            <th
                                class="text-center"
                                style="width:230px;"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($serviceTypes as $serviceType)
                            <tr>
                                <td class="text-muted">
                                    {{
                                        $serviceTypes->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $serviceType->serv_code }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ $serviceType->serv_name }}
                                    </strong>

                                    @if(!empty($serviceType->notes))
                                        <small class="d-block text-muted mt-1">
                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $serviceType->notes,
                                                    90
                                                )
                                            }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $serviceType->serv_method }}
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-{{
                                        $serviceType->status
                                            ? 'success'
                                            : 'secondary'
                                    }}">
                                        {{
                                            $serviceType->status
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @can('view_service_types')
                                            @if(Route::has('service-types.show'))
                                                <a
                                                    href="{{
                                                        route(
                                                            'service-types.show',
                                                            $serviceType
                                                        )
                                                    }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                        @endcan

                                        @can('edit_service_types')
                                            @if(Route::has('service-types.edit'))
                                                <a
                                                    href="{{
                                                        route(
                                                            'service-types.edit',
                                                            $serviceType
                                                        )
                                                    }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if(Route::has('service-types.toggle-status'))
                                                <form
                                                    action="{{
                                                        route(
                                                            'service-types.toggle-status',
                                                            $serviceType
                                                        )
                                                    }}"
                                                    method="POST"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-{{
                                                            $serviceType->status
                                                                ? 'warning'
                                                                : 'success'
                                                        }}"
                                                        title="{{
                                                            $serviceType->status
                                                                ? 'Deactivate'
                                                                : 'Activate'
                                                        }}"
                                                        onclick="
                                                            return confirm(
                                                                '{{
                                                                    $serviceType->status
                                                                        ? 'Deactivate this service? It will no longer appear publicly.'
                                                                        : 'Activate this service and display it publicly?'
                                                                }}'
                                                            );
                                                        "
                                                    >
                                                        <i class="fas {{
                                                            $serviceType->status
                                                                ? 'fa-pause'
                                                                : 'fa-play'
                                                        }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('delete_service_types')
                                            @if(Route::has('service-types.destroy'))
                                                <form
                                                    action="{{
                                                        route(
                                                            'service-types.destroy',
                                                            $serviceType
                                                        )
                                                    }}"
                                                    method="POST"
                                                    onsubmit="
                                                        return confirm(
                                                            'Delete this laboratory service permanently?'
                                                        );
                                                    "
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
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
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-flask-vial fa-3x mb-3"></i>

                                        <h5>No laboratory services found</h5>

                                        <p>
                                            No services match the selected
                                            search or status filter.
                                        </p>

                                        @can('create_service_types')
                                            @if(Route::has('service-types.create'))
                                                <a
                                                    href="{{ route('service-types.create') }}"
                                                    class="btn btn-primary"
                                                >
                                                    <i class="fas fa-plus me-1"></i>
                                                    Add First Service
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

        @if($serviceTypes->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Showing
                        {{ $serviceTypes->firstItem() }}
                        to
                        {{ $serviceTypes->lastItem() }}
                        of
                        {{ $serviceTypes->total() }}
                        records
                    </small>

                    {{ $serviceTypes->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-types-page {
        padding-bottom: 2rem;
    }

    .service-header {
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

    .stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        flex-shrink: 0;
        border-radius: 50%;
        font-size: 1.3rem;
    }

    .service-filter-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .search-group {
        width: 300px;
    }

    .status-filter {
        width: 150px;
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

    @media (max-width: 991.98px) {
        .service-filter-form {
            width: 100%;
            flex-wrap: wrap;
        }

        .search-group {
            width: 100%;
        }

        .status-filter {
            flex: 1 1 180px;
            width: auto;
        }
    }

    @media (max-width: 767.98px) {
        .service-header .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endpush
