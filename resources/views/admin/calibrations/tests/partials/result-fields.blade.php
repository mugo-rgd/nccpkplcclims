{{--
    Shared test-result fields partial.

    Expected variable:
    - $result : Test result model

    Optional variable:
    - $compact : bool, defaults to false
--}}

@php
    $compact = $compact ?? false;

    $workflowType =
        $result->workflow_type
        ?? $result->workflow
        ?? null;

    $equipmentType =
        $result->equipment_type
        ?? $result->equipment_type_name
        ?? null;

    $testCategory =
        $result->test_category
        ?? null;

    $standardTestCode =
        $result->standard_test_code
        ?? null;

    $displayError =
        $result->error_value
        ?? $result->error_percentage
        ?? null;

    $displayResult = strtoupper(
        (string) (
            $result->result
            ?? (
                $result->is_within_tolerance === true
                    ? 'PASS'
                    : (
                        $result->is_within_tolerance === false
                            ? 'FAIL'
                            : ''
                    )
            )
        )
    );

    $hasPassed = $displayResult === 'PASS';
    $hasFailed = $displayResult === 'FAIL';

    $trialData = $result->trial_data ?? [];

    if (is_string($trialData)) {
        $trialData = json_decode($trialData, true) ?: [];
    }

    $trialData = is_array($trialData)
        ? array_values(
            array_filter(
                $trialData,
                fn ($value) => $value !== null && $value !== ''
            )
        )
        : [];

    $trialAverage = count($trialData)
        ? collect($trialData)->map(fn ($value) => (float) $value)->average()
        : null;

    $toleranceValue =
        $result->tolerance
        ?? null;

    $toleranceMin =
        $result->tolerance_min
        ?? null;

    $toleranceMax =
        $result->tolerance_max
        ?? null;

    $fieldRows = collect([
        [
            'label' => 'Equipment Type',
            'value' => $equipmentType
                ? ucfirst(str_replace('_', ' ', $equipmentType))
                : null,
        ],
        [
            'label' => 'Test Category',
            'value' => $testCategory
                ? ucfirst(str_replace('_', ' ', $testCategory))
                : null,
        ],
        [
            'label' => 'Standard Test',
            'value' => $standardTestCode
                ? strtoupper(str_replace('_', ' ', $standardTestCode))
                : null,
        ],
        [
            'label' => 'Workflow',
            'value' => $workflowType
                ? ucfirst(str_replace('_', ' ', $workflowType))
                : null,
        ],
        [
            'label' => 'Applied Value / Range',
            'value' => $result->applied_value ?? null,
        ],
        [
            'label' => 'Power Factor',
            'value' => $result->power_factor ?? null,
        ],
        [
            'label' => 'Load Type',
            'value' => $result->load_type
                ? ucfirst(str_replace('_', ' ', $result->load_type))
                : null,
        ],
        [
            'label' => 'Voltage',
            'value' => $result->voltage ?? null,
        ],
        [
            'label' => 'Frequency',
            'value' => $result->frequency ?? null,
        ],
        [
            'label' => 'Nominal Value',
            'value' => $result->nominal_value !== null
                ? trim(
                    (string) $result->nominal_value
                    . ' '
                    . ($result->unit ?? '')
                )
                : null,
        ],
        [
            'label' => 'Measured Value',
            'value' => $result->measured_value !== null
                ? trim(
                    (string) $result->measured_value
                    . ' '
                    . ($result->unit ?? '')
                )
                : null,
        ],
        [
            'label' => 'Error',
            'value' => $displayError !== null
                ? number_format((float) $displayError, 6) . '%'
                : null,
        ],
        [
            'label' => 'Uncertainty',
            'value' => $result->uncertainty !== null
                ? number_format((float) $result->uncertainty, 6) . '%'
                : null,
        ],
        [
            'label' => 'Standard Deviation',
            'value' => $result->standard_deviation !== null
                ? number_format((float) $result->standard_deviation, 6)
                : null,
        ],
        [
            'label' => 'Tolerance',
            'value' => $toleranceValue !== null
                ? '±' . number_format((float) $toleranceValue, 4) . '%'
                : (
                    $toleranceMin !== null || $toleranceMax !== null
                        ? trim(
                            ($toleranceMin ?? 'N/A')
                            . ' to '
                            . ($toleranceMax ?? 'N/A')
                            . ' '
                            . ($result->unit ?? '')
                        )
                        : null
                ),
        ],
    ])->filter(fn ($field) => $field['value'] !== null && $field['value'] !== '');
