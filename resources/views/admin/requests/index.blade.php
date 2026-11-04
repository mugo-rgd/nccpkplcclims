@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Customer Requests</h1>
                <a href="{{ route('requests.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Request
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by Request #, Serial #, Customer..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
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
                            <th>Request #</th>
                            <th>Customer</th>
                            <th>Equipment</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Quote</th>
                            <th>Job Card</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td><strong>{{ $request->request_number }}</strong></td>
                            <td>{{ $request->customer->company_name ?? 'N/A' }}</td>
                            <td>
                                @if($request->serial_number)
                                    {{ $request->serial_number }}
                                @else
                                    {{ $request->equipment_type ?? 'N/A' }}
                                @endif
                            </td>
                            <td>{{ ucfirst($request->service_type) }}</td>
                            <td>
                                <span class="badge bg-{{ $request->status_color }}">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($request->quote)
                                    <span class="badge bg-info">{{ $request->quote->quote_number }}</span>
                                @else
                                    <span class="text-muted">No quote</span>
                                @endif
                            </td>
                            <td>
                                @if($request->jobCard)
                                    <span class="badge bg-success">{{ $request->jobCard->job_card_number }}</span>
                                @else
                                    <span class="text-muted">No job card</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit_customers')
                                    <a href="{{ route('requests.edit', $request) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
