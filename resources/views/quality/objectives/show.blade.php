@extends('layouts.app')

@section('title', 'View Quality Objective')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Quality Objective Details</h1>
            <p class="text-muted mb-0">{{ $objective->objective_id }} - {{ $objective->section }}</p>
        </div>
        <div>
            <a href="{{ route('quality.objectives.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @can('edit_quality_objectives')
            <a href="{{ route('quality.objectives.edit', $objective->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Objective Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Objective ID:</div>
                        <div class="col-md-9">{{ $objective->objective_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Description:</div>
                        <div class="col-md-9">{{ $objective->description }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Section:</div>
                        <div class="col-md-9">
                            <span class="badge bg-secondary">{{ $objective->section }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Unit of Measure:</div>
                        <div class="col-md-9">{{ $objective->unit_of_measure }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Target:</div>
                        <div class="col-md-9">
                            {{ $objective->target_value }} {{ $objective->unit_of_measure }}
                            @if($objective->baseline_value)
                                <br><small class="text-muted">Baseline: {{ $objective->baseline_value }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Target FYR:</div>
                        <div class="col-md-9">{{ $objective->target_fyr }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Owner:</div>
                        <div class="col-md-9">{{ $objective->owner->name ?? 'Unassigned' }}</div>
                    </div>
                    @if($objective->iso17025_clause)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">ISO 17025:</div>
                        <div class="col-md-9">
                            {{ $objective->iso17025_clause }}
                            @if($objective->iso17025_requirement)
                                - {{ $objective->iso17025_requirement }}
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($objective->remarks)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Remarks:</div>
                        <div class="col-md-9">{{ $objective->remarks }}</div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Status:</div>
                        <div class="col-md-9">
                            @if($objective->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Created:</div>
                        <div class="col-md-9">{{ $objective->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Last Updated:</div>
                        <div class="col-md-9">{{ $objective->updated_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <!-- Historical Targets -->
            @if(count($objective->target_trend) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Performance Trend
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>FYR</th>
                                    <th>Target</th>
                                    <th>Achievement</th>
                                    <th>Performance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($objective->target_trend as $trend)
                                <tr>
                                    <td>{{ $trend['fyr'] }}</td>
                                    <td>{{ $trend['target'] }}</td>
                                    <td>{{ $trend['achievement'] ?? 'Not tracked' }}</td>
                                    <td>
                                        @if($trend['achievement'])
                                            {{ round(($trend['achievement'] / $trend['target']) * 100, 2) }}%
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($trend['achievement'])
                                            @php
                                                $pct = ($trend['achievement'] / $trend['target']) * 100;
                                            @endphp
                                            @if($pct >= 95)
                                                <span class="badge bg-success">On Track</span>
                                            @elseif($pct >= 80)
                                                <span class="badge bg-warning">At Risk</span>
                                            @else
                                                <span class="badge bg-danger">Not Met</span>
                                            @endif
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
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Current Achievement -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bullseye"></i> Current Achievement
                    </h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $latest = $objective->latest_achievement;
                        $percentage = $objective->achievement_percentage;
                    @endphp
                    @if($latest)
                        <div class="display-4 mb-2">{{ $latest->actual_achievement }}</div>
                        <div class="text-muted mb-2">{{ $objective->unit_of_measure }}</div>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar
                                @if($percentage >= 95) bg-success
                                @elseif($percentage >= 80) bg-warning
                                @else bg-danger
                                @endif"
                                role="progressbar"
                                style="width: {{ min($percentage ?? 0, 100) }}%;
                                       font-weight: bold; font-size: 14px;">
                                {{ round($percentage ?? 0) }}%
                            </div>
                        </div>
                        <div class="text-muted">
                            Target: {{ $objective->target_value }} {{ $objective->unit_of_measure }}
                        </div>
                        <div class="mt-2">
                            <span class="badge
                                @if($percentage >= 95) bg-success
                                @elseif($percentage >= 80) bg-warning
                                @else bg-danger
                                @endif">
                                {{ $latest->status }}
                            </span>
                        </div>
                        <small class="text-muted d-block mt-2">
                            {{ $latest->quarter_label }} - {{ $latest->created_at->format('Y-m-d') }}
                        </small>
                    @else
                        <div class="py-4">
                            <i class="fas fa-clock fa-3x text-gray-300 d-block mb-2"></i>
                            <p>No achievement recorded yet</p>
                            @can('create_achievements')
                            <a href="{{ route('quality.achievements.create', ['objective_id' => $objective->id]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Record Achievement
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            <!-- Corrective Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Corrective Actions
                    </h6>
                    @can('create_corrective_actions')
                    <a href="{{ route('quality.corrective-actions.create', ['objective_id' => $objective->id]) }}"
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($objective->correctiveActions->count() > 0)
                        @foreach($objective->correctiveActions as $car)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $car->car_id }}</strong>
                                    <div class="small text-muted">{{ Str::limit($car->issue_description, 50) }}</div>
                                </div>
                                <div>
                                    @if($car->status == 'Open')
                                        <span class="badge bg-danger">Open</span>
                                    @elseif($car->status == 'In Progress')
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-success">Closed</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Owner: {{ $car->actionOwner->name ?? 'N/A' }}</small>
                                <small class="text-muted">Deadline: {{ $car->deadline->format('Y-m-d') }}</small>
                            </div>
                            <a href="{{ route('quality.corrective-actions.show', $car->id) }}" class="btn btn-sm btn-link p-0">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-2">No corrective actions</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
