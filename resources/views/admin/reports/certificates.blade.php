@extends('layouts.app')

@section('title', 'Certificates Report - KPLC Meter Lab')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                Certificates Report
            </h1>
            <p class="text-muted mb-0">
                Certificate generation and status report
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

    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Certificate List
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Certificate #</th>
                            <th>Calibration ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Machine Used</th>
                            <th>Test Engineer</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <code>{{ $certificate->certificate_number }}</code>
                                </td>
                                <td>{{ $certificate->calibration_id ?? 'N/A' }}</td>
                                <td>
                                    {{ optional($certificate->calibration->customer)->company_name ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $certificate->status === 'approved' ? 'success' : ($certificate->status === 'sent' ? 'info' : 'warning') }}">
                                        {{ ucfirst($certificate->status ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>{{ $certificate->machine_used ?? 'N/A' }}</td>
                                <td>{{ $certificate->test_engineer ?? 'N/A' }}</td>
                                <td>{{ $certificate->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-file-pdf fa-2x text-muted d-block mb-2"></i>
                                    No certificates found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    {{ $certificates->appends(request()->query())->links() }}
</div>
@endsection
