@extends('layouts.app')

@section('title', 'Add Test Result')

@section('content')
@php
    $equipmentTypeName = $selectedEquipment->equipmentType?->name
        ?? $selectedEquipment->equipment_name
        ?? 'Energy Meter';

    $normalizedEquipmentType = \Illuminate\Support\Str::slug($equipmentTypeName, '_');

    $equipmentTypeCode = match (true) {
        str_contains($normalizedEquipmentType, 'current_transformer') || $normalizedEquipmentType === 'ct' => 'current_transformer',
        str_contains($normalizedEquipmentType, 'voltage_transformer') || str_contains($normalizedEquipmentType, 'potential_transformer') || in_array($normalizedEquipmentType, ['vt', 'pt'], true) => 'voltage_transformer',
        str_contains($normalizedEquipmentType, 'reference_meter') => 'reference_meter',
        str_contains($normalizedEquipmentType, 'test_bench') => 'meter_test_bench',
        str_contains($normalizedEquipmentType, 'portable') => 'portable_test_unit',
        str_contains($normalizedEquipmentType, 'clamp') => 'clamp_meter',
        str_contains($normalizedEquipmentType, 'multimeter') => 'multimeter',
        str_contains($normalizedEquipmentType, 'power_analyzer') => 'power_analyzer',
        str_contains($normalizedEquipmentType, 'insulation') => 'insulation_tester',
        default => 'energy_meter',
    };
@endphp

<div class="container-fluid test-result-create-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-gauge-high text-primary me-2"></i>
                Add Test Result
            </h1>

            <p class="text-muted mb-0">
                Calibration #{{ $calibration->id }}
                · {{ $calibration->equipment_name ?? $calibration->equipment?->name ?? 'N/A' }}
            </p>
        </div>

        <a
            href="{{ route('calibrations.tests.index', $calibration) }}"
            class="btn btn-outline-secondary"
        >
            <i class="fas fa-arrow-left me-1"></i>
            Back to Tests
        </a>
    </div>

    <form
        action="{{ route('calibrations.tests.store', $calibration) }}"
        method="POST"
        id="testForm"
        class="test-result-wizard"
        novalidate
     >
        @csrf

        <input
    type="hidden"
    name="calibration_item_id"
    value="{{ $selectedEquipment->id }}"
>
        <div class="calibration-context-card mb-4">
            <div class="context-item"><span class="context-icon"><i class="fas fa-building"></i></span><div><small>Customer</small><strong>{{ $calibration->customer?->company_name ?? 'N/A' }}</strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-microscope"></i></span><div><small>Equipment</small><strong>{{ $selectedEquipment->equipment_name }}</strong></div></div>
