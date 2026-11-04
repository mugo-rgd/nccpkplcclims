@extends('layouts.app')

@section('title', 'Calibrations Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-microscope text-primary me-2"></i>
                Calibrations Report
            </h1>
            <p class="text-muted mb-0">
                Detailed report of all calibration activities
            </p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.calibrations') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="certified" {{ request('status') == 'certified' ? 'selected' : '' }}>Certified</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.calibrations') }}" class="btn btn-secondary">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Equipment</th>
                            <th>Serial #</th>
                            <th>Status</th>
                            <th>Technician</th>
                            <th>Created</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calibrations as $calibration)
                            <tr>
                                <td>{{ $calibration->id }}</td>
                                <td>{{ optional($calibration->customer)->company_name ?? 'N/A' }}</td>
                                <td>{{ $calibration->equipment_name ?? 'N/A' }}</td>
                                <td><code>{{ $calibration->equipment_serial ?? 'N/A' }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $calibration->status === 'completed' ? 'success' : ($calibration->status === 'pending' ? 'warning' : 'primary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $calibration->status ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td>
                                    {{ optional($calibration->performedBy)->name ?? 'Unassigned' }}
                                </td>
                                <td>{{ $calibration->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $calibration->completed_date ? \Carbon\Carbon::parse($calibration->completed_date)->format('Y-m-d H:i') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted d-block mb-2"></i>
                                    No calibrations found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    {{ $calibrations->appends(request()->query())->links() }}
</div>
@endsection
