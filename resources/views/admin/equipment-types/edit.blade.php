@extends('layouts.app')

@section('title', 'Edit Equipment Type and Pricing')

@section('content')
<div class="container-fluid equipment-type-form-page">

    <div class="card page-header-card border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-pen-to-square me-1"></i>
                        EDIT PRICING
                    </span>

                    <h2 class="fw-bold mb-2">
                        {{ $equipmentType->name }}
                    </h2>

                    <p class="mb-0 text-white-50">
                        Update the equipment category and its published
                        laboratory rate.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('equipment-types.show'))
                        <a
                            href="{{ route(
                                'equipment-types.show',
                                $equipmentType
                            ) }}"
                            class="btn btn-outline-light"
                        >
                            <i class="fas fa-eye me-1"></i>
                            View
                        </a>
                    @endif

                    <a
                        href="{{ route('equipment-types.index') }}"
                        class="btn btn-light"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Pricing
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
        <div class="col-xl-8 col-lg-10">
            @include(
                'admin.equipment-types._form',
                ['equipmentType' => $equipmentType]
            )
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .equipment-type-form-page {
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

    .form-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.09);
    }

    .form-control,
    .input-group-text {
        min-height: 48px;
    }

    @media (max-width: 767.98px) {
        .page-header-card .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endpush
