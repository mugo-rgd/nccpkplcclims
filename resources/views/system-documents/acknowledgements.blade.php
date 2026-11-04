@extends('layouts.app')

@section('title', 'Document Acknowledgements')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex
                justify-content-between
                align-items-center
                mb-4">

        <div>

            <h2 class="fw-bold mb-1">

                <i class="fas
                           fa-user-check
                           text-primary
                           me-2">
                </i>

                Document Acknowledgement Compliance

            </h2>

            <div class="text-muted">

                {{ $document->document_code }}
                —
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

            Back

        </a>

    </div>


    <div class="row g-3 mb-4">

        <div class="col-md-3">

            <div class="card
                        shadow-sm
                        border-0">

                <div class="card-body">

                    <div class="text-muted small">
                        Required Users
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $totalUsers }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card
                        shadow-sm
                        border-0">

                <div class="card-body">

                    <div class="text-muted small">
                        Acknowledged
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-success">

                        {{ $acknowledgedCount }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card
                        shadow-sm
                        border-0">

                <div class="card-body">

                    <div class="text-muted small">
                        Outstanding
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-danger">

                        {{ $outstandingCount }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card
                        shadow-sm
                        border-0">

                <div class="card-body">

                    <div class="text-muted small">
                        Compliance
                    </div>

                    <div class="fs-2
                                fw-bold
                                text-primary">

                        {{ number_format(
                            $compliancePercentage,
                            1
                        ) }}%

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="table-responsive">

            <table class="table
                          table-hover
                          align-middle
                          mb-0">

                <thead class="table-light">

                    <tr>

                        <th>User</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Acknowledged At</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($users as $user)

                        @php
                            $ack =
                                $acknowledgements
                                    ->get($user->id);
                        @endphp


                        <tr>

                            <td class="fw-semibold">
                                {{ $user->name }}
                            </td>


                            <td>
                                {{ $user->email }}
                            </td>


                            <td>

                                @if($ack)

                                    <span class="badge
                                                 bg-success">

                                        <i class="fas
                                                   fa-check
                                                   me-1">
                                        </i>

                                        Acknowledged

                                    </span>

                                @else

                                    <span class="badge
                                                 bg-warning
                                                 text-dark">

                                        Outstanding

                                    </span>

                                @endif

                            </td>


                            <td>

                                @if($ack)

                                    {{
                                        $ack
                                            ->acknowledged_at
                                            ->format(
                                                'd M Y H:i'
                                            )
                                    }}

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection