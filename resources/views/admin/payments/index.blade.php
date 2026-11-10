
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-money-check-alt me-2 text-primary"></i>
                Payment Management
            </h2>
            <div class="text-muted">
                Quotation: <strong>{{ $quote->quote_number }}</strong>
            </div>
        </div>

        @if(Route::has('admin.payments.dashboard'))
            <a href="{{ route('admin.payments.dashboard') }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Payments Dashboard
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small mb-2">Quotation Total</div>
                    <h4 class="mb-0">
                        KES {{ number_format((float)$quote->total_amount, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small mb-2">Confirmed Amount Paid</div>
                    <h4 class="mb-0 text-success">
                        KES {{ number_format((float)$quote->amount_paid, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small mb-2">Outstanding Balance</div>
                    <h4 class="mb-0 text-danger">
                        KES {{ number_format((float)$quote->balance_due, 2) }}
                    </h4>
                    <span class="badge bg-secondary mt-2">
                        {{ ucwords(str_replace('_', ' ', $quote->status)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @can('payments.record')
        @if($quote->canReceivePayment())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Record Manual Payment
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST"
                          action="{{ route('admin.payments.store', $quote) }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Payment Method</label>
                                <select name="method"
                                        class="form-select @error('method') is-invalid @enderror"
                                        required>
                                    @foreach(['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'cheque' => 'Cheque'] as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('method', 'bank_transfer') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Amount (KES)</label>
                                <input type="number"
                                       name="amount"
                                       step="0.01"
                                       min="0.01"
                                       max="{{ $quote->balance_due }}"
                                       value="{{ old('amount') }}"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Payment Date</label>
                                <input type="date"
                                       name="payment_date"
                                       value="{{ old('payment_date', now()->toDateString()) }}"
                                       max="{{ now()->toDateString() }}"
                                       class="form-control @error('payment_date') is-invalid @enderror"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Transaction Reference</label>
                                <input type="text"
                                       name="reference"
                                       maxlength="120"
                                       value="{{ old('reference') }}"
                                       class="form-control @error('reference') is-invalid @enderror"
                                       required>
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Receipt (PDF/JPG/PNG, max 5MB)</label>
                                <input type="file"
                                       name="receipt"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="form-control @error('receipt') is-invalid @enderror"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Cheque Clearance Evidence</label>
                                <input type="file"
                                       name="clearance"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="form-control">
                                <div class="form-text">
                                    Required before a cheque payment can be confirmed.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <textarea name="notes"
                                          class="form-control"
                                          rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-paper-plane me-1"></i>
                            Submit for Verification
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Payment Transactions</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Evidence</th>
                            <th>Recorded By</th>
                            <th>Verified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    {{ $payment->payment_date?->format('d M Y') ?? '—' }}
                                </td>

                                <td>
                                    {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                                </td>

                                <td>
                                    <span class="font-monospace">
                                        {{ $payment->reference ?? '—' }}
                                    </span>
                                </td>

                                <td class="text-end text-nowrap">
                                    KES {{ number_format((float)$payment->amount, 2) }}
                                </td>

                                <td>
                                    @switch($payment->status)
                                        @case('confirmed')
                                            <span class="badge bg-success">Confirmed</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                    @endswitch
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('payments.receipts.download')
                                            @if($payment->receipt_path && Route::has('admin.payments.receipt'))
                                                <a href="{{ route('admin.payments.receipt', $payment) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-download me-1"></i>
                                                    Receipt
                                                </a>
                                            @endif

                                            @if($payment->clearance_path && Route::has('admin.payments.clearance'))
                                                <a href="{{ route('admin.payments.clearance', $payment) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    Clearance
                                                </a>
                                            @endif
                                        @endcan
                                    </div>
                                </td>

                                <td>{{ $payment->recorder?->name ?? 'System / M-Pesa' }}</td>

                                <td>
                                    {{ $payment->confirmer?->name ?? '—' }}
                                </td>

                                <td>
                                    @if($payment->status === 'pending' && $payment->method !== 'mpesa')
                                        <div class="d-flex flex-wrap align-items-start gap-2">

                                            @can('payments.confirm')
                                                @if(
                                                    $payment->recorded_by !== null &&
                                                    (int)$payment->recorded_by !== (int)auth()->id()
                                                )
                                                    <form method="POST"
                                                          action="{{ route('admin.payments.confirm', $payment) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success"
                                                                onclick="return confirm('Confirm that funds have been independently verified?')">
                                                            <i class="fas fa-check me-1"></i>
                                                            Confirm
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('payments.reject')
                                                <form method="POST"
                                                      action="{{ route('admin.payments.reject', $payment) }}"
                                                      class="d-flex gap-1">
                                                    @csrf
                                                    <input type="text"
                                                           name="rejection_reason"
                                                           maxlength="2000"
                                                           class="form-control form-control-sm"
                                                           placeholder="Rejection reason"
                                                           aria-label="Rejection reason"
                                                           required>
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Reject this payment?')">
                                                        Reject
                                                    </button>
                                                </form>
                                            @endcan

                                        </div>
                                    @elseif($payment->status === 'confirmed')
                                        <span class="text-success small">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Verified
                                        </span>
                                    @elseif($payment->status === 'rejected')
                                        <span class="text-danger small">Rejected</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No payment transactions have been recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
