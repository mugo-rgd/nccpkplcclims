@extends('layouts.app')

@section('title', 'Upload System Document')

@section('content')

<div class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-xl-10">

            <div class="d-flex
                        justify-content-between
                        align-items-center
                        mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="fas
                                   fa-cloud-upload-alt
                                   me-2
                                   text-primary">
                        </i>

                        Upload System Document

                    </h2>

                    <p class="text-muted mb-0">
                        Add a controlled document to the
                        Meter Laboratory documentation library.
                    </p>

                </div>


                <a href="{{
                    route('system-documents.index')
                }}"
                   class="btn btn-outline-secondary">

                    <i class="fas fa-arrow-left me-1"></i>
                    Back

                </a>

            </div>


            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <form action="{{
                    route('system-documents.store')
                }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf


                {{-- =====================================================
                    DOCUMENT INFORMATION
                ====================================================== --}}

                <div class="card
                            shadow-sm
                            border-0
                            mb-4">

                    <div class="card-header
                                bg-white
                                py-3">

                        <h5 class="mb-0 fw-bold">

                            <i class="fas
                                       fa-file-lines
                                       me-2">
                            </i>

                            Document Information

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-md-4">

                                <label class="form-label fw-semibold">

                                    Document Code
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                       name="document_code"
                                       value="{{
                                           old(
                                               'document_code'
                                           )
                                       }}"
                                       class="form-control
                                           @error('document_code')
                                               is-invalid
                                           @enderror"
                                       placeholder="e.g. MCL-DOC-001"
                                       required>

                                @error('document_code')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="col-md-8">

                                <label class="form-label fw-semibold">

                                    Document Title
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                       name="title"
                                       value="{{ old('title') }}"
                                       class="form-control
                                           @error('title')
                                               is-invalid
                                           @enderror"
                                       placeholder="e.g. Meter Laboratory User Manual"
                                       required>

                                @error('title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="col-12">

                                <label class="form-label fw-semibold">
                                    Description
                                </label>

                                <textarea name="description"
                                          rows="3"
                                          class="form-control"
                                          placeholder="Brief description of the document...">{{ old('description') }}</textarea>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    CLASSIFICATION
                ====================================================== --}}

                <div class="card
                            shadow-sm
                            border-0
                            mb-4">

                    <div class="card-header
                                bg-white
                                py-3">

                        <h5 class="mb-0 fw-bold">

                            <i class="fas
                                       fa-shield-alt
                                       me-2">
                            </i>

                            Classification & Version

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-md-4">

                                <label class="form-label fw-semibold">

                                    Category
                                    <span class="text-danger">*</span>

                                </label>

                                <select name="category"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Select Category --
                                    </option>

                                    @foreach([
                                        'User Documentation',
                                        'Technical Documentation',
                                        'Database Documentation',
                                        'Security & Cybersecurity',
                                        'Quality & Testing',
                                        'Operations & Maintenance',
                                        'Backup & Disaster Recovery',
                                        'Governance & Compliance',
                                        'Training Materials',
                                    ] as $category)

                                        <option value="{{ $category }}"
                                            @selected(
                                                old('category')
                                                === $category
                                            )>

                                            {{ $category }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-md-4">

                                <label class="form-label fw-semibold">

                                    Classification
                                    <span class="text-danger">*</span>

                                </label>

                                <select name="classification"
                                        class="form-select"
                                        required>

                                    <option value="general"
                                        @selected(
                                            old('classification')
                                            === 'general'
                                        )>

                                        General

                                    </option>

                                    <option value="internal"
                                        @selected(
                                            old(
                                                'classification',
                                                'internal'
                                            )
                                            === 'internal'
                                        )>

                                        Internal

                                    </option>

                                    <option value="confidential"
                                        @selected(
                                            old('classification')
                                            === 'confidential'
                                        )>

                                        Confidential

                                    </option>

                                    <option value="restricted"
                                        @selected(
                                            old('classification')
                                            === 'restricted'
                                        )>

                                        Restricted

                                    </option>

                                </select>

                            </div>


                            <div class="col-md-4">

                                <label class="form-label fw-semibold">

                                    Version
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                       name="version"
                                       value="{{
                                           old(
                                               'version',
                                               '1.0'
                                           )
                                       }}"
                                       class="form-control"
                                       required>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    FILE
                ====================================================== --}}

                <div class="card
                            shadow-sm
                            border-0
                            mb-4">

                    <div class="card-header
                                bg-white
                                py-3">

                        <h5 class="mb-0 fw-bold">

                            <i class="fas fa-paperclip me-2"></i>
                            Document File

                        </h5>

                    </div>


                    <div class="card-body">

                        <label class="form-label fw-semibold">

                            Select File
                            <span class="text-danger">*</span>

                        </label>

                        <input type="file"
                               name="file"
                               class="form-control
                                   @error('file')
                                       is-invalid
                                   @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv"
                               required>

                        @error('file')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror


                        <div class="form-text">

                            Maximum file size:
                            <strong>20 MB</strong>.

                            Supported:
                            PDF, Word, Excel, PowerPoint,
                            TXT and CSV.

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    DOCUMENT CONTROL
                ====================================================== --}}

                <div class="card
                            shadow-sm
                            border-0
                            mb-4">

                    <div class="card-header
                                bg-white
                                py-3">

                        <h5 class="mb-0 fw-bold">

                            <i class="fas
                                       fa-calendar-check
                                       me-2">
                            </i>

                            Document Control

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-md-4">

                                <label class="form-label">
                                    Effective Date
                                </label>

                                <input type="date"
                                       name="effective_date"
                                       value="{{
                                           old(
                                               'effective_date'
                                           )
                                       }}"
                                       class="form-control">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Review Date
                                </label>

                                <input type="date"
                                       name="review_date"
                                       value="{{
                                           old(
                                               'review_date'
                                           )
                                       }}"
                                       class="form-control">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Expiry Date
                                </label>

                                <input type="date"
                                       name="expiry_date"
                                       value="{{
                                           old(
                                               'expiry_date'
                                           )
                                       }}"
                                       class="form-control">

                            </div>


                            <div class="col-md-6">

                                <div class="form-check
                                            form-switch
                                            mt-3">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="requires_acknowledgement"
                                           value="1"
                                           id="requiresAcknowledgement"
                                           @checked(
                                               old(
                                                   'requires_acknowledgement'
                                               )
                                           )>

                                    <label class="form-check-label"
                                           for="requiresAcknowledgement">

                                        Require users to acknowledge
                                        this document

                                    </label>

                                </div>

                                <div class="col-12"
     id="acknowledgementAudience">

    <hr>

    <h6 class="fw-bold mb-3">

        <i class="fas fa-users me-2"></i>
        Required Acknowledgement Audience

    </h6>

    <p class="text-muted small">

        Select the roles and/or individual users
        who must acknowledge this document.

    </p>


    <div class="row g-3">

        <div class="col-lg-6">

            <label class="form-label fw-semibold">
                Required Roles
            </label>

            <select name="required_roles[]"
                    class="form-select"
                    multiple
                    size="7">

                @foreach($roles as $role)

                    <option value="{{ $role->id }}"
                        @selected(
                            in_array(
                                $role->id,
                                old(
                                    'required_roles',
                                    []
                                )
                            )
                        )>

                        {{ ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $role->name
                            )
                        ) }}

                    </option>

                @endforeach

            </select>

            <div class="form-text">
                Hold Ctrl while selecting multiple roles.
            </div>

        </div>


        <div class="col-lg-6">

            <label class="form-label fw-semibold">
                Specific Users
            </label>

            <select name="required_users[]"
                    class="form-select"
                    multiple
                    size="7">

                @foreach($users as $user)

                    <option value="{{ $user->id }}"
                        @selected(
                            in_array(
                                $user->id,
                                old(
                                    'required_users',
                                    []
                                )
                            )
                        )>

                        {{ $user->name }}

                        @if($user->email)
                            — {{ $user->email }}
                        @endif

                    </option>

                @endforeach

            </select>

        </div>

    </div>

