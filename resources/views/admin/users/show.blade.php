@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-user-circle text-primary me-2"></i>
                User Details
            </h1>
            <p class="text-muted mb-0">
                View user account details for {{ $user->name }}
            </p>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning ms-2">
                <i class="fas fa-edit me-1"></i> Edit User
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle img-fluid"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                 style="width: 150px; height: 150px; font-size: 4rem; color: white;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->designation ?? 'No Designation' }}</p>
                    <p class="text-muted">
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge bg-primary ms-1">{{ ucfirst($user->role) }}</span>
                    </p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-chart-bar text-primary me-1"></i>
                        Quick Stats
                    </h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5>{{ $user->performed_calibrations_count ?? 0 }}</h5>
                            <small class="text-muted">Calibrations</small>
                        </div>
                        <div class="col-6">
                            <h5>{{ $user->generated_certificates_count ?? 0 }}</h5>
                            <small class="text-muted">Certificates</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Full Name</label>
                            <p class="mb-0"><strong>{{ $user->name }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Staff Number</label>
                            <p class="mb-0"><strong>{{ $user->staff_number ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email Address</label>
                            <p class="mb-0"><strong>
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phone Number</label>
                            <p class="mb-0"><strong>{{ $user->phone_number ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Department</label>
                            <p class="mb-0"><strong>{{ $user->department ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Designation</label>
                            <p class="mb-0"><strong>{{ $user->designation ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Station</label>
                            <p class="mb-0"><strong>{{ $user->station ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Role</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-1"></i>
                        Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $user->status ? 'success' : 'danger' }}">
                                    {{ $user->status ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Account Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Email Verified</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                                    {{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Last Login</label>
                            <p class="mb-0"><strong>{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : 'Never' }}</strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Last Activity</label>
                            <p class="mb-0"><strong>{{ $user->last_activity_at ? $user->last_activity_at->format('Y-m-d H:i') : 'Never' }}</strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Auto-Signature</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $user->use_auto_signature ? 'success' : 'secondary' }}">
                                    {{ $user->use_auto_signature ? 'Enabled' : 'Disabled' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Digital Signature -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-signature me-1"></i>
                        Digital Signature
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->signature_path)
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $user->signature_path) }}"
                                 alt="{{ $user->name }} Signature"
                                 style="max-height: 100px; max-width: 300px; border: 1px solid #ddd; padding: 10px; background: white;">
                            <p class="text-muted small mt-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Signature uploaded on {{ $user->signature_updated_at ? $user->signature_updated_at->format('Y-m-d H:i') : 'N/A' }}
                            </p>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            No signature uploaded yet.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Activity Log -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-1"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($activities) && $activities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action</th>
                                        <th>Module</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activities->take(10) as $activity)
                                        <tr>
                                            <td>{{ $activity->action }}</td>
                                            <td>{{ $activity->module }}</td>
                                            <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted d-block mb-2"></i>
                            <p class="text-muted">No recent activity found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-3">
                <div class="btn-group">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit User
                    </a>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 12px;
    overflow: hidden;
}
</style>
@endpush
