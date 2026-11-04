<!-- resources/views/calibrations/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i> Edit Calibration Work
                    </h4>
                    <a href="{{ route('calibrations.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <!-- Calibration Details Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Work Order:</strong>
                                        <span class="badge bg-primary">{{ $calibration->work_order ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Status:</strong>
                                        <span class="badge bg-{{ $calibration->status == 'completed' ? 'success' : ($calibration->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($calibration->status ?? 'Pending') }}
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Created:</strong>
                                        {{ $calibration->created_at->format('Y-m-d H:i') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Last Updated:</strong>
                                        {{ $calibration->updated_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reassignment Section -->
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">
                                <i class="fas fa-exchange-alt"></i> Reassign Work
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('calibrations.reassign', $calibration->id) }}" method="POST" id="reassignmentForm">
                                @csrf
                                @method('PUT')

                                <!-- Current Technician (Read-only) -->
                                <div class="form-group mb-3">
                                    <label><strong>Currently Assigned To:</strong></label>
                                    <div class="form-control bg-light">
                                        @if($calibration->technician)
                                            <i class="fas fa-user-check text-success"></i>
                                            {{ $calibration->technician->name }}
                                            <span class="text-muted">({{ $calibration->technician->department ?? 'N/A' }})</span>
                                            @if($calibration->technician->staff_number)
                                                <span class="badge bg-secondary">Staff: {{ $calibration->technician->staff_number }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-user-slash"></i> Not Assigned
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Select New Technician -->
                                <div class="form-group mb-3">
                                    <label for="performed_by">
                                        <strong>Reassign To:</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="performed_by"
                                        class="form-select @error('performed_by') is-invalid @enderror"
                                        id="technicianSelect"
                                        required>
                                        <option value="">— Select New Technician —</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}"
                                                {{ old('performed_by') == $technician->id ? 'selected' : '' }}
                                                {{ $calibration->performed_by == $technician->id ? 'disabled' : '' }}
                                                data-department="{{ $technician->department ?? 'N/A' }}"
                                                data-staff="{{ $technician->staff_number ?? '' }}">
                                                {{ $technician->name }}
                                                <span style="color: darkblue; font-size: 0.85em;">({{ $technician->department ?? 'N/A' }})</span>
                                                @if($calibration->performed_by == $technician->id)
                                                    <span class="badge bg-secondary">Currently Assigned</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('performed_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Current technician is disabled in the list
                                    </small>
                                </div>

                                <!-- Selected Technician Preview -->
                                <div id="selectedTechnicianPreview" class="alert alert-info d-none">
                                    <i class="fas fa-user-check"></i>
                                    <strong>Selected:</strong>
                                    <span id="selectedTechName"></span>
                                    <span class="text-muted">(<span id="selectedTechDept"></span>)</span>
                                    <span id="selectedTechStaff" class="badge bg-secondary"></span>
                                </div>

                                <!-- Reason for Reassignment -->
                                <div class="form-group mb-3">
                                    <label for="reassignment_reason">
                                        <strong>Reason for Reassignment:</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="reassignment_reason"
                                        class="form-control @error('reassignment_reason') is-invalid @enderror"
                                        rows="3"
                                        placeholder="Please provide a detailed reason for reassigning this work..."
                                        required>{{ old('reassignment_reason') }}</textarea>
                                    @error('reassignment_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="char-counter" class="text-muted small">500 characters remaining</div>
                                </div>

                                <!-- Hidden Fields -->
                                <input type="hidden" name="previous_technician_id" value="{{ $calibration->performed_by }}">

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning" id="submitBtn">
                                        <i class="fas fa-exchange-alt"></i> Reassign Work
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Reset Form
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reassignment History Section -->
                    @if($reassignmentHistory->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history"></i> Reassignment History
                                <span class="badge bg-light text-dark">{{ $reassignmentHistory->count() }}</span>
                            </h5>
                            <span class="badge bg-light text-dark">
                                Total Reassignments: {{ $reassignmentHistory->count() }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Reassigned By</th>
                                            <th>Reason</th>
                                            <th>Date/Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reassignmentHistory as $history)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if($history->fromTechnician)
                                                    <strong>{{ $history->fromTechnician->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-building"></i> {{ $history->fromTechnician->department ?? 'N/A' }}
                                                    </small>
                                                    @if($history->fromTechnician->staff_number)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-id-card"></i> {{ $history->fromTechnician->staff_number }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($history->toTechnician)
                                                    <strong>{{ $history->toTechnician->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-building"></i> {{ $history->toTechnician->department ?? 'N/A' }}
                                                    </small>
                                                    @if($history->toTechnician->staff_number)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-id-card"></i> {{ $history->toTechnician->staff_number }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($history->reassignedBy)
                                                    <strong>{{ $history->reassignedBy->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-envelope"></i> {{ $history->reassignedBy->email }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $history->reason ?? 'No reason provided' }}
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> {{ $history->reassigned_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong>{{ $history->reassigned_at->format('Y-m-d') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $history->reassigned_at->format('H:i:s') }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No reassignment history found for this work.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Enhancements -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('technicianSelect');
    const form = document.getElementById('reassignmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const reasonTextarea = document.querySelector('textarea[name="reassignment_reason"]');
    const previewDiv = document.getElementById('selectedTechnicianPreview');
    const previewName = document.getElementById('selectedTechName');
    const previewDept = document.getElementById('selectedTechDept');
    const previewStaff = document.getElementById('selectedTechStaff');
    const charCounter = document.getElementById('char-counter');

    // Preview selected technician
    if (select) {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            // Check if the selected option is disabled (current technician)
            if (selectedOption.disabled) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Selection',
                    text: 'This technician is already assigned to this work. Please select a different technician.',
                    confirmButtonColor: '#ffc107',
                });
                this.value = '';
                previewDiv.classList.add('d-none');
                return;
            }

            // Show preview for selected technician
            if (this.value) {
                const name = selectedOption.textContent.trim().split('(')[0].trim();
                const department = selectedOption.getAttribute('data-department') || 'N/A';
                const staffNumber = selectedOption.getAttribute('data-staff') || '';

                previewName.textContent = name;
                previewDept.textContent = department;
                previewStaff.textContent = staffNumber ? `Staff: ${staffNumber}` : '';
                previewStaff.classList.toggle('d-none', !staffNumber);
                previewDiv.classList.remove('d-none');
            } else {
                previewDiv.classList.add('d-none');
            }
        });
    }

    // Character counter for reason textarea
    if (reasonTextarea) {
        const maxLength = 500;

        reasonTextarea.addEventListener('input', function() {
            const charCount = this.value.length;
            const remaining = maxLength - charCount;

            charCounter.textContent = `${remaining} characters remaining`;

            if (remaining < 50) {
                charCounter.style.color = remaining < 10 ? 'red' : 'orange';
            } else {
                charCounter.style.color = 'inherit';
            }

            // Auto-resize textarea
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Trigger on load
        reasonTextarea.dispatchEvent(new Event('input'));
    }

    // Form submission with SweetAlert2 confirmation
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedOption = select.options[select.selectedIndex];

            if (!select.value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Selection Required',
                    text: 'Please select a technician to reassign the work to.',
                    confirmButtonColor: '#dc3545',
                });
                return;
            }

            if (selectedOption.disabled) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Selection',
                    text: 'This technician is already assigned to this work.',
                    confirmButtonColor: '#dc3545',
                });
                return;
            }

            const reason = reasonTextarea.value.trim();
            if (reason.length < 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Reason Required',
                    text: 'Please provide a detailed reason (minimum 10 characters).',
                    confirmButtonColor: '#dc3545',
                });
                reasonTextarea.focus();
                return;
            }

            e.preventDefault();

            const techName = selectedOption.textContent.trim().split('(')[0].trim();

            Swal.fire({
                title: 'Confirm Reassignment',
                html: `
                    <div class="text-start">
                        <p><strong>Are you sure you want to reassign this work?</strong></p>
                        <hr>
                        <p><i class="fas fa-user text-warning"></i> <strong>To:</strong> ${techName}</p>
                        <p><i class="fas fa-comment text-info"></i> <strong>Reason:</strong> ${reason}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-exchange-alt"></i> Yes, Reassign',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reassigning...';
                    form.submit();
                }
            });
        });
    }

    // Reset form handler
    document.querySelector('button[type="reset"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Reset Form?',
            text: 'All selections will be cleared.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Reset',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.reset();
                previewDiv.classList.add('d-none');
                charCounter.textContent = '500 characters remaining';
                charCounter.style.color = 'inherit';
                Swal.fire({
                    icon: 'success',
                    title: 'Form Reset',
                    text: 'Form has been reset successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
});
</script>

<!-- Include SweetAlert2 (if not already included) -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

<!-- Add FontAwesome (if not already included) -->
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

<style>
    .badge.bg-warning {
        color: #000 !important;
    }
    .form-control.bg-light {
        background-color: #f8f9fa !important;
    }
    textarea[name="reassignment_reason"] {
        resize: vertical;
        min-height: 100px;
    }
    #selectedTechnicianPreview {
        border-left: 4px solid #ffc107;
    }
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    .card-header.bg-info {
        background-color: #0dcaf0 !important;
    }
    .btn-warning {
        color: #000 !important;
    }
    .btn-warning:hover {
        color: #000 !important;
    }
    .alert-info {
        background-color: #cfe2ff;
        border-color: #b6d4fe;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 202, 240, 0.1);
    }
</style>
@endsection
