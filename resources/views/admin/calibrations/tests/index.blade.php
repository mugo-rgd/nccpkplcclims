@extends('layouts.app')

@section('title', 'Test Results - Calibration #' . $calibration->id)

@section('content')
@php
    $currentUser = auth()->user();
    $activeCalibrationStatuses = ['assigned', 'in_progress', 'testing'];
    $calibrationIsActive = in_array($calibration->status, $activeCalibrationStatuses, true);
    $isAssignedTechnician = $currentUser
        && (int) $calibration->performed_by === (int) $currentUser->id;
    $canPerformTesting = $currentUser
        && $currentUser->hasAnyRole(['technician', 'lab_technician'])
        && $currentUser->can('perform_calibrations')
        && $calibrationIsActive
        && $isAssignedTechnician;
    $canReview = $currentUser && $currentUser->hasAnyRole(['supervisor', 'checker']);
    $canApprove = $currentUser && $currentUser->hasRole('lab_manager');
    $canReturn = $currentUser && $currentUser->hasAnyRole(['supervisor', 'checker', 'lab_manager']);

    $equipmentItems = collect($equipmentItems ?? []);
    $allTestResults = $equipmentItems->flatMap(fn ($item) => $item->testResults ?? collect());
    $totalTests = $allTestResults->count();
    $passedTests = $allTestResults->where('result', 'PASS')->count();
    $failedTests = $allTestResults->where('result', 'FAIL')->count();
    $averageUncertainty = $allTestResults->whereNotNull('uncertainty')->avg('uncertainty');
    $approvedItems = $equipmentItems->where('review_status', 'approved')->count();

    $canAddTestResult = $currentUser
        && $currentUser->can('perform_calibrations')
        && in_array(
            (string) $calibration->status,
            [
                'assigned',
                'in_progress',
                'testing',
            ],
            true
        )
        && (int) $calibration->performed_by === (int) $currentUser->id;
@endphp

