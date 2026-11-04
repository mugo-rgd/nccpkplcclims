@extends('layouts.app')

@section('title', 'Customers Report - KPLC Meter Lab')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-building text-success me-2"></i>
                Customers Report
            </h1>
            <p class="text-muted mb-0">
                Customer activity and calibration statistics
            </p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <button onclick="window.print()" class="btn btn-primary ms-2">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Customers</h6>
                    <h2 class="mb-0">{{ $customers->total() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Calibrations</h6>
                    <h2 class="mb-0">{{ $customers->sum('calibrations_count') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Average per Customer</h6>
                    <h2 class="mb-0">{{ $customers->total() > 0 ? round($customers->sum('calibrations_count') / $customers->total(), 1) : 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Customer List
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Calibrations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $customer->company_name }}</strong>
                                </td>
                                <td>{{ $customer->contact_person ?? 'N/A' }}</td>
                                <td>{{ $customer->phone ?? 'N/A' }}</td>
                                <td>{{ $customer->email ?? 'N/A' }}</td>
                                <td>{{ $customer->city ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'inactive' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($customer->status ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $customer->calibrations_count }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted d-block mb-2"></i>
                                    No customers found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
   {{ $customers->appends(request()->query())->links() }}
</div>
@endsection
