@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Create Delivery Record</h2>
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

                    <form method="POST" action="{{ route('deliveries.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Request <span class="text-danger">*</span></label>
                                <select name="customer_request_id" class="form-select" required>
                                    <option value="">Select Request</option>
                                    @foreach($requests as $id => $number)
                                        <option value="{{ $id }}" {{ old('customer_request_id') == $id ? 'selected' : '' }}>
                                            {{ $number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="delivery">Delivery</option>
                                    <option value="collection">Collection</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Method <span class="text-danger">*</span></label>
                                <select name="method" class="form-select" required>
                                    <option value="pickup">Pickup</option>
                                    <option value="courier">Courier</option>
                                    <option value="site_visit">Site Visit</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scheduled At</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">From Address</label>
                                <input type="text" name="from_address" class="form-control" value="{{ old('from_address') }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">To Address</label>
                                <input type="text" name="to_address" class="form-control" value="{{ old('to_address') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Condition Notes</label>
                                <textarea name="condition_notes" class="form-control" rows="2">{{ old('condition_notes') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Handling Instructions</label>
                                <textarea name="handling_instructions" class="form-control" rows="2">{{ old('handling_instructions') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Record</button>
                        <a href="{{ route('deliveries.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
