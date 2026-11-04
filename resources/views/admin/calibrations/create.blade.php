@extends('layouts.app')

@section('title', ($isCustomer ?? false) ? 'Request Calibration' : 'Create New Calibration')

@section('content')
@php
    $isCustomer = $isCustomer ?? auth()->user()?->hasRole('customer');
    $isKplcStaff = $isKplcStaff ?? !$isCustomer;
@endphp

<div class="container-fluid px-0 calibration-create-page">
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header">
                    <h2 class="mb-1">
                        <i class="fas fa-microscope text-primary me-1"></i>
                        {{ $isCustomer ? 'Request Calibration / Testing' : 'Create New Calibration' }}
                    </h2>
                    <p class="mb-0 text-muted">
                        @if($isCustomer)
                            Enter the equipment and service requirements below. After submission,
                            please await an official quotation from Kenya Power Meter Central Laboratory.
                        @else
                            Enter the customer request, allocate laboratory resources and schedule the work.
                        @endif
                    </p>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Please correct the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="calibrationForm" method="POST" action="{{ route('calibrations.store') }}">
                        @csrf

                        {{-- SECTION 1: CUSTOMER --}}
                        @if($isCustomer)
                            <input type="hidden" name="customer_id" value="{{ $authenticatedCustomer?->id }}">
                            <div class="alert alert-primary mb-3">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="fas fa-building mt-1"></i>
                                    <div>
                                        <strong>Customer Account</strong>
                                        <div class="fw-semibold">
                                            {{ $authenticatedCustomer?->company_name ?? $authenticatedCustomer?->name ?? auth()->user()->name }}
                                        </div>
                                        <small>This request will be recorded under your registered customer account.</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="customer_id" class="form-label fw-bold">
                                        <i class="fas fa-building me-1"></i> Customer <span class="text-danger">*</span>
                                    </label>
                                    <select id="customer_id" name="customer_id" class="form-select form-select-lg @error('customer_id') is-invalid @enderror" required>
                                        <option value="">— Select Customer —</option>
                                        @foreach($customers as $id => $name)
                                            <option value="{{ $id }}" {{ (string) old('customer_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endif

                        {{-- SECTION 2: EQUIPMENT ITEMS (each with verification + lab equipment) --}}
                        <div class="card border-secondary mb-3">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h6 class="mb-0"><i class="fas fa-tools me-1"></i> Customer Equipment Details (To be Tested/Calibrated)</h6>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-light" id="addEquipmentBtn">
                                        <i class="fas fa-plus me-1"></i> Add Equipment
                                    </button>
                                    @if($isKplcStaff && Route::has('calibrations.bulk-upload'))
                                        <a href="{{ route('calibrations.bulk-upload') }}" class="btn btn-sm btn-light">
                                            <i class="fas fa-file-upload me-1"></i> Bulk Upload
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="equipmentContainer"></div>
                                <div class="alert alert-info mb-0 mt-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Each equipment has its own verification and laboratory equipment settings.
                                </div>
                            </div>
                        </div>

                        @if($isKplcStaff)
                        {{-- SECTION 3: CALIBRATION-LEVEL DETAILS --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"><i class="far fa-calendar-alt me-1"></i> Scheduled Date</label>
                                <input type="datetime-local" name="scheduled_date" class="form-control" value="{{ old('scheduled_date') }}" min="{{ now()->addHours(24)->format('Y-m-d\TH:i') }}">
                                <small class="text-muted">Minimum 24 hours from now.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-user-cog me-1"></i> Assign Technician</label>
                                <select name="performed_by" class="form-select" id="technicianSelect">
                                    <option value="">— Select Technician —</option>
                                    @foreach($technicians as $technician)
                                        <option value="{{ $technician->id }}" {{ old('performed_by') == $technician->id ? 'selected' : '' }}>
                                            {{ $technician->name }} — {{ $technician->department ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-1"></i> Calibration Location <span class="text-danger">*</span></label>
                                <select name="calibration_location" id="calibration_location" class="form-select" required>
                                    <option value="laboratory" {{ old('calibration_location', 'laboratory') == 'laboratory' ? 'selected' : '' }}>Laboratory (MCL)</option>
                                    <option value="on_site" {{ old('calibration_location') == 'on_site' ? 'selected' : '' }}>On-Site (Customer Premises)</option>
                                </select>
                                <div id="on-site-costs" style="display: none;">
                                    <hr>
                                    <div class="on-site-section">
                                        <h6 class="text-primary"><i class="fas fa-truck me-1"></i> On-Site Calibration Costs</h6>
                                        <div class="row">
                                            <div class="col-md-3 mb-2"><label class="form-label fw-bold">Staff</label><input type="number" name="number_of_staff" class="form-control" value="{{ old('number_of_staff', 2) }}" min="1"></div>
                                            <div class="col-md-3 mb-2"><label class="form-label fw-bold">Travel Days</label><input type="number" name="travel_days" class="form-control" value="{{ old('travel_days', 1) }}" min="1"></div>
                                            <div class="col-md-3 mb-2"><label class="form-label fw-bold">Distance (KM)</label><input type="number" name="site_distance_km" class="form-control" value="{{ old('site_distance_km', 50) }}" min="0"></div>
                                            <div class="col-md-3 mb-2"><label class="form-label fw-bold">Per Diem (KSh)</label><input type="number" name="per_diem_rate" class="form-control" value="{{ old('per_diem_rate', 3000) }}" step="100" min="0"></div>
                                            <div class="col-md-3 mb-2"><label class="form-label fw-bold">Transport (KSh/KM)</label><input type="number" name="transport_rate" class="form-control" value="{{ old('transport_rate', 50) }}" step="5" min="0"></div>
                                            <div class="col-md-3 mb-2"><label class="form-label fw-bold">Other Costs</label><input type="number" name="other_costs" class="form-control" value="{{ old('other_costs', 0) }}" step="100" min="0"></div>
                                            <div class="col-md-6 mb-2"><label class="form-label fw-bold">Other Costs Description</label><input type="text" name="other_costs_description" class="form-control" value="{{ old('other_costs_description') }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-flag me-1"></i> Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        {{-- SECTION 4: TEST PARAMETERS --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-chart-bar me-1"></i> Test Parameters</label>
                            <div class="border p-3 rounded">
                                <div id="parameters-container">
                                    <div class="row parameter-row">
                                        <div class="col-md-4 mb-2 mb-md-0"><input type="text" name="test_parameters[0][name]" class="form-control" placeholder="Parameter Name"></div>
                                        <div class="col-md-3 mb-2 mb-md-0"><input type="number" name="test_parameters[0][nominal_value]" class="form-control" placeholder="Nominal Value" step="any"></div>
                                        <div class="col-md-3 mb-2 mb-md-0"><input type="text" name="test_parameters[0][unit]" class="form-control" placeholder="Unit"></div>
                                        <div class="col-md-2"><button type="button" class="btn btn-danger remove-parameter w-100"><i class="fas fa-times me-1"></i> Remove</button></div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mt-2" id="add-parameter"><i class="fas fa-plus me-1"></i> Add Parameter</button>
                            </div>
                        </div>
                        @endif

                        {{-- SECTION 5: NOTES --}}
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold"><i class="fas fa-sticky-note me-1"></i> {{ $isCustomer ? 'Additional Request Notes' : 'Additional Notes' }}</label>
                            <textarea id="notes" name="notes" class="form-control" rows="{{ $isCustomer ? 3 : 2 }}">{{ old('notes') }}</textarea>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas {{ $isCustomer ? 'fa-paper-plane' : 'fa-save' }} me-1"></i>
                                {{ $isCustomer ? 'Submit Calibration Request' : 'Create Calibration' }}
                            </button>
                            <a href="{{ route('calibrations.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- EQUIPMENT ROW TEMPLATE --}}
<template id="equipmentTemplate">
    <div class="equipment-item border rounded p-3 mb-3 bg-light" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-primary mb-0">
                <i class="fas fa-microchip me-1"></i> Equipment <span class="item-number">__NUMBER__</span>
            </h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-equipment" title="Remove this equipment">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="row">
            {{-- EQUIPMENT FIELDS --}}
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Equipment Name <span class="text-danger">*</span></label>
                <input type="text" name="items[__INDEX__][equipment_name]" class="form-control equipment-name" placeholder="e.g., Single Phase Meter" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Serial Number <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" name="items[__INDEX__][serial_number]" class="form-control serial-number" placeholder="e.g., SN2024001" required>
                    <button type="button" class="btn btn-outline-primary lookup-serial-btn" title="Search serial">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="form-text lookup-feedback text-muted">Enter serial number to search.</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Manufacturer</label>
                <input type="text" name="items[__INDEX__][manufacturer]" class="form-control manufacturer" placeholder="e.g., Hexing">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Model</label>
                <input type="text" name="items[__INDEX__][model]" class="form-control model" placeholder="e.g., HXE-320">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Accuracy Class</label>
                <select name="items[__INDEX__][accuracy_class]" class="form-select accuracy-class">
                    <option value="">— Select —</option>
                    <option value="0.1">Class 0.1</option>
                    <option value="0.2">Class 0.2</option>
                    <option value="0.2S">Class 0.2S</option>
                    <option value="0.5S">Class 0.5S</option>
                    <option value="1.0">Class 1.0</option>
                    <option value="2.0">Class 2.0</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Ratings</label>
                <input type="text" name="items[__INDEX__][ratings]" class="form-control ratings" placeholder="e.g., 230V, 10-100A, 50Hz">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Equipment Type</label>
                <select name="items[__INDEX__][equipment_type_id]" class="form-select equipment-type">
                    <option value="">— Select Equipment Type —</option>
                    @foreach($equipmentTypes as $type)
                        <option value="{{ $type->id }}" data-rate="{{ $type->rate }}">
                            {{ $type->name }}@if($isKplcStaff) ({{ $type->formatted_rate }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            @if($isKplcStaff)
            <div class="col-md-12 mb-3 rate-display-container d-none">
                <div class="alert alert-success py-2 mb-0">
                    <strong><i class="fas fa-money-bill-wave"></i> Rate:</strong>
                    <span class="rate-display">KSh 0.00</span>
                </div>
            </div>
            @endif

            {{-- PER-EQUIPMENT SERVICE TYPES --}}

{{-- PER-EQUIPMENT SERVICE TYPES --}}
<div class="col-12 mb-3">
    <label class="form-label fw-bold">
        <i class="fas fa-cogs me-1"></i>
        Service Type(s)
        <span class="text-danger">*</span>
    </label>

    <div class="border p-3 rounded bg-white">
        <div class="service-types-grid">
            @foreach($serviceTypes as $service)
                <label class="service-checkbox-wrapper">
                    <input
                        type="checkbox"
                        name="items[__INDEX__][service_types][]"
                        value="{{ $service->id }}"
                        class="service-checkbox"
                        data-code="{{ $service->serv_code }}"
                        data-name="{{ $service->serv_name }}"
                        data-method="{{ $service->serv_method }}"
                    >

                    <span class="custom-checkbox">
                        <i class="fas fa-check"></i>
                    </span>

                    <span class="service-checkbox-content">
                        <span class="service-title">
                            <strong>{{ $service->serv_code }}</strong>
                            - {{ $service->serv_name }}
                        </span>

                        @if(!empty($service->serv_method))
                            <small class="service-method">
                                <i class="fas fa-flask me-1"></i>
                                {{ $service->serv_method }}
                            </small>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>

        <div class="service-type-error text-danger small mt-2 d-none">
            Select at least one service for this equipment.
        </div>
    </div>
</div>


            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Equipment Condition <span class="text-danger">*</span></label>
                <select name="items[__INDEX__][equipment_condition]" class="form-select equipment-condition" required>
                    <option value="">— Select Condition —</option>
                    <option value="Excellent">Excellent - Like new</option>
                    <option value="Good">Good - Working properly</option>
                    <option value="Fair">Fair - Minor issues</option>
                    <option value="Poor">Poor - Needs attention</option>
                    <option value="Damaged">Damaged - Visible damage</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Last Calibration Date</label>
                <input type="date" name="items[__INDEX__][last_calibration_date]" class="form-control last-calibration-date">
            </div>

            {{-- PER-EQUIPMENT CUSTOMER VERIFICATION --}}
            <div class="col-12 mb-3">
                <div class="card border-info">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="mb-0"><i class="fas fa-clipboard-check me-1"></i> Customer Verification</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-check-circle me-1"></i> Verification of item</label>
                                <textarea name="items[__INDEX__][verification_of_item]" class="form-control verification-of-item" rows="2" placeholder="e.g., Item verified and found to be in good condition..."></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-comments me-1"></i> Review of Service and Discussions</label>
                                <textarea name="items[__INDEX__][review_of_service]" class="form-control review-of-service" rows="3" placeholder="e.g., Discussed calibration requirements..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-calendar-check me-1"></i> Validity of Calibration Period required?</label>
                                <div class="validity-options">
                                    <input type="radio" name="items[__INDEX__][validity_period_required]" id="validity_yes___INDEX__" value="yes" class="validity-radio validity-yes">
                                    <label for="validity_yes___INDEX__" class="validity-card yes">
                                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                                        <div><div class="title">YES</div><small>Calibration validity period is required.</small></div>
                                    </label>
                                    <input type="radio" name="items[__INDEX__][validity_period_required]" id="validity_no___INDEX__" value="no" class="validity-radio validity-no" checked>
                                    <label for="validity_no___INDEX__" class="validity-card no">
                                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                                        <div><div class="title">NO</div><small>Do not include a calibration validity period.</small></div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3 validity-period-container" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><i class="fas fa-clock me-1"></i> Validity Period</label>
                                        <input type="text" name="items[__INDEX__][validity_period]" class="form-control validity-period" placeholder="e.g., 12 months, 1 year">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Validity Date</label>
                                        <input type="date" name="items[__INDEX__][validity_period_date]" class="form-control validity-period-date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PER-EQUIPMENT LABORATORY EQUIPMENT --}}
            @if($isKplcStaff)
            <div class="col-12 mb-3">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0"><i class="fas fa-microchip me-1"></i> Laboratory Equipment (For This Equipment)</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-bold">Select MCL Equipment <span class="text-danger">*</span></label>
                        <select name="items[__INDEX__][laboratory_equipment_id]" class="form-select laboratory-equipment-select" required>
                            <option value="">— Select Laboratory Equipment —</option>
                            @foreach($mclEquipment as $eqId => $details)
                                <option value="{{ $eqId }}"
                                        data-name="{{ $details['name'] ?? '' }}"
                                        data-serial="{{ $details['serial_number'] ?? '' }}"
                                        data-model="{{ $details['model'] ?? '' }}"
                                        data-status="{{ $details['status'] ?? 'active' }}">
                                    {{ $details['name'] ?? 'Equipment #' . $eqId }}
                                    @if(!empty($details['serial_number'])) (SN: {{ $details['serial_number'] }}) @endif
                                    @if(!empty($details['model'])) - {{ $details['model'] }} @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="mcl-equipment-details alert alert-info mt-2 mb-0 d-none">
                            <div class="row">
                                <div class="col-md-3 mb-1"><strong>Name:</strong> <span class="mcl-eq-name">—</span></div>
                                <div class="col-md-3 mb-1"><strong>Model:</strong> <span class="mcl-eq-model">—</span></div>
                                <div class="col-md-3 mb-1"><strong>Serial:</strong> <span class="mcl-eq-serial">—</span></div>
                                <div class="col-md-3 mb-1"><strong>Status:</strong> <span class="mcl-eq-status">—</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const isKplcStaff = @json($isKplcStaff);
    const form = document.getElementById('calibrationForm');
    if (!form) return;

    const equipmentContainer = document.getElementById('equipmentContainer');
    const template = document.getElementById('equipmentTemplate');
    const addEquipmentBtn = document.getElementById('addEquipmentBtn');

    let equipmentIndex = 0;

    /* ---------- HELPERS ---------- */
    function formatCurrency(v) {
        const a = Number.parseFloat(v);
        if (!isFinite(a)) return 'KSh 0.00';
        return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 2 }).format(a);
    }

    /* ---------- SERVICE CHECKBOXES (per row) ---------- */

function initServicesForRow(row) {
    const checkboxes = row.querySelectorAll('.service-checkbox');
    const errorMessage = row.querySelector('.service-type-error');

    checkboxes.forEach(checkbox => {
        const card = checkbox.closest('.service-checkbox-wrapper');

        const updateCard = () => {
            card.classList.toggle('selected', checkbox.checked);

            const anySelected = Array.from(checkboxes)
                .some(cb => cb.checked);

            if (anySelected) {
                errorMessage?.classList.add('d-none');
            }
        };

        checkbox.addEventListener('change', updateCard);
        updateCard();
    });
}


    /* ---------- RATE DISPLAY (per row) ---------- */
    function initRateDisplayForRow(row) {
        if (!isKplcStaff) return;
        const select = row.querySelector('.equipment-type');
        const container = row.querySelector('.rate-display-container');
        const display = row.querySelector('.rate-display');
        if (!select || !container || !display) return;

        select.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const rate = parseFloat(opt?.dataset.rate || 0);
            if (rate > 0) {
                display.textContent = formatCurrency(rate);
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        });
    }

    /* ---------- SERIAL LOOKUP (per row) ---------- */
    function initSerialLookupForRow(row) {
        const btn = row.querySelector('.lookup-serial-btn');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const input = row.querySelector('.serial-number');
            const feedback = row.querySelector('.lookup-feedback');
            const serial = input?.value.trim();
            if (!serial) return;

            feedback.textContent = 'Searching...';
            feedback.className = 'form-text text-primary lookup-feedback';

            try {
                const url = new URL(@json(route('calibrations.lookup-by-serial')), window.location.origin);
                url.searchParams.set('serial_number', serial);
                const customerId = document.querySelector('[name="customer_id"]')?.value;
                if (customerId) url.searchParams.set('customer_id', customerId);

                const res = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (!data.found || !data.equipment) {
                    feedback.textContent = 'No previous record found.';
                    feedback.className = 'form-text text-muted lookup-feedback';
                    return;
                }

                const eq = data.equipment;
                const set = (sel, val) => {
                    const f = row.querySelector(sel);
                    if (f && !f.value) f.value = val ?? '';
                };
                set('.equipment-name', eq.equipment_name || eq.name);
                set('.manufacturer', eq.manufacturer);
                set('.model', eq.model);
                set('.accuracy-class', eq.accuracy_class);
                set('.ratings', eq.ratings);
                set('.equipment-condition', eq.equipment_condition || eq.condition);
                set('.last-calibration-date', eq.last_calibration_date);

                const typeSelect = row.querySelector('.equipment-type');
                if (typeSelect && eq.equipment_type_id) {
                    typeSelect.value = eq.equipment_type_id;
                    typeSelect.dispatchEvent(new Event('change'));
                }
                feedback.textContent = 'Details loaded.';
                feedback.className = 'form-text text-success lookup-feedback';
            } catch {
                feedback.textContent = 'Lookup failed.';
                feedback.className = 'form-text text-warning lookup-feedback';
            }
        });
    }

    /* ---------- VALIDITY RADIO (per row) ---------- */
    function initValidityForRow(row) {
        const container = row.querySelector('.validity-period-container');
        const textInput = row.querySelector('.validity-period');
        const dateInput = row.querySelector('.validity-period-date');
        const yesRadio = row.querySelector('.validity-yes');
        const noRadio = row.querySelector('.validity-no');

        const updateVisibility = () => {
            container.style.display = yesRadio.checked ? 'block' : 'none';
        };

        yesRadio?.addEventListener('change', updateVisibility);
        noRadio?.addEventListener('change', updateVisibility);

        textInput?.addEventListener('input', function () {
            const match = this.value.match(/(\d+)\s*(month|year)/i);
            if (!match) return;
            const num = parseInt(match[1]);
            const unit = match[2].toLowerCase();
            const base = new Date();
            base.setHours(0, 0, 0, 0);
            if (unit === 'year') base.setFullYear(base.getFullYear() + num);
            else base.setMonth(base.getMonth() + num);
            dateInput.value = base.toISOString().split('T')[0];
        });
    }

    /* ---------- LAB EQUIPMENT (per row) ---------- */
    function initLabEquipmentForRow(row) {
        if (!isKplcStaff) return;
        const select = row.querySelector('.laboratory-equipment-select');
        const details = row.querySelector('.mcl-equipment-details');
        if (!select || !details) return;

        select.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!this.value) {
                details.classList.add('d-none');
                return;
            }
            details.querySelector('.mcl-eq-name').textContent = opt.dataset.name || '—';
            details.querySelector('.mcl-eq-model').textContent = opt.dataset.model || '—';
            details.querySelector('.mcl-eq-serial').textContent = opt.dataset.serial || '—';
            details.querySelector('.mcl-eq-status').textContent = opt.dataset.status || 'active';
            details.classList.remove('d-none');
        });
    }

    /* ---------- ROW MANAGEMENT ---------- */

function renumberRows() {
    const rows = equipmentContainer.querySelectorAll('.equipment-item');

    rows.forEach((row, index) => {
        row.dataset.index = index;

        const number = row.querySelector('.item-number');
        if (number) number.textContent = index + 1;

        row.querySelectorAll('input, select, textarea')
            .forEach(element => {
                if (element.name) {
                    element.name = element.name.replace(
                        /items\[\d+\]/,
                        `items[${index}]`
                    );
                }
            });

        // Update only the equipment index, not service IDs.
        row.querySelectorAll('[id]').forEach(element => {
            element.id = element.id.replace(
                /^(validity_(?:yes|no)_)\d+$/,
                `$1${index}`
            );
        });

        row.querySelectorAll('label[for]').forEach(label => {
            const oldFor = label.getAttribute('for');

            if (oldFor) {
                label.setAttribute(
                    'for',
                    oldFor.replace(
                        /^(validity_(?:yes|no)_)\d+$/,
                        `$1${index}`
                    )
                );
            }
        });

        const removeButton = row.querySelector('.remove-equipment');

        if (removeButton) {
            removeButton.disabled = rows.length <= 1;
        }
    });
}


    function createRow() {
        const html = template.innerHTML
            .replace(/__INDEX__/g, equipmentIndex)
            .replace(/__NUMBER__/g, equipmentContainer.querySelectorAll('.equipment-item').length + 1);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;

        initServicesForRow(row);
        initRateDisplayForRow(row);
        initSerialLookupForRow(row);
        initValidityForRow(row);
        initLabEquipmentForRow(row);

        row.querySelector('.remove-equipment')?.addEventListener('click', () => {
            if (equipmentContainer.querySelectorAll('.equipment-item').length <= 1) return;
            if (confirm('Remove this equipment?')) {
                row.remove();
                renumberRows();
            }
        });

        equipmentIndex++;
        return row;
    }

    function addRow() {
        const row = createRow();
        equipmentContainer.appendChild(row);
        renumberRows();
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    addEquipmentBtn?.addEventListener('click', addRow);

    // Initial row
    addRow();

    /* ---------- GLOBAL: On-site costs ---------- */
    const locationSelect = document.getElementById('calibration_location');
    const onSiteCosts = document.getElementById('on-site-costs');
    if (locationSelect && onSiteCosts) {
        locationSelect.addEventListener('change', function () {
            onSiteCosts.style.display = this.value === 'on_site' ? 'block' : 'none';
        });
    }

    /* ---------- GLOBAL: Test parameters ---------- */
    const parametersContainer = document.getElementById('parameters-container');
    const addParameterBtn = document.getElementById('add-parameter');
    if (parametersContainer && addParameterBtn) {
        addParameterBtn.addEventListener('click', function () {
            const count = parametersContainer.querySelectorAll('.parameter-row').length;
            const row = document.createElement('div');
            row.className = 'row parameter-row';
            row.innerHTML = `
                <div class="col-md-4 mb-2 mb-md-0"><input type="text" name="test_parameters[${count}][name]" class="form-control" placeholder="Parameter Name"></div>
                <div class="col-md-3 mb-2 mb-md-0"><input type="number" name="test_parameters[${count}][nominal_value]" class="form-control" placeholder="Nominal Value" step="any"></div>
                <div class="col-md-3 mb-2 mb-md-0"><input type="text" name="test_parameters[${count}][unit]" class="form-control" placeholder="Unit"></div>
                <div class="col-md-2"><button type="button" class="btn btn-danger remove-parameter w-100"><i class="fas fa-times me-1"></i> Remove</button></div>
            `;
            row.querySelector('.remove-parameter').addEventListener('click', () => {
                if (parametersContainer.querySelectorAll('.parameter-row').length > 1) row.remove();
            });
            parametersContainer.appendChild(row);
        });
        parametersContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-parameter');
            if (btn && parametersContainer.querySelectorAll('.parameter-row').length > 1) {
                btn.closest('.parameter-row').remove();
            }
        });
    }

    /* ---------- SUBMIT VALIDATION ---------- */
    form.addEventListener('submit', function (e) {
        const errors = [];
        equipmentContainer.querySelectorAll('.equipment-item').forEach((row, i) => {
            const name = row.querySelector('.equipment-name')?.value.trim();
            const serial = row.querySelector('.serial-number')?.value.trim();
            const condition = row.querySelector('.equipment-condition')?.value;
            const services = row.querySelectorAll('.service-checkbox:checked');
            const labEq = row.querySelector('.laboratory-equipment-select')?.value;

            if (!name) errors.push(`Equipment ${i + 1}: Name required.`);
            if (!serial) errors.push(`Equipment ${i + 1}: Serial required.`);
            if (!condition) errors.push(`Equipment ${i + 1}: Condition required.`);
            if (!services.length) {
                errors.push(`Equipment ${i + 1}: Select at least one service.`);
                row.querySelector('.service-type-error').classList.remove('d-none');
            }
            if (isKplcStaff && !labEq) errors.push(`Equipment ${i + 1}: Laboratory equipment required.`);
        });

        if (errors.length) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .calibration-create-page .form-select-lg { font-size: 1rem; padding: 0.75rem 1rem; }
    .calibration-create-page .btn-lg { padding: 0.75rem 2rem; }
    .calibration-create-page .card-header h6 { margin-bottom: 0; }

    .equipment-item { border-left: 4px solid #0d6efd; transition: background 0.2s; }
    .equipment-item .item-number { color: #0d6efd; font-weight: 700; }

    .calibration-create-page .parameter-row {
        background: #f8f9fa; padding: 10px; border-radius: 6px;
        margin-bottom: 10px; border-left: 4px solid #0d6efd;
    }
    .calibration-create-page .parameter-row:hover { background: #eef4ff; }


.service-types-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    align-items: stretch;
}

.service-checkbox-wrapper {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    width: 100%;
    min-width: 0;
    min-height: 175px;
    height: auto;
    padding: 20px;
    margin: 0;
    border: 2px solid #dedede;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    box-sizing: border-box;
    transition: border-color .2s, background-color .2s;
}

.service-checkbox-wrapper:hover {
    border-color: #1a237e;
    background: #f8f9ff;
}

.service-checkbox-wrapper.selected,
.service-checkbox-wrapper:has(.service-checkbox:checked) {
    border-color: #1a237e;
    background: #e8eaf6;
}

.service-checkbox {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
}

.service-checkbox:focus-visible ~ .custom-checkbox {
    outline: 3px solid #6ea8fe;
    outline-offset: 3px;
}

.custom-checkbox {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    border: 3px solid #adb5bd;
    border-radius: 7px;
    background: #fff;
}

.custom-checkbox .fa-check {
    color: #fff;
    opacity: 0;
}

.service-checkbox:checked ~ .custom-checkbox {
    border-color: #1a237e;
    background: #1a237e;
}

.service-checkbox:checked ~ .custom-checkbox .fa-check {
    opacity: 1;
}

.service-checkbox-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}

.service-title {
    color: #212529;
    font-size: 1rem;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.service-title strong {
    color: #000f82;
}

.service-method {
    display: block;
    color: #6c757d;
    font-size: .9rem;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

@media (max-width: 991px) {
    .service-types-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575px) {
    .service-types-grid {
        grid-template-columns: 1fr;
    }

    .service-checkbox-wrapper {
        min-height: 130px;
        padding: 15px;
    }
}


    .on-site-section {
        background-color: #fff3cd; border-left: 4px solid #ffc107;
        padding: 15px; border-radius: 8px; margin-top: 10px;
    }

    .validity-options { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 12px; }
    .validity-radio { display: none; }
    .validity-card {
        flex: 1; min-width: 260px; display: flex; align-items: center; gap: 18px;
        padding: 20px; border: 3px solid #d9d9d9; border-radius: 12px;
        background: #fff; cursor: pointer; transition: .25s;
    }
    .validity-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); }
    .validity-card .icon {
        width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 28px; background: #f3f3f3;
    }
    .validity-card .title { font-size: 20px; font-weight: 700; margin-bottom: 3px; }
    .validity-card small { color: #6c757d; font-size: 14px; }
    .validity-radio:checked + .validity-card.yes { border-color: #198754; background: #ecfff4; box-shadow: 0 0 0 4px rgba(25,135,84,.15); }
    .validity-radio:checked + .validity-card.yes .icon { background: #198754; color: #fff; }
    .validity-radio:checked + .validity-card.no { border-color: #dc3545; background: #fff1f3; box-shadow: 0 0 0 4px rgba(220,53,69,.15); }
    .validity-radio:checked + .validity-card.no .icon { background: #dc3545; color: #fff; }

    @media (max-width: 768px) {
        .service-checkbox-wrapper { padding: 12px; }
        .custom-checkbox { flex-basis: 26px; width: 26px; height: 26px; }
        .validity-options { flex-direction: column; }
        .validity-card { min-width: 100%; }
    }
</style>
@endpush
