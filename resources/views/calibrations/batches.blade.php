@extends('layouts.app')

@section('title', 'Batches')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-layer-group text-primary"></i> Batches
        </h1>
        <a href="{{ route('calibrations.bulk-upload') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> New Batch Upload
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">All Customers</option>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}" {{ request('customer_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batches Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Batches
                <span class="badge bg-primary float-right">{{ $batches->count() }} Batches</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Batch ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status Distribution</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $batch['batch_id'] }}</span>
                            </td>
                            <td>{{ $batch['customer'] }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $batch['total'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($batch['statuses'] as $status => $count)
                                        <span class="badge bg-{{ $status == 'completed' ? 'success' : ($status == 'pending' ? 'warning' : ($status == 'rejected' ? 'danger' : 'info')) }}">
                                            {{ ucfirst($status) }}: {{ $count }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $batch['created_at']->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('calibrations.batch.details', $batch['batch_id']) }}"
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-danger"
                                        onclick="confirmDelete('{{ $batch['batch_id'] }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-gray-300 d-block mb-3"></i>
                                <p class="h5">No batches found</p>
                                <p class="text-muted">Upload your first batch to get started.</p>
                                <a href="{{ route('calibrations.bulk-upload') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-upload"></i> Upload Batch
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this batch?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
                <p><strong>Batch ID:</strong> <span id="deleteBatchId"></span></p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Batch</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(batchId) {
    if (!batchId) {
        alert('Invalid batch ID');
        return;
    }

    // Set batch ID in the modal
    document.getElementById('deleteBatchId').textContent = batchId;

    // Set form action
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('calibrations.batch.delete', ':batch_id') }}".replace(':batch_id', batchId);

    // Show modal
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
