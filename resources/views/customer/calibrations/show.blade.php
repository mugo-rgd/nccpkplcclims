@extends('layouts.app')

@section('title', 'Calibration #' . $calibration->id)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>
                    <i class="fas fa-microscope text-primary me-2"></i>
                    Calibration #{{ $calibration->id }}
                </h3>
                <div>
                    <a href="{{ route('customer.calibrations') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Calibrations
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Status Badge -->
            <div class="alert alert-{{ $calibration->status === 'completed' || $calibration->status === 'certified' ? 'success' : ($calibration->status === 'pending' ? 'warning' : ($calibration->status === 'rejected' ? 'danger' : 'info')) }}">
                <strong>Status:</strong>
                <span class="badge bg-{{ $calibration->status === 'completed' || $calibration->status === 'certified' ? 'success' : ($calibration->status === 'pending' ? 'warning' : ($calibration->status === 'rejected' ? 'danger' : 'info')) }}">
                    {{ ucfirst(str_replace('_', ' ', $calibration->status)) }}
                </span>
                @if($calibration->priority && $calibration->priority !== 'normal')
                    <span class="badge bg-{{ $calibration->priority === 'urgent' ? 'danger' : 'warning' }} ms-2">
                        <i class="fas fa-flag me-1"></i>
                        {{ ucfirst($calibration->priority) }}
                    </span>
                @endif
            </div>

            <!-- Equipment Details -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light p-3 mb-3">
                        <h5><i class="fas fa-tools me-2"></i>Equipment Details</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Equipment Name:</strong></td>
                                <td>{{ $calibration->equipment_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Serial Number:</strong></td>
                                <td>{{ $calibration->serial_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Manufacturer:</strong></td>
                                <td>{{ $calibration->manufacturer ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Model:</strong></td>
                                <td>{{ $calibration->model ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Equipment Type:</strong></td>
                                <td>{{ $calibration->equipment_type ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Accuracy Class:</strong></td>
                                <td>{{ $calibration->accuracy_class ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ratings:</strong></td>
                                <td>{{ $calibration->ratings ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Condition:</strong></td>
                                <td>{{ $calibration->equipment_condition ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light p-3 mb-3">
                        <h5><i class="fas fa-info-circle me-2"></i>Calibration Details</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Calibration ID:</strong></td>
                                <td>#{{ $calibration->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $calibration->calibration_location ?? 'N/A')) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Priority:</strong></td>
                                <td>{{ ucfirst($calibration->priority ?? 'Normal') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Assigned To:</strong></td>
                                <td>{{ $calibration->performedBy->name ?? 'Not Assigned' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Scheduled Date:</strong></td>
                                <td>{{ $calibration->scheduled_date ? \Carbon\Carbon::parse($calibration->scheduled_date)->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Completed At:</strong></td>
                                <td>{{ $calibration->completed_at ? \Carbon\Carbon::parse($calibration->completed_at)->format('d/m/Y H:i') : 'Pending' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $calibration->created_at ? $calibration->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Related Documents -->
            @if($quote || $certificate || $jobCard)
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light p-3 mb-3">
                            <h5><i class="fas fa-folder-open me-2"></i>Related Documents</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @if($quote)
                                    <a href="{{ route('customer.quotes.show', $quote->id) }}" class="btn btn-success">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>
                                        View Quote
                                    </a>
                                @endif
                                @if($certificate)
                                    <a href="{{ route('customer.certificates.show', $certificate->id) }}" class="btn btn-info">
                                        <i class="fas fa-certificate me-1"></i>
                                        View Certificate
                                    </a>
                                @endif
                                @if($jobCard)
                                    <a href="#" class="btn btn-secondary">
                                        <i class="fas fa-clipboard-list me-1"></i>
                                        View Job Card
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($calibration->notes)
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light p-3">
                            <h5><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                            <p class="text-muted">{{ $calibration->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif
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
</style>
@endpush
