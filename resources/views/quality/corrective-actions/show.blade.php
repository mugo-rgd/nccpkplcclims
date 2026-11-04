@extends('layouts.app')

@section('title', 'View Corrective Action')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Corrective Action Details</h1>
            <p class="text-muted mb-0">{{ $correctiveAction->car_id }}</p>
        </div>
        <div>
            <a href="{{ route('quality.corrective-actions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @can('edit_corrective_actions')
            <a href="{{ route('quality.corrective-actions.edit', $correctiveAction->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> CAR Information
                    </h6>
                    @if($correctiveAction->status == 'Open')
                        <span class="badge bg-danger">Open</span>
                    @elseif($correctiveAction->status == 'In Progress')
                        <span class="badge bg-warning">In Progress</span>
                    @else
                        <span class="badge bg-success">Closed</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">CAR ID:</div>
                        <div class="col-md-9">{{ $correctiveAction->car_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Objective:</div>
                        <div class="col-md-9">
                            <a href="{{ route('quality.objectives.show', $correctiveAction->objective_id) }}">
                                {{ $correctiveAction->objective->objective_id ?? 'N/A' }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $correctiveAction->objective->description ?? 'N/A' }}</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Issue Description:</div>
                        <div class="col-md-9">{{ $correctiveAction->issue_description }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Root Cause:</div>
                        <div class="col-md-9">{{ $correctiveAction->root_cause ?? 'Not specified' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Action Plan:</div>
                        <div class="col-md-9">{{ $correctiveAction->action_plan }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Verification Notes:</div>
                        <div class="col-md-9">{{ $correctiveAction->verification_notes ?? 'Not specified' }}</div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock"></i> Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6>Opened</h6>
                                <p>{{ $correctiveAction->opened_date->format('Y-m-d H:i:s') }}</p>
                                <small class="text-muted">CAR created</small>
                            </div>
                        </div>
                        @if($correctiveAction->status != 'Open')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6>In Progress</h6>
                                <p>{{ $correctiveAction->updated_at->format('Y-m-d H:i:s') }}</p>
                                <small class="text-muted">Action started</small>
                            </div>
                        </div>
                        @endif
                        @if($correctiveAction->status == 'Closed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6>Closed</h6>
                                <p>{{ $correctiveAction->closed_date->format('Y-m-d H:i:s') }}</p>
                                <small class="text-muted">Issue resolved</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Status Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-5 fw-bold">Status:</div>
                        <div class="col-7">
                            @if($correctiveAction->status == 'Open')
                                <span class="badge bg-danger">Open</span>
                            @elseif($correctiveAction->status == 'In Progress')
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-success">Closed</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-bold">Owner:</div>
                        <div class="col-7">{{ $correctiveAction->actionOwner->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-bold">Opened:</div>
                        <div class="col-7">{{ $correctiveAction->opened_date->format('Y-m-d') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-bold">Deadline:</div>
                        <div class="col-7">
                            {{ $correctiveAction->deadline->format('Y-m-d') }}
                            @php
                                $daysLeft = now()->diffInDays($correctiveAction->deadline, false);
                            @endphp
                            @if($correctiveAction->status != 'Closed')
                                @if($daysLeft < 0)
                                    <br><span class="badge bg-danger">Overdue by {{ abs($daysLeft) }} days</span>
                                @elseif($daysLeft <= 7)
                                    <br><span class="badge bg-warning">{{ $daysLeft }} days remaining</span>
                                @else
                                    <br><span class="badge bg-info">{{ $daysLeft }} days remaining</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    @if($correctiveAction->closed_date)
                    <div class="row mb-3">
                        <div class="col-5 fw-bold">Closed:</div>
                        <div class="col-7">{{ $correctiveAction->closed_date->format('Y-m-d') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @can('approve_corrective_actions')
            @if($correctiveAction->status != 'Closed')
            <div class="card shadow mb-4">
                <div class="card-body">
                    @if($correctiveAction->status == 'Open')
                    <form action="{{ route('quality.corrective-actions.updateStatus', $correctiveAction->id) }}" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="status" value="In Progress">
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Mark this CAR as In Progress?')">
                            <i class="fas fa-play"></i> Start Progress
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('quality.corrective-actions.approve', $correctiveAction->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve and close this corrective action?')">
                            <i class="fas fa-check"></i> Approve & Close
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endcan

            <!-- Related Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link"></i> Related Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Created:</strong>
                        <br>{{ $correctiveAction->created_at->format('Y-m-d H:i:s') }}
                    </div>
                    <div class="mb-2">
                        <strong>Last Updated:</strong>
                        <br>{{ $correctiveAction->updated_at->format('Y-m-d H:i:s') }}
                    </div>
                    @can('view_quality_objectives')
                    <a href="{{ route('quality.objectives.show', $correctiveAction->objective_id) }}"
                       class="btn btn-sm btn-info w-100">
                        <i class="fas fa-bullseye"></i> View Related Objective
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    top: 5px;
}
.timeline-content {
    padding-left: 10px;
}
.timeline-content h6 {
    margin-bottom: 2px;
}
.timeline-content p {
    margin-bottom: 2px;
    font-size: 0.9rem;
}
</style>
@endpush
