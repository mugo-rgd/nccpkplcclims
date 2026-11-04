@php
    $isEditing = isset($equipmentType) && $equipmentType?->exists;

    $formAction = $isEditing
        ? route('equipment-types.update', $equipmentType)
        : route('equipment-types.store');

    $submitLabel = $isEditing
        ? 'Update Equipment Type'
        : 'Save Equipment Type';

    $submitIcon = $isEditing
        ? 'fa-save'
        : 'fa-plus-circle';
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

    <div class="card form-card border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">
                <i class="fas fa-tags text-primary me-2"></i>

                {{ $isEditing
                    ? 'Edit Equipment Type and Pricing'
                    : 'New Equipment Type and Pricing' }}
            </h5>

            <small class="text-muted">
                Enter the equipment category and its published laboratory rate.
            </small>
        </div>

        <div class="card-body p-4">

            {{-- Equipment type name --}}
            <div class="mb-4">
                <label
                    for="name"
                    class="form-label fw-semibold"
                >
                    Equipment Type Name

                    <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old(
                        'name',
                        $equipmentType->name ?? ''
                    ) }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="e.g. CT Analyzer"
                    maxlength="255"
                    required
                    autofocus
                >

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

                <div class="form-text">
                    Use a clear, unique equipment category name. This name will
                    appear on the public pricing page and laboratory forms.
                </div>
            </div>

            {{-- Rate --}}
            <div class="mb-4">
                <label
                    for="rate"
                    class="form-label fw-semibold"
                >
                    Published Laboratory Rate

                    <span class="text-danger">*</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text fw-semibold">
                        KES
                    </span>

                    <input
                        type="number"
                        id="rate"
                        name="rate"
                        value="{{ old(
                            'rate',
                            isset($equipmentType)
                                ? number_format(
                                    (float) $equipmentType->rate,
                                    2,
                                    '.',
                                    ''
                                )
                                : ''
                        ) }}"
                        class="form-control @error('rate') is-invalid @enderror"
                        placeholder="0.00"
                        min="0"
                        max="999999999.99"
                        step="0.01"
                        required
                    >

                    @error('rate')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-text">
                    Enter the base calibration or testing charge. The value is
                    published on the public laboratory landing page.
                </div>
            </div>

            {{-- Public notice --}}
            <div class="alert alert-warning mb-0">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-triangle-exclamation mt-1"></i>

                    <div>
                        <strong>Public pricing notice</strong>

                        <div class="small mt-1">
                            Saving this record may immediately change the rate
                            displayed to public visitors. Confirm the approved
                            rate before submitting.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <a
                    href="{{ route('equipment-types.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="fas fa-arrow-left me-1"></i>
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn {{ $isEditing
                        ? 'btn-primary'
                        : 'btn-success' }}"
                >
                    <i class="fas {{ $submitIcon }} me-1"></i>
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </div>
</form>
