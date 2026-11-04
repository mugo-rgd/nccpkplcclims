@extends('layouts.app')

@section('title', 'Create Quality Objective')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create Quality Objective</h1>
            <p class="text-muted mb-0">Define a new quality objective for the laboratory</p>
        </div>
        <a href="{{ route('quality.objectives.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-plus-circle"></i> Objective Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.objectives.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Objective ID <span class="text-danger">*</span></label>
                            <input type="text" name="objective_id"
                                   class="form-control @error('objective_id') is-invalid @enderror"
                                   value="{{ old('objective_id') }}"
                                   placeholder="e.g., MCL-001" required>
                            <small class="text-muted">Unique identifier for this objective</small>
                            @error('objective_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Section <span class="text-danger">*</span></label>
                            <select name="section" class="form-select @error('section') is-invalid @enderror" required>
                                <option value="">Select Section</option>
                                @foreach($sections as $key => $value)
                                    <option value="{{ $key }}" {{ old('section') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('section')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3" placeholder="Describe the quality objective in detail" required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Unit of Measure <span class="text-danger">*</span></label>
                            <select name="unit_of_measure" class="form-select @error('unit_of_measure') is-invalid @enderror" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}" {{ old('unit_of_measure') == $unit ? 'selected' : '' }}>
                                        {{ $unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_of_measure')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Target Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="target_value"
                                   class="form-control @error('target_value') is-invalid @enderror"
                                   value="{{ old('target_value') }}" placeholder="0.00" required>
                            @error('target_value')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Target FYR <span class="text-danger">*</span></label>
                            <select name="target_fyr" class="form-select @error('target_fyr') is-invalid @enderror" required>
                                <option value="">Select FYR</option>
                                @foreach($fyrPeriods as $fyr)
                                    <option value="{{ $fyr->fyr }}"
                                        {{ old('target_fyr', $currentFyr->fyr ?? '') == $fyr->fyr ? 'selected' : '' }}
                                        {{ $fyr->is_closed ? 'disabled' : '' }}>
                                        {{ $fyr->display_name }}
                                        @if($fyr->is_active) (Current) @endif
                                        @if($fyr->is_closed) (Closed) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('target_fyr')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Owner <span class="text-danger">*</span></label>
                            <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror" required>
                                <option value="">Select Owner</option>
                                @foreach($owners as $id => $name)
                                    <option value="{{ $id }}" {{ old('owner_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('owner_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Baseline Value</label>
                            <input type="number" step="0.01" name="baseline_value"
                                   class="form-control @error('baseline_value') is-invalid @enderror"
                                   value="{{ old('baseline_value') }}" placeholder="0.00">
                            <small class="text-muted">Previous year's achievement or starting point</small>
                            @error('baseline_value')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">ISO 17025 Clause</label>
                            <select name="iso17025_clause" class="form-select @error('iso17025_clause') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach($isoClauses as $clause => $description)
                                    <option value="{{ $clause }}" {{ old('iso17025_clause') == $clause ? 'selected' : '' }}>
                                        {{ $clause }} - {{ $description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('iso17025_clause')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Baseline FYR</label>
                            <select name="baseline_fyr" class="form-select @error('baseline_fyr') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach($fyrPeriods as $fyr)
                                    <option value="{{ $fyr->fyr }}" {{ old('baseline_fyr') == $fyr->fyr ? 'selected' : '' }}>
                                        {{ $fyr->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('baseline_fyr')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Remarks</label>
                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                              rows="2" placeholder="Additional notes or comments">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('quality.objectives.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Objective
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
