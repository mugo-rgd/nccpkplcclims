@extends('layouts.app')

@section('title', 'Quality Report')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Quality Report</h1>
            <p class="text-muted mb-0">
                {{ $section == 'All' ? 'All Sections' : $section }} |
                {{ $fyr }} |
                Status: {{ $status }}
            </p>
        </div>
        <div>
            <a href="{{ route('quality.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Report Header -->
    <div class="card shadow mb-4">
        <div class="card-body text-center">
            <h2>Quality Management Report</h2>
            <p class="text-muted">
                Generated on {{ $generated_at->format('Y-m-d H:i:s') }} by {{ $generated_by }}
            </p>
            <p>
                <span class="badge bg-primary">{{ $fyr }}</span>
                <span class="badge bg-secondary">{{ $section }}</span>
                <span class="badge bg-info">{{ $status }}</span>
            </p>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Objectives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                On Track
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['on_track'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                At Risk
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['at_risk'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Not Met
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['not_met'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-bar"></i> Performance Summary
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Overall Achievement</h6>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar
                            @if($summary['avg_achievement'] >= 95) bg-success
                            @elseif($summary['avg_achievement'] >= 80) bg-warning
                            @else bg-danger
                            @endif"
                            role="progressbar"
                            style="width: {{ min($summary['avg_achievement'] ?? 0, 100) }}%; font-weight: bold; font-size: 16px;">
                            {{ round($summary['avg_achievement'] ?? 0) }}%
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Completion Rate:</small>
                            <br><strong>{{ $summary['completion_rate'] }}%</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Tracked:</small>
                            <br><strong>{{ $summary['tracked'] }} / {{ $summary['total'] }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Status Distribution</h6>
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="badge bg-success">On Track</span>
                            <br><strong>{{ $summary['on_track'] }}</strong>
                        </div>
                        <div>
                            <span class="badge bg-warning">At Risk</span>
                            <br><strong>{{ $summary['at_risk'] }}</strong>
                        </div>
                        <div>
                            <span class="badge bg-danger">Not Met</span>
                            <br><strong>{{ $summary['not_met'] }}</strong>
                        </div>
                        <div>
                            <span class="badge bg-secondary">Pending</span>
                            <br><strong>{{ $summary['pending'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Analysis -->
    @if(count($section_analysis) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-layer-group"></i> Section Analysis
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Section</th>
                            <th>Total</th>
                            <th>Tracked</th>
                            <th>On Track</th>
                            <th>At Risk</th>
                            <th>Not Met</th>
                            <th>Avg Achievement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section_analysis as $section => $data)
                        <tr>
                            <td><strong>{{ $section }}</strong></td>
                            <td>{{ $data['total'] }}</td>
                            <td>{{ $data['tracked'] }}</td>
                            <td>{{ $data['on_track'] }}</td>
                            <td>{{ $data['at_risk'] }}</td>
                            <td>{{ $data['not_met'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                        <div class="progress-bar
                                            @if($data['avg_achievement'] >= 95) bg-success
                                            @elseif($data['avg_achievement'] >= 80) bg-warning
                                            @else bg-danger
                                            @endif"
                                            role="progressbar"
                                            style="width: {{ min($data['avg_achievement'] ?? 0, 100) }}%;">
                                        </div>
                                    </div>
                                    <span>{{ round($data['avg_achievement'] ?? 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Objectives -->
    @if(count($objectives) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Detailed Objectives
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Section</th>
                            <th>Target</th>
                            <th>Actual</th>
                            <th>Performance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($objectives as $objective)
                        <tr>
                            <td>{{ $objective->objective_id }}</td>
                            <td>{{ Str::limit($objective->description, 40) }}</td>
                            <td>{{ $objective->section }}</td>
                            <td>{{ $objective->target_value }}</td>
                            <td>
                                @php
                                    $achievement = $objective->achievements->first();
                                @endphp
                                {{ $achievement ? $achievement->actual_achievement : 'N/A' }}
                            </td>
                            <td>
                                @if($achievement)
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar
                                            @if($achievement->achievement_percentage >= 95) bg-success
                                            @elseif($achievement->achievement_percentage >= 80) bg-warning
                                            @else bg-danger
                                            @endif"
                                            role="progressbar"
                                            style="width: {{ min($achievement->achievement_percentage ?? 0, 100) }}%;">
                                        </div>
                                    </div>
                                    <small>{{ round($achievement->achievement_percentage ?? 0) }}%</small>
                                @else
                                    <span class="text-muted">Not tracked</span>
                                @endif
                            </td>
                            <td>
                                @if($achievement)
                                    <span class="badge
                                        @if($achievement->status == 'On Track') bg-success
                                        @elseif($achievement->status == 'At Risk') bg-warning
                                        @elseif($achievement->status == 'Not Met') bg-danger
                                        @else bg-secondary
                                        @endif">
                                        {{ $achievement->status }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="text-center text-muted">
        <small>
            Report generated on {{ $generated_at->format('Y-m-d H:i:s') }} |
            {{ $objectives->count() }} objectives included
        </small>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .no-print {
        display: none !important;
    }
    .card {
        break-inside: avoid;
    }
}
</style>
@endpush
