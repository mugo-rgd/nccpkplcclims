@php
    $displayError = $result->error_value ?? $result->error_percentage;
    $storedResult = strtoupper((string) ($result->result ?? ''));

    if ($storedResult === '') {
        $storedResult = $result->is_within_tolerance === true
            ? 'PASS'
            : ($result->is_within_tolerance === false ? 'FAIL' : '');
    }

    $trialData = $result->trial_data ?? [];

    if (is_string($trialData)) {
        $trialData = json_decode($trialData, true) ?: [];
    }

    $trialData = is_array($trialData) ? $trialData : [];

    $workflowType = $result->workflow_type ?? $result->test_type ?? null;
    $equipmentTypeName = $result->equipment_type
        ?? $calibration->equipmentType?->name
        ?? $calibration->equipment_type
        ?? 'N/A';

    $dynamicMeasurements = collect([
        'Rated Primary Current (A)' => $result->rated_primary_current ?? $result->ct_rated_primary,
        'Rated Secondary Current (A)' => $result->rated_secondary_current ?? $result->ct_rated_secondary,
        'Applied Primary Current (A)' => $result->applied_primary_current ?? $result->ct_applied_primary,
        'Measured Primary Current (A)' => $result->measured_primary_current,
        'Measured Secondary Current (A)' => $result->measured_secondary_current ?? $result->ct_measured_secondary,
        'Phase Displacement (min)' => $result->phase_displacement,
        'Rated Primary Voltage (V)' => $result->vt_rated_primary,
        'Rated Secondary Voltage (V)' => $result->vt_rated_secondary,
        'Applied Primary Voltage (V)' => $result->vt_applied_primary,
        'Measured Secondary Voltage (V)' => $result->vt_measured_secondary,
        'Reference Value' => $result->reference_value,
        'Indicated Value' => $result->indicated_value,
        'Measurement Range' => $result->measurement_range,
        'Instrument Resolution' => $result->instrument_resolution,
        'Reference Resistance (Ω)' => $result->reference_resistance,
        'Measured Resistance (Ω)' => $result->measured_resistance,
        'Insulation Test Voltage (V DC)' => $result->insulation_test_voltage,
        'Insulation Resistance (MΩ)' => $result->insulation_measured_resistance,
        'Minimum Insulation Resistance (MΩ)' => $result->insulation_minimum,
    ])->filter(fn ($value) => $value !== null && $value !== '');
@endphp

