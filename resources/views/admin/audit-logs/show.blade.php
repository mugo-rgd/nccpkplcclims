@extends('layouts.app')

@section('title', 'Audit Log Details #' . $auditLog->id)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">
                        <i class="fas fa-history text-primary me-2"></i>
                        Audit Log Details #{{ $auditLog->id }}
                    </h3>
                </div>
                <div>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Logs
                    </a>
                    @if(Route::has('audit-logs.destroy'))
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i>
                            Delete Log
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-info-circle me-1"></i>
                            Basic Information
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th style="width: 30%;">Log ID</th>
                                    <td><strong>#{{ $auditLog->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Action</th>
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
                                            $color = $actionColors[$auditLog->action] ?? 'info';
                                        @endphp
                                        <span class="badge bg-{{ $color }} fs-6">
                                            {{ ucfirst($auditLog->action) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($auditLog->user)
                                            <strong>{{ $auditLog->user->name }}</strong>
                                            @if($auditLog->user->staff_number)
                                                <br>
                                                <small class="text-muted">Staff #: {{ $auditLog->user->staff_number }}</small>
                                            @endif
                                            @if($auditLog->user->email)
                                                <br>
                                                <small class="text-muted">Email: {{ $auditLog->user->email }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">System (No user)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Model</th>
                                    <td>
                                        @php
                                            $modelName = $auditLog->model_type;
                                            $parts = explode('\\', $modelName);
                                            $shortName = end($parts);
                                            $displayName = ucfirst(str_replace('_', ' ', $shortName));
                                        @endphp
                                        <span class="badge bg-secondary">{{ $displayName }}</span>
                                        <br>
                                        <small class="text-muted">{{ $modelName }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Model ID</th>
                                    <td>
                                        @if($auditLog->model_id)
                                            <code>{{ $auditLog->model_id }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>
                                        {{ $auditLog->created_at ? $auditLog->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $auditLog->created_at ? $auditLog->created_at->diffForHumans() : '' }}
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>
                                        {{ $auditLog->updated_at ? $auditLog->updated_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        @if($auditLog->created_at && $auditLog->updated_at && $auditLog->created_at != $auditLog->updated_at)
                                            <br>
                                            <small class="text-muted">
                                                Updated {{ $auditLog->updated_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Request Information -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-network-wired me-1"></i>
                            Request Information
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th style="width: 30%;">IP Address</th>
                                    <td>
                                        @if($auditLog->ip_address)
                                            <code>{{ $auditLog->ip_address }}</code>
                                            <br>
                                            <small class="text-muted">
                                                @php
                                                    $ipDetails = '';
                                                    try {
                                                        $ip = $auditLog->ip_address;
                                                        if (filter_var($ip, FILTER_VALIDATE_IP)) {
                                                            if (strpos($ip, ':') !== false) {
                                                                $ipDetails = 'IPv6';
                                                            } else {
                                                                $ipDetails = 'IPv4';
                                                            }
                                                        }
                                                    } catch (\Exception $e) {
                                                        $ipDetails = '';
                                                    }
                                                @endphp
                                                {{ $ipDetails }}
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Method</th>
                                    <td>
                                        @if($auditLog->method)
                                            <span class="badge bg-{{ $auditLog->method === 'GET' ? 'success' : ($auditLog->method === 'POST' ? 'primary' : ($auditLog->method === 'PUT' || $auditLog->method === 'PATCH' ? 'warning' : ($auditLog->method === 'DELETE' ? 'danger' : 'secondary'))) }}">
                                                {{ $auditLog->method }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>URL</th>
                                    <td>
                                        @if($auditLog->url)
                                            <div class="text-break">
                                                <code class="small">{{ $auditLog->url }}</code>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>User Agent</th>
                                    <td>
                                        @if($auditLog->user_agent)
                                            <div class="text-break">
                                                <small>{{ $auditLog->user_agent }}</small>
                                            </div>
                                            <br>
                                            <small class="text-muted">
                                                @php
                                                    $userAgent = $auditLog->user_agent;
                                                    $browser = 'Unknown';
                                                    $os = 'Unknown';

                                                    if (strpos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
                                                    elseif (strpos($userAgent, 'Firefox') !== false) $browser = 'Firefox';
                                                    elseif (strpos($userAgent, 'Safari') !== false) $browser = 'Safari';
                                                    elseif (strpos($userAgent, 'Edge') !== false) $browser = 'Edge';
                                                    elseif (strpos($userAgent, 'Opera') !== false) $browser = 'Opera';

                                                    if (strpos($userAgent, 'Windows') !== false) $os = 'Windows';
                                                    elseif (strpos($userAgent, 'Macintosh') !== false) $os = 'macOS';
                                                    elseif (strpos($userAgent, 'Linux') !== false) $os = 'Linux';
                                                    elseif (strpos($userAgent, 'Android') !== false) $os = 'Android';
                                                    elseif (strpos($userAgent, 'iPhone') !== false) $os = 'iOS';
                                                @endphp
                                                {{ $browser }} on {{ $os }}
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Changes (Old & New Values) -->
            @if($auditLog->old_values || $auditLog->new_values)
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-exchange-alt me-1"></i>
                        Changes Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($auditLog->old_values)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-danger text-white">
                                            <i class="fas fa-arrow-left me-1"></i>
                                            Old Values
                                        </div>
                                        <div class="card-body p-0">
                                            <pre class="p-3 mb-0" style="max-height: 400px; overflow-y: auto; font-size: 12px; background: #f8f9fa; border-radius: 0;">{{ is_array($auditLog->old_values) ? json_encode($auditLog->old_values, JSON_PRETTY_PRINT) : $auditLog->old_values }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($auditLog->new_values)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-arrow-right me-1"></i>
                                            New Values
                                        </div>
                                        <div class="card-body p-0">
                                            <pre class="p-3 mb-0" style="max-height: 400px; overflow-y: auto; font-size: 12px; background: #f8f9fa; border-radius: 0;">{{ is_array($auditLog->new_values) ? json_encode($auditLog->new_values, JSON_PRETTY_PRINT) : $auditLog->new_values }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($auditLog->old_values && $auditLog->new_values)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <i class="fas fa-code-branch me-1"></i>
                                            Changed Fields
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $old = is_array($auditLog->old_values) ? $auditLog->old_values : json_decode($auditLog->old_values, true);
                                                $new = is_array($auditLog->new_values) ? $auditLog->new_values : json_decode($auditLog->new_values, true);

                                                $changedFields = [];
                                                if ($old && $new) {
                                                    foreach ($new as $key => $value) {
                                                        if (isset($old[$key]) && $old[$key] != $value) {
                                                            $changedFields[$key] = ['old' => $old[$key], 'new' => $value];
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @if(!empty($changedFields))
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped table-hover">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Old Value</th>
                                                                <th>New Value</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($changedFields as $field => $values)
                                                                <tr>
                                                                    <td><strong>{{ $field }}</strong></td>
                                                                    <td class="text-danger">{{ $values['old'] ?? 'N/A' }}</td>
                                                                    <td class="text-success">{{ $values['new'] ?? 'N/A' }}</td>
                                                                    <td>
                                                                        <span class="badge bg-warning text-dark">
                                                                            <i class="fas fa-pen me-1"></i> Changed
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    No changed fields detected.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Related Actions -->
            @if($auditLog->model_id && $auditLog->model_type)
                <div class="card mt-3">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-link me-1"></i>
                        Related Actions
                    </div>
                    <div class="card-body">
                        @php
                            $relatedLogs = App\Models\AuditLog::where('model_type', $auditLog->model_type)
                                ->where('model_id', $auditLog->model_id)
                                ->where('id', '!=', $auditLog->id)
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        @if($relatedLogs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($relatedLogs as $log)
                                            <tr>
                                                <td>#{{ $log->id }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $actionColors[$log->action] ?? 'info' }}">
                                                        {{ ucfirst($log->action) }}
                                                    </span>
                                                </td>
                                                <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                                                <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('audit-logs.show', $log->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($relatedLogs->count() > 5)
                                <p class="text-muted text-center mt-2">
                                    <small>Showing 5 of {{ $relatedLogs->count() }} related logs</small>
                                </p>
                            @endif
                        @else
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                No related logs found.
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
@if(Route::has('audit-logs.destroy'))
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('audit-logs.destroy', $auditLog->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                            Delete Audit Log
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <strong>Warning:</strong> This action cannot be undone.
                        </div>
                        <p>Are you sure you want to delete this audit log?</p>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th style="width: 30%;">Log ID</th>
                                <td>#{{ $auditLog->id }}</td>
                            </tr>
                            <tr>
                                <th>Action</th>
                                <td>{{ ucfirst($auditLog->action) }}</td>
                            </tr>
                            <tr>
                                <th>User</th>
                                <td>{{ $auditLog->user ? $auditLog->user->name : 'System' }}</td>
                            </tr>
                            <tr>
                                <th>Model</th>
                                <td>
                                    @php
                                        $modelName = $auditLog->model_type;
                                        $parts = explode('\\', $modelName);
                                        $shortName = end($parts);
                                    @endphp
                                    {{ $shortName }}
                                </td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $auditLog->created_at ? $auditLog->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Delete Log
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .text-break {
        word-break: break-all;
        overflow-wrap: break-word;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    pre {
        font-size: 12px;
        margin: 0;
        background: #f8f9fa;
        border-radius: 0;
    }
    .card-header .badge {
        font-size: 0.9rem;
    }
    .table-responsive pre {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-expand pre tags for better readability
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre').forEach(function(pre) {
            if (pre.textContent.length > 5000) {
                pre.style.maxHeight = '300px';
                pre.style.overflowY = 'auto';

                // Add expand button
                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                pre.parentNode.insertBefore(wrapper, pre);
                wrapper.appendChild(pre);

                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-primary mt-2';
                btn.textContent = 'Expand All';
                btn.onclick = function() {
                    if (pre.style.maxHeight === 'none') {
                        pre.style.maxHeight = '300px';
                        this.textContent = 'Expand All';
                    } else {
                        pre.style.maxHeight = 'none';
                        this.textContent = 'Collapse';
                    }
                };
                wrapper.appendChild(btn);
            }
        });
    });
</script>
@endpush
