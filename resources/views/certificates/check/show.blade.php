@extends('layouts.app')

@section('content')
@php
    $currentUser = auth()->user();

    $isSupervisor = $currentUser?->hasAnyRole([
        'supervisor',
        'super_admin',
    ]) ?? false;

    $isLabManager = $currentUser?->hasAnyRole([
        'lab_manager',
        'super_admin',
    ]) ?? false;

    $canCheckCertificate =
        $isSupervisor
        && in_array(
            $certificate->status,
            ['draft', 'pending', 'generated'],
            true
        )
        && (
            !$certificate->generated_by
            || (int) $certificate->generated_by !== (int) $currentUser?->id
        );

    $canApproveCertificate =
        $isLabManager
        && $certificate->status === 'checked'
        && (
            !$certificate->checked_by
            || (int) $certificate->checked_by !== (int) $currentUser?->id
        )
        && (
            !$certificate->generated_by
            || (int) $certificate->generated_by !== (int) $currentUser?->id
        );

    $canIssueCertificate =
        $isLabManager
        && $certificate->status === 'approved';

    $canSendCertificate =
        $isLabManager
        && $certificate->status === 'issued';
@endphp
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-certificate text-primary me-2"></i>Review Certificate</h1>
            <p class="text-muted">Certificate #: {{ $certificate->certificate_number }}</p>
        </div>
        <div class="col-md-4 text-right">
            @if($previous)
                <a href="{{ route('certificates.check.show', $previous) }}" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            @endif
            @if($next)
                <a href="{{ route('certificates.check.show', $next) }}" class="btn btn-secondary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Certificate Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Certificate Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Certificate Number:</strong> {{ $certificate->certificate_number }}</p>
                            <p><strong>Reference Number:</strong> {{ $certificate->reference_number ?? 'N/A' }}</p>
                            <p><strong>Status:</strong>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'generated' => 'info',
                                        'checked' => 'primary',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'sent' => 'success',
                                        'issued' => 'primary',
                                    ];
                                    $statusColor = $statusColors[$certificate->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ ucfirst($certificate->status) }}
                                </span>
                            </p>
                            <p><strong>Created:</strong> {{ $certificate->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Equipment Name:</strong> {{ $certificate->equipment_name ?? 'N/A' }}</p>
                            <p><strong>Serial Number:</strong> {{ $certificate->serial_number ?? 'N/A' }}</p>
                            <p><strong>Manufacturer:</strong> {{ $certificate->manufacturer ?? 'N/A' }}</p>
                            <p><strong>Model:</strong> {{ $certificate->model ?? 'N/A' }}</p>
                            <p><strong>Accuracy Class:</strong> {{ $certificate->accuracy_class ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Details -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Test Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Service Name:</strong> {{ $certificate->service_name ?? 'N/A' }}</p>
                            <p><strong>Service Method:</strong> {{ $certificate->service_method ?? 'N/A' }}</p>
                            <p>
                                <strong>Test Result:</strong>
                                @php
                                    $testResult = $certificate->test_result ?? 'N/A';
                                    $resultColor = 'secondary';

                                    if (strtoupper($testResult) == 'PASS' || strtoupper($testResult) == 'PASSED') {
                                        $resultColor = 'success';
                                    } elseif (strtoupper($testResult) == 'FAIL' || strtoupper($testResult) == 'FAILED') {
                                        $resultColor = 'danger';
                                    }
                                @endphp
                                <span class="badge bg-{{ $resultColor }}">
                                    {{ $testResult }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Voltage:</strong> {{ $certificate->voltage ?? 'N/A' }} V</p>
                            <p><strong>Current:</strong> {{ $certificate->current ?? 'N/A' }} A</p>
                            <p><strong>Temperature:</strong> {{ $certificate->temperature ?? 'N/A' }} °C</p>
                            <p><strong>Humidity:</strong> {{ $certificate->humidity ?? 'N/A' }} %</p>
                        </div>
                    </div>

                    @if($certificate->service_notes)
                        <div class="mt-2">
                            <strong>Service Notes:</strong>
                            <p class="text-muted">{{ $certificate->service_notes }}</p>
                        </div>
                    @endif

                    @if($certificate->comments)
                        <div class="mt-2">
                            <strong>Comments:</strong>
                            <p class="text-muted">{{ $certificate->comments }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TEST RESULTS FROM test_results TABLE         -->
            <!-- ============================================ -->
            @php
                // Get test results from the test_results table
                $testResults = collect();
                if (isset($calibration) && $calibration) {
                    // Load test results from the relationship
                    $testResults = $calibration->testResults()->orderBy('test_order')->get();
                }
            @endphp

            @if($testResults->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-flask text-warning me-1"></i>
                            Test Results
                            <span class="badge bg-secondary ms-2">{{ $testResults->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Test Details</th>
                                        <th>Nominal Value</th>
                                        <th>Measured Value</th>
                                        <th>Tolerance</th>
                                        <th>Error</th>
                                        <th>Result</th>
                                        <th>Uncertainty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($testResults as $index => $result)
                                        @php
                                            // Calculate error percentage
                                            $errorPercentage = null;
                                            if ($result->measured_value && $result->nominal_value && $result->nominal_value != 0) {
                                                $errorPercentage = (($result->measured_value - $result->nominal_value) / $result->nominal_value) * 100;
                                            }

                                            // Check if within tolerance
                                            $isWithinTolerance = $result->is_within_tolerance;
                                            if (!$isWithinTolerance && $result->tolerance_min && $result->tolerance_max && $result->measured_value) {
                                                $isWithinTolerance = $result->measured_value >= $result->tolerance_min && $result->measured_value <= $result->tolerance_max;
                                            }

                                            // Get uncertainty calculations
                                            $uncertaintyCalc = $result->uncertaintyCalculation;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $result->parameter ?? 'N/A' }}</strong>
                                                @if($result->test_name)
                                                    <br><small class="text-muted">{{ $result->test_name }}</small>
                                                @endif
                                                @if($result->test_type)
                                                    <br><small class="text-muted">Type: {{ ucfirst($result->test_type) }}</small>
                                                @endif
                                                @if($result->applied_value)
                                                    <br><small class="text-muted">Applied: {{ $result->applied_value }}</small>
                                                @endif
                                                @if($result->power_factor)
                                                    <br><small class="text-muted">PF: {{ $result->power_factor }}</small>
                                                @endif
                                                @if($result->voltage || $result->frequency)
                                                    <br><small class="text-muted">
                                                        @if($result->voltage) {{ $result->voltage }}V @endif
                                                        @if($result->frequency) {{ $result->frequency }}Hz @endif
                                                    </small>
                                                @endif
                                                @if($result->notes)
                                                    <br><small class="text-muted"><i class="fas fa-comment"></i> {{ $result->notes }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ number_format($result->nominal_value, 4) }}
                                                <br><small class="text-muted">{{ $result->unit ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                {{ number_format($result->measured_value, 4) }}
                                                <br><small class="text-muted">{{ $result->unit ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @if($result->tolerance_min && $result->tolerance_max)
                                                    {{ number_format($result->tolerance_min, 4) }} -
                                                    {{ number_format($result->tolerance_max, 4) }}
                                                    <br><small class="text-muted">±{{ number_format($result->tolerance, 2) }}%</small>
                                                @else
                                                    ±{{ number_format($result->tolerance, 2) }}%
                                                @endif
                                            </td>
                                            <td>
                                                @if($errorPercentage !== null)
                                                    <span class="{{ abs($errorPercentage) > $result->tolerance ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($errorPercentage, 4) }}%
                                                    </span>
                                                    @if($result->error_value)
                                                        <br><small class="text-muted">Value: {{ number_format($result->error_value, 4) }}</small>
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($isWithinTolerance)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i> PASS
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i> FAIL
                                                    </span>
                                                @endif
                                                @if($result->result)
                                                    <br><small class="text-muted">{{ $result->result }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($uncertaintyCalc)
                                                    <span class="badge bg-info">
                                                        ±{{ number_format($uncertaintyCalc->expanded_uncertainty, 4) }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        Combined: {{ number_format($uncertaintyCalc->combined_uncertainty, 4) }}
                                                    </small>
                                                    @if($uncertaintyCalc->load_type)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-tag"></i> {{ ucfirst(str_replace('_', ' ', $uncertaintyCalc->load_type)) }}
                                                        </small>
                                                    @endif
                                                @elseif($result->uncertainty)
                                                    <span class="badge bg-info">
                                                        ±{{ number_format($result->uncertainty, 4) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- UNCERTAINTY CALCULATIONS DETAIL              -->
                <!-- ============================================ -->
                @php
                    $hasUncertaintyCalculations = $testResults->filter(function($result) {
                        return $result->uncertaintyCalculation;
                    })->count() > 0;
                @endphp

                @if($hasUncertaintyCalculations)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator text-primary me-1"></i>
                                Uncertainty Calculations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Test #</th>
                                            <th>Parameter</th>
                                            <th>Load Type</th>
                                            <th>Repeatability (u1)</th>
                                            <th>Reference Std (u2)</th>
                                            <th>Resolution (u3)</th>
                                            <th>Combined Uncertainty</th>
                                            <th>Expanded Uncertainty</th>
                                            <th>Confidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($testResults as $index => $result)
                                            @if($result->uncertaintyCalculation)
                                                @php
                                                    $uc = $result->uncertaintyCalculation;
                                                @endphp
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $result->parameter ?? 'N/A' }}</strong>
                                                        @if($result->test_name)
                                                            <br><small class="text-muted">{{ $result->test_name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            {{ ucfirst(str_replace('_', ' ', $uc->load_type ?? 'N/A')) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ number_format($uc->u1_repeatability ?? 0, 6) }}</td>
                                                    <td>{{ number_format($uc->u2_reference_std ?? 0, 6) }}</td>
                                                    <td>{{ number_format($uc->u3_resolution ?? 0, 6) }}</td>
                                                    <td>
                                                        <strong>{{ number_format($uc->combined_uncertainty ?? 0, 6) }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            ±{{ number_format($uc->expanded_uncertainty ?? 0, 6) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ number_format($uc->confidence_level ?? 95, 2) }}%</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ============================================ -->
                <!-- REGISTER TEST DETAILS (if applicable)       -->
                <!-- ============================================ -->
                @php
                    $hasRegisterTests = $testResults->filter(function($result) {
                        return $result->start_reading || $result->stop_reading || $result->energy_difference || $result->dial_error;
                    })->count() > 0;
                @endphp

                @if($hasRegisterTests)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tachometer-alt text-success me-1"></i>
                                Register Test Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Parameter</th>
                                            <th>Start Reading (kWh)</th>
                                            <th>Stop Reading (kWh)</th>
                                            <th>Energy Difference</th>
                                            <th>Dial Error (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($testResults as $index => $result)
                                            @if($result->start_reading || $result->stop_reading || $result->energy_difference || $result->dial_error)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $result->parameter ?? 'N/A' }}</strong>
                                                        @if($result->test_name)
                                                            <br><small class="text-muted">{{ $result->test_name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $result->start_reading ? number_format($result->start_reading, 2) : 'N/A' }}</td>
                                                    <td>{{ $result->stop_reading ? number_format($result->stop_reading, 2) : 'N/A' }}</td>
                                                    <td>{{ $result->energy_difference ? number_format($result->energy_difference, 2) : 'N/A' }}</td>
                                                    <td>
                                                        @if($result->dial_error !== null)
                                                            <span class="{{ abs($result->dial_error) > $result->tolerance ? 'text-danger' : 'text-success' }}">
                                                                {{ number_format($result->dial_error, 4) }}%
                                                            </span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ============================================ -->
                <!-- TRIAL DATA SUMMARY (FIXED)                  -->
                <!-- ============================================ -->
                @php
                    $hasTrialData = $testResults->filter(function($result) {
                        if (!$result->trial_data) {
                            return false;
                        }

                        // Check if trial_data is already an array or needs decoding
                        $trialData = $result->trial_data;
                        if (is_string($trialData)) {
                            $trialData = json_decode($trialData, true);
                        }

                        return is_array($trialData) && count($trialData) > 0;
                    })->count() > 0;
                @endphp

                @if($hasTrialData)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar text-info me-1"></i>
                                Trial Data Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($testResults as $index => $result)
                                @php
                                    // Safely get trial data
                                    $trialDataArray = [];
                                    if ($result->trial_data) {
                                        if (is_string($result->trial_data)) {
                                            $trialDataArray = json_decode($result->trial_data, true) ?? [];
                                        } elseif (is_array($result->trial_data)) {
                                            $trialDataArray = $result->trial_data;
                                        }
                                    }
                                @endphp
                                @if(count($trialDataArray) > 0)
                                    <div class="mb-3">
                                        <strong>Test #{{ $index + 1 }}: {{ $result->parameter ?? 'N/A' }}</strong>
                                        <div class="row mt-2">
                                            @foreach($trialDataArray as $trialIndex => $value)
                                                <div class="col-md-1 col-3 mb-1">
                                                    <span class="badge bg-secondary" style="font-size: 10px;">
                                                        T{{ $trialIndex + 1 }}: {{ number_format($value, 4) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @php
                                            $numericData = array_filter($trialDataArray, 'is_numeric');
                                            if (count($numericData) > 0) {
                                                $mean = array_sum($numericData) / count($numericData);
                                                $min = min($numericData);
                                                $max = max($numericData);
                                                $count = count($numericData);
                                                $stdDev = null;
                                                if (count($numericData) > 1) {
                                                    $variance = array_sum(array_map(function($x) use ($mean) {
                                                        return pow($x - $mean, 2);
                                                    }, $numericData)) / count($numericData);
                                                    $stdDev = sqrt($variance);
                                                }
                                            } else {
                                                $mean = $min = $max = $count = 0;
                                                $stdDev = null;
                                            }
                                        @endphp
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-chart-line"></i>
                                            Mean: {{ number_format($mean ?? 0, 4) }} |
                                            Min: {{ number_format($min ?? 0, 4) }} |
                                            Max: {{ number_format($max ?? 0, 4) }}
                                            @if(isset($stdDev) && $stdDev !== null)
                                                | Std Dev: {{ number_format($stdDev, 4) }}
                                            @endif
                                            | Count: {{ $count ?? 0 }}
                                        </div>
                                        <hr>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="card mt-3">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-flask fa-3x mb-3"></i>
                        <p>No test results available for this calibration.</p>
                    </div>
                </div>
            @endif

            <!-- ============================================ -->
            <!-- MACHINE DETAILS (if test report attached)   -->
            <!-- ============================================ -->
            @if($certificate->test_report_attachment_path)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-microchip text-info me-1"></i>
                            Test Machine Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="20%"><strong>Machine Used:</strong></td>
                                        <td>{{ $certificate->machine_used ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Machine Serial:</strong></td>
                                        <td>{{ $certificate->machine_serial ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Test Engineer:</strong></td>
                                        <td>{{ $certificate->test_engineer ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Test Date:</strong></td>
                                        <td>{{ $certificate->test_date ? \Carbon\Carbon::parse($certificate->test_date)->format('d M Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Equipment Class:</strong></td>
                                        <td>{{ $certificate->equipment_class ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Wiring:</strong></td>
                                        <td>{{ $certificate->wiring ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Equipment Constant:</strong></td>
                                        <td>{{ $certificate->equipment_constant ?? 'N/A' }}</td>
                                    </tr>
                                    @if($certificate->test_report_original_name)
                                        <tr>
                                            <td><strong>Report File:</strong></td>
                                            <td>
                                                <a href="{{ asset('storage/' . $certificate->test_report_attachment_path) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-pdf me-1"></i>
                                                    {{ $certificate->test_report_original_name }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Traceability Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Traceability & Conformity</h5>
                </div>
                <div class="card-body">
                    @if($certificate->traceability_information)
                        <div class="mb-3">
                            <strong>Traceability Information:</strong>
                            <p class="text-muted mt-1">{{ $certificate->traceability_information }}</p>
                        </div>
                    @endif
                    @if($certificate->statement_conformity)
                        <div>
                            <strong>Statement of Conformity:</strong>
                            <p class="text-muted mt-1">{{ $certificate->statement_conformity }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <!-- Calibration Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Calibration Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Calibration ID:</strong> {{ $calibration->id ?? 'N/A' }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $calibration->getStatusColorAttribute() ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $calibration->status ?? 'N/A')) }}
                        </span>
                    </p>
                    <p>
                        <strong>Customer:</strong>
                        {{
                            $certificate->customer?->company_name
                            ?? $certificate->customer?->name
                            ?? $calibration?->customer?->company_name
                            ?? $calibration?->customer?->name
                            ?? 'N/A'
                        }}
                    </p>
                    <p><strong>Generated By:</strong> {{ $certificate->generatedBy?->name ?? 'N/A' }}</p>
                    <p><strong>Generated At:</strong> {{ $certificate->generated_at ? \Carbon\Carbon::parse($certificate->generated_at)->format('Y-m-d H:i') : 'N/A' }}</p>
                    @if($certificate->checkedBy)
                        <p><strong>Checked By:</strong> {{ $certificate->checkedBy->name ?? 'N/A' }}</p>
                        <p><strong>Checked At:</strong> {{ $certificate->checked_at ? \Carbon\Carbon::parse($certificate->checked_at)->format('Y-m-d H:i') : 'N/A' }}</p>
                    @endif
                    @if($certificate->approvedBy)
                        <p><strong>Approved By:</strong> {{ $certificate->approvedBy->name ?? 'N/A' }}</p>
                        <p><strong>Approved At:</strong> {{ $certificate->approved_at ? \Carbon\Carbon::parse($certificate->approved_at)->format('Y-m-d H:i') : 'N/A' }}</p>
                    @endif
                    @if($certificate->issuedBy)
                        <p><strong>Issued By:</strong> {{ $certificate->issuedBy?->name ?? 'N/A' }}</p>
                        <p><strong>Issued Date:</strong> {{ $certificate->issued_date ? \Carbon\Carbon::parse($certificate->issued_date)->format('Y-m-d H:i') : 'N/A' }}</p>
                    @endif
                    @if($certificate->sentBy)
                        <p><strong>Sent By:</strong> {{ $certificate->sentBy->name ?? 'N/A' }}</p>
                        <p><strong>Sent At:</strong> {{ $certificate->sent_at ? \Carbon\Carbon::parse($certificate->sent_at)->format('Y-m-d H:i') : 'N/A' }}</p>
                    @endif
                </div>
            </div>

            <!-- Test Summary Stats -->
            @if($testResults->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Test Summary</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalTests = $testResults->count();
                            $passedTests = $testResults->where('is_within_tolerance', true)->count();
                            $failedTests = $totalTests - $passedTests;
                            $passRate = $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0;
                        @endphp
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-success">{{ $passedTests }}</h3>
                                <small class="text-muted">Passed</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-danger">{{ $failedTests }}</h3>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 20px;">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: {{ $passRate }}%;"
                                 aria-valuenow="{{ $passRate }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ number_format($passRate, 1) }}%
                            </div>
                        </div>
                        <div class="text-center text-muted small mt-1">
                            {{ $totalTests }} total tests
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- REVIEW ACTIONS                                             --}}
            {{-- ============================================================ --}}

            @if(in_array($certificate->status, ['draft', 'pending', 'generated', 'checked'], true))
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check me-1"></i>
                            Review Actions
                        </h5>
                    </div>

                    <div class="card-body">
                        {{-- Supervisor check --}}
                        @if($canCheckCertificate)
                            <form
                                method="POST"
                                action="{{ route('certificates.check.check', $certificate) }}"
                                class="mb-3"
                            >
                                @csrf

                                <div class="form-group mb-2">
                                    <label for="check_notes" class="form-label">
                                        Check Notes (Optional)
                                    </label>

                                    <textarea
                                        id="check_notes"
                                        name="check_notes"
                                        class="form-control"
                                        rows="2"
                                        maxlength="1000"
                                        placeholder="Add any notes about this check..."
                                    >{{ old('check_notes') }}</textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-block"
                                    onclick="return confirm('Confirm that you have reviewed and checked this certificate?')"
                                >
                                    <i class="fas fa-check-double me-1"></i>
                                    Check Certificate
                                </button>
                            </form>
                        @elseif(
                            $isSupervisor
                            && in_array($certificate->status, ['draft', 'pending', 'generated'], true)
                            && (int) $certificate->generated_by === (int) $currentUser?->id
                        )
                            <div class="alert alert-warning py-2 small">
                                <i class="fas fa-user-shield me-1"></i>
                                You generated this certificate, therefore you cannot check it.
                            </div>
                        @endif

                        {{-- Lab Manager approval --}}
                        @if($canApproveCertificate)
                            <form
                                method="POST"
                                action="{{ route('certificates.check.approve', $certificate) }}"
                                class="mb-3"
                            >
                                @csrf

                                <div class="form-group mb-2">
                                    <label for="approval_notes" class="form-label">
                                        Approval Notes (Optional)
                                    </label>

                                    <textarea
                                        id="approval_notes"
                                        name="approval_notes"
                                        class="form-control"
                                        rows="2"
                                        maxlength="1000"
                                        placeholder="Add any notes about this approval..."
                                    >{{ old('approval_notes') }}</textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-success btn-block"
                                    onclick="return confirm('Approve this checked certificate?')"
                                >
                                    <i class="fas fa-check-circle me-1"></i>
                                    Approve Certificate
                                </button>
                            </form>
                        @elseif($isLabManager && $certificate->status === 'checked')
                            @if((int) $certificate->checked_by === (int) $currentUser?->id)
                                <div class="alert alert-warning py-2 small">
                                    <i class="fas fa-user-shield me-1"></i>
                                    You checked this certificate, therefore another lab manager must approve it.
                                </div>
                            @elseif((int) $certificate->generated_by === (int) $currentUser?->id)
                                <div class="alert alert-warning py-2 small">
                                    <i class="fas fa-user-shield me-1"></i>
                                    You generated this certificate, therefore you cannot approve it.
                                </div>
                            @endif
                        @endif

                        {{-- Reject --}}
                        @if(in_array($certificate->status, ['draft', 'pending', 'generated', 'checked'], true))
                            <hr>

                            <form
                                method="POST"
                                action="{{ route('certificates.check.reject', $certificate) }}"
                                class="mb-3"
                            >
                                @csrf

                                <div class="form-group mb-2">
                                    <label for="rejection_reason" class="form-label">
                                        Rejection Reason
                                        <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        id="rejection_reason"
                                        name="rejection_reason"
                                        class="form-control"
                                        rows="3"
                                        minlength="10"
                                        maxlength="1000"
                                        placeholder="Provide a detailed reason for rejection..."
                                        required
                                    >{{ old('rejection_reason') }}</textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger btn-block"
                                    onclick="return confirm('Reject this certificate?')"
                                >
                                    <i class="fas fa-times-circle me-1"></i>
                                    Reject Certificate
                                </button>
                            </form>

                            {{-- Modification request --}}
                            <form
                                method="POST"
                                action="{{ route('certificates.check.modify', $certificate) }}"
                            >
                                @csrf

                                <div class="form-group mb-2">
                                    <label for="modification_notes" class="form-label">
                                        Modification Notes
                                        <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        id="modification_notes"
                                        name="modification_notes"
                                        class="form-control"
                                        rows="2"
                                        minlength="10"
                                        maxlength="1000"
                                        placeholder="Describe what must be corrected..."
                                        required
                                    >{{ old('modification_notes') }}</textarea>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="send_back_to" class="form-label">
                                        Send Back To
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        id="send_back_to"
                                        name="send_back_to"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">-- Select Technician --</option>

                                        @foreach($technicians ?? [] as $id => $name)
                                            <option
                                                value="{{ $id }}"
                                                @selected((string) old('send_back_to') === (string) $id)
                                            >
                                                {{ $name }}
                                                @if((int) $certificate->generated_by === (int) $id)
                                                    (Original Generator)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-warning btn-block"
                                >
                                    <i class="fas fa-edit me-1"></i>
                                    Request Modification
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- FINAL ACTIONS                                                --}}
            {{-- ============================================================ --}}

            @if($canIssueCertificate || $canSendCertificate)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-stamp me-1"></i>
                            Final Actions
                        </h5>
                    </div>

                    <div class="card-body">
                        @if($canIssueCertificate)
                            <form
                                method="POST"
                                action="{{ route('certificates.check.issue', $certificate) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-block"
                                    onclick="return confirm('Issue this approved certificate?')"
                                >
                                    <i class="fas fa-certificate me-1"></i>
                                    Issue Certificate
                                </button>
                            </form>
                        @endif

                        @if($canSendCertificate)
                            <form
                                method="POST"
                                action="{{ route('certificates.check.send', $certificate) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-info btn-block"
                                    onclick="return confirm('Confirm that this issued certificate should be marked as sent?')"
                                >
                                    <i class="fas fa-paper-plane me-1"></i>
                                    Mark as Sent
                                </button>
                            </form>

                            @if(Route::has('certificates.send-to-customer'))
                                <form
                                    method="POST"
                                    action="{{ route('certificates.send-to-customer', $certificate) }}"
                                    class="mt-2"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-success btn-block"
                                        onclick="return confirm('Email this issued certificate to the customer?')"
                                    >
                                        <i class="fas fa-envelope me-1"></i>
                                        Email to Customer
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- CURRENT PDF / DOWNLOADS                                      --}}
            {{-- ============================================================ --}}

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf me-1"></i>
                        Certificate PDF
                    </h5>
                </div>

                <div class="card-body">
                    <div class="alert alert-info small py-2">
                        <i class="fas fa-sync-alt me-1"></i>
                        The live preview is generated from the current database record, so checker and approver details are reflected immediately.
                    </div>

                    @if(Route::has('certificates.preview'))
                        <a
                            href="{{ route('certificates.preview', $certificate) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline-primary btn-block mb-2"
                        >
                            <i class="fas fa-eye me-1"></i>
                            View Current PDF Certificate
                        </a>
                    @elseif($certificate->pdf_path)
                        <a
                            href="{{ asset('storage/' . $certificate->pdf_path) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline-primary btn-block mb-2"
                        >
                            <i class="fas fa-eye me-1"></i>
                            View Stored PDF Certificate
                        </a>
                    @endif

                    @if(Route::has('certificates.download'))
                        <a
                            href="{{ route('certificates.download', ['certificate' => $certificate, 'force' => 1]) }}"
                            class="btn btn-outline-success btn-block mb-2"
                        >
                            <i class="fas fa-download me-1"></i>
                            Download Latest PDF
                        </a>
                    @endif

                    @if($certificate->test_report_attachment_path)
                        @if(Route::has('certificates.download-test-report'))
                            <a
                                href="{{ route('certificates.download-test-report', $certificate) }}"
                                class="btn btn-outline-secondary btn-block"
                            >
                                <i class="fas fa-file-alt me-1"></i>
                                Download Test Report
                            </a>
                        @else
                            <a
                                href="{{ asset('storage/' . $certificate->test_report_attachment_path) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-secondary btn-block"
                            >
                                <i class="fas fa-file-alt me-1"></i>
                                View Test Report
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Status Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-file text-secondary me-2"></i>
                            <strong>Created:</strong> {{ $certificate->created_at->format('Y-m-d H:i') }}
                        </li>
                        @if($certificate->generated_at)
                            <li class="mb-2">
                                <i class="fas fa-file-pdf text-info me-2"></i>
                                <strong>Generated:</strong> {{ \Carbon\Carbon::parse($certificate->generated_at)->format('Y-m-d H:i') }}
                            </li>
                        @endif
                        @if($certificate->checked_at)
                            <li class="mb-2">
                                <i class="fas fa-check-double text-primary me-2"></i>
                                <strong>Checked:</strong> {{ \Carbon\Carbon::parse($certificate->checked_at)->format('Y-m-d H:i') }}
                                @if($certificate->checkedBy)
                                    <br><small class="text-muted">by {{ $certificate->checkedBy->name }}</small>
                                @endif
                            </li>
                        @endif
                        @if($certificate->approved_at)
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Approved:</strong> {{ \Carbon\Carbon::parse($certificate->approved_at)->format('Y-m-d H:i') }}
                                @if($certificate->approvedBy)
                                    <br><small class="text-muted">by {{ $certificate->approvedBy->name }}</small>
                                @endif
                            </li>
                        @endif
                        @if($certificate->issued_date)
                            <li class="mb-2">
                                <i class="fas fa-certificate text-primary me-2"></i>
                                <strong>Issued:</strong>
                                {{ \Carbon\Carbon::parse($certificate->issued_date)->format('Y-m-d H:i') }}
                                @if($certificate->issuedBy)
                                    <br><small class="text-muted">by {{ $certificate->issuedBy->name }}</small>
                                @endif
                            </li>
                        @endif
                        @if($certificate->sent_at)
                            <li class="mb-2">
                                <i class="fas fa-paper-plane text-info me-2"></i>
                                <strong>Sent:</strong> {{ \Carbon\Carbon::parse($certificate->sent_at)->format('Y-m-d H:i') }}
                                @if($certificate->sentBy)
                                    <br><small class="text-muted">by {{ $certificate->sentBy->name }}</small>
                                @endif
                            </li>
                        @endif
                        @if($certificate->status == 'rejected')
                            <li class="mb-2">
                                <i class="fas fa-times-circle text-danger me-2"></i>
                                <strong>Rejected:</strong> {{ $certificate->updated_at->format('Y-m-d H:i') }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
    }
    .table td {
        vertical-align: middle;
    }
    .progress {
        border-radius: 10px;
    }
    .progress-bar {
        font-size: 0.75rem;
        line-height: 20px;
    }
    .btn-block {
        width: 100%;
    }
    .card-header .badge {
        font-size: 0.75rem;
    }
</style>
@endpush
