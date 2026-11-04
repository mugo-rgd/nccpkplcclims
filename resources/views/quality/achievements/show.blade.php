@extends('layouts.app')

@section('title', 'Achievement Details')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Achievement Details</h1>
        <div>
            <a href="{{ route('quality.achievements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @can('edit_achievements')
            <a href="{{ route('quality.achievements.edit', $achievement->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Objective:</strong> {{ $achievement->objective->objective_id ?? 'N/A' }}</p>
                    <p><strong>Description:</strong> {{ $achievement->objective->description ?? 'N/A' }}</p>
                    <p><strong>Quarter:</strong> {{ $achievement->quarter_label }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Achievement:</strong> {{ $achievement->actual_achievement }}</p>
                    <p><strong>Target:</strong> {{ $achievement->objective->target_value ?? 'N/A' }}</p>
                    <p><strong>Performance:</strong> {{ round($achievement->achievement_percentage ?? 0, 2) }}%</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $achievement->status == 'On Track' ? 'success' : ($achievement->status == 'At Risk' ? 'warning' : 'danger') }}">
                            {{ $achievement->status }}
                        </span>
                    </p>
                </div>
            </div>
            @if($achievement->remarks)
            <div class="row mt-3">
                <div class="col-12">
                    <p><strong>Remarks:</strong> {{ $achievement->remarks }}</p>
                </div>
            </div>
            @endif
            @if($achievement->evidence_url)
            <div class="row mt-3">
                <div class="col-12">
                    <p><strong>Evidence:</strong> <a href="{{ $achievement->evidence_url }}" target="_blank">{{ $achievement->evidence_url }}</a></p>
                </div>
            </div>
            @endif
            <div class="row mt-3">
                <div class="col-12">
                    <p><strong>Collected By:</strong> {{ $achievement->collectedBy->name ?? 'N/A' }}</p>
                    <p><strong>Collection Date:</strong> {{ $achievement->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
