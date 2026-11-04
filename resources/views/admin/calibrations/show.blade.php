@extends('layouts.app')

@section('title', 'Calibration Details #' . $calibration->id)

@section('content')
@php
    $currentUser = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | Logged-in user and customer classification
    |--------------------------------------------------------------------------
    */
    $isCustomerUser = $currentUser?->hasRole('customer') ?? false;
    $isKplcStaffUser = !$isCustomerUser;
    $isKplcCustomer = $calibration->customer?->isKplc() ?? false;
    $isExternalCustomer = !$isKplcCustomer;

    /*
    |--------------------------------------------------------------------------
    | Display helpers
    |--------------------------------------------------------------------------
    */
    $statusClass = match ($calibration->status) {
        'completed', 'certified', 'paid' => 'success',
        'pending', 'pending_payment' => 'warning',
        'rejected' => 'danger',
        'in_progress', 'testing', 'quoted' => 'info',
        'assigned' => 'primary',
        'dispatched' => 'secondary',
        default => 'secondary',
    };

    $priorityClass = match ($calibration->priority) {
        'urgent' => 'danger',
        'high' => 'warning',
        default => 'secondary',
    };

    $paymentStatusClass = match ($calibration->payment_status) {
        'paid' => 'success',
        'partial' => 'info',
        'pending' => 'warning',
        'failed', 'overdue' => 'danger',
        'refunded' => 'secondary',
        default => 'secondary',
    };

    $equipmentConditionClass = match (
        strtolower((string) $calibration->equipment_condition)
    ) {
        'excellent', 'good' => 'success',
        'fair' => 'warning',
        'poor', 'damaged' => 'danger',
        default => 'secondary',
    };

    /*
    |--------------------------------------------------------------------------
    | Related data
    |--------------------------------------------------------------------------
    */
    $testResults = collect($calibration->testResults ?? []);
    $recentTestResults = $testResults
        ->sortByDesc('id')
        ->take(5)
        ->values();

    $latestCertificate = collect($calibration->certificates ?? [])
        ->sortByDesc('id')
        ->first()
        ?? $calibration->certificate;

    $serviceTypes = collect($serviceTypes ?? []);
    $equipmentType = $calibration->equipmentType
        ?? ($equipmentType ?? null);

    $testParameters = collect($calibration->test_parameters ?? [])
        ->filter(
            fn ($parameter) =>
                !empty($parameter['name'])
                || !empty($parameter['nominal_value'])
        );

    /*
    |--------------------------------------------------------------------------
    | Quotation and payment state
    |--------------------------------------------------------------------------
    |
    | External workflow:
    | pending -> quoted -> pending_payment -> paid -> assigned -> testing
    | -> completed -> certified
    |
    | Internal KPLC workflow:
    | pending -> quoted -> assigned -> testing -> completed -> certified
    |
    */
    $quotation = $calibration->quotation
        ?? ($quote ?? null);

    $quotationGenerated = $quotation !== null;

    $customerEmail = trim(
        (string) ($calibration->customer?->email ?? '')
    );

    $quotationAmount = (float) (
        $quotation?->total_amount
        ?? $calibration->amount_due
        ?? 0
    );

    $amountPaid = (float) (
        $quotation?->amount_paid
        ?? $calibration->amount_paid
        ?? 0
    );

    $balance = max(
        0,
        $quotationAmount - $amountPaid
    );

    $quotationPaid = $isKplcCustomer
        || in_array(
            (string) $calibration->payment_status,
            ['paid', 'completed'],
            true
        )
        || in_array(
            (string) ($quotation?->mpesa_payment_status ?? ''),
            ['paid', 'completed'],
            true
        )
        || (
            $quotationAmount > 0
            && $amountPaid >= $quotationAmount
        );

    $technicianAssigned = filled($calibration->performed_by);

    /*
    |--------------------------------------------------------------------------
    | Action permissions
    |--------------------------------------------------------------------------
    */
    $canGenerateQuotation = $isKplcStaffUser
        && !$quotationGenerated
        && in_array(
            (string) $calibration->status,
            ['pending', 'requested'],
            true
        )
        && $currentUser?->can('create_calibrations');

    $canViewQuotation = $quotationGenerated
        && ($isKplcStaffUser || $isCustomerUser);

    /* External customers receive quotations. Internal KPLC work does not need
       a Send-to-Customer stage. */
    $canSendQuotation = $isKplcStaffUser
        && $isExternalCustomer
        && $quotationGenerated
        && $customerEmail !== ''
        && !$quotationPaid
        && in_array(
            (string) $calibration->status,
            ['quoted', 'pending_payment'],
            true
        );

    $canMarkPayment = $isKplcStaffUser
        && $isExternalCustomer
        && $quotationGenerated
        && !$quotationPaid
        && $balance > 0
        && in_array(
            (string) $calibration->status,
            ['quoted', 'pending_payment'],
            true
        );

    /* Internal: assign immediately after quotation.
       External: assign only after full payment. */
    $assignmentGateOpen = $quotationGenerated
        && (
            $isKplcCustomer
            || $quotationPaid
        );

    $canAssignTechnician = $isKplcStaffUser
        && $assignmentGateOpen
        && $currentUser?->can('assign_calibrations');

    $canOpenAssignmentModal = $canAssignTechnician;

    $canAddTestResult = $isKplcStaffUser
        && $currentUser?->can('perform_calibrations')
        && in_array(
            (string) $calibration->status,
            ['assigned', 'in_progress', 'testing'],
            true
        )
        && (int) $calibration->performed_by === (int) $currentUser?->id;

        /*
|--------------------------------------------------------------------------
| Document accessibility
|--------------------------------------------------------------------------
|
| Documents become available when their workflow milestone is reached
| and remain accessible for later workflow statuses.
|
| Request Form:
|   pending onwards
|
| Delivery Note:
|   pending onwards
|
| Quotation:
|   quoted onwards
|
| Job Card:
|   assigned onwards
|
| Collection Note:
|   completed onwards
|--------------------------------------------------------------------------
*/

$currentStatus = (string) $calibration->status;

/*
|--------------------------------------------------------------------------
| Request Form
|--------------------------------------------------------------------------
|
| Available from pending and remains available afterwards.
*/

$canAccessRequestForm = in_array(
    $currentStatus,
    [
        'pending',
        'quoted',
        'pending_payment',
        'paid',
        'assigned',
        'in_progress',
        'testing',
        'completed',
        'certified',
    ],
    true
);

/*
|--------------------------------------------------------------------------
| Delivery Note
|--------------------------------------------------------------------------
|
| Available from pending and remains available afterwards.
| Not applicable to on-site calibrations.
*/

$canAccessDeliveryNote =
    $isKplcStaffUser
    && $calibration->calibration_location !== 'on_site'
    && in_array(
        $currentStatus,
        [
            'pending',
            'quoted',
            'pending_payment',
            'paid',
            'assigned',
            'in_progress',
            'testing',
            'completed',
            'certified',
        ],
        true
    );

/*
|--------------------------------------------------------------------------
| Quotation
|--------------------------------------------------------------------------
|
| Must actually exist.
| Once generated at quoted stage, it remains accessible thereafter.
*/

$canAccessQuotation =
    $quotationGenerated
    && in_array(
        $currentStatus,
        [
            'quoted',
            'pending_payment',
            'paid',
            'assigned',
            'in_progress',
            'testing',
            'completed',
            'certified',
        ],
        true
    );

/*
|--------------------------------------------------------------------------
| Job Card
|--------------------------------------------------------------------------
|
| Available once technician assignment has occurred.
| Remains available throughout testing/completion/certification.
*/

$canAccessJobCard =
    $isKplcStaffUser
    && $technicianAssigned
    && in_array(
        $currentStatus,
        [
            'assigned',
            'in_progress',
            'testing',
            'completed',
            'certified',
        ],
        true
    );

/*
|--------------------------------------------------------------------------
| Collection Note
|--------------------------------------------------------------------------
|
| Available once the calibration is completed.
| Remains available after certification.
| Not applicable to on-site work.
*/

$canAccessCollectionNote =
    $isKplcStaffUser
    && $calibration->calibration_location !== 'on_site'
    && in_array(
        $currentStatus,
        [
            'completed',
            'certified',
        ],
        true
    );
@endphp

@if(session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="container-fluid px-0 calibration-show-page">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-microscope text-primary me-1"></i>
                        Calibration Details #{{ $calibration->id }}
                    </h2>
                    <p class="text-muted mb-0">
                        Created {{ optional($calibration->created_at)->format('Y-m-d H:i') ?? 'N/A' }}
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-{{ $statusClass }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $calibration->status ?? 'N/A')) }}
                    </span>

                    @if($calibration->priority && $calibration->priority !== 'normal')
                        <span class="badge bg-{{ $priorityClass }} fs-6">
                            {{ ucfirst($calibration->priority) }} priority
                        </span>
                    @endif

                    <span class="badge bg-{{ $isKplcCustomer ? 'primary' : 'secondary' }} fs-6">
                        {{ $isKplcCustomer ? 'Internal KPLC' : 'External Customer' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('components.workflow-tracker', ['calibration' => $calibration])

            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <section class="detail-section h-100">
                        <h4><i class="fas fa-building text-primary me-1"></i> Customer Information</h4>
                        <div class="detail-grid">
                            <div><small>Company</small><strong>{{ $calibration->customer?->company_name ?? 'N/A' }}</strong></div>
                            <div><small>Contact</small><strong>{{ $calibration->customer?->contact_person ?? 'N/A' }}</strong></div>
                            <div><small>Phone</small><strong>{{ $calibration->customer?->phone ?? 'N/A' }}</strong></div>
                            <div><small>Email</small><strong>{{ $calibration->customer?->email ?? 'N/A' }}</strong></div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-6">
                    <section class="detail-section h-100">
                        <h4><i class="fas fa-clipboard-list text-success me-1"></i> Calibration Summary</h4>
                        <div class="detail-grid">
                            <div><small>Location</small><strong>{{ ucfirst(str_replace('_', ' ', $calibration->calibration_location ?? 'N/A')) }}</strong></div>
                            <div><small>Priority</small><strong>{{ ucfirst($calibration->priority ?? 'Normal') }}</strong></div>
                            <div><small>Assigned Technician</small><strong>{{ $calibration->performedBy?->name ?? 'Not assigned' }}</strong></div>
                            <div><small>Recorded Tests</small><strong>{{ $testResults->count() }}</strong></div>
                        </div>
                    </section>
                </div>


{{-- CUSTOMER EQUIPMENT --}}
<div class="col-12">
    <section
        id="customer-equipment-section"
        class="detail-section border-start border-warning border-4"
    >

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="mb-0">
                <i class="fas fa-tools text-warning me-2"></i>
                Customer Equipment
            </h4>

            <span class="badge bg-primary">
                {{ $calibration->items->count() }} Equipment
            </span>
        </div>

        @if($calibration->items->isNotEmpty())

            @php
                $equipmentItems = $calibration->items;

                $allServiceIds = $equipmentItems
                    ->flatMap(function ($item) {
                        $ids = $item->service_types;

                        if (is_string($ids)) {
                            $ids = json_decode($ids, true);
                        }

                        return is_array($ids) ? $ids : [];
                    })
                    ->unique()
                    ->values();

                $serviceLookup = \App\Models\KplcServiceType::query()
                    ->whereIn('id', $allServiceIds)
                    ->get()
                    ->keyBy('id');
            @endphp

            <div class="table-responsive">
                <table class="table table-hover align-middle customer-equipment-table mb-0">

                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Equipment Name</th>
                            <th scope="col">Serial Number</th>
                            <th scope="col">Manufacturer</th>
                            <th scope="col">Model</th>
                            <th scope="col">Services Requested</th>
                            <th scope="col">Condition</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($equipmentItems as $index => $item)

                            @php
                                $serviceIds = $item->service_types;

                                if (is_string($serviceIds)) {
                                    $serviceIds = json_decode($serviceIds, true);
                                }

                                $serviceIds = is_array($serviceIds)
                                    ? $serviceIds
                                    : [];

                                $itemServices = collect($serviceIds)
                                    ->map(fn ($id) => $serviceLookup->get($id))
                                    ->filter();

                                $condition = strtolower(
                                    (string) ($item->equipment_condition ?? '')
                                );

                                $conditionClass = match ($condition) {
                                    'excellent', 'good' => 'success',
                                    'fair' => 'warning',
                                    'poor', 'damaged' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp

                            <tr>
                                {{-- NUMBER --}}
                                <td class="text-muted">
                                    {{ $index + 1 }}
                                </td>

                                {{-- EQUIPMENT NAME --}}
                                <td>
                                    <strong class="equipment-name">
                                        {{ $item->equipment_name ?: 'N/A' }}
                                    </strong>
                                </td>

                                {{-- SERIAL NUMBER --}}
                                <td>
                                    <span class="fw-semibold">
                                        {{ $item->serial_number ?: 'N/A' }}
                                    </span>
                                </td>

                                {{-- MANUFACTURER --}}
                                <td>
                                    {{ $item->manufacturer ?: 'N/A' }}
                                </td>

                                {{-- MODEL --}}
                                <td>
                                    {{ $item->model ?: 'N/A' }}
                                </td>

                                {{-- SERVICES REQUESTED --}}
                                <td>
                                    @if($itemServices->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($itemServices as $service)
                                                <span
                                                    class="badge bg-info text-dark"
                                                    title="{{ $service->serv_name }}"
                                                >
                                                    {{ $service->serv_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            No services recorded
                                        </span>
                                    @endif
                                </td>

                                {{-- CONDITION --}}
                                <td>
                                    <span class="badge bg-{{ $conditionClass }}">
                                        {{ ucfirst($item->equipment_condition ?: 'N/A') }}
                                    </span>
                                </td>

                                {{-- ACTIONS: per-equipment tests and certificates --}}
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-1">
                                        @if($canAddTestResult)
                                            <a href="{{ route('calibrations.tests.create', [
                                                'calibration' => $calibration,
                                                'equipment_id' => $item->id
                                            ]) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus me-1"></i> Add Test
                                            </a>
                                        @endif
                                        @include('admin.calibrations.partials.equipment-certificate-actions', [
                                            'item' => $item,
                                            'calibration' => $calibration,
                                            'isKplcStaffUser' => $isKplcStaffUser,
                                        ])
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                No customer equipment has been recorded.
            </div>
        @endif

    </section>
</div>



                @if($serviceTypes->isNotEmpty())
                    <div class="col-12">
                        <section class="detail-section">
                            <h4><i class="fas fa-cogs text-info me-1"></i> Service Types</h4>
                            <div class="row g-2">
                                @foreach($serviceTypes as $service)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded p-3 h-100">
                                            <span class="badge bg-primary me-1">{{ $service->serv_code }}</span>
                                            <strong>{{ $service->serv_name }}</strong>
                                            <small class="d-block text-muted mt-1">{{ $service->serv_method ?? '' }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>
                @endif

                @if($calibration->equipment)
                    <div class="col-12">
                        <section class="detail-section border-start border-primary border-4">
                            <h4><i class="fas fa-microchip text-primary me-1"></i> Laboratory Equipment Used</h4>
                            <div class="detail-grid detail-grid-four">
                                <div><small>Name</small><strong>{{ $calibration->equipment->name ?? 'N/A' }}</strong></div>
                                <div><small>Serial Number</small><strong>{{ $calibration->equipment->serial_number ?? 'N/A' }}</strong></div>
                                <div><small>Model</small><strong>{{ $calibration->equipment->model ?? 'N/A' }}</strong></div>
                                <div><small>Manufacturer</small><strong>{{ $calibration->equipment->manufacturer?->name ?? 'N/A' }}</strong></div>
                                <div><small>Status</small><strong>{{ ucfirst(str_replace('_', ' ', $calibration->equipment->status ?? 'N/A')) }}</strong></div>
                                <div><small>Condition</small><strong>{{ ucfirst(str_replace('_', ' ', $calibration->equipment->condition ?? 'N/A')) }}</strong></div>
                                <div><small>Calibration Laboratory</small><strong>{{ $calibration->equipment->calibration_laboratory ?? 'N/A' }}</strong></div>
                                <div><small>Next Calibration Due</small><strong>{{ $calibration->equipment->next_calibration_date ? \Carbon\Carbon::parse($calibration->equipment->next_calibration_date)->format('Y-m-d') : 'N/A' }}</strong></div>
                            </div>
                        </section>
                    </div>
                @endif

                <div class="col-12">
                    <section class="detail-section">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h4 class="mb-0"><i class="fas fa-flask text-danger me-1"></i> Test Results</h4>
                                                        <div class="d-flex gap-2">
                                @if($canAddTestResult)
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('customer-equipment-section').scrollIntoView({behavior: 'smooth'});">
                                        <i class="fas fa-arrow-up me-1"></i> Select Equipment to Add Test
                                    </button>
                                @endif
                                <a href="{{ route('calibrations.tests.index', $calibration) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-list me-1"></i> View All Tests
                                </a>
                            </div>
                        </div>

                        @include('admin.calibrations.tests.partials.summary', [
                            'testResults' => $testResults,
                        ])

                        @include('admin.calibrations.tests.partials.table', [
                            'calibration' => $calibration,
                            'testResults' => $recentTestResults,
                            'tableTitle' => 'Recent Test Results',
                            'showAllActions' => false,
                        ])

                        {{-- @include(
    'admin.calibrations.tests.partials.result-fields',
    [
        'result' => $result,
        'compact' => true,
    ]
) --}}

                        @if($testResults->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('calibrations.tests.index', $calibration) }}" class="btn btn-outline-primary btn-sm">
                                    View All {{ $testResults->count() }} Tests
                                </a>
                            </div>
                        @endif
                    </section>
                </div>

                @if($calibration->calibration_location === 'on_site')
                    <div class="col-12">
                        <section class="detail-section border-start border-warning border-4">
                            <h4><i class="fas fa-truck text-warning me-1"></i> On-Site Costs</h4>
                            @php
                                $totalOnsite = (float) ($calibration->per_diem_cost ?? 0)
                                    + (float) ($calibration->transport_cost ?? 0)
                                    + (float) ($calibration->other_costs ?? 0);
                            @endphp
                            <div class="detail-grid detail-grid-four">
                                <div><small>Staff</small><strong>{{ $calibration->number_of_staff ?? 1 }}</strong></div>
                                <div><small>Travel Days</small><strong>{{ $calibration->travel_days ?? 1 }}</strong></div>
                                <div><small>Distance</small><strong>{{ $calibration->site_distance_km ?? 0 }} km</strong></div>
                                <div><small>Total Cost</small><strong>KSh {{ number_format($totalOnsite, 2) }}</strong></div>
                            </div>
                        </section>
                    </div>
                @endif

                <div class="col-12">
                    <section class="detail-section">
                        <h4>
                            <i class="fas fa-sliders text-primary me-1"></i>
                            Actions
                        </h4>

                        <div class="d-flex flex-wrap gap-2">
                            <a
                                href="{{ route('calibrations.index') }}"
                                class="btn btn-secondary"
                            >
                                <i class="fas fa-arrow-left me-1"></i>
                                Back
                            </a>

                            @if($isKplcStaffUser)
                                @can('edit_calibrations')
                                    <a
                                        href="{{ route('calibrations.edit', $calibration) }}"
                                        class="btn btn-warning"
                                    >
                                        <i class="fas fa-edit me-1"></i>
                                        Edit
                                    </a>
                                @endcan
                            @endif

                            {{-- Step 1: Generate quotation for both internal and external work. --}}
                            @if($canGenerateQuotation)
                                <a
                                    href="{{ route('calibrations.quotation-form', $calibration) }}"
                                    class="btn btn-success"
                                >
                                    <i class="fas fa-file-invoice me-1"></i>
                                    {{ $isKplcCustomer ? 'Generate Internal Quotation' : 'Generate Quotation' }}
                                </a>
                            @endif

                            @if($canViewQuotation)
                                <a
                                    href="{{ route('calibrations.quotation', $calibration) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-file-pdf me-1"></i>
                                    {{ $isCustomerUser ? 'View My Quotation' : 'View Quotation' }}
                                </a>
                            @endif

                            {{-- External workflow only: quotation is sent to customer. --}}
                            @if($canSendQuotation)
                                <form
                                    action="{{ route('calibrations.send-quotation', $calibration) }}"
                                    method="POST"
                                    class="d-inline"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        onclick="return confirm('Send quotation to {{ addslashes($customerEmail) }}?')"
                                    >
                                        <i class="fas fa-paper-plane me-1"></i>
                                        Send to Customer
                                    </button>
                                </form>
                            @elseif(
                                $isKplcStaffUser
                                && $isExternalCustomer
                                && $quotationGenerated
                                && $customerEmail === ''
                            )
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    disabled
                                    title="The customer email address is missing."
                                >
                                    <i class="fas fa-paper-plane me-1"></i>
                                    Send to Customer
                                </button>
                            @endif

                            {{-- External workflow only: manual payment fallback. --}}
                            @if($canMarkPayment)
                                <button
                                    type="button"
                                    class="btn btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#paymentModal"
                                >
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Mark Payment Received
                                </button>
                            @endif

                            {{-- Internal: available after quotation.
                                 External: available only after full payment. --}}
                            @if($canAssignTechnician)
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignModal{{ $calibration->id }}"
                                >
                                    <i class="fas fa-user-check me-1"></i>
                                    {{ $technicianAssigned ? 'Reassign Technician' : 'Assign Technician' }}
                                </button>
                                 @elseif(
                                $isKplcStaffUser
                                && !$technicianAssigned
                                && $quotationGenerated
                                && $isExternalCustomer
                                && !$quotationPaid
                                )
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    disabled
                                    title="Full payment is required before assigning a technician."
                                >
                                    <i class="fas fa-lock me-1"></i>
                                    Assign Technician
                                </button>
                            @endif

                            @if($canAddTestResult)
                                <a
                                    href="{{ route('calibrations.tests.create', $calibration) }}"
                                    class="btn btn-primary"
                                >
                                    <i class="fas fa-flask me-1"></i>
                                    Add Test Result
                                </a>
                            @endif

                            @if($isKplcStaffUser)
                                @can('perform_calibrations')
                                    @if(
                                        $calibration->status === 'testing'
                                        && (int) $calibration->performed_by === (int) auth()->id()
                                    )
                                        <form
                                            action="{{ route('calibrations.complete', $calibration) }}"
                                            method="POST"
                                            class="d-inline"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-success"
                                                onclick="return confirm('Complete this calibration?')"
                                            >
                                                <i class="fas fa-check-circle me-1"></i>
                                                Complete Calibration
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            @endif

                            {{-- Certificates are issued per equipment item in the table above. --}}
                            @if(in_array((string) $calibration->status, ['completed', 'certified'], true))
                                <a href="#customer-equipment-section" class="btn btn-outline-primary">
                                    <i class="fas fa-certificate me-1"></i> Equipment Certificates
                                </a>
                            @endif

                            {{-- ===================================================== --}}
{{-- DOCUMENTS DROPDOWN                                    --}}
{{-- ===================================================== --}}

<div class="btn-group" role="group">

    <button
        type="button"
        class="btn btn-info dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >
        <i class="fas fa-file-pdf me-1"></i>
        Documents
    </button>

    <ul class="dropdown-menu dropdown-menu-end">

        {{-- ============================================= --}}
        {{-- REQUEST FORM                                  --}}
        {{-- Pending onwards                               --}}
        {{-- ============================================= --}}

        @if($canAccessRequestForm)
            <li>
                <a
                    class="dropdown-item"
                    href="{{ route(
                        'calibrations.request-form',
                        $calibration
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    Request Form
                </a>
            </li>
        @endif


        {{-- ============================================= --}}
        {{-- DELIVERY NOTE                                 --}}
        {{-- Pending onwards                               --}}
        {{-- KPLC staff only                               --}}
        {{-- ============================================= --}}

        @if($canAccessDeliveryNote)
            <li>
                <a
                    class="dropdown-item"
                    href="{{ route(
                        'calibrations.delivery-note',
                        $calibration
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fas fa-truck-loading me-2 text-warning"></i>
                    Delivery Note
                </a>
            </li>
        @endif


        {{-- ============================================= --}}
        {{-- QUOTATION                                     --}}
        {{-- Quoted onwards                                --}}
        {{-- ============================================= --}}

        @if($canAccessQuotation)
            <li>
                <a
                    class="dropdown-item"
                    href="{{ route(
                        'calibrations.quotation',
                        $calibration
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fas fa-file-invoice-dollar me-2 text-success"></i>

                    {{
                        $isCustomerUser
                            ? 'My Quotation'
                            : 'Quotation'
                    }}
                </a>
            </li>
        @endif


        {{-- ============================================= --}}
        {{-- JOB CARD                                      --}}
        {{-- Assigned onwards                              --}}
        {{-- KPLC staff only                               --}}
        {{-- ============================================= --}}

        @if($canAccessJobCard)
            <li>
                <a
                    class="dropdown-item"
                    href="{{ route(
                        'calibrations.job-card',
                        $calibration
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fas fa-clipboard-list me-2 text-info"></i>
                    Job Card
                </a>
            </li>
        @endif


        {{-- ============================================= --}}
        {{-- COLLECTION NOTE                               --}}
        {{-- Completed onwards                             --}}
        {{-- KPLC staff only                               --}}
        {{-- ============================================= --}}

        @if($canAccessCollectionNote)
            <li>
                <a
                    class="dropdown-item"
                    href="{{ route(
                        'calibrations.collection-note',
                        $calibration
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fas fa-truck me-2 text-success"></i>
                    Collection Note
                </a>
            </li>
        @endif


        {{-- ============================================= --}}
        {{-- CERTIFICATE                                   --}}
        {{-- Optional but useful                           --}}
        {{-- ============================================= --}}

        @if($latestCertificate)
            <li>
                <hr class="dropdown-divider">
            </li>

            @can('view_certificates')
                <li>
                    <a
                        class="dropdown-item"
                        href="{{ route(
                            'certificates.show',
                            $latestCertificate
                        ) }}"
                    >
                        <i class="fas fa-certificate me-2 text-primary"></i>
                        Certificate
                    </a>
                </li>
            @endcan
        @endif

    </ul>

</div>
                        </div>

                        {{-- Workflow guidance for KPLC staff. --}}
                        @if($isKplcStaffUser)
                            @if(!$quotationGenerated)
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    <strong>Quotation required.</strong>
                                    Generate the {{ $isKplcCustomer ? 'internal' : 'customer' }} quotation first.
                                </div>
                            @elseif($isExternalCustomer && !$quotationPaid)
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Awaiting customer payment.</strong>
                                    Technician assignment is locked until full payment is received.
                                </div>
                            @elseif(!$technicianAssigned)
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="fas fa-user-check me-2"></i>
                                    <strong>{{ $isKplcCustomer ? 'Internal quotation ready.' : 'Payment confirmed.' }}</strong>
                                    You can now assign a technician.
                                </div>
                            @else
                                <div class="alert alert-primary mt-3 mb-0">
                                    <i class="fas fa-user-cog me-2"></i>
                                    Assigned to <strong>{{ $calibration->performedBy?->name ?? 'the selected technician' }}</strong>.
                                </div>
                            @endif
                        @endif

                        {{-- Customer-facing guidance. --}}
                        @if($isCustomerUser)
                            @if(!$quotationGenerated)
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-clock me-1"></i>
                                    Your calibration request has been received. Please await an official quotation from Kenya Power Meter Central Laboratory.
                                </div>
                            @elseif($isExternalCustomer && !$quotationPaid)
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Your quotation is ready. Open <strong>View My Quotation</strong> and complete payment using the provided M-PESA details.
                                </div>
                            @else
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $isExternalCustomer ? 'Your payment has been received. MCL will now assign a technician.' : 'Your internal request is ready for technician assignment.' }}
                                </div>
                            @endif
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Test-result modals are rendered after the page structure, never inside a table. --}}
@foreach($recentTestResults as $result)
    @include('admin.calibrations.tests.partials.view-modal', [
        'calibration' => $calibration,
        'result' => $result,
    ])
@endforeach

{{-- Payment modal --}}
@if($canMarkPayment)
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('calibrations.mark-payment', $calibration) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark Payment Received</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            <div class="modal-body">


    {{-- Payment Summary --}}
    <div class="alert alert-info border mb-4">
        <div class="row text-center">

            <div class="col-md-4 border-end">
                <small class="text-muted d-block">
                    Quotation Amount
                </small>

                <h4 class="text-primary mb-0">
                    KSh {{ number_format($quotationAmount,2) }}
                </h4>
            </div>

            <div class="col-md-4 border-end">
                <small class="text-muted d-block">
                    Amount Already Paid
                </small>

                <h4 class="text-success mb-0">
                    KSh {{ number_format($amountPaid,2) }}
                </h4>
            </div>

            <div class="col-md-4">
                <small class="text-muted d-block">
                    Outstanding Balance
                </small>

                <h4 class="text-danger mb-0">
                    KSh {{ number_format($balance,2) }}
                </h4>
            </div>

        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">
            Payment Reference
        </label>

        <input
            type="text"
            name="payment_reference"
            class="form-control"
            placeholder="e.g. MPESA / Bank Reference"
        >
    </div>

    <div class="mb-3">
        <label class="form-label">
            Payment Method
        </label>

        <select
            name="payment_method"
            class="form-select"
        >
            <option value="mpesa">M-PESA</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="cash">Cash</option>
            <option value="cheque">Cheque</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">
            Amount Received (KSh)
        </label>

        <input
            type="number"
            name="amount_paid"
            id="amount_paid"
            class="form-control"
            step="0.01"
            min="0"
            max="{{ $balance }}"
            placeholder="Enter amount received"
        >

        <small
            id="paymentHint"
            class="text-muted"
        >
            Outstanding balance:
            <strong>
                KSh {{ number_format($balance,2) }}
            </strong>
        </small>
    </div>

    <div
        id="paymentWarning"
        class="alert alert-warning d-none"
    >
        <i class="fas fa-exclamation-triangle me-2"></i>

        The amount entered is greater than the outstanding balance.
    </div>

</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Assignment modal: only when workflow permits assignment/reassignment --}}
@if($canOpenAssignmentModal)
<div class="modal fade"
     id="assignModal{{ $calibration->id }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <form
                action="{{ route('calibrations.assign', $calibration) }}"
                method="POST"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-check me-2"></i>
                        Assign Technician
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Technician
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="technician_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                -- Select Technician --
                            </option>

                            @foreach($technicians as $technician)

                                <option
                                    value="{{ $technician->id }}"
                                    @selected(
                                        (int)$calibration->performed_by === (int)$technician->id
                                    )
                                >
                                    {{ $technician->name }}

                                    @if($technician->employee_number)
                                        ({{ $technician->employee_number }})
                                    @endif

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Assignment Notes
                        </label>

                        <textarea
                            name="assignment_notes"
                            class="form-control"
                            rows="3"
                        >{{ old('assignment_notes', $calibration->assignment_notes) }}</textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-user-check me-1"></i>

                        {{ $calibration->performed_by
                            ? 'Reassign Technician'
                            : 'Assign Technician' }}
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
@endif
@endsection

@if($canMarkPayment)
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const paymentModal =
            document.getElementById('paymentModal');

        const amountInput =
            document.getElementById('amount_paid');

        const warning =
            document.getElementById('paymentWarning');

        const paymentHint =
            document.getElementById('paymentHint');

        const paymentForm =
            paymentModal?.querySelector('form');

        const confirmButton =
            paymentForm?.querySelector(
                'button[type="submit"]'
            );

        if (
            !paymentModal
            || !amountInput
            || !warning
            || !paymentHint
        ) {
            return;
        }

        const quotationAmount =
            Number(@json($quotationAmount)) || 0;

        const amountAlreadyPaid =
            Number(@json($amountPaid)) || 0;

        const outstandingBalance =
            Number(@json($balance)) || 0;

        function formatCurrency(amount) {
            const value = Number(amount) || 0;

            return new Intl.NumberFormat('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        function resetWarning() {
            warning.classList.add('d-none');
            warning.classList.remove(
                'alert-warning',
                'alert-danger',
                'alert-success'
            );

            amountInput.classList.remove(
                'is-invalid',
                'is-valid'
            );

            if (confirmButton) {
                confirmButton.disabled = false;
            }
        }

        function updatePaymentPreview() {
            resetWarning();

            const enteredValue =
                Number.parseFloat(amountInput.value);

            const amountEntered =
                Number.isFinite(enteredValue)
                    ? enteredValue
                    : 0;

            const remainingBalance =
                Math.max(
                    0,
                    outstandingBalance - amountEntered
                );

            paymentHint.innerHTML = `
                <div class="border rounded bg-light p-3 mt-2">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Quotation amount</span>
                        <strong>
                            KSh ${formatCurrency(quotationAmount)}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Previously paid</span>
                        <strong class="text-success">
                            KSh ${formatCurrency(amountAlreadyPaid)}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment being entered</span>
                        <strong class="text-primary">
                            KSh ${formatCurrency(amountEntered)}
                        </strong>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between">
                        <span>Balance after payment</span>
                        <strong class="${
                            remainingBalance === 0
                                ? 'text-success'
                                : 'text-danger'
                        }">
                            KSh ${formatCurrency(remainingBalance)}
                        </strong>
                    </div>
                </div>
            `;

            if (
                amountInput.value !== ''
                && amountEntered <= 0
            ) {
                amountInput.classList.add('is-invalid');

                warning.className =
                    'alert alert-danger mt-3';

                warning.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Enter a payment amount greater than zero.
                `;

                warning.classList.remove('d-none');

                if (confirmButton) {
                    confirmButton.disabled = true;
                }

                return;
            }

            if (amountEntered > outstandingBalance) {
                amountInput.classList.add('is-invalid');

                warning.className =
                    'alert alert-danger mt-3';

                warning.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    The payment exceeds the outstanding balance by
                    <strong>
                        KSh ${formatCurrency(
                            amountEntered - outstandingBalance
                        )}
                    </strong>.
                `;

                warning.classList.remove('d-none');

                if (confirmButton) {
                    confirmButton.disabled = true;
                }

                return;
            }

            if (
                amountEntered === outstandingBalance
                && outstandingBalance > 0
            ) {
                amountInput.classList.add('is-valid');

                warning.className =
                    'alert alert-success mt-3';

                warning.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    This payment will fully settle the quotation.
                `;

                warning.classList.remove('d-none');

                return;
            }

            if (
                amountEntered > 0
                && amountEntered < outstandingBalance
            ) {
                warning.className =
                    'alert alert-warning mt-3';

                warning.innerHTML = `
                    <i class="fas fa-info-circle me-2"></i>
                    This is a partial payment. The remaining balance
                    will be
                    <strong>
                        KSh ${formatCurrency(remainingBalance)}
                    </strong>.
                `;

                warning.classList.remove('d-none');
            }
        }

        amountInput.addEventListener(
            'input',
            updatePaymentPreview
        );

        paymentForm?.addEventListener(
            'submit',
            function (event) {
                const amount =
                    Number.parseFloat(amountInput.value);

                if (
                    !Number.isFinite(amount)
                    || amount <= 0
                    || amount > outstandingBalance
                ) {
                    event.preventDefault();

                    updatePaymentPreview();
                    amountInput.focus();
                }
            }
        );

        paymentModal.addEventListener(
            'shown.bs.modal',
            function () {
                amountInput.value = '';
                resetWarning();
                updatePaymentPreview();
                amountInput.focus();
            }
        );
    });
    </script>
    @endpush
@endif

@push('styles')
<style>
    .calibration-show-page .detail-section {
        padding: 1.25rem;
        border: 1px solid #dce5f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .calibration-show-page .detail-section h4 {
        margin-bottom: 1rem;
        font-size: 1.05rem;
    }

    .calibration-show-page .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .calibration-show-page .detail-grid-four {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .calibration-show-page .detail-grid small {
        display: block;
        margin-bottom: .25rem;
        color: #68768a;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .calibration-show-page .detail-grid strong {
        color: #172033;
    }

    .result-detail-list dt {
        color: #65748b;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .calibration-show-page .detail-grid-four {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .calibration-show-page .detail-grid,
        .calibration-show-page .detail-grid-four {
            grid-template-columns: 1fr;
        }
    }


/* CUSTOMER EQUIPMENT TABLE */

.calibration-show-page .customer-equipment-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.94rem;
}

.calibration-show-page .customer-equipment-table thead th {
    background: #f1f5f9;
    color: #243247;
    font-weight: 700;
    padding: 14px 12px;
    border-bottom: 2px solid #dce5f0;
    vertical-align: middle;
    white-space: nowrap;
}

.calibration-show-page .customer-equipment-table tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #e9edf3;
}

.calibration-show-page .customer-equipment-table tbody tr:hover {
    background-color: #f8fbff;
}

.calibration-show-page .customer-equipment-table .equipment-name {
    color: #172033;
    font-weight: 700;
}

.calibration-show-page .customer-equipment-table .badge {
    font-size: 0.78rem;
    padding: 6px 9px;
    border-radius: 6px;
}

.calibration-show-page .customer-equipment-table .btn {
    white-space: nowrap;
}

@media (max-width: 991px) {
    .calibration-show-page .customer-equipment-table {
        min-width: 900px;
    }
}

</style>
@endpush
