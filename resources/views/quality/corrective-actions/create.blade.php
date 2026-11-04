@extends('layouts.app')

@section('title', 'Create Corrective Action')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create Corrective Action</h1>
            <p class="text-muted mb-0">Document and track corrective actions for quality issues</p>
        </div>
        <a href="{{ route('quality.corrective-actions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-plus-circle"></i> CAR Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.corrective-actions.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">CAR ID <span class="text-danger">*</span></label>
                            <input type="text" name="car_id"
                                   class="form-control @error('car_id') is-invalid @enderror"
                                   value="{{ old('car_id', $carId ?? '') }}" readonly>
                            <small class="text-muted">Auto-generated unique identifier</small>
                            @error('car_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Objective <span class="text-danger">*</span></label>
                            <select name="objective_id" class="form-select @error('objective_id') is-invalid @enderror" required>
                                <option value="">Select Objective</option>
                                @foreach($objectives as $id => $description)
                                    <option value="{{ $id }}" {{ old('objective_id', $objective->id ?? '') == $id ? 'selected' : '' }}>
                                        {{ $description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('objective_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Issue Description <span class="text-danger">*</span></label>
                    <textarea name="issue_description" class="form-control @error('issue_description') is-invalid @enderror"
                              rows="3" placeholder="Describe the quality issue or non-conformance" required>{{ old('issue_description') }}</textarea>
                    @error('issue_description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Root Cause Analysis</label>
                    <textarea name="root_cause" class="form-control @error('root_cause') is-invalid @enderror"
                              rows="2" placeholder="Identify the root cause of the issue">{{ old('root_cause') }}</textarea>
                    @error('root_cause')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Action Plan <span class="text-danger">*</span></label>
                    <textarea name="action_plan" class="form-control @error('action_plan') is-invalid @enderror"
                              rows="3" placeholder="Describe the corrective actions to be taken" required>{{ old('action_plan') }}</textarea>
                    @error('action_plan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Action Owner <span class="text-danger">*</span></label>
                            <select name="action_owner_id" class="form-select @error('action_owner_id') is-invalid @enderror" required>
                                <option value="">Select Owner</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ old('action_owner_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('action_owner_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Deadline <span class="text-danger">*</span></label>
                            <input type="date" name="deadline"
                                   class="form-control @error('deadline') is-invalid @enderror"
                                   value="{{ old('deadline', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('deadline')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Opened Date <span class="text-danger">*</span></label>
                            <input type="date" name="opened_date"
                                   class="form-control @error('opened_date') is-invalid @enderror"
                                   value="{{ old('opened_date', date('Y-m-d')) }}" required>
                            @error('opened_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Status</label>
                            <input type="text" class="form-control" value="Open" disabled>
                            <small class="text-muted">New CARs start as Open</small>
                        </div>
                    </div>
                </div>

                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle"></i> CAR Process</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge bg-danger">1. Open</span> - Issue identified
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-warning">2. In Progress</span> - Action ongoing
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-success">3. Closed</span> - Issue resolved
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('quality.corrective-actions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create CAR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
