@php
    $isEditing = isset($serviceType) && $serviceType?->exists;

    $formAction = $isEditing
        ? route('service-types.update', $serviceType)
        : route('service-types.store');

    $submitLabel = $isEditing
        ? 'Update Laboratory Service'
        : 'Save Laboratory Service';

    $submitIcon = $isEditing
        ? 'fa-save'
        : 'fa-plus-circle';

    $selectedStatus = old(
        'status',
        $isEditing
            ? (int) $serviceType->status
            : 1
    );
@endphp

<form
    action="{{ $formAction }}"
    method="POST"
    novalidate
>
    @csrf

    @if($isEditing)
        @method('PUT')
    @endif

    <div class="card service-form-card border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">
                <i class="fas fa-flask-vial text-primary me-2"></i>

                {{ $isEditing
                    ? 'Edit Laboratory Service'
                    : 'New Laboratory Service' }}
            </h5>

            <small class="text-muted">
                Maintain the service code, service name, applicable method,
                public description and publication status.
            </small>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">

                {{-- Service code --}}
                <div class="col-md-4">
                    <label
                        for="serv_code"
                        class="form-label fw-semibold"
                    >
                        Service Code

                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="serv_code"
                        name="serv_code"
                        value="{{ old(
                            'serv_code',
                            $serviceType->serv_code ?? ''
                        ) }}"
                        class="form-control text-uppercase
                            @error('serv_code') is-invalid @enderror"
                        placeholder="e.g. EM-001"
                        maxlength="50"
                        required
                        autofocus
                    >

                    @error('serv_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Enter a unique code used to identify this service.
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-md-4">
                    <label
                        for="status"
                        class="form-label fw-semibold"
                    >
                        Publication Status

                        <span class="text-danger">*</span>
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="form-select
                            @error('status') is-invalid @enderror"
                        required
                    >
                        <option
                            value="1"
                            {{ (string) $selectedStatus === '1'
                                ? 'selected'
                                : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            {{ (string) $selectedStatus === '0'
                                ? 'selected'
                                : '' }}
                        >
                            Inactive
                        </option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Active services appear on the public landing page.
                    </div>
                </div>

                {{-- Public visibility --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Public Visibility
                    </label>

                    <div class="public-status-box">
                        <i class="fas fa-globe text-primary"></i>

                        <div>
                            <strong>Public Service Catalogue</strong>

                            <small class="d-block text-muted">
                                Active records are advertised before login.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Service name --}}
                <div class="col-12">
                    <label
                        for="serv_name"
                        class="form-label fw-semibold"
                    >
                        Service Name

                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="serv_name"
                        name="serv_name"
                        value="{{ old(
                            'serv_name',
                            $serviceType->serv_name ?? ''
                        ) }}"
                        class="form-control
                            @error('serv_name') is-invalid @enderror"
                        placeholder="e.g. Energy Meter Calibration"
                        maxlength="255"
                        required
                    >

                    @error('serv_name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Use a clear customer-facing service description.
                    </div>
                </div>

                {{-- Method --}}
                <div class="col-12">
                    <label
                        for="serv_method"
                        class="form-label fw-semibold"
                    >
                        Applicable Method or Standard

                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        id="serv_method"
                        name="serv_method"
                        rows="3"
                        class="form-control
                            @error('serv_method') is-invalid @enderror"
                        placeholder="e.g. IEC 62053, internal laboratory procedure, or approved method"
                        maxlength="500"
                        required
                    >{{ old(
                        'serv_method',
                        $serviceType->serv_method ?? ''
                    ) }}</textarea>

                    @error('serv_method')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        State the standard, procedure or approved method used.
                    </div>
                </div>

                {{-- Public notes --}}
                <div class="col-md-6">
                    <label
                        for="notes"
                        class="form-label fw-semibold"
                    >
                        Public Service Notes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="5"
                        class="form-control
                            @error('notes') is-invalid @enderror"
                        placeholder="Describe the service for customers and public visitors"
                        maxlength="2000"
                    >{{ old(
                        'notes',
                        $serviceType->notes ?? ''
                    ) }}</textarea>

                    @error('notes')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        This content may be displayed publicly.
                    </div>
                </div>

                {{-- Internal comments --}}
                <div class="col-md-6">
                    <label
                        for="comments"
                        class="form-label fw-semibold"
                    >
                        Internal Comments
                    </label>

                    <textarea
                        id="comments"
                        name="comments"
                        rows="5"
                        class="form-control
                            @error('comments') is-invalid @enderror"
                        placeholder="Internal remarks for laboratory staff"
                        maxlength="2000"
                    >{{ old(
                        'comments',
                        $serviceType->comments ?? ''
                    ) }}</textarea>

                    @error('comments')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Internal comments should not appear on the public page.
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-4 mb-0">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-triangle-exclamation mt-1"></i>

                    <div>
                        <strong>Public content warning</strong>

                        <div class="small mt-1">
                            When this service is active, its name, code, method
                            and notes may be visible to unauthenticated visitors.
                            Confirm the wording before saving.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <a
                    href="{{ route('service-types.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="fas fa-arrow-left me-1"></i>
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn {{
                        $isEditing
                            ? 'btn-primary'
                            : 'btn-success'
                    }}"
                >
                    <i class="fas {{ $submitIcon }} me-1"></i>
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </div>
</form>
