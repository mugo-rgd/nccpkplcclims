@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>
                    <i class="fas fa-users"></i> Customers
                    <span class="badge bg-secondary">{{ $customers->total() }}</span>
                </h1>
                <div>
                    @can('export_data')
                        <a href="{{ route('customers.export') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    @endcan
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Customer
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Customers</h5>
                            <h2>{{ $customers->total() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Active</h5>
                            <h2>{{ $customers->where('status', 'active')->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2>{{ $customers->where('status', 'pending')->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">KPLC Customers</h5>
                            <h2>{{ $customers->filter(function($c) { return stripos($c->company_name, 'KPLC') !== false || stripos($c->company_name, 'Kenya Power') !== false; })->count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by company, contact, phone, email..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="kplc" {{ request('type') == 'kplc' ? 'selected' : '' }}>KPLC</option>
                                <option value="external" {{ request('type') == 'external' ? 'selected' : '' }}>External</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Date From">
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>
                                <strong>{{ $customer->company_name }}</strong>
                                @if($customer->tax_id)
                                    <br><small class="text-muted">PIN: {{ $customer->tax_id }}</small>
                                @endif
                            </td>
                            <td>{{ $customer->contact_person }}</td>
                            <td>
                                <a href="tel:{{ $customer->phone }}">
                                    <i class="fas fa-phone"></i> {{ $customer->phone }}
                                </a>
                            </td>
                            <td>
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}">
                                        <i class="fas fa-envelope"></i> {{ $customer->email }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $isKplc = stripos($customer->company_name, 'KPLC') !== false ||
                                              stripos($customer->company_name, 'Kenya Power') !== false;
                                @endphp
                                @if($isKplc)
                                    <span class="badge bg-primary">
                                        <i class="fas fa-building"></i> KPLC
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-building"></i> External
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'suspended' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$customer->status] ?? 'secondary' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td>
                                {{ $customer->created_at->format('Y-m-d') }}
                                <br><small class="text-muted">{{ $customer->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $customer->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this customer?</p>
                                                <div class="alert alert-warning">
                                                    <strong>Company:</strong> {{ $customer->company_name }}<br>
                                                    <strong>Contact:</strong> {{ $customer->contact_person }}
                                                </div>
                                                <p class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    This action cannot be undone. All associated data will be removed.
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No customers found</h5>
                                <p class="text-muted">Try adjusting your search or filters</p>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Customer
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted">
                        Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }}
                        of {{ $customers->total() }} customers
                    </span>
                </div>
                <div>
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        cursor: pointer;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    .card.text-white .card-body {
        padding: 0.75rem 1rem;
    }
    .card.text-white .card-title {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    .card.text-white h2 {
        font-size: 1.8rem;
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Clickable row - navigate to customer details
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.table tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Ignore if clicking on a button or link
                if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.btn')) {
                    return;
                }
                const viewLink = this.querySelector('a.btn-info');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
            });
        });
    });
</script>
@endpush