@endphp

@if(!$compact)
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h6 class="mb-1">
                        {{ $result->test_name
                            ?? $result->parameter
                            ?? 'Test Result' }}
                    </h6>

                    @if($result->test_type)
                        <span class="badge bg-secondary">
                            {{ ucfirst(str_replace('_', ' ', $result->test_type)) }}
                        </span>
                    @endif
                </div>

                @if($displayResult)
                    <span
                        class="badge bg-{{ $hasPassed ? 'success' : ($hasFailed ? 'danger' : 'secondary') }} fs-6"
                    >
                        <i class="fas {{ $hasPassed ? 'fa-circle-check' : ($hasFailed ? 'fa-circle-xmark' : 'fa-circle-question') }} me-1"></i>
                        {{ $displayResult }}
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif

@if($fieldRows->count())
    <div class="row g-3">
        @foreach($fieldRows as $field)
            <div class="{{ $compact ? 'col-md-6' : 'col-md-6 col-lg-4' }}">
                <div class="result-field-item">
                    <small class="text-muted d-block">
                        {{ $field['label'] }}
                    </small>

                    <strong>
                        {{ $field['value'] }}
                    </strong>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Energy meter register details --}}
@if(
    $result->start_reading !== null
    || $result->stop_reading !== null
    || $result->energy_difference !== null
    || $result->dial_error !== null
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-calculator me-1"></i>
            Register Test Details
        </h6>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="result-field-item">
                    <small class="text-muted d-block">
                        Start Reading
                    </small>
                    <strong>
                        {{ $result->start_reading !== null
                            ? number_format((float) $result->start_reading, 4) . ' kWh'
                            : 'N/A' }}
                    </strong>
                </div>
            </div>

            <div class="col-md-3">
                <div class="result-field-item">
                    <small class="text-muted d-block">
                        Stop Reading
                    </small>
                    <strong>
                        {{ $result->stop_reading !== null
                            ? number_format((float) $result->stop_reading, 4) . ' kWh'
                            : 'N/A' }}
                    </strong>
                </div>
            </div>

            <div class="col-md-3">
                <div class="result-field-item">
                    <small class="text-muted d-block">
                        Energy Difference
                    </small>
                    <strong>
                        {{ $result->energy_difference !== null
                            ? number_format((float) $result->energy_difference, 4) . ' kWh'
                            : 'N/A' }}
                    </strong>
                </div>
            </div>

            <div class="col-md-3">
                <div class="result-field-item">
                    <small class="text-muted d-block">
                        Dial Error
                    </small>
                    <strong>
                        {{ $result->dial_error !== null
                            ? number_format((float) $result->dial_error, 6) . '%'
                            : 'N/A' }}
                    </strong>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Current transformer fields --}}
