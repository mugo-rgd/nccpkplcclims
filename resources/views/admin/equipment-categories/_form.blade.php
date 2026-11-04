@php
    $isEditing =
        isset($equipmentCategory)
        && $equipmentCategory?->exists;

    $formAction = $isEditing
        ? route(
            'equipment-categories.update',
            $equipmentCategory
        )
        : route('equipment-categories.store');

    $submitLabel = $isEditing
        ? 'Update Equipment Category'
        : 'Save Equipment Category';

    $submitIcon = $isEditing
        ? 'fa-save'
        : 'fa-plus-circle';

    $selectedStatus = old(
        'is_active',
        $isEditing
            ? (int) $equipmentCategory->is_active
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

    <div class="card category-form-card border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">
                <i class="fas fa-layer-group text-primary me-2"></i>

                {{ $isEditing
                    ? 'Edit Equipment Category'
                    : 'New Equipment Category' }}
            </h5>

            <small class="text-muted">
                Maintain the category name, unique code, description and
                availability for equipment registration.
            </small>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">

                {{-- Category name --}}
                <div class="col-md-8">
                    <label
                        for="name"
                        class="form-label fw-semibold"
                    >
                        Category Name
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old(
                            'name',
                            $equipmentCategory->name ?? ''
                        ) }}"
                        class="form-control
                            @error('name') is-invalid @enderror"
                        placeholder="e.g. Electrical Measuring Instruments"
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
                        Enter a clear category name used when classifying
                        laboratory equipment.
                    </div>
                </div>

                {{-- Category code --}}
                <div class="col-md-4">
                    <label
                        for="code"
                        class="form-label fw-semibold"
                    >
                        Category Code
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old(
                            'code',
                            $equipmentCategory->code ?? ''
                        ) }}"
                        class="form-control text-uppercase
                            @error('code') is-invalid @enderror"
                        placeholder="e.g. EMI"
                        maxlength="255"
                        required
                    >

                    @error('code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Letters, numbers, hyphens and underscores only.
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label
                        for="is_active"
                        class="form-label fw-semibold"
                    >
                        Category Status
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        id="is_active"
                        name="is_active"
                        class="form-select
                            @error('is_active') is-invalid @enderror"
                        required
                    >
                        <option
                            value="1"
                            @selected((string) $selectedStatus === '1')
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            @selected((string) $selectedStatus === '0')
                        >
                            Inactive
                        </option>
                    </select>

                    @error('is_active')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Only active categories should be available in new
                        equipment registration forms.
                    </div>
                </div>

                {{-- Usage information --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Configuration Usage
                    </label>

                    <div class="configuration-usage-box">
                        <i class="fas fa-tools text-primary"></i>

                        <div>
                            <strong>Equipment Registration</strong>

                            <small class="d-block text-muted">
                                This category may be selected when creating or
                                updating laboratory equipment.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label
                        for="description"
                        class="form-label fw-semibold"
                    >
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        class="form-control
                            @error('description') is-invalid @enderror"
                        placeholder="Describe the instruments or equipment that belong to this category"
                        maxlength="2000"
                    >{{ old(
                        'description',
                        $equipmentCategory->description ?? ''
                    ) }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Provide enough detail to help users select the correct
                        category during equipment registration.
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-0">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-circle-info mt-1"></i>

                    <div>
                        <strong>Category management notice</strong>

                        <div class="small mt-1">
                            Categories already assigned to equipment cannot be
                            deleted. Deactivate an existing category when it
                            should no longer be used for new records.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <a
                    href="{{ route('equipment-categories.index') }}"
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
