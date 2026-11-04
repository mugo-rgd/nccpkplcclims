@extends('layouts.app')

@section('title', 'Edit Achievement')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Achievement</h1>
            <p class="text-muted mb-0">Update achievement record</p>
        </div>
        <a href="{{ route('quality.achievements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-edit"></i> Edit Achievement Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.achievements.update', $achievement->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Objective <span class="text-danger">*</span></label>
                            <select name="objective_id" class="form-select @error('objective_id') is-invalid @enderror" required>
                                @foreach($objectives as $id => $description)
                                    <option value="{{ $id }}" {{ old('objective_id', $achievement->objective_id) == $id ? 'selected' : '' }}>
                                        {{ $description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('objective_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">FYR Period <span class="text-danger">*</span></label>
                            <select name="fyr_period_id" class="form-select @error('fyr_period_id') is-invalid @enderror" required>
                                @foreach($fyrPeriods as $fyr)
                                    <option value="{{ $fyr->id }}"
                                        {{ old('fyr_period_id', $achievement->fyr_period_id) == $fyr->id ? 'selected' : '' }}>
                                        {{ $fyr->display_name }}
                                        @if($fyr->is_active) (Current) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('fyr_period_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Quarter <span class="text-danger">*</span></label>
                            <select name="quarter" class="form-select @error('quarter') is-invalid @enderror" required>
                                @foreach($quarters as $key => $label)
                                    <option value="{{ $key }}" {{ old('quarter', $achievement->quarter) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('quarter')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Quarter Label <span class="text-danger">*</span></label>
                            <input type="text" name="quarter_label"
                                   class="form-control @error('quarter_label') is-invalid @enderror"
                                   value="{{ old('quarter_label', $achievement->quarter_label) }}" required>
                            @error('quarter_label')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Data Collection Date <span class="text-danger">*</span></label>
                            <input type="date" name="data_collection_date"
                                   class="form-control @error('data_collection_date') is-invalid @enderror"
                                   value="{{ old('data_collection_date', $achievement->data_collection_date->format('Y-m-d')) }}" required>
                            @error('data_collection_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Actual Achievement <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="actual_achievement"
                                   class="form-control @error('actual_achievement') is-invalid @enderror"
                                   value="{{ old('actual_achievement', $achievement->actual_achievement) }}" required>
                            @error('actual_achievement')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Evidence URL</label>
                            <input type="url" name="evidence_url"
                                   class="form-control @error('evidence_url') is-invalid @enderror"
                                   value="{{ old('evidence_url', $achievement->evidence_url) }}"
                                   placeholder="https://example.com/evidence.pdf">
                            @error('evidence_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Remarks</label>
                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                              rows="2">{{ old('remarks', $achievement->remarks) }}</textarea>
                    @error('remarks')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Current Status Display -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Current Achievement:</strong> {{ $achievement->actual_achievement }}
                            </div>
                            <div class="col-md-4">
                                <strong>Performance:</strong>
                                <span class="badge
                                    @if($achievement->achievement_percentage >= 95) bg-success
                                    @elseif($achievement->achievement_percentage >= 80) bg-warning
                                    @else bg-danger
                                    @endif">
                                    {{ round($achievement->achievement_percentage ?? 0) }}%
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong>
                                <span class="badge
                                    @if($achievement->status == 'On Track') bg-success
                                    @elseif($achievement->status == 'At Risk') bg-warning
                                    @elseif($achievement->status == 'Not Met') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ $achievement->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('quality.achievements.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Achievement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
