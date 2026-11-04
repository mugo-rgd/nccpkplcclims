@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Schedule On-Site Calibration</h2>
                    <p class="mb-0">Request #: <strong>{{ $request->request_number }}</strong></p>
                </div>

                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        You are scheduling an <strong>on-site calibration</strong> at the customer's premises.
                    </div>

                    <form method="POST" action="{{ route('requests.site-visit.store', $request) }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visit Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="site_visit_date" class="form-control"
                                       min="{{ now()->addHours(24)->format('Y-m-d\TH:i') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Address <span class="text-danger">*</span></label>
                                <input type="text" name="site_address" class="form-control"
                                       value="{{ $request->customer->address }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" name="site_contact_person" class="form-control"
                                       value="{{ $request->customer->contact_person }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="text" name="site_contact_phone" class="form-control"
                                       value="{{ $request->customer->phone }}" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Site Instructions</label>
                                <textarea name="site_instructions" class="form-control" rows="2"
                                          placeholder="Any special instructions for the site visit..."></textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Access Requirements</label>
                                <textarea name="site_access_requirements" class="form-control" rows="2"
                                          placeholder="Security clearance, parking, building access, etc..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-calendar-check"></i> Schedule Site Visit
                        </button>
                        <a href="{{ route('requests.show', $request) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
