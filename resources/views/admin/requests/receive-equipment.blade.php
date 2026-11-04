@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Receive Equipment at Laboratory</h2>
                    <p class="mb-0">Request #: <strong>{{ $request->request_number }}</strong></p>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>Equipment Details</h5>
                        <p><strong>Type:</strong> {{ $request->equipment_type ?? 'N/A' }}</p>
                        <p><strong>Manufacturer:</strong> {{ $request->manufacturer ?? 'N/A' }}</p>
                        <p><strong>Model:</strong> {{ $request->model ?? 'N/A' }}</p>
                        <p><strong>Serial Number:</strong> {{ $request->serial_number ?? 'N/A' }}</p>
                    </div>

                    <form method="POST" action="{{ route('requests.receive-equipment.store', $request) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Equipment Condition</label>
                            <select name="condition_notes" class="form-select">
                                <option value="Good">Good - No visible damage</option>
                                <option value="Fair">Fair - Minor wear and tear</option>
                                <option value="Poor">Poor - Visible damage</option>
                                <option value="Needs_repair">Needs Repair - Not functional</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_damaged" class="form-check-input" value="1">
                                <label class="form-check-label">Equipment is damaged</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Person (Delivery)</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ $request->customer->contact_person }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $request->customer->phone }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="condition_notes" class="form-control" rows="3" placeholder="Any additional notes about the equipment..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirm Receiving
                        </button>
                        <a href="{{ route('requests.show', $request) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
