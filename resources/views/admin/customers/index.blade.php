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
                    @hasanyrole('super_admin|lab_manager|supervisor')
    <button type="button"
            class="btn btn-warning"
            data-bs-toggle="modal"
            data-bs-target="#quickAddCustomerModal">
        <i class="fas fa-user-plus me-1"></i>
        Quick Add Customer
    </button>
@endhasanyrole

<a href="{{ route('register') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i> Add Customer
</a>
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
                    <strong>Could not complete the request:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                            <h2>{{ \App\Models\Customer::where('status', 'active')->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2>{{ \App\Models\Customer::where('status', 'pending')->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">KPLC Customers</h5>
                            <h2>{{ \App\Models\Customer::where(function ($q) { $q->where('company_name', 'like', '%KPLC%')->orWhere('company_name', 'like', '%Kenya Power%'); })->count() }}</h2>
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
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                   

@hasanyrole('supervisor|lab_manager')

    @if($customer->status === 'pending')

        {{-- Approve eligible registrations --}}
        @if($customer->can_approve_registration)

            <button
                type="button"
                class="btn btn-sm btn-success"
                data-bs-toggle="modal"
                data-bs-target="#approveCustomerModal{{ $customer->id }}"
                title="Review and Approve Customer">

                <i class="fas fa-check"></i>

            </button>

        @elseif(
            $customer->is_quick_add
            && !$customer->is_incomplete_quick_add
            && !$customer->kra_verified
        )

            <a href="{{ route('customers.show', $customer) }}"
               class="btn btn-sm btn-outline-primary"
               title="Review KRA PIN Certificate">

                <i class="fas fa-file-alt me-1"></i>
                @if($customer->latest_kra_status === 'rejected')
                    Replace KRA
                @elseif($customer->latest_kra_status === 'pending')
                    Verify KRA
                @else
                    Review KRA
                @endif

            </a>

        @endif

        {{-- Reject pending registrations --}}
        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#rejectCustomerModal{{ $customer->id }}"
            title="Reject Customer">

            <i class="fas fa-ban"></i>

        </button>

    @endif

@endhasanyrole

                                    @hasanyrole('super_admin|lab_manager|supervisor')
                                        @if( $customer->status === 'pending'
                                                && $customer->is_incomplete_quick_add
                                            )
                                            <a
                                                href="{{ route(
                                                    'customers.complete-registration.form',
                                                    $customer
                                                ) }}"
                                                class="btn btn-sm btn-warning"
                                                title="Complete Customer Registration"
                                            >
                                                <i class="fas fa-user-check me-1"></i>
                                                Complete Registration
                                            </a>
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
                                <a href="{{ route('register') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Customer
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Modals must be outside the table and .table-responsive to avoid clipping and invalid table markup. --}}
            @foreach($customers as $customer)
                                @hasanyrole('supervisor|lab_manager')
    @if($customer->status === 'pending')
@if($customer->can_approve_registration)
        <!-- APPROVE CUSTOMER MODAL -->
        <div class="modal fade"
             id="approveCustomerModal{{ $customer->id }}"
             tabindex="-1"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-lg">
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
        @endif

        <!-- REJECT CUSTOMER MODAL -->
        <div class="modal fade"
             id="rejectCustomerModal{{ $customer->id }}"
             tabindex="-1"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
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
                                    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
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
                                                    This action may be irreversible. Confirm that deletion is permitted for this customer and its associated records.
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


@hasanyrole('super_admin|lab_manager|supervisor')
<div class="modal fade"
     id="quickAddCustomerModal"
     tabindex="-1"
     aria-labelledby="quickAddCustomerModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form method="POST"
                  action="{{ route('customers.quick-add') }}"
                  id="quickAddCustomerForm"
                  class="d-flex flex-column overflow-hidden"
                  style="min-height:0; max-height:calc(100dvh - 2rem);">
                @csrf

                <div class="modal-header bg-warning">
                    <h5 class="modal-title"
                        id="quickAddCustomerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Quick Add Customer
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        This creates a pending customer record.
                        Full registration can be completed later.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Company Name *
                        </label>
                        <input type="text"
                               name="company_name"
                               class="form-control"
                               value="{{ old('company_name') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Contact Person *
                        </label>
                        <input type="text"
                               name="contact_person"
                               class="form-control"
                               value="{{ old('contact_person') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Email
                        </label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Phone Number *
                        </label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Address
                        </label>
                        <textarea name="address"
          class="form-control"
          rows="2"
          required>{{ old('address') }}</textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>
                        Save Pending Customer
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endhasanyrole

@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
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
  /* Quick Add Customer Modal */
#quickAddCustomerModal .modal-dialog {
    max-height: calc(100dvh - 2rem);
}

#quickAddCustomerModal .modal-content {
    max-height: calc(100dvh - 2rem);
    overflow: hidden;
}

#quickAddCustomerModal #quickAddCustomerForm {
    display: flex;
    flex-direction: column;
    min-height: 0;
    max-height: calc(100dvh - 2rem);
    overflow: hidden;
}

#quickAddCustomerModal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
}

#quickAddCustomerModal .modal-header,
#quickAddCustomerModal .modal-footer {
    flex: 0 0 auto;
}
</style>
@endpush