<div class="context-item"><span class="context-icon"><i class="fas fa-microscope"></i></span><div><small>Manufacturer</small><strong>{{ $selectedEquipment->manufacturer }}</strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-barcode"></i></span><div><small>Serial Number</small><strong>{{ $selectedEquipment->serial_number }}</strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-layer-group"></i></span><div><small>Model</small><strong>{{ $selectedEquipment->model }}</strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-layer-group"></i></span><div><small>Equipment type</small><strong>{{ $selectedEquipment->equipmentTypeName }}</strong></div></div>
        </div>

        <div class="test-workflow mb-4" aria-label="Test result entry steps">
            <button type="button" class="workflow-step active" data-step-target="1"><span>1</span><small>Test details</small></button>
            <div class="workflow-line"></div>
            <button type="button" class="workflow-step" data-step-target="2"><span>2</span><small>Measurements</small></button>
            <div class="workflow-line"></div>
            <button type="button" class="workflow-step" data-step-target="3"><span>3</span><small>Calculation</small></button>
            <div class="workflow-line"></div>
            <button type="button" class="workflow-step" data-step-target="4"><span>4</span><small>Review & save</small></button>
        </div>

        <section class="wizard-panel active" data-step="1">
            <div class="test-entry-section">
                <div class="section-heading"><span class="section-heading-icon"><i class="fas fa-clipboard-list"></i></span><div><h6>Equipment-based test selection</h6><p>Select a category and standard test applicable to this equipment type.</p></div></div>
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Equipment type</label>
                        <input type="text" class="form-control" value="{{ $equipmentTypeName }}" readonly>
                        <input type="hidden" name="equipment_type" class="equipment-type-field" value="{{ $equipmentTypeCode }}">
                    </div>
                    <div class="col-lg-4">
                        <label for="testCategory" class="form-label">Test category <span class="text-danger">*</span></label>
                        <select id="testCategory" name="test_category" class="form-select test-category-select" required><option value="">— Select category —</option></select>
                    </div>
                    <div class="col-lg-4">
                        <label for="standardTest" class="form-label">Standard test <span class="text-danger">*</span></label>
                        <select id="standardTest" class="form-select standard-test-select" required disabled><option value="">— Select category first —</option></select>
                    </div>
                    <div class="col-lg-4 custom-test-wrapper d-none">
                        <label for="customTestName" class="form-label">Custom test name <span class="text-danger">*</span></label>
                        <input type="text" id="customTestName" class="form-control custom-test-name" placeholder="Enter test name">
                    </div>
                    <div class="col-lg-4 custom-register-current-wrapper d-none">
                        <label for="customRegisterCurrent" class="form-label">Register current <span class="text-danger">*</span></label>
                        <div class="input-group"><input type="number" id="customRegisterCurrent" class="form-control custom-register-current" step="0.01" min="0"><span class="input-group-text">A</span></div>
                    </div>
                    <input type="hidden" name="test_name" class="test-name-field" required>
                    <input type="hidden" name="test_type" class="test-type-field" required>
                    <input type="hidden" name="standard_test_code" class="standard-test-code-field">
                    <input type="hidden" name="workflow_type" class="workflow-type-field">

                    <div class="col-md-4 condition-field condition-applied"><label class="form-label">Applied value</label><input type="text" name="applied_value" class="form-control applied-value-field"></div>
                    <div class="col-md-4 condition-field condition-power-factor"><label class="form-label">Power factor</label><select name="power_factor" class="form-select power-factor-field"><option value="">Not applicable</option><option value="1.0PF">1.0 Unity</option><option value="0.5LAG">0.5 Lagging</option><option value="0.5LEAD">0.5 Leading</option><option value="0.8LEAD">0.8 Leading</option></select></div>
                    <div class="col-md-4 condition-field condition-load-type"><label class="form-label">Load / range</label><input type="text" name="load_type" class="form-control load-type-field"></div>
                    <div class="col-md-4 condition-field condition-voltage"><label class="form-label">Voltage</label><input type="text" name="voltage" class="form-control voltage-field"></div>
                    <div class="col-md-4 condition-field condition-frequency"><label class="form-label">Frequency</label><input type="text" name="frequency" class="form-control frequency-field"></div>
                    <div class="col-md-4"><label class="form-label">Tolerance (%) <span class="text-danger">*</span></label><input type="number" name="tolerance" class="form-control tolerance-field" step="0.0001" min="0" value="{{ old('tolerance', 0.5) }}" required></div>
                </div>

                <div class="standard-reference-card mt-3"><div><small>Selected test</small><strong class="selected-test-preview">Select a standard test</strong><small class="mt-1">Reference</small><strong class="standard-reference">Reference will appear here</strong></div><i class="fas fa-scale-balanced"></i></div>

                <div class="register-fields d-none mt-3">
                    <hr><div class="section-heading compact"><span class="section-heading-icon"><i class="fas fa-calculator"></i></span><div><h6>Register test details</h6><p>Enter start and stop register readings.</p></div></div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Start reading (kWh) <span class="text-danger">*</span></label><input type="number" name="start_reading" class="form-control start-reading-field" step="0.0001"></div>
                        <div class="col-md-3"><label class="form-label">Stop reading (kWh) <span class="text-danger">*</span></label><input type="number" name="stop_reading" class="form-control stop-reading-field" step="0.0001"></div>
                        <div class="col-md-3"><label class="form-label">Energy difference</label><input type="number" name="energy_difference" class="form-control calculated-field energy-difference-field" step="0.0001" readonly></div>
                        <div class="col-md-3"><label class="form-label">Dial error (%)</label><input type="number" name="dial_error" class="form-control dial-error-field" step="0.0001"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="wizard-panel" data-step="2">
            <div class="test-entry-section">
                <div class="section-heading"><span class="section-heading-icon"><i class="fas fa-table-list"></i></span><div><h6 class="stage-two-title">Test measurements</h6><p class="stage-two-description">Enter measurements for the selected test.</p></div></div>

                <div class="dynamic-test-panel d-none" data-workflow-panel="trial_readings">
                    <div class="trial-progress mb-3"><div class="d-flex justify-content-between mb-1"><small>Readings entered</small><strong class="trial-count">0 / 10</strong></div><div class="progress" style="height:8px"><div class="progress-bar trial-progress-bar" style="width:0%"></div></div></div>
                    <div class="table-responsive trial-table-wrapper"><table class="table trial-readings-table mb-0"><thead><tr><th>Trial</th><th>Error (%)</th><th>Trial</th><th>Error (%)</th></tr></thead><tbody>
                        @for($i=1;$i<=5;$i++)<tr><th>{{ $i }}</th><td><input type="number" name="trial_data[]" class="form-control trial-input" step="0.001"></td><th>{{ $i+5 }}</th><td><input type="number" name="trial_data[]" class="form-control trial-input" step="0.001"></td></tr>@endfor
                    </tbody></table></div><div class="trial-validation-message mt-3 text-warning">Enter all 10 readings to continue.</div>
                </div>

                <div class="dynamic-test-panel d-none" data-workflow-panel="starting"><div class="row g-3"><div class="col-md-3"><label class="form-label">Applied voltage (V)</label><input type="number" name="starting_voltage" class="form-control dynamic-required starting-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Starting current (mA)</label><input type="number" name="starting_current" class="form-control dynamic-required starting-current" step="0.001"></div><div class="col-md-3"><label class="form-label">Pulse detected</label><select name="pulse_detected" class="form-select dynamic-required starting-pulse"><option value="">Select</option><option value="yes">Yes</option><option value="no">No</option></select></div><div class="col-md-3"><label class="form-label">Time taken (s)</label><input type="number" name="starting_time" class="form-control starting-time" step="0.01"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="creep"><div class="row g-3"><div class="col-md-3"><label class="form-label">Applied voltage (V)</label><input type="number" name="creep_voltage" class="form-control dynamic-required creep-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Observation time (min)</label><input type="number" name="creep_time" class="form-control dynamic-required creep-time" step="0.01"></div><div class="col-md-3"><label class="form-label">Pulse count</label><input type="number" name="creep_pulses" class="form-control dynamic-required creep-pulses" step="1"></div><div class="col-md-3"><label class="form-label">Maximum allowed pulses</label><input type="number" name="creep_max_pulses" class="form-control dynamic-required creep-max-pulses" step="1" value="0"></div></div></div>

                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_ratio"><div class="row g-3"><div class="col-md-3"><label class="form-label">Rated primary current (A)</label><input type="number" name="ct_rated_primary" class="form-control dynamic-required ct-rated-primary" step="0.001"></div><div class="col-md-3"><label class="form-label">Rated secondary current (A)</label><input type="number" name="ct_rated_secondary" class="form-control dynamic-required ct-rated-secondary" step="0.001"></div><div class="col-md-3"><label class="form-label">Applied primary current (A)</label><input type="number" name="ct_applied_primary" class="form-control dynamic-required ct-applied-primary" step="0.001"></div><div class="col-md-3"><label class="form-label">Measured secondary current (A)</label><input type="number" name="ct_measured_secondary" class="form-control dynamic-required ct-measured-secondary" step="0.0001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_accuracy"><div class="row g-3"><div class="col-md-3"><label class="form-label">Rated primary current (A)</label><input type="number" name="ct_accuracy_rated_primary" class="form-control dynamic-required ct-accuracy-rated-primary" step="0.001"></div><div class="col-md-3"><label class="form-label">Rated secondary current (A)</label><input type="number" name="ct_accuracy_rated_secondary" class="form-control dynamic-required ct-accuracy-rated-secondary" step="0.001"></div><div class="col-md-3"><label class="form-label">Measured primary current (A)</label><input type="number" name="ct_accuracy_primary" class="form-control dynamic-required ct-accuracy-primary" step="0.0001"></div><div class="col-md-3"><label class="form-label">Measured secondary current (A)</label><input type="number" name="ct_accuracy_secondary" class="form-control dynamic-required ct-accuracy-secondary" step="0.0001"></div><div class="col-md-3"><label class="form-label">Phase displacement (min)</label><input type="number" name="phase_displacement" class="form-control ct-phase" step="0.001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_excitation"><div class="row g-3"><div class="col-md-3"><label class="form-label">Applied voltage (V)</label><input type="number" name="excitation_voltage" class="form-control dynamic-required excitation-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Excitation current (mA)</label><input type="number" name="excitation_current" class="form-control dynamic-required excitation-current" step="0.001"></div><div class="col-md-3"><label class="form-label">Knee-point voltage (V)</label><input type="number" name="knee_point_voltage" class="form-control dynamic-required knee-point-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Minimum knee point (V)</label><input type="number" name="minimum_knee_point" class="form-control dynamic-required minimum-knee-point" step="0.01"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_burden"><div class="row g-3"><div class="col-md-4"><label class="form-label">Rated burden (VA)</label><input type="number" name="rated_burden" class="form-control dynamic-required rated-burden" step="0.01"></div><div class="col-md-4"><label class="form-label">Measured burden (VA)</label><input type="number" name="measured_burden" class="form-control dynamic-required measured-burden" step="0.01"></div><div class="col-md-4"><label class="form-label">Phase displacement (min)</label><input type="number" name="burden_phase" class="form-control burden-phase" step="0.001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="vt_ratio"><div class="row g-3"><div class="col-md-3"><label class="form-label">Rated primary voltage (V)</label><input type="number" name="vt_rated_primary" class="form-control dynamic-required vt-rated-primary" step="0.01"></div><div class="col-md-3"><label class="form-label">Rated secondary voltage (V)</label><input type="number" name="vt_rated_secondary" class="form-control dynamic-required vt-rated-secondary" step="0.01"></div><div class="col-md-3"><label class="form-label">Applied primary voltage (V)</label><input type="number" name="vt_applied_primary" class="form-control dynamic-required vt-applied-primary" step="0.01"></div><div class="col-md-3"><label class="form-label">Measured secondary voltage (V)</label><input type="number" name="vt_measured_secondary" class="form-control dynamic-required vt-measured-secondary" step="0.0001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="comparison"><div class="row g-3"><div class="col-md-3"><label class="form-label">Reference value</label><input type="number" name="reference_value" class="form-control dynamic-required comparison-reference" step="0.000001"></div><div class="col-md-3"><label class="form-label">Indicated value</label><input type="number" name="indicated_value" class="form-control dynamic-required comparison-indicated" step="0.000001"></div><div class="col-md-3"><label class="form-label">Range / unit</label><input type="text" name="measurement_range" class="form-control dynamic-required"></div><div class="col-md-3"><label class="form-label">Resolution</label><input type="number" name="instrument_resolution" class="form-control comparison-resolution" step="0.000001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="resistance"><div class="row g-3"><div class="col-md-4"><label class="form-label">Reference resistance (Ω)</label><input type="number" name="reference_resistance" class="form-control dynamic-required resistance-reference" step="0.0001"></div><div class="col-md-4"><label class="form-label">Measured resistance (Ω)</label><input type="number" name="measured_resistance" class="form-control dynamic-required resistance-measured" step="0.0001"></div><div class="col-md-4"><label class="form-label">Temperature (°C)</label><input type="number" name="resistance_temperature" class="form-control" step="0.1"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="insulation"><div class="row g-3"><div class="col-md-4"><label class="form-label">Test voltage (V DC)</label><input type="number" name="insulation_test_voltage" class="form-control dynamic-required insulation-voltage" step="1"></div><div class="col-md-4"><label class="form-label">Measured resistance (MΩ)</label><input type="number" name="insulation_measured_resistance" class="form-control dynamic-required insulation-resistance" step="0.01"></div><div class="col-md-4"><label class="form-label">Minimum acceptable (MΩ)</label><input type="number" name="insulation_minimum" class="form-control dynamic-required insulation-minimum" step="0.01"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="custom"><div class="row g-3"><div class="col-md-4"><label class="form-label">Reference value</label><input type="number" name="custom_reference_value" class="form-control dynamic-required custom-reference" step="0.000001"></div><div class="col-md-4"><label class="form-label">Measured value</label><input type="number" name="custom_measured_value" class="form-control dynamic-required custom-measured" step="0.000001"></div><div class="col-md-4"><label class="form-label">Unit</label><input type="text" name="custom_unit" class="form-control dynamic-required"></div></div></div>
            </div>
        </section>

        <section class="wizard-panel" data-step="3">
            <div class="test-entry-section">
                <div class="section-heading"><span class="section-heading-icon"><i class="fas fa-square-root-variable"></i></span><div><h6 class="stage-three-title">Calculation and decision</h6><p class="stage-three-description">Review the calculated result.</p></div></div>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label dynamic-result-label">Calculated error (%)</label><input type="number" name="error_value" class="form-control calculated-field error-value-field" step="0.000001" readonly></div>
                    <div class="col-md-3 standard-deviation-wrapper d-none"><label class="form-label">Standard deviation</label><input type="number" name="standard_deviation" class="form-control calculated-field standard-deviation-field" step="0.000001" readonly></div>
                    <div class="col-md-3 uncertainty-wrapper d-none"><label class="form-label">Expanded uncertainty (%)</label><input type="number" name="uncertainty" class="form-control calculated-field uncertainty-field" step="0.000001" readonly></div>
                    <div class="col-md-3"><label class="form-label">Result <span class="text-danger">*</span></label><select name="result" class="form-select result-field" required><option value="">Select Result</option><option value="PASS">PASS</option><option value="FAIL">FAIL</option></select></div>
                </div>
                <div class="calculation-breakdown mt-3"><div><small class="metric-one-label">Measured</small><strong class="u1-value">—</strong></div><div><small class="metric-two-label">Reference</small><strong class="u2-value">—</strong></div><div><small class="metric-three-label">Deviation</small><strong class="u3-value">—</strong></div><div><small>Tolerance</small><strong class="dynamic-tolerance-display">—</strong></div></div>
                <div class="mt-3"><button type="button" class="btn btn-info calculate-button"><i class="fas fa-calculator me-1"></i>Recalculate</button><span class="calculation-message ms-2"></span></div>
                <div class="result-summary-card result-pending mt-4"><div class="result-status"><span class="result-status-icon"><i class="fas fa-hourglass-half"></i></span><div><small>Test decision</small><strong class="result-status-text">Awaiting calculation</strong></div></div><div class="result-metrics"><div><small>Error / margin</small><strong class="summary-error">—</strong></div><div><small>Additional metric</small><strong class="summary-uncertainty">—</strong></div><div><small>Tolerance</small><strong class="summary-tolerance">—</strong></div></div></div>
            </div>
        </section>

        <section class="wizard-panel" data-step="4">
            <div class="row g-4"><div class="col-lg-8"><div class="test-entry-section h-100"><div class="section-heading"><span class="section-heading-icon"><i class="fas fa-clipboard-check"></i></span><div><h6>Review result</h6><p>Confirm all details before saving.</p></div></div><div class="review-grid"><div><small>Equipment type</small><strong class="review-equipment-type">—</strong></div><div><small>Test category</small><strong class="review-category">—</strong></div><div><small>Test name</small><strong class="review-test-name">—</strong></div><div><small>Test type</small><strong class="review-test-type">—</strong></div><div><small>Applied value</small><strong class="review-applied-value">—</strong></div><div><small>Conditions</small><strong class="review-voltage-frequency">—</strong></div><div><small class="review-readings-label">Measurements</small><strong class="review-trials">—</strong></div><div><small>Error / margin</small><strong class="review-error">—</strong></div><div><small>Uncertainty</small><strong class="review-uncertainty">—</strong></div><div><small>Result</small><strong class="review-result">—</strong></div></div></div></div><div class="col-lg-4"><div class="test-entry-section h-100"><div class="section-heading"><span class="section-heading-icon"><i class="fas fa-note-sticky"></i></span><div><h6>Technician notes</h6><p>Add observations or abnormalities.</p></div></div><textarea name="notes" class="form-control" rows="10">{{ old('notes') }}</textarea></div></div></div>
        </section>

        <div class="wizard-footer mt-4"><div class="footer-calculation-status"><i class="fas fa-circle-info me-1"></i><span>Complete step 1 to continue.</span></div><div class="d-flex gap-2"><button type="button" class="btn btn-outline-secondary previous-step-button d-none"><i class="fas fa-arrow-left me-1"></i>Previous</button><button type="button" class="btn btn-primary next-step-button">Next<i class="fas fa-arrow-right ms-1"></i></button><button type="submit" class="btn btn-success save-test-button d-none"><span class="save-label"><i class="fas fa-check me-1"></i>Save Test Result</span><span class="saving-label d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span></button></div></div>

    </form>
