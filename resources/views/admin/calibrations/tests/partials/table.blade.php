@php
    $resultsCollection = collect($testResults ?? []);
    $showAllActions = $showAllActions ?? true;
@endphp

<div class="card border-0 shadow-sm test-results-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <i class="fas fa-list text-primary me-1"></i>
            <strong>{{ $tableTitle ?? 'Test Results' }}</strong>
        </div>

        <span class="badge bg-secondary">{{ $resultsCollection->count() }}</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 55px;">#</th>
                        <th>Test Information</th>
                        <th>Test Point / Range</th>
                        <th>Error / Deviation</th>
                        <th>Uncertainty</th>
                        <th>Result</th>
                        <th class="text-end" style="min-width: 135px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($resultsCollection as $result)
                        @php
                            $displayError = $result->error_value ?? $result->error_percentage;
                            $storedResult = strtoupper((string) ($result->result ?? ''));

                            if ($storedResult === '') {
                                $storedResult = $result->is_within_tolerance === true
                                    ? 'PASS'
                                    : ($result->is_within_tolerance === false ? 'FAIL' : '');
                            }

                            $hasPassed = $storedResult === 'PASS';
                            $resultColor = $storedResult === 'PASS'
                                ? 'success'
                                : ($storedResult === 'FAIL' ? 'danger' : 'secondary');

                            $equipmentTypeName = $result->equipment_type
                                ?? $calibration->equipmentType?->name
                                ?? $calibration->equipment_type
                                ?? 'N/A';

                            $workflowType = $result->workflow_type
                                ?? $result->test_type
                                ?? 'N/A';

                            $testPoint = $result->applied_value
                                ?? $result->measurement_range
                                ?? $result->load_type
                                ?? null;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong class="d-block">
                                    {{ $result->test_name ?? $result->parameter ?? 'Unnamed Test' }}
                                </strong>

                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $workflowType)) }}
                                    </span>

                                    <span class="badge bg-light text-dark border">
                                        {{ $equipmentTypeName }}
                                    </span>

                                    @if($result->test_category)
                                        <span class="badge bg-primary-subtle text-primary border">
                                            {{ ucfirst(str_replace('_', ' ', $result->test_category)) }}
                                        </span>
                                    @endif

                                    @if($result->power_factor)
                                        <span class="badge bg-info text-dark">{{ $result->power_factor }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($testPoint)
                                    <strong>{{ $testPoint }}</strong>
                                @elseif($result->nominal_value !== null)
                                    {{ $result->nominal_value }} {{ $result->unit ?? '' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif

                                @if($result->voltage || $result->frequency)
                                    <small class="d-block text-muted mt-1">
                                        {{ $result->voltage ?? '' }}
                                        {{ $result->voltage && $result->frequency ? ' · ' : '' }}
                                        {{ $result->frequency ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if($displayError !== null)
                                    <span class="fw-bold text-{{ $storedResult ? ($hasPassed ? 'success' : 'danger') : 'dark' }}">
                                        {{ number_format((float) $displayError, 6) }}
                                        {{ in_array($workflowType, ['insulation', 'resistance'], true) ? '' : '%' }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td>
                                @if($result->uncertainty !== null)
                                    {{ number_format((float) $result->uncertainty, 6) }}%
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-{{ $resultColor }}">
                                    @if($storedResult === 'PASS')
                                        <i class="fas fa-check me-1"></i>
                                    @elseif($storedResult === 'FAIL')
                                        <i class="fas fa-times me-1"></i>
                                    @endif
                                    {{ $storedResult ?: 'Pending' }}
                                </span>

                                @if($result->tolerance !== null)
                                    <small class="d-block text-muted mt-1">
                                        Tolerance: ±{{ number_format((float) $result->tolerance, 4) }}%
                                    </small>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewTestResultModal{{ $result->id }}"
                                        title="View details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @can('edit_test_results')
                                        <a
                                            href="{{ route('calibrations.tests.edit', [$calibration, $result]) }}"
                                            class="btn btn-sm btn-warning"
                                            title="Edit test result"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('delete_test_results')
                                        @if($showAllActions)
                                            <form
                                                action="{{ route('calibrations.tests.destroy', [$calibration, $result]) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Delete this test result permanently?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete test result"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-flask fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No test results recorded</h5>
                                <p class="text-muted mb-0">
                                    Test results will appear here after the assigned technician records them.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
