@extends('layouts.app')

@section('title', 'Edit Laboratory Service')

@section('content')
<div class="container-fluid service-type-form-page">

    <div class="card service-page-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-pen-to-square me-1"></i>
                        EDIT LABORATORY SERVICE
                    </span>

                    <h2 class="fw-bold mb-2">
                        {{ $serviceType->serv_name }}
                    </h2>

                    <p class="mb-0 text-white-50">
                        Update the service description, applicable method,
                        notes and publication status.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('service-types.show'))
                        <a
                            href="{{ route(
                                'service-types.show',
                                $serviceType
                            ) }}"
                            class="btn btn-outline-light"
                        >
                            <i class="fas fa-eye me-1"></i>
                            View
                        </a>
                    @endif

                    <a
                        href="{{ route('service-types.index') }}"
                        class="btn btn-light"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Services
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-circle-exclamation me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-10">
            @include(
                'admin.service-types._form',
                ['serviceType' => $serviceType]
            )
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-type-form-page {
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

    .service-form-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.09);
    }

    .form-control,
    .form-select,
    .input-group-text {
        min-height: 48px;
    }

    textarea.form-control {
        min-height: auto;
    }

    .public-status-box {
        min-height: 48px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
    }

    @media (max-width: 767.98px) {
        .service-page-header .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endpush
