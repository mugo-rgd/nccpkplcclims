@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Delivery & Collection Records</h1>
                <a href="{{ route('deliveries.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Record
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by #, Contact..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Request #</th>
                            <th>Contact</th>
                            <th>Scheduled</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td><strong>{{ $record->delivery_number }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $record->type === 'delivery' ? 'info' : 'warning' }}">
                                    {{ ucfirst($record->type) }}
                                </span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $record->method)) }}</td>
                            <td>{{ $record->customerRequest->request_number ?? 'N/A' }}</td>
                            <td>{{ $record->contact_person ?? 'N/A' }}</td>
                            <td>{{ $record->scheduled_at ? $record->scheduled_at->format('Y-m-d') : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $record->status === 'completed' || $record->status === 'delivered' || $record->status === 'collected' ? 'success' : ($record->status === 'scheduled' ? 'warning' : ($record->status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td>{{ $record->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('deliveries.show', $record) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit_customers')
                                    <a href="{{ route('deliveries.edit', $record) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No delivery records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
