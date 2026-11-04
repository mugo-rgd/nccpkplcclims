@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-plus"></i> Add New Equipment</h2>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('equipment.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                            </div>

                            <div class="col-md-6 mb-3">
   <label for="manufacturer_id" class="form-label">
    Manufacturer
    <span class="text-danger">*</span>
</label>

<select
    id="manufacturer_id"
    name="manufacturer_id"
    class="form-select @error('manufacturer_id') is-invalid @enderror"
    required
>
        <option value="">
            -- Select Manufacturer --
        </option>

        @foreach($manufacturers ?? collect() as $manufacturer)
            <option
                value="{{ $manufacturer->id }}"
                @selected(
                    (string) old('manufacturer_id')
                    === (string) $manufacturer->id
                )
            >
                {{ $manufacturer->code }}
                -
                {{ $manufacturer->name }}
            </option>
        @endforeach
    </select>

    @error('manufacturer_id')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

    <div class="form-text">
        Select an approved manufacturer from Laboratory Configuration.
    </div>
</div>

                            <div class="col-md-6 mb-3">
    <label for="category_id" class="form-label">
    Equipment Category
    <span class="text-danger">*</span>
</label>

<select
    id="category_id"
    name="category_id"
    class="form-select @error('category_id') is-invalid @enderror"
    required
>
        <option value="">
            -- Select Equipment Category --
        </option>

        @foreach($categories ?? collect() as $category)
            <option
                value="{{ $category->id }}"
                @selected(
                    (string) old('category_id')
                    === (string) $category->id
                )
            >
                {{ $category->code }}
                -
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    @error('category_id')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

    <div class="form-text">
        Select the controlled category used to classify this equipment.
    </div>
</div>

<div class="col-md-6 mb-3">
    <label for="type_id" class="form-label">
    Equipment Type / Pricing Category
    <span class="text-danger">*</span>
</label>

<select
    id="type_id"
    name="type_id"
    class="form-select @error('type_id') is-invalid @enderror"
    required
>
        <option value="">
            -- Select Equipment Type --
        </option>

        @foreach($equipmentTypes ?? collect() as $equipmentType)
            <option
                value="{{ $equipmentType->id }}"
                data-rate="{{ (float) $equipmentType->rate }}"
                @selected(
                    (string) old('type_id')
                    === (string) $equipmentType->id
                )
            >
                {{ $equipmentType->name }}
                —
                {{ $equipmentType->formatted_rate }}
            </option>
        @endforeach
    </select>

    @error('type_id')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

    <div class="form-text">
        This selection determines the published base laboratory rate.
    </div>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label fw-semibold">
        Published Base Rate
    </label>

    <div class="published-rate-box">
        <i class="fas fa-tags text-success"></i>

        <div>
            <small class="d-block text-muted">
                Selected equipment type rate
            </small>

            <strong
                id="selectedEquipmentRate"
                class="text-success"
            >
                KES 0.00
            </strong>
        </div>
    </div>
</div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="in_use" {{ old('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                                    <option value="under_repair" {{ old('status') == 'under_repair' ? 'selected' : '' }}>Under Repair</option>
                                    <option value="calibration_due" {{ old('status') == 'calibration_due' ? 'selected' : '' }}>Calibration Due</option>
                                    <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>Retired</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select">
                                    <option value="excellent" {{ old('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                                    <option value="needs_repair" {{ old('condition') == 'needs_repair' ? 'selected' : '' }}>Needs Repair</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Equipment</button>
                        <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .published-rate-box {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-height: 48px;
        padding: 0.7rem 0.9rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
    }

    .published-rate-box i {
        font-size: 1.25rem;
    }

    #selectedEquipmentRate {
        font-size: 1rem;
        font-weight: 700;
    }
</style>
@endpush
