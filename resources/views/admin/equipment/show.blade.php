@extends('layouts.app')

@section('content')
<div class="container">

    <div class="card mb-4 border-{{
    $equipment->is_configuration_complete
        ? 'success'
        : 'warning'
}}">
    <div class="card-header bg-{{
        $equipment->is_configuration_complete
            ? 'success'
            : 'warning'
    }} {{
        $equipment->is_configuration_complete
            ? 'text-white'
            : 'text-dark'
    }}">
        <h5 class="mb-0">
            <i class="fas {{
                $equipment->is_configuration_complete
                    ? 'fa-circle-check'
                    : 'fa-triangle-exclamation'
            }} me-2"></i>

            Configuration Status
        </h5>
    </div>

    <div class="card-body">
        @if($equipment->is_configuration_complete)
            <p class="mb-0 text-success fw-semibold">
                This equipment record is fully linked to Laboratory
                Configuration master data.
            </p>
        @else
            <p class="mb-2">
                This equipment record is missing:
            </p>

            <ul class="mb-3">
                @foreach(
                    $equipment->missing_configuration
                    as $missingItem
                )
                    <li>{{ $missingItem }}</li>
                @endforeach
            </ul>

            @can('edit_equipment')
                <a
                    href="{{ route(
                        'equipment.edit',
                        $equipment
                    ) }}"
                    class="btn btn-warning"
                >
                    <i class="fas fa-pen-to-square me-1"></i>
                    Complete Configuration
                </a>
            @endcan
        @endif
    </div>
