@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Collect Equipment</h2>
                    <p class="mb-0">Request #: <strong>{{ $request->request_number }}</strong></p>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>Equipment Ready for Collection</h5>
                        <p><strong>Serial Number:</strong> {{ $request->serial_number ?? 'N/A' }}</p>
                        @if($request->certificate)
                            <p><strong>Certificate:</strong> {{ $request->certificate->certificate_number }}</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('requests.collect.store', $request) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Collected By <span class="text-danger">*</span></label>
                            <input type="text" name="collected_by" class="form-control"
                                   value="{{ $request->customer->contact_person }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" name="contact_phone" class="form-control"
                                   value="{{ $request->customer->phone }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Collection Notes</label>
                            <textarea name="collection_notes" class="form-control" rows="3"
                                      placeholder="Any notes about the collection..."></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Please confirm that the equipment is in good condition and all accessories are included.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirm Collection
                        </button>
                        <a href="{{ route('requests.show', $request) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
