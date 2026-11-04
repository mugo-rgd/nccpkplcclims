@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Edit Customer: {{ $customer->company_name }}</h2>
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

                    <form method="POST" action="{{ route('customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $customer->company_name) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $customer->contact_person) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required>{{ old('address', $customer->address) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax ID (PIN)</label>
                                <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id', $customer->tax_id) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Credit Limit</label>
                                <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}" step="0.01">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $customer->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ $customer->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="suspended" {{ $customer->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Is Active</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" {{ $customer->is_active ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', is_array($customer->notes) ? json_encode($customer->notes) : $customer->notes) }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
