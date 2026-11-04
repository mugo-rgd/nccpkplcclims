@extends('layouts.app')

@section('title', 'My Certificates')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Customer-visible certificate statuses
    |--------------------------------------------------------------------------
    |
    | Once the certificate reaches an approved/issued state,
    | it remains accessible to the customer.
    |--------------------------------------------------------------------------
    */

    $customerVisibleStatuses = [
        'approved',
        'issued',
        'sent',
    ];
@endphp

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-certificate text-primary me-2"></i>
            My Certificates
        </h4>

        <small class="text-muted">
            View and download certificates issued for your calibration work.
        </small>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>Certificate Number</th>
                        <th>Calibration</th>
                        <th>Equipment</th>
                        <th>Status</th>
                        <th>Issue Date</th>
                        <th>Expiry Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($certificates as $cert)

                        @php
                            $certificateStatus =
                                (string) (
                                    $cert->status
                                    ?? ''
                                );

                            $canAccessCertificate =
                                in_array(
                                    $certificateStatus,
                                    $customerVisibleStatuses,
                                    true
                                );

                            $statusClass = match (
                                $certificateStatus
                            ) {
                                'approved' =>
                                    'success',

                                'issued' =>
                                    'primary',

                                'sent' =>
                                    'info',

                                'checked' =>
                                    'warning',

                                'generated',
                                'pending',
                                'draft' =>
                                    'secondary',

                                'rejected' =>
                                    'danger',

                                default =>
                                    'secondary',
                            };

                            $calibration =
                                $cert->calibration;
                        @endphp

                        <tr>

                            {{-- Certificate Number --}}
                            <td>
                                <strong>
                                    {{
                                        $cert->certificate_number
                                        ?? 'N/A'
                                    }}
                                </strong>
                            </td>

                            {{-- Calibration --}}
                            <td>
                                @if($cert->calibration_id)
                                    <span
                                        class="
                                            badge
                                            bg-light
                                            text-dark
                                            border
                                        "
                                    >
                                        #{{ $cert->calibration_id }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>

                            {{-- Equipment --}}
                            <td>
                                {{
                                    $calibration?->equipment_name
                                    ?? 'N/A'
                                }}

                                @if($calibration?->serial_number)
                                    <small class="d-block text-muted">
                                        SN:
                                        {{ $calibration->serial_number }}
                                    </small>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge bg-{{ $statusClass }}">
                                    {{
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $certificateStatus
                                                ?: 'N/A'
                                            )
                                        )
                                    }}
                                </span>
                            </td>

                            {{-- Issue Date --}}
                            <td>
                                {{
                                    $cert->issued_date
                                        ? \Carbon\Carbon::parse(
                                            $cert->issued_date
                                        )->format('d/m/Y')
                                        : 'N/A'
                                }}
                            </td>

                            {{-- Expiry Date --}}
                            <td>
                                {{
                                    $cert->expiry_date
                                        ? \Carbon\Carbon::parse(
                                            $cert->expiry_date
                                        )->format('d/m/Y')
                                        : 'N/A'
                                }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">

                                @if($canAccessCertificate)

                                    <div
                                        class="btn-group btn-group-sm"
                                        role="group"
                                    >

                                        {{-- View Certificate --}}
                                        <a
                                            href="{{ route(
                                                'customer.certificates.show',
                                                $cert
                                            ) }}"
                                            class="btn btn-outline-info"
                                            title="View Certificate"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Combined Certificate + Test Report --}}
                                        @if(
                                            !empty(
                                                $cert->test_report_attachment_path
                                            )
                                        )

                                            <a
                                                href="{{ route(
                                                    'certificates.download-combined',
                                                    $cert
                                                ) }}"
                                                class="btn btn-outline-warning"
                                                title="Download Certificate and Test Report"
                                            >
                                                <i class="fas fa-file-pdf"></i>
                                            </a>

                                        @else

                                            {{-- Certificate only --}}
                                            <a
                                                href="{{ route(
                                                    'certificates.download',
                                                    [
                                                        'certificate' =>
                                                            $cert,

                                                        'force' =>
                                                            true,
                                                    ]
                                                ) }}"
                                                class="btn btn-outline-success"
                                                title="Download Certificate"
                                            >
                                                <i class="fas fa-download"></i>
                                            </a>

                                        @endif

                                    </div>

                                @else

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        disabled
                                        title="Certificate is still under internal review."
                                    >
                                        <i class="fas fa-lock me-1"></i>
                                        Not Available
                                    </button>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-5"
                            >
                                <i
                                    class="
                                        fas
                                        fa-certificate
                                        fa-3x
                                        text-muted
                                        mb-3
                                        d-block
                                    "
                                ></i>

                                <p class="text-muted mb-0">
                                    No certificates are currently available.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@if($certificates->hasPages())
    <div class="mt-3">
        {{
            $certificates
                ->appends(
                    request()->query()
                )
                ->links(
                    
                )
        }}
    </div>
@endif

@endsection
