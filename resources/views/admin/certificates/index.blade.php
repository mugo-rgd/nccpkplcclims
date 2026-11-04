@extends('layouts.app')

@section('title', 'Certificates')

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h1 class="mb-1">
                        <i class="fas fa-file-pdf text-danger me-2"></i>
                        Certificates
                        <span class="badge bg-secondary">{{ $certificates->total() }}</span>
                    </h1>
                    <p class="text-muted mb-0">
                        Manage calibration certificates, approvals, and customer delivery.
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-3 g-2">
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <h6 class="card-title">Total</h6>
                            <h3 class="mb-0">{{ $certificates->total() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <h6 class="card-title">Pending</h6>
                            <h3 class="mb-0">{{ $certificates->where('status', 'draft')->count() + $certificates->where('status', 'generated')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <h6 class="card-title">Checked</h6>
                            <h3 class="mb-0">{{ $certificates->where('status', 'checked')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <h6 class="card-title">Approved/Sent</h6>
                            <h3 class="mb-0">{{ $certificates->where('status', 'approved')->count() + $certificates->where('status', 'sent')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 g-md-3">
                        <div class="col-md-3 col-sm-6">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by certificate # or equipment..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <a href="{{ route('certificates.index') }}" class="btn btn-secondary btn-sm w-100">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Certificate #</th>
                                    <th>Equipment</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Test Result</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($certificates as $certificate)
                                <tr>
                                    <td>{{ $certificate->id }}</td>
                                    <td>
                                        <code>{{ $certificate->certificate_number }}</code>
                                    </td>
                                    <td>{{ $certificate->equipment_name ?? 'N/A' }}</td>
                                    <td>{{ optional($certificate->customer)->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $certificate->status_color }}">
                                            {{ $certificate->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $certificate->test_result === 'Pass' ? 'success' : 'danger' }}">
                                            {{ $certificate->test_result ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $certificate->created_at->format('Y-m-d') }}</td>
                                    <td>
    <div class="btn-group" role="group">
        <a
            href="{{ route('certificates.show', $certificate) }}"
            class="btn btn-sm btn-info"
            title="View Certificate"
        >
            <i class="fas fa-eye"></i>
        </a>

        <a
            href="{{ route('certificates.preview', $certificate) }}"
            target="_blank"
            class="btn btn-sm btn-primary"
            title="Preview PDF"
        >
            <i class="fas fa-file-pdf"></i>
        </a>

        <a
            href="{{ route('certificates.download', [
                'certificate' => $certificate,
                'force' => true,
            ]) }}"
            class="btn btn-sm btn-success"
            title="Regenerate and Download PDF"
        >
            <i class="fas fa-download"></i>
        </a>
    </div>
</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-file-pdf fa-3x text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">No certificates found</h5>
                                        <p class="text-muted">Certificates will appear here once generated.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                <div>
                    <span class="text-muted">
                        Showing <strong>{{ $certificates->firstItem() ?? 0 }}</strong>
                        to <strong>{{ $certificates->lastItem() ?? 0 }}</strong>
                        of <strong>{{ $certificates->total() }}</strong> certificates
                    </span>
                </div>
                <div>
                    {{ $certificates->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
