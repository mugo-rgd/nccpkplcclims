@extends('layouts.app')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1"><i class="fas fa-money-check-alt text-primary me-2"></i>Payment Management</h2>
            <p class="text-muted mb-0">Finance and Accounts · Payments across quotations</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Confirmed receipts (KES)</small><h4>{{ number_format((float) $summary['confirmed_total'], 2) }}</h4></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Pending verification</small><h4>{{ number_format($summary['pending_count']) }}</h4></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Confirmed transactions</small><h4>{{ number_format($summary['confirmed_count']) }}</h4></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Outstanding sent quotations (KES)</small><h4>{{ number_format((float) $summary['outstanding_total'], 2) }}</h4></div></div></div>
    </div>

    <div class="card mb-3"><div class="card-body">
        <form method="GET" action="{{ route('admin.payments.dashboard') }}" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label">Quotation or reference</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search quotation / transaction"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All statuses</option>@foreach(['pending'=>'Pending', 'confirmed'=>'Confirmed', 'rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Method</label><select class="form-select" name="method"><option value="">All methods</option>@foreach(['bank_transfer'=>'Bank transfer', 'cash'=>'Cash', 'cheque'=>'Cheque', 'mpesa'=>'M-Pesa'] as $value=>$label)<option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Filter</button><a class="btn btn-outline-secondary" href="{{ route('admin.payments.dashboard') }}">Reset</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Date</th><th>Quotation</th><th>Method</th><th>Reference</th><th class="text-end">Amount (KES)</th><th>Status</th><th>Recorded by</th><th>Receipt</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($transactions as $payment)
            <tr>
                <td>{{ $payment->created_at?->format('d M Y H:i') ?? '—' }}</td>
                <td>{{ $payment->quote?->quote_number ?? 'Quotation unavailable' }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                <td>{{ $payment->reference }}</td>
                <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                <td><span class="badge bg-{{ $payment->status === 'confirmed' ? 'success' : ($payment->status === 'pending' ? 'warning text-dark' : 'secondary') }}">{{ ucfirst($payment->status) }}</span></td>
                <td>{{ $payment->recorder?->name ?? '—' }}</td>
                <td>@can('payments.receipts.download') @if($payment->receipt_path && Route::has('admin.payments.receipt'))<a href="{{ route('admin.payments.receipt', $payment) }}">Receipt</a>@else — @endif @else — @endcan</td>
                <td>@if($payment->quote && Route::has('admin.payments.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.payments.index', $payment->quote) }}">Manage</a>@else — @endif</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No payment transactions match your filters.</td></tr>
        @endforelse
        </tbody>
    </table></div></div><div class="card-footer">{{ $transactions->links() }}</div></div>
</div>
@endsection
