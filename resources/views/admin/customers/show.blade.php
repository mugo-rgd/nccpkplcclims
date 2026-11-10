
@extends('layouts.app')

@section('content')
@php
    $notes = $customer->notes;

    if (is_string($notes)) {
        $notes = json_decode($notes, true) ?: [];
    }

    $notes = is_array($notes) ? $notes : [];

    $isQuickAdd =
        ($notes['registration_source'] ?? null) === 'quick_add';

    $registrationIncomplete =
        $isQuickAdd
        && (
            ($notes['registration_incomplete'] ?? false)
            || !$customer->user_id
        );

    $kraDocuments = \App\Models\CustomerTaxDocument::query()
        ->where('customer_id', $customer->id)
        ->where('document_type', 'kra_pin_certificate')
        ->latest()
        ->get();

    // Most recently uploaded KRA certificate determines the current status.
    $latestKraDocument = $kraDocuments->sortByDesc('id')->first();

    $vatDocuments = \App\Models\CustomerTaxDocument::query()
        ->where('customer_id', $customer->id)
        ->where('document_type', 'vat_exemption_certificate')
        ->latest()
        ->get();

   $latestKraDocument = $kraDocuments
        ->sortByDesc('id')
        ->first();

    $kraVerified = $latestKraDocument
        && $latestKraDocument->verification_status === 'verified';

    $canReview = auth()->user()?->hasAnyRole([
        'supervisor',
        'lab_manager',
    ]) ?? false;
@endphp

<div class="container py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-building me-2"></i>
                Customer Details
            </h2>
            <div class="text-muted">
                {{ $customer->company_name }}
                | Customer #{{ $customer->id }}
            </div>
        </div>

        <span class="badge bg-{{
            $customer->status === 'active'
                ? 'success'
                : ($customer->status === 'pending'
                    ? 'warning text-dark'
                    : 'danger')
        }} fs-6">
            {{ ucfirst($customer->status) }}
        </span>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Customer Information --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-address-card me-2"></i>
                Customer Information
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">
                        Company Information
                    </h5>

                    <p><strong>Company:</strong>
                        {{ $customer->company_name }}
                    </p>

                    <p><strong>Contact Person:</strong>
                        {{ $customer->contact_person }}
                    </p>

                    <p><strong>Phone:</strong>
                        {{ $customer->phone }}
                    </p>

                    <p><strong>Email:</strong>
                        {{ $customer->email ?? 'N/A' }}
                    </p>

                    <p><strong>Address:</strong>
                        {{ $customer->address ?? 'N/A' }}
                    </p>
                </div>

                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">
                        Business Details
                    </h5>

                    <p><strong>KRA PIN:</strong>
                        {{ $customer->tax_id ?? 'N/A' }}
                    </p>

                    <p><strong>Credit Limit:</strong>
                        {{ $customer->credit_limit !== null
                            ? number_format($customer->credit_limit, 2)
                            : 'N/A' }}
                    </p>

                    <p><strong>Payment Terms:</strong>
                        {{ $customer->payment_terms
                            ? ucfirst(str_replace('_', ' ', $customer->payment_terms))
                            : 'N/A' }}
                    </p>

                    <p><strong>Registration Date:</strong>
                        {{ $customer->registration_date
                            ? \Illuminate\Support\Carbon::parse(
                                $customer->registration_date
                              )->format('d M Y')
                            : 'N/A' }}
                    </p>

                    <p><strong>User Account:</strong>
                        {{ $customer->user?->name ?? 'No user linked' }}
                    </p>

                    <p><strong>Account Active:</strong>
                        @if($customer->is_active)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Registration Status --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-user-check me-2"></i>
                Registration & Approval
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Registration Source</strong>
                    <div>
                        {{ $isQuickAdd
                            ? 'Quick Add (KPLC Staff)'
                            : 'Standard Registration' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Registration Completion</strong>
                    <div>
                        @if($registrationIncomplete)
                            <span class="badge bg-warning text-dark">
                                Incomplete
                            </span>
                        @elseif($isQuickAdd)
                            <span class="badge bg-success">
                                Completed
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                Standard Registration
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Account Approval</strong>
                    <div>
                        <span class="badge bg-{{
                            $customer->status === 'active'
                                ? 'success'
                                : 'warning text-dark'
                        }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($registrationIncomplete)
                <div class="alert alert-warning mt-3">
                    This customer was created through Quick Add.
                    Complete registration before account approval.
                </div>

                @hasanyrole('super_admin|lab_manager|supervisor')
                    <a href="{{
                        route(
                            'customers.complete-registration.form',
                            $customer
                        )
                    }}" class="btn btn-warning">
                        <i class="fas fa-user-edit me-1"></i>
                        Complete Registration
                    </a>
                @endhasanyrole
            @endif

            @if(!empty($notes['account_review']))
                <hr>
                <h6>Last Account Review</h6>

                <p class="mb-1">
                    <strong>Decision:</strong>
                    {{ ucfirst(
                        $notes['account_review']['decision']
                            ?? 'N/A'
                    ) }}
                </p>

                <p class="mb-1">
                    <strong>Reviewer:</strong>
                    {{ $notes['account_review']['reviewer_name']
                        ?? 'N/A' }}
                </p>

                <p class="mb-1">
                    <strong>Reviewed At:</strong>
                    {{ $notes['account_review']['reviewed_at']
                        ?? 'N/A' }}
                </p>

                <p class="mb-0">
                    <strong>Notes:</strong>
                    {{ $notes['account_review']['approval_notes']
                        ?? 'N/A' }}
                </p>
            @endif

        </div>
    </div>

    {{-- KRA PIN Certificate Verification --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="fas fa-file-alt me-2"></i>
                KRA PIN Certificate Verification
            </h5>
        </div>

        <div class="card-body">

            @if(
    $customer->status === 'active'
    && !$kraVerified
)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>

        <strong>Documentation Verification Outstanding</strong>

        <p class="mb-0 mt-2">
            This customer account is already active, but the
            KRA PIN certificate has not been verified.
            An authorized reviewer should inspect the
            uploaded certificate and complete verification.
        </p>
    </div>
@endif

            @if($kraDocuments->isEmpty())
                <div class="alert alert-warning mb-0">
                    No KRA PIN certificate has been uploaded.
                </div>
            @else

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Document</th>
                                <th>Uploaded</th>
                                <th>Status</th>
                                <th>Verified At</th>
                                <th>Verification Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($kraDocuments as $document)
                                <tr>
                                    <td>
    <div class="d-flex align-items-center gap-2 flex-wrap">

        <span>
            <i class="fas fa-file-pdf text-danger me-1"></i>
            {{ $document->original_filename }}
        </span>

        <a href="{{ route('admin.customers.kra-pin.view', [$customer, $document]) }}"
           class="btn btn-sm btn-outline-primary"
           title="Download KRA PIN Certificate">

            <i class="fas fa-download me-1"></i>
            Download

        </a>

    </div>
</td>

                                    <td>
                                        {{ $document->created_at
                                            ? $document->created_at->format('d M Y H:i')
                                            : 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{
                                            $document->verification_status === 'verified'
                                                ? 'success'
                                                : ($document->verification_status === 'rejected'
                                                    ? 'danger'
                                                    : 'warning text-dark')
                                        }}">
                                            {{ ucfirst(
                                                $document->verification_status
                                            ) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $document->verified_at
                                            ? \Illuminate\Support\Carbon::parse(
                                                $document->verified_at
                                              )->format('d M Y H:i')
                                            : 'Not verified' }}
                                    </td>

                                    <td>
                                        {{ $document->verification_notes
                                            ?: 'N/A' }}
                                    </td>

                                    <td>
                                       @if(
    $canReview
    && $latestKraDocument
    && $document->id === $latestKraDocument->id
    && $document->verification_status === 'pending'
)
                                            <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#verifyKraModal{{ $document->id }}">
                                                Verify
                                            </button>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectKraModal{{ $document->id }}">
                                                Reject
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

            @if(
                $customer->status === 'pending'
                && $latestKraDocument
                && $latestKraDocument->verification_status === 'rejected'
            )
                @hasanyrole('super_admin|supervisor|lab_manager')
                    <button type="button"
                            class="btn btn-warning mt-3"
                            data-bs-toggle="modal"
                            data-bs-target="#replaceKraModal">
                        <i class="fas fa-upload me-1"></i>
                        Upload Replacement Certificate
                    </button>
                @endhasanyrole
            @endif

            <div class="mt-2">
                <strong>Overall KRA Verification:</strong>

                @if($kraVerified)
                    <span class="badge bg-success">
                        Verified
                    </span>
                @else
                    <span class="badge bg-warning text-dark">
                        Not Verified
                    </span>
                @endif
            </div>

        </div>
    </div>

    {{-- VAT Exemption Details --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-file-invoice me-2"></i>
                VAT Exemption Details
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <strong>VAT Review Status</strong>
                    <div>
                        {{ ucfirst(
                            $customer->vat_exemption_status ?? 'none'
                        ) }}
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Approved VAT Rate</strong>
                    <div>
                        {{ $customer->vat_exemption_status === 'approved'
                            ? ($customer->customer_vat_rate ?? $customer->vat_rate ?? 'N/A') . '%'
                            : 'Standard VAT applies' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Exemption Certificate Number</strong>
                    <div>
                        {{ $customer->vat_exemption_certificate_number
                            ?? 'N/A' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Exemption Expiry Date</strong>
                    <div>
                        {{ $customer->vat_exemption_expiry_date
                            ? \Illuminate\Support\Carbon::parse(
                                $customer->vat_exemption_expiry_date
                              )->format('d M Y')
                            : 'N/A' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>VAT Documents</strong>
                    <div>{{ $vatDocuments->count() }}</div>
                </div>

                <div class="col-md-4">
                    <strong>VAT Verification Notes</strong>
                    <div>
                        {{ $customer->vat_verification_notes ?? 'N/A' }}
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Existing Statistics --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6>Total Calibrations</h6>
                    <h2>{{ $stats['total_calibrations'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6>Completed</h6>
                    <h2>{{ $stats['completed_calibrations'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h2>{{ $stats['pending_calibrations'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h6>Certificates</h6>
                    <h2>{{ $stats['certificates_issued'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

    </div>

    {{-- Customer Approval --}}
    @if(
        $canReview
        && $customer->status === 'pending'
        && !$registrationIncomplete
    )
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Customer Account Approval</h5>
            </div>

            <div class="card-body">

                @if(!$kraVerified && $isQuickAdd)
                    <div class="alert alert-warning">
                        Verify the KRA PIN certificate before
                        approving this customer.
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('admin.customers.approve', $customer) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Approval Notes
                        </label>

                        <textarea
                            name="approval_notes"
                            class="form-control"
                            rows="3"
                            maxlength="2000"
                        ></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="approval_confirmed"
                            id="approvalConfirmed"
                            value="1"
                            required>

                        <label class="form-check-label"
                               for="approvalConfirmed">
                            I confirm that I have reviewed
                            the customer registration.
                        </label>
                    </div>

                    <button type="submit"
                            class="btn btn-success"
                            @disabled($isQuickAdd && !$kraVerified)>
                        <i class="fas fa-check-circle me-1"></i>
                        Approve Customer
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Bottom Actions --}}
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('customers.index') }}"
           class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>

        @can('edit_customers')
            <a href="{{ route('customers.edit', $customer) }}"
               class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>
                Edit Customer
            </a>
        @endcan
    </div>

</div>

{{-- KRA Verification Modals --}}
@if($canReview)
    @foreach($kraDocuments as $document)

        @if($document->verification_status === 'pending')

            {{-- Verify Modal --}}
            <div class="modal fade"
                 id="verifyKraModal{{ $document->id }}"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST"
                          class="modal-content"
                          action="{{ route(
                              'admin.customers.kra-pin.verify',
                              $customer
                          ) }}">
                        @csrf

                        <input type="hidden"
                               name="document_id"
                               value="{{ $document->id }}">

                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                Verify KRA PIN Certificate
                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>
                                <strong>Document:</strong>
                                {{ $document->original_filename }}
                            </p>

                            <label class="form-label">
                                Verification Notes
                            </label>

                            <textarea
                                name="verification_notes"
                                class="form-control"
                                rows="3"
                                maxlength="2000"
                            ></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-success">
                                Confirm Verification
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Reject Modal --}}
            <div class="modal fade"
                 id="rejectKraModal{{ $document->id }}"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST"
                          class="modal-content"
                          action="{{ route(
                              'admin.customers.kra-pin.reject',
                              $customer
                          ) }}">
                        @csrf

                        <input type="hidden"
                               name="document_id"
                               value="{{ $document->id }}">

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                Reject KRA PIN Certificate
                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>
                                <strong>Document:</strong>
                                {{ $document->original_filename }}
                            </p>

                            <label class="form-label">
                                Reason for Rejection *
                            </label>

                            <textarea
                                name="verification_notes"
                                class="form-control"
                                rows="4"
                                minlength="10"
                                maxlength="2000"
                                required
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
                                Reject Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @endif
            @endforeach
@endif


@hasanyrole('super_admin|supervisor|lab_manager')
<div class="modal fade"
     id="replaceKraModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
              action="{{ route('admin.customers.kra-pin.replace', $customer) }}"
              enctype="multipart/form-data"
              class="modal-content">

            @csrf

            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-file-upload me-2"></i>
                    Replace KRA PIN Certificate
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info">
                    The rejected document will be preserved
                    for audit purposes. The replacement
                    requires verification before approval.
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Replacement Certificate *
                    </label>

                    <input type="file"
                           name="kra_pin_certificate"
                           class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png"
                           required>

                    <small class="text-muted">
                        PDF, JPG or PNG. Maximum 5 MB.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Reason for Replacement *
                    </label>

                    <textarea
                        name="replacement_reason"
                        class="form-control"
                        rows="3"
                        minlength="10"
                        maxlength="2000"
                        required
                    ></textarea>
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
                    Upload Replacement
                </button>
            </div>

        </form>
    </div>
</div>
@endhasanyrole

@endsection