@if(
    str_contains((string) $workflowType, 'ct_')
    || $equipmentType === 'current_transformer'
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-arrows-left-right me-1"></i>
            Current Transformer Measurements
        </h6>

        <div class="row g-3">
            @foreach([
                'rated_primary_current' => ['Rated Primary Current', 'A'],
                'rated_secondary_current' => ['Rated Secondary Current', 'A'],
                'applied_current_percent' => ['Applied Current', '%'],
                'measured_primary_current' => ['Measured Primary Current', 'A'],
                'measured_secondary_current' => ['Measured Secondary Current', 'A'],
                'ct_ratio_rated_primary' => ['Rated Primary Current', 'A'],
                'ct_ratio_rated_secondary' => ['Rated Secondary Current', 'A'],
                'ct_ratio_applied_primary' => ['Applied Primary Current', 'A'],
                'ct_ratio_measured_secondary' => ['Measured Secondary Current', 'A'],
                'phase_displacement' => ['Phase Displacement', 'min'],
                'rated_burden' => ['Rated Burden', 'VA'],
                'measured_burden' => ['Measured Burden', 'VA'],
                'burden_ratio_error' => ['Burden Ratio Error', '%'],
                'burden_phase_displacement' => ['Burden Phase Displacement', 'min'],
                'excitation_voltage' => ['Applied Voltage', 'V'],
                'excitation_current' => ['Excitation Current', 'mA'],
                'knee_point_voltage' => ['Knee-Point Voltage', 'V'],
                'excitation_temperature' => ['Temperature', '°C'],
                'polarity_result' => ['Polarity', ''],
            ] as $attribute => [$label, $unit])
                @php
                    $value = data_get($result, $attribute);
                @endphp

                @if($value !== null && $value !== '')
                    <div class="col-md-6 col-lg-4">
                        <div class="result-field-item">
                            <small class="text-muted d-block">
                                {{ $label }}
                            </small>
                            <strong>
                                {{ is_numeric($value)
                                    ? number_format((float) $value, 6)
                                    : ucfirst((string) $value) }}
                                {{ $unit }}
                            </strong>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Voltage transformer fields --}}
@if(
    str_contains((string) $workflowType, 'vt_')
    || $equipmentType === 'voltage_transformer'
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-bolt me-1"></i>
            Voltage Transformer Measurements
        </h6>

        <div class="row g-3">
            @foreach([
                'vt_rated_primary_voltage' => ['Rated Primary Voltage', 'V'],
                'vt_rated_secondary_voltage' => ['Rated Secondary Voltage', 'V'],
                'vt_applied_primary_voltage' => ['Applied Primary Voltage', 'V'],
                'vt_measured_secondary_voltage' => ['Measured Secondary Voltage', 'V'],
                'vt_rated_primary' => ['Rated Primary Voltage', 'V'],
                'vt_rated_secondary' => ['Rated Secondary Voltage', 'V'],
                'vt_applied_primary' => ['Applied Primary Voltage', 'V'],
                'vt_measured_secondary' => ['Measured Secondary Voltage', 'V'],
                'vt_burden' => ['Burden', 'VA'],
                'vt_phase_displacement' => ['Phase Displacement', 'min'],
            ] as $attribute => [$label, $unit])
                @php
                    $value = data_get($result, $attribute);
                @endphp

                @if($value !== null && $value !== '')
                    <div class="col-md-6 col-lg-4">
                        <div class="result-field-item">
                            <small class="text-muted d-block">
                                {{ $label }}
                            </small>
                            <strong>
                                {{ is_numeric($value)
                                    ? number_format((float) $value, 6)
                                    : $value }}
                                {{ $unit }}
                            </strong>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Generic reference-comparison fields --}}
@if(
    $workflowType === 'comparison'
    || $result->reference_value !== null
    || $result->indicated_value !== null
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-scale-balanced me-1"></i>
            Reference Comparison
        </h6>

        <div class="row g-3">
            @foreach([
                'reference_value' => ['Reference Value', ''],
                'indicated_value' => ['Indicated Value', ''],
                'measurement_range' => ['Range / Unit', ''],
                'instrument_resolution' => ['Resolution', ''],
            ] as $attribute => [$label, $unit])
                @php
                    $value = data_get($result, $attribute);
                @endphp

                @if($value !== null && $value !== '')
                    <div class="col-md-6 col-lg-3">
                        <div class="result-field-item">
                            <small class="text-muted d-block">
                                {{ $label }}
                            </small>
                            <strong>
                                {{ is_numeric($value)
                                    ? number_format((float) $value, 6)
                                    : $value }}
                                {{ $unit }}
                            </strong>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Resistance fields --}}
