@extends('layouts.app')

@section('title', 'Equipment Manufacturers')

@section('content')
<div class="container-fluid equipment-manufacturer-page">

    {{-- ===================================================== --}}
    {{-- PAGE HEADER                                           --}}
    {{-- ===================================================== --}}
    <div class="card manufacturer-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-industry me-1"></i>
                        LABORATORY CONFIGURATION
                    </span>

                    <h2 class="fw-bold mb-2">
                        Equipment Manufacturers
                    </h2>

                    <p class="mb-0 text-white-50">
                        Manage approved equipment manufacturers and their
                        contact or reference details.
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

                    @can('create_equipment_manufacturers')
                        @if(Route::has('equipment-manufacturers.create'))
                            <a
                                href="{{ route(
                                    'equipment-manufacturers.create'
                                ) }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-plus-circle me-1"></i>
                                Add Manufacturer
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
        <div class="col-xl-3 col-md-6">
            <div class="card manufacturer-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="manufacturer-stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-industry"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Total Manufacturers
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['total'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card manufacturer-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="manufacturer-stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-circle-check"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Active
                        </small>

                        <h3 class="fw-bold text-success mb-0">
                            {{ number_format($statistics['active'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card manufacturer-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="manufacturer-stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                        <i class="fas fa-circle-pause"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Inactive
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format(
                                $statistics['inactive'] ?? 0
                            ) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card manufacturer-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="manufacturer-stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-tools"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Manufacturers in Use
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['used'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TABLE AND FILTERS                                     --}}
    {{-- ===================================================== --}}
    <div class="card manufacturer-table-card border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-industry text-primary me-2"></i>
                        Manufacturer List
                    </h5>

                    <small class="text-muted">
                        Approved manufacturers used in equipment registration.
                    </small>
                </div>

                <form
                    action="{{ route('equipment-manufacturers.index') }}"
                    method="GET"
                    class="manufacturer-filter-form"
                >
                    <div class="input-group manufacturer-search">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="search"
                            name="search"
                            value="{{ $search ?? request('search') }}"
                            class="form-control"
                            placeholder="Search manufacturer or contact"
                        >
                    </div>

                    <select
                        name="status"
                        class="form-select manufacturer-status-filter"
                    >
                        <option value="">
                            All statuses
                        </option>

                        <option
                            value="active"
                            @selected(($status ?? '') === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(($status ?? '') === 'inactive')
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
                            href="{{ route(
                                'equipment-manufacturers.index'
                            ) }}"
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
                            <th style="width:120px;">Code</th>
                            <th>Manufacturer</th>
                            <th>Contact</th>
                            <th>Website</th>
                            <th class="text-center">Equipment</th>
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
                        @forelse($manufacturers as $manufacturer)
                            <tr>
                                <td class="text-muted">
                                    {{
                                        $manufacturers->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $manufacturer->code }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="manufacturer-row-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="fas fa-industry"></i>
                                        </span>

                                        <div>
                                            <strong>
                                                {{ $manufacturer->name }}
                                            </strong>

                                            @if(!empty($manufacturer->address))
                                                <small class="d-block text-muted">
                                                    {{
                                                        \Illuminate\Support\Str::limit(
                                                            $manufacturer->address,
                                                            60
                                                        )
                                                    }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if(!empty($manufacturer->contact_person))
                                        <strong class="d-block">
                                            {{ $manufacturer->contact_person }}
                                        </strong>
                                    @endif

                                    @if(!empty($manufacturer->contact_phone))
                                        <small class="d-block text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            {{ $manufacturer->contact_phone }}
                                        </small>
                                    @endif

                                    @if(!empty($manufacturer->contact_email))
                                        <small class="d-block text-muted">
                                            <i class="fas fa-envelope me-1"></i>
                                            {{ $manufacturer->contact_email }}
                                        </small>
                                    @endif

                                    @if(
                                        empty($manufacturer->contact_person)
                                        && empty($manufacturer->contact_phone)
                                        && empty($manufacturer->contact_email)
                                    )
                                        <span class="text-muted">
                                            No contact details
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if(!empty($manufacturer->website))
                                        <a
                                            href="{{ $manufacturer->website }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-decoration-none"
                                        >
                                            <i class="fas fa-globe me-1"></i>
                                            Visit website
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            Not provided
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-info">
                                        {{
                                            number_format(
                                                $manufacturer->equipment_count
                                                ?? 0
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-{{
                                        $manufacturer->is_active
                                            ? 'success'
                                            : 'secondary'
                                    }}">
                                        {{
                                            $manufacturer->is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @can('view_equipment_manufacturers')
                                            <a
                                                href="{{ route(
                                                    'equipment-manufacturers.show',
                                                    $manufacturer
                                                ) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="View"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan

                                        @can('edit_equipment_manufacturers')
                                            <a
                                                href="{{ route(
                                                    'equipment-manufacturers.edit',
                                                    $manufacturer
                                                ) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form
                                                action="{{ route(
                                                    'equipment-manufacturers.toggle-status',
                                                    $manufacturer
                                                ) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-{{
                                                        $manufacturer->is_active
                                                            ? 'warning'
                                                            : 'success'
                                                    }}"
                                                    title="{{
                                                        $manufacturer->is_active
                                                            ? 'Deactivate'
                                                            : 'Activate'
                                                    }}"
                                                    onclick="return confirm(
                                                        '{{ $manufacturer->is_active
                                                            ? 'Deactivate this manufacturer?'
                                                            : 'Activate this manufacturer?' }}'
                                                    );"
                                                >
                                                    <i class="fas {{
                                                        $manufacturer->is_active
                                                            ? 'fa-pause'
                                                            : 'fa-play'
                                                    }}"></i>
                                                </button>
                                            </form>
                                        @endcan

                                        @can('delete_equipment_manufacturers')
                                            <form
                                                action="{{ route(
                                                    'equipment-manufacturers.destroy',
                                                    $manufacturer
                                                ) }}"
                                                method="POST"
                                                onsubmit="return confirm(
                                                    'Delete this manufacturer permanently?'
                                                );"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    @disabled(
                                                        ($manufacturer->equipment_count ?? 0) > 0
                                                    )
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="manufacturer-empty-state">
                                        <i class="fas fa-industry fa-3x mb-3"></i>

                                        <h5>
                                            No manufacturers found
                                        </h5>

                                        <p class="mb-3">
                                            No manufacturers match the selected
                                            search or status filter.
                                        </p>

                                        @can('create_equipment_manufacturers')
                                            <a
                                                href="{{ route(
                                                    'equipment-manufacturers.create'
                                                ) }}"
                                                class="btn btn-primary"
                                            >
                                                <i class="fas fa-plus me-1"></i>
                                                Add First Manufacturer
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($manufacturers->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Showing
                        {{ $manufacturers->firstItem() }}
                        to
                        {{ $manufacturers->lastItem() }}
                        of
                        {{ $manufacturers->total() }}
                        records
                    </small>

                    {{ $manufacturers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .equipment-manufacturer-page {
        padding-bottom: 2rem;
    }

    .manufacturer-header {
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

    .manufacturer-stat-card,
    .manufacturer-table-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .manufacturer-stat-icon,
    .manufacturer-row-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .manufacturer-stat-icon {
        width: 56px;
        height: 56px;
        font-size: 1.3rem;
    }

    .manufacturer-row-icon {
        width: 42px;
        height: 42px;
    }

    .manufacturer-filter-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .manufacturer-search {
        width: 320px;
    }

    .manufacturer-status-filter {
        width: 150px;
    }

    .manufacturer-table-card thead th {
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

    @media (max-width: 1199.98px) {
        .manufacturer-filter-form {
            width: 100%;
            flex-wrap: wrap;
        }

        .manufacturer-search {
            width: 100%;
        }

        .manufacturer-status-filter {
            flex: 1 1 180px;
            width: auto;
        }
    }

    @media (max-width: 767.98px) {
        .manufacturer-header .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endpush
