@extends('layouts.app')

@section('title', 'Finance Report - KPLC Meter Lab')

@section('content')
@php
    $money = static fn ($value) => 'KES ' . number_format((float) ($value ?? 0), 2);

    $statusColors = [
        'paid' => 'success',
        'pending' => 'warning',
        'quoted' => 'info',
        'assigned' => 'primary',
        'completed' => 'secondary',
        'overdue' => 'danger',
        'partially_paid' => 'warning',
        'refunded' => 'secondary',
        'failed' => 'danger',
        'unclassified' => 'dark',
    ];

    $paymentLabels = [
        'mpesa' => 'M-Pesa',
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'card' => 'Card',
        'eft' => 'EFT',
    ];

    $paymentIcons = [
        'mpesa' => 'fa-mobile-alt text-success',
        'cash' => 'fa-money-bill-wave text-success',
        'bank_transfer' => 'fa-university text-primary',
        'cheque' => 'fa-file-invoice text-secondary',
        'card' => 'fa-credit-card text-info',
        'eft' => 'fa-money-check-alt text-primary',
    ];

    $equipmentTotals = collect($revenueByEquipment ?? []);
    $customerTotals = collect($revenueByCustomer ?? []);
    $technicianTotals = collect($revenueByTechnician ?? []);
    $costTotals = collect($costBreakdown ?? []);
    $agingTotals = collect($agingAnalysis ?? []);

    $periodLabel = \Carbon\Carbon::parse($startDate)->format('d M Y')
        . ' to '
        . \Carbon\Carbon::parse($endDate)->format('d M Y');
@endphp

<div class="container-fluid finance-report-page">

    {{-- ===================================================== --}}
    {{-- PAGE HEADER                                           --}}
    {{-- ===================================================== --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4 no-print">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-coins text-success me-2"></i>
                Meter Laboratory Finance Report
            </h1>

            <p class="text-muted mb-0">
                Revenue, collections, receivables, costs, profitability, customers,
                equipment types, technicians, and payment performance.
            </p>

            <small class="text-muted">
                Reporting period: <strong>{{ $periodLabel }}</strong>
            </small>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Reports
            </a>

            <button type="button" onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i>
                Print
            </button>

            <a
                href="{{ route('reports.finance.export', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]) }}"
                class="btn btn-success"
            >
                <i class="fas fa-file-csv me-1"></i>
                Export CSV
            </a>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- FILTERS                                               --}}
    {{-- ===================================================== --}}
    <div class="card report-card mb-4 no-print">
        <div class="card-body">
            <form
                method="GET"
                action="{{ route('reports.finance') }}"
                class="row g-3 align-items-end"
            >
                <div class="col-lg-3 col-md-4">
                    <label for="start_date" class="form-label">
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', $startDate) }}"
                        required
                    >

                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-3 col-md-4">
                    <label for="end_date" class="form-label">
                        End Date
                    </label>

                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date', $endDate) }}"
                        required
                    >

                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-2 col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>
                        Apply
                    </button>
                </div>

                <div class="col-lg-2 col-md-4">
                    <a href="{{ route('reports.finance') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i>
                        Reset
                    </a>
                </div>

                <div class="col-lg-2 col-md-4">
                    <a
                        href="{{ route('reports.finance.export', [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]) }}"
                        class="btn btn-success w-100"
                    >
                        <i class="fas fa-download me-1"></i>
                        Export
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- EXECUTIVE SUMMARY                                     --}}
    {{-- ===================================================== --}}
    <div class="section-heading mb-3">
        <div>
            <h4 class="mb-1">Executive Financial Summary</h4>
            <p class="text-muted mb-0">
                Headline indicators for the selected reporting period.
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card metric-revenue h-100">
                <div class="metric-card-body">
                    <div>
                        <span class="metric-label">Revenue Generated</span>
                        <h3>{{ $money($financeStats['total_revenue_generated'] ?? 0) }}</h3>
                        <small>
                            {{ number_format($financeStats['total_calibrations'] ?? 0) }}
                            calibration(s)
                        </small>
                    </div>

                    <span class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card metric-collected h-100">
                <div class="metric-card-body">
                    <div>
                        <span class="metric-label">Revenue Collected</span>
                        <h3>{{ $money($financeStats['revenue_collected'] ?? 0) }}</h3>
                        <small>
                            Collection rate:
                            {{ number_format($financeStats['collection_rate'] ?? 0, 1) }}%
                        </small>
                    </div>

                    <span class="metric-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card metric-outstanding h-100">
                <div class="metric-card-body">
                    <div>
                        <span class="metric-label">Outstanding Receivables</span>
                        <h3>{{ $money($financeStats['outstanding'] ?? 0) }}</h3>
                        <small>
                            Overdue:
                            {{ $money($financeStats['overdue_amount'] ?? 0) }}
                        </small>
                    </div>

                    <span class="metric-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card metric-profit h-100">
                <div class="metric-card-body">
                    <div>
                        <span class="metric-label">Net Operating Profit</span>
                        <h3>{{ $money($financeStats['net_profit'] ?? 0) }}</h3>
                        <small>
                            Margin:
                            {{ number_format($financeStats['profit_margin'] ?? 0, 1) }}%
                        </small>
                    </div>

                    <span class="metric-icon">
                        <i class="fas fa-sack-dollar"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-stat-card">
                <span>Average Revenue</span>
                <strong>{{ $money($financeStats['avg_revenue_per_calibration'] ?? 0) }}</strong>
                <small>Per calibration</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-stat-card">
                <span>Average Collected</span>
                <strong>{{ $money($financeStats['avg_collected_per_calibration'] ?? 0) }}</strong>
                <small>Per calibration</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-stat-card">
                <span>Total Costs</span>
                <strong>{{ $money($financeStats['total_costs'] ?? 0) }}</strong>
                <small>Operating costs</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-stat-card">
                <span>Paid Jobs</span>
                <strong>{{ number_format($financeStats['paid_calibrations'] ?? 0) }}</strong>
                <small>Fully paid</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-stat-card">
                <span>Partially Paid</span>
                <strong>{{ number_format($financeStats['partially_paid_calibrations'] ?? 0) }}</strong>
                <small>Balance outstanding</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-stat-card">
                <span>Unpaid Jobs</span>
                <strong>{{ number_format($financeStats['unpaid_calibrations'] ?? 0) }}</strong>
                <small>
                    {{ number_format($financeStats['overdue_count'] ?? 0) }} overdue
                </small>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MONTHLY PERFORMANCE                                   --}}
    {{-- ===================================================== --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card report-card h-100">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-chart-column text-primary me-2"></i>
                            Monthly Revenue, Collections, Costs and Profit
                        </h5>

                        <small class="text-muted">
                            Financial movement by month.
                        </small>
                    </div>
                </div>

                <div class="card-body chart-area">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card report-card h-100">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-chart-pie text-success me-2"></i>
                            Payment Status
                        </h5>

                        <small class="text-muted">
                            Revenue generated by payment status.
                        </small>
                    </div>
                </div>

                <div class="card-body chart-area">
                    <canvas id="revenueByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- COSTS AND PAYMENT METHODS                             --}}
    {{-- ===================================================== --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card report-card h-100">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-receipt text-danger me-2"></i>
                            Operating Cost Breakdown
                        </h5>

                        <small class="text-muted">
                            Per diem, transport, and other laboratory costs.
                        </small>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <div class="chart-area-small">
                                <canvas id="costBreakdownChart"></canvas>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cost Category</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($costBreakdown ?? [] as $cost)
                                            <tr>
                                                <td>{{ $cost['category'] ?? 'Unknown' }}</td>
                                                <td class="text-end">
                                                    {{ $money($cost['amount'] ?? 0) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($cost['percentage'] ?? 0, 1) }}%
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    No cost data available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    <tfoot>
                                        <tr class="table-light fw-bold">
                                            <td>Total</td>
                                            <td class="text-end">
                                                {{ $money($costTotals->sum('amount')) }}
                                            </td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card report-card h-100">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-credit-card text-warning me-2"></i>
                            Payment Method Breakdown
                        </h5>

                        <small class="text-muted">
                            Collection channels used by customers.
                        </small>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <div class="chart-area-small">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Method</th>
                                            <th class="text-end">Transactions</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($paymentMethods ?? [] as $method)
                                            @php
                                                $methodKey = $method['method'] ?? 'unknown';
                                            @endphp

                                            <tr>
                                                <td>
                                                    <i class="fas {{ $paymentIcons[$methodKey] ?? 'fa-wallet text-secondary' }} me-1"></i>
                                                    {{ $paymentLabels[$methodKey] ?? ucfirst(str_replace('_', ' ', $methodKey)) }}
                                                </td>

                                                <td class="text-end">
                                                    {{ number_format($method['transactions'] ?? 0) }}
                                                </td>

                                                <td class="text-end">
                                                    {{ $money($method['amount'] ?? 0) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ number_format($method['percentage'] ?? 0, 1) }}%
                                                    </small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    No payment-method data available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- EQUIPMENT TYPE PROFITABILITY                          --}}
    {{-- ===================================================== --}}
    <div class="card report-card mb-4">
        <div class="card-header">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-microchip text-info me-2"></i>
                    Revenue and Profitability by Equipment Type
                </h5>

                <small class="text-muted">
                    Financial contribution of each meter or laboratory equipment category.
                </small>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Equipment Type</th>
                            <th class="text-center">Jobs</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Collected</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">Costs</th>
                            <th class="text-end">Profit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($revenueByEquipment ?? [] as $item)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $item['equipment_type'] ?? 'Unknown' }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($item['count'] ?? 0) }}
                                </td>

                                <td class="text-end">
                                    {{ $money($item['revenue'] ?? 0) }}
                                </td>

                                <td class="text-end text-success">
                                    {{ $money($item['collected'] ?? 0) }}
                                </td>

                                <td class="text-end text-danger">
                                    {{ $money($item['outstanding'] ?? 0) }}
                                </td>

                                <td class="text-end">
                                    {{ $money($item['costs'] ?? 0) }}
                                </td>

                                <td class="text-end">
                                    <span class="{{ ($item['profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $money($item['profit'] ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No equipment revenue data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td>TOTAL</td>
                            <td class="text-center">
                                {{ number_format($equipmentTotals->sum('count')) }}
                            </td>
                            <td class="text-end">
                                {{ $money($equipmentTotals->sum('revenue')) }}
                            </td>
                            <td class="text-end">
                                {{ $money($equipmentTotals->sum('collected')) }}
                            </td>
                            <td class="text-end">
                                {{ $money($equipmentTotals->sum('outstanding')) }}
                            </td>
                            <td class="text-end">
                                {{ $money($equipmentTotals->sum('costs')) }}
                            </td>
                            <td class="text-end">
                                {{ $money($equipmentTotals->sum('profit')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CUSTOMER AND TECHNICIAN PERFORMANCE                   --}}
    {{-- ===================================================== --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card report-card h-100">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-building text-primary me-2"></i>
                            Top Customers by Revenue
                        </h5>

                        <small class="text-muted">
                            Customer contribution and receivable exposure.
                        </small>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-center">Jobs</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Collected</th>
                                    <th class="text-end">Outstanding</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($revenueByCustomer ?? [] as $item)
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $item['customer_name'] ?? 'Unknown' }}
                                        </td>

                                        <td class="text-center">
                                            {{ number_format($item['count'] ?? 0) }}
                                        </td>

                                        <td class="text-end">
                                            {{ $money($item['revenue'] ?? 0) }}
                                        </td>

                                        <td class="text-end text-success">
                                            {{ $money($item['collected'] ?? 0) }}
                                        </td>

                                        <td class="text-end text-danger">
                                            {{ $money($item['outstanding'] ?? 0) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No customer revenue data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td>TOTAL DISPLAYED</td>
                                    <td class="text-center">
                                        {{ number_format($customerTotals->sum('count')) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $money($customerTotals->sum('revenue')) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $money($customerTotals->sum('collected')) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $money($customerTotals->sum('outstanding')) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card report-card h-100">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-user-cog text-secondary me-2"></i>
                            Technician Financial Performance
                        </h5>

                        <small class="text-muted">
                            Workload, completion, billed revenue, and collections.
                        </small>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th class="text-center">Jobs</th>
                                    <th class="text-center">Done</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($revenueByTechnician ?? [] as $item)
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $item['technician_name'] ?? 'Unassigned' }}
                                        </td>

                                        <td class="text-center">
                                            {{ number_format($item['count'] ?? 0) }}
                                        </td>

                                        <td class="text-center">
                                            {{ number_format($item['completed'] ?? 0) }}
                                        </td>

                                        <td class="text-end">
                                            {{ $money($item['revenue'] ?? 0) }}
                                            <br>
                                            <small class="text-success">
                                                Collected:
                                                {{ $money($item['collected'] ?? 0) }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No technician financial data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-center">
                                        {{ number_format($technicianTotals->sum('count')) }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($technicianTotals->sum('completed')) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $money($technicianTotals->sum('revenue')) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- AGING ANALYSIS                                        --}}
    {{-- ===================================================== --}}
    <div class="card report-card mb-4">
        <div class="card-header">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-clock text-danger me-2"></i>
                    Receivables Aging Analysis
                </h5>

                <small class="text-muted">
                    Outstanding balances grouped by age.
                </small>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="chart-area-small">
                        <canvas id="agingAnalysisChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Age Category</th>
                                    <th class="text-end">Invoices</th>
                                    <th class="text-end">Outstanding</th>
                                    <th class="text-end">Share</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($agingAnalysis ?? [] as $item)
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $item['category'] ?? 'Unknown' }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($item['count'] ?? 0) }}
                                        </td>

                                        <td class="text-end">
                                            {{ $money($item['amount'] ?? 0) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($item['percentage'] ?? 0, 1) }}%
                                        </td>

                                        <td>
                                            @if(($item['days'] ?? 999) <= 30)
                                                <span class="badge bg-success">Current</span>
                                            @elseif(($item['days'] ?? 999) <= 60)
                                                <span class="badge bg-warning text-dark">Watch</span>
                                            @elseif(($item['days'] ?? 999) <= 90)
                                                <span class="badge bg-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-dark">Critical</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No outstanding receivables.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-end">
                                        {{ number_format($agingTotals->sum('count')) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $money($agingTotals->sum('amount')) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $agingTotals->sum('amount') > 0 ? '100%' : '0%' }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- RECENT FINANCIAL TRANSACTIONS                         --}}
    {{-- ===================================================== --}}
    <div class="card report-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-list text-primary me-2"></i>
                    Recent Calibrations with Financial Data
                </h5>

                <small class="text-muted">
                    Latest billing and payment records within the selected period.
                </small>
            </div>

            <span class="badge bg-secondary">
                {{ collect($recentCalibrations ?? [])->count() }} record(s)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 financial-detail-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Equipment</th>
                            <th>Equipment Type</th>
                            <th>Payment Status</th>
                            <th class="text-end">Amount Due</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">Costs</th>
                            <th>Payment Method</th>
                            <th>Technician</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse(($recentCalibrations ?? collect())->filter() as $cal)
                            @php
                                $paymentStatus = $cal?->payment_status ?: 'pending';
                                $statusClass = $statusColors[$paymentStatus] ?? 'secondary';

                                $amountDue = (float) ($cal?->amount_due ?? 0);
                                $amountPaid = (float) ($cal?->amount_paid ?? 0);
                                $outstanding = max($amountDue - $amountPaid, 0);

                                $costs =
                                    (float) ($cal?->per_diem_cost ?? 0)
                                    + (float) ($cal?->transport_cost ?? 0)
                                    + (float) ($cal?->other_costs ?? 0);

                                $methodKey = $cal?->payment_method ?: 'unknown';
                            @endphp

                            <tr>
                                <td>
                                    <a
                                        href="{{ route('calibrations.show', $cal) }}"
                                        class="fw-semibold text-decoration-none"
                                    >
                                        #{{ $cal?->id ?? 'N/A' }}
                                    </a>
                                </td>

                                <td>
                                    {{ $cal?->customer?->company_name
                                        ?? $cal?->customer?->name
                                        ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $cal?->equipment_name
                                        ?? $cal?->equipment?->name
                                        ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $cal?->serial_number
                                            ?? $cal?->equipment?->serial_number
                                            ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $cal?->equipmentType?->name
                                        ?? $cal?->equipment_type
                                        ?? 'N/A' }}
                                </td>

                                <td>
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    {{ $money($amountDue) }}
                                </td>

                                <td class="text-end text-success">
                                    {{ $money($amountPaid) }}
                                </td>

                                <td class="text-end">
                                    <span class="{{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $money($outstanding) }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    {{ $money($costs) }}
                                </td>

                                <td>
                                    @if($methodKey !== 'unknown')
                                        <i class="fas {{ $paymentIcons[$methodKey] ?? 'fa-wallet text-secondary' }} me-1"></i>
                                        {{ $paymentLabels[$methodKey] ?? ucfirst(str_replace('_', ' ', $methodKey)) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $cal?->performedBy?->name ?? 'Unassigned' }}
                                </td>

                                <td>
                                    {{ $cal?->created_at?->format('d/m/Y') ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5">
                                    <i class="fas fa-receipt fa-3x mb-3 d-block"></i>
                                    No financial records found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="report-footer text-center text-muted">
        <p class="mb-1">
            Meter Central Laboratory — Kenya Power &amp; Lighting Company PLC
        </p>
        <small>
            Generated {{ now()->format('d M Y H:i:s') }}
            | Reporting period {{ $periodLabel }}
        </small>
    </div>
</div>
@endsection

@push('styles')
<style>
    .finance-report-page {
        --report-primary: #0b2d5c;
        --report-secondary: #1a237e;
        --report-success: #198754;
        --report-warning: #d39e00;
        --report-danger: #c82333;
        padding-bottom: 2rem;
    }

    .report-card {
        overflow: hidden;
        border: 1px solid #e4e8ee;
        border-radius: 0.85rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .report-card .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 64px;
        padding: 0.9rem 1.1rem;
        border-bottom: 1px solid #e8edf3;
        background: #fff;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        align-items: end;
    }

    .metric-card {
        overflow: hidden;
        border: 0;
        border-radius: 0.9rem;
        color: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
    }

    .metric-revenue {
        background: linear-gradient(135deg, #198754, #20a566);
    }

    .metric-collected {
        background: linear-gradient(135deg, #0d6efd, #3d8bfd);
    }

    .metric-outstanding {
        background: linear-gradient(135deg, #d39e00, #f0b429);
    }

    .metric-profit {
        background: linear-gradient(135deg, #0dcaf0, #157a8a);
    }

    .metric-card-body {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        min-height: 150px;
        padding: 1.25rem;
    }

    .metric-label {
        display: block;
        margin-bottom: 0.4rem;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .metric-card h3 {
        margin: 0 0 0.5rem;
        font-size: clamp(1.25rem, 2vw, 1.75rem);
        font-weight: 800;
    }

    .metric-card small {
        color: rgba(255, 255, 255, 0.82);
    }

    .metric-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        font-size: 1.5rem;
    }

    .small-stat-card {
        min-height: 112px;
        padding: 1rem;
        border: 1px solid #e4e8ee;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    }

    .small-stat-card span,
    .small-stat-card small {
        display: block;
        color: #6c757d;
    }

    .small-stat-card strong {
        display: block;
        margin: 0.35rem 0;
        color: var(--report-primary);
        font-size: 1.05rem;
    }

    .chart-area {
        position: relative;
        width: 100%;
        height: 340px;
        min-height: 340px;
        overflow: hidden;
    }

    .chart-area-small {
        position: relative;
        width: 100%;
        height: 260px;
        min-height: 260px;
        overflow: hidden;
    }

    .chart-area canvas,
    .chart-area-small canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
        max-width: 100%;
        max-height: 100%;
    }

    .finance-report-page table thead th {
        border-bottom-width: 1px;
        background: #f4f7fb;
        color: #344054;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .finance-report-page table td {
        font-size: 0.88rem;
    }

    .financial-detail-table {
        min-width: 1500px;
    }

    .report-footer {
        padding: 1rem 0;
        border-top: 1px solid #e4e8ee;
    }

    @media (max-width: 767.98px) {
        .metric-card-body {
            min-height: 130px;
        }

        .chart-area,
        .chart-area-small {
            height: 280px;
            min-height: 280px;
        }
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            background: #fff !important;
        }

        .no-print,
        .navbar,
        .sidebar,
        .footer {
            display: none !important;
        }

        .finance-report-page {
            padding: 0;
        }

        .report-card,
        .metric-card,
        .small-stat-card {
            break-inside: avoid;
            box-shadow: none !important;
        }

        .metric-card {
            border: 1px solid #bbb;
            color: #000 !important;
            background: #fff !important;
        }

        .metric-label,
        .metric-card small {
            color: #555 !important;
        }

        .metric-card h3 {
            color: #000 !important;
        }

        .chart-area {
            height: 260px;
            min-height: 260px;
        }

        .chart-area-small {
            height: 220px;
            min-height: 220px;
        }

        .financial-detail-table {
            min-width: 0;
            font-size: 8px;
        }
    }
</style>
@endpush

@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    <script>
        (() => {
            'use strict';

            function initializeFinanceCharts() {
                if (window.__financeChartsInitialized) {
                    return;
                }

                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not available.');
                    return;
                }

                window.__financeChartsInitialized = true;

                const destroyExistingChart = (canvas) => {
                    if (!canvas) {
                        return;
                    }

                    const chart = Chart.getChart(canvas);

                    if (chart) {
                        chart.destroy();
                    }
                };

                const currencyFormatter = new Intl.NumberFormat('en-KE', {
                    style: 'currency',
                    currency: 'KES',
                    maximumFractionDigits: 2,
                });

                const chartDefaults = {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 300,
                    },
                    resizeDelay: 150,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    const label =
                                        context.dataset.label ||
                                        context.label ||
                                        '';

                                    const value = Number(
                                        context.raw || 0
                                    );

                                    return `${label}: ${
                                        currencyFormatter.format(value)
                                    }`;
                                },
                            },
                        },
                    },
                };

                const statusData =
                    @json($revenueByStatus ?? []);

                const monthlyData =
                    @json($monthlyRevenue ?? []);

                const paymentData =
                    @json($paymentMethods ?? []);

                const costData =
                    @json($costBreakdown ?? []);

                const agingData =
                    @json($agingAnalysis ?? []);

                /*
                 * Payment-status chart
                 */
                const statusCanvas = document.getElementById(
                    'revenueByStatusChart'
                );

                if (statusCanvas) {
                    destroyExistingChart(statusCanvas);

                    const statusKeys = Object.keys(statusData);

                    new Chart(statusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: statusKeys.map(value =>
                                value
                                    .replace(/_/g, ' ')
                                    .replace(
                                        /\b\w/g,
                                        letter => letter.toUpperCase()
                                    )
                            ),
                            datasets: [{
                                data: statusKeys.map(key =>
                                    Number(
                                        statusData[key]?.amount || 0
                                    )
                                ),
                                backgroundColor: [
                                    '#198754',
                                    '#ffc107',
                                    '#0dcaf0',
                                    '#0d6efd',
                                    '#6c757d',
                                    '#dc3545',
                                    '#212529',
                                    '#6610f2',
                                ],
                                borderWidth: 2,
                            }],
                        },
                        options: chartDefaults,
                    });
                }

                /*
                 * Monthly financial performance
                 */
                const monthlyCanvas = document.getElementById(
                    'monthlyRevenueChart'
                );

                if (monthlyCanvas) {
                    destroyExistingChart(monthlyCanvas);

                    new Chart(monthlyCanvas, {
                        type: 'bar',
                        data: {
                            labels: monthlyData.map(
                                item => item.month || ''
                            ),
                            datasets: [
                                {
                                    label: 'Revenue Generated',
                                    data: monthlyData.map(item =>
                                        Number(item.generated || 0)
                                    ),
                                    backgroundColor:
                                        'rgba(25, 135, 84, 0.72)',
                                    borderColor: '#198754',
                                    borderWidth: 1,
                                },
                                {
                                    label: 'Collected',
                                    data: monthlyData.map(item =>
                                        Number(item.collected || 0)
                                    ),
                                    backgroundColor:
                                        'rgba(13, 110, 253, 0.72)',
                                    borderColor: '#0d6efd',
                                    borderWidth: 1,
                                },
                                {
                                    label: 'Costs',
                                    data: monthlyData.map(item =>
                                        Number(item.costs || 0)
                                    ),
                                    backgroundColor:
                                        'rgba(220, 53, 69, 0.65)',
                                    borderColor: '#dc3545',
                                    borderWidth: 1,
                                },
                                {
                                    type: 'line',
                                    label: 'Profit',
                                    data: monthlyData.map(item =>
                                        Number(item.profit || 0)
                                    ),
                                    borderColor: '#6f42c1',
                                    backgroundColor: '#6f42c1',
                                    borderWidth: 3,
                                    pointRadius: 3,
                                    tension: 0.3,
                                },
                            ],
                        },
                        options: {
                            ...chartDefaults,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback(value) {
                                            return `KES ${
                                                Number(value)
                                                    .toLocaleString('en-KE')
                                            }`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                /*
                 * Payment-method chart
                 */
                const paymentCanvas = document.getElementById(
                    'paymentMethodChart'
                );

                if (paymentCanvas && paymentData.length > 0) {
                    destroyExistingChart(paymentCanvas);

                    new Chart(paymentCanvas, {
                        type: 'pie',
                        data: {
                            labels: paymentData.map(item =>
                                String(item.method || 'Unknown')
                                    .replace(/_/g, ' ')
                                    .replace(
                                        /\b\w/g,
                                        letter => letter.toUpperCase()
                                    )
                            ),
                            datasets: [{
                                data: paymentData.map(item =>
                                    Number(item.amount || 0)
                                ),
                                backgroundColor: [
                                    '#00b894',
                                    '#0d6efd',
                                    '#ffc107',
                                    '#6c757d',
                                    '#0dcaf0',
                                    '#6610f2',
                                ],
                                borderWidth: 2,
                            }],
                        },
                        options: chartDefaults,
                    });
                }

                /*
                 * Cost chart
                 */
                const costCanvas = document.getElementById(
                    'costBreakdownChart'
                );

                if (costCanvas && costData.length > 0) {
                    destroyExistingChart(costCanvas);

                    new Chart(costCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: costData.map(
                                item => item.category || 'Unknown'
                            ),
                            datasets: [{
                                data: costData.map(item =>
                                    Number(item.amount || 0)
                                ),
                                backgroundColor: [
                                    '#dc3545',
                                    '#fd7e14',
                                    '#6c757d',
                                ],
                                borderWidth: 2,
                            }],
                        },
                        options: chartDefaults,
                    });
                }

                /*
                 * Aging chart
                 */
                const agingCanvas = document.getElementById(
                    'agingAnalysisChart'
                );

                if (agingCanvas && agingData.length > 0) {
                    destroyExistingChart(agingCanvas);

                    new Chart(agingCanvas, {
                        type: 'bar',
                        data: {
                            labels: agingData.map(
                                item => item.category || 'Unknown'
                            ),
                            datasets: [{
                                label: 'Outstanding Receivables',
                                data: agingData.map(item =>
                                    Number(item.amount || 0)
                                ),
                                backgroundColor: [
                                    '#198754',
                                    '#ffc107',
                                    '#dc3545',
                                    '#212529',
                                ],
                                borderWidth: 1,
                            }],
                        },
                        options: {
                            ...chartDefaults,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback(value) {
                                            return `KES ${
                                                Number(value)
                                                    .toLocaleString('en-KE')
                                            }`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener(
                    'DOMContentLoaded',
                    initializeFinanceCharts,
                    { once: true }
                );
            } else {
                initializeFinanceCharts();
            }
        })();
    </script>
@endpush
