@extends('layouts.app')

@section('title', 'Edit Corrective Action')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Corrective Action</h1>
            <p class="text-muted mb-0">{{ $correctiveAction->car_id }}</p>
        </div>
        <a href="{{ route('quality.corrective-actions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-edit"></i> Edit CAR Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.corrective-actions.update', $correctiveAction->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">CAR ID</label>
                            <input type="text" class="form-control" value="{{ $correctiveAction->car_id }}" disabled>
                            <small class="text-muted">ID cannot be changed</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Objective <span class="text-danger">*</span></label>
                            <select name="objective_id" class="form-select @error('objective_id') is-invalid @enderror" required>
                                @foreach($objectives as $id => $description)
                                    <option value="{{ $id }}" {{ old('objective_id', $correctiveAction->objective_id) == $id ? 'selected' : '' }}>
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
                              rows="3" required>{{ old('issue_description', $correctiveAction->issue_description) }}</textarea>
                    @error('issue_description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Root Cause Analysis</label>
                    <textarea name="root_cause" class="form-control @error('root_cause') is-invalid @enderror"
                              rows="2">{{ old('root_cause', $correctiveAction->root_cause) }}</textarea>
                    @error('root_cause')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Action Plan <span class="text-danger">*</span></label>
                    <textarea name="action_plan" class="form-control @error('action_plan') is-invalid @enderror"
                              rows="3" required>{{ old('action_plan', $correctiveAction->action_plan) }}</textarea>
                    @error('action_plan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Action Owner <span class="text-danger">*</span></label>
                            <select name="action_owner_id" class="form-select @error('action_owner_id') is-invalid @enderror" required>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ old('action_owner_id', $correctiveAction->action_owner_id) == $id ? 'selected' : '' }}>
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
                                   value="{{ old('deadline', $correctiveAction->deadline->format('Y-m-d')) }}" required>
                            @error('deadline')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Opened Date</label>
                            <input type="date" class="form-control"
                                   value="{{ $correctiveAction->opened_date->format('Y-m-d') }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $correctiveAction->status) == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Verification Notes</label>
                    <textarea name="verification_notes" class="form-control @error('verification_notes') is-invalid @enderror"
                              rows="2">{{ old('verification_notes', $correctiveAction->verification_notes) }}</textarea>
                    @error('verification_notes')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                @if($correctiveAction->closed_date)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    This CAR was closed on {{ $correctiveAction->closed_date->format('Y-m-d H:i:s') }}
                </div>
                @endif

                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('quality.corrective-actions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update CAR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