</div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-tools"></i> Equipment Details</h2>
                            <small class="text-muted">#{{ $equipment->equipment_number ?? 'N/A' }}</small>
                        </div>
                        <div>
                            <span class="badge bg-{{ $equipment->status_color }} fs-6 me-1">
                                <i class="fas fa-circle"></i> {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
                            </span>
                            <span class="badge bg-{{ $equipment->condition_color }} fs-6">
                                <i class="fas fa-check-circle"></i> {{ ucfirst(str_replace('_', ' ', $equipment->condition)) }}
                            </span>
                            @if($equipment->is_overdue)
                                <span class="badge bg-danger fs-6 ms-1">
                                    <i class="fas fa-exclamation-triangle"></i> OVERDUE
                                </span>
                            @elseif($equipment->is_calibration_due)
                                <span class="badge bg-warning fs-6 ms-1">
                                    <i class="fas fa-clock"></i> Due Soon
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs" id="equipmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                <i class="fas fa-info-circle"></i> Basic Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="calibration-tab" data-bs-toggle="tab" data-bs-target="#calibration" type="button" role="tab">
                                <i class="fas fa-calendar-check"></i> Calibration
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reference-tab" data-bs-toggle="tab" data-bs-target="#reference" type="button" role="tab">
                                <i class="fas fa-flask"></i> Reference Standard
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="procurement-tab" data-bs-toggle="tab" data-bs-target="#procurement" type="button" role="tab">
                                <i class="fas fa-shopping-cart"></i> Procurement
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="additional-tab" data-bs-toggle="tab" data-bs-target="#additional" type="button" role="tab">
                                <i class="fas fa-sticky-note"></i> Additional
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="equipmentTabsContent">
                        {{-- Basic Information Tab --}}
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Equipment Name</th>
                                            <td><strong>{{ $equipment->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Equipment Number</th>
                                            <td><code>{{ $equipment->equipment_number ?? 'N/A' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number</th>
                                            <td>{{ $equipment->serial_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Model</th>
                                            <td>{{ $equipment->model ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Part Number</th>
                                            <td>{{ $equipment->part_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Manufacturer</th>
                                            <td>{{ $equipment->manufacturer->name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Category</th>
                                            <td>{{ $equipment->category->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td>{{ $equipment->type->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Accuracy Class</th>
                                            <td>{{ $equipment->accuracy_class ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span class="badge bg-{{ $equipment->status_color }}">{{ ucfirst(str_replace('_', ' ', $equipment->status)) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Condition</th>
                                            <td><span class="badge bg-{{ $equipment->condition_color }}">{{ ucfirst(str_replace('_', ' ', $equipment->condition)) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Location</th>
                                            <td>{{ $equipment->location ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                @if($equipment->description)
                                <div class="col-md-12 mt-2">
                                    <h5>Description</h5>
                                    <p>{{ $equipment->description }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Calibration Information Tab --}}
                        <div class="tab-pane fade" id="calibration" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Calibration Laboratory</th>
                                            <td>{{ $equipment->calibration_laboratory ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Calibration Date</th>
                                            <td>
                                                @if($equipment->last_calibration_date)
                                                    {{ $equipment->last_calibration_date->format('Y-m-d') }}
                                                    <span class="badge bg-secondary ms-2">{{ $equipment->last_calibration_date->diffForHumans() }}</span>
                                                @else
                                                    <span class="text-muted">Not calibrated</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Next Calibration Date</th>
                                            <td>
                                                @if($equipment->next_calibration_date)
                                                    {{ $equipment->next_calibration_date->format('Y-m-d') }}
                                                    @if($equipment->is_overdue)
                                                        <span class="badge bg-danger ms-2">OVERDUE</span>
                                                    @elseif($equipment->is_calibration_due)
                                                        <span class="badge bg-warning ms-2">Due Soon</span>
                                                    @else
                                                        <span class="badge bg-info ms-2">{{ $equipment->days_until_calibration }} days remaining</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Not scheduled</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Calibration Interval</th>
                                            <td>{{ $equipment->calibration_interval_days ?? 365 }} days</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title">Calibration Status</h5>
                                            @if($equipment->is_overdue)
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <strong>Overdue!</strong> This equipment is overdue for calibration by
                                                    {{ now()->diffInDays($equipment->next_calibration_date) }} days.
                                                </div>
                                            @elseif($equipment->is_calibration_due)
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-clock"></i>
                                                    <strong>Due Soon!</strong> Calibration is due in
                                                    {{ $equipment->days_until_calibration }} days.
                                                </div>
                                            @elseif($equipment->next_calibration_date)
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle"></i>
                                                    <strong>Valid</strong> Calibration is valid for
                                                    {{ $equipment->days_until_calibration }} more days.
                                                </div>
                                            @else
                                                <div class="alert alert-secondary">
                                                    <i class="fas fa-info-circle"></i>
                                                    No calibration schedule set.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Reference Standard Tab --}}
                        <div class="tab-pane fade" id="reference" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Reference Standard Cert No.</th>
                                            <td><code>{{ $equipment->reference_standard_cert_no ?? 'N/A' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Reference Standard Calibration Date</th>
                                            <td>{{ $equipment->reference_standard_cal_date ? $equipment->reference_standard_cal_date->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reference Standard Due Date</th>
                                            <td>
                                                @if($equipment->reference_standard_due_date)
                                                    {{ $equipment->reference_standard_due_date->format('Y-m-d') }}
                                                    @php
                                                        $refStatus = $equipment->reference_standard_status;
                                                    @endphp
                                                    <span class="badge bg-{{ $equipment->reference_standard_status_color }} ms-2">
                                                        {{ ucfirst(str_replace('_', ' ', $refStatus)) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Reference Standard Accuracy</th>
                                            <td><code>{{ $equipment->reference_standard_accuracy ?? 'N/A' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Traceability Certificate No.</th>
                                            <td><code>{{ $equipment->traceability_certificate_no ?? 'N/A' }}</code></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    @if($equipment->reference_standard_cert_no || $equipment->traceability_certificate_no)
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h5 class="card-title"><i class="fas fa-shield-alt"></i> Traceability Information</h5>
                                                <p class="card-text">
                                                    <strong>Reference Standard:</strong>
                                                    @if($equipment->reference_standard_cert_no)
                                                        {{ $equipment->reference_standard_cert_no }}
                                                        @if($equipment->reference_standard_accuracy)
                                                            ({{ $equipment->reference_standard_accuracy }})
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                                </p>
                                                <p class="card-text">
                                                    <strong>Traceability Certificate:</strong>
                                                    {{ $equipment->traceability_certificate_no ?? 'N/A' }}
                                                </p>
                                                @if($equipment->reference_standard_due_date)
                                                    <div class="mt-2">
                                                        @if($equipment->reference_standard_due_date > now())
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i> Valid
                                                            </span>
                                                        @else
                                                            <span class="text-danger">
                                                                <i class="fas fa-times-circle"></i> Expired
                                                            </span>
                                                        @endif
                                                        - Expires {{ $equipment->reference_standard_due_date->diffForHumans() }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Procurement Tab --}}
                        <div class="tab-pane fade" id="procurement" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Purchase Date</th>
                                            <td>{{ $equipment->purchase_date ? $equipment->purchase_date->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Purchase Price</th>
                                            <td><strong>{{ $equipment->formatted_purchase_price }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Supplier</th>
                                            <td>{{ $equipment->supplier ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Warranty Until</th>
                                            <td>
                                                @if($equipment->warranty_until)
                                                    {{ $equipment->warranty_until->format('Y-m-d') }}
                                                    @if($equipment->warranty_until < now())
                                                        <span class="badge bg-danger ms-2">Expired</span>
                                                    @else
                                                        <span class="badge bg-success ms-2">{{ $equipment->warranty_until->diffForHumans() }}</span>
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Procurement Notes</th>
                                            <td>{{ $equipment->procurement_notes ?? 'None' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-chart-line"></i> Asset Value</h5>
                                            @if($equipment->purchase_price)
                                                <p class="card-text">
                                                    <strong>Purchase Price:</strong>
                                                    <span class="h4 text-primary">{{ $equipment->formatted_purchase_price }}</span>
                                                </p>
                                                @php
                                                    $yearsOld = $equipment->purchase_date ? now()->diffInYears($equipment->purchase_date) : 0;
                                                    $depreciation = $equipment->purchase_price * (1 - (1 / (1 + $yearsOld * 0.1)));
                                                    $currentValue = $equipment->purchase_price - $depreciation;
                                                @endphp
                                                <p class="card-text">
                                                    <strong>Estimated Current Value:</strong>
                                                    <span class="text-success">${{ number_format(max(0, $currentValue), 2) }}</span>
                                                </p>
                                                <p class="card-text">
                                                    <strong>Age:</strong>
                                                    {{ $yearsOld }} years
                                                </p>
                                            @else
                                                <p class="text-muted">No purchase information available</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Information Tab --}}
                        <div class="tab-pane fade" id="additional" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-user"></i> Assignment</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Assigned To</th>
                                            <td>{{ $equipment->assigned_to ?? 'Unassigned' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Location</th>
                                            <td>{{ $equipment->location ?? 'Not specified' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="fas fa-clipboard-list"></i> Training</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Training Required</th>
                                            <td>{{ $equipment->training_required ?? 'None' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <h5><i class="fas fa-sticky-note"></i> Remarks</h5>
                                    <div class="p-3 bg-light rounded">
                                        {{ $equipment->remarks ?? 'No remarks' }}
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <h5><i class="fas fa-history"></i> System Information</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 20%;">Created By</th>
                                            <td>{{ $equipment->createdBy->name ?? 'N/A' }}</td>
                                            <th style="width: 20%;">Created At</th>
                                            <td>{{ $equipment->created_at ? $equipment->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated By</th>
                                            <td>{{ $equipment->updatedBy->name ?? 'N/A' }}</td>
                                            <th>Last Updated</th>
                                            <td>{{ $equipment->updated_at ? $equipment->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mt-3 d-flex justify-content-between">
                        <div>
                            <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @if($equipment->canBeUsed())
                                <span class="badge bg-success fs-6 ms-2">
                                    <i class="fas fa-check-circle"></i> Ready for Use
                                </span>
                            @elseif($equipment->status === 'retired')
                                <span class="badge bg-secondary fs-6 ms-2">
                                    <i class="fas fa-archive"></i> Retired
                                </span>
                            @else
                                <span class="badge bg-danger fs-6 ms-2">
                                    <i class="fas fa-times-circle"></i> Not Available
                                </span>
                            @endif
                        </div>
                        <div>
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-info" onclick="exportDetails()">
                                <i class="fas fa-file-export"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportDetails() {
    // Implement export functionality
    window.location.href = "{{ route('equipment.export', $equipment) }}";
}

// Auto-activate the tab from URL hash
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (tab) {
            const tabInstance = new bootstrap.Tab(tab);
            tabInstance.show();
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.table-bordered th {
    background-color: #f8f9fa;
}
.nav-tabs .nav-link {
    color: #495057;
}
.nav-tabs .nav-link.active {
    font-weight: 600;
}
</style>
@endpush

@endsection
