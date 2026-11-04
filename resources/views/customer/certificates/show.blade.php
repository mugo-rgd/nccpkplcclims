@extends('layouts.app')

@section('title', 'Certificate #' . ($certificate->certificate_number ?? 'N/A'))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>
                    <i class="fas fa-certificate text-info me-2"></i>
                    Certificate #{{ $certificate->certificate_number ?? 'N/A' }}
                </h3>
                <div>
                    <a href="{{ route('customer.certificates') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Certificates
                    </a>
                    @if($certificate->file_path)
                        <a href="{{ asset('storage/' . $certificate->file_path) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>
                            Download PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Certificate Details -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light p-3 mb-3">
                        <h5><i class="fas fa-info-circle me-2"></i>Certificate Information</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Certificate Number:</strong></td>
                                <td>{{ $certificate->certificate_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Reference Number:</strong></td>
                                <td>{{ $certificate->reference_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $certificate->status === 'approved' ? 'success' : ($certificate->status === 'pending' ? 'warning' : ($certificate->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                        {{ ucfirst($certificate->status ?? 'Draft') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Issue Date:</strong></td>
                                <td>{{ $certificate->issued_date ? \Carbon\Carbon::parse($certificate->issued_date)->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Expiry Date:</strong></td>
                                <td>{{ $certificate->expiry_date ? \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $certificate->created_at ? $certificate->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td>{{ $certificate->updated_at ? $certificate->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light p-3 mb-3">
                        <h5><i class="fas fa-user me-2"></i>Customer Information</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Company:</strong></td>
                                <td>{{ $certificate->customer->company_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact Person:</strong></td>
                                <td>{{ $certificate->customer->contact_person ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $certificate->customer->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $certificate->customer->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>{{ $certificate->customer->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Calibration Details -->
            @if($certificate->calibration)
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light p-3 mb-3">
                            <h5><i class="fas fa-microscope me-2"></i>Calibration Details</h5>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="20%"><strong>Calibration ID:</strong></td>
                                    <td>#{{ $certificate->calibration->id }}</td>
                                    <td width="20%"><strong>Equipment:</strong></td>
                                    <td>{{ $certificate->calibration->equipment_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Serial Number:</strong></td>
                                    <td>{{ $certificate->calibration->serial_number ?? 'N/A' }}</td>
                                    <td><strong>Manufacturer:</strong></td>
                                    <td>{{ $certificate->calibration->manufacturer ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Model:</strong></td>
                                    <td>{{ $certificate->calibration->model ?? 'N/A' }}</td>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $certificate->calibration->status === 'completed' || $certificate->calibration->status === 'certified' ? 'success' : 'warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $certificate->calibration->status ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            @if($certificate->calibration->id)
                                <div class="mt-2">
                                    <a href="{{ route('customer.calibrations.show', $certificate->calibration->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye me-1"></i>
                                        View Calibration
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Certificate Details (if available) -->
            @if($certificate->details)
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light p-3">
                            <h5><i class="fas fa-clipboard-list me-2"></i>Certificate Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Test</th>
                                            <th>Applied Value</th>
                                            <th>Error %</th>
                                            <th>Uncertainty</th>
                                            <th>Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $details = is_string($certificate->details) ? json_decode($certificate->details, true) : $certificate->details;
                                        @endphp
                                        @if($details && count($details) > 0)
                                            @foreach($details as $detail)
                                                <tr>
                                                    <td>{{ $detail['test'] ?? $detail['test_name'] ?? 'N/A' }}</td>
                                                    <td>{{ $detail['applied_value'] ?? 'N/A' }}</td>
                                                    <td>{{ $detail['error_percentage'] ?? 'N/A' }}</td>
                                                    <td>{{ $detail['uncertainty'] ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ isset($detail['result']) && strtolower($detail['result']) === 'pass' ? 'success' : 'danger' }}">
                                                            {{ $detail['result'] ?? 'N/A' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No details available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Signature Information -->
            @if($certificate->issued_by || $certificate->checked_by || $certificate->approved_by)
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light p-3">
                            <h5><i class="fas fa-signature me-2"></i>Signatures</h5>
                            <div class="row">
                                @if($certificate->issued_by)
                                    <div class="col-md-4 text-center">
                                        <p><strong>Issued By</strong></p>
                                        <p>{{ $certificate->issuedBy->name ?? 'N/A' }}</p>
                                        <p><small class="text-muted">{{ $certificate->issued_date ? \Carbon\Carbon::parse($certificate->issued_date)->format('d/m/Y') : 'N/A' }}</small></p>
                                    </div>
                                @endif
                                @if($certificate->checked_by)
                                    <div class="col-md-4 text-center">
                                        <p><strong>Checked By</strong></p>
                                        <p>{{ $certificate->checkedBy->name ?? 'N/A' }}</p>
                                        <p><small class="text-muted">{{ $certificate->checked_at ? \Carbon\Carbon::parse($certificate->checked_at)->format('d/m/Y') : 'N/A' }}</small></p>
                                    </div>
                                @endif
                                @if($certificate->approved_by)
                                    <div class="col-md-4 text-center">
                                        <p><strong>Approved By</strong></p>
                                        <p>{{ $certificate->approvedBy->name ?? 'N/A' }}</p>
                                        <p><small class="text-muted">{{ $certificate->approved_at ? \Carbon\Carbon::parse($certificate->approved_at)->format('d/m/Y') : 'N/A' }}</small></p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($certificate->notes)
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light p-3">
                            <h5><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                            <p class="text-muted">{{ $certificate->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        @if($certificate->file_path)
                            <a href="{{ asset('storage/' . $certificate->file_path) }}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i>
                                Download Certificate
                            </a>
                        @endif
                        @if($certificate->calibration_id)
                            <a href="{{ route('customer.calibrations.show', $certificate->calibration_id) }}" class="btn btn-info">
                                <i class="fas fa-microscope me-1"></i>
                                View Calibration
                            </a>
                        @endif
                        <a href="{{ route('customer.certificates') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-borderless td {
        padding: 4px 8px;
    }
    .table-borderless td:first-child {
        font-weight: 600;
    }
    .table-bordered td {
        vertical-align: middle;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush
