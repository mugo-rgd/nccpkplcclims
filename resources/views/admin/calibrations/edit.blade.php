@extends('layouts.app')

@section('title', 'Edit Calibration #' . $calibration->id)

@section('content')
@php
    $currentUser = auth()->user();
    $isCustomerUser = $currentUser?->hasRole('customer') ?? false;
    $isKplcStaffUser = !$isCustomerUser;

    $isKplcCustomer = $calibration->customer?->isKplc() ?? false;
    $isExternalCustomer = !$isKplcCustomer;

    $quotation = $calibration->quotation;
    $quotationGenerated = $quotation !== null;

    $quotationAmount = (float) ($quotation?->total_amount ?? $calibration->amount_due ?? 0);
    $amountPaid = (float) ($quotation?->amount_paid ?? $calibration->amount_paid ?? 0);

    $quotationPaid = $isKplcCustomer
        || in_array((string) $calibration->payment_status, ['paid', 'completed'], true)
        || in_array((string) ($quotation?->mpesa_payment_status ?? ''), ['paid', 'completed'], true)
        || ($quotationAmount > 0 && $amountPaid >= $quotationAmount);

    $technicianAssigned = filled($calibration->performed_by);

    $canAssignTechnician = $isKplcStaffUser
        && $quotationGenerated
        && ($isKplcCustomer || $quotationPaid);

    $allowedStatuses = $isKplcCustomer
        ? [
            'pending' => 'Pending',
            'quoted' => 'Quoted',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'testing' => 'Testing',
            'completed' => 'Completed',
            'certified' => 'Certified',
            'rejected' => 'Rejected',
        ]
        : [
            'pending' => 'Pending',
            'quoted' => 'Quoted',
            'pending_payment' => 'Pending Payment',
            'paid' => 'Paid',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'testing' => 'Testing',
            'completed' => 'Completed',
            'certified' => 'Certified',
            'rejected' => 'Rejected',
        ];
@endphp

