@extends('layouts.app')

@section('title', 'Quality Control Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-clipboard-check text-success me-2"></i>
                Quality Control Dashboard
            </h1>
            <p class="text-muted mb-0">
                Manage and oversee quality control for all calibrations
            </p>
        </div>
        <div>
            <a href="{{ route('admin.quality.reports') }}" class="btn btn-info">
                <i class="fas fa-chart-bar me-1"></i> View Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-clock me-1"></i> Pending Checks
                    </h6>
                    <h2 class="mb-0">{{ $stats['pending_quality_checks'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-check-circle me-1"></i> Total Checks
                    </h6>
                    <h2 class="mb-0">{{ $stats['total_quality_checks'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-check-double me-1"></i> Passed
                    </h6>
                    <h2 class="mb-0">{{ $stats['passed_quality'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-times-circle me-1"></i> Failed
                    </h6>
                    <h2 class="mb-0">{{ $stats['failed_quality'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Quality Checks -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-1"></i>
                Pending Quality Checks
            </h5>
        </div>
        <div class="card-body p-0">
            @if($pendingChecks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Equipment</th>
                                <th>Technician</th>
                                <th>Test Results</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingChecks as $calibration)
                                <tr>
                                    <td>#{{ $calibration->id }}</td>
                                    <td>{{ optional($calibration->customer)->company_name ?? 'N/A' }}</td>
                                    <td>{{ $calibration->equipment_name ?? 'N/A' }}</td>
                                    <td>{{ optional($calibration->performedBy)->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $calibration->testResults->count() }} tests</span>
                                    </td>
                                    <td>{{ $calibration->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.quality.check', $calibration) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-clipboard-check me-1"></i> Check
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $pendingChecks->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No pending quality checks. All calibrations have been reviewed.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
