@extends('layouts.app')

@section('title', 'Laboratory Service Details')

@section('content')
<div class="container-fluid service-type-show-page">

    <div class="card service-page-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-flask-vial me-1"></i>
                        SERVICE DETAILS
                    </span>

                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h2 class="fw-bold mb-0">
                            {{ $serviceType->serv_name }}
                        </h2>

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
                    </div>

                    <p class="mb-0 text-white-50">
                        {{ $serviceType->serv_code }}
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('edit_service_types')
                        @if(Route::has('service-types.edit'))
                            <a
                                href="{{ route(
                                    'service-types.edit',
                                    $serviceType
                                ) }}"
                                class="btn btn-warning"
                            >
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                        @endif
                    @endcan

                    <a
                        href="{{ route('service-types.index') }}"
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

        {{-- Main service information --}}
        <div class="col-lg-8">
            <div class="card service-detail-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-circle-info text-primary me-2"></i>
                        Service Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="service-heading-box mb-4">
                        <div class="service-detail-icon">
                            <i class="fas fa-flask-vial fa-2x"></i>
                        </div>

                        <div>
                            <span class="badge bg-primary mb-2">
                                {{ $serviceType->serv_code }}
                            </span>

                            <h4 class="fw-bold mb-1">
                                {{ $serviceType->serv_name }}
                            </h4>

                            <small class="text-muted">
                                {{
                                    $serviceType->status
                                        ? 'Visible on the public service catalogue'
                                        : 'Hidden from the public service catalogue'
                                }}
                            </small>
                        </div>
                    </div>

                    <div class="service-information-row">
                        <span class="service-information-label">
                            Service Code
                        </span>

                        <strong>
                            {{ $serviceType->serv_code }}
                        </strong>
                    </div>

                    <div class="service-information-row">
                        <span class="service-information-label">
                            Service Name
                        </span>

                        <strong>
                            {{ $serviceType->serv_name }}
                        </strong>
                    </div>

                    <div class="service-information-row">
                        <span class="service-information-label">
                            Method / Standard
                        </span>

                        <div class="service-information-value">
                            {{ $serviceType->serv_method }}
                        </div>
                    </div>

                    <div class="service-information-row">
                        <span class="service-information-label">
                            Status
                        </span>

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
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold text-primary">
                            Public Service Notes
                        </h6>

                        <div class="content-box">
                            @if(!empty($serviceType->notes))
                                {!! nl2br(e($serviceType->notes)) !!}
                            @else
                                <span class="text-muted">
                                    No public notes have been provided.
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold text-secondary">
                            Internal Comments
                        </h6>

                        <div class="content-box internal-content-box">
                            @if(!empty($serviceType->comments))
                                {!! nl2br(e($serviceType->comments)) !!}
                            @else
                                <span class="text-muted">
                                    No internal comments have been provided.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metadata and actions --}}
        <div class="col-lg-4">
            <div class="card service-detail-card border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-clock text-primary me-2"></i>
                        Record Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="meta-row">
                        <span>Created</span>

                        <strong>
                            {{ $serviceType->created_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="meta-row">
                        <span>Last Updated</span>

                        <strong>
                            {{ $serviceType->updated_at
                                ?->format('d/m/Y H:i')
                                ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="meta-row">
                        <span>Public Status</span>

                        <strong class="{{
                            $serviceType->status
                                ? 'text-success'
                                : 'text-secondary'
                        }}">
                            {{
                                $serviceType->status
                                    ? 'Published'
                                    : 'Not Published'
                            }}
                        </strong>
                    </div>
                </div>
            </div>

            @can('edit_service_types')
                @if(Route::has('service-types.toggle-status'))
                    <div class="card service-detail-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-2">
                                Public Visibility
                            </h6>

                            <p class="text-muted small">
                                {{
                                    $serviceType->status
                                        ? 'Deactivating this service removes it from the public service catalogue.'
                                        : 'Activating this service publishes it on the public service catalogue.'
                                }}
                            </p>

                            <form
                                action="{{ route(
                                    'service-types.toggle-status',
                                    $serviceType
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        '{{
                                            $serviceType->status
                                                ? 'Deactivate this laboratory service?'
                                                : 'Activate and publish this laboratory service?'
                                        }}'
                                    );
                                "
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-{{
                                        $serviceType->status
                                            ? 'outline-warning'
                                            : 'success'
                                    }} w-100"
                                >
                                    <i class="fas {{
                                        $serviceType->status
                                            ? 'fa-pause'
                                            : 'fa-play'
                                    }} me-1"></i>

                                    {{
                                        $serviceType->status
                                            ? 'Deactivate Service'
                                            : 'Activate Service'
                                    }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            @can('delete_service_types')
                @if(Route::has('service-types.destroy'))
                    <div class="card service-detail-card danger-card border-0">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-danger mb-2">
                                Delete Service
                            </h6>

                            <p class="text-muted small">
                                Delete this service only when it is no longer
                                needed and is not referenced by any records.
                            </p>

                            <form
                                action="{{ route(
                                    'service-types.destroy',
                                    $serviceType
                                ) }}"
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
                                    class="btn btn-outline-danger w-100"
                                >
                                    <i class="fas fa-trash me-1"></i>
                                    Delete Laboratory Service
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-type-show-page {
        padding-bottom: 2rem;
    }

    .service-page-header {
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

    .service-detail-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .service-heading-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.1rem;
        border: 1px solid #e6edf5;
        border-radius: 0.8rem;
        background: #f8fbff;
    }

    .service-detail-icon {
        width: 68px;
        height: 68px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
        color: #0057b8;
        background: rgba(0, 87, 184, 0.1);
    }

    .service-information-row,
    .meta-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 0;
        border-bottom: 1px solid #edf0f4;
    }

    .service-information-row:last-child,
    .meta-row:last-child {
        border-bottom: 0;
    }

    .service-information-label {
        min-width: 145px;
        color: #6c757d;
    }

    .service-information-value {
        text-align: right;
    }

    .content-box {
        min-height: 85px;
        padding: 1rem;
        border: 1px solid #e4e8ed;
        border-radius: 0.65rem;
        background: #f8f9fa;
        line-height: 1.65;
    }

    .internal-content-box {
        border-left: 4px solid #6c757d;
    }

    .danger-card {
        border-left: 4px solid #dc3545 !important;
    }

    @media (max-width: 767.98px) {
        .service-page-header .card-body {
            padding: 1.5rem !important;
        }

        .service-heading-box {
            align-items: flex-start;
        }

        .service-information-row,
        .meta-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.25rem;
        }

        .service-information-value {
            text-align: left;
        }
    }
</style>
@endpush
