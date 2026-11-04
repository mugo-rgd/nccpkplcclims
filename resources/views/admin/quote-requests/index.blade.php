@extends('layouts.app')

@section('title', 'Public Quote Requests')

@section('content')

<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- HEADER                                                    --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                Public Quote Requests
            </h1>
            <p class="text-muted mb-0">
                Review quotation requests submitted from the public website.
            </p>
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FILTERS                                                   --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET" action="{{ route('admin.quote-requests.index') }}">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Search</label>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Reference, customer, email, phone or company"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending"   @selected(request('status') === 'pending')>Pending</option>
                            <option value="reviewed"  @selected(request('status') === 'reviewed')>Reviewed</option>
                            <option value="converted" @selected(request('status') === 'converted')>Converted</option>
                            <option value="rejected"  @selected(request('status') === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- TABLE                                                     --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($quoteRequests as $quoteRequest)

                            @php
                                $statusClass = match($quoteRequest->status) {
                                    'pending'   => 'warning',
                                    'reviewed'  => 'info',
                                    'converted' => 'success',
                                    'rejected'  => 'danger',
                                    default     => 'secondary',
                                };

                                $itemCount = $quoteRequest->items_count
                                    ?? $quoteRequest->items->count();

                                $totalQty = $quoteRequest->items->sum('quantity');
                                $firstItem = $quoteRequest->items->first();
                            @endphp

                            <tr>

                                {{-- Reference --}}
                                <td>
                                    <strong>
                                        {{ $quoteRequest->reference_number ?? 'N/A' }}
                                    </strong>
                                </td>


                                {{-- Customer --}}
                                <td>
                                    <strong>{{ $quoteRequest->name ?? 'N/A' }}</strong>

                                    @if($quoteRequest->company)
                                        <small class="d-block text-muted">
                                            {{ $quoteRequest->company }}
                                        </small>
                                    @endif

                                    @if($quoteRequest->email)
                                        <small class="d-block text-muted">
                                            {{ $quoteRequest->email }}
                                        </small>
                                    @endif
                                </td>


                                {{-- Items --}}
                                <td>
                                    @if($itemCount > 0)

                                        <strong>{{ $itemCount }}</strong>
                                        {{ \Illuminate\Support\Str::plural('line', $itemCount) }}

                                        <small class="d-block text-muted">
                                            {{ $totalQty }}
                                            {{ \Illuminate\Support\Str::plural('unit', $totalQty) }} total
                                        </small>

                                        @if($firstItem?->equipmentType)
                                            <small class="d-block text-muted">
                                                First: {{ $firstItem->equipmentType->name }}
                                            </small>
                                        @endif

                                    @else
                                        <span class="text-muted">No items</span>
                                    @endif
                                </td>


                                {{-- Status --}}
                                <td>
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $quoteRequest->status)) }}
                                    </span>
                                </td>


                                {{-- Date --}}
                                <td>
                                    {{ $quoteRequest->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </td>


                                {{-- Actions --}}
                                <td class="text-end">

                                    <div class="btn-group btn-group-sm" role="group">

                                        @if($quoteRequest->status === 'pending')

                                            <a
                                                href="{{ route('admin.quote-requests.show', $quoteRequest) }}"
                                                class="btn btn-primary"
                                                title="Review Quote Request"
                                            >
                                                <i class="fas fa-eye me-1"></i> Review
                                            </a>

                                        @elseif($quoteRequest->status === 'reviewed')

                                            <a
                                                href="{{ route('admin.quote-requests.show', $quoteRequest) }}"
                                                class="btn btn-info"
                                            >
                                                <i class="fas fa-edit me-1"></i> Continue Review
                                            </a>

                                        @elseif($quoteRequest->status === 'converted' && $quoteRequest->calibration)

                                            <a
                                                href="{{ route('calibrations.show', $quoteRequest->calibration) }}"
                                                class="btn btn-success"
                                                title="Open Calibration"
                                            >
                                                <i class="fas fa-microscope me-1"></i> Open Calibration
                                            </a>

                                        @endif

                                        {{-- Detail icon only for statuses that don't already show a show-link --}}
                                        @if(!in_array($quoteRequest->status, ['pending', 'reviewed'], true))
                                            <a
                                                href="{{ route('admin.quote-requests.show', $quoteRequest) }}"
                                                class="btn btn-outline-secondary"
                                                title="View Details"
                                            >
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">

                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>

                                    <h5 class="text-muted">No Quote Requests</h5>

                                    <p class="text-muted mb-0">
                                        No public quotation requests have been submitted yet.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- PAGINATION                                                --}}
    {{-- ========================================================= --}}

    @if($quoteRequests->hasPages())

        <div class="mt-3">

            {{ $quoteRequests->appends(request()->query())->links() }}

        </div>

    @endif

</div>

@endsection