</div>
@endsection

@push('styles')
<style>
    .test-result-create-page {
        max-width: 1500px;
    }

    .test-result-wizard {
        padding: 20px;
        border: 1px solid #dce5f0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 16px 42px rgba(15, 23, 42, 0.10);
    }

    .calibration-context-card {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 14px;
        border: 1px solid #dce5f0;
        border-radius: 14px;
        background: #f7f9fc;
    }

    .context-item {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .context-icon,
    .section-heading-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        border-radius: 10px;
        background: #e8f0fa;
        color: #0b2d5c;
    }

    .context-item div {
        min-width: 0;
    }

    .context-item small,
    .standard-reference-card small,
    .result-status small,
    .result-metrics small,
    .calculation-breakdown small,
    .review-grid small {
        display: block;
        color: #68768a;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .context-item strong {
        display: block;
        overflow: hidden;
        color: #172033;
        font-size: 0.86rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .test-workflow {
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .workflow-step {
        width: 105px;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: center;
        cursor: pointer;
    }

    .workflow-step span {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #cad5e3;
        border-radius: 50%;
        background: #fff;
        color: #66758a;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .workflow-step.active span {
        border-color: #0b2d5c;
        background: #0b2d5c;
        color: #fff;
    }

    .workflow-step.completed span {
        border-color: #198754;
        background: #198754;
        color: #fff;
    }

    .workflow-step small {
        display: block;
        margin-top: 6px;
        color: #68768a;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .workflow-line {
        width: 72px;
        height: 2px;
        margin-top: 16px;
        background: #d4deea;
    }

    .wizard-panel {
        display: none;
    }

    .wizard-panel.active {
        display: block;
        animation: wizardFadeIn 0.22s ease;
    }

    @keyframes wizardFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .test-entry-section {
        padding: 20px;
        border: 1px solid #dce5f0;
        border-radius: 15px;
        background: #fff;
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 18px;
    }

    .section-heading.compact {
        margin-bottom: 12px;
    }

    .section-heading h6 {
        margin: 0 0 3px;
        color: #0b2d5c;
        font-size: 0.98rem;
        font-weight: 800;
    }

    .section-heading p {
        margin: 0;
        color: #68768a;
        font-size: 0.8rem;
    }

    .standard-reference-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-left: 4px solid #f2b705;
        border-radius: 10px;
        background: #fff9df;
    }

    .standard-reference-card strong {
        display: block;
        margin-top: 2px;
        color: #614c00;
        font-size: 0.82rem;
    }

    .trial-table-wrapper {
        border: 1px solid #dce5f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .trial-readings-table {
        table-layout: fixed;
    }

    .trial-readings-table thead th {
        padding: 9px;
        border-bottom: 0;
        background: #0b2d5c;
        color: #fff;
        font-size: 0.78rem;
    }

    .trial-readings-table tbody th {
        width: 58px;
        background: #f3f6fa;
        color: #0b2d5c;
        text-align: center;
        vertical-align: middle;
    }

    .trial-readings-table td {
        padding: 7px;
    }

    .trial-readings-table .form-control {
        min-width: 120px;
        text-align: right;
    }

    .trial-input.is-filled {
        border-color: #198754;
        background: #f0faf4;
    }

    .calculated-field {
        background: #eef3f8 !important;
        color: #0b2d5c;
        font-weight: 800;
    }

    .calculation-breakdown,
    .review-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .calculation-breakdown > div,
    .review-grid > div {
        padding: 12px;
        border: 1px solid #dce5f0;
        border-radius: 10px;
        background: #f7f9fc;
    }

    .calculation-breakdown strong,
    .review-grid strong {
        display: block;
        margin-top: 4px;
        color: #172033;
        font-size: 0.88rem;
        overflow-wrap: anywhere;
    }

    .result-summary-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px;
        border: 1px solid #dce5f0;
        border-left: 6px solid #94a3b8;
        border-radius: 15px;
        background: #f8fafc;
    }

    .result-summary-card.result-pass {
        border-left-color: #198754;
        background: #eaf8f0;
    }

    .result-summary-card.result-fail {
        border-left-color: #dc3545;
        background: #fdeced;
    }

    .result-status {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .result-status-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        font-size: 1.2rem;
    }

    .result-pass .result-status-icon {
        background: #198754;
        color: #fff;
    }

    .result-fail .result-status-icon {
        background: #dc3545;
        color: #fff;
    }

    .result-status-text {
        display: block;
        color: #334155;
        font-size: 1.25rem;
        font-weight: 900;
    }

    .result-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(90px, 1fr));
        gap: 12px;
    }

    .result-metrics > div {
        padding: 8px 12px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.72);
        text-align: center;
    }

    .result-metrics strong {
        display: block;
        margin-top: 2px;
        color: #172033;
        font-size: 0.9rem;
    }

    .wizard-footer {
        position: sticky;
        bottom: 12px;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 16px;
        border: 1px solid #dce5f0;
        border-radius: 13px;
        background: rgba(255, 255, 255, 0.97);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14);
        backdrop-filter: blur(12px);
    }

    .footer-calculation-status {
        color: #68768a;
        font-size: 0.8rem;
    }

    .save-test-button {
        min-width: 170px;
        font-weight: 800;
    }

    @media (max-width: 991.98px) {
        .calibration-context-card {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .calculation-breakdown,
        .review-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .result-summary-card {
            align-items: stretch;
            flex-direction: column;
        }

        .result-metrics {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .test-result-wizard {
            padding: 12px;
        }

        .calibration-context-card,
        .calculation-breakdown,
        .review-grid,
        .result-metrics {
            grid-template-columns: 1fr;
        }

        .test-workflow {
            overflow-x: auto;
            justify-content: flex-start;
        }

        .workflow-line {
            width: 35px;
            min-width: 35px;
        }

        .test-entry-section {
            padding: 15px;
        }

        .wizard-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .wizard-footer > div:last-child {
            width: 100%;
        }

        .previous-step-button,
        .next-step-button,
        .save-test-button {
            flex: 1;
        }
    }

    .dynamic-test-panel { padding: 4px 0; }
    .dynamic-test-panel.d-none { display: none !important; }
    .calculated-field { background: #eef3f8 !important; font-weight: 700; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const laboratoryTestConfig = {
        energy_meter:{label:'Energy Meter',categories:{accuracy:{label:'Accuracy and Uncertainty',tests:{uncertainty_low_load:{name:'UNCERTAINTY OF MEASUREMENT AT 0.05 Ib; 1.0 PF',type:'accuracy',appliedValue:'0.05 Ib',powerFactor:'1.0PF',loadType:'Low load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'},uncertainty_base_load:{name:'UNCERTAINTY OF MEASUREMENT AT Ib; 1.0 PF',type:'accuracy',appliedValue:'Ib',powerFactor:'1.0PF',loadType:'Base load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'},uncertainty_inductive:{name:'UNCERTAINTY OF MEASUREMENT AT Ib; 0.5 LAG PF',type:'accuracy',appliedValue:'Ib',powerFactor:'0.5LAG',loadType:'Inductive load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'},uncertainty_imax:{name:'UNCERTAINTY OF MEASUREMENT AT IMAX; 1.0 PF',type:'accuracy',appliedValue:'IMAX',powerFactor:'1.0PF',loadType:'Maximum load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'}}},register:{label:'Register Verification',tests:{register_025a:{name:'REGISTER TEST @ 0.25 A',type:'register',appliedValue:'0.25 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_1a:{name:'REGISTER TEST @ 1 A',type:'register',appliedValue:'1 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_5a:{name:'REGISTER TEST @ 5 A',type:'register',appliedValue:'5 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_20a:{name:'REGISTER TEST @ 20 A',type:'register',appliedValue:'20 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_imax:{name:'REGISTER TEST @ IMAX',type:'register',appliedValue:'IMAX',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_custom:{name:'',type:'register',appliedValue:'',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Custom register verification',workflow:'register',customCurrent:true}}},functional:{label:'Functional Tests',tests:{starting_current:{name:'STARTING CURRENT TEST',type:'starting',appliedValue:'',powerFactor:'1.0PF',loadType:'Starting current',voltage:'230V',frequency:'50Hz',tolerance:0,reference:'Starting-current procedure',workflow:'starting'},creep:{name:'NO-LOAD / CREEP TEST',type:'creep',appliedValue:'No load',powerFactor:'1.0PF',loadType:'No load',voltage:'230V',frequency:'50Hz',tolerance:0,reference:'No-load / creep procedure',workflow:'creep'}}},custom:{label:'Other Tests',tests:{other:{name:'',type:'custom',appliedValue:'',powerFactor:'',loadType:'',voltage:'',frequency:'',tolerance:1,reference:'Laboratory procedure',workflow:'custom',customName:true}}}}},
        current_transformer:{label:'Current Transformer',categories:{accuracy:{label:'Accuracy',tests:{ct_accuracy_5:{name:'CT ACCURACY TEST @ 5% RATED CURRENT',type:'ct_accuracy',appliedValue:'5% rated current',frequency:'50Hz',tolerance:1,reference:'IEC 61869-2',workflow:'ct_accuracy'},ct_accuracy_20:{name:'CT ACCURACY TEST @ 20% RATED CURRENT',type:'ct_accuracy',appliedValue:'20% rated current',frequency:'50Hz',tolerance:1,reference:'IEC 61869-2',workflow:'ct_accuracy'},ct_accuracy_100:{name:'CT ACCURACY TEST @ 100% RATED CURRENT',type:'ct_accuracy',appliedValue:'100% rated current',frequency:'50Hz',tolerance:1,reference:'IEC 61869-2',workflow:'ct_accuracy'}}},ratio:{label:'Ratio and Polarity',tests:{ct_ratio:{name:'CT TRANSFORMATION RATIO TEST',type:'ct_ratio',tolerance:1,reference:'IEC 61869-2',workflow:'ct_ratio'}}},excitation:{label:'Excitation',tests:{ct_excitation:{name:'CT EXCITATION / KNEE-POINT TEST',type:'ct_excitation',tolerance:0,reference:'IEC 61869-2',workflow:'ct_excitation'}}},burden:{label:'Burden',tests:{ct_burden:{name:'CT BURDEN TEST',type:'ct_burden',tolerance:5,reference:'IEC 61869-2',workflow:'ct_burden'}}},resistance:{label:'Resistance and Insulation',tests:{ct_resistance:{name:'CT SECONDARY WINDING RESISTANCE',type:'resistance',tolerance:1,reference:'IEC 61869-2',workflow:'resistance'},ct_insulation:{name:'CT INSULATION RESISTANCE TEST',type:'insulation',tolerance:0,reference:'IEC 61869-2',workflow:'insulation'}}}}},
        voltage_transformer:{label:'Voltage Transformer',categories:{ratio:{label:'Ratio and Accuracy',tests:{vt_ratio:{name:'VT TRANSFORMATION RATIO TEST',type:'vt_ratio',frequency:'50Hz',tolerance:1,reference:'IEC 61869-3',workflow:'vt_ratio'},vt_accuracy:{name:'VT ACCURACY TEST',type:'vt_accuracy',frequency:'50Hz',tolerance:1,reference:'IEC 61869-3',workflow:'vt_ratio'}}},insulation:{label:'Insulation',tests:{vt_insulation:{name:'VT INSULATION RESISTANCE TEST',type:'insulation',tolerance:0,reference:'IEC 61869-3',workflow:'insulation'}}}}},
        reference_meter:{label:'Reference Meter',categories:{performance:{label:'Performance',tests:{reference_accuracy:{name:'REFERENCE METER ACCURACY VERIFICATION',type:'comparison',tolerance:.1,reference:'Laboratory comparison procedure',workflow:'comparison'},reference_stability:{name:'REFERENCE METER STABILITY TEST',type:'comparison',tolerance:.1,reference:'Laboratory stability procedure',workflow:'comparison'}}}}},
        meter_test_bench:{label:'Meter Test Bench',categories:{verification:{label:'Bench Verification',tests:{bench_voltage:{name:'METER TEST BENCH VOLTAGE VERIFICATION',type:'comparison',tolerance:.2,reference:'Bench verification procedure',workflow:'comparison'},bench_current:{name:'METER TEST BENCH CURRENT VERIFICATION',type:'comparison',tolerance:.2,reference:'Bench verification procedure',workflow:'comparison'},bench_energy:{name:'METER TEST BENCH ENERGY VERIFICATION',type:'comparison',tolerance:.2,reference:'Bench verification procedure',workflow:'comparison'},bench_frequency:{name:'METER TEST BENCH FREQUENCY VERIFICATION',type:'comparison',tolerance:.1,reference:'Bench verification procedure',workflow:'comparison'}}}}},
        portable_test_unit:{label:'Portable Test Unit',categories:{verification:{label:'Verification',tests:{portable_voltage:{name:'PORTABLE TEST UNIT VOLTAGE VERIFICATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},portable_current:{name:'PORTABLE TEST UNIT CURRENT VERIFICATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'}}}}},
        clamp_meter:{label:'Clamp Meter',categories:{current:{label:'Current Calibration',tests:{clamp_ac:{name:'CLAMP METER AC CURRENT CALIBRATION',type:'comparison',frequency:'50Hz',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},clamp_dc:{name:'CLAMP METER DC CURRENT CALIBRATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'}}}}},
        multimeter:{label:'Multimeter',categories:{electrical:{label:'Electrical Measurements',tests:{dmm_dc_voltage:{name:'MULTIMETER DC VOLTAGE CALIBRATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},dmm_ac_voltage:{name:'MULTIMETER AC VOLTAGE CALIBRATION',type:'comparison',frequency:'50Hz',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},dmm_current:{name:'MULTIMETER CURRENT CALIBRATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},dmm_resistance:{name:'MULTIMETER RESISTANCE CALIBRATION',type:'resistance',tolerance:1,reference:'Laboratory comparison procedure',workflow:'resistance'}}}}},
        power_analyzer:{label:'Power Analyzer',categories:{electrical:{label:'Electrical Accuracy',tests:{pa_voltage:{name:'POWER ANALYZER VOLTAGE ACCURACY',type:'comparison',tolerance:.5,reference:'Laboratory comparison procedure',workflow:'comparison'},pa_current:{name:'POWER ANALYZER CURRENT ACCURACY',type:'comparison',tolerance:.5,reference:'Laboratory comparison procedure',workflow:'comparison'},pa_power:{name:'POWER ANALYZER POWER ACCURACY',type:'comparison',tolerance:.5,reference:'Laboratory comparison procedure',workflow:'comparison'},pa_frequency:{name:'POWER ANALYZER FREQUENCY ACCURACY',type:'comparison',tolerance:.1,reference:'Laboratory comparison procedure',workflow:'comparison'}}}}},
        insulation_tester:{label:'Insulation Tester',categories:{insulation:{label:'Insulation Tests',tests:{insulation_output:{name:'INSULATION TESTER OUTPUT VOLTAGE VERIFICATION',type:'comparison',tolerance:5,reference:'Laboratory comparison procedure',workflow:'comparison'},insulation_resistance:{name:'INSULATION RESISTANCE VERIFICATION',type:'insulation',tolerance:0,reference:'Laboratory insulation procedure',workflow:'insulation'}}}}}
    };

    document.querySelectorAll('.calibration-test-form, #testForm').forEach(function(form){
        if(form.dataset.wizardReady==='1') return; form.dataset.wizardReady='1';
        let currentStep=1, highestCompletedStep=0, activeTest=null;
        const q=s=>form.querySelector(s), qa=s=>[...form.querySelectorAll(s)], num=e=>{if(!e||e.value.trim()==='')return null;const v=Number(e.value);return Number.isFinite(v)?v:null};
        const panels=qa('.wizard-panel'), steps=qa('.workflow-step'), lines=qa('.workflow-line'), dynamicPanels=qa('.dynamic-test-panel'), trials=qa('.trial-input');
        const previous=q('.previous-step-button'), next=q('.next-step-button'), save=q('.save-test-button'), footer=q('.footer-calculation-status span');
        const equipment=q('.equipment-type-field'), category=q('.test-category-select'), standard=q('.standard-test-select'), testName=q('.test-name-field'), testType=q('.test-type-field'), testCode=q('.standard-test-code-field'), workflowField=q('.workflow-type-field');
        const customNameWrap=q('.custom-test-wrapper'), customName=q('.custom-test-name'), customCurrentWrap=q('.custom-register-current-wrapper'), customCurrent=q('.custom-register-current');
        const applied=q('.applied-value-field'), pf=q('.power-factor-field'), load=q('.load-type-field'), voltage=q('.voltage-field'), frequency=q('.frequency-field'), tolerance=q('.tolerance-field'), reference=q('.standard-reference'), preview=q('.selected-test-preview');
        const registerFields=q('.register-fields'), start=q('.start-reading-field'), stop=q('.stop-reading-field'), difference=q('.energy-difference-field'), dial=q('.dial-error-field');
        const error=q('.error-value-field'), sd=q('.standard-deviation-field'), uncertainty=q('.uncertainty-field'), result=q('.result-field'), calcButton=q('.calculate-button');
        const workflow=()=>activeTest?.workflow||'custom';
        function config(){return laboratoryTestConfig[equipment.value]||laboratoryTestConfig.energy_meter}
        function fillCategories(){category.innerHTML='<option value="">— Select category —</option>';Object.entries(config().categories).forEach(([k,v])=>category.add(new Option(v.label,k)));standard.innerHTML='<option value="">— Select category first —</option>';standard.disabled=true}
        function fillTests(){const c=config().categories[category.value];standard.innerHTML='<option value="">— Select standard test —</option>';activeTest=null;resetSelection();if(!c){standard.disabled=true;return}Object.entries(c.tests).forEach(([k,v])=>standard.add(new Option(v.name||'Other / Custom Test',k)));standard.disabled=false}
        function resetSelection(){testName.value='';testType.value='';testCode.value='';workflowField.value='';[applied,load,voltage,frequency].forEach(e=>e.value='');pf.value='';preview.textContent='Select a standard test';reference.textContent='Reference will appear here';customNameWrap.classList.add('d-none');customCurrentWrap.classList.add('d-none');registerFields.classList.add('d-none');customName.required=false;customCurrent.required=false;setRegisterRequired(false);updateDynamicPanel()}
        function applyTest(){const c=config().categories[category.value];activeTest=c?.tests?.[standard.value]||null;if(!activeTest){resetSelection();return}testName.value=activeTest.name||'';testType.value=activeTest.type||'';testCode.value=standard.value;workflowField.value=activeTest.workflow;applied.value=activeTest.appliedValue||'';pf.value=activeTest.powerFactor||'';load.value=activeTest.loadType||'';voltage.value=activeTest.voltage||'';frequency.value=activeTest.frequency||'';tolerance.value=activeTest.tolerance??.5;preview.textContent=activeTest.name||'Custom laboratory test';reference.textContent=activeTest.reference||'Laboratory procedure';customNameWrap.classList.toggle('d-none',!activeTest.customName);customCurrentWrap.classList.toggle('d-none',!activeTest.customCurrent);customName.required=!!activeTest.customName;customCurrent.required=!!activeTest.customCurrent;registerFields.classList.toggle('d-none',workflow()!=='register');setRegisterRequired(workflow()==='register');updateDynamicPanel();updateWorkflowLabels()}
        function setRegisterRequired(on){[start,stop].forEach(e=>{if(e)e.required=on})}
        function updateRegister(){const a=num(start),b=num(stop);difference.value=a!==null&&b!==null?(b-a).toFixed(4):''}
        function updateDynamicPanel(){dynamicPanels.forEach(p=>{p.classList.add('d-none');p.querySelectorAll('.dynamic-required').forEach(e=>e.required=false)});trials.forEach(e=>e.required=false);if(workflow()==='register')return;const p=q(`[data-workflow-panel="${workflow()}"]`)||q('[data-workflow-panel="custom"]');p?.classList.remove('d-none');p?.querySelectorAll('.dynamic-required').forEach(e=>e.required=true);if(workflow()==='trial_readings')trials.forEach(e=>e.required=true);const map={trial_readings:['Ten mandatory trial readings','Enter all 10 error readings.'],starting:['Starting-current measurements','Enter voltage, starting current and pulse observation.'],creep:['No-load / creep measurements','Enter observation time and pulse count.'],ct_ratio:['CT ratio measurements','Enter rated, applied and measured currents.'],ct_accuracy:['CT accuracy measurements','Enter measured currents and phase displacement.'],ct_excitation:['CT excitation measurements','Enter voltage, current and knee-point requirements.'],ct_burden:['CT burden measurements','Enter rated and measured burden.'],vt_ratio:['VT ratio measurements','Enter rated, applied and measured voltages.'],comparison:['Reference comparison','Enter reference and indicated values.'],resistance:['Resistance measurements','Enter reference and measured resistance.'],insulation:['Insulation resistance','Enter test voltage and acceptance limits.'],custom:['Custom measurements','Enter reference and measured values.']};const m=map[workflow()]||map.custom;q('.stage-two-title').textContent=m[0];q('.stage-two-description').textContent=m[1]}
        function updateWorkflowLabels(){const labels={trial_readings:['10 readings','Uncertainty'],starting:['Measurements','Assessment'],creep:['Measurements','Assessment'],ct_ratio:['Ratio data','Ratio result'],ct_accuracy:['Accuracy data','Accuracy result'],ct_excitation:['Excitation data','Assessment'],ct_burden:['Burden data','Assessment'],vt_ratio:['Voltage data','Ratio result'],comparison:['Comparison','Error result'],resistance:['Resistance','Error result'],insulation:['Insulation','Assessment'],custom:['Measurements','Calculation'],register:['Register details','Review']};const l=labels[workflow()]||labels.custom;q('.workflow-step[data-step-target="2"] small').textContent=l[0];q('.workflow-step[data-step-target="3"] small').textContent=l[1]}
        function validateStep1(){form.classList.add('was-validated');if(!category.value||!standard.value||!testName.value||!testType.value){footer.textContent='Select the category and standard test.';return false}if(customName.required&&!customName.value.trim()){customName.focus();return false}if(customCurrent.required&&num(customCurrent)===null){customCurrent.focus();return false}if(workflow()==='register'&&(num(start)===null||num(stop)===null)){footer.textContent='Enter register start and stop readings.';return false}return true}
        function validateStep2(){if(workflow()==='register')return true;const p=q(`[data-workflow-panel="${workflow()}"]`)||q('[data-workflow-panel="custom"]');if(workflow()==='trial_readings'){const vals=trials.map(num);if(vals.some(v=>v===null)){footer.textContent='All 10 trial readings are required.';return false}}const invalid=p?.querySelector(':invalid');if(invalid){invalid.reportValidity();invalid.focus();return false}return true}
        function pct(ref,meas){return ref===null||meas===null||ref===0?null:((meas-ref)/ref)*100}
        function present(title,label,showStats=false){q('.stage-three-title').textContent=title;q('.dynamic-result-label').textContent=label;q('.standard-deviation-wrapper').classList.toggle('d-none',!showStats);q('.uncertainty-wrapper').classList.toggle('d-none',!showStats)}
        function finalize(value,m1,m2,m3,forced=null,extra='N/A'){const tol=num(tolerance)??0;const pass=forced?forced==='PASS':Math.abs(value)<=tol;error.value=value.toFixed(6);result.value=pass?'PASS':'FAIL';q('.u1-value').textContent=m1;q('.u2-value').textContent=m2;q('.u3-value').textContent=m3;q('.dynamic-tolerance-display').textContent=`±${tol.toFixed(4)}%`;q('.summary-error').textContent=value.toFixed(6);q('.summary-uncertainty').textContent=extra;q('.summary-tolerance').textContent=`±${tol.toFixed(4)}%`;const box=q('.result-summary-card');box.classList.remove('result-pending','result-pass','result-fail');box.classList.add(pass?'result-pass':'result-fail');q('.result-status-text').textContent=pass?'PASS':'FAIL';q('.result-status-icon i').className=pass?'fas fa-circle-check':'fas fa-circle-xmark';return true}
        function calculate(){const w=workflow();if(w==='register'){const d=num(dial)??0;present('Register test decision','Dial error (%)');return finalize(d,`${num(difference)?.toFixed(4)??'—'} kWh`,'Register difference',`${d.toFixed(4)}%`)}if(w==='trial_readings'){const vals=trials.map(num);if(vals.some(v=>v===null))return false;const mean=vals.reduce((a,b)=>a+b,0)/vals.length;const variance=vals.reduce((a,b)=>a+(b-mean)**2,0)/(vals.length-1);const s=Math.sqrt(variance),u1=s/Math.sqrt(vals.length),u2=.05/Math.sqrt(3),u3=.01/Math.sqrt(12),U=2*Math.sqrt(u1*u1+u2*u2+u3*u3);sd.value=s.toFixed(6);uncertainty.value=U.toFixed(6);present('Accuracy and uncertainty calculation','Average error (%)',true);return finalize(mean,u1.toFixed(6),u2.toFixed(6),u3.toFixed(6),null,`${U.toFixed(6)}%`)}
            let ref=null,meas=null,val=null,forced=null,m1='—',m2='—',m3='—',extra='N/A';
            if(w==='starting'){const detected=q('.starting-pulse').value==='yes';val=num(q('.starting-current'))??0;forced=detected?'PASS':'FAIL';present('Starting-current assessment','Starting current (mA)');return finalize(val,`${val} mA`,detected?'Pulse detected':'No pulse',`${num(q('.starting-time'))??'—'} s`,forced)}
            if(w==='creep'){const pulses=num(q('.creep-pulses')),max=num(q('.creep-max-pulses'));if(pulses===null||max===null)return false;val=pulses-max;forced=pulses<=max?'PASS':'FAIL';present('No-load / creep assessment','Pulse margin');return finalize(val,`${pulses} pulses`,`${max} allowed`,`${val} margin`,forced)}
            if(w==='ct_ratio'||w==='ct_accuracy'){const rp=num(q(w==='ct_ratio'?'.ct-rated-primary':'.ct-accuracy-rated-primary')),rs=num(q(w==='ct_ratio'?'.ct-rated-secondary':'.ct-accuracy-rated-secondary')),ap=num(q(w==='ct_ratio'?'.ct-applied-primary':'.ct-accuracy-primary')),ms=num(q(w==='ct_ratio'?'.ct-measured-secondary':'.ct-accuracy-secondary'));if([rp,rs,ap,ms].some(v=>v===null)||rs===0||ms===0)return false;ref=rp/rs;meas=ap/ms;val=pct(ref,meas);m1=meas.toFixed(6);m2=ref.toFixed(6);m3=w==='ct_accuracy'?`${num(q('.ct-phase'))??'—'} min`:`${val.toFixed(6)}%`;present('Current-transformer ratio calculation','Ratio error (%)');return finalize(val,m1,m2,m3,null,w==='ct_accuracy'?m3:'N/A')}
            if(w==='ct_excitation'){meas=num(q('.knee-point-voltage'));ref=num(q('.minimum-knee-point'));if(meas===null||ref===null)return false;val=meas-ref;forced=meas>=ref?'PASS':'FAIL';present('CT excitation assessment','Knee-point margin (V)');return finalize(val,`${meas} V`,`${ref} V`,`${num(q('.excitation-current'))??'—'} mA`,forced)}
            if(w==='ct_burden'){ref=num(q('.rated-burden'));meas=num(q('.measured-burden'));if(ref===null||meas===null)return false;val=pct(ref,meas);present('CT burden calculation','Burden error (%)');return finalize(val,`${meas} VA`,`${ref} VA`,`${num(q('.burden-phase'))??'—'} min`)}
            if(w==='vt_ratio'){const rp=num(q('.vt-rated-primary')),rs=num(q('.vt-rated-secondary')),ap=num(q('.vt-applied-primary')),ms=num(q('.vt-measured-secondary'));if([rp,rs,ap,ms].some(v=>v===null)||rs===0||ms===0)return false;ref=rp/rs;meas=ap/ms;val=pct(ref,meas);present('Voltage-transformer ratio calculation','Ratio error (%)');return finalize(val,meas.toFixed(6),ref.toFixed(6),`${val.toFixed(6)}%`)}
            if(w==='comparison'){ref=num(q('.comparison-reference'));meas=num(q('.comparison-indicated'))}else if(w==='resistance'){ref=num(q('.resistance-reference'));meas=num(q('.resistance-measured'))}else if(w==='insulation'){meas=num(q('.insulation-resistance'));ref=num(q('.insulation-minimum'));if(meas===null||ref===null)return false;val=meas-ref;forced=meas>=ref?'PASS':'FAIL';present('Insulation resistance assessment','Acceptance margin (MΩ)');return finalize(val,`${meas} MΩ`,`${ref} MΩ`,`${val} MΩ`,forced)}else{ref=num(q('.custom-reference'));meas=num(q('.custom-measured'))}if(ref===null||meas===null)return false;val=pct(ref,meas);present('Reference comparison calculation','Error (%)');return finalize(val,meas.toFixed(6),ref.toFixed(6),(meas-ref).toFixed(6))
        }
        function updateTrials(){const count=trials.filter(e=>num(e)!==null).length;const c=q('.trial-count'),b=q('.trial-progress-bar');if(c)c.textContent=`${count} / 10`;if(b)b.style.width=`${count*10}%`}
        function updateReview(){q('.review-equipment-type').textContent=config().label;q('.review-category').textContent=config().categories[category.value]?.label||'—';q('.review-test-name').textContent=testName.value||'—';q('.review-test-type').textContent=testType.value||'—';q('.review-applied-value').textContent=applied.value||'—';q('.review-voltage-frequency').textContent=[voltage.value,frequency.value,pf.value].filter(Boolean).join(' · ')||'—';q('.review-trials').textContent=workflow()==='trial_readings'?trials.map(e=>e.value||'—').join(', '):workflow()==='register'?`${start.value} → ${stop.value} kWh`:workflow().replaceAll('_',' ');q('.review-error').textContent=error.value||'—';q('.review-uncertainty').textContent=uncertainty.value||'N/A';q('.review-result').textContent=result.value||'—'}
        function showStep(n){currentStep=n;panels.forEach(p=>p.classList.toggle('active',Number(p.dataset.step)===n));steps.forEach(s=>{const x=Number(s.dataset.stepTarget);s.classList.toggle('active',x===n);s.classList.toggle('completed',x<n&&x<=highestCompletedStep)});lines.forEach((l,i)=>l.classList.toggle('completed',i+1<n&&i+1<=highestCompletedStep));previous.classList.toggle('d-none',n===1);next.classList.toggle('d-none',n===4);save.classList.toggle('d-none',n!==4);footer.textContent=n===1?'Complete test details to continue.':n===2?'Enter all required measurements.':n===3?'Review the calculated decision.':'Confirm and save the result.';q(`.wizard-panel[data-step="${n}"]`)?.scrollIntoView({block:'start',behavior:'smooth'})}
        function goNext(){if(currentStep===1){if(!validateStep1())return;highestCompletedStep=Math.max(highestCompletedStep,1);if(workflow()==='register'){calculate();updateReview();highestCompletedStep=3;showStep(4)}else showStep(2);return}if(currentStep===2){if(!validateStep2())return;if(!calculate()){footer.textContent='Check the entered measurements.';return}highestCompletedStep=Math.max(highestCompletedStep,2);showStep(3);return}if(currentStep===3){if(!calculate())return;highestCompletedStep=Math.max(highestCompletedStep,3);updateReview();showStep(4)}}
        category.addEventListener('change',fillTests);standard.addEventListener('change',applyTest);customName.addEventListener('input',()=>{if(activeTest?.customName){testName.value=customName.value.trim();preview.textContent=testName.value||'Custom laboratory test'}});customCurrent.addEventListener('input',()=>{if(activeTest?.customCurrent){const v=customCurrent.value;testName.value=v?`REGISTER TEST @ ${v} A`:'';applied.value=v?`${v} A`:'';preview.textContent=testName.value||'Custom register test'}});[start,stop].forEach(e=>e?.addEventListener('input',updateRegister));trials.forEach(e=>e.addEventListener('input',updateTrials));next.addEventListener('click',goNext);previous.addEventListener('click',()=>showStep(Math.max(1,currentStep-1)));calcButton?.addEventListener('click',calculate);steps.forEach(s=>s.addEventListener('click',()=>{const t=Number(s.dataset.stepTarget);if(t<=highestCompletedStep+1&&!(workflow()==='register'&&(t===2||t===3)))showStep(t)}));form.addEventListener('submit',e=>{if(!validateStep1()||!validateStep2()||!calculate()){e.preventDefault();return}save.disabled=true;q('.save-label')?.classList.add('d-none');q('.saving-label')?.classList.remove('d-none')});
        fillCategories();updateDynamicPanel();updateWorkflowLabels();updateTrials();showStep(1);
    });
});
</script>
@endpush
