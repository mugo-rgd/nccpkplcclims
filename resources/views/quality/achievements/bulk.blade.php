@extends('layouts.app')

@section('title', 'Bulk Achievement Entry')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Bulk Achievement Entry</h1>
            <p class="text-muted mb-0">Enter achievements for multiple objectives at once</p>
        </div>
        <div>
            <a href="{{ route('quality.achievements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-upload"></i> Bulk Entry Form
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quality.achievements.bulk') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">FYR Period <span class="text-danger">*</span></label>
                            <select name="fyr_period_id" class="form-select" required>
                                @foreach($fyrPeriods as $fyr)
                                    <option value="{{ $fyr->id }}"
                                        {{ $currentFyr && $currentFyr->id == $fyr->id ? 'selected' : '' }}>
                                        {{ $fyr->display_name }}
                                        @if($fyr->is_active) (Current) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Quarter <span class="text-danger">*</span></label>
                            <select name="quarter" class="form-select" required>
                                @foreach($quarters as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Quarter Label <span class="text-danger">*</span></label>
                            <input type="text" name="quarter_label" class="form-control"
                                   value="{{ date('M Y') }}" required>
                            <small class="text-muted">e.g., Sept 2025</small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Enter achievements for each objective. Leave fields empty to skip.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="bulkTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%">Objective ID</th>
                                <th style="width: 35%">Description</th>
                                <th style="width: 15%">Section</th>
                                <th style="width: 20%">Achievement <span class="text-danger">*</span></th>
                                <th style="width: 15%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($objectives as $section => $items)
                                <tr class="table-secondary">
                                    <td colspan="5">
                                        <strong><i class="fas fa-folder"></i> {{ $section }}</strong>
                                    </td>
                                </tr>
                                @foreach($items as $index => $objective)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $objective->objective_id }}</span>
                                        <input type="hidden" name="data[{{ $loop->parent->index }}][objective_id]"
                                               value="{{ $objective->id }}">
                                    </td>
                                    <td>
                                        {{ Str::limit($objective->description, 60) }}
                                        <br>
                                        <small class="text-muted">
                                            Target: {{ $objective->target_value }} {{ $objective->unit_of_measure }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $objective->section }}</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01"
                                               name="data[{{ $loop->parent->index }}][actual_achievement]"
                                               class="form-control achievement-input"
                                               placeholder="Enter value"
                                               data-target="{{ $objective->target_value }}"
                                               data-unit="{{ $objective->unit_of_measure }}">
                                        <small class="text-muted unit-display">{{ $objective->unit_of_measure }}</small>
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="data[{{ $loop->parent->index }}][remarks]"
                                               class="form-control"
                                               placeholder="Optional remarks">
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Summary</h6>
                                <div id="bulkSummary">
                                    <p>Total objectives: <strong>{{ $objectives->sum(function($items) { return $items->count(); }) }}</strong></p>
                                    <p>Entered: <strong id="enteredCount">0</strong></p>
                                    <p>Remaining: <strong id="remainingCount">0</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-end d-flex align-items-center justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="clearAll()">
                            <i class="fas fa-eraser"></i> Clear All
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-upload"></i> Save All Achievements
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
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.achievement-input');
        const enteredCount = document.getElementById('enteredCount');
        const remainingCount = document.getElementById('remainingCount');
        const totalInputs = inputs.length;

        function updateSummary() {
            let entered = 0;
            inputs.forEach(input => {
                if (input.value.trim() !== '') {
                    entered++;
                }
            });
            enteredCount.textContent = entered;
            remainingCount.textContent = totalInputs - entered;
        }

        inputs.forEach(input => {
            input.addEventListener('input', updateSummary);
            input.addEventListener('change', function() {
                // Validate against target
                const target = parseFloat(this.dataset.target);
                const value = parseFloat(this.value);
                if (value && target) {
                    const unit = this.dataset.unit;
                    // Just show a visual indicator
                    if (value > target * 1.2) {
                        this.style.borderColor = 'orange';
                        this.title = 'Value is significantly above target';
                    } else if (value < target * 0.8) {
                        this.style.borderColor = 'red';
                        this.title = 'Value is significantly below target';
                    } else {
                        this.style.borderColor = 'green';
                        this.title = 'Value is within expected range';
                    }
                }
            });
        });

        updateSummary();
    });

    function clearAll() {
        if (confirm('Clear all entered values?')) {
            document.querySelectorAll('.achievement-input').forEach(input => {
                input.value = '';
                input.style.borderColor = '';
            });
            document.getElementById('enteredCount').textContent = '0';
            document.getElementById('remainingCount').textContent =
                document.querySelectorAll('.achievement-input').length;
        }
    }
</script>
@endpush
