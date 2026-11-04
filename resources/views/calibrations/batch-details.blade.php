@extends('layouts.app')

@section('title', 'Batch Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-layer-group text-primary"></i> Batch Details
            </h1>
            <p class="text-muted mb-0">Batch ID: <strong>{{ $batchId }}</strong></p>
            <p class="text-muted mb-0">Customer: <strong>{{ $summary['customer'] ?? 'N/A' }}</strong></p>
        </div>
        <div>
            <a href="{{ route('calibrations.batches') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Batches
            </a>
            <a href="{{ route('calibrations.bulk-upload') }}" class="btn btn-success">
                <i class="fas fa-upload"></i> New Batch
            </a>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @foreach($summary['statuses'] as $status => $count)
                            @php
                                $statusColor = match($status) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    'quoted' => 'info',
                                    'paid' => 'success',
                                    'assigned' => 'primary',
                                    'in_progress' => 'info',
                                    'testing' => 'info',
                                    'certified' => 'success',
                                    'dispatched' => 'secondary',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $statusColor }} p-2" style="font-size: 0.9rem;">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}: <strong>{{ $count }}</strong>
                            </span>
                        @endforeach
                        <span class="badge bg-secondary p-2" style="font-size: 0.9rem;">
                            <i class="fas fa-boxes"></i> Total: <strong>{{ $summary['total'] }}</strong>
                        </span>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted">
                            <i class="fas fa-info-circle text-info"></i>
                            <strong>Batch Quotation</strong> can be generated for <strong>pending</strong> calibrations.
                            @if(($summary['statuses']['pending'] ?? 0) == 0)
                                <span class="text-danger ms-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No pending calibrations available.
                                    @if(($summary['statuses']['quoted'] ?? 0) > 0)
                                        <span class="text-warning">Calibrations are already quoted.</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-success ms-2">
                                    <i class="fas fa-check-circle"></i>
                                    {{ $summary['statuses']['pending'] ?? 0 }} pending calibration(s) ready for quotation.
                                </span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $summary['total'] }}</h3>
                    <p class="mb-0">Total Equipment</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $summary['statuses']['completed'] ?? 0 }}</h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3>{{ $summary['statuses']['pending'] ?? 0 }}</h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $summary['statuses']['quoted'] ?? 0 }}</h3>
                    <p class="mb-0">Quoted</p>
                </div>
            </div>
        </div>
    </div>

 <!-- Batch Quotation -->
@if(isset($summary['quotation']) && $summary['quotation'])
<div class="card shadow mb-4 border-success">
    <div class="card-header bg-success text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-file-invoice"></i> Batch Quotation
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Quote Number:</strong> {{ $summary['quotation']->quote_number ?? 'N/A' }}</p>
                <p><strong>Quote Date:</strong> {{ isset($summary['quotation']->created_at) ? $summary['quotation']->created_at->format('Y-m-d H:i') : 'N/A' }}</p>
                <p><strong>Valid Until:</strong> {{ isset($summary['quotation']->valid_until) ? \Carbon\Carbon::parse($summary['quotation']->valid_until)->format('Y-m-d') : 'N/A' }}</p>
                <p><strong>Total Equipment:</strong> {{ $summary['quotation']->items ? count(json_decode($summary['quotation']->items, true)) : 0 }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Subtotal:</strong> KSh {{ number_format($summary['quotation']->subtotal ?? 0, 2) }}</p>
                <p><strong>Tax ({{ $summary['quotation']->tax_rate ?? 16 }}%):</strong> KSh {{ number_format($summary['quotation']->tax_amount ?? 0, 2) }}</p>
                <p><strong>Total Amount:</strong>
                    <span class="badge bg-success fs-6">
                        KSh {{ number_format($summary['quotation']->total_amount ?? 0, 2) }}
                    </span>
                </p>
                <p><strong>Status:</strong>
                    <span class="badge bg-{{ $summary['quotation']->status == 'paid' ? 'success' : ($summary['quotation']->status == 'sent' ? 'info' : 'warning') }}">
                        {{ ucfirst($summary['quotation']->status ?? 'Draft') }}
                    </span>
                </p>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('calibrations.batch.quotation.preview', $batchId) }}"
               class="btn btn-info" target="_blank">
                <i class="fas fa-eye"></i> Preview PDF
            </a>
            <a href="{{ route('calibrations.batch.quotation.download', $batchId) }}"
               class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>
</div>
@else
<!-- No Quotation Yet -->
<div class="card shadow mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-file-invoice"></i> Batch Quotation
        </h6>
    </div>
    <div class="card-body text-center py-4">
        <i class="fas fa-file-invoice fa-3x text-muted d-block mb-3"></i>
        <p class="h5">No quotation generated yet</p>
        <p class="text-muted">Use the "Generate Batch Quotation" button above to create a quotation.</p>
        @if(($summary['statuses']['pending'] ?? 0) > 0)
            <form action="{{ route('calibrations.bulk.quote', $batchId) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-file-invoice"></i> Generate Batch Quotation Now
                </button>
            </form>
        @endif
    </div>
