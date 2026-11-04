@extends('layouts.app')

@section('title', 'Equipment Report - KPLC Meter Lab')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-tools text-warning me-2"></i>
                Equipment Report
            </h1>
            <p class="text-muted mb-0">
                Laboratory equipment inventory report
            </p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <button onclick="window.print()" class="btn btn-primary ms-2">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <a href="{{ route('reports.equipment.export') }}" class="btn btn-success ms-2">
                <i class="fas fa-file-excel me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-hover mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.equipment') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control"
                           placeholder="Name, Serial, Model..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                        <option value="under_repair" {{ request('status') == 'under_repair' ? 'selected' : '' }}>Under Repair</option>
                        <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="condition" class="form-label">Condition</label>
                    <select name="condition" id="condition" class="form-select">
                        <option value="">All Conditions</option>
                        <option value="excellent" {{ request('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                        <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                        <option value="poor" {{ request('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                        <option value="needs_repair" {{ request('condition') == 'needs_repair' ? 'selected' : '' }}>Needs Repair</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('reports.equipment') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between">
                    <span>Total Equipment</span>
                    <span><strong>{{ $equipment->total() ?? 0 }}</strong></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between">
                    <span>Active</span>
                    <span><strong>{{ $stats['active_count'] ?? 0 }}</strong></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body d-flex justify-content-between">
                    <span>In Use</span>
                    <span><strong>{{ $stats['in_use_count'] ?? 0 }}</strong></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body d-flex justify-content-between">
                    <span>Under Repair</span>
                    <span><strong>{{ $stats['under_repair_count'] ?? 0 }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Equipment List
            <span class="badge bg-secondary ms-2">{{ $equipment->total() ?? 0 }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Equipment Name</th>
                            <th>Serial #</th>
                            <th>Model</th>
                            <th>Manufacturer</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Last Calibration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipment as $index => $item)
                            <tr>
                                <td>{{ $loop->iteration + ($equipment->currentPage() - 1) * $equipment->perPage() }}</td>
                                <td>
                                    <strong>{{ $item->name ?? 'N/A' }}</strong>
                                    @if($item->equipment_number)
                                        <br>
                                        <small class="text-muted">#{{ $item->equipment_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $item->serial_number ?? 'N/A' }}</code>
                                </td>
                                <td>{{ $item->model ?? 'N/A' }}</td>
                                <td>{{ $item->manufacturer ?? 'N/A' }}</td>
                                <td>
                                    @if($item->category)
                                        <span class="badge bg-info">{{ $item->category->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'in_use' => 'primary',
                                            'under_repair' => 'danger',
                                            'retired' => 'secondary',
                                            'inactive' => 'secondary',
                                        ];
                                        $statusClass = $statusColors[$item->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->status ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $conditionColors = [
                                            'excellent' => 'success',
                                            'good' => 'primary',
                                            'fair' => 'warning',
                                            'poor' => 'danger',
                                            'needs_repair' => 'danger',
                                        ];
                                        $conditionClass = $conditionColors[$item->condition] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $conditionClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->condition ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->last_calibration_date)
                                        {{ \Carbon\Carbon::parse($item->last_calibration_date)->format('Y-m-d') }}
                                        @php
                                            $daysSinceCal = \Carbon\Carbon::parse($item->last_calibration_date)->diffInDays(now());
                                            $interval = $item->calibration_interval_days ?? 365;
                                        @endphp
                                        @if($daysSinceCal > $interval)
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @elseif($daysSinceCal > $interval - 30)
                                            <span class="badge bg-warning ms-1">Expiring Soon</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Never</span>
                                        <span class="badge bg-danger ms-1">No Calibration</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('equipment.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('equipment.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-tools fa-2x text-muted d-block mb-2"></i>
                                    No equipment found matching your criteria
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
     {{ $equipment->appends(request()->query())->links() }}

</div>
@endsection

@push('styles')
<style>
.shadow-hover {
    transition: all 0.3s ease;
}
.shadow-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
}
@media print {
    .btn, .no-print, form {
        display: none !important;
    }
    .card {
        break-inside: avoid;
    }
}
</style>
@endpush
