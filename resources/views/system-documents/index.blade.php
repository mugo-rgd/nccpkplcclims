@extends('layouts.app')

@section('title', 'System Documentation')

@section('content')

<div class="container-fluid py-4">

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="d-flex flex-column flex-lg-row
                justify-content-between align-items-lg-center
                gap-3 mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-book-open me-2 text-primary"></i>
                System Documentation
            </h2>

            <p class="text-muted mb-0">
                Access controlled manuals, procedures, policies,
                technical documents and system records.
            </p>
        </div>

        @can('upload_system_documents')
            <a href="{{ route('system-documents.create') }}"
               class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt me-2"></i>
                Upload Document
            </a>
        @endcan
    </div>


    {{-- =========================================================
        ALERTS
    ========================================================== --}}

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================== --}}

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex
                            justify-content-between align-items-center">

                    <div>
                        <div class="small text-muted">
                            Documents
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $stats['total'] }}
                        </div>
                    </div>

                    <div class="fs-2 text-primary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">

                <div class="card-body d-flex
                            justify-content-between align-items-center">

                    <div>
                        <div class="small text-muted">
                            Approved
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $stats['approved'] }}
                        </div>
                    </div>

                    <div class="fs-2 text-success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">

    <div class="card
                shadow-sm
                border-0
                h-100">

        <div class="card-body
                    d-flex
                    justify-content-between
                    align-items-center">

            <div>

                <div class="small text-muted">
                    Revision Required
                </div>

                <div class="fs-3
                            fw-bold
                            text-warning">

                    {{
                        $stats[
                            'revision_required'
                        ] ?? 0
                    }}

                </div>

            </div>


            <div class="fs-2 text-warning">

                <i class="fas
                           fa-pen-to-square">
                </i>

            </div>

        </div>

    </div>

</div>

        <div class="col-md-6 col-xl-3">

    <div class="card shadow-sm border-0 h-100">

        <div class="card-body d-flex
                    justify-content-between
                    align-items-center">

            <div>

                <div class="small text-muted">
                    Due for Review
                </div>

                <div class="fs-3 fw-bold
                            text-warning">

                    {{ $stats['review_due'] }}

                </div>

            </div>

            <div class="fs-2 text-warning">
                <i class="fas fa-calendar-clock"></i>
            </div>

        </div>

    </div>

</div>


<div class="col-md-6 col-xl-3">

    <div class="card shadow-sm border-0 h-100">

        <div class="card-body d-flex
                    justify-content-between
                    align-items-center">

            <div>

                <div class="small text-muted">
                    Review Overdue
                </div>

                <div class="fs-3 fw-bold
                            text-danger">

                    {{ $stats['review_overdue'] }}

                </div>

            </div>

            <div class="fs-2 text-danger">
                <i class="fas fa-triangle-exclamation"></i>
            </div>

        </div>

    </div>

</div>


        @can('manage_system_documents')
            <div class="col-md-6 col-xl-3">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body d-flex
                                justify-content-between align-items-center">

                        <div>
                            <div class="small text-muted">
                                Awaiting Approval
                            </div>

                            <div class="fs-3 fw-bold">
                                {{ $stats['pending'] }}
                            </div>
                        </div>

                        <div class="fs-2 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>

                </div>
            </div>
        @endcan


        <div class="col-md-6 col-xl-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body d-flex
                            justify-content-between align-items-center">

                    <div>
                        <div class="small text-muted">
                            Acknowledgement Required
                        </div>

                        <div class="fs-3 fw-bold">
    {{ $stats['acknowledgement_required'] ?? 0 }}
</div>
                    </div>

                    <div class="fs-2 text-info">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>

            </div>
        </div>

