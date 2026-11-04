@extends('layouts.app')

@section('title', 'Quotation #' . ($quote->quote_number ?? $quote->id))

@section('content')
<div class="container-fluid py-4">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Unable to process quotation</strong>

        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

@if($quote->isCancelled())
    <div class="alert alert-warning mt-3">
        <i class="fas fa-ban me-2"></i>

        This quotation has been cancelled and is
        retained for audit purposes.

        @if(!empty($quote->metadata['replaced_by_quote_id']))
            <a href="{{ route(
                'admin.quotes.show',
                $quote->metadata['replaced_by_quote_id']
            ) }}" class="fw-bold">
                View Replacement Quotation
            </a>
        @endif
    </div>
@endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-file-invoice-dollar text-success me-2"></i>
                        Quotation #{{ $quote->quote_number ?? $quote->id }}
                    </h3>

                    <small class="text-muted">
                        Created:
                        {{ optional($quote->created_at)->format('d M Y, H:i') ?? 'N/A' }}
                    </small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="{{ url()->previous() }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back
                    </a>

                    <a
                        href="{{ route('admin.quotes.download', $quote) }}"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-file-pdf me-1"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-3 p-lg-4">
            <div class="row g-4 mb-4">
                {{-- Customer information --}}
                <div class="col-lg-6">
                    <h5 class="mb-3">
                        <i class="fas fa-building text-primary me-2"></i>
                        Customer Information
                    </h5>

                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Company
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->customer?->company_name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Contact Person
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->customer?->contact_person ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Phone
                                    </small>

                                    @if($quote->customer?->phone)
                                        <a
                                            href="tel:{{ $quote->customer->phone }}"
                                            class="text-decoration-none fw-semibold"
                                        >
                                            {{ $quote->customer->phone }}
                                        </a>
                                    @else
                                        <span class="fw-semibold">N/A</span>
                                    @endif
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Email
                                    </small>

                                    @if($quote->customer?->email)
                                        <a
                                            href="mailto:{{ $quote->customer->email }}"
                                            class="text-decoration-none fw-semibold text-break"
                                        >
                                            {{ $quote->customer->email }}
                                        </a>
                                    @else
                                        <span class="fw-semibold">N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quote details --}}
                <div class="col-lg-6">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Quotation Details
                    </h5>

                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Quotation Number
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->quote_number ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Status
                                    </small>

                                    <span class="badge bg-{{ $quote->status_color }}">
                                        {{ $quote->status_label }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Valid Until
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->valid_until?->format('d M Y') ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Currency
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->currency_code }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Created At
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->created_at?->format('d M Y, H:i') ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Last Updated
                                    </small>

                                    <span class="fw-semibold">
                                        {{ $quote->updated_at?->format('d M Y, H:i') ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
     <div class="mt-5 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list text-primary me-2"></i>
            Quotation Items
        </h5>

        <span class="badge bg-primary px-3 py-2">
            {{ count($quote->items_data) }}
            {{ count($quote->items_data) === 1 ? 'item' : 'items' }}
        </span>
    </div>
</div>

            <div class="table-responsive border rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
<tr>
    <th style="width:60px;">#</th>
    <th>Equipment Name</th>
    <th>Serial Number</th>
    <th>Description</th>
    <th class="text-end">Qty</th>
    <th class="text-end">Unit Price</th>
    <th class="text-end">Total</th>
</tr>
</thead>

                    <tbody>
@forelse($quote->items_data as $item)

@php
    $equipmentName = $item['equipment_name']
        ?? $calibration->equipment_name
        ?? $calibration->equipment?->name
        ?? 'N/A';

    $serialNumber = $item['serial_number']
        ?? $calibration->serial_number
        ?? $calibration->equipment?->serial_number
        ?? 'N/A';

    $description = $item['description']
        ?? 'Calibration Service';

    $quantity = (float) ($item['quantity'] ?? 1);

    $unitPrice = (float) (
        $item['unit_price']
        ?? $item['price']
        ?? $item['rate']
        ?? 0
    );

    $itemTotal = (float) (
        $item['total']
        ?? $item['amount']
        ?? ($quantity * $unitPrice)
    );
@endphp

<tr>

    <td>{{ $loop->iteration }}</td>

    <td>
        <strong>{{ $equipmentName }}</strong>
    </td>

    <td>
        {{ $serialNumber }}
    </td>

    <td>
        {{ $description }}

        @if(!empty($item['details']))
            <br>
            <small class="text-muted">
                {{ $item['details'] }}
            </small>
        @endif
    </td>

    <td class="text-end">
        {{ number_format($quantity,0) }}
    </td>

    <td class="text-end">
        {{ $quote->currency_code }}
        {{ number_format($unitPrice,2) }}
    </td>

    <td class="text-end fw-bold">
        {{ $quote->currency_code }}
        {{ number_format($itemTotal,2) }}
    </td>

</tr>

@empty

<tr>
    <td colspan="7" class="text-center py-4">
        No quotation items found.
    </td>
</tr>

@endforelse
</tbody>

                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end">
                                <strong>Subtotal</strong>
                            </td>

                            <td class="text-end">
                                <strong>
                                    {{ $quote->formatted_subtotal }}
                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4" class="text-end">
                                <strong>
                                    Tax ({{ number_format((float) $quote->tax_rate, 2) }}%)
                                </strong>
                            </td>

                            <td class="text-end">
                                <strong>
                                    {{ $quote->formatted_tax }}
                                </strong>
                            </td>
                        </tr>

                        @if((float) $quote->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="text-end">
                                    <strong>Discount</strong>
                                </td>

                                <td class="text-end text-danger">
                                    <strong>
                                        -{{ $quote->formatted_discount }}
                                    </strong>
                                </td>
                            </tr>
                        @endif

                        <tr class="table-success">
                            <td colspan="4" class="text-end fs-5">
                                <strong>Total Amount</strong>
                            </td>

                            <td class="text-end fs-5">
                                <strong>
                                    {{ $quote->formatted_total }}
                                </strong>
                            </td>
                        </tr>

                        @if((float) $quote->amount_paid > 0)
                            <tr>
                                <td colspan="4" class="text-end">
                                    <strong>Amount Paid</strong>
                                </td>

                                <td class="text-end text-success">
                                    <strong>
                                        {{ $quote->formatted_amount_paid }}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4" class="text-end">
                                    <strong>Balance Due</strong>
                                </td>

                                <td class="text-end {{ $quote->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    <strong>
                                        {{ $quote->formatted_balance_due }}
                                    </strong>
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Notes and terms --}}
            @if($quote->notes || $quote->terms)
                <div class="row g-4 mt-2">
                    @if($quote->notes)
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-sticky-note text-warning me-2"></i>
                                        Notes
                                    </h5>

                                    <div
                                        class="text-muted"
                                        style="white-space: pre-line;"
                                    >{{ $quote->notes }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($quote->terms)
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-file-contract text-primary me-2"></i>
                                        Terms & Conditions
                                    </h5>

                                    <div
                                        class="text-muted"
                                        style="white-space: pre-line;"
                                    >{{ $quote->terms }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Related calibration --}}
            @if($calibration)
                <div class="mt-4">
                    <h5 class="mb-3">
                        <i class="fas fa-tools text-info me-2"></i>
                        Related Calibration
                    </h5>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-9">
                                    <div class="row g-3">
                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">
                                                Calibration ID
                                            </small>

                                            <span class="fw-semibold">
                                                #{{ $calibration->id }}
                                            </span>
                                        </div>

                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">
                                                Equipment
                                            </small>

                                            <span class="fw-semibold">
                                                {{
                                                    $calibration->equipment_name
                                                    ?? $calibration->equipment?->name
                                                    ?? 'N/A'
                                                }}
                                            </span>
                                        </div>

                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">
                                                Status
                                            </small>

                                            <span class="badge bg-secondary">
                                                {{
                                                    ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $calibration->status ?? 'N/A'
                                                        )
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 text-lg-end">
                                    @if(Route::has('calibrations.show'))
                                        <a
                                            href="{{ route('calibrations.show', $calibration) }}"
                                            class="btn btn-info"
                                        >
                                            <i class="fas fa-eye me-1"></i>
                                            View Calibration
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            
{{-- Cancel quotation and create replacement --}}
@php
    $canReplaceQuotation =
        in_array($quote->status, [
            \App\Models\Quote::STATUS_DRAFT,
            \App\Models\Quote::STATUS_SENT,
            \App\Models\Quote::STATUS_REJECTED,
        ], true)
        && (float) ($quote->amount_paid ?? 0) <= 0
        && auth()->user()->hasAnyRole([
            'super_admin',
            'lab_manager',
        ]);
@endphp

@if($canReplaceQuotation)
    <div class="card border-warning mt-4 shadow-sm">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-exchange-alt me-2"></i>
                Cancel &amp; Replace Quotation
            </h5>
        </div>

        <div class="card-body">
            <p class="text-muted mb-3">
                Cancel this quotation and create a new draft
                using the customer's currently approved VAT rate.
                The original quotation will be retained for
                audit purposes.
            </p>

            <form
                action="{{ route('admin.quotes.cancel-and-replace', $quote) }}"
                method="POST"
                onsubmit="return confirm('Cancel quotation {{ $quote->quote_number }} and create a replacement? This action cannot be undone automatically.');"
            >
                @csrf

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sync-alt me-2"></i>
                        Cancel &amp; Create Replacement
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif


            {{-- Approval --}}
            @can('approve_quotes')
                @if($quote->canBeApproved())
                    <div class="mt-4 pt-4 border-top">
                        <form
                            action="{{ route('admin.quotes.approve', $quote) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to approve this quotation?');"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label
                                    for="approval_notes"
                                    class="form-label"
                                >
                                    Approval Notes
                                    <span class="text-muted">
                                        (optional)
                                    </span>
                                </label>

                                <textarea
                                    name="approval_notes"
                                    id="approval_notes"
                                    rows="3"
                                    class="form-control @error('approval_notes') is-invalid @enderror"
                                    placeholder="Enter approval comments..."
                                >{{ old('approval_notes') }}</textarea>

                                @error('approval_notes')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="text-end">
                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    <i class="fas fa-check-circle me-1"></i>
                                    Approve Quotation
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection
