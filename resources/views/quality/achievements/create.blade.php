@extends('layouts.app')

@section('title', 'Record Achievement')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Record Achievement</h1>
            <p class="text-muted mb-0">Record performance against a quality objective</p>
        </div>
        <a href="{{ route('quality.achievements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-plus-circle"></i> Achievement Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.achievements.store') }}" method="POST">
                @csrf

                <div class="row">
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
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">FYR Period <span class="text-danger">*</span></label>
                            <select name="fyr_period_id" class="form-select @error('fyr_period_id') is-invalid @enderror" required>
                                <option value="">Select FYR</option>
                                @foreach($fyrPeriods as $fyr)
                                    <option value="{{ $fyr->id }}"
                                        {{ old('fyr_period_id', $currentFyr->id ?? '') == $fyr->id ? 'selected' : '' }}
                                        {{ $fyr->is_closed ? 'disabled' : '' }}>
                                        {{ $fyr->display_name }}
                                        @if($fyr->is_active) (Current) @endif
                                        @if($fyr->is_closed) (Closed) @endif
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
                                <option value="">Select Quarter</option>
                                @foreach($quarters as $key => $label)
                                    <option value="{{ $key }}" {{ old('quarter') == $key ? 'selected' : '' }}>
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
                                   value="{{ old('quarter_label') }}"
                                   placeholder="e.g., Sept 2025" required>
                            <small class="text-muted">Display name for the quarter</small>
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
                                   value="{{ old('data_collection_date', date('Y-m-d')) }}" required>
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
                                   value="{{ old('actual_achievement') }}"
                                   placeholder="Enter the actual value achieved" required>
                            <small class="text-muted">The actual value achieved for this objective</small>
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
                                   value="{{ old('evidence_url') }}"
                                   placeholder="https://example.com/evidence.pdf">
                            <small class="text-muted">Link to supporting evidence or documentation</small>
                            @error('evidence_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-bold">Remarks</label>
                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                              rows="2" placeholder="Additional notes about this achievement">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Target Info -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Target:</strong>
                                <span id="targetDisplay">Select an objective to see target</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Unit:</strong>
                                <span id="unitDisplay">Select an objective to see unit</span>
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
                            <i class="fas fa-save"></i> Save Achievement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-populate target and unit when objective is selected
    document.addEventListener('DOMContentLoaded', function() {
        const objectiveSelect = document.querySelector('select[name="objective_id"]');
        const targetDisplay = document.getElementById('targetDisplay');
        const unitDisplay = document.getElementById('unitDisplay');

        // Store objective data from PHP
        const objectives = @json($objectives->map(function($desc, $id) {
            $obj = \App\Models\QualityObjective::find($id);
            return $obj ? ['target' => $obj->target_value, 'unit' => $obj->unit_of_measure] : null;
        })->filter()->toArray());

        objectiveSelect.addEventListener('change', function() {
            const id = this.value;
            if (id && objectives[id]) {
                targetDisplay.textContent = objectives[id].target;
                unitDisplay.textContent = objectives[id].unit;
            } else {
                targetDisplay.textContent = 'Select an objective to see target';
                unitDisplay.textContent = 'Select an objective to see unit';
            }
        });
    });
</script>
@endpush
