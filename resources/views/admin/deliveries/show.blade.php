@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Delivery Record: {{ $delivery->delivery_number }}</h2>
                    <div class="float-end">
                        <span class="badge bg-{{ $delivery->type === 'delivery' ? 'info' : 'warning' }} fs-6">
                            {{ ucfirst($delivery->type) }}
                        </span>
                        <span class="badge bg-{{ $delivery->status === 'completed' || $delivery->status === 'delivered' || $delivery->status === 'collected' ? 'success' : ($delivery->status === 'scheduled' ? 'warning' : ($delivery->status === 'cancelled' ? 'danger' : 'secondary')) }} fs-6">
                            {{ ucfirst($delivery->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Delivery Details</h4>
                            <p><strong>Type:</strong> {{ ucfirst($delivery->type) }}</p>
                            <p><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $delivery->method)) }}</p>
                            <p><strong>Request #:</strong> {{ $delivery->customerRequest->request_number ?? 'N/A' }}</p>
                            <p><strong>Job Card:</strong> {{ $delivery->jobCard->job_card_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Contact Details</h4>
                            <p><strong>Contact Person:</strong> {{ $delivery->contact_person ?? 'N/A' }}</p>
                            <p><strong>Contact Phone:</strong> {{ $delivery->contact_phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Address Information</h4>
                            <p><strong>From:</strong> {{ $delivery->from_address ?? 'N/A' }}</p>
                            <p><strong>To:</strong> {{ $delivery->to_address ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Items</h4>
                            @if($delivery->items)
                                <ul>
                                    @foreach($delivery->items as $key => $value)
                                        <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>No items listed.</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Timeline</h4>
                            <p><strong>Scheduled:</strong> {{ $delivery->scheduled_at ? $delivery->scheduled_at->format('Y-m-d H:i') : 'N/A' }}</p>
                            <p><strong>Dispatched:</strong> {{ $delivery->dispatched_at ? $delivery->dispatched_at->format('Y-m-d H:i') : 'N/A' }}</p>
                            <p><strong>Delivered:</strong> {{ $delivery->delivered_at ? $delivery->delivered_at->format('Y-m-d H:i') : 'N/A' }}</p>
                            <p><strong>Collected:</strong> {{ $delivery->collected_at ? $delivery->collected_at->format('Y-m-d H:i') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Notes</h4>
                            <p><strong>Condition Notes:</strong> {{ $delivery->condition_notes ?? 'N/A' }}</p>
                            <p><strong>Handling Instructions:</strong> {{ $delivery->handling_instructions ?? 'N/A' }}</p>
                            <p><strong>Additional Notes:</strong> {{ $delivery->notes ?? 'N/A' }}</p>
                            <p><strong>Created By:</strong> {{ $delivery->createdBy->name ?? 'N/A' }}</p>
                            <p><strong>Created At:</strong> {{ $delivery->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <!-- Signatures -->
                    @if($delivery->sender_signature_path || $delivery->receiver_signature_path)
                    <hr>
                    <h4>Signatures</h4>
                    <div class="row">
                        @if($delivery->sender_signature_path)
                        <div class="col-md-6">
                            <h5>Sender</h5>
                            <p><strong>Name:</strong> {{ $delivery->sender_name ?? 'N/A' }}</p>
                            <img src="{{ asset('storage/' . $delivery->sender_signature_path) }}" alt="Sender Signature" style="max-height: 100px;">
                        </div>
                        @endif
                        @if($delivery->receiver_signature_path)
                        <div class="col-md-6">
                            <h5>Receiver</h5>
                            <p><strong>Name:</strong> {{ $delivery->receiver_name ?? 'N/A' }}</p>
                            <img src="{{ asset('storage/' . $delivery->receiver_signature_path) }}" alt="Receiver Signature" style="max-height: 100px;">
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-3">
                        <a href="{{ route('deliveries.index') }}" class="btn btn-secondary">Back</a>

                        @can('edit_customers')
                            <a href="{{ route('deliveries.edit', $delivery) }}" class="btn btn-warning">Edit</a>
                        @endcan

                        @if($delivery->status === 'pending' || $delivery->status === 'scheduled')
                            <form action="{{ route('deliveries.dispatch', $delivery) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Dispatch this delivery?')">
                                    <i class="fas fa-truck"></i> Dispatch
                                </button>
                            </form>
                        @endif

                        @if($delivery->status === 'dispatched' && $delivery->type === 'delivery')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#deliverModal">
                                <i class="fas fa-check"></i> Mark Delivered
                            </button>
                        @endif

                        @if($delivery->status === 'dispatched' && $delivery->type === 'collection')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#collectModal">
                                <i class="fas fa-hand-holding"></i> Mark Collected
                            </button>
                        @endif

                        @if($delivery->status !== 'cancelled' && $delivery->status !== 'completed')
                            <form action="{{ route('deliveries.cancel', $delivery) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this delivery record?')">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deliver Modal -->
@if($delivery->status === 'dispatched' && $delivery->type === 'delivery')
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('deliveries.deliver', $delivery) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Receiver Name</label>
                        <input type="text" name="receiver_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receiver Signature (Base64 or path)</label>
                        <input type="text" name="receiver_signature" class="form-control" placeholder="Upload signature image path">
                    </div>
                    <p>Confirm that the items have been delivered successfully.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Collect Modal -->
@if($delivery->status === 'dispatched' && $delivery->type === 'collection')
<div class="modal fade" id="collectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('deliveries.collect', $delivery) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sender Name</label>
                        <input type="text" name="sender_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sender Signature (Base64 or path)</label>
                        <input type="text" name="sender_signature" class="form-control" placeholder="Upload signature image path">
                    </div>
                    <p>Confirm that the items have been collected successfully.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Collection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
