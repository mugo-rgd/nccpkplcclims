@extends('layouts.app')

@section('title', 'Documentation Compliance Dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
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
                           fa-chart-line
                           text-primary
                           me-2">
                </i>

                Documentation Compliance Dashboard

            </h2>

            <p class="text-muted mb-0">

                Controlled-document status,
                review compliance and
                acknowledgement performance.

            </p>

        </div>


        <a href="{{
            route(
                'system-documents.index'
            )
        }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-book-open me-1"></i>

            Document Library

        </a>

    </div>


    {{-- =========================================================
        DOCUMENT STATUS CARDS
    ========================================================== --}}

    <div class="row g-3 mb-4">


        <div class="col-md-6 col-xl-3">

            <div class="card
                        border-0
                        shadow-sm
                        h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Total Documents
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $totalDocuments }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card
                        border-0
                        shadow-sm
                        h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Approved & Active
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-success">

                        {{ $approvedDocuments }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card
                        border-0
                        shadow-sm
                        h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Pending Approval
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-warning">

                        {{ $pendingApproval }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card
                        border-0
                        shadow-sm
                        h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Revision Required
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-danger">

                        {{ $revisionRequired }}

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- REVIEW CARDS --}}

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="small text-muted">
                        Due for Review
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-warning">

                        {{ $reviewDue }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="small text-muted">
                        Review Overdue
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-danger">

                        {{ $reviewOverdue }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="small text-muted">
                        Archived
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $archivedDocuments }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="small text-muted">
                        Obsolete
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $obsoleteDocuments }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        ACKNOWLEDGEMENT COMPLIANCE
    ========================================================== --}}

    <div class="card
                shadow-sm
                border-0
                mb-4">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">

                <i class="fas
                           fa-user-check
                           text-primary
                           me-2">
                </i>

                Acknowledgement Compliance

            </h5>

        </div>


        <div class="card-body">

            <div class="row g-4 align-items-center">


                <div class="col-lg-4 text-center">

                    <div class="display-4 fw-bold
                        {{
                            $acknowledgementCompliance >= 90
                                ? 'text-success'
                                : (
                                    $acknowledgementCompliance >= 70
                                        ? 'text-warning'
                                        : 'text-danger'
                                )
                        }}">

                        {{
                            number_format(
                                $acknowledgementCompliance,
                                1
                            )
                        }}%

                    </div>

                    <div class="text-muted">
                        Overall Compliance
                    </div>

                </div>


                <div class="col-lg-8">

                    <div class="progress mb-3"
                         style="height: 25px;">

                        <div class="progress-bar"
                             role="progressbar"
                             style="
                                width:
                                {{
                                    min(
                                        100,
                                        $acknowledgementCompliance
                                    )
                                }}%;
                             ">

                            {{
                                number_format(
                                    $acknowledgementCompliance,
                                    1
                                )
                            }}%

                        </div>

                    </div>


                    <div class="row text-center">

                        <div class="col-4">

                            <div class="fw-bold fs-4">

                                {{
                                    $requiredAcknowledgements
                                }}

                            </div>

                            <div class="small text-muted">
                                Required
                            </div>

                        </div>


                        <div class="col-4">

                            <div class="fw-bold
                                        fs-4
                                        text-success">

                                {{
                                    $completedAcknowledgements
                                }}

                            </div>

                            <div class="small text-muted">
                                Completed
                            </div>

                        </div>


                        <div class="col-4">

                            <div class="fw-bold
                                        fs-4
                                        text-danger">

                                {{
                                    $outstandingAcknowledgements
                                }}

                            </div>

                            <div class="small text-muted">
                                Outstanding
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        REVIEW ATTENTION
    ========================================================== --}}

    <div class="row g-4">


        <div class="col-xl-7">

            <div class="card
                        shadow-sm
                        border-0
                        h-100">

                <div class="card-header bg-white">

                    <h5 class="fw-bold mb-0">
                        Documents Requiring Review Attention
                    </h5>

                </div>


                <div class="table-responsive">

                    <table class="table
                                  table-hover
                                  align-middle
                                  mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>Document</th>
                                <th>Version</th>
                                <th>Review Date</th>
                                <th>Status</th>
                                <th></th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse(
                                $documentsDueForReview
                                as $document
                            )

                                <tr>

                                    <td>

                                        <div class="fw-semibold">

                                            {{
                                                $document->title
                                            }}

                                        </div>

                                        <div class="small
                                                    text-muted">

                                            {{
                                                $document
                                                    ->document_code
                                            }}

                                        </div>

                                    </td>


                                    <td>
                                        {{
                                            $document
                                                ->current_version
                                        }}
                                    </td>


                                    <td>

                                        {{
                                            $document
                                                ->review_date
                                                ?->format(
                                                    'd M Y'
                                                )
                                        }}

                                    </td>


                                    <td>

                                        @if(
                                            $document
                                                ->isReviewOverdue()
                                        )

                                            <span class="badge
                                                         bg-danger">

                                                Overdue

                                            </span>

                                        @else

                                            <span class="badge
                                                         bg-warning
                                                         text-dark">

                                                Due Soon

                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-end">

                                        <a href="{{
                                            route(
                                                'system-documents.show',
                                                $document
                                            )
                                        }}"
                                           class="btn
                                                  btn-sm
                                                  btn-outline-primary">

                                            View

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5"
                                        class="text-center
                                               text-muted
                                               py-4">

                                        No documents currently
                                        require review attention.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- RECENT REVIEWS --}}

        <div class="col-xl-5">

            <div class="card
                        shadow-sm
                        border-0
                        h-100">

                <div class="card-header bg-white">

                    <h5 class="fw-bold mb-0">
                        Recent Reviews
                    </h5>

                </div>


                <div class="card-body">

                    @forelse(
                        $recentReviews
                        as $review
                    )

                        <div class="border-bottom
                                    pb-3
                                    mb-3">

                            <div class="fw-semibold">

                                {{
                                    $review
                                        ->document
                                        ?->title
                                    ?? 'Document unavailable'
                                }}

                            </div>


                            <div class="small text-muted">

                                Version
                                {{
                                    $review
                                        ->document_version
                                }}

                                •

                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $review->outcome
                                        )
                                    )
                                }}

                            </div>


                            <div class="small mt-1">

                                Reviewed by

                                <strong>

                                    {{
                                        $review
                                            ->reviewedBy
                                            ?->name
                                        ?? 'Unknown'
                                    }}

                                </strong>

                                on

                                {{
                                    $review
                                        ->reviewed_at
                                        ->format(
                                            'd M Y H:i'
                                        )
                                }}

                            </div>

                        </div>

                    @empty

                        <div class="text-center
                                    text-muted
                                    py-4">

                            No document reviews recorded yet.

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

@endsection