@extends('layouts.app')

@section('title', 'Review Quote Request')

@section('content')

@php
    $status = (string) ($quoteRequest->status ?? 'pending');

    $statusClass = match ($status) {
        'pending'   => 'warning',
        'reviewed'  => 'info',
        'converted' => 'success',
        'rejected'  => 'danger',
        default     => 'secondary',
    };

    $isConverted = $status === 'converted';

    $items = $quoteRequest->items ?? collect();

    $itemsTotal = 0;
    foreach ($items as $line) {
        $rate = (float) ($line->equipmentType?->rate ?? 0);
        $itemsTotal += $rate * (int) $line->quantity;
    }

    $hasItems = $items->isNotEmpty();
@endphp


<div class="container-fluid quote-request-review-page">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER                                               --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                Review Quote Request
            </h1>
            <p class="text-muted mb-0">
                Reference:
                <strong>{{ $quoteRequest->reference_number ?? 'N/A' }}</strong>
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">

            <a href="{{ route('admin.quote-requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

            <span class="badge bg-{{ $statusClass }} fs-6 align-self-center">
                {{ ucfirst(str_replace('_', ' ', $status)) }}
            </span>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- ALERTS                                                    --}}
    {{-- ========================================================= --}}

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="row g-4">

        {{-- ===================================================== --}}
        {{-- LEFT COLUMN                                          --}}
        {{-- ===================================================== --}}

        <div class="col-xl-5">

            {{-- ================================================= --}}
            {{-- CUSTOMER REQUEST                                 --}}
            {{-- ================================================= --}}

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        Customer Request
                    </h5>
                </div>

                <div class="card-body">

                    <div class="detail-row">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">{{ $quoteRequest->name ?? 'N/A' }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">{{ $quoteRequest->company ?? 'N/A' }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">
                            @if($quoteRequest->email)
                                <a href="mailto:{{ $quoteRequest->email }}">{{ $quoteRequest->email }}</a>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $quoteRequest->phone ?? 'N/A' }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Submitted</div>
                        <div class="detail-value">
                            {{ $quoteRequest->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- REQUESTED ITEMS                                  --}}
            {{-- ================================================= --}}

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-microchip text-info me-2"></i>
                        Requested Equipment &amp; Services
                    </h5>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Equipment</th>
                                    <th>Serial / Model</th>
                                    <th>Service</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($items as $line)

                                    @php
                                        $rate = (float) ($line->equipmentType?->rate ?? 0);
                                        $lineTotal = $rate * (int) $line->quantity;
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>{{ $line->equipment_name ?: ($line->equipmentType?->name ?? '—') }}</strong>

                                            @if(!empty($line->equipmentType?->code))
                                                <small class="d-block text-muted">{{ $line->equipmentType->code }}</small>
                                            @endif

                                            @if($line->manufacturer || $line->rating)
                                                <small class="d-block text-muted">
                                                    {{ $line->manufacturer }} {{ $line->rating ? ' / ' . $line->rating : '' }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $line->serial_number ?: '—' }}
                                            @if($line->model)
                                                <small class="d-block text-muted">{{ $line->model }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $line->serviceType?->serv_name ?? '—' }}
                                            @if($line->serviceType?->serv_code)
                                                <small class="d-block text-muted">
                                                    {{ $line->serviceType->serv_code }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $line->quantity }}</td>
                                        <td class="text-end">KES {{ number_format($rate, 2) }}</td>
                                        <td class="text-end">KES {{ number_format($lineTotal, 2) }}</td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            No equipment items recorded.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                            @if($items->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Indicative Total</th>
                                        <th class="text-end text-success">
                                            KES {{ number_format($itemsTotal, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            @endif

                        </table>

                    </div>

                </div>

                <div class="card-footer bg-white">
                    <div class="alert alert-warning mb-0 py-2 small">
                        <i class="fas fa-info-circle me-2"></i>
                        Amounts are indicative only. The official quotation is generated
                        after MCL review and confirmation of the calibration details.
                    </div>
                </div>

            </div>


            {{-- ================================================= --}}
            {{-- REQUIREMENTS                                     --}}
            {{-- ================================================= --}}

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comment-alt text-secondary me-2"></i>
                        Customer Requirements
                    </h5>
                </div>

                <div class="card-body">
                    <div class="requirements-box">
                        {{ $quoteRequest->requirements ?: 'No additional requirements were provided.' }}
                    </div>
                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- RIGHT COLUMN                                         --}}
        {{-- ===================================================== --}}

        <div class="col-xl-7">

            @if($isConverted)

                {{-- ================================================= --}}
                {{-- ALREADY CONVERTED                                 --}}
                {{-- ================================================= --}}

                <div class="card shadow-sm border-0">

                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Request Converted
                        </h5>
                    </div>

                    <div class="card-body">

                        <p>
                            This public quotation request has already been converted
                            into an official calibration request.
                        </p>

                        @if($quoteRequest->calibration)

                            <div class="mb-3">
                                <strong>Calibration:</strong>
                                #{{ $quoteRequest->calibration->id }}
                            </div>

                            <a
                                href="{{ route('calibrations.show', $quoteRequest->calibration) }}"
                                class="btn btn-success"
                            >
                                <i class="fas fa-microscope me-1"></i>
                                Open Calibration
                            </a>

                        @endif

                    </div>

                </div>

            @else

                {{-- ================================================= --}}
                {{-- CONVERSION FORM — ONE CALIBRATION, MANY ITEMS     --}}
                {{-- ================================================= --}}

                @unless($hasItems)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This quote request has no items to convert. Add items to the request
                        before converting.
                    </div>
                @else

                    <form
                        action="{{ route('admin.quote-requests.convert', $quoteRequest) }}"
                        method="POST"
                        id="convertForm"
                    >
                        @csrf

                        <div class="card shadow-sm border-0">

                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                                    Prepare Official Calibration Request
                                    <small class="text-muted fw-normal ms-2">
                                        ({{ $items->count() }} {{ \Illuminate\Support\Str::plural('item', $items->count()) }})
                                    </small>
                                </h5>
                            </div>

                            <div class="card-body">

                                {{-- ===================================== --}}
                                {{-- 1. CUSTOMER                           --}}
                                {{-- ===================================== --}}

                                <div class="section-heading">
                                    <span>1</span> Customer Details
                                </div>

                                <div class="row g-3 mb-4">

                                    <div class="col-12">
                                        <label for="customer_id" class="form-label fw-bold">Existing Customer</label>
                                        <select
                                            name="customer_id"
                                            id="customer_id"
                                            class="form-select @error('customer_id') is-invalid @enderror"
                                        >
                                            <option value="">-- Create / Use Details Below --</option>
                                            @foreach($customers as $customer)
                                                <option
                                                    value="{{ $customer->id }}"
                                                    @selected((string) old('customer_id') === (string) $customer->id)
                                                >
                                                    {{ $customer->company_name ?? 'Customer #' . $customer->id }}
                                                    @if($customer->email) — {{ $customer->email }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            Select an existing customer, or leave blank to create one from the details below.
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="company_name" class="form-label fw-bold">Company Name</label>
                                        <input
                                            type="text"
                                            name="company_name"
                                            id="company_name"
                                            class="form-control @error('company_name') is-invalid @enderror"
                                            value="{{ old('company_name', $quoteRequest->company) }}"
                                        >
                                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contact_person" class="form-label fw-bold">
                                            Contact Person <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="contact_person"
                                            id="contact_person"
                                            class="form-control @error('contact_person') is-invalid @enderror"
                                            value="{{ old('contact_person', $quoteRequest->name) }}"
                                            required
                                        >
                                        @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-bold">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $quoteRequest->email) }}"
                                            required
                                        >
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-bold">
                                            Phone <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="phone"
                                            id="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone', $quoteRequest->phone) }}"
                                            required
                                        >
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                </div>


                                {{-- ===================================== --}}
                                {{-- 2. EQUIPMENT ITEMS                     --}}
                                {{-- ===================================== --}}

                                <div class="section-heading">
                                    <span>2</span> Equipment Items
                                </div>

                                <div id="equipmentItems">

                                    @foreach($items as $index => $line)

                                        @php
                                            $oldKey = "items.{$index}";
                                        @endphp

                                        <div class="equipment-row card mb-3" data-index="{{ $index }}">

                                            <div class="card-body">

                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <strong class="text-muted small item-position-label">
                                                        Item {{ $index + 1 }}
                                                    </strong>
                                                    @if($items->count() > 1)
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger remove-row"
                                                            title="Remove this item"
                                                        >
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>

                                                <input
                                                    type="hidden"
                                                    name="items[{{ $index }}][quote_request_item_id]"
                                                    value="{{ $line->id }}"
                                                >

                                                <div class="row g-3">

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">
                                                            Equipment Type <span class="text-danger">*</span>
                                                        </label>
                                                        <select
                                                            name="items[{{ $index }}][equipment_type_id]"
                                                            class="form-select equipment-select @error("items.{$index}.equipment_type_id") is-invalid @enderror"
                                                            required
                                                        >
                                                            <option value="">-- Select Equipment Type --</option>
                                                            @foreach($equipmentTypes as $type)
                                                                <option
                                                                    value="{{ $type->id }}"
                                                                    data-rate="{{ $type->rate }}"
                                                                    data-name="{{ $type->name }}"
                                                                    @selected(
                                                                        (string) old(
                                                                            "items.{$index}.equipment_type_id",
                                                                            $line->equipment_type_id
                                                                        ) === (string) $type->id
                                                                    )
                                                                >
                                                                    {{ $type->name }} — KES {{ number_format((float) $type->rate, 2) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error("items.{$index}.equipment_type_id")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">
                                                            Equipment Name <span class="text-danger">*</span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            name="items[{{ $index }}][equipment_name]"
                                                            class="form-control equipment-name @error("items.{$index}.equipment_name") is-invalid @enderror"
                                                            value="{{ old("items.{$index}.equipment_name", $line->equipment_name ?: $line->equipmentType?->name) }}"
                                                            required
                                                        >
                                                        @error("items.{$index}.equipment_name")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">
                                                            Serial Number <span class="text-danger">*</span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            name="items[{{ $index }}][serial_number]"
                                                            class="form-control @error("items.{$index}.serial_number") is-invalid @enderror"
                                                            value="{{ old("items.{$index}.serial_number", $line->serial_number) }}"
                                                            placeholder="Enter equipment serial number"
                                                            required
                                                        >
                                                        @error("items.{$index}.serial_number")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Manufacturer</label>
                                                        <input
                                                            type="text"
                                                            name="items[{{ $index }}][manufacturer]"
                                                            class="form-control @error("items.{$index}.manufacturer") is-invalid @enderror"
                                                            value="{{ old("items.{$index}.manufacturer", $line->manufacturer) }}"
                                                        >
                                                        @error("items.{$index}.manufacturer")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Model</label>
                                                        <input
                                                            type="text"
                                                            name="items[{{ $index }}][model]"
                                                            class="form-control @error("items.{$index}.model") is-invalid @enderror"
                                                            value="{{ old("items.{$index}.model", $line->model) }}"
                                                        >
                                                        @error("items.{$index}.model")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Rating</label>
                                                        <input
                                                            type="text"
                                                            name="items[{{ $index }}][rating]"
                                                            class="form-control @error("items.{$index}.rating") is-invalid @enderror"
                                                            value="{{ old("items.{$index}.rating", $line->rating) }}"
                                                            placeholder="e.g. 230V 10-100A"
                                                        >
                                                        @error("items.{$index}.rating")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">
                                                            Equipment Condition <span class="text-danger">*</span>
                                                        </label>
                                                        <select
                                                            name="items[{{ $index }}][equipment_condition]"
                                                            class="form-select @error("items.{$index}.equipment_condition") is-invalid @enderror"
                                                            required
                                                        >
                                                            <option value="">-- Select Condition --</option>
                                                            <option value="good"    @selected(old("items.{$index}.equipment_condition") === 'good')>Good</option>
                                                            <option value="fair"    @selected(old("items.{$index}.equipment_condition") === 'fair')>Fair</option>
                                                            <option value="damaged" @selected(old("items.{$index}.equipment_condition") === 'damaged')>Damaged</option>
                                                        </select>
                                                        @error("items.{$index}.equipment_condition")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Calibration Services</label>
                                                        <div class="service-grid">
                                                            @foreach($serviceTypes as $service)
                                                                <label class="service-option">
                                                                    <input
                                                                        type="checkbox"
                                                                        name="items[{{ $index }}][service_types][]"
                                                                        value="{{ $service->id }}"
                                                                        @checked(in_array(
                                                                            (string) $service->id,
                                                                            array_map(
                                                                                'strval',
                                                                                (array) old(
                                                                                    "items.{$index}.service_types",
                                                                                    $line->service_type_id
                                                                                        ? [$line->service_type_id]
                                                                                        : []
                                                                                )
                                                                            ),
                                                                            true
                                                                        ))
                                                                    >
                                                                    <span>
                                                                        <strong>{{ $service->serv_name }}</strong>
                                                                        <small>
                                                                            {{ $service->serv_code }}
                                                                            @if($service->serv_method) · {{ $service->serv_method }} @endif
                                                                        </small>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Item Notes</label>
                                                        <input
                                                            type="text"
                                                            name="items[{{ $index }}][notes]"
                                                            class="form-control @error("items.{$index}.notes") is-invalid @enderror"
                                                            value="{{ old("items.{$index}.notes") }}"
                                                            placeholder="Any item-specific notes…"
                                                        >
                                                        @error("items.{$index}.notes")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                                <button type="button" id="addEquipmentRow" class="btn btn-sm btn-outline-primary mb-4">
                                    <i class="fas fa-plus me-1"></i> Add another equipment
                                </button>


                                {{-- ===================================== --}}
                                {{-- 3. REVIEW NOTES                       --}}
                                {{-- ===================================== --}}

                                <div class="section-heading">
                                    <span>3</span> Review Notes
                                </div>

                                <div class="row g-3">

                                    <div class="col-12">
                                        <label for="notes" class="form-label fw-bold">Notes</label>
                                        <textarea
                                            name="notes"
                                            id="notes"
                                            rows="4"
                                            class="form-control @error('notes') is-invalid @enderror"
                                            placeholder="{{ \Illuminate\Support\Str::limit($quoteRequest->requirements ?? '', 120, '…') ?: 'No customer requirements recorded.' }}"
                                        >{{ old('notes') }}</textarea>
                                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">
                                            Internal review notes — do not overwrite the customer's original requirements.
                                        </small>
                                    </div>

                                </div>

                            </div>

                            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This creates <strong>one calibration request</strong> containing all items above.
                                    The official quotation is generated afterwards.
                                </small>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-1"></i>
                                    Convert to Calibration Request
                                </button>

                            </div>

                        </div>

                    </form>

                @endunless

            @endif

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    .quote-request-review-page .card {
        border-radius: 14px;
        overflow: hidden;
    }

    .quote-request-review-page .card-header {
        padding: 14px 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .detail-row {
        display: grid;
        grid-template-columns: minmax(130px, 38%) minmax(0, 62%);
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #eef2f7;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .detail-value {
        color: #172033;
        font-size: 0.9rem;
    }

    .requirements-box {
        min-height: 90px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        color: #334155;
        white-space: pre-line;
        line-height: 1.55;
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        color: #06366f;
        font-size: 0.95rem;
        font-weight: 800;
    }

    .section-heading span {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #06366f;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 900;
    }

    .service-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .service-option {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 10px 12px;
        border: 1px solid #dce4ee;
        border-radius: 10px;
        background: #f8fafc;
        cursor: pointer;
    }

    .service-option:hover {
        border-color: #9db9d9;
        background: #f3f8fe;
    }

    .service-option input {
        margin-top: 4px;
    }

    .service-option strong {
        display: block;
        color: #172033;
        font-size: 0.82rem;
    }

    .service-option small {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 0.72rem;
    }

    @media (max-width: 767.98px) {

        .detail-row {
            grid-template-columns: 1fr;
            gap: 3px;
        }

        .service-grid {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const wrapper = document.getElementById('equipmentItems');
    const addBtn  = document.getElementById('addEquipmentRow');
    if (!wrapper || !addBtn) return;

    /* ----------------------------------------------
       Auto-fill Equipment Name from selected type
    ---------------------------------------------- */
    wrapper.addEventListener('change', function (e) {
        if (!e.target.classList.contains('equipment-select')) return;

        const opt      = e.target.options[e.target.selectedIndex];
        const name     = opt?.dataset.name || '';
        const row      = e.target.closest('.equipment-row');
        const nameInput = row?.querySelector('.equipment-name');

        if (nameInput && !nameInput.value.trim()) {
            nameInput.value = name;
        }
    });

    /* ----------------------------------------------
       Remove a row
    ---------------------------------------------- */
    wrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;

        const row = btn.closest('.equipment-row');
        if (!row) return;

        if (wrapper.querySelectorAll('.equipment-row').length <= 1) {
            return;
        }

        row.remove();
        reindex();
    });

    /* ----------------------------------------------
       Add a blank row
    ---------------------------------------------- */
    addBtn.addEventListener('click', function () {
        const last = wrapper.querySelector('.equipment-row');
        if (!last) return;

        const clone = last.cloneNode(true);

        clone.querySelectorAll('input[type="text"], input[type="number"]').forEach(el => el.value = '');
        clone.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
        clone.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);
        clone.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        clone.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        const hiddenId = clone.querySelector('input[name*="[quote_request_item_id]"]');
        if (hiddenId) hiddenId.remove();

        wrapper.appendChild(clone);
        reindex();
    });

    /* ----------------------------------------------
       Renumber items[i] names after add/remove
    ---------------------------------------------- */
    function reindex() {
        wrapper.querySelectorAll('.equipment-row').forEach(function (row, i) {
            row.dataset.index = i;

            row.querySelectorAll('input, select, textarea').forEach(function (el) {
                if (!el.name) return;
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
            });

            const label = row.querySelector('.item-position-label');
            if (label) label.textContent = 'Item ' + (i + 1);
        });
    }

});
</script>
@endpush
