@extends('layouts.app')

@section('title', 'System Documentation Reports')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex
                flex-column
                flex-lg-row
                justify-content-between
                align-items-lg-center
                gap-3
                mb-4">

        <div>

            <h2 class="fw-bold mb-1">

                <i class="fas
                           fa-file-lines
                           text-primary
                           me-2">
                </i>

                System Documentation Reports

            </h2>

            <p class="text-muted mb-0">
                Review the controlled document register,
                acknowledgement compliance and review status.
            </p>

        </div>


        <div class="d-flex gap-2 flex-wrap">

            <a href="{{
                route(
                    'system-documents.dashboard'
                )
            }}"
               class="btn btn-outline-secondary">

                Compliance Dashboard

            </a>

            <a href="{{
                route(
                    'system-documents.index'
                )
            }}"
               class="btn btn-outline-primary">

                Document Library

            </a>

        </div>

    </div>


    {{-- FILTERS --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row g-3">

                    <div class="col-md-3">

                        <label class="form-label">
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
                                        request('category')
                                        === $category
                                    )>

                                    {{ $category }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="">
                                All Statuses
                            </option>

                            @foreach([
                                'draft',
                                'pending_approval',
                                'approved',
                                'archived',
                                'obsolete'
                            ] as $status)

                                <option value="{{ $status }}"
                                    @selected(
                                        request('status')
                                        === $status
                                    )>

                                    {{
                                        ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $status
                                            )
                                        )
                                    }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Classification
                        </label>

                        <select name="classification"
                                class="form-select">

                            <option value="">
                                All
                            </option>

                            @foreach([
                                'general',
                                'internal',
                                'confidential',
                                'restricted'
                            ] as $classification)

                                <option
                                    value="{{ $classification }}"
                                    @selected(
                                        request(
                                            'classification'
                                        )
                                        === $classification
                                    )>

                                    {{
                                        ucfirst(
                                            $classification
                                        )
                                    }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Review Status
                        </label>

                        <select name="review_status"
                                class="form-select">

                            <option value="">
                                All
                            </option>

                            <option value="due"
                                @selected(
                                    request(
                                        'review_status'
                                    ) === 'due'
                                )>
                                Due Soon
                            </option>

                            <option value="overdue"
                                @selected(
                                    request(
                                        'review_status'
                                    ) === 'overdue'
                                )>
                                Overdue
                            </option>

                            <option value="revision_required"
                                @selected(
                                    request(
                                        'review_status'
                                    ) === 'revision_required'
                                )>
                                Revision Required
                            </option>

                        </select>

                    </div>

                </div>


                <div class="mt-3 d-flex gap-2">

                    <button class="btn btn-primary">

                        <i class="fas fa-filter me-1"></i>
                        Apply Filters

                    </button>

                    <a href="{{
                        route(
                            'system-documents.reports'
                        )
                    }}"
                       class="btn btn-outline-secondary">

                        Reset

                    </a>

                </div>

            </form>

        </div>

    </div>


    {{-- EXPORTS --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="d-flex
                        flex-wrap
                        gap-2">

                <a href="{{
                    route(
                        'system-documents.reports.register.export'
                    )
                }}"
                   class="btn btn-success">

                    <i class="fas
                               fa-file-csv
                               me-1">
                    </i>

                    Export Document Register

                </a>


                <a href="{{
                    route(
                        'system-documents.reports.acknowledgements.export'
                    )
                }}"
                   class="btn btn-info">

                    <i class="fas
                               fa-user-check
                               me-1">
                    </i>

                    Export Acknowledgements

                </a>


                <a href="{{
                    route(
                        'system-documents.reports.reviews.export'
                    )
                }}"
                   class="btn btn-warning">

                    <i class="fas
                               fa-clipboard-check
                               me-1">
                    </i>

                    Export Review History

                </a>

            </div>

        </div>

    </div>


    {{-- DOCUMENT REGISTER --}}

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <strong>
                Controlled Document Register
            </strong>

        </div>


        <div class="table-responsive">

            <table class="table
                          table-hover
                          align-middle
                          mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Code</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Classification</th>
                        <th>Effective</th>
                        <th>Review</th>
                        <th>Owner</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($documents as $document)

                        <tr>

                            <td class="fw-semibold">
                                {{
                                    $document
                                        ->document_code
                                }}
                            </td>

                            <td>

                                <a href="{{
                                    route(
                                        'system-documents.show',
                                        $document
                                    )
                                }}">

                                    {{
                                        $document
                                            ->title
                                    }}

                                </a>

                            </td>

                            <td>
                                {{
                                    $document
                                        ->category
                                }}
                            </td>

                            <td>
                                {{
                                    $document
                                        ->current_version
                                }}
                            </td>

                            <td>

                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $document->status
                                        )
                                    )
                                }}

                            </td>

                            <td>

                                {{
                                    ucfirst(
                                        $document
                                            ->classification
                                    )
                                }}

                            </td>

                            <td>

                                {{
                                    $document
                                        ->effective_date
                                        ?->format(
                                            'd M Y'
                                        )
                                    ?? '—'
                                }}

                            </td>

                            <td>

                                {{
                                    $document
                                        ->review_date
                                        ?->format(
                                            'd M Y'
                                        )
                                    ?? '—'
                                }}

                            </td>

                            <td>

                                {{
                                    $document
                                        ->owner
                                        ?->name
                                    ?? '—'
                                }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center
                                       text-muted
                                       py-5">

                                No documents found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="card-footer bg-white">

            {{
                $documents->links()
            }}

        </div>

    </div>

</div>

@endsection