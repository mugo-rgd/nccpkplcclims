@extends('layouts.app')

@section('title', 'Reports - KPLC Meter Lab')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                Reports
            </h1>
            <p class="text-muted mb-0">
                View and export reports for all system data
            </p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Calibrations</h6>
                    <h2 class="mb-0">{{ $stats['total_calibrations'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Pending</h6>
                    <h2 class="mb-0">{{ $stats['pending_calibrations'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed</h6>
                    <h2 class="mb-0">{{ $stats['completed_calibrations'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Customers</h6>
                    <h2 class="mb-0">{{ $stats['total_customers'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Types -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-hover">
                <div class="card-body text-center">
                    <i class="fas fa-microscope fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Calibrations Report</h5>
                    <p class="text-muted">View and export calibration data</p>
                    <a href="{{ route('reports.calibrations') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-hover">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Customers Report</h5>
                    <p class="text-muted">View customer activity data</p>
                    <a href="{{ route('reports.customers') }}" class="btn btn-success">
                        <i class="fas fa-arrow-right me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-hover">
                <div class="card-body text-center">
                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Certificates Report</h5>
                    <p class="text-muted">View certificate generation data</p>
                    <a href="{{ route('reports.certificates') }}" class="btn btn-danger">
                        <i class="fas fa-arrow-right me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-hover">
                <div class="card-body text-center">
                    <i class="fas fa-microchip fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Equipment Report</h5>
                    <p class="text-muted">View equipment inventory data</p>
                    <a href="{{ route('reports.equipment') }}" class="btn btn-info text-white">
                        <i class="fas fa-arrow-right me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-hover">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Finance Report</h5>
                    <p class="text-muted">View revenue, payments, and financial performance</p>
                    <a href="{{ route('reports.finance') }}" class="btn btn-warning text-white">
                        <i class="fas fa-arrow-right me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.shadow-hover {
    transition: all 0.3s ease;
}
.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
</style>
@endpush