</div>
@endif



    <!-- Batch Actions -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks"></i> Batch Actions
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <form action="{{ route('calibrations.bulk.quote', $batchId) }}" method="POST">
                        @csrf
                        @if(($summary['statuses']['pending'] ?? 0) > 0)
                            <button type="submit" class="btn btn-info w-100" onclick="return confirm('Generate a single quotation for all equipment in this batch?')">
                                <i class="fas fa-file-invoice"></i> Generate Batch Quotation ({{ $summary['statuses']['pending'] ?? 0 }})
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-file-invoice"></i> No Pending Items
                            </button>
                        @endif
                    </form>
                </div>
                <div class="col-md-3 mb-2">
                    <form action="{{ route('calibrations.batch.process', $batchId) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="assign">
                        <select name="technician_id" class="form-control mb-2" required>
                            <option value="">Select Technician</option>
                            @foreach($technicians as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @php
                            $assignableCount = $calibrations->filter(function($c) {
                                return in_array($c->status, ['pending', 'quoted', 'paid']) &&
                                       (!$c->requiresPayment() || $c->isPaymentCompleted());
                            })->count();
                        @endphp
                        @if($assignableCount > 0)
                            <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Assign all eligible calibrations to this technician?')">
                                <i class="fas fa-user-check"></i> Assign Technician ({{ $assignableCount }})
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-user-check"></i> No Eligible Items
                            </button>
                        @endif
                    </form>
                </div>
                <div class="col-md-3 mb-2">
                    <form action="{{ route('calibrations.batch.process', $batchId) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="complete">
                        @php
                            $completeCount = $calibrations->filter(function($c) {
                                return in_array($c->status, ['testing', 'assigned', 'in_progress']);
                            })->count();
                        @endphp
                        @if($completeCount > 0)
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark all eligible calibrations as completed?')">
                                <i class="fas fa-check"></i> Complete All ({{ $completeCount }})
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-check"></i> No Items to Complete
                            </button>
                        @endif
                    </form>
                </div>
                <div class="col-md-3 mb-2">
                    <form action="{{ route('calibrations.batch.process', $batchId) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Delete all calibrations in this batch? This action cannot be undone.')">
                            <i class="fas fa-trash"></i> Delete Batch
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Documents -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-file-pdf"></i> Batch Documents
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="{{ route('calibrations.batch.job-card', $batchId) }}" class="btn btn-primary w-100" target="_blank">
                    <i class="fas fa-clipboard-list"></i> Job Card
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('calibrations.batch.request-form', $batchId) }}" class="btn btn-info w-100" target="_blank">
                    <i class="fas fa-file-signature"></i> Request Form
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('calibrations.batch.delivery-note', $batchId) }}" class="btn btn-success w-100" target="_blank">
                    <i class="fas fa-truck"></i> Delivery Note
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('calibrations.batch.collection-note', $batchId) }}" class="btn btn-warning w-100" target="_blank">
                    <i class="fas fa-hand-holding"></i> Collection Note
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- Calibrations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Calibrations in this Batch
                <span class="badge bg-primary float-right">{{ $calibrations->count() }} Items</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Equipment Name</th>
                            <th>Serial Number</th>
                            <th>Type</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th>Technician</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calibrations as $calibration)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $calibration->equipment_name }}</td>
                            <td>{{ $calibration->serial_number }}</td>
                            <td>{{ $calibration->equipmentType->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $requiresPayment = $calibration->requiresPayment();
                                    $isPaymentCompleted = $calibration->isPaymentCompleted();
                                @endphp
                                @if($requiresPayment)
                                    <span class="badge bg-{{ $isPaymentCompleted ? 'success' : 'danger' }}">
                                        <i class="fas {{ $isPaymentCompleted ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                        {{ $isPaymentCompleted ? 'Paid ✓' : 'Pending ⏳' }}
                                    </span>
                                    @if(!$isPaymentCompleted && $calibration->amount_due > 0)
                                        <br>
                                        <small class="text-muted">KSh {{ number_format($calibration->amount_due - ($calibration->amount_paid ?? 0), 2) }} due</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $calibration->status_color ?? 'secondary' }}">
                                    {{ ucfirst($calibration->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $calibration->performedBy->name ?? 'Unassigned' }}</td>
                            <td>
                                <a href="{{ route('calibrations.show', $calibration) }}"
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('calibrations.edit', $calibration) }}"
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-gray-300 d-block mb-3"></i>
                                <p class="h5">No calibrations in this batch</p>
                                <p class="text-muted">This batch is empty.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