<div class="container-fluid test-results-index-page">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-flask text-primary me-2"></i>
                Test Results
                <span class="badge bg-secondary">Calibration #{{ $calibration->id }}</span>
            </h1>
            <p class="text-muted mb-0">
                {{ $calibration->customer?->company_name ?? $calibration->customer?->name ?? 'Customer not specified' }}
                &middot; {{ $equipmentItems->count() }} equipment item(s)
                &middot; {{ $approvedItems }} approved
            </p>
        </div>
        <a href="{{ route('calibrations.show', $calibration) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Calibration
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$calibrationIsActive)
        <div class="alert alert-info">
            <i class="fas fa-lock me-1"></i>
            Calibration status is <strong>{{ ucwords(str_replace('_', ' ', $calibration->status ?? 'unknown')) }}</strong>.
            Ordinary testing and resubmission are locked. Completed records require an authorized reopening process.
        </div>
    @elseif(!$calibration->performed_by)
        <div class="alert alert-warning">No technician is assigned to this calibration.</div>
    @elseif($currentUser && $currentUser->can('perform_calibrations') && !$isAssignedTechnician && !$canReview && !$canApprove)
        <div class="alert alert-info">
            Testing is assigned to {{ $calibration->performedBy?->name ?? 'another technician' }}.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card bg-primary text-white h-100"><div class="card-body">
                <div class="small">TOTAL TESTS</div><h2 class="mb-0">{{ $totalTests }}</h2>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-success text-white h-100"><div class="card-body">
                <div class="small">PASSED</div><h2 class="mb-0">{{ $passedTests }}</h2>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-danger text-white h-100"><div class="card-body">
                <div class="small">FAILED</div><h2 class="mb-0">{{ $failedTests }}</h2>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-info text-white h-100"><div class="card-body">
                <div class="small">AVG UNCERTAINTY</div>
                <h2 class="mb-0">{{ $averageUncertainty !== null ? number_format((float) $averageUncertainty, 6) : 'N/A' }}</h2>
            </div></div>
        </div>
    </div>

    @forelse($equipmentItems as $item)
        @php
            $itemResults = collect($item->testResults ?? [])->sortBy('test_order')->values();
            $conditionsRecorded = $item->hasEnvironmentalConditions();
            $reviewStatus = $item->review_status ?? 'testing';
            $equipmentLocked = in_array($item->status, ['completed', 'certified'], true);
            $itemCanTest = $canPerformTesting && $conditionsRecorded && !$equipmentLocked && $reviewStatus === 'testing';
            $canRecordConditions = $canPerformTesting && !$conditionsRecorded && !$equipmentLocked && $reviewStatus === 'testing';
            $canSubmitForReview = $itemCanTest && $itemResults->isNotEmpty() && !empty($item->test_report_path);
            $canReopen = $canPerformTesting && !$equipmentLocked && $reviewStatus === 'returned';
            $reviewerIsIndependent = $currentUser && (int) $item->testing_completed_by !== (int) $currentUser->id;
            $approverIsIndependent = $currentUser
                && (int) $item->testing_completed_by !== (int) $currentUser->id
                && (int) $item->reviewed_by !== (int) $currentUser->id;
            $canAcceptReview = $canReview && $reviewStatus === 'pending_review' && $reviewerIsIndependent;
            $canApproveItem = $canApprove && $reviewStatus === 'reviewed' && $approverIsIndependent;
            $canReturnItem = $canReturn
                && in_array($reviewStatus, ['pending_review', 'reviewed'], true)
                && $reviewerIsIndependent;
            $itemPassed = $itemResults->where('result', 'PASS')->count();
            $itemFailed = $itemResults->where('result', 'FAIL')->count();
            $statusClass = match ($reviewStatus) {
                'approved' => 'bg-success',
                'reviewed' => 'bg-info text-dark',
                'pending_review' => 'bg-primary',
                'returned', 'rejected' => 'bg-danger',
                default => 'bg-secondary',
            };
        @endphp

        <div class="card shadow-sm mb-4 equipment-test-card" id="equipment-{{ $item->id }}">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-microscope text-primary me-2"></i>
                            {{ $item->equipment_name ?? 'Equipment' }}
                        </h5>
                        <div class="text-muted small">
                            Serial: <strong>{{ $item->serial_number ?? 'N/A' }}</strong>
                            &middot; Equipment item #{{ $item->id }}
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge {{ $statusClass }}">
                                Review: {{ ucwords(str_replace('_', ' ', $reviewStatus)) }}
                            </span>
                            <span class="badge {{ $conditionsRecorded ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $conditionsRecorded ? 'Conditions recorded' : 'Conditions required' }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-start">
                        @if($canRecordConditions)
                            <a href="{{ route('calibrations.items.environment.edit', ['calibration' => $calibration->id, 'item' => $item->id]) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-thermometer-half me-1"></i> Record Conditions
                            </a>
                        @endif
                        @if($itemCanTest)
                            <a href="{{ route('calibrations.tests.create', ['calibration' => $calibration->id, 'calibration_item_id' => $item->id]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Test Result
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">Equipment status</small>
                        <strong>{{ ucwords(str_replace('_', ' ', $item->status ?? 'Not set')) }}</strong>
                    </div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">Recorded tests</small>
                        <strong>{{ $itemResults->count() }}</strong>
                        <span class="text-muted small">({{ $itemPassed }} passed, {{ $itemFailed }} failed)</span>
                    </div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">Environmental conditions</small>
                        <strong>{{ $conditionsRecorded ? 'Complete' : 'Pending' }}</strong>
                    </div></div>
                </div>

                @if($conditionsRecorded)
                    <div class="alert alert-light border">
                        <strong><i class="fas fa-thermometer-half me-1"></i> Environmental Conditions</strong>
                        <div class="row g-2 mt-2 small">
                            <div class="col-md-4">Temperature: {{ $item->temperature_start }} – {{ $item->temperature_end }} °C</div>
                            <div class="col-md-4">Humidity: {{ $item->humidity_start }} – {{ $item->humidity_end }} %</div>
                            <div class="col-md-4">Supply: {{ $item->supply_voltage }} V / {{ $item->supply_frequency }} Hz</div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">Environmental conditions must be recorded for this equipment before testing.</div>
                @endif

                @if($itemFailed > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This equipment has {{ $itemFailed }} failed test(s). Review the results and applicable acceptance criteria before approval.
                    </div>
                @endif

                @if($item->review_comments)
                    <div class="alert alert-secondary"><strong>Review / correction comments:</strong> {{ $item->review_comments }}</div>
                @endif
                @if($item->approval_comments)
                    <div class="alert alert-light border"><strong>Approval comments:</strong> {{ $item->approval_comments }}</div>
                @endif

                @include('admin.calibrations.tests.partials.table', [
                    'calibration' => $calibration,
                    'testResults' => $itemResults,
                    'tableTitle' => 'Equipment Test Results',
                    'showAllActions' => $itemCanTest,
                ])

                <div class="d-flex flex-wrap align-items-start gap-2 mt-3 review-actions">
                    @if($canSubmitForReview)
                        <form method="POST" action="{{ route('calibrations.items.submit-testing', ['calibration' => $calibration->id, 'item' => $item->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm"
                                    onclick="return confirm('Submit this equipment and its test results for independent technical review?')">
                                <i class="fas fa-paper-plane me-1"></i> Submit for Review
                            </button>
                        </form>
                    @endif

                    @if($canAcceptReview)
                        <form method="POST" class="review-form" action="{{ route('calibrations.items.review-testing', ['calibration' => $calibration->id, 'item' => $item->id]) }}">
                            @csrf
                            <label for="review-comments-{{ $item->id }}" class="form-label small mb-1">Technical review comments</label>
                            <textarea id="review-comments-{{ $item->id }}" name="comments" rows="2" maxlength="5000"
                                      class="form-control form-control-sm mb-2" placeholder="Findings, including any failed tests"></textarea>
                            <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return confirm('Confirm that you have reviewed all test results for this equipment?')">
                                <i class="fas fa-check me-1"></i> Accept Technical Review
                            </button>
                        </form>
                    @endif

                    @if($canReturnItem)
                        <form method="POST" class="review-form" action="{{ route('calibrations.items.return-testing', ['calibration' => $calibration->id, 'item' => $item->id]) }}">
                            @csrf
                            <label for="return-comments-{{ $item->id }}" class="form-label small mb-1">Reason for correction <span class="text-danger">*</span></label>
                            <textarea id="return-comments-{{ $item->id }}" name="comments" rows="2" maxlength="5000"
                                      class="form-control form-control-sm mb-2" required placeholder="Describe the correction required"></textarea>
                            <button type="submit" class="btn btn-warning btn-sm"
                                    onclick="return confirm('Return this equipment for correction?')">
                                <i class="fas fa-undo me-1"></i> Return for Correction
                            </button>
                        </form>
                    @endif

                    @if($canReopen)
                        <form method="POST" action="{{ route('calibrations.items.reopen-testing', ['calibration' => $calibration->id, 'item' => $item->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm"
                                    onclick="return confirm('Reopen this equipment for correction?')">
                                <i class="fas fa-edit me-1"></i> Reopen Testing
                            </button>
                        </form>
                    @endif

                    @if($canApproveItem)
                        <form method="POST" class="review-form" action="{{ route('calibrations.items.approve-testing', ['calibration' => $calibration->id, 'item' => $item->id]) }}">
                            @csrf
                            <label for="approval-comments-{{ $item->id }}" class="form-label small mb-1">Lab manager approval comments</label>
                            <textarea id="approval-comments-{{ $item->id }}" name="comments" rows="2" maxlength="5000"
                                      class="form-control form-control-sm mb-2" placeholder="Approval rationale and disposition of any failed tests"></textarea>
                            <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return confirm('Approve these reviewed equipment results?')">
                                <i class="fas fa-check-double me-1"></i> Approve Equipment Results
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle me-2"></i>
            No equipment items have been registered for this calibration.
        </div>
    @endforelse
</div>


<div class="card border mb-4">
    <div class="card-header bg-light">
        <strong>
            <i class="fas fa-file-pdf text-danger me-2"></i>
            Test Machine Report
        </strong>
    </div>

    <div class="card-body">

        @if($item->test_report_path)
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i>
                Report attached:
                <strong>
                    {{ $item->test_report_original_name }}
                </strong>

                <div class="small mt-1">
                    Uploaded:
                    {{ $item->test_report_uploaded_at?->format('d M Y H:i') }}
                </div>
            </div>

            <a href="{{ route(
                'calibrations.items.test-report.download',
                [
                    'calibration' => $calibration->id,
                    'item' => $item->id,
                ]
            ) }}"
               class="btn btn-outline-primary btn-sm mb-3">
                <i class="fas fa-download me-1"></i>
                Download Test Report
            </a>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                No Test Machine Report attached.
            </div>
        @endif

       @if(
    $canAddTestResult
    && ($item->review_status ?? 'testing') === 'testing'
    && !in_array(
        (string) $item->status,
        ['completed', 'certified'],
        true
    )
)
            <form method="POST"
                  enctype="multipart/form-data"
                  action="{{ route(
                      'calibrations.items.test-report.store',
                      [
                          'calibration' => $calibration->id,
                          'item' => $item->id,
                      ]
                  ) }}">
                @csrf

                <label class="form-label fw-semibold">
                    Upload Test Machine Report (PDF)
                </label>

                <input type="file"
                       name="test_report"
                       class="form-control mb-2"
                       accept=".pdf,application/pdf"
                       required>

                <div class="form-text mb-3">
                    PDF only. Maximum file size: 10 MB.
                </div>

                <button type="submit"
                        class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i>
                    {{ $item->test_report_path
                        ? 'Replace Test Report'
                        : 'Upload Test Report' }}
                </button>
            </form>
        @endif

    </div>
</div>


{{-- Modals stay outside tables; each result is rendered once. --}}
@foreach($allTestResults as $result)
    @include('admin.calibrations.tests.partials.view-modal', [
        'calibration' => $calibration,
        'result' => $result,
    ])
@endforeach
@endsection

@push('styles')
<style>
    .test-results-index-page { padding-top: .5rem; padding-bottom: 2rem; }
    .equipment-test-card { border: 1px solid #e2e8f0; border-radius: 15px; overflow: hidden; }
    .equipment-test-card .card-header { border-bottom: 1px solid #e2e8f0; }
    .equipment-test-card .table th,
    .equipment-test-card .table td { vertical-align: middle; }
    .review-actions .review-form { width: min(100%, 320px); }
    @media (max-width: 767.98px) {
        .test-results-index-page h1 { font-size: 1.5rem; }
        .equipment-test-card .card-header .btn { width: 100%; }
        .review-actions, .review-actions form, .review-actions .btn { width: 100%; }
    }
</style>
@endpush
