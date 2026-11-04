@extends('layouts.app')

@section('title', 'Document Review History')

@section('content')

<div class="container-fluid py-4">


    <div class="d-flex
                justify-content-between
                align-items-center
                mb-4">

        <div>

            <h2 class="fw-bold mb-1">

                <i class="fas
                           fa-clock-rotate-left
                           text-primary
                           me-2">
                </i>

                Review History

            </h2>

            <div class="text-muted">

                {{ $document->document_code }}
                —
                {{ $document->title }}

            </div>

        </div>


        <a href="{{
            route(
                'system-documents.show',
                $document
            )
        }}"
           class="btn btn-outline-secondary">

            Back

        </a>

    </div>


    <div class="card shadow-sm border-0">

        <div class="table-responsive">

            <table class="table
                          table-hover
                          align-middle
                          mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Date</th>
                        <th>Version</th>
                        <th>Review Type</th>
                        <th>Outcome</th>
                        <th>Reviewer</th>
                        <th>Next Review</th>
                        <th>Notes</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse(
                        $document->reviews
                        as $review
                    )

                        <tr>

                            <td>

                                {{
                                    $review
                                        ->reviewed_at
                                        ->format(
                                            'd M Y H:i'
                                        )
                                }}

                            </td>


                            <td class="fw-semibold">

                                {{ $review->document_version }}

                            </td>


                            <td>

                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $review->review_type
                                        )
                                    )
                                }}

                            </td>


                            <td>

                                @php

                                    $outcomeClass = match(
                                        $review->outcome
                                    ) {
                                        'no_change'
                                            => 'success',

                                        'revision_required'
                                            => 'warning',

                                        'obsolete'
                                            => 'danger',

                                        default
                                            => 'secondary',
                                    };

                                @endphp


                                <span class="badge
                                             bg-{{ $outcomeClass }}">

                                    {{
                                        ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $review->outcome
                                            )
                                        )
                                    }}

                                </span>

                            </td>


                            <td>

                                {{
                                    $review
                                        ->reviewedBy
                                        ?->name
                                    ?? 'Unknown'
                                }}

                            </td>


                            <td>

                                {{
                                    $review
                                        ->next_review_date
                                        ?->format(
                                            'd M Y'
                                        )
                                    ?? '—'
                                }}

                            </td>


                            <td style="min-width: 250px;">

                                {{ $review->review_notes }}

                                @if(
                                    $review
                                        ->recommended_changes
                                )

                                    <div class="small
                                                text-muted
                                                mt-2">

                                        <strong>
                                            Recommended:
                                        </strong>

                                        {{
                                            $review
                                                ->recommended_changes
                                        }}

                                    </div>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="text-center
                                       text-muted
                                       py-5">

                                No formal reviews have
                                been recorded for this document.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection