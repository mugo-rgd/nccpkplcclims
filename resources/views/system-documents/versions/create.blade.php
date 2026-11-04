@extends('layouts.app')

@section('title', 'Upload New Document Version')

@section('content')

<div class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-xl-9">

            <div class="d-flex
                        justify-content-between
                        align-items-center
                        mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="fas
                                   fa-code-branch
                                   text-primary
                                   me-2">
                        </i>

                        Upload New Version

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


            @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <div class="alert alert-info">

                Current approved/current version:

                <strong>
                    {{ $document->current_version }}
                </strong>

                <br>

                Uploading a replacement will return
                the document to
                <strong>Pending Approval</strong>.

            </div>


            <form method="POST"
                  enctype="multipart/form-data"
                  action="{{
                      route(
                          'system-documents.versions.store',
                          $document
                      )
                  }}">

                @csrf


                <div class="card
                            shadow-sm
                            border-0">

                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-md-4">

                                <label class="form-label fw-semibold">

                                    New Version
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                       name="version"
                                       value="{{ old('version') }}"
                                       class="form-control"
                                       placeholder="e.g. 1.1"
                                       required>

                            </div>


                            <div class="col-md-8">

                                <label class="form-label fw-semibold">

                                    Replacement File
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="file"
                                       name="file"
                                       class="form-control"
                                       required>

                            </div>


                            <div class="col-12">

                                <label class="form-label fw-semibold">

                                    Change Summary
                                    <span class="text-danger">*</span>

                                </label>

                                <textarea name="change_summary"
                                          rows="3"
                                          class="form-control"
                                          placeholder="Summarize what changed in this revision..."
                                          required>{{ old(
                                              'change_summary'
                                          ) }}</textarea>

                            </div>


                            <div class="col-12">

                                <label class="form-label">
                                    Detailed Revision Notes
                                </label>

                                <textarea name="revision_notes"
                                          rows="4"
                                          class="form-control">{{ old(
                                              'revision_notes'
                                          ) }}</textarea>

                            </div>


                            <div class="col-md-6">

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


                            <div class="col-md-6">

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

                        </div>

                    </div>


                    <div class="card-footer
                                bg-white
                                text-end">

                        <a href="{{
                            route(
                                'system-documents.show',
                                $document
                            )
                        }}"
                           class="btn
                                  btn-outline-secondary">

                            Cancel

                        </a>

                        <button class="btn btn-primary">

                            <i class="fas
                                       fa-cloud-upload-alt
                                       me-1">
                            </i>

                            Upload New Version

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection