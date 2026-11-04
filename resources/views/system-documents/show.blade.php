@extends('layouts.app')

@section('title', $document->title)

@section('content')

@if(
    $document->review_state
    === 'revision_required'
)

    <div class="alert
                alert-warning
                border-warning">

        <div class="d-flex
                    align-items-start">

            <i class="fas
                       fa-triangle-exclamation
                       fs-4
                       me-3
                       mt-1">
            </i>

            <div>

                <strong>
                    Revision Required
                </strong>

                <div class="small">

                    The most recent formal review
                    determined that this document
                    requires revision.

                </div>

                @can('edit_system_documents')

                    <a href="{{
                        route(
                            'system-documents.versions.create',
                            $document
                        )
                    }}"
                       class="btn
                              btn-sm
                              btn-warning
                              mt-2">

                        Upload Revised Version

                    </a>

                @endcan

            </div>

        </div>

    </div>

@endif
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

            <div class="text-muted small">
                {{ $document->document_code }}
            </div>

            <h2 class="fw-bold mb-1">
                {{ $document->title }}
            </h2>

            <div class="text-muted">
                Version {{ $document->current_version }}
                •
                {{ $document->category }}
            </div>

        </div>


        <div class="d-flex gap-2 flex-wrap">

            <a href="{{
                route(
                    'system-documents.index'
                )
            }}"
               class="btn btn-outline-secondary">

                <i class="fas fa-arrow-left me-1"></i>
                Back

            </a>


            @if(
                $document->status === 'approved'
                && $document->is_downloadable
            )

                @can('download_system_documents')

                    <a href="{{
                        route(
                            'system-documents.download',
                            $document
                        )
                    }}"
                       class="btn btn-success">

                        <i class="fas
                                   fa-download
                                   me-1">
                        </i>

                        Download

                    </a>

                @endcan

            @endif

        </div>

        @can('edit_system_documents')

    <a href="{{
        route(
            'system-documents.edit',
            $document
        )
    }}"
       class="btn btn-outline-primary">

        <i class="fas fa-pen me-1"></i>
        Edit

    </a>


    <a href="{{
        route(
            'system-documents.versions.create',
            $document
        )
    }}"
       class="btn btn-primary">

        <i class="fas
                   fa-code-branch
                   me-1">
        </i>

        New Version

    </a>

@endcan

@can('manage_system_documents')

    @if($document->requires_acknowledgement)

        <a href="{{
            route(
                'system-documents.acknowledgements',
                $document
            )
        }}"
           class="btn btn-outline-info">

            <i class="fas
                       fa-users
                       me-1">
            </i>

            Compliance

        </a>

    @endif

@endcan

@can('review_system_documents')

    @if(
        $document->status === 'approved'
        && $document->is_active
    )

        <a href="{{
            route(
                'system-documents.review',
                $document
            )
        }}"
           class="btn btn-outline-success">

            <i class="fas
                       fa-clipboard-check
                       me-1">
            </i>

            Review

        </a>

    @endif

@endcan

<a href="{{
    route(
        'system-documents.reviews',
        $document
    )
}}"
   class="btn btn-outline-secondary">

    <i class="fas
               fa-clock-rotate-left
               me-1">
    </i>

    Review History

</a>

    </div>


    {{-- ALERTS --}}

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger">
            {{ session('error') }}
        </div>

    @endif


    <div class="row g-4">


        {{-- =========================================================
            MAIN INFORMATION
        ========================================================== --}}

        <div class="col-xl-8">

            <div class="card
                        shadow-sm
                        border-0
                        mb-4">

                <div class="card-header
                            bg-white
                            py-3">

                    <h5 class="fw-bold mb-0">
                        Document Information
                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <div class="text-muted small">
                                Document Code
                            </div>

                            <div class="fw-semibold">
                                {{ $document->document_code }}
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small">
                                Current Version
                            </div>

                            <div class="fw-semibold">
                                {{ $document->current_version }}
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small">
                                Category
                            </div>

                            <div class="fw-semibold">
                                {{ $document->category }}
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small">
                                Classification
                            </div>

                            <div class="fw-semibold">
                                {{ ucfirst(
                                    $document->classification
                                ) }}
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small">
                                Effective Date
                            </div>

                            <div class="fw-semibold">

                                {{
                                    $document
                                        ->effective_date
                                        ?->format(
                                            'd M Y'
                                        )
                                    ?? 'Not specified'
                                }}

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small">
                                Review Date
                            </div>

                            <div class="fw-semibold">

                                {{
                                    $document
                                        ->review_date
                                        ?->format(
                                            'd M Y'
                                        )
                                    ?? 'Not specified'
                                }}

                            </div>

                        </div>


                        <div class="col-12">

                            <hr>

                            <div class="text-muted small mb-1">
                                Description
                            </div>

                            <div>
                                {{
                                    $document->description
                                    ?: 'No description provided.'
                                }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                VERSION HISTORY
            ====================================================== --}}

            <div class="card
                        shadow-sm
                        border-0">

                <div class="card-header
                            bg-white
                            py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="fas
                                   fa-code-branch
                                   me-2">
                        </i>

                        Version History

                    </h5>

                </div>


                <div class="table-responsive">

                    <table class="table
                                  table-hover
                                  align-middle
                                  mb-0">

                        <thead class="table-light">

                            <tr>
    <th>Version</th>
    <th>Status</th>
    <th>Changes</th>
    <th>Uploaded By</th>
    <th>Date</th>
    <th>Current</th>
    <th class="text-end">Action</th>
</tr>

                        </thead>


                        <tbody>

                            @forelse(
                                $document->versions
                                as $version
                            )

                                <tr>

                                    <td class="fw-semibold">
                                        {{ $version->version }}
                                    </td>
                                    
                                    <td>

                                        <span class="badge
                                                     text-bg-light
                                                     border">

                                            {{
                                                ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $version->status
                                                    )
                                                )
                                            }}

                                        </span>

                                    </td>

                                    <td>
                                        {{ $version->change_summary ?: '—' }}
                                    </td>

                                    <td>

                                        {{
                                            $version
                                                ->uploadedBy
                                                ?->name
                                            ?? 'Unknown'
                                        }}

                                    </td>

                                    <td>

                                        {{
                                            $version
                                                ->created_at
                                                ?->format(
                                                    'd M Y H:i'
                                                )
                                        }}

                                    </td>

                                    <td>

                                        @if($version->is_current)

                                            <span class="badge
                                                         bg-success">
                                                Current
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-end">

    @can('download_system_documents')

        <a href="{{
            route(
                'system-documents.versions.download',
                [
                    $document,
                    $version
                ]
            )
        }}"
           class="btn
                  btn-sm
                  btn-outline-secondary">

            <i class="fas fa-download"></i>

        </a>

    @endcan

</td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7"
                                        class="text-center
                                               text-muted
                                               py-4">

                                        No version history available.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- =========================================================
            RIGHT PANEL
        ========================================================== --}}

        <div class="col-xl-4">
@can('archive_system_documents')

    @if(
        $document->status === 'archived'
        && !$document->is_active
    )

        <form method="POST"
              action="{{
                  route(
                      'system-documents.restore',
                      $document
                  )
              }}">

            @csrf

            <button type="submit"
                    class="btn
                           btn-outline-success
                           w-100
                           mb-2">

                <i class="fas
                           fa-rotate-left
                           me-1">
                </i>

                Restore Document

            </button>

        </form>

    @endif

