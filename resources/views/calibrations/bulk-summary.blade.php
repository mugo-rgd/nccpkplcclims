@extends('layouts.app')

@section('title', 'Bulk Upload Summary')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-file-upload text-primary"></i> Bulk Upload Summary</h2>
                    <p class="text-muted mb-0">Batch ID: <strong>{{ $batchId }}</strong></p>
                </div>
                <div>
                    <a href="{{ route('calibrations.batch.details', $batchId) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Batch Details
                    </a>
                    <a href="{{ route('calibrations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Calibrations
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Batch Info -->
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-md-3">
                        <strong><i class="fas fa-tag"></i> Batch ID:</strong>
                        <p class="mb-0">{{ $batchId }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fas fa-building"></i> Customer:</strong>
                        <p class="mb-0">{{ $customer->company_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fas fa-boxes"></i> Total Equipment:</strong>
                        <p class="mb-0">{{ $total }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fas fa-clock"></i> Created:</strong>
                        <p class="mb-0">{{ now()->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $total }}</h3>
                            <p class="mb-0">Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3>{{ $pending ?? 0 }}</h3>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $quoted ?? 0 }}</h3>
                            <p class="mb-0">Quoted</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $completed ?? 0 }}</h3>
                            <p class="mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Bulk Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(($pending ?? 0) > 0)
                        <div class="col-md-3 mb-2">
                            <form action="{{ route('calibrations.bulk.quote', $batchId) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info w-100" onclick="return confirm('Generate a single quotation for all pending equipment in this batch?')">
                                    <i class="fas fa-file-invoice"></i> Generate Quotation ({{ $pending }})
                                </button>
                            </form>
                        </div>
                        @endif
                        @if(($quoted ?? 0) > 0)
                        <div class="col-md-3 mb-2">
                            <form action="{{ route('calibrations.bulk.payment', $batchId) }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_method" value="mpesa">
                                <input type="hidden" name="payment_reference" value="BULK-PAY-{{ $batchId }}">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Process payment for all quoted calibrations?')">
                                    <i class="fas fa-money-bill-wave"></i> Process Payment ({{ $quoted }})
                                </button>
                            </form>
                        </div>
                        @endif
                        @if(($pending ?? 0) > 0 || ($quoted ?? 0) > 0)
                        <div class="col-md-3 mb-2">
                            <form action="{{ route('calibrations.bulk.assign', $batchId) }}" method="POST">
                                @csrf
                                <select name="technician_id" class="form-control mb-2" required>
                                    <option value="">Select Technician</option>
                                    @foreach($technicians ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Assign all eligible calibrations to this technician?')">
                                    <i class="fas fa-user-check"></i> Bulk Assign Technician
                                </button>
                            </form>
                        </div>
                        @endif
                        @if(($completed ?? 0) > 0)
                        <div class="col-md-3 mb-2">
                            <form action="{{ route('calibrations.bulk.generate-certs', $batchId) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Generate certificates for all completed calibrations?')">
                                    <i class="fas fa-file-pdf"></i> Generate Certificates ({{ $completed }})
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Calibrations Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Calibrations in this Batch</h5>
                    <span class="badge bg-primary float-right">{{ $total }} Items</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Equipment Name</th>
                                    <th>Serial Number</th>
                                    <th>Type</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
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
                                                {{ $isPaymentCompleted ? 'Paid ✓' : 'Pending ⏳' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $calibration->status_color ?? 'secondary' }}">
                                            {{ ucfirst($calibration->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('calibrations.show', $calibration) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('calibrations.edit', $calibration) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-gray-300 d-block mb-3"></i>
                                        <p class="h5">No calibrations found in this batch</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
