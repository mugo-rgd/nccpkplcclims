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
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please correct the following:</strong>
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

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
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
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
                            <th>VAT Review</th>
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
                                        'suspended' => 'danger',
                                        'rejected' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$customer->status] ?? 'secondary' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td>
                                @if($customer->vat_exemption_status === 'pending')
                                    <span class="badge bg-warning text-dark">VAT Pending</span>
                                @elseif($customer->vat_exemption_status === 'approved')
                                    <span class="badge bg-success">VAT Approved ({{ $customer->customer_vat_rate }}%)</span>
                                @elseif($customer->vat_exemption_status === 'rejected')
                                    <span class="badge bg-danger">VAT Rejected</span>
                                @else
                                    <span class="badge bg-secondary">Standard VAT</span>
                                @endif
                            </td>
                            <td>
                                {{ $customer->created_at->format('Y-m-d') }}
                                <br><small class="text-muted">{{ $customer->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.customers.show', $customer) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $customer) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @hasanyrole('supervisor|lab_manager')
    @if($customer->status === 'pending')

        <button
            type="button"
            class="btn btn-sm btn-success"
            data-bs-toggle="modal"
            data-bs-target="#approveCustomerModal{{ $customer->id }}"
            title="Review and Approve Customer"
        >
            <i class="fas fa-check"></i>
        </button>

        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#rejectCustomerModal{{ $customer->id }}"
            title="Review and Reject Customer"
        >
            <i class="fas fa-ban"></i>
        </button>

    @endif
@endhasanyrole
                                    @role('lab_manager')
                                        @if($customer->vat_exemption_status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vatReviewModal{{ $customer->id }}" title="Review VAT Exemption"><i class="fas fa-file-invoice"></i> VAT</button>
                                        @endif
                                    @endrole
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $customer->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>


                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No customers found</h5>
                                <p class="text-muted">Try adjusting your search or filters</p>
                                <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Customer
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Modals outside the table to prevent click and overflow conflicts -->
            @foreach($customers as $customer)
@hasanyrole('supervisor|lab_manager')
    @if($customer->status === 'pending')

        <!-- APPROVE CUSTOMER MODAL -->
        <div class="modal fade"
             id="approveCustomerModal{{ $customer->id }}"
             tabindex="-1"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">

                    <form method="POST"
                          action="{{ route('admin.customers.approve', $customer) }}">
                        @csrf

                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-user-check me-2"></i>
                                Approve Customer Registration
                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal">
                            </button>
                        </div>

                        <div class="modal-body">

                            <div class="alert alert-info">
                                You are reviewing the registration for
                                <strong>{{ $customer->company_name }}</strong>.
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <strong>Contact Person</strong>
                                    <div>{{ $customer->contact_person }}</div>
                                </div>

                                <div class="col-md-6">
                                    <strong>KRA PIN</strong>
                                    <div>{{ $customer->tax_id ?: 'Not provided' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <strong>Email</strong>
                                    <div>{{ $customer->email }}</div>
                                </div>

                                <div class="col-md-6">
                                    <strong>Phone</strong>
                                    <div>{{ $customer->phone }}</div>
                                </div>
                            </div>

                            <label class="form-label">
                                Approval Notes
                            </label>

                            <textarea
                                name="approval_notes"
                                class="form-control"
                                rows="3"
                                maxlength="2000"
                                placeholder="Enter your customer registration review comments..."
                            ></textarea>

                            <div class="form-check mt-3">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="approval_confirmed"
                                    value="1"
                                    id="approvalConfirmed{{ $customer->id }}"
                                    required
                                >

                                <label class="form-check-label"
                                       for="approvalConfirmed{{ $customer->id }}">
                                    I confirm that I have reviewed this customer's
                                    registration details.
                                </label>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Confirm Approval
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


        <!-- REJECT CUSTOMER MODAL -->
        <div class="modal fade"
             id="rejectCustomerModal{{ $customer->id }}"
             tabindex="-1"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <form method="POST"
                          action="{{ route('admin.customers.reject', $customer) }}">
                        @csrf

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                Reject Customer Registration
                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal">
                            </button>
                        </div>

                        <div class="modal-body">

                            <p>
                                Customer:
                                <strong>{{ $customer->company_name }}</strong>
                            </p>

                            <label class="form-label">
                                Reason for Rejection
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="rejection_reason"
                                class="form-control"
                                rows="4"
                                minlength="10"
                                maxlength="2000"
                                required
                                placeholder="Explain why the registration is being rejected..."
                            ></textarea>

                        </div>

                        <div class="modal-footer">

                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-danger">
                                Confirm Rejection
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

    @endif
@endhasanyrole
                                @role('lab_manager')
                                @if($customer->vat_exemption_status === 'pending')
                                <div class="modal fade" id="vatReviewModal{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg"><div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">VAT Exemption Review — {{ $customer->company_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <p><strong>KRA PIN:</strong> {{ $customer->tax_id ?: 'Not supplied' }}</p>
                                            <p><strong>Exemption Certificate:</strong> {{ $customer->vat_exemption_certificate_number ?: 'Not supplied' }}</p>
                                            <p><strong>Expiry:</strong> {{ $customer->vat_exemption_expiry_date?->format('d M Y') ?? 'Not supplied' }}</p>
                                            <p class="text-muted">Verify the uploaded exemption document and its validity before approving. The customer account decision is independent.</p>
                                            <form method="POST" action="{{ route('admin.customers.vat.approve', $customer) }}" class="mb-3">
                                                @csrf
                                                <label class="form-label">Approved VAT rate (%)</label>
                                                <input type="number" name="approved_vat_rate" class="form-control mb-2" min="0" max="100" step="0.01" value="0" required>
                                                <label class="form-label">Verification notes</label><textarea name="verification_notes" class="form-control mb-2" rows="2"></textarea>
                                                <button type="submit" class="btn btn-success">Approve VAT Exemption</button>
                                            </form>
                                            <hr>
                                            <form method="POST" action="{{ route('admin.customers.vat.reject', $customer) }}">
                                                @csrf
                                                <label class="form-label">Reason for VAT rejection</label><textarea name="verification_notes" class="form-control mb-2" rows="2" minlength="10" required></textarea>
                                                <button type="submit" class="btn btn-danger">Reject VAT Exemption</button>
                                            </form>
                                        </div>
                                    </div></div>
                                </div>
                                @endif
                                @endrole

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
                                                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" style="display:inline;">
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
            @endforeach

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
        cursor: default;
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
    .modal-dialog {
    max-height: calc(100dvh - 2rem);
}

.modal-content {
    max-height: calc(100dvh - 2rem);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.modal-content > form {
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}
.modal-header, .modal-footer { flex-shrink: 0; }

.modal-body {
    overflow-y: auto;
    min-height: 0;
    flex: 1 1 auto;
}
</style>
@endpush


