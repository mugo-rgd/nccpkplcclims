@extends('layouts.app')

@section('title', 'Edit Quality Objective')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Quality Objective</h1>
            <p class="text-muted mb-0">Update objective: <strong>{{ $objective->objective_id }}</strong></p>
        </div>
        <a href="{{ route('quality.objectives.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-edit"></i> Edit Objective Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.objectives.update', $objective->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Objective ID</label>
                            <input type="text" class="form-control" value="{{ $objective->objective_id }}" disabled>
                            <small class="text-muted">ID cannot be changed</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Section <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="{{ $objective->section }}" disabled>
                            <small class="text-muted">Section cannot be changed</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3" required>{{ old('description', $objective->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Unit of Measure <span class="text-danger">*</span></label>
                            <select name="unit_of_measure" class="form-select @error('unit_of_measure') is-invalid @enderror" required>
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}" {{ old('unit_of_measure', $objective->unit_of_measure) == $unit ? 'selected' : '' }}>
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
                                   value="{{ old('target_value', $objective->target_value) }}" required>
                            @error('target_value')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Target FYR <span class="text-danger">*</span></label>
                            <select name="target_fyr" class="form-select @error('target_fyr') is-invalid @enderror" required>
                                @foreach($fyrPeriods as $fyr)
                                    <option value="{{ $fyr->fyr }}"
                                        {{ old('target_fyr', $objective->target_fyr) == $fyr->fyr ? 'selected' : '' }}>
                                        {{ $fyr->display_name }}
                                        @if($fyr->is_active) (Current) @endif
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
                                @foreach($owners as $id => $name)
                                    <option value="{{ $id }}" {{ old('owner_id', $objective->owner_id) == $id ? 'selected' : '' }}>
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
                                   value="{{ old('baseline_value', $objective->baseline_value) }}" placeholder="0.00">
                            @error('baseline_value')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $objective->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $objective->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">ISO 17025 Clause</label>
                            <input type="text" name="iso17025_clause"
                                   class="form-control @error('iso17025_clause') is-invalid @enderror"
                                   value="{{ old('iso17025_clause', $objective->iso17025_clause) }}"
                                   placeholder="e.g., 7.7">
                            @error('iso17025_clause')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Remarks</label>
                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                              rows="2">{{ old('remarks', $objective->remarks) }}</textarea>
                    @error('remarks')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Historical Targets -->
                @if($objective->historical_targets && count($objective->historical_targets) > 1)
                <div class="card bg-light mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-history"></i> Historical Targets</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>FYR</th>
                                        <th>Target</th>
                                        <th>Achievement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($objective->target_trend as $trend)
                                    <tr>
                                        <td>{{ $trend['fyr'] }}</td>
                                        <td>{{ $trend['target'] }}</td>
                                        <td>
                                            @if($trend['achievement'])
                                                {{ $trend['achievement'] }}
                                                ({{ round(($trend['achievement'] / $trend['target']) * 100, 2) }}%)
                                            @else
                                                <span class="text-muted">Not tracked</span>
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

                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('quality.objectives.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Objective
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
