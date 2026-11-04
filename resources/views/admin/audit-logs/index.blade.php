@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-history text-primary me-2"></i>
                Audit Logs
            </h1>
            <p class="text-muted mb-0">
                Track all system activities and user actions
            </p>
        </div>
        <div>
            @if(Route::has('audit-logs.export'))
                <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn btn-success me-2">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
            @endif
            <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">
                <i class="fas fa-trash me-1"></i> Clear Old Logs
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst($action) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="model" class="form-select">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                            <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                                {{ $model }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>Model ID</th>
                            <th>IP Address</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    @if($log->user)
                                        <strong>{{ $log->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $log->user->staff_number ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $actionColors = [
                                            'create' => 'success',
                                            'created' => 'success',
                                            'update' => 'warning',
                                            'updated' => 'warning',
                                            'delete' => 'danger',
                                            'deleted' => 'danger',
                                            'login' => 'info',
                                            'logout' => 'secondary',
                                            'export' => 'primary',
                                            'import' => 'info',
                                            'view' => 'secondary',
                                            'viewed' => 'secondary',
                                        ];
                                        $color = $actionColors[$log->action] ?? 'info';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $modelName = $log->model_type;
                                        $parts = explode('\\', $modelName);
                                        $shortName = end($parts);
                                        $displayName = ucfirst(str_replace('_', ' ', $shortName));
                                    @endphp
                                    <span class="badge bg-secondary">{{ $displayName }}</span>
                                </td>
                                <td>
                                    <code>{{ $log->model_id ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    <code>{{ $log->ip_address ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(Route::has('audit-logs.destroy'))
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLogModal{{ $log->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Audit Log Details #{{ $log->id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>ID:</strong> {{ $log->id }}</p>
                                                    <p>
                                                        <strong>User:</strong>
                                                        {{ $log->user ? $log->user->name : 'System' }}
                                                        @if($log->user && $log->user->staff_number)
                                                            <br><small class="text-muted">Staff #: {{ $log->user->staff_number }}</small>
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <strong>Action:</strong>
                                                        <span class="badge bg-{{ $log->action === 'create' || $log->action === 'created' ? 'success' : ($log->action === 'update' || $log->action === 'updated' ? 'warning' : ($log->action === 'delete' || $log->action === 'deleted' ? 'danger' : 'info')) }}">
                                                            {{ ucfirst($log->action) }}
                                                        </span>
                                                    </p>
                                                    <p>
                                                        <strong>Model:</strong>
                                                        @php
                                                            $modelName = $log->model_type;
                                                            $parts = explode('\\', $modelName);
                                                            $shortName = end($parts);
                                                        @endphp
                                                        {{ $shortName }}
                                                    </p>
                                                    <p><strong>Model ID:</strong> {{ $log->model_id ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>IP Address:</strong> {{ $log->ip_address ?? 'N/A' }}</p>
                                                    <p><strong>URL:</strong> <small class="text-break">{{ $log->url ?? 'N/A' }}</small></p>
                                                    <p><strong>Method:</strong> {{ $log->method ?? 'N/A' }}</p>
                                                    <p><strong>User Agent:</strong> <small class="text-break">{{ $log->user_agent ?? 'N/A' }}</small></p>
                                                    <p><strong>Created:</strong> {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                                                </div>
                                                @if($log->old_values || $log->new_values)
                                                    <div class="col-12">
                                                        <hr>
                                                        <div class="row">
                                                            @if($log->old_values)
                                                                <div class="col-md-6">
                                                                    <h6>Old Values</h6>
                                                                    <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto; font-size: 11px;">{{ is_array($log->old_values) ? json_encode($log->old_values, JSON_PRETTY_PRINT) : $log->old_values }}</pre>
                                                                </div>
                                                            @endif
                                                            @if($log->new_values)
                                                                <div class="col-md-6">
                                                                    <h6>New Values</h6>
                                                                    <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto; font-size: 11px;">{{ is_array($log->new_values) ? json_encode($log->new_values, JSON_PRETTY_PRINT) : $log->new_values }}</pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            @if(Route::has('audit-logs.destroy'))
                                <div class="modal fade" id="deleteLogModal{{ $log->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('audit-logs.destroy', $log->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Audit Log</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this audit log?</p>
                                                    <p class="text-muted small">
                                                        <strong>ID:</strong> {{ $log->id }}<br>
                                                        <strong>User:</strong> {{ $log->user ? $log->user->name : 'System' }}<br>
                                                        <strong>Action:</strong> {{ $log->action }}<br>
                                                        <strong>Model:</strong> {{ $log->model_type }}<br>
                                                        <strong>Model ID:</strong> {{ $log->model_id ?? 'N/A' }}<br>
                                                        <strong>Date:</strong> {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted d-block mb-2"></i>
                                    <p class="text-muted">No audit logs found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <span class="text-muted">
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs
            </span>
        </div>
        <div>
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Clear Logs Confirmation -->
@push('scripts')
<script>
function confirmClearLogs() {
    const days = prompt('Delete audit logs older than how many days? (Enter a number between 1 and 365)', '30');
    if (days !== null && days !== '') {
        const numDays = parseInt(days);
        if (!isNaN(numDays) && numDays > 0 && numDays <= 365) {
            if (confirm('Are you sure you want to delete audit logs older than ' + numDays + ' days?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("audit-logs.clear") }}';
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                                '<input type="hidden" name="days" value="' + numDays + '">';
                document.body.appendChild(form);
                form.submit();
            }
        } else {
            alert('Please enter a valid number between 1 and 365.');
        }
    }
}
</script>
@endpush

@push('styles')
<style>
    .text-break {
        word-break: break-all;
        overflow-wrap: break-word;
    }
    .table td {
        vertical-align: middle;
    }
    pre {
        font-size: 11px;
        max-height: 200px;
        overflow-y: auto;
    }
</style>
@endpush
@endsection