<div
    class="modal fade"
    id="viewTestResultModal{{ $result->id }}"
    tabindex="-1"
    aria-labelledby="viewTestResultModalLabel{{ $result->id }}"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="viewTestResultModalLabel{{ $result->id }}">
                        <i class="fas fa-flask me-1"></i>
                        Test Result Details
                    </h5>
                    <small>{{ $result->test_name ?? 'Laboratory Test' }}</small>
                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">Test Identification</h6>

                                <dl class="row mb-0 result-detail-list">
                                    <dt class="col-sm-5">Equipment Type</dt>
                                    <dd class="col-sm-7">{{ $equipmentTypeName }}</dd>

                                    <dt class="col-sm-5">Test Category</dt>
                                    <dd class="col-sm-7">
                                        {{ $result->test_category
                                            ? ucfirst(str_replace('_', ' ', $result->test_category))
                                            : 'N/A' }}
                                    </dd>

                                    <dt class="col-sm-5">Standard Test</dt>
                                    <dd class="col-sm-7">{{ $result->standard_test_code ?? 'N/A' }}</dd>

                                    <dt class="col-sm-5">Workflow</dt>
                                    <dd class="col-sm-7">
                                        {{ $workflowType
                                            ? ucfirst(str_replace('_', ' ', $workflowType))
                                            : 'N/A' }}
                                    </dd>

                                    <dt class="col-sm-5">Applied Value / Range</dt>
                                    <dd class="col-sm-7">{{ $result->applied_value ?? $result->measurement_range ?? 'N/A' }}</dd>

                                    <dt class="col-sm-5">Power Factor</dt>
                                    <dd class="col-sm-7">{{ $result->power_factor ?? 'N/A' }}</dd>

                                    <dt class="col-sm-5">Voltage / Frequency</dt>
                                    <dd class="col-sm-7">
                                        {{ $result->voltage ?? 'N/A' }}
                                        {{ $result->frequency ? ' · ' . $result->frequency : '' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-success mb-3">Calculated Result</h6>

                                <dl class="row mb-0 result-detail-list">
                                    <dt class="col-sm-5">Error / Deviation</dt>
                                    <dd class="col-sm-7">
                                        {{ $displayError !== null
                                            ? number_format((float) $displayError, 6)
                                            : 'N/A' }}
                                    </dd>

                                    <dt class="col-sm-5">Standard Deviation</dt>
                                    <dd class="col-sm-7">
                                        {{ $result->standard_deviation !== null
                                            ? number_format((float) $result->standard_deviation, 6)
                                            : 'N/A' }}
                                    </dd>

                                    <dt class="col-sm-5">Uncertainty</dt>
                                    <dd class="col-sm-7">
                                        {{ $result->uncertainty !== null
                                            ? number_format((float) $result->uncertainty, 6) . '%'
                                            : 'N/A' }}
                                    </dd>

                                    <dt class="col-sm-5">Tolerance</dt>
                                    <dd class="col-sm-7">
                                        {{ $result->tolerance !== null
                                            ? '±' . number_format((float) $result->tolerance, 4) . '%'
                                            : 'N/A' }}
                                    </dd>

                                    <dt class="col-sm-5">Decision</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge bg-{{ $storedResult === 'PASS' ? 'success' : ($storedResult === 'FAIL' ? 'danger' : 'secondary') }}">
                                            {{ $storedResult ?: 'Pending' }}
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    @if($dynamicMeasurements->isNotEmpty())
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <strong>Equipment-Specific Measurements</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @foreach($dynamicMeasurements as $label => $value)
                                            <div class="col-sm-6 col-lg-4">
                                                <small class="text-muted d-block">{{ $label }}</small>
                                                <strong>{{ $value }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(count($trialData) > 0)
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <strong>Trial Readings</strong>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($trialData as $index => $trial)
                                            <span class="badge bg-secondary p-2">
                                                T{{ $index + 1 }}: {{ $trial }}
                                            </span>
                                        @endforeach
                                    </div>

                                    <small class="text-muted d-block mt-2">
                                        Average: {{ number_format((float) collect($trialData)->average(), 6) }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($result->start_reading !== null || $result->stop_reading !== null)
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <strong>Register Test Details</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-sm-6 col-lg-3">
                                            <small class="text-muted d-block">Start Reading</small>
                                            <strong>{{ $result->start_reading ?? 'N/A' }} kWh</strong>
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <small class="text-muted d-block">Stop Reading</small>
                                            <strong>{{ $result->stop_reading ?? 'N/A' }} kWh</strong>
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <small class="text-muted d-block">Energy Difference</small>
                                            <strong>{{ $result->energy_difference ?? 'N/A' }} kWh</strong>
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <small class="text-muted d-block">Dial Error</small>
                                            <strong>{{ $result->dial_error ?? 'N/A' }}%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($result->notes)
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0">
                                <strong>Technician Notes</strong>
                                <div class="mt-1">{{ $result->notes }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <small class="text-muted me-auto">
                    Created {{ optional($result->created_at)->format('Y-m-d H:i') ?? 'N/A' }}
                    @if($result->updated_at && $result->updated_at->ne($result->created_at))
                        · Updated {{ $result->updated_at->format('Y-m-d H:i') }}
                    @endif
                </small>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>

                @can('edit_test_results')
                    <a
                        href="{{ route('calibrations.tests.edit', [$calibration, $result]) }}"
                        class="btn btn-warning"
                    >
                        <i class="fas fa-edit me-1"></i>
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>
