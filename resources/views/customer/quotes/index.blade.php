@extends('layouts.app')

@section('title', 'My Quotes')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
            My Quotations
        </h4>

        <small class="text-muted">
            View quotations issued for your calibration requests.
        </small>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>Quote Number</th>
                        <th>Calibration</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Valid Until</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($quotes as $quote)

                        @php
                            $calibration =
                                $quote->calibration;

                            /*
                            |--------------------------------------------------------------------------
                            | Quotation availability
                            |--------------------------------------------------------------------------
                            |
                            | Once generated, the quotation remains accessible.
                            |
                            */

                            $canViewQuotation =
                                !empty($quote->id)
                                && !empty($quote->quote_number);

                            /*
                            |--------------------------------------------------------------------------
                            | Payment state
                            |--------------------------------------------------------------------------
                            */

                            $totalAmount =
                                (float) (
                                    $quote->total_amount
                                    ?? 0
                                );

                            $amountPaid =
                                (float) (
                                    $quote->amount_paid
                                    ?? 0
                                );

                            $balanceDue =
                                max(
                                    (float) (
                                        $quote->balance_due
                                        ?? (
                                            $totalAmount
                                            - $amountPaid
                                        )
                                    ),
                                    0
                                );

                            $isPaid =
                                in_array(
                                    (string) $quote->mpesa_payment_status,
                                    [
                                        'paid',
                                        'completed',
                                    ],
                                    true
                                )
                                || (
                                    $totalAmount > 0
                                    && $amountPaid >= $totalAmount
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Quote status badge
                            |--------------------------------------------------------------------------
                            */

                            $statusClass = match (
                                (string) $quote->status
                            ) {
                                'approved' =>
                                    'success',

                                'sent' =>
                                    'info',

                                'draft' =>
                                    'secondary',

                                'rejected' =>
                                    'danger',

                                'expired' =>
                                    'warning',

                                default =>
                                    'secondary',
                            };

                            /*
                            |--------------------------------------------------------------------------
                            | Payment badge
                            |--------------------------------------------------------------------------
                            */

                            $paymentClass =
                                $isPaid
                                    ? 'success'
                                    : (
                                        $amountPaid > 0
                                            ? 'warning'
                                            : 'secondary'
                                    );

                            $paymentLabel =
                                $isPaid
                                    ? 'Paid'
                                    : (
                                        $amountPaid > 0
                                            ? 'Partial'
                                            : 'Unpaid'
                                    );
                        @endphp

                        <tr>

                            {{-- Quote Number --}}
                            <td>
                                <strong>
                                    {{
                                        $quote->quote_number
                                        ?? 'N/A'
                                    }}
                                </strong>
                            </td>

                            {{-- Calibration --}}
                            <td>
                                @if($quote->calibration_id)
                                    <span
                                        class="
                                            badge
                                            bg-light
                                            text-dark
                                            border
                                        "
                                    >
                                        #{{ $quote->calibration_id }}
                                    </span>

                                    @if($calibration?->equipment_name)
                                        <small class="d-block text-muted mt-1">
                                            {{ $calibration->equipment_name }}
                                        </small>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td>
                                <strong>
                                    {{
                                        $quote->currency
                                        ?? 'KES'
                                    }}

                                    {{
                                        number_format(
                                            $totalAmount,
                                            2
                                        )
                                    }}
                                </strong>

                                @if($balanceDue > 0)
                                    <small class="d-block text-danger">
                                        Balance:
                                        {{
                                            $quote->currency
                                            ?? 'KES'
                                        }}

                                        {{
                                            number_format(
                                                $balanceDue,
                                                2
                                            )
                                        }}
                                    </small>
                                @endif
                            </td>

                            {{-- Payment --}}
                            <td>
                                <span class="badge bg-{{ $paymentClass }}">
                                    {{ $paymentLabel }}
                                </span>
                            </td>

                            {{-- Quote Status --}}
                            <td>
                                <span class="badge bg-{{ $statusClass }}">
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
                            </td>

                            {{-- Valid Until --}}
                            <td>
                                {{
                                    $quote->valid_until
                                        ? \Carbon\Carbon::parse(
                                            $quote->valid_until
                                        )->format('d/m/Y')
                                        : 'N/A'
                                }}
                            </td>

                            {{-- Created --}}
                            <td>
                                {{
                                    $quote->created_at
                                        ?->format('d/m/Y')
                                    ?? 'N/A'
                                }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">

                                <div
                                    class="btn-group btn-group-sm"
                                    role="group"
                                >

                                    {{-- View quotation details --}}
                                    <a
                                        href="{{ route(
                                            'customer.quotes.show',
                                            $quote
                                        ) }}"
                                        class="btn btn-outline-info"
                                        title="View Quotation"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- View/download quotation PDF --}}
                                    @if($canViewQuotation)

                                        <a
                                            href="{{ route(
                                                'admin.quotes.download',
                                                $quote
                                            ) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-outline-success"
                                            title="Download Quotation"
                                        >
                                            <i class="fas fa-file-pdf"></i>
                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                class="text-center py-5"
                            >
                                <i
                                    class="
                                        fas
                                        fa-file-invoice
                                        fa-3x
                                        text-muted
                                        mb-3
                                        d-block
                                    "
                                ></i>

                                <p class="text-muted mb-0">
                                    No quotations are currently available.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@if($quotes->hasPages())
    <div class="mt-3">
        {{
            $quotes
                ->appends(
                    request()->query()
                )
                ->links(
                    
                )
        }}
    </div>
@endif

@endsection
