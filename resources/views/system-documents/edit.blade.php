@extends('layouts.app')

@section('title', 'Edit Document')

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
                        <i class="fas fa-pen-to-square
                                  text-primary me-2"></i>

                        Edit Document
                    </h2>

                    <div class="text-muted">
                        {{ $document->document_code }}
                        •
                        Version
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


            <form method="POST"
                  action="{{
                      route(
                          'system-documents.update',
                          $document
                      )
                  }}">

                @csrf
                @method('PUT')


                <div class="card
                            shadow-sm
                            border-0
                            mb-4">

                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    Document Code
                                </label>

                                <input type="text"
                                       name="document_code"
                                       class="form-control"
                                       value="{{
                                           old(
                                               'document_code',
                                               $document->document_code
                                           )
                                       }}"
                                       required>

                            </div>


                            <div class="col-md-8">

                                <label class="form-label fw-semibold">
                                    Title
                                </label>

                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       value="{{
                                           old(
                                               'title',
                                               $document->title
                                           )
                                       }}"
                                       required>

                            </div>


                            <div class="col-12">

                                <label class="form-label">
                                    Description
                                </label>

                                <textarea name="description"
                                          rows="3"
                                          class="form-control">{{ old(
                                              'description',
                                              $document->description
                                          ) }}</textarea>

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Category
                                </label>

                                <select name="category"
                                        class="form-select"
                                        required>

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
                                                old(
                                                    'category',
                                                    $document->category
                                                )
                                                === $category
                                            )>

                                            {{ $category }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Classification
                                </label>

                                <select name="classification"
                                        class="form-select">

                                    @foreach([
                                        'general',
                                        'internal',
                                        'confidential',
                                        'restricted'
                                    ] as $classification)

                                        <option
                                            value="{{ $classification }}"
                                            @selected(
                                                old(
                                                    'classification',
                                                    $document
                                                        ->classification
                                                )
                                                === $classification
                                            )>

                                            {{ ucfirst(
                                                $classification
                                            ) }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Display Order
                                </label>

                                <input type="number"
                                       name="sort_order"
                                       min="0"
                                       class="form-control"
                                       value="{{
                                           old(
                                               'sort_order',
                                               $document->sort_order
                                           )
                                       }}">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Effective Date
                                </label>

                                <input type="date"
                                       name="effective_date"
                                       class="form-control"
                                       value="{{
                                           old(
                                               'effective_date',
                                               $document
                                                   ->effective_date
                                                   ?->format(
                                                       'Y-m-d'
                                                   )
                                           )
                                       }}">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Review Date
                                </label>

                                <input type="date"
                                       name="review_date"
                                       class="form-control"
                                       value="{{
                                           old(
                                               'review_date',
                                               $document
                                                   ->review_date
                                                   ?->format(
                                                       'Y-m-d'
                                                   )
                                           )
                                       }}">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Expiry Date
                                </label>

                                <input type="date"
                                       name="expiry_date"
                                       class="form-control"
                                       value="{{
                                           old(
                                               'expiry_date',
                                               $document
                                                   ->expiry_date
                                                   ?->format(
                                                       'Y-m-d'
                                                   )
                                           )
                                       }}">

                            </div>


                            <div class="col-md-6">

                                <div class="form-check
                                            form-switch
                                            mt-3">

                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="ack"
                                           name="requires_acknowledgement"
                                           value="1"
                                           @checked(
                                               old(
                                                   'requires_acknowledgement',
                                                   $document
                                                       ->requires_acknowledgement
                                               )
                                           )>

                                    <label for="ack"
                                           class="form-check-label">

                                        Require acknowledgement

                                    </label>

                                </div>

                                <div class="col-12 mt-3">

    <hr>

    <h6 class="fw-bold">
        Required Acknowledgement Audience
    </h6>


    <div class="row g-3 mt-1">

        <div class="col-md-6">

            <label class="form-label">
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
                                    $selectedRoles
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

        </div>


        <div class="col-md-6">

            <label class="form-label">
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
                                    $selectedUsers
                                )
                            )
                        )>

                        {{ $user->name }}

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

                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="downloadable"
                                           name="is_downloadable"
                                           value="1"
                                           @checked(
                                               old(
                                                   'is_downloadable',
                                                   $document
                                                       ->is_downloadable
                                               )
                                           )>

                                    <label for="downloadable"
                                           class="form-check-label">

                                        Allow document download

                                    </label>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="alert alert-info">

                    <i class="fas fa-circle-info me-2"></i>

                    This form modifies document metadata only.

                    To replace the document file,
                    use <strong>Upload New Version</strong>.

                </div>


                <div class="d-flex
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

                    <button class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>
                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection