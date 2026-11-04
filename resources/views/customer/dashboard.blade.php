@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Helper for calibration status badges
    |--------------------------------------------------------------------------
    */

    $statusBadge = static function (?string $status): string {
        return match ((string) $status) {
            'completed',
            'certified',
            'paid' =>
                'success',

            'pending',
            'pending_payment' =>
                'warning',

            'quoted' =>
                'info',

            'assigned',
            'in_progress',
            'testing' =>
                'primary',

            'rejected' =>
                'danger',

            default =>
                'secondary',
        };
    };

    /*
    |--------------------------------------------------------------------------
    | Helper for quotation status badges
    |--------------------------------------------------------------------------
    */

    $quoteStatusBadge = static function (?string $status): string {
        return match ((string) $status) {
            'approved' =>
                'success',

            'sent' =>
                'info',

            'rejected' =>
                'danger',

            'expired' =>
                'warning',

            default =>
                'secondary',
        };
    };

    /*
    |--------------------------------------------------------------------------
    | Helper for certificate status badges
    |--------------------------------------------------------------------------
    */

    $certificateStatusBadge = static function (?string $status): string {
        return match ((string) $status) {
            'approved' =>
                'success',

            'issued' =>
                'primary',

            'sent' =>
                'info',

            'checked' =>
                'warning',

            'rejected' =>
                'danger',

            default =>
                'secondary',
        };
    };
@endphp


