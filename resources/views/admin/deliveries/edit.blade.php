@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Edit Delivery Record</h2>
                    <p class="mb-0">#{{ $delivery->delivery_number }}</p>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deliveries.update', $delivery) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="delivery" {{ $delivery->type === 'delivery' ? 'selected' : '' }}>Delivery</option>
                                    <option value="collection" {{ $delivery->type === 'collection' ? 'selected' : '' }}>Collection</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Method</label>
                                <select name="method" class="form-select" required>
                                    <option value="pickup" {{ $delivery->method === 'pickup' ? 'selected' : '' }}>Pickup</option>
                                    <option value="courier" {{ $delivery->method === 'courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="site_visit" {{ $delivery->method === 'site_visit' ? 'selected' : '' }}>Site Visit</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">From Address</label>
                                <input type="text" name="from_address" class="form-control" value="{{ old('from_address', $delivery->from_address) }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">To Address</label>
                                <input type="text" name="to_address" class="form-control" value="{{ old('to_address', $delivery->to_address) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $delivery->contact_person) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $delivery->contact_phone) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scheduled At</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control"
                                       value="{{ $delivery->scheduled_at ? $delivery->scheduled_at->format('Y-m-d\TH:i') : '' }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $delivery->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="scheduled" {{ $delivery->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="dispatched" {{ $delivery->status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="delivered" {{ $delivery->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="collected" {{ $delivery->status === 'collected' ? 'selected' : '' }}>Collected</option>
                                    <option value="completed" {{ $delivery->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $delivery->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Condition Notes</label>
                                <textarea name="condition_notes" class="form-control" rows="2">{{ old('condition_notes', $delivery->condition_notes) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Handling Instructions</label>
                                <textarea name="handling_instructions" class="form-control" rows="2">{{ old('handling_instructions', $delivery->handling_instructions) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $delivery->notes) }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Record</button>
                        <a href="{{ route('deliveries.show', $delivery) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
