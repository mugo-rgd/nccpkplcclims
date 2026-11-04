@extends('layouts.app')

@section('title', 'Review Document')

@section('content')

<div class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-xl-9">


            {{-- HEADER --}}

            <div class="d-flex
                        justify-content-between
                        align-items-center
                        mb-4">

                <div>

                    <div class="text-muted small">
                        {{ $document->document_code }}
                    </div>

                    <h2 class="fw-bold mb-1">

                        <i class="fas
                                   fa-clipboard-check
                                   text-primary
                                   me-2">
                        </i>

                        Document Review

                    </h2>

                    <div class="text-muted">

                        {{ $document->title }}

                        • Version
                        {{ $document->current_version }}

                    </div>

                </div>


                <a href="{{
                    route(
                        'system-documents.show',
                        $document
                    )
                }}"
                   class="btn btn-outline-secondary">

                    <i class="fas
                               fa-arrow-left
                               me-1">
                    </i>

                    Back

                </a>

            </div>


            @if(session('error'))

                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>

            @endif


            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- CURRENT DOCUMENT --}}

            <div class="card
                        shadow-sm
                        border-0
                        mb-4">

                <div class="card-header bg-white">

                    <strong>
                        Current Document Information
                    </strong>

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Status
                            </div>

                            <strong>
                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $document->status
                                        )
                                    )
                                }}
                            </strong>

                        </div>


                        <div class="col-md-4">

                            <div class="small text-muted">
                                Current Version
                            </div>

                            <strong>
                                {{ $document->current_version }}
                            </strong>

                        </div>


                        <div class="col-md-4">

                            <div class="small text-muted">
                                Review Date
                            </div>

                            <strong>

                                {{
                                    $document
                                        ->review_date
                                        ?->format(
                                            'd M Y'
                                        )
                                    ?? 'Not defined'
                                }}

                            </strong>

                        </div>


                        <div class="col-md-6">

                            <div class="small text-muted">
                                Last Reviewed
                            </div>

                            <strong>

                                {{
                                    $document
                                        ->last_reviewed_at
                                        ?->format(
                                            'd M Y H:i'
                                        )
                                    ?? 'Never formally reviewed'
                                }}

                            </strong>

                        </div>


                        <div class="col-md-6">

                            <div class="small text-muted">
                                Last Reviewer
                            </div>

                            <strong>

                                {{
                                    $document
                                        ->lastReviewedBy
                                        ?->name
                                    ?? '—'
                                }}

                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            {{-- REVIEW FORM --}}

            <form method="POST"
                  action="{{
                      route(
                          'system-documents.review.store',
                          $document
                      )
                  }}">

                @csrf


                <div class="card
                            shadow-sm
                            border-0">

                    <div class="card-header bg-white">

                        <strong>
                            Review Decision
                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">

                                    Review Type
                                    <span class="text-danger">*</span>

                                </label>

                                <select name="review_type"
                                        class="form-select"
                                        required>

                                    <option value="scheduled">
                                        Scheduled Review
                                    </option>

                                    <option value="ad_hoc">
                                        Ad-hoc Review
                                    </option>

                                    <option value="post_incident">
                                        Post-Incident Review
                                    </option>

                                    <option value="regulatory">
                                        Regulatory / Compliance Review
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">

                                    Review Outcome
                                    <span class="text-danger">*</span>

                                </label>

                                <select name="outcome"
                                        id="reviewOutcome"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Select Outcome --
                                    </option>

                                    <option value="no_change">
                                        Reviewed – No Change Required
                                    </option>

                                    <option value="revision_required">
                                        Revision Required
                                    </option>

                                    <option value="obsolete">
                                        Make Document Obsolete
                                    </option>

                                </select>

                            </div>


                            <div class="col-12">

                                <label class="form-label fw-semibold">

                                    Review Notes
                                    <span class="text-danger">*</span>

                                </label>

                                <textarea name="review_notes"
                                          rows="4"
                                          class="form-control"
                                          required
                                          placeholder="Record the review performed and findings...">{{ old('review_notes') }}</textarea>

                            </div>


                            <div class="col-12">

                                <label class="form-label">
                                    Recommended Changes
                                </label>

                                <textarea name="recommended_changes"
                                          rows="4"
                                          class="form-control"
                                          placeholder="Describe any changes required...">{{ old('recommended_changes') }}</textarea>

                            </div>


                            <div class="col-md-6"
                                 id="nextReviewSection">

                                <label class="form-label">
                                    Next Review Date
                                </label>

                                <input type="date"
                                       name="next_review_date"
                                       value="{{
                                           old(
                                               'next_review_date'
                                           )
                                       }}"
                                       class="form-control">

                                <div class="form-text">

                                    Required when the document
                                    remains current.

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="card-footer
                                bg-white
                                d-flex
                                justify-content-end
                                gap-2">

                        <a href="{{
                            route(
                                'system-documents.show',
                                $document
                            )
                        }}"
                           class="btn btn-outline-secondary">

                            Cancel

                        </a>

                        <button type="submit"
                                class="btn btn-primary">

                            <i class="fas
                                       fa-check
                                       me-1">
                            </i>

                            Complete Review

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection