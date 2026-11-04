@php
    $isEditing =
        isset($equipmentManufacturer)
        && $equipmentManufacturer?->exists;

    $formAction = $isEditing
        ? route(
            'equipment-manufacturers.update',
            $equipmentManufacturer
        )
        : route('equipment-manufacturers.store');

    $submitLabel = $isEditing
        ? 'Update Manufacturer'
        : 'Save Manufacturer';

    $submitIcon = $isEditing
        ? 'fa-save'
        : 'fa-plus-circle';

    $selectedStatus = old(
        'is_active',
        $isEditing
            ? (int) $equipmentManufacturer->is_active
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

    <div class="card manufacturer-form-card border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">
                <i class="fas fa-industry text-primary me-2"></i>

                {{ $isEditing
                    ? 'Edit Equipment Manufacturer'
                    : 'New Equipment Manufacturer' }}
            </h5>

            <small class="text-muted">
                Maintain the manufacturer name, code, contact details,
                website, address and availability.
            </small>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">

                {{-- Manufacturer name --}}
                <div class="col-md-8">
                    <label
                        for="name"
                        class="form-label fw-semibold"
                    >
                        Manufacturer Name

                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old(
                            'name',
                            $equipmentManufacturer->name ?? ''
                        ) }}"
                        class="form-control
                            @error('name') is-invalid @enderror"
                        placeholder="e.g. PONOVO Power Co. Ltd."
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
                        Enter the approved manufacturer or supplier name.
                    </div>
                </div>

                {{-- Manufacturer code --}}
                <div class="col-md-4">
                    <label
                        for="code"
                        class="form-label fw-semibold"
                    >
                        Manufacturer Code

                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old(
                            'code',
                            $equipmentManufacturer->code ?? ''
                        ) }}"
                        class="form-control text-uppercase
                            @error('code') is-invalid @enderror"
                        placeholder="e.g. PONOVO"
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
                        Manufacturer Status

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
                        Only active manufacturers should be available when
                        registering equipment.
                    </div>
                </div>

                {{-- Website --}}
                <div class="col-md-6">
                    <label
                        for="website"
                        class="form-label fw-semibold"
                    >
                        Website
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-globe"></i>
                        </span>

                        <input
                            type="url"
                            id="website"
                            name="website"
                            value="{{ old(
                                'website',
                                $equipmentManufacturer->website ?? ''
                            ) }}"
                            class="form-control
                                @error('website') is-invalid @enderror"
                            placeholder="https://www.example.com"
                            maxlength="255"
                        >

                        @error('website')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-text">
                        Include the full address beginning with
                        <code>https://</code>.
                    </div>
                </div>

                {{-- Contact person --}}
                <div class="col-md-6">
                    <label
                        for="contact_person"
                        class="form-label fw-semibold"
                    >
                        Contact Person
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>

                        <input
                            type="text"
                            id="contact_person"
                            name="contact_person"
                            value="{{ old(
                                'contact_person',
                                $equipmentManufacturer->contact_person ?? ''
                            ) }}"
                            class="form-control
                                @error('contact_person') is-invalid @enderror"
                            placeholder="Contact person's full name"
                            maxlength="255"
                        >

                        @error('contact_person')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Contact phone --}}
                <div class="col-md-6">
                    <label
                        for="contact_phone"
                        class="form-label fw-semibold"
                    >
                        Contact Phone
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-phone"></i>
                        </span>

                        <input
                            type="text"
                            id="contact_phone"
                            name="contact_phone"
                            value="{{ old(
                                'contact_phone',
                                $equipmentManufacturer->contact_phone ?? ''
                            ) }}"
                            class="form-control
                                @error('contact_phone') is-invalid @enderror"
                            placeholder="+254..."
                            maxlength="255"
                        >

                        @error('contact_phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Contact email --}}
                <div class="col-md-6">
                    <label
                        for="contact_email"
                        class="form-label fw-semibold"
                    >
                        Contact Email
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>

                        <input
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            value="{{ old(
                                'contact_email',
                                $equipmentManufacturer->contact_email ?? ''
                            ) }}"
                            class="form-control
                                @error('contact_email') is-invalid @enderror"
                            placeholder="contact@example.com"
                            maxlength="255"
                        >

                        @error('contact_email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Usage information --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Configuration Usage
                    </label>

                    <div class="manufacturer-usage-box">
                        <i class="fas fa-tools text-primary"></i>

                        <div>
                            <strong>Equipment Registration</strong>

                            <small class="d-block text-muted">
                                The manufacturer may be selected when
                                registering or updating laboratory equipment.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="col-12">
                    <label
                        for="address"
                        class="form-label fw-semibold"
                    >
                        Physical or Postal Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                        class="form-control
                            @error('address') is-invalid @enderror"
                        placeholder="Enter the manufacturer's physical or postal address"
                        maxlength="2000"
                    >{{ old(
                        'address',
                        $equipmentManufacturer->address ?? ''
                    ) }}</textarea>

                    @error('address')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-0">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-circle-info mt-1"></i>

                    <div>
                        <strong>Manufacturer management notice</strong>

                        <div class="small mt-1">
                            A manufacturer already assigned to equipment
                            records cannot be deleted. Deactivate it when it
                            should no longer be selected for new equipment.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <a
                    href="{{ route('equipment-manufacturers.index') }}"
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