@if(
    $workflowType === 'resistance'
    || $result->reference_resistance !== null
    || $result->measured_resistance !== null
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-wave-square me-1"></i>
            Resistance Measurements
        </h6>

        <div class="row g-3">
            @foreach([
                'reference_resistance' => ['Reference Resistance', 'Ω'],
                'measured_resistance' => ['Measured Resistance', 'Ω'],
                'resistance_temperature' => ['Test Temperature', '°C'],
            ] as $attribute => [$label, $unit])
                @php
                    $value = data_get($result, $attribute);
                @endphp

                @if($value !== null && $value !== '')
                    <div class="col-md-4">
                        <div class="result-field-item">
                            <small class="text-muted d-block">
                                {{ $label }}
                            </small>
                            <strong>
                                {{ number_format((float) $value, 6) }}
                                {{ $unit }}
                            </strong>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Insulation fields --}}
@if(
    $workflowType === 'insulation'
    || $result->insulation_test_voltage !== null
    || $result->insulation_measured_resistance !== null
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-shield-halved me-1"></i>
            Insulation Measurements
        </h6>

        <div class="row g-3">
            @foreach([
                'insulation_test_voltage' => ['Test Voltage', 'V DC'],
                'insulation_measured_resistance' => ['Measured Resistance', 'MΩ'],
                'insulation_minimum' => ['Minimum Acceptable', 'MΩ'],
            ] as $attribute => [$label, $unit])
                @php
                    $value = data_get($result, $attribute);
                @endphp

                @if($value !== null && $value !== '')
                    <div class="col-md-4">
                        <div class="result-field-item">
                            <small class="text-muted d-block">
                                {{ $label }}
                            </small>
                            <strong>
                                {{ number_format((float) $value, 6) }}
                                {{ $unit }}
                            </strong>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Custom fields --}}
@if(
    $workflowType === 'custom'
    || $result->custom_reference_value !== null
    || $result->custom_measured_value !== null
)
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-sliders me-1"></i>
            Custom Measurements
        </h6>

        <div class="row g-3">
            @foreach([
                'custom_reference_value' => 'Reference Value',
                'custom_measured_value' => 'Measured Value',
                'custom_unit' => 'Unit',
            ] as $attribute => $label)
                @php
                    $value = data_get($result, $attribute);
                @endphp

                @if($value !== null && $value !== '')
                    <div class="col-md-4">
                        <div class="result-field-item">
                            <small class="text-muted d-block">
                                {{ $label }}
                            </small>
                            <strong>
                                {{ is_numeric($value)
                                    ? number_format((float) $value, 6)
                                    : $value }}
                            </strong>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Trial readings --}}
@if(count($trialData))
    <div class="mt-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-table-list me-1"></i>
            Trial Readings
        </h6>

        <div class="d-flex flex-wrap gap-2">
            @foreach($trialData as $index => $trial)
                <span class="badge bg-secondary px-3 py-2">
                    T{{ $index + 1 }}:
                    {{ number_format((float) $trial, 6) }}
                </span>
            @endforeach
        </div>

        @if($trialAverage !== null)
            <small class="text-muted d-block mt-2">
                Average:
                <strong>{{ number_format((float) $trialAverage, 6) }}</strong>
            </small>
        @endif
    </div>
@endif

@if($result->notes)
    <div class="mt-4">
        <h6 class="text-primary mb-2">
            <i class="fas fa-note-sticky me-1"></i>
            Notes
        </h6>

        <div class="alert alert-light border mb-0">
            {{ $result->notes }}
        </div>
    </div>
@endif

@once
    @push('styles')
        <style>
            .result-field-item {
                height: 100%;
                padding: 12px 14px;
                border: 1px solid #dce5f0;
                border-radius: 10px;
                background: #f8fafc;
            }

            .result-field-item small {
                margin-bottom: 4px;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .result-field-item strong {
                display: block;
                color: #172033;
                font-size: 0.92rem;
                overflow-wrap: anywhere;
            }
        </style>
    @endpush
@endonce