</div>

                            </div>


                            <div class="col-md-6">

                                <div class="form-check
                                            form-switch
                                            mt-3">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_downloadable"
                                           value="1"
                                           id="isDownloadable"
                                           checked>

                                    <label class="form-check-label"
                                           for="isDownloadable">

                                        Allow authorized users
                                        to download

                                    </label>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    SECURITY NOTICE
                ====================================================== --}}

                <div class="alert alert-info">

                    <i class="fas fa-shield-alt me-2"></i>

                    Uploaded documents are stored in
                    <strong>private Laravel storage</strong>
                    and are delivered only through authorized
                    application routes.

                </div>


                <div class="d-flex
                            justify-content-end
                            gap-2">

                    <a href="{{
                        route(
                            'system-documents.index'
                        )
                    }}"
                       class="btn btn-outline-secondary">

                        Cancel

                    </a>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas
                                   fa-cloud-upload-alt
                                   me-2">
                        </i>

                        Upload Document

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const acknowledgement =
            document.getElementById(
                'requiresAcknowledgement'
            );

        const audience =
            document.getElementById(
                'acknowledgementAudience'
            );


        function toggleAudience()
        {
            if (!acknowledgement || !audience) {
                return;
            }

            audience.style.display =
                acknowledgement.checked
                    ? ''
                    : 'none';
        }


        acknowledgement?.addEventListener(
            'change',
            toggleAudience
        );

        toggleAudience();
    }
);

</script>

@endpush