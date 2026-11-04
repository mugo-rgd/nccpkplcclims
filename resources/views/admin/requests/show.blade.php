@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Request Details: {{ $request->request_number }}</h2>
                    <div class="float-end">
                        <span class="badge bg-{{ $request->status_color }} fs-6">
                            Status: {{ $request->status_label }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Workflow Status Bar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-secondary" style="width: 14%">Request</div>
                                <div class="progress-bar bg-info" style="width: 14%">Quote</div>
                                <div class="progress-bar bg-primary" style="width: 14%">Approved</div>
                                <div class="progress-bar bg-success" style="width: 14%">Paid</div>
                                <div class="progress-bar bg-warning" style="width: 14%">Processing</div>
                                <div class="progress-bar bg-success" style="width: 14%">Completed</div>
                                <div class="progress-bar bg-success" style="width: 16%">Certificate</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Customer Info -->
                        <div class="col-md-6">
                            <h4>Customer Details</h4>
                            <p><strong>Company:</strong> {{ $request->customer->company_name ?? 'N/A' }}</p>
                            <p><strong>Contact:</strong> {{ $request->customer->contact_person ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $request->customer->phone ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $request->customer->email ?? 'N/A' }}</p>
                            <p><strong>Address:</strong> {{ $request->customer->address ?? 'N/A' }}</p>
                        </div>

                        <!-- Equipment Info -->
                        <div class="col-md-6">
                            <h4>Equipment Details</h4>
                            <p><strong>Equipment Type:</strong> {{ $request->equipment_type ?? 'N/A' }}</p>
                            <p><strong>Manufacturer:</strong> {{ $request->manufacturer ?? 'N/A' }}</p>
                            <p><strong>Model:</strong> {{ $request->model ?? 'N/A' }}</p>
                            <p><strong>Serial Number:</strong> {{ $request->serial_number ?? 'N/A' }}</p>
                            <p><strong>Ratings:</strong> {{ $request->ratings ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Service Details</h4>
                            <p><strong>Service Type:</strong> {{ ucfirst($request->service_type) }}</p>
                            <p><strong>Method:</strong> {{ $request->method ?? 'N/A' }}</p>
                            <p><strong>Description:</strong> {{ $request->description ?? 'N/A' }}</p>
                            <p><strong>Validity Period:</strong> {{ $request->validity_period_days ? $request->validity_period_days . ' days' : 'N/A' }}</p>
                            <p><strong>Request Date:</strong> {{ $request->request_date ? $request->request_date->format('Y-m-d H:i') : 'N/A' }}</p>
                        </div>
<!-- Add after Service Details section -->
<hr>
<div class="row">
    <div class="col-md-6">
        <h4>Calibration Location</h4>
        <p>
            <span class="badge bg-{{ $request->location_color }}">
                {{ $request->location_label }}
            </span>
        </p>
    </div>
    <div class="col-md-6">
        <h4>Actions</h4>

        <!-- Laboratory Actions -->
        @if($request->isLaboratoryCalibration())
            @if(!$request->hasEquipmentBeenReceived() && $request->status === 'paid')
                <a href="{{ route('requests.receive-equipment', $request) }}" class="btn btn-primary">
                    <i class="fas fa-box"></i> Receive Equipment
                </a>
            @endif

            @if($request->hasEquipmentBeenReceived() && !$request->hasEquipmentBeenCollected() && $request->status === 'completed')
                <a href="{{ route('requests.collect-equipment', $request) }}" class="btn btn-success">
                    <i class="fas fa-hand-holding"></i> Collect Equipment
                </a>
            @endif
        @endif

        <!-- On-Site Actions -->
        @if($request->isOnSiteCalibration())
            @if(!$request->hasSiteVisitScheduled() && $request->status === 'paid')
                <a href="{{ route('requests.schedule-site-visit', $request) }}" class="btn btn-warning">
                    <i class="fas fa-calendar-plus"></i> Schedule Site Visit
                </a>
            @endif
        @endif
    </div>
</div>

<!-- Add Location Details -->
@if($request->isOnSiteCalibration())
<hr>
<h4>Site Visit Details</h4>
<div class="row">
    <div class="col-md-6">
        <p><strong>Visit Date:</strong> {{ $request->site_visit_date ? $request->site_visit_date->format('Y-m-d H:i') : 'Not scheduled' }}</p>
        <p><strong>Site Address:</strong> {{ $request->site_address ?? 'N/A' }}</p>
        <p><strong>Contact Person:</strong> {{ $request->site_contact_person ?? 'N/A' }}</p>
    </div>
    <div class="col-md-6">
        <p><strong>Contact Phone:</strong> {{ $request->site_contact_phone ?? 'N/A' }}</p>
        <p><strong>Instructions:</strong> {{ $request->site_instructions ?? 'N/A' }}</p>
        <p><strong>Access Requirements:</strong> {{ $request->site_access_requirements ?? 'N/A' }}</p>
    </div>
</div>
@endif

<!-- Add Laboratory Receiving Details -->
@if($request->isLaboratoryCalibration())
<hr>
<h4>Laboratory Receiving Details</h4>
<div class="row">
    <div class="col-md-6">
        <p><strong>Received At:</strong> {{ $request->received_at_lab ? $request->received_at_lab->format('Y-m-d H:i') : 'Not received' }}</p>
        <p><strong>Received By:</strong> {{ $request->received_by ?? 'N/A' }}</p>
    </div>
    <div class="col-md-6">
        <p><strong>Condition:</strong> {{ $request->equipment_condition_notes ?? 'N/A' }}</p>
        <p><strong>Is Damaged:</strong> {{ $request->is_equipment_damaged ? 'Yes' : 'No' }}</p>
        @if($request->collected_at)
            <p><strong>Collected At:</strong> {{ $request->collected_at->format('Y-m-d H:i') }}</p>
            <p><strong>Collected By:</strong> {{ $request->collected_by ?? 'N/A' }}</p>
        @endif
    </div>
</div>
@endif

<!-- Delivery Records -->
@if($request->deliveryRecords->count() > 0)
<hr>
<h4>Delivery/Collection Records</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($request->deliveryRecords as $record)
            <tr>
                <td>{{ $record->delivery_number }}</td>
                <td><span class="badge bg-{{ $record->type === 'delivery' ? 'info' : 'warning' }}">{{ ucfirst($record->type) }}</span></td>
                <td>{{ ucfirst(str_replace('_', ' ', $record->method)) }}</td>
                <td><span class="badge bg-{{ $record->status === 'completed' ? 'success' : ($record->status === 'scheduled' ? 'warning' : 'secondary') }}">{{ ucfirst($record->status) }}</span></td>
                <td>{{ $record->created_at->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('deliveries.show', $record) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
                        <div class="col-md-6">
                            <h4>Status Timeline</h4>
                            <ul class="list-unstyled">
                                <li><strong>Created:</strong> {{ $request->created_at->format('Y-m-d H:i') }}</li>
                                @if($request->quoted_at)
                                    <li><strong>Quoted:</strong> {{ $request->quoted_at->format('Y-m-d H:i') }}</li>
                                @endif
                                @if($request->customer_approved_at)
                                    <li><strong>Approved:</strong> {{ $request->customer_approved_at->format('Y-m-d H:i') }}</li>
                                @endif
                                @if($request->paid_at)
                                    <li><strong>Paid:</strong> {{ $request->paid_at->format('Y-m-d H:i') }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Quote Section -->
                    @if($request->quote)
                        <hr>
                        <h4>Quote</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Quote #</th>
                                        <th>Subtotal</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Valid Until</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $request->quote->quote_number }}</td>
                                        <td>KSh {{ number_format($request->quote->subtotal, 2) }}</td>
                                        <td>KSh {{ number_format($request->quote->tax_amount, 2) }}</td>
                                        <td><strong>KSh {{ number_format($request->quote->total_amount, 2) }}</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $request->quote->status === 'approved' ? 'success' : ($request->quote->status === 'draft' ? 'secondary' : 'warning') }}">
                                                {{ ucfirst($request->quote->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->quote->valid_until ? $request->quote->valid_until->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            @if($request->status === 'quoted' || $request->status === 'pending_quote')
                                                <form action="{{ route('requests.approve-quote', $request) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this quote?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('quotes.show', $request->quote) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <p>No quote generated yet.</p>
                            @if($request->status === 'pending_quote' || $request->status === 'draft')
                                <a href="{{ route('requests.generate-quote', $request) }}" class="btn btn-primary">
                                    <i class="fas fa-file-invoice"></i> Generate Quote
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Job Card Section -->
                    @if($request->jobCard)
                        <hr>
                        <h4>Job Card</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Job Card #</th>
                                        <th>Allocated To</th>
                                        <th>Status</th>
                                        <th>Started</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $request->jobCard->job_card_number }}</td>
                                        <td>{{ $request->jobCard->allocatedTo->name ?? 'Unassigned' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->jobCard->status === 'completed' ? 'success' : ($request->jobCard->status === 'pending' ? 'warning' : 'info') }}">
                                                {{ ucfirst($request->jobCard->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->jobCard->started_at ? $request->jobCard->started_at->format('Y-m-d') : 'Not started' }}</td>
                                        <td>{{ $request->jobCard->completed_at ? $request->jobCard->completed_at->format('Y-m-d') : 'Pending' }}</td>
                                        <td>
                                            <a href="{{ route('jobcards.show', $request->jobCard) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p>No job card created yet.</p>
                            @if($request->status === 'paid' || $request->status === 'processing')
                                <a href="{{ route('requests.create-jobcard', $request) }}" class="btn btn-success">
                                    <i class="fas fa-clipboard-list"></i> Create Job Card
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Certificate Section -->
                    @if($request->certificate)
                        <hr>
                        <h4>Certificate</h4>
                        <div class="alert alert-success">
                            <p><strong>Certificate Number:</strong> {{ $request->certificate->certificate_number }}</p>
                            <p><strong>Issued Date:</strong> {{ $request->certificate->issued_date ? $request->certificate->issued_date->format('Y-m-d') : 'N/A' }}</p>
                            <p><strong>Signed:</strong> {{ $request->certificate->is_signed ? 'Yes' : 'No' }}</p>
                            <a href="{{ route('certificates.show', $request->certificate) }}" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> View Certificate
                            </a>
                        </div>
                    @endif

                    <hr>

                    <div class="mt-3">
                        <a href="{{ route('requests.index') }}" class="btn btn-secondary">Back</a>
                        @can('edit_customers')
                            <a href="{{ route('requests.edit', $request) }}" class="btn btn-warning">Edit</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
