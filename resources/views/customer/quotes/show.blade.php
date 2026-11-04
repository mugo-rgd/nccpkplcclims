@extends('layouts.app')

@section('title', 'Quote #' . $quote->quote_number)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>
                    <i class="fas fa-file-invoice-dollar text-success me-2"></i>
                    Quote #{{ $quote->quote_number }}
                </h3>
                <div>
                    <a href="{{ route('customer.quotes') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Quotes
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Customer Information</h5>
                    <div class="card bg-light p-3">
                        <p class="mb-1"><strong>Company:</strong> {{ $quote->customer->company_name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Contact:</strong> {{ $quote->customer->contact_person ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Phone:</strong> {{ $quote->customer->phone ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Quote Details</h5>
                    <div class="card bg-light p-3">
                        <p class="mb-1"><strong>Quote Number:</strong> {{ $quote->quote_number }}</p>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $quote->status === 'approved' ? 'success' : ($quote->status === 'sent' ? 'info' : ($quote->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Valid Until:</strong> {{ $quote->valid_until ? \Carbon\Carbon::parse($quote->valid_until)->format('d/m/Y') : 'N/A' }}</p>
                        <p class="mb-0"><strong>Created:</strong> {{ $quote->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <h5 class="mt-4">Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $items = $quote->items_data; @endphp
                        @if($items && count($items) > 0)
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['description'] ?? 'N/A' }}</td>
                                    <td>{{ $item['quantity'] ?? 1 }}</td>
                                    <td>{{ $quote->currency }} {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                    <td>{{ $quote->currency }} {{ number_format($item['total'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center text-muted">No items found</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>{{ $quote->currency }} {{ number_format($quote->subtotal, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Tax ({{ $quote->tax_rate }}%):</strong></td>
                            <td><strong>{{ $quote->currency }} {{ number_format($quote->tax_amount, 2) }}</strong></td>
                        </tr>
                        @if($quote->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                            <td><strong>-{{ $quote->currency }} {{ number_format($quote->discount_amount, 2) }}</strong></td>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                            <td><strong>{{ $quote->currency }} {{ number_format($quote->total_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($quote->notes)
                <div class="mt-3">
                    <h5>Notes</h5>
                    <p class="text-muted">{{ $quote->notes }}</p>
                </div>
            @endif

            @if($quote->terms)
                <div class="mt-3">
                    <h5>Terms & Conditions</h5>
                    <p class="text-muted">{{ $quote->terms }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
