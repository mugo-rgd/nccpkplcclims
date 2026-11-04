@extends('layouts.app')

@section('title', 'Quality Check - Calibration #' . $calibration->id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-clipboard-check text-primary me-2"></i>
                Quality Check
                <span class="badge bg-secondary">Calibration #{{ $calibration->id }}</span>
            </h1>
            <p class="text-muted mb-0">
                Review and verify calibration quality
            </p>
        </div>
        <div>
            <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Calibration Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Calibration Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Customer:</th>
                            <td>{{ optional($calibration->customer)->company_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Equipment:</th>
                            <td>{{ $calibration->equipment_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Serial Number:</th>
                            <td><code>{{ $calibration->serial_number ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>Technician:</th>
                            <td>{{ optional($calibration->performedBy)->name ?? 'Unassigned' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $calibration->getStatusColorAttribute() }}">
                                    {{ $calibration->getStatusLabelAttribute() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $calibration->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Test Results Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-flask me-1"></i>
                        Test Results Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4>{{ $testResults->count() }}</h4>
                            <small class="text-muted">Total Tests</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success">{{ $testResults->where('is_within_tolerance', true)->count() }}</h4>
                            <small class="text-muted">Passed</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger">{{ $testResults->where('is_within_tolerance', false)->count() }}</h4>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Average Uncertainty:</span>
                        <strong>
                            @php
                                $avgUncertainty = $testResults->where('uncertainty', '!=', null)->avg('uncertainty');
                            @endphp
                            {{ $avgUncertainty ? number_format($avgUncertainty, 6) . '%' : 'N/A' }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results Table -->
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list me-1"></i>
                Test Results
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Test Name</th>
                            <th>Applied Value</th>
                            <th>Error %</th>
                            <th>Uncertainty</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testResults as $result)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $result->test_name ?? $result->parameter }}</td>
                                <td>{{ $result->applied_value ?? 'N/A' }}</td>
                                <td>{{ $result->error_percentage !== null ? number_format($result->error_percentage, 4) . '%' : 'N/A' }}</td>
                                <td>{{ $result->uncertainty !== null ? number_format($result->uncertainty, 6) . '%' : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $result->is_within_tolerance ? 'success' : 'danger' }}">
                                        {{ $result->is_within_tolerance ? 'PASS' : 'FAIL' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quality Check Form -->
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-check-double me-1"></i>
                Quality Check Decision
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.quality.process-check', $calibration) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Decision <span class="text-danger">*</span></label>
                            <select name="quality_decision" class="form-select" required>
                                <option value="">Select Decision</option>
                                <option value="pass">✅ Pass - Approve Calibration</option>
                                <option value="fail">❌ Fail - Reject Calibration</option>
                                <option value="needs_review">🔄 Needs Review - Return for Rework</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Quality Checklist</label>
                            <div class="border rounded p-2">
                                <div class="form-check">
                                    <input type="checkbox" name="checklist_items[]" value="test_results_complete" class="form-check-input">
                                    <label class="form-check-label">Test results are complete</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="checklist_items[]" value="uncertainty_calculated" class="form-check-input">
                                    <label class="form-check-label">Uncertainty calculated correctly</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="checklist_items[]" value="traceability_verified" class="form-check-input">
                                    <label class="form-check-label">Traceability verified</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="checklist_items[]" value="equipment_calibrated" class="form-check-input">
                                    <label class="form-check-label">Equipment within calibration date</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Quality Notes</label>
                            <textarea name="quality_notes" class="form-control" rows="3"
                                      placeholder="Any comments or observations about the quality check..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Submit Quality Check
                    </button>
                    <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
