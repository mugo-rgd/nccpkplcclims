@extends('layouts.app')

@section('title', 'Quality Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                Quality Reports
            </h1>
            <p class="text-muted mb-0">
                View and analyze quality control statistics
            </p>
        </div>
        <div>
            <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Checks</h6>
                    <h2 class="mb-0">{{ $stats['total_checked'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Passed</h6>
                    <h2 class="mb-0">{{ $stats['passed'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Failed</h6>
                    <h2 class="mb-0">{{ $stats['failed'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Pass Rate</h6>
                    <h2 class="mb-0">{{ $stats['pass_rate'] ?? 0 }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                </div>
                <div class="col-md-4">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <select name="decision" class="form-select">
                        <option value="">All Decisions</option>
                        <option value="passed" {{ request('decision') == 'passed' ? 'selected' : '' }}>Passed</option>
                        <option value="failed" {{ request('decision') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Equipment</th>
                            <th>Decision</th>
                            <th>Checked By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>#{{ $report->id }}</td>
                                <td>{{ optional($report->customer)->company_name ?? 'N/A' }}</td>
                                <td>{{ $report->equipment_name ?? 'N/A' }}</td>
                                <td>
                                    @if($report->quality_approved)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i> Passed
                                        </span>
                                    @elseif($report->quality_rejected)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i> Failed
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>{{ optional($report->qualityCheckedBy)->name ?? 'N/A' }}</td>
                                <td>
                                    @if($report->quality_checked_at)
                                        @php
                                            try {
                                                $checkedAt = $report->quality_checked_at instanceof \Carbon\Carbon
                                                    ? $report->quality_checked_at
                                                    : \Carbon\Carbon::parse($report->quality_checked_at);
                                            } catch (\Exception $e) {
                                                $checkedAt = null;
                                            }
                                        @endphp
                                        @if($checkedAt)
                                            {{ $checkedAt->format('Y-m-d H:i') }}
                                            <br>
                                            <small class="text-muted">{{ $checkedAt->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Invalid date</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.quality.check', $report) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted d-block mb-2"></i>
                                    <p class="text-muted">No quality reports found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <span class="text-muted">
                Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
            </span>
        </div>
        <div>
            {{ $reports->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