<div class="container-fluid px-0 calibration-edit-page">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-edit text-warning me-1"></i>
                            Edit Calibration #{{ $calibration->id }}
                        </h2>
                        <p class="mb-0 text-muted">Update customer equipment details and calibration information</p>
                    </div>
                    <a href="{{ route('calibrations.show', $calibration) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
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

                    <form method="POST" action="{{ route('calibrations.update', $calibration) }}" id="mainForm">
                        @csrf
                        @method('PUT')

                        {{-- SECTION 1: CUSTOMER SELECTION --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-building me-1"></i> Customer <span class="text-danger">*</span>
                                </label>
                                <select name="customer_id" class="form-select form-select-lg" required>
                                    <option value="">— Select Customer —</option>
                                    @foreach($customers as $id => $name)
                                        <option value="{{ $id }}" {{ $calibration->customer_id == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select the customer who owns the equipment.</small>
                            </div>
                        </div>

                        {{-- SECTION 2 & 3: CUSTOMER EQUIPMENT DETAILS (MULTI-ITEM WITH SERVICES) --}}
                        <div class="card border-secondary mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-tools me-1"></i> Customer Equipment Details & Services
                                </h6>
                            </div>

                            <div class="card-body">
                                @foreach($calibration->items as $index => $item)
                                    <div class="border rounded p-3 mb-4 bg-light">
                                        <h6 class="text-primary mb-3">Equipment {{ $index + 1 }}</h6>
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Equipment Name <span class="text-danger">*</span></label>
                                                <input type="text" name="items[{{ $index }}][equipment_name]" class="form-control"
                                                       value="{{ old("items.{$index}.equipment_name", $item->equipment_name) }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Serial Number <span class="text-danger">*</span></label>
                                                <input type="text" name="items[{{ $index }}][serial_number]" class="form-control"
                                                       value="{{ old("items.{$index}.serial_number", $item->serial_number) }}" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Manufacturer</label>
                                                <input type="text" name="items[{{ $index }}][manufacturer]" class="form-control"
                                                       value="{{ old("items.{$index}.manufacturer", $item->manufacturer) }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Model</label>
                                                <input type="text" name="items[{{ $index }}][model]" class="form-control"
                                                       value="{{ old("items.{$index}.model", $item->model) }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Condition <span class="text-danger">*</span></label>
                                                <select name="items[{{ $index }}][equipment_condition]" class="form-select" required>
                                                    <option value="Good" @selected(old("items.{$index}.equipment_condition", $item->equipment_condition) == 'Good')>Good</option>
                                                    <option value="Excellent" @selected(old("items.{$index}.equipment_condition", $item->equipment_condition) == 'Excellent')>Excellent</option>
                                                    <option value="Fair" @selected(old("items.{$index}.equipment_condition", $item->equipment_condition) == 'Fair')>Fair</option>
                                                    <option value="Damaged" @selected(old("items.{$index}.equipment_condition", $item->equipment_condition) == 'Damaged')>Damaged</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Per-Equipment Service Types --}}
                                        <div class="mt-3">
                                            <label class="form-label fw-bold text-info"><i class="fas fa-cogs me-1"></i> Services Requested for Equipment {{ $index + 1 }}</label>
                                            <div class="row">
                                                @php
                                                    $itemServices = is_array($item->service_types)
                                                        ? $item->service_types
                                                        : (is_null($item->service_types) ? [] : json_decode($item->service_types, true));

                                                    $selectedItemServices = old("items.{$index}.service_types", $itemServices ?? []);
                                                    $selectedItemServices = is_array($selectedItemServices) ? array_map('intval', $selectedItemServices) : [];
                                                @endphp

                                                @foreach($kplcServiceTypes as $service)
                                                    <div class="col-md-6 col-lg-4 mb-2">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="items[{{ $index }}][service_types][]"
                                                                   value="{{ $service->id }}"
                                                                   class="form-check-input"
                                                                   id="service_{{ $index }}_{{ $service->id }}"
                                                                   {{ in_array((int)$service->id, $selectedItemServices, true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="service_{{ $index }}_{{ $service->id }}">
                                                                <strong>{{ $service->serv_code }}</strong> - {{ $service->serv_name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- SECTION 4: LABORATORY EQUIPMENT --}}
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-microchip me-1"></i> Laboratory Equipment (For Testing/Calibration)
                                </h6>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Select MCL Equipment <span class="text-danger">*</span></label>
                                        <select name="equipment_id" class="form-select form-select-lg" required>
                                            <option value="">— Select Laboratory Equipment —</option>
                                            @foreach($mclEquipment as $id => $details)
                                                <option value="{{ $id }}" {{ old('equipment_id', $calibration->equipment_id) == $id ? 'selected' : '' }}>
                                                    {{ $details['name'] ?? 'Equipment #' . $id }}
                                                    @if(!empty($details['serial_number']))
                                                        (SN: {{ $details['serial_number'] }})
                                                    @endif
                                                    @if(!empty($details['model']))
                                                        - {{ $details['model'] }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Select the laboratory equipment to be used for testing/calibration.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 5: CALIBRATION DETAILS --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="far fa-calendar-alt me-1"></i> Scheduled Date
                                </label>
                                <input type="datetime-local" name="scheduled_date" class="form-control"
                                       value="{{ old('scheduled_date', $calibration->scheduled_date ? $calibration->scheduled_date->format('Y-m-d\TH:i') : '') }}"
                                       min="{{ now()->addHours(24)->format('Y-m-d\TH:i') }}">
                                <small class="text-muted">Minimum 24 hours from now.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="technicianSelect" class="form-label fw-bold">
                                    <i class="fas fa-user-cog me-1"></i> Assign Technician
                                </label>

                                <select name="performed_by" class="form-select @error('performed_by') is-invalid @enderror"
                                        id="technicianSelect" @disabled(!$canAssignTechnician && !$technicianAssigned)>
                                    <option value="">— Select Technician —</option>
                                    @foreach($technicians as $technician)
                                        <option value="{{ $technician->id }}"
                                                data-name="{{ $technician->name }}"
                                                data-department="{{ $technician->department ?? 'N/A' }}"
                                                @selected(old('performed_by', $calibration->performed_by) == $technician->id)>
                                            {{ $technician->name }} ({{ $technician->department ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>

                                @error('performed_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if(!$quotationGenerated)
                                    <small class="text-warning d-block mt-1">
                                        <i class="fas fa-lock me-1"></i> Generate the quotation before assigning a technician.
                                    </small>
                                @elseif($isExternalCustomer && !$quotationPaid)
                                    <small class="text-danger d-block mt-1">
                                        <i class="fas fa-lock me-1"></i> Full payment is required before assigning a technician.
                                    </small>
                                @else
                                    <small class="text-success d-block mt-1">
                                        <i class="fas fa-check-circle me-1"></i> Technician assignment is available.
                                    </small>
                                @endif
                            </div>

                            <div class="col-12 mb-3" id="reassignmentReasonContainer" style="display: {{ old('performed_by', $calibration->performed_by) && old('performed_by', $calibration->performed_by) != $calibration->performed_by ? 'block' : 'none' }};">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-comment me-1"></i> Reassignment Reason <span class="text-danger">*</span>
                                </label>
                                <textarea name="reassignment_reason" class="form-control" rows="2"
                                          placeholder="Please provide a reason for reassigning this work..." id="reassignmentReason">{{ old('reassignment_reason') }}</textarea>
                                <small class="text-muted">Required when changing technician assignment.</small>
                                <div id="reasonCharCount" class="text-muted small">500 characters remaining</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-1"></i> Calibration Location <span class="text-danger">*</span>
                                </label>
                                <select name="calibration_location" class="form-select" required>
                                    <option value="laboratory" {{ old('calibration_location', $calibration->calibration_location) == 'laboratory' ? 'selected' : '' }}>Laboratory (MCL)</option>
                                    <option value="on_site" {{ old('calibration_location', $calibration->calibration_location) == 'on_site' ? 'selected' : '' }}>On-Site (Customer Premises)</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-flag me-1"></i> Priority
                                </label>
                                <select name="priority" class="form-select">
                                    <option value="normal" {{ old('priority', $calibration->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority', $calibration->priority) == 'high' ? 'selected' : '' }}>High Priority</option>
                                    <option value="urgent" {{ old('priority', $calibration->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        {{-- SECTION 6: PAYMENT / INTERNAL WORKFLOW --}}
                        @if($isExternalCustomer)
                            <div class="card border-success mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-money-bill-wave me-1"></i> External Customer Payment Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <small class="text-muted d-block">Workflow</small>
                                                <strong>External Customer</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <small class="text-muted d-block">Payment Status</small>
                                                <strong>{{ ucfirst(str_replace('_', ' ', $calibration->payment_status ?? 'pending')) }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <small class="text-muted d-block">Outstanding Balance</small>
                                                <strong class="{{ $quotationPaid ? 'text-success' : 'text-danger' }}">
                                                    KSh {{ number_format(max(0, $quotationAmount - $amountPaid), 2) }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                    @if($quotationPaid)
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle me-2"></i> <strong>Payment completed.</strong> Technician assignment is allowed.
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-clock me-2"></i> <strong>Payment outstanding.</strong> Technician assignment is locked.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-building me-1"></i> Internal KPLC Workflow</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i> <strong>No customer payment stage applies.</strong> After the internal quotation, the calibration can be assigned.
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- SECTION 7: TEST PARAMETERS --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-chart-bar me-1"></i> Test Parameters
                            </label>
                            <div class="border p-3 rounded">
                                <div id="parameters-container">
                                    @php
                                        $oldParameters = old('test_parameters', $calibration->test_parameters ?? [['name' => '', 'nominal_value' => '', 'unit' => '']]);
                                        if (empty($oldParameters) || !is_array($oldParameters)) {
                                            $oldParameters = [['name' => '', 'nominal_value' => '', 'unit' => '']];
                                        }
                                    @endphp
                                    @foreach($oldParameters as $index => $parameter)
                                        <div class="row parameter-row">
                                            <div class="col-md-4 mb-2 mb-md-0">
                                                <input type="text" name="test_parameters[{{ $index }}][name]" class="form-control" value="{{ $parameter['name'] ?? '' }}" placeholder="Parameter Name">
                                            </div>
                                            <div class="col-md-3 mb-2 mb-md-0">
                                                <input type="number" name="test_parameters[{{ $index }}][nominal_value]" class="form-control" value="{{ $parameter['nominal_value'] ?? '' }}" placeholder="Nominal Value" step="any">
                                            </div>
                                            <div class="col-md-3 mb-2 mb-md-0">
                                                <input type="text" name="test_parameters[{{ $index }}][unit]" class="form-control" value="{{ $parameter['unit'] ?? '' }}" placeholder="Unit">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-parameter w-100">
                                                    <i class="fas fa-times me-1"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mt-2" id="add-parameter">
                                    <i class="fas fa-plus me-1"></i> Add Parameter
                                </button>
                            </div>
                        </div>

                        {{-- SECTION 8: STATUS --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle me-1"></i> Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select" id="statusSelect" required>
                                <option value="">— Select Status —</option>
                                @foreach($allowedStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $calibration->status) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <span id="statusHelp">Current status: <strong>{{ ucfirst(str_replace('_', ' ', old('status', $calibration->status))) }}</strong></span>
                                <br>
                                <span class="text-info" id="statusNote"></span>
                            </small>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-warning btn-lg" id="submitBtn">
                                <i class="fas fa-save me-1"></i> Update Calibration
                            </button>
                            <a href="{{ route('calibrations.show', $calibration) }}" class="btn btn-secondary btn-lg">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test Parameters Management
    let parameterCount = document.querySelectorAll('.parameter-row').length;

    document.getElementById('add-parameter').addEventListener('click', function() {
        const container = document.getElementById('parameters-container');
        const newRow = document.createElement('div');
        newRow.className = 'row parameter-row';
        newRow.innerHTML = `
            <div class="col-md-4 mb-2 mb-md-0">
                <input type="text" name="test_parameters[${parameterCount}][name]" class="form-control" placeholder="Parameter Name">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <input type="number" name="test_parameters[${parameterCount}][nominal_value]" class="form-control" placeholder="Nominal Value" step="any">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <input type="text" name="test_parameters[${parameterCount}][unit]" class="form-control" placeholder="Unit">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-parameter w-100">
                    <i class="fas fa-times me-1"></i> Remove
                </button>
            </div>
        `;
        container.appendChild(newRow);
        parameterCount++;

        newRow.querySelector('.remove-parameter').addEventListener('click', function() {
            if (document.querySelectorAll('.parameter-row').length > 1) {
                newRow.remove();
            }
        });
    });

    document.querySelectorAll('.remove-parameter').forEach(button => {
        button.addEventListener('click', function() {
            if (document.querySelectorAll('.parameter-row').length > 1) {
                this.closest('.parameter-row').remove();
            }
        });
    });

    // Technician Assignment
    const technicianSelect = document.getElementById('technicianSelect');
    const statusSelect = document.getElementById('statusSelect');
    const statusNote = document.getElementById('statusNote');
    const reassignReasonContainer = document.getElementById('reassignmentReasonContainer');
    const reassignReasonText = document.getElementById('reassignmentReason');
    const reasonCharCount = document.getElementById('reasonCharCount');
    const originalTechnicianId = "{{ $calibration->performed_by }}";

    if (reassignReasonText) {
        reassignReasonText.addEventListener('input', function() {
            const maxLength = 500;
            const remaining = maxLength - this.value.length;
            if (reasonCharCount) {
                reasonCharCount.textContent = `${remaining} characters remaining`;
                reasonCharCount.style.color = remaining < 50 ? (remaining < 10 ? 'red' : 'orange') : 'inherit';
            }
        });
    }

    if (technicianSelect) {
        technicianSelect.addEventListener('change', function() {
            const technicianName = this.options[this.selectedIndex]?.text || '';
            const currentValue = this.value;

            if (currentValue && currentValue !== originalTechnicianId) {
                reassignReasonContainer.style.display = 'block';
                if (reassignReasonText) reassignReasonText.required = true;
            } else {
                reassignReasonContainer.style.display = 'none';
                if (reassignReasonText) {
                    reassignReasonText.required = false;
                    reassignReasonText.value = '';
                    if (reasonCharCount) {
                        reasonCharCount.textContent = '500 characters remaining';
                        reasonCharCount.style.color = 'inherit';
                    }
                }
            }

            if (this.value) {
                statusSelect.value = 'assigned';
                statusNote.innerHTML = `<i class="fas fa-info-circle text-info me-1"></i> Technician <strong>"${technicianName}"</strong> assigned. Status set to <strong>"Assigned"</strong>.`;
                statusNote.style.color = '#0d6efd';
            } else {
                if (statusSelect.value === 'assigned') {
                    statusSelect.value = 'pending';
                    statusNote.innerHTML = `<i class="fas fa-info-circle text-secondary me-1"></i> No technician selected. Status set to <strong>"Pending"</strong>.`;
                    statusNote.style.color = '#6c757d';
                } else {
                    statusNote.innerHTML = '';
                }
            }
        });

        if (technicianSelect.value && technicianSelect.value !== originalTechnicianId) {
            reassignReasonContainer.style.display = 'block';
            if (reassignReasonText) reassignReasonText.required = true;
        }

        if (technicianSelect.value && statusSelect.value === 'assigned') {
            const technicianName = technicianSelect.options[technicianSelect.selectedIndex]?.text || '';
            statusNote.innerHTML = `<i class="fas fa-info-circle text-info me-1"></i> Technician <strong>"${technicianName}"</strong> is assigned. Status: <strong>"Assigned"</strong>.`;
            statusNote.style.color = '#0d6efd';
        }
    }

    const mainForm = document.getElementById('mainForm');
    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            const techSelect = document.getElementById('technicianSelect');
            const reasonText = document.getElementById('reassignmentReason');

            if (techSelect && techSelect.value && techSelect.value !== originalTechnicianId) {
                if (!reasonText || reasonText.value.trim().length < 10) {
                    e.preventDefault();
                    alert('Please provide a detailed reason (minimum 10 characters) for changing the technician.');
                    if (reasonText) {
                        reasonText.focus();
                        reasonText.style.borderColor = '#dc3545';
                    }
                    return false;
                }
            }
            return true;
        });
    }

    const workflowCanAssign = @json($canAssignTechnician || $technicianAssigned);
    if (technicianSelect && !workflowCanAssign) {
        technicianSelect.disabled = true;
    }
});
</script>
@endpush

@push('styles')
<style>
    .calibration-edit-page .form-select-lg { font-size: 1rem; padding: 0.75rem 1rem; }
    .calibration-edit-page .btn-lg { padding: 0.75rem 2rem; }
    .calibration-edit-page .parameter-row {
        background: #f8f9fa; padding: 10px; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #ffc107;
    }
    .calibration-edit-page .parameter-row:hover { background: #eef4ff; }
    .calibration-edit-page .card-header h6 { margin-bottom: 0; }
    #statusNote { display: block; margin-top: 5px; font-size: 0.9rem; }
    .form-switch .form-check-input { width: 3em; height: 1.5em; }
    .dept-name { color: #00008B; font-size: 0.8em; font-weight: 500; margin-left: 2px; }
    .tech-name { font-weight: 500; }
    .table-responsive { max-height: 500px; overflow-y: auto; }
    .card-header.bg-info { background-color: #0dcaf0 !important; }
    .btn-warning { color: #000 !important; }
    .btn-warning:hover { color: #000 !important; }
    @media (max-width: 768px) {
        .calibration-edit-page h2 { font-size: 1.35rem; }
        .calibration-edit-page .btn-lg { width: 100%; }
    }
</style>
@endpush