</div>


    {{-- =========================================================
        SEARCH / FILTERS
    ========================================================== --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('system-documents.index') }}">

                <div class="row g-3">

                    <div class="col-lg-5">

                        <label class="form-label fw-semibold">
                            Search
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="form-control"
                                   placeholder="Search title, code, category...">

                        </div>
                    </div>


                    <div class="col-lg-3">

                        <label class="form-label fw-semibold">
                            Category
                        </label>

                        <select name="category"
                                class="form-select">

                            <option value="">
                                All Categories
                            </option>

                            @foreach($categories as $category)

                                <option value="{{ $category }}"
                                    @selected(
                                        request('category') === $category
                                    )>
                                    {{ $category }}
                                </option>

                            @endforeach

                        </select>
                    </div>


                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            Classification
                        </label>

                        <select name="classification"
                                class="form-select">

                            <option value="">
                                All
                            </option>

                            @foreach([
                                'general' => 'General',
                                'internal' => 'Internal',
                                'confidential' => 'Confidential',
                                'restricted' => 'Restricted'
                            ] as $value => $label)

                                <option value="{{ $value }}"
                                    @selected(
                                        request('classification') === $value
                                    )>
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    @can('manage_system_documents')

                        <div class="col-lg-2">

                            <label class="form-label fw-semibold">
                                Status
                            </label>

                            <select name="status"
                                    class="form-select">

                                <option value="">
                                    All
                                </option>

                                @foreach([
                                    'draft' => 'Draft',
                                    'pending_approval' => 'Pending Approval',
                                    'approved' => 'Approved',
                                    'archived' => 'Archived',
                                    'obsolete' => 'Obsolete'
                                ] as $value => $label)

                                    <option value="{{ $value }}"
                                        @selected(
                                            request('status') === $value
                                        )>
                                        {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-lg-2">

    <label class="form-label fw-semibold">
        Review
    </label>

    <select name="review_status"
            class="form-select">

        <option value="">
            All
        </option>

        <option value="current"
            @selected(
                request('review_status')
                === 'current'
            )>

            Current

        </option>

        <option value="due"
            @selected(
                request('review_status')
                === 'due'
            )>

            Due Soon

        </option>

        <option value="overdue"
            @selected(
                request('review_status')
                === 'overdue'
            )>

            Overdue

        </option>

    </select>

</div>

                    @endcan

                </div>


                <div class="d-flex gap-2 mt-3">

                    <button class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>
                        Apply Filters
                    </button>

                    <a href="{{ route('system-documents.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
        DOCUMENTS
    ========================================================== --}}

    @if($documents->count())

        <div class="row g-4">

            @foreach($documents as $document)

                @php

                    $classificationClass = match(
                        $document->classification
                    ) {
                        'general' => 'success',
                        'internal' => 'primary',
                        'confidential' => 'warning',
                        'restricted' => 'danger',
                        default => 'secondary',
                    };

                    $statusClass = match(
                        $document->status
                    ) {
                        'approved' => 'success',
                        'pending_approval' => 'warning',
                        'draft' => 'secondary',
                        'archived' => 'dark',
                        'obsolete' => 'danger',
                        default => 'secondary',
                    };

                    $extension =
                        strtolower(
                            $document->file_extension ?? ''
                        );

                    $icon = match($extension) {
                        'pdf' => 'fa-file-pdf text-danger',
                        'doc',
                        'docx' => 'fa-file-word text-primary',
                        'xls',
                        'xlsx',
                        'csv' => 'fa-file-excel text-success',
                        'ppt',
                        'pptx' => 'fa-file-powerpoint text-warning',
                        default => 'fa-file-lines text-secondary',
                    };

                @endphp


                <div class="col-md-6 col-xl-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-start mb-3">

                                <div class="fs-1">
                                    <i class="fas {{ $icon }}"></i>
                                </div>

                                <div class="text-end">

                                    <span class="badge
                                                 bg-{{ $classificationClass }}">
                                        {{ ucfirst($document->classification) }}
                                    </span>

                                    <br>

                                    <span class="badge
                                                 bg-{{ $statusClass }}
                                                 mt-1">
                                        {{
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $document->status
                                                )
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>


                            <div class="small text-muted mb-1">
                                {{ $document->document_code }}
                            </div>


                            <h5 class="fw-bold">
                                {{ $document->title }}
                            </h5>


                            <div class="mb-2">

                                <span class="badge
                                             text-bg-light
                                             border">

                                    <i class="fas fa-folder me-1"></i>

                                    {{ $document->category }}

                                </span>


                                <span class="badge
                                             text-bg-light
                                             border">

                                    Version
                                    {{ $document->current_version }}

                                </span>

                            </div>


                            @if($document->description)

                                <p class="text-muted small">

                                    {{
                                        \Illuminate\Support\Str::limit(
                                            $document->description,
                                            150
                                        )
                                    }}

                                </p>

                            @endif


                            <hr>


                            <div class="small text-muted">

                                <div class="mb-1">

                                    <i class="far fa-calendar me-1"></i>

                                    Effective:

                                    <strong>
                                        {{
                                            optional(
                                                $document->effective_date
                                            )->format('d M Y')
                                            ?? 'Not specified'
                                        }}
                                    </strong>

                                </div>


                                <div class="mb-1">

                                    <i class="fas fa-user me-1"></i>

                                    Owner:

                                    <strong>
                                        {{
                                            $document->owner?->name
                                            ?? 'Not assigned'
                                        }}
                                    </strong>

                                </div>


                                @if(
                                    $document
                                        ->requires_acknowledgement
                                )

                                    <div class="text-info">

                                        <i class="fas
                                                   fa-user-check
                                                   me-1">
                                        </i>

                                        Acknowledgement required

                                    </div>

                                @endif

                            </div>

                        </div>


                        <div class="card-footer
                                    bg-white
                                    border-top-0">

                            <div class="d-flex
                                        gap-2
                                        flex-wrap">

                                <a href="{{
                                    route(
                                        'system-documents.show',
                                        $document
                                    )
                                }}"
                                   class="btn btn-sm
                                          btn-outline-primary">

                                    <i class="fas fa-eye me-1"></i>
                                    View

                                </a>


                                @if(
                                    $document->status === 'approved'
                                    && $document->is_downloadable
                                    && auth()->user()->can(
                                        'download_system_documents'
                                    )
                                )

                                    <a href="{{
                                        route(
                                            'system-documents.download',
                                            $document
                                        )
                                    }}"
                                       class="btn btn-sm
                                              btn-outline-success">

                                        <i class="fas
                                                   fa-download
                                                   me-1">
                                        </i>

                                        Download

                                    </a>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

                @if($document->isReviewOverdue())

    <div class="alert alert-danger
                py-2 px-3 small mt-3 mb-0">

        <i class="fas
                   fa-triangle-exclamation
                   me-1">
        </i>

        Document review is overdue.

    </div>

 @elseif($document->isDueForReview())

    <div class="alert alert-warning
                py-2 px-3 small mt-3 mb-0">

        <i class="fas
                   fa-calendar-clock
                   me-1">
        </i>

        Review due
        {{ $document->review_date->format('d M Y') }}.

    </div>

