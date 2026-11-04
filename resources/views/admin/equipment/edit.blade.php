@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Equipment: {{ $equipment->name }}</h2>
                        <span class="badge bg-{{ $equipment->status == 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($equipment->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('equipment.update', $equipment) }}" id="editEquipmentForm">
                        @csrf
                        @method('PUT')

                        {{-- Basic Information --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Equipment Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $equipment->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Equipment Number</label>
                                        <input type="text" name="equipment_number" class="form-control @error('equipment_number') is-invalid @enderror"
                                               value="{{ old('equipment_number', $equipment->equipment_number) }}" readonly>
                                        <small class="text-muted">Auto-generated, read-only</small>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                  rows="2">{{ old('description', $equipment->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                                        <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror"
                                               value="{{ old('serial_number', $equipment->serial_number) }}" required>
                                        @error('serial_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                               value="{{ old('model', $equipment->model) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
    <label
        for="manufacturer_id"
        class="form-label fw-semibold"
    >
        Manufacturer
    </label>

    <select
        id="manufacturer_id"
        name="manufacturer_id"
        class="form-select @error('manufacturer_id') is-invalid @enderror"
    >
        <option value="">
            -- Select Manufacturer --
        </option>

        @foreach($manufacturers ?? collect() as $manufacturer)
            <option
                value="{{ $manufacturer->id }}"
                @selected(
                    (string) old(
                        'manufacturer_id',
                        $equipment->manufacturer_id
                    ) === (string) $manufacturer->id
                )
            >
                {{ $manufacturer->code }}
                -
                {{ $manufacturer->name }}

                @if(!$manufacturer->is_active)
                    (Inactive — Current Selection)
                @endif
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
                                        <label class="form-label">Part Number</label>
                                        <input type="text" name="part_number" class="form-control @error('part_number') is-invalid @enderror"
                                               value="{{ old('part_number', $equipment->part_number) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Classification --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-tags"></i> Classification</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
    <label
        for="category_id"
        class="form-label fw-semibold"
    >
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
                    (string) old(
                        'category_id',
                        $equipment->category_id
                    ) === (string) $category->id
                )
            >
                {{ $category->code }}
                -
                {{ $category->name }}

                @if(!$category->is_active)
                    (Inactive — Current Selection)
                @endif
            </option>
        @endforeach
    </select>

    @error('category_id')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

    <div class="form-text">
        Select the controlled category from Laboratory Configuration.
    </div>
</div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type_id" class="form-select @error('type_id') is-invalid @enderror">
                                            <option value="">Select Type</option>
                                           <div class="col-md-6 mb-3">
    <label
        for="type_id"
        class="form-label fw-semibold"
    >
        Equipment Type / Pricing Category
    </label>

    <select
        id="type_id"
        name="type_id"
        class="form-select @error('type_id') is-invalid @enderror"
    >
        <option value="">
            -- Select Equipment Type --
        </option>

        @foreach($equipmentTypes ?? collect() as $equipmentType)
            <option
                value="{{ $equipmentType->id }}"
                data-rate="{{ (float) $equipmentType->rate }}"
                @selected(
                    (string) old(
                        'type_id',
                        $equipment->type_id
                    ) === (string) $equipmentType->id
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
        This equipment type determines the published base rate.
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
                                        </select>
                                        @error('type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Accuracy Class</label>
                                        <input type="text" name="accuracy_class" class="form-control @error('accuracy_class') is-invalid @enderror"
                                               value="{{ old('accuracy_class', $equipment->accuracy_class) }}"
                                               placeholder="e.g., Class 0.5, Class 1.0">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="active" {{ old('status', $equipment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="in_use" {{ old('status', $equipment->status) == 'in_use' ? 'selected' : '' }}>In Use</option>
                                            <option value="under_repair" {{ old('status', $equipment->status) == 'under_repair' ? 'selected' : '' }}>Under Repair</option>
                                            <option value="calibration_due" {{ old('status', $equipment->status) == 'calibration_due' ? 'selected' : '' }}>Calibration Due</option>
                                            <option value="retired" {{ old('status', $equipment->status) == 'retired' ? 'selected' : '' }}>Retired</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Condition</label>
                                        <select name="condition" class="form-select @error('condition') is-invalid @enderror">
                                            <option value="excellent" {{ old('condition', $equipment->condition) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                            <option value="good" {{ old('condition', $equipment->condition) == 'good' ? 'selected' : '' }}>Good</option>
                                            <option value="fair" {{ old('condition', $equipment->condition) == 'fair' ? 'selected' : '' }}>Fair</option>
                                            <option value="poor" {{ old('condition', $equipment->condition) == 'poor' ? 'selected' : '' }}>Poor</option>
                                            <option value="needs_repair" {{ old('condition', $equipment->condition) == 'needs_repair' ? 'selected' : '' }}>Needs Repair</option>
                                        </select>
                                        @error('condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Calibration Information --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Calibration Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Calibration Date</label>
                                        <input type="date" name="last_calibration_date" class="form-control @error('last_calibration_date') is-invalid @enderror"
                                               value="{{ old('last_calibration_date', $equipment->last_calibration_date ? $equipment->last_calibration_date->format('Y-m-d') : '') }}">
                                        @error('last_calibration_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Next Calibration Date</label>
                                        <input type="date" name="next_calibration_date" class="form-control @error('next_calibration_date') is-invalid @enderror"
                                               value="{{ old('next_calibration_date', $equipment->next_calibration_date ? $equipment->next_calibration_date->format('Y-m-d') : '') }}">
                                        @error('next_calibration_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Calibration Interval (Days)</label>
                                        <input type="number" name="calibration_interval_days" class="form-control @error('calibration_interval_days') is-invalid @enderror"
                                               value="{{ old('calibration_interval_days', $equipment->calibration_interval_days) }}" min="1">
                                        @error('calibration_interval_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Calibration Laboratory</label>
                                        <input type="text" name="calibration_laboratory" class="form-control @error('calibration_laboratory') is-invalid @enderror"
                                               value="{{ old('calibration_laboratory', $equipment->calibration_laboratory) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Reference Standard Information --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-flask"></i> Reference Standard & Traceability</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference Standard Cert No.</label>
                                        <input type="text" name="reference_standard_cert_no" class="form-control @error('reference_standard_cert_no') is-invalid @enderror"
                                               value="{{ old('reference_standard_cert_no', $equipment->reference_standard_cert_no) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference Standard Calibration Date</label>
                                        <input type="date" name="reference_standard_cal_date" class="form-control @error('reference_standard_cal_date') is-invalid @enderror"
                                               value="{{ old('reference_standard_cal_date', $equipment->reference_standard_cal_date ? $equipment->reference_standard_cal_date->format('Y-m-d') : '') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference Standard Due Date</label>
                                        <input type="date" name="reference_standard_due_date" class="form-control @error('reference_standard_due_date') is-invalid @enderror"
                                               value="{{ old('reference_standard_due_date', $equipment->reference_standard_due_date ? $equipment->reference_standard_due_date->format('Y-m-d') : '') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference Standard Accuracy</label>
                                        <input type="text" name="reference_standard_accuracy" class="form-control @error('reference_standard_accuracy') is-invalid @enderror"
                                               value="{{ old('reference_standard_accuracy', $equipment->reference_standard_accuracy) }}"
                                               placeholder="e.g., ±0.05%">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Traceability Certificate No.</label>
                                        <input type="text" name="traceability_certificate_no" class="form-control @error('traceability_certificate_no') is-invalid @enderror"
                                               value="{{ old('traceability_certificate_no', $equipment->traceability_certificate_no) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Procurement Information --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Procurement Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Purchase Date</label>
                                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                                               value="{{ old('purchase_date', $equipment->purchase_date ? $equipment->purchase_date->format('Y-m-d') : '') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Purchase Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Ksh</span>
                                            <input type="number" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                                                   value="{{ old('purchase_price', $equipment->purchase_price) }}" step="0.01" min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Supplier</label>
                                        <input type="text" name="supplier" class="form-control @error('supplier') is-invalid @enderror"
                                               value="{{ old('supplier', $equipment->supplier) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Warranty Until</label>
                                        <input type="date" name="warranty_until" class="form-control @error('warranty_until') is-invalid @enderror"
                                               value="{{ old('warranty_until', $equipment->warranty_until) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Location & Assignment --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Location & Assignment</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                               value="{{ old('location', $equipment->location) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Assigned To</label>
                                        <input type="text" name="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror"
                                               value="{{ old('assigned_to', $equipment->assigned_to) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Additional Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                                  rows="3">{{ old('notes', $equipment->notes) }}</textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Remarks</label>
                                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                                                  rows="3">{{ old('remarks', $equipment->remarks) }}</textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Training Required</label>
                                        <textarea name="training_required" class="form-control @error('training_required') is-invalid @enderror"
                                                  rows="2">{{ old('training_required', $equipment->training_required) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Equipment
                                </button>
                                <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this equipment? This action cannot be undone.')) {
        document.getElementById('editEquipmentForm').action = "{{ route('equipment.destroy', $equipment) }}";
        document.getElementById('editEquipmentForm').method = 'POST';
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_method';
        input.value = 'DELETE';
        document.getElementById('editEquipmentForm').appendChild(input);
        document.getElementById('editEquipmentForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type_id');
    const rateOutput = document.getElementById(
        'selectedEquipmentRate'
    );

    if (!typeSelect || !rateOutput) {
        return;
    }

    function updatePublishedRate() {
        const selectedOption =
            typeSelect.options[typeSelect.selectedIndex];

        const rate = Number(
            selectedOption?.dataset?.rate ?? 0
        );

        rateOutput.textContent = new Intl.NumberFormat(
            'en-KE',
            {
                style: 'currency',
                currency: 'KES',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }
        ).format(
            Number.isFinite(rate) ? rate : 0
        );
    }

    typeSelect.addEventListener(
        'change',
        updatePublishedRate
    );

    updatePublishedRate();
});

document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type_id');
    const rateOutput = document.getElementById(
        'selectedEquipmentRate'
    );

    if (!typeSelect || !rateOutput) {
        return;
    }

    function updatePublishedRate() {
        const selectedOption =
            typeSelect.options[typeSelect.selectedIndex];

        const rate = Number(
            selectedOption?.dataset?.rate ?? 0
        );

        rateOutput.textContent = new Intl.NumberFormat(
            'en-KE',
            {
                style: 'currency',
                currency: 'KES',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }
        ).format(
            Number.isFinite(rate) ? rate : 0
        );
    }

    typeSelect.addEventListener(
        'change',
        updatePublishedRate
    );

    updatePublishedRate();
});
</script>
@endpush

@endsection