<div class="container-fluid customer-dashboard">

    {{-- ========================================================= --}}
    {{-- WELCOME                                                   --}}
    {{-- ========================================================= --}}

    <div class="row mb-4">

        <div class="col-12">

            <div class="card border-0 shadow-sm dashboard-welcome-card">

                <div
                    class="
                        card-body
                        d-flex
                        justify-content-between
                        align-items-center
                        flex-wrap
                        gap-3
                    "
                >

                    <div>

                        <h2 class="mb-1">
                            <i class="fas fa-user-circle me-2"></i>

                            Welcome,
                            {{ auth()->user()?->name ?? 'Customer' }}
                        </h2>

                        <p class="mb-0">

                            <i class="fas fa-building me-1"></i>

                            {{
                                $customer->company_name
                                ?? 'Customer'
                            }}

                            @if(!empty($customer->customer_type))

                                <span
                                    class="
                                        badge
                                        bg-light
                                        text-dark
                                        ms-2
                                    "
                                >
                                    {{
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $customer->customer_type
                                            )
                                        )
                                    }}
                                </span>

                            @endif

                        </p>

                    </div>


                    <a
                        href="{{ route('calibrations.create') }}"
                        class="btn btn-light"
                    >
                        <i class="fas fa-plus me-1"></i>
                        Request Calibration
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- STATISTICS                                                --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">

            <div class="card stat-card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-bold">
                                Total Calibrations
                            </small>

                            <h2 class="mb-0 mt-1">
                                {{ $stats['total'] ?? 0 }}
                            </h2>

                        </div>

                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="fas fa-microscope"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-3 col-md-6">

            <div class="card stat-card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-bold">
                                Pending
                            </small>

                            <h2 class="mb-0 mt-1">
                                {{ $stats['pending'] ?? 0 }}
                            </h2>

                        </div>

                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="fas fa-clock"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-3 col-md-6">

            <div class="card stat-card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-bold">
                                In Progress
                            </small>

                            <h2 class="mb-0 mt-1">
                                {{ $stats['in_progress'] ?? 0 }}
                            </h2>

                        </div>

                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="fas fa-spinner"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-3 col-md-6">

            <div class="card stat-card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-bold">
                                Completed
                            </small>

                            <h2 class="mb-0 mt-1">
                                {{ $stats['completed'] ?? 0 }}
                            </h2>

                        </div>

                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SECONDARY WORKFLOW STATS                                  --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">

        <div class="col-lg-4 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted text-uppercase fw-bold">
                                Quoted
                            </small>

                            <h3 class="mb-0 mt-1">
                                {{ $stats['quoted'] ?? 0 }}
                            </h3>
                        </div>

                        <i class="fas fa-file-invoice-dollar fa-2x text-info"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-4 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted text-uppercase fw-bold">
                                Paid
                            </small>

                            <h3 class="mb-0 mt-1">
                                {{ $stats['paid'] ?? 0 }}
                            </h3>
                        </div>

                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-4 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted text-uppercase fw-bold">
                                Rejected
                            </small>

                            <h3 class="mb-0 mt-1">
                                {{ $stats['rejected'] ?? 0 }}
                            </h3>
                        </div>

                        <i class="fas fa-times-circle fa-2x text-danger"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- QUICK ACCESS                                              --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                <i class="fas fa-th-large me-2"></i>
                Quick Access
            </h5>

        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.calibrations') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-microscope text-primary"></i>

                        <span>
                            Calibrations
                        </span>
                    </a>

                </div>


                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.quotes') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-file-invoice-dollar text-success"></i>

                        <span>
                            Quotations
                        </span>
                    </a>

                </div>


                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.job-cards') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-clipboard-list text-info"></i>

                        <span>
                            Job Cards
                        </span>
                    </a>

                </div>


                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.certificates') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-certificate text-warning"></i>

                        <span>
                            Certificates
                        </span>
                    </a>

                </div>


                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.request-forms') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-file-signature text-primary"></i>

                        <span>
                            Request Forms
                        </span>
                    </a>

                </div>


                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.delivery-notes') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-truck-loading text-warning"></i>

                        <span>
                            Delivery Notes
                        </span>
                    </a>

                </div>


                <div class="col-xl col-md-3 col-6">

                    <a
                        href="{{ route('customer.collection-notes') }}"
                        class="quick-link-card"
                    >
                        <i class="fas fa-hand-holding text-success"></i>

                        <span>
                            Collection Notes
                        </span>
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- RECENT CALIBRATIONS                                       --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div
            class="
                card-header
                bg-white
                d-flex
                justify-content-between
                align-items-center
            "
        >

            <h5 class="mb-0">
                <i class="fas fa-clock me-2"></i>
                Recent Calibrations
            </h5>

            <a
                href="{{ route('customer.calibrations') }}"
                class="btn btn-sm btn-outline-primary"
            >
                View All
                <i class="fas fa-arrow-right ms-1"></i>
            </a>

        </div>


        <div class="card-body p-0">

            @if($recentCalibrations->isNotEmpty())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Equipment</th>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>Technician</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($recentCalibrations as $cal)

                                @php
                                    $calStatus =
                                        (string) (
                                            $cal->status
                                            ?? ''
                                        );
                                @endphp

                                <tr>

                                    <td>
                                        <strong>
                                            #{{ $cal->id }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{
                                            $cal->equipment_name
                                            ?? 'N/A'
                                        }}
                                    </td>

                                    <td>
                                        <code>
                                            {{
                                                $cal->serial_number
                                                ?? 'N/A'
                                            }}
                                        </code>
                                    </td>

                                    <td>

                                        <span
                                            class="
                                                badge
                                                bg-{{ $statusBadge($calStatus) }}
                                            "
                                        >
                                            {{
                                                ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $calStatus
                                                        ?: 'N/A'
                                                    )
                                                )
                                            }}
                                        </span>

                                    </td>

                                    <td>
                                        {{
                                            $cal->performedBy?->name
                                            ?? 'Not Assigned'
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            $cal->created_at
                                                ?->format('d/m/Y')
                                            ?? 'N/A'
                                        }}
                                    </td>

                                    <td class="text-end">

                                        <a
                                            href="{{ route(
                                                'customer.calibrations.show',
                                                $cal
                                            ) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="View Calibration"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5">

                    <i
                        class="
                            fas
                            fa-microscope
                            fa-3x
                            text-muted
                            mb-3
                        "
                    ></i>

                    <h5 class="text-muted">
                        No Calibrations Yet
                    </h5>

                    <p class="text-muted">
                        You have not submitted any calibration requests.
                    </p>

                    <a
                        href="{{ route('calibrations.create') }}"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-plus me-1"></i>
                        Request New Calibration
                    </a>

                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- RECENT QUOTES + CERTIFICATES                              --}}
    {{-- ========================================================= --}}

    <div class="row g-4">


        {{-- ===================================================== --}}
        {{-- RECENT QUOTATIONS                                     --}}
        {{-- ===================================================== --}}

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div
                    class="
                        card-header
                        bg-white
                        d-flex
                        justify-content-between
                        align-items-center
                    "
                >

                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar text-success me-2"></i>
                        Recent Quotations
                    </h5>

                    <a
                        href="{{ route('customer.quotes') }}"
                        class="btn btn-sm btn-outline-success"
                    >
                        View All
                    </a>

                </div>


                <div class="card-body p-0">

                    @if($quotes->isNotEmpty())

                        <div class="list-group list-group-flush">

                            @foreach($quotes as $quote)

                                <div class="list-group-item py-3">

                                    <div
                                        class="
                                            d-flex
                                            justify-content-between
                                            align-items-center
                                            gap-3
                                        "
                                    >

                                        <div>

                                            <strong>
                                                {{
                                                    $quote->quote_number
                                                    ?? 'N/A'
                                                }}
                                            </strong>

                                            <small class="d-block text-muted">
                                                {{
                                                    $quote->currency
                                                    ?? 'KES'
                                                }}

                                                {{
                                                    number_format(
                                                        (float) (
                                                            $quote->total_amount
                                                            ?? 0
                                                        ),
                                                        2
                                                    )
                                                }}
                                            </small>

                                            <small class="text-muted">
                                                {{
                                                    $quote->created_at
                                                        ?->format('d/m/Y')
                                                    ?? 'N/A'
                                                }}
                                            </small>

                                        </div>


                                        <div class="text-end">

                                            <span
                                                class="
                                                    badge
                                                    bg-{{
                                                        $quoteStatusBadge(
                                                            $quote->status
                                                        )
                                                    }}
                                                    mb-2
                                                "
                                            >
                                                {{
                                                    ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $quote->status
                                                            ?? 'N/A'
                                                        )
                                                    )
                                                }}
                                            </span>

                                            <br>

                                            <a
                                                href="{{ route(
                                                    'customer.quotes.show',
                                                    $quote
                                                ) }}"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-info
                                                "
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="text-center py-5">

                            <i
                                class="
                                    fas
                                    fa-file-invoice-dollar
                                    fa-3x
                                    text-muted
                                    mb-3
                                "
                            ></i>

                            <p class="text-muted mb-0">
                                No quotations are currently available.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- RECENT CERTIFICATES                                   --}}
        {{-- ===================================================== --}}

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div
                    class="
                        card-header
                        bg-white
                        d-flex
                        justify-content-between
                        align-items-center
                    "
                >

                    <h5 class="mb-0">
                        <i class="fas fa-certificate text-warning me-2"></i>
                        Recent Certificates
                    </h5>

                    <a
                        href="{{ route('customer.certificates') }}"
                        class="btn btn-sm btn-outline-warning"
                    >
                        View All
                    </a>

                </div>


                <div class="card-body p-0">

                    @if($certificates->isNotEmpty())

                        <div class="list-group list-group-flush">

                            @foreach($certificates as $cert)

                                <div class="list-group-item py-3">

                                    <div
                                        class="
                                            d-flex
                                            justify-content-between
                                            align-items-center
                                            gap-3
                                        "
                                    >

                                        <div>

                                            <strong>
                                                {{
                                                    $cert->certificate_number
                                                    ?? 'N/A'
                                                }}
                                            </strong>

                                            <small class="d-block text-muted">
                                                Calibration
                                                #{{ $cert->calibration_id }}
                                            </small>

                                            <small class="text-muted">
                                                {{
                                                    $cert->issued_date
                                                        ? \Carbon\Carbon::parse(
                                                            $cert->issued_date
                                                        )->format('d/m/Y')
                                                        : (
                                                            $cert->created_at
                                                                ?->format('d/m/Y')
                                                            ?? 'N/A'
                                                        )
                                                }}
                                            </small>

                                        </div>


                                        <div class="text-end">

                                            <span
                                                class="
                                                    badge
                                                    bg-{{
                                                        $certificateStatusBadge(
                                                            $cert->status
                                                        )
                                                    }}
                                                    mb-2
                                                "
                                            >
                                                {{
                                                    ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $cert->status
                                                            ?? 'N/A'
                                                        )
                                                    )
                                                }}
                                            </span>

                                            <br>

                                            <a
                                                href="{{ route(
                                                    'customer.certificates.show',
                                                    $cert
                                                ) }}"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-info
                                                "
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="text-center py-5">

                            <i
                                class="
                                    fas
                                    fa-certificate
                                    fa-3x
                                    text-muted
                                    mb-3
                                "
                            ></i>

                            <p class="text-muted mb-0">
                                No certificates are currently available.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('styles')