@endif

@if(
    $document->review_state
        === 'revision_required'
)

    <div class="alert
                alert-warning
                py-2
                px-3
                small
                mt-3
                mb-0">

        <i class="fas
                   fa-pen-to-square
                   me-1">
        </i>

        Revision required following review.

    </div>

@elseif($document->isReviewOverdue())

    <div class="alert
                alert-danger
                py-2
                px-3
                small
                mt-3
                mb-0">

        <i class="fas
                   fa-triangle-exclamation
                   me-1">
        </i>

        Document review is overdue.

    </div>

@elseif($document->isDueForReview())

    <div class="alert
                alert-warning
                py-2
                px-3
                small
                mt-3
                mb-0">

        <i class="fas
                   fa-calendar-alt
                   me-1">
        </i>

        Review due

        {{
            $document
                ->review_date
                ->format('d M Y')
        }}.

    </div>

@endif


@endforeach

        </div>


        <div class="mt-4">

            {{ $documents->links() }}

        </div>

    @else

        <div class="card shadow-sm border-0">

            <div class="card-body text-center py-5">

                <div class="display-4 text-muted mb-3">
                    <i class="fas fa-folder-open"></i>
                </div>

                <h5>
                    No documents found
                </h5>

                <p class="text-muted">
                    No documents match the selected filters.
                </p>

                @can('upload_system_documents')

                    <a href="{{
                        route(
                            'system-documents.create'
                        )
                    }}"
                       class="btn btn-primary">

                        Upload First Document

                    </a>

                @endcan

            </div>

        </div>

    @endif





</div>

@endsection