@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Customer Details</h2>
                    <div class="float-end">
                        <span class="badge bg-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Company Information</h4>
                            <p><strong>Company:</strong> {{ $customer->company_name }}</p>
                            <p><strong>Contact Person:</strong> {{ $customer->contact_person }}</p>
                            <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                            <p><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
                            <p><strong>Address:</strong> {{ $customer->address }}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Business Details</h4>
                            <p><strong>Tax ID:</strong> {{ $customer->tax_id ?? 'N/A' }}</p>
                            <p><strong>Credit Limit:</strong> {{ $customer->credit_limit ? number_format($customer->credit_limit, 2) : 'N/A' }}</p>
                            <p><strong>Payment Terms:</strong> {{ str_replace('_', ' ', ucfirst($customer->payment_terms)) }}</p>
                            <p><strong>Registration Date:</strong> {{ $customer->registration_date ? $customer->registration_date->format('Y-m-d') : 'N/A' }}</p>
                            <p><strong>User Account:</strong> {{ $customer->user ? $customer->user->name : 'No user linked' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Total Calibrations</h5>
                                    <h2>{{ $stats['total_calibrations'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h5 class="card-title">Completed</h5>
                                    <h2>{{ $stats['completed_calibrations'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <h5 class="card-title">Pending</h5>
                                    <h2>{{ $stats['pending_calibrations'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <h5 class="card-title">Certificates</h5>
                                    <h2>{{ $stats['certificates_issued'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back</a>
                        @can('edit_customers')
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">Edit</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
