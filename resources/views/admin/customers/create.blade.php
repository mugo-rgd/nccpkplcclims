@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Create New Customer</h2>
                    <p class="mb-0 text-muted">
                        <small>Creating customer as: {{ auth()->user()->name }}
                        @if(auth()->user()->staff_number)
                            (Staff #: {{ auth()->user()->staff_number }})
                        @endif
                        </small>
                    </p>
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

                    <form method="POST" action="{{ route('customers.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                                <small class="text-muted">For KPLC customers, include "KPLC" in the name</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Type</label>
                                <div class="mt-1">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="customer_type" value="external" checked>
                                        <label class="form-check-label">External</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="customer_type" value="kplc">
                                        <label class="form-check-label">KPLC</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                <small class="text-muted">Format: 0722XXXXXX or 254722XXXXXX</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                                <small class="text-muted">If provided, a user account will be created.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax ID (PIN)</label>
                                <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id') }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required>{{ old('address') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Credit Limit</label>
                                <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit') }}" step="0.01">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Terms</label>
                                <select name="payment_terms" class="form-select">
                                    <option value="30_days">30 Days</option>
                                    <option value="60_days">60 Days</option>
                                    <option value="90_days">90 Days</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                <small class="text-muted">Additional information about the customer</small>
                            </div>

                            <!-- Hidden fields for tracking -->
                            <input type="hidden" name="created_by" value="{{ auth()->id() }}">
                            <input type="hidden" name="created_by_name" value="{{ auth()->user()->name }}">
                            @if(auth()->user()->staff_number)
                                <input type="hidden" name="created_by_staff" value="{{ auth()->user()->staff_number }}">
                            @endif
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Customer
                            </button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-detect KPLC customer
    document.querySelector('input[name="company_name"]').addEventListener('input', function() {
        const value = this.value.toUpperCase();
        if (value.includes('KPLC') || value.includes('KENYA POWER')) {
            document.querySelector('input[name="customer_type"][value="kplc"]').checked = true;
        }
    });
</script>
@endpush
@endsection
