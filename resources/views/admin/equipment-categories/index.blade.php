@extends('layouts.app')

@section('title', 'Equipment Categories')

@section('content')
<div class="container-fluid equipment-category-page">

    {{-- Page header --}}
    <div class="card category-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-layer-group me-1"></i>
                        LABORATORY CONFIGURATION
                    </span>

                    <h2 class="fw-bold mb-2">
                        Equipment Categories
                    </h2>

                    <p class="mb-0 text-white-50">
                        Manage the classification groups used to organize
                        laboratory equipment and reference standards.
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

                    @can('create_equipment_categories')
                        @if(Route::has('equipment-categories.create'))
                            <a
                                href="{{ route('equipment-categories.create') }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-plus-circle me-1"></i>
                                Add Category
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Messages --}}
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

    {{-- Statistics --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card category-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="category-stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-layer-group"></i>
                    </span>

                    <div>
                        <small class="text-muted">Total Categories</small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['total'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card category-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="category-stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-circle-check"></i>
                    </span>

                    <div>
                        <small class="text-muted">Active</small>

                        <h3 class="fw-bold text-success mb-0">
                            {{ number_format($statistics['active'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card category-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="category-stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                        <i class="fas fa-circle-pause"></i>
                    </span>

                    <div>
                        <small class="text-muted">Inactive</small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['inactive'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card category-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="category-stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-tools"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Categories in Use
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ number_format($statistics['used'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card category-table-card border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-layer-group text-primary me-2"></i>
                        Category List
                    </h5>

                    <small class="text-muted">
                        Categories are used by laboratory equipment records.
                    </small>
                </div>

                <form
                    action="{{ route('equipment-categories.index') }}"
                    method="GET"
                    class="category-filter-form"
                >
                    <div class="input-group category-search">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="search"
                            name="search"
                            value="{{ $search ?? request('search') }}"
                            class="form-control"
                            placeholder="Search name, code or description"
                        >
                    </div>

                    <select
                        name="status"
                        class="form-select category-status-filter"
                    >
                        <option value="">All statuses</option>

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
                            href="{{ route('equipment-categories.index') }}"
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
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-center">Equipment</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width:230px;">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="text-muted">
                                    {{
                                        $categories->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $category->code }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="category-row-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="fas fa-layer-group"></i>
                                        </span>

                                        <div>
                                            <strong>
                                                {{ $category->name }}
                                            </strong>

                                            <small class="d-block text-muted">
                                                Updated
                                                {{
                                                    $category->updated_at
                                                        ?->diffForHumans()
                                                    ?? 'recently'
                                                }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if(!empty($category->description))
                                        {{
                                            \Illuminate\Support\Str::limit(
                                                $category->description,
                                                100
                                            )
                                        }}
                                    @else
                                        <span class="text-muted">
                                            No description
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-info">
                                        {{
                                            number_format(
                                                $category->equipment_count
                                                ?? 0
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-{{
                                        $category->is_active
                                            ? 'success'
                                            : 'secondary'
                                    }}">
                                        {{
                                            $category->is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @can('view_equipment_categories')
                                            <a
                                                href="{{ route(
                                                    'equipment-categories.show',
                                                    $category
                                                ) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="View"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan

                                        @can('edit_equipment_categories')
                                            <a
                                                href="{{ route(
                                                    'equipment-categories.edit',
                                                    $category
                                                ) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form
                                                action="{{ route(
                                                    'equipment-categories.toggle-status',
                                                    $category
                                                ) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-{{
                                                        $category->is_active
                                                            ? 'warning'
                                                            : 'success'
                                                    }}"
                                                    title="{{
                                                        $category->is_active
                                                            ? 'Deactivate'
                                                            : 'Activate'
                                                    }}"
                                                    onclick="return confirm(
                                                        '{{ $category->is_active
                                                            ? 'Deactivate this category?'
                                                            : 'Activate this category?' }}'
                                                    );"
                                                >
                                                    <i class="fas {{
                                                        $category->is_active
                                                            ? 'fa-pause'
                                                            : 'fa-play'
                                                    }}"></i>
                                                </button>
                                            </form>
                                        @endcan

                                        @can('delete_equipment_categories')
                                            <form
                                                action="{{ route(
                                                    'equipment-categories.destroy',
                                                    $category
                                                ) }}"
                                                method="POST"
                                                onsubmit="return confirm(
                                                    'Delete this equipment category?'
                                                );"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    @disabled(
                                                        ($category->equipment_count ?? 0) > 0
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
                                <td colspan="7">
                                    <div class="category-empty-state">
                                        <i class="fas fa-layer-group fa-3x mb-3"></i>

                                        <h5>No equipment categories found</h5>

                                        <p class="mb-3">
                                            No categories match the selected
                                            search or status.
                                        </p>

                                        @can('create_equipment_categories')
                                            <a
                                                href="{{ route(
                                                    'equipment-categories.create'
                                                ) }}"
                                                class="btn btn-primary"
                                            >
                                                <i class="fas fa-plus me-1"></i>
                                                Add First Category
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

        @if($categories->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Showing {{ $categories->firstItem() }}
                        to {{ $categories->lastItem() }}
                        of {{ $categories->total() }} records
                    </small>

                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .equipment-category-page {
        padding-bottom: 2rem;
    }

    .category-header {
        overflow: hidden;
        border-radius: 1rem;
        background: linear-gradient(
            135deg,
            #06366f 0%,
            #0057b8 60%,
            #031d40 100%
        );
        box-shadow: 0 12px 35px rgba(0, 87, 184, 0.22);
    }

    .category-stat-card,
    .category-table-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .category-stat-icon,
    .category-row-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .category-stat-icon {
        width: 56px;
        height: 56px;
        font-size: 1.3rem;
    }

    .category-row-icon {
        width: 42px;
        height: 42px;
    }

    .category-filter-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .category-search {
        width: 310px;
    }

    .category-status-filter {
        width: 150px;
    }

    .category-table-card thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.82rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .category-empty-state {
        padding: 3rem 1rem;
        color: #6c757d;
        text-align: center;
    }

    @media (max-width: 991.98px) {
        .category-filter-form {
            width: 100%;
            flex-wrap: wrap;
        }

        .category-search {
            width: 100%;
        }

        .category-status-filter {
            flex: 1 1 180px;
            width: auto;
        }
    }

    @media (max-width: 767.98px) {
        .category-header .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endpush
