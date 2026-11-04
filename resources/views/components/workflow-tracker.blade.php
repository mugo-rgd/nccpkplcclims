@php
    $flowSteps = $calibration->getFlowSteps();
    $progress = $calibration->getProgressPercentage();
    $customerType = $calibration->customer && $calibration->customer->isKplc() ? 'Internal (KPLC)' : 'External';
    $statusColor = $calibration->getStatusColorAttribute();
@endphp

<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-project-diagram text-primary me-1"></i>
                Workflow Status
                <span class="badge bg-{{ $statusColor }} ms-2">
                    {{ $calibration->status_label }}
                </span>
            </h6>
            <div>
                <span class="badge bg-secondary">
                    <i class="fas fa-building me-1"></i>
                    {{ $customerType }}
                </span>
                <span class="badge bg-info ms-1">
                    <i class="fas fa-percent me-1"></i>
                    {{ $progress }}% Complete
                </span>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Progress Bar -->
        <div class="progress mb-3" style="height: 8px;">
            <div class="progress-bar bg-{{ $statusColor }} progress-bar-striped progress-bar-animated"
                 role="progressbar"
                 style="width: {{ $progress }}%;"
                 aria-valuenow="{{ $progress }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
        </div>

        <!-- Workflow Steps -->
        <div class="row">
            @foreach($flowSteps as $step)
                <div class="col text-center {{ $loop->last ? '' : 'position-relative' }}">
                    @if(!$loop->last)
                        <div class="position-absolute top-50 start-50 translate-middle-y"
                             style="left: 75% !important; width: 50%; height: 2px;
                                    background: {{ $step['is_completed'] ? '#198754' : '#dee2e6' }};">
                        </div>
                    @endif

                    <div class="position-relative" style="z-index: 1;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto
                                  {{ $step['is_active'] ? 'border border-3 border-primary' : '' }}"
                             style="width: 40px; height: 40px;
                                    background: {{ $step['is_completed'] ? '#198754' : ($step['is_active'] ? '#0d6efd' : '#e9ecef') }};">
                            @if($step['is_completed'])
                                <i class="fas fa-check text-white"></i>
                            @elseif($step['is_active'])
                                <i class="fas fa-spinner fa-spin text-white"></i>
                            @else
                                <span class="text-muted">{{ $loop->iteration }}</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            <small class="{{ $step['is_completed'] || $step['is_active'] ? 'fw-bold' : 'text-muted' }}">
                                {{ $step['label'] }}
                            </small>
                        </div>
                        @if($step['is_active'])
                            <span class="badge bg-primary mt-1" style="font-size: 8px;">Current</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Next Action -->
        @if($calibration->canTransitionToNext())
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Next Step:</strong>
                {{ ucfirst(str_replace('_', ' ', $calibration->getNextStatus())) }}
                @if($calibration->canBeAssigned() && auth()->user()->can('assign_calibrations'))
                    <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <i class="fas fa-user-check me-1"></i> Assign Now
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