<style>
    .customer-dashboard .dashboard-welcome-card {
        background:
            linear-gradient(
                135deg,
                #0d6efd,
                #0746a7
            );
        color: #ffffff;
        border-radius: 16px;
    }

    .customer-dashboard .stat-card {
        border-radius: 14px;
    }

    .customer-dashboard .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    .customer-dashboard .quick-link-card {
        min-height: 115px;
        border: 1px solid #dee2e6;
        border-radius: 14px;
        padding: 16px 10px;
        background: #ffffff;
        text-decoration: none;
        color: #212529;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease;
    }

    .customer-dashboard .quick-link-card i {
        font-size: 1.8rem;
    }

    .customer-dashboard .quick-link-card span {
        font-size: .85rem;
        font-weight: 600;
        text-align: center;
    }

    .customer-dashboard .quick-link-card:hover {
        transform: translateY(-3px);
        border-color: #b8c7dc;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }

    .customer-dashboard .card {
        border-radius: 14px;
        overflow: hidden;
    }

    .customer-dashboard .table td,
    .customer-dashboard .table th {
        vertical-align: middle;
    }

    .customer-dashboard code {
        font-size: .82rem;
        color: #495057;
        background: #f8f9fa;
        padding: 2px 5px;
        border-radius: 4px;
    }

    @media (max-width: 767.98px) {
        .customer-dashboard .dashboard-welcome-card h2 {
            font-size: 1.4rem;
        }

        .customer-dashboard .quick-link-card {
            min-height: 100px;
        }
    }
</style>
@endpush
