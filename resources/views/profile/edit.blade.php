@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- Page header --}}
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-cog text-primary me-2"></i>
                        My Profile
                    </h1>

                    <p class="text-muted mb-0">
                        Update your account details, signature, auto-signature preference, and password.
                    </p>
                </div>

                <button
    type="button"
    class="btn btn-outline-secondary"
    onclick="goBackOrDashboard()"
>
    <i class="fas fa-arrow-left me-1"></i>
    Back
</button>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i>
                    {{ session('success') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ session('error') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-bold mb-2">
                        Please correct the following:
                    </div>

                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">

                {{-- Left summary card --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="profile-avatar mx-auto mb-3">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>

                            <h4 class="mb-1">
                                {{ $user->name }}
                            </h4>

                            <p class="text-muted mb-2">
                                {{ $user->designation ?? 'Designation not set' }}
                            </p>

                            @if($user->staff_number)
                                <span class="badge bg-primary mb-3">
                                    Staff No: {{ $user->staff_number }}
                                </span>
                            @endif

                            <div class="profile-summary text-start mt-3">
                                <div class="summary-row">
                                    <span class="summary-label">
                                        <i class="fas fa-envelope me-2"></i>
                                        Email
                                    </span>

                                    <span class="summary-value">
                                        {{ $user->email }}
                                    </span>
                                </div>

                                <div class="summary-row">
                                    <span class="summary-label">
                                        <i class="fas fa-phone me-2"></i>
                                        Phone
                                    </span>

                                    <span class="summary-value">
                                        {{ $user->phone_number ?? 'Not set' }}
                                    </span>
                                </div>

                                <div class="summary-row">
                                    <span class="summary-label">
                                        <i class="fas fa-building me-2"></i>
                                        Department
                                    </span>

                                    <span class="summary-value">
                                        {{ $user->department ?? 'Not set' }}
                                    </span>
                                </div>

                                <div class="summary-row">
                                    <span class="summary-label">
                                        <i class="fas fa-user-tag me-2"></i>
                                        Role
                                    </span>

                                    <span class="summary-value">
                                        {{ $user->role_display ?? ucfirst($user->role ?? 'User') }}
                                    </span>
                                </div>
                            </div>

                            <hr>

                            <div class="text-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">
                                        Auto-Signature
                                    </span>

                                    @if($user->use_auto_signature)
                                        <span class="badge bg-success">
                                            Enabled
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Disabled
                                        </span>
                                    @endif
                                </div>

                                <small class="text-muted d-block mt-2">
                                    Your stored signature may be inserted automatically into supported laboratory documents.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right profile forms --}}
                <div class="col-lg-8">

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="details-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#details-pane"
                                type="button"
                                role="tab"
                            >
                                <i class="fas fa-id-card me-1"></i>
                                Personal Details
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="signature-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#signature-pane"
                                type="button"
                                role="tab"
                            >
                                <i class="fas fa-signature me-1"></i>
                                Signature
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="password-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#password-pane"
                                type="button"
                                role="tab"
                            >
                                <i class="fas fa-lock me-1"></i>
                                Password
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- Personal details --}}
                        <div
                            class="tab-pane fade show active"
                            id="details-pane"
                            role="tabpanel"
                        >
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        Personal Information
                                    </h5>
                                </div>

                                <div class="card-body">
                                    <form
                                        action="{{ route('profile.update') }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="name" class="form-label">
                                                    Full Name
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input
                                                    type="text"
                                                    id="name"
                                                    name="name"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    value="{{ old('name', $user->name) }}"
                                                    required
                                                >

                                                @error('name')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="email" class="form-label">
                                                    Email Address
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input
                                                    type="email"
                                                    id="email"
                                                    name="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    value="{{ old('email', $user->email) }}"
                                                    required
                                                >

                                                @error('email')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="phone_number" class="form-label">
                                                    Phone Number
                                                </label>

                                                <input
                                                    type="text"
                                                    id="phone_number"
                                                    name="phone_number"
                                                    class="form-control @error('phone_number') is-invalid @enderror"
                                                    value="{{ old('phone_number', $user->phone_number) }}"
                                                >

                                                @error('phone_number')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="staff_number" class="form-label">
                                                    Staff Number
                                                </label>

                                                <input
                                                    type="text"
                                                    id="staff_number"
                                                    name="staff_number"
                                                    class="form-control @error('staff_number') is-invalid @enderror"
                                                    value="{{ old('staff_number', $user->staff_number) }}"
                                                >

                                                @error('staff_number')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="designation" class="form-label">
                                                    Designation
                                                </label>

                                                <input
                                                    type="text"
                                                    id="designation"
                                                    name="designation"
                                                    class="form-control @error('designation') is-invalid @enderror"
                                                    value="{{ old('designation', $user->designation) }}"
                                                >

                                                @error('designation')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="department" class="form-label">
                                                    Department
                                                </label>

                                                <input
                                                    type="text"
                                                    id="department"
                                                    name="department"
                                                    class="form-control @error('department') is-invalid @enderror"
                                                    value="{{ old('department', $user->department) }}"
                                                >

                                                @error('department')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                Update Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Signature --}}
                        <div
                            class="tab-pane fade"
                            id="signature-pane"
                            role="tabpanel"
                        >
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        Digital Signature
                                    </h5>
                                </div>

                                <div class="card-body">
                                    <div class="signature-preview mb-4">
                                        @if($user->signature_path)
                                            <img
                                                src="{{ asset('storage/' . $user->signature_path) }}"
                                                alt="Current signature"
                                                class="img-fluid signature-preview-image"
                                            >
                                        @else
                                            <div class="text-muted text-center">
                                                <i class="fas fa-signature fa-3x mb-2"></i>
                                                <div>No signature uploaded</div>
                                            </div>
                                        @endif
                                    </div>

                                    <form
                                        action="{{ route('profile.signature.upload') }}"
                                        method="POST"
                                        enctype="multipart/form-data"
                                    >
                                        @csrf

                                        <div class="mb-3">
                                            <label for="signature" class="form-label">
                                                {{ $user->signature_path
                                                    ? 'Replace Signature'
                                                    : 'Upload Signature' }}
                                            </label>

                                            <input
                                                type="file"
                                                id="signature"
                                                name="signature"
                                                class="form-control @error('signature') is-invalid @enderror"
                                                accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                                                required
                                            >

                                            @error('signature')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <div class="form-text">
                                                Accepted formats: PNG, JPG, JPEG. Maximum size: 2 MB.
                                                A transparent PNG gives the best PDF result.
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-upload me-1"></i>
                                            {{ $user->signature_path
                                                ? 'Replace Signature'
                                                : 'Upload Signature' }}
                                        </button>
                                    </form>

                                    @if($user->signature_path)
                                        <form
                                            action="{{ route('profile.signature.delete') }}"
                                            method="POST"
                                            class="mt-3"
                                            onsubmit="return confirm('Delete your stored signature?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i>
                                                Delete Signature
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        Auto-Signature
                                    </h5>
                                </div>

                                <div class="card-body">
                                    <form
                                        action="{{ route('profile.auto-signature.update') }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="form-check form-switch">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="use_auto_signature"
                                                name="use_auto_signature"
                                                value="1"
                                                {{ old(
                                                    'use_auto_signature',
                                                    $user->use_auto_signature
                                                ) ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label fw-semibold"
                                                for="use_auto_signature"
                                            >
                                                Enable Auto-Signature
                                            </label>
                                        </div>

                                        <p class="text-muted mt-3 mb-3">
                                            When enabled, your stored signature may be inserted automatically into certificates, job cards, request forms, delivery notes, and collection notes where your approval or acknowledgement is required.
                                        </p>

                                        @if(!$user->signature_path)
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Upload a signature before enabling auto-signature.
                                            </div>
                                        @endif

                                        <button
                                            type="submit"
                                            class="btn btn-success"
                                            {{ !$user->signature_path ? 'disabled' : '' }}
                                        >
                                            <i class="fas fa-toggle-on me-1"></i>
                                            Save Auto-Signature Setting
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div
                            class="tab-pane fade"
                            id="password-pane"
                            role="tabpanel"
                        >
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        Change Password
                                    </h5>
                                </div>

                                <div class="card-body">
                                    <form
                                        action="{{ route('profile.password.update') }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">
                                                Current Password
                                            </label>

                                            <input
                                                type="password"
                                                id="current_password"
                                                name="current_password"
                                                class="form-control @error('current_password') is-invalid @enderror"
                                                required
                                                autocomplete="current-password"
                                            >

                                            @error('current_password')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">
                                                New Password
                                            </label>

                                            <input
                                                type="password"
                                                id="password"
                                                name="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                required
                                                autocomplete="new-password"
                                            >

                                            @error('password')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">
                                                Confirm New Password
                                            </label>

                                            <input
                                                type="password"
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                class="form-control"
                                                required
                                                autocomplete="new-password"
                                            >
                                        </div>

                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key me-1"></i>
                                            Change Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function goBackOrDashboard() {
    const dashboard = @json(
        auth()->user()->hasRole('customer')
            ? route('customer.dashboard')
            : route('dashboard')
    );

    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = dashboard;
    }
}
</script>

@push('styles')
<style>
    .profile-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a237e, #0d6efd);
        color: #fff;
        font-size: 2.5rem;
        font-weight: 700;
        box-shadow: 0 8px 24px rgba(26, 35, 126, 0.2);
    }

    .profile-summary {
        border-top: 1px solid #eee;
    }

    .summary-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .summary-label {
        display: block;
        color: #6c757d;
        font-size: 0.8rem;
    }

    .summary-value {
        display: block;
        margin-top: 0.15rem;
        color: #212529;
        font-weight: 600;
        word-break: break-word;
    }

    .signature-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        padding: 1rem;
        border: 2px dashed #ced4da;
        border-radius: 0.75rem;
        background: #fafafa;
    }

    .signature-preview-image {
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
    }

    #profileTabs .nav-link {
        color: #495057;
        font-weight: 600;
    }

    #profileTabs .nav-link.active {
        color: #1a237e;
    }
</style>
@endpush
