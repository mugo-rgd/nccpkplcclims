
@extends('layouts.app')

@section('title', 'Complete Customer Registration')

@section('content')
<div class="container-fluid py-4">

    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-1">
                        <i class="fas fa-user-check me-2"></i>
                        Complete Customer Registration
                    </h4>

                    <small>
                        Complete the provisional customer record
                        and create an inactive login account.
                    </small>
                </div>

                <div class="card-body p-4">

                    <div class="alert alert-info">
                        <strong>Existing Customer:</strong>
                        {{ $customer->company_name }}

                        <div class="small mt-1">
                            Customer ID: {{ $customer->id }}
                            |
                            Status: {{ ucfirst($customer->status) }}
                        </div>

                        <div class="small mt-2">
                            The customer will remain pending until
                            approved by an authorized officer.
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>
                                Please correct the following:
                            </strong>

                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route(
                            'customers.complete-registration',
                            $customer
                        ) }}"
                        enctype="multipart/form-data"
                        id="completeRegistrationForm"
                    >
                        @csrf

                        <h5 class="border-bottom pb-2 mb-3">
                            1. Customer Login Account
                        </h5>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">
                                    Account Holder Name *
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old(
                                        'name',
                                        $customer->contact_person
                                    ) }}"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Login Email *
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old(
                                        'email',
                                        $customer->email
                                    ) }}"
                                    required
                                >

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Login Phone *
                                </label>

                                <input
                                    type="text"
                                    name="phone_number"
                                    class="form-control @error('phone_number') is-invalid @enderror"
                                    value="{{ old(
                                        'phone_number',
                                        $customer->phone
                                    ) }}"
                                    required
                                >

                                @error('phone_number')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Password *
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Confirm Password *
                                </label>

                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >
                            </div>

                        </div>

                        <h5 class="border-bottom pb-2 mt-4 mb-3">
                            2. Customer / Company Details
                        </h5>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">
                                    Company Name *
                                </label>

                                <input
                                    type="text"
                                    name="company_name"
                                    class="form-control"
                                    value="{{ old(
                                        'company_name',
                                        $customer->company_name
                                    ) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Contact Person *
                                </label>

                                <input
                                    type="text"
                                    name="contact_person"
                                    class="form-control"
                                    value="{{ old(
                                        'contact_person',
                                        $customer->contact_person
                                    ) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Company Phone *
                                </label>

                                <input
                                    type="text"
                                    name="company_phone"
                                    class="form-control"
                                    value="{{ old(
                                        'company_phone',
                                        $customer->phone
                                    ) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Company Email
                                </label>

                                <input
                                    type="email"
                                    name="company_email"
                                    class="form-control"
                                    value="{{ old(
                                        'company_email',
                                        $customer->email
                                    ) }}"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    KRA PIN *
                                </label>

                                <input
                                    type="text"
                                    name="tax_id"
                                    class="form-control text-uppercase"
                                    value="{{ old(
                                        'tax_id',
                                        $customer->tax_id
                                    ) }}"
                                    placeholder="A123456789B"
                                    maxlength="20"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    KRA PIN Certificate *
                                </label>

                                <input
                                    type="file"
                                    name="kra_pin_certificate"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required
                                >

                                <div class="form-text">
                                    PDF, JPG or PNG. Maximum 5 MB.
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Address *
                                </label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    required
                                >{{ old(
                                    'address',
                                    $customer->address
                                ) }}</textarea>
                            </div>

                        </div>

                        <h5 class="border-bottom pb-2 mt-4 mb-3">
                            3. VAT Exemption
                        </h5>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">
                                    Request VAT Exemption? *
                                </label>

                                <select
                                    name="vat_exemption_requested"
                                    id="vat_exemption_requested"
                                    class="form-select"
                                    required
                                >
                                    <option value="0"
                                        @selected(
                                            old(
                                                'vat_exemption_requested',
                                                '0'
                                            ) == '0'
                                        )
                                    >
                                        No
                                    </option>

                                    <option value="1"
                                        @selected(
                                            old(
                                                'vat_exemption_requested',
                                                '0'
                                            ) == '1'
                                        )
                                    >
                                        Yes
                                    </option>
                                </select>
                            </div>

                        </div>

                        <div
                            id="vatExemptionFields"
                            class="row g-3 mt-1"
                            style="display:none;"
                        >

                            <div class="col-md-6">
                                <label class="form-label">
                                    VAT Exemption Certificate Number *
                                </label>

                                <input
                                    type="text"
                                    name="vat_exemption_certificate_number"
                                    id="vat_exemption_certificate_number"
                                    class="form-control"
                                    value="{{ old(
                                        'vat_exemption_certificate_number'
                                    ) }}"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Certificate Expiry Date *
                                </label>

                                <input
                                    type="date"
                                    name="vat_exemption_expiry_date"
                                    id="vat_exemption_expiry_date"
                                    class="form-control"
                                    min="{{ now()->toDateString() }}"
                                    value="{{ old(
                                        'vat_exemption_expiry_date'
                                    ) }}"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    VAT Exemption Certificate *
                                </label>

                                <input
                                    type="file"
                                    name="vat_exemption_certificate"
                                    id="vat_exemption_certificate"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                >

                                <div class="form-text">
                                    PDF, JPG or PNG. Maximum 5 MB.
                                </div>
                            </div>

                        </div>

                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-shield-alt me-2"></i>
                            Completing registration does not approve
                            the customer account or VAT exemption.
                        </div>

                        <div class="d-flex justify-content-between mt-4">

                            <a
                                href="{{ route('admin.customers.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                id="submitRegistration"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-check-circle me-1"></i>
                                Complete Registration
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const vatSelect = document.getElementById(
        'vat_exemption_requested'
    );

    const vatFields = document.getElementById(
        'vatExemptionFields'
    );

    const requiredFields = [
        'vat_exemption_certificate_number',
        'vat_exemption_expiry_date',
        'vat_exemption_certificate'
    ];

    function toggleVatFields() {

        const requested = vatSelect.value === '1';

        vatFields.style.display = requested
            ? ''
            : 'none';

        requiredFields.forEach(function (id) {

            const field = document.getElementById(id);

            if (field) {
                field.required = requested;
            }
        });
    }

    vatSelect.addEventListener(
        'change',
        toggleVatFields
    );

    toggleVatFields();

    const form = document.getElementById(
        'completeRegistrationForm'
    );

    const submitButton = document.getElementById(
        'submitRegistration'
    );

    form.addEventListener('submit', function () {

        if (!form.checkValidity()) {
            return;
        }

        submitButton.disabled = true;

        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>'
            + 'Processing Registration...';
    });

});
</script>
@endpush