@endcan

            {{-- STATUS --}}

            <div class="card
                        shadow-sm
                        border-0
                        mb-4">

                <div class="card-header
                            bg-white
                            py-3">

                    <h5 class="fw-bold mb-0">
                        Document Control
                    </h5>

                </div>


                <div class="card-body">

                    <dl class="row mb-0">

                        <dt class="col-5">
                            Status
                        </dt>

                        <dd class="col-7">

                            {{
                                ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $document->status
                                    )
                                )
                            }}

                        </dd>


                        <dt class="col-5">
                            Owner
                        </dt>

                        <dd class="col-7">
                            {{
                                $document
                                    ->owner
                                    ?->name
                                ?? 'Not assigned'
                            }}
                        </dd>


                        <dt class="col-5">
                            Uploaded By
                        </dt>

                        <dd class="col-7">
                            {{
                                $document
                                    ->uploadedBy
                                    ?->name
                                ?? 'Unknown'
                            }}
                        </dd>


                        <dt class="col-5">
                            Approved By
                        </dt>

                        <dd class="col-7">
                            {{
                                $document
                                    ->approvedBy
                                    ?->name
                                ?? 'Not approved'
                            }}
                        </dd>


                        <dt class="col-5">
                            Downloads
                        </dt>

                        <dd class="col-7">
                            {{ number_format(
                                $document->download_count
                            ) }}
                        </dd>


                        <dt class="col-5">
                            Views
                        </dt>

                        <dd class="col-7">
                            {{ number_format(
                                $document->view_count
                            ) }}
                        </dd>

                    </dl>

                </div>

            </div>


            {{-- =====================================================
                APPROVAL
            ====================================================== --}}

            @can('approve_system_documents')

                @if(
                    $document->status
                    === 'pending_approval'
                )

                    <div class="card
                                shadow-sm
                                border-warning
                                mb-4">

                        <div class="card-header
                                    bg-warning-subtle">

                            <strong>
                                <i class="fas
                                           fa-check-circle
                                           me-2">
                                </i>

                                Approval Required
                            </strong>

                        </div>


                        <div class="card-body">

                            @if(
                                $document->uploaded_by
                                === auth()->id()
                            )

                                <div class="alert
                                            alert-warning
                                            mb-0">

                                    You uploaded this document.

                                    Another authorized user
                                    must approve it.

                                </div>

                            @else

                                <form method="POST"
                                      action="{{
                                          route(
                                              'system-documents.approve',
                                              $document
                                          )
                                      }}">

                                    @csrf


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Approval Notes
                                        </label>

                                        <textarea name="approval_notes"
                                                  rows="3"
                                                  class="form-control"
                                                  placeholder="Optional approval notes..."></textarea>

                                    </div>


                                    <button type="submit"
                                            class="btn
                                                   btn-success
                                                   w-100">

                                        <i class="fas
                                                   fa-check
                                                   me-1">
                                        </i>

                                        Approve Document

                                    </button>

                                </form>

                            @endif

                        </div>

                    </div>

                @endif

            @endcan


            {{-- =====================================================
                ACKNOWLEDGEMENT
            ====================================================== --}}

           @if(
    $document->requires_acknowledgement
    && $acknowledgementRequiredForUser
    && $document->status === 'approved'
)

                <div class="card
                            shadow-sm
                            border-info
                            mb-4">

                    <div class="card-header
                                bg-info-subtle">

                        <strong>

                            <i class="fas
                                       fa-user-check
                                       me-2">
                            </i>

                            Acknowledgement

                        </strong>

                    </div>


                    <div class="card-body">

                        @if($acknowledged)

                            <div class="alert
                                        alert-success
                                        mb-0">

                                <i class="fas
                                           fa-check-circle
                                           me-1">
                                </i>

                                You have acknowledged
                                version
                                {{ $document->current_version }}.

                            </div>

                        @else

                            <p class="small text-muted">

                                Confirm that you have read
                                and understood this document.

                            </p>


                            <form method="POST"
                                  action="{{
                                      route(
                                          'system-documents.acknowledge',
                                          $document
                                      )
                                  }}">

                                @csrf

                                <button type="submit"
                                        class="btn
                                               btn-info
                                               w-100">

                                    <i class="fas
                                               fa-user-check
                                               me-1">
                                    </i>

                                    I Acknowledge This Document

                                </button>

                            </form>

                        @endif

                    </div>

                </div>

            @endif


            {{-- =====================================================
                ARCHIVE
            ====================================================== --}}

            @can('archive_system_documents')

                @if($document->is_active)

                    <form method="POST"
                          action="{{
                              route(
                                  'system-documents.archive',
                                  $document
                              )
                          }}"
                          onsubmit="
                              return confirm(
                                  'Archive this document?'
                              );
                          ">

                        @csrf

                        <button type="submit"
                                class="btn
                                       btn-outline-warning
                                       w-100
                                       mb-2">

                            <i class="fas
                                       fa-box-archive
                                       me-1">
                            </i>

                            Archive Document

                        </button>

                    </form>

                @endif

            @endcan


            {{-- =====================================================
                DELETE
            ====================================================== --}}

            @can('delete_system_documents')

                <form method="POST"
                      action="{{
                          route(
                              'system-documents.destroy',
                              $document
                          )
                      }}"
                      onsubmit="
                          return confirm(
                              'Remove this document from the library?'
                          );
                      ">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn
                                   btn-outline-danger
                                   w-100">

                        <i class="fas
                                   fa-trash
                                   me-1">
                        </i>

                        Delete Document

                    </button>

                </form>

            @endcan

        </div>

    </div>

</div>

@endsection