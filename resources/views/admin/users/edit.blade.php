@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-user-edit text-primary me-2"></i>
                Edit User
            </h1>
            <p class="text-muted mb-0">
                Edit user account details and assigned roles for {{ $user->name }}.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-1"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Personal Information
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}"
                                           required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Staff Number</label>
                                    <input type="text" name="staff_number"
                                           class="form-control @error('staff_number') is-invalid @enderror"
                                           value="{{ old('staff_number', $user->staff_number) }}"
                                           placeholder="e.g., KPLC/1234">
                                    @error('staff_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone_number"
                                           class="form-control @error('phone_number') is-invalid @enderror"
                                           value="{{ old('phone_number', $user->phone_number) }}">
                                    @error('phone_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department"
                                           class="form-control @error('department') is-invalid @enderror"
                                           value="{{ old('department', $user->department) }}">
                                    @error('department')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation"
                                           class="form-control @error('designation') is-invalid @enderror"
                                           value="{{ old('designation', $user->designation) }}">
                                    @error('designation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Station</label>
                                    <input type="text" name="station"
                                           class="form-control @error('station') is-invalid @enderror"
                                           value="{{ old('station', $user->station) }}">
                                    @error('station')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-cog text-primary me-1"></i>
                                    Account Settings
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Leave blank to keep current password">
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted">
                                        Leave blank to keep current password.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Roles <span class="text-danger">*</span>
                                    </label>

                                    <select name="roles[]"
                                            class="form-select @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror"
                                            multiple
                                            required
                                            size="7">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ in_array($role->name, old('roles', $userRoles ?? [])) ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('roles')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror

                                    @error('roles.*')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror

                                    <small class="text-muted">
                                        Hold CTRL and click to select multiple roles.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active"
                                               class="form-check-input"
                                               id="isActive"
                                               value="1"
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isActive">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Active Account
                                        </label>
                                    </div>
                                    <small class="text-muted">Enable or disable user account access.</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="status"
                                               class="form-check-input"
                                               id="status"
                                               value="1"
                                               {{ old('status', $user->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Status Active
                                        </label>
                                    </div>
                                    <small class="text-muted">Enable or disable user status.</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="use_auto_signature"
                                               class="form-check-input"
                                               id="useAutoSignature"
                                               value="1"
                                               {{ old('use_auto_signature', $user->use_auto_signature) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="useAutoSignature">
                                            <i class="fas fa-robot text-info me-1"></i>
                                            Enable Auto-Signature
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        When enabled, signature will be automatically applied to certificates.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light mt-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-signature text-primary me-1"></i>
                                    Digital Signature
                                </h6>

                                @if($user->signature_path)
                                    <div class="mb-3">
                                        <label class="form-label">Current Signature:</label>
                                        <div class="border p-2 rounded text-center bg-white">
                                            <img src="{{ asset('storage/' . $user->signature_path) }}"
                                                 alt="{{ $user->name }} Signature"
                                                 style="max-height: 80px; max-width: 200px;">
                                        </div>
                                        <small class="text-muted">
                                            Upload a new signature to replace the current one.
                                        </small>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Upload New Signature</label>
                                    <input type="file" name="signature"
                                           class="form-control @error('signature') is-invalid @enderror"
                                           accept="image/png,image/jpeg,image/jpg,image/gif">
                                    @error('signature')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted">
                                        Upload PNG, JPG, JPEG, or GIF. Maximum size: 2MB.
                                    </small>
                                </div>

                                @if($user->signature_updated_at)
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Last updated: {{ $user->signature_updated_at->format('Y-m-d H:i') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update User
                    </button>

                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card.bg-light {
    background-color: #f8f9fa !important;
}

select[multiple] {
    min-height: 180px;
}
</style>
@endpush
