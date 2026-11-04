@php
    $resultsCollection = collect($testResults ?? []);

    $passedCount = $resultsCollection->filter(function ($test) {
        $storedResult = strtoupper((string) ($test->result ?? ''));

        return $storedResult === 'PASS'
            || ($storedResult === '' && $test->is_within_tolerance === true);
    })->count();

    $failedCount = $resultsCollection->filter(function ($test) {
        $storedResult = strtoupper((string) ($test->result ?? ''));

        return $storedResult === 'FAIL'
            || ($storedResult === '' && $test->is_within_tolerance === false);
    })->count();

    $avgUncertainty = $resultsCollection
        ->filter(fn ($test) => $test->uncertainty !== null)
        ->avg('uncertainty');
@endphp

<div class="row g-3 mb-4 test-result-summary">
    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-primary text-white h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold opacity-75">Total Tests</small>
                        <h3 class="mb-0">{{ $resultsCollection->count() }}</h3>
                    </div>
                    <i class="fas fa-flask fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-success text-white h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold opacity-75">Passed</small>
                        <h3 class="mb-0">{{ $passedCount }}</h3>
                    </div>
                    <i class="fas fa-circle-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-danger text-white h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold opacity-75">Failed</small>
                        <h3 class="mb-0">{{ $failedCount }}</h3>
                    </div>
                    <i class="fas fa-circle-xmark fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-info text-white h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold opacity-75">Avg Uncertainty</small>
                        <h3 class="mb-0 fs-5">
                            {{ $avgUncertainty !== null
                                ? number_format((float) $avgUncertainty, 6) . '%'
                                : 'N/A' }}
                        </h3>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
