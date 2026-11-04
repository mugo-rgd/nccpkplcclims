{{-- @extends('layouts.app')

@section('title', 'User Guide - KPLC Meter Lab')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="card shadow-lg" style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);">
        <div class="card-body text-white p-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">
                        <i class="fas fa-book-open me-3"></i>User Guide
                    </h1>
                    <p class="lead opacity-75">
                        Comprehensive guide for using the Meter Central Laboratory System
                    </p>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('user-guide.search') }}" method="GET" class="d-flex">
                        <input type="text" name="q" placeholder="Search the guide..."
                               class="form-control form-control-lg border-0"
                               style="border-radius: 50px 0 0 50px;">
                        <button type="submit" class="btn btn-light btn-lg"
                                style="border-radius: 0 50px 50px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Cards -->
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                    <h6>Getting Started</h6>
                    <small class="text-muted">Login, navigation, basics</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-flask fa-3x text-success mb-3"></i>
                    <h6>Test Management</h6>
                    <small class="text-muted">Add results, uncertainty</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-certificate fa-3x text-warning mb-3"></i>
                    <h6>Certificates</h6>
                    <small class="text-muted">Generate, sign, send</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-question-circle fa-3x text-danger mb-3"></i>
                    <h6>FAQ</h6>
                    <small class="text-muted">Common questions</small>
                </div>
            </div>
        </div>
    </div>

    <!-- TEST MANAGEMENT SUMMARY CARD -->
    <div class="card shadow mt-4 border-success">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-flask me-2"></i>Test Management Module
                </h4>
                <a href="{{ route('user-guide.category', 'test-management') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-book-open me-1"></i> View Full Guide
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>New Feature:</strong> The Test Management module allows technicians to record test results with automated uncertainty calculations for calibration certificates.
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('user-guide.category', 'test-management') }}" class="btn btn-success">
                        <i class="fas fa-arrow-right me-1"></i> Learn More
                    </a>
                </div>
            </div>

            <!-- Quick Overview Stats -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-plus-circle fa-2x text-primary"></i>
                            <h6 class="mt-1 mb-0">Add Test Results</h6>
                            <small class="text-muted">Record 10 trial readings</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-calculator fa-2x text-success"></i>
                            <h6 class="mt-1 mb-0">Auto Uncertainty</h6>
                            <small class="text-muted">Calculate u₁, u₂, u₃, U</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-check-circle fa-2x text-warning"></i>
                            <h6 class="mt-1 mb-0">Auto PASS/FAIL</h6>
                            <small class="text-muted">Based on tolerance</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                            <h6 class="mt-1 mb-0">Uncertainty Tracking</h6>
                            <small class="text-muted">Full audit trail</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links to Test Management Guides -->
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-list-ul me-1"></i> Quick Links:
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('user-guide.show', 'adding-test-results') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-flask me-1"></i> Adding Test Results
                        </a>
                        <a href="{{ route('user-guide.show', 'uncertainty-calculation') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-calculator me-1"></i> Uncertainty Calculation
                        </a>
                        <a href="{{ route('user-guide.show', 'tolerance-pass-fail') }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-balance-scale me-1"></i> Tolerance & PASS/FAIL
                        </a>
                        <a href="{{ route('user-guide.show', 'managing-test-results') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-list-ul me-1"></i> Managing Test Results
                        </a>
                        <a href="{{ route('user-guide.category', 'test-management') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-book-open me-1"></i> View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="row mt-4">
        @forelse($categories as $category)
            <div class="col-md-4 mb-4">
                <div class="card shadow-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-{{ $category->icon ?? 'book' }} fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('user-guide.category', $category->slug) }}"
                                       class="text-decoration-none text-dark">
                                        {{ $category->name }}
                                    </a>
                                </h5>
                                <small class="text-muted">{{ $category->guides->count() }} guides</small>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            @foreach($category->guides->take(5) as $guide)
                                <li class="py-1">
                                    <a href="{{ route('user-guide.show', $guide->slug) }}"
                                       class="text-decoration-none text-secondary hover-primary">
                                        <i class="fas fa-chevron-right text-primary me-2" style="font-size: 0.6rem;"></i>
                                        {{ $guide->title }}
                                    </a>
                                </li>
                            @endforeach
                            @if($category->guides->count() > 5)
                                <li class="py-1">
                                    <a href="{{ route('user-guide.category', $category->slug) }}"
                                       class="text-decoration-none text-primary small">
                                        + {{ $category->guides->count() - 5 }} more...
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No categories available yet.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Recent Guides -->
    @if(isset($recentGuides) && $recentGuides->isNotEmpty())
        <div class="card shadow mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-clock text-primary me-2"></i>Recently Updated Guides
                </h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($recentGuides as $guide)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('user-guide.show', $guide->slug) }}"
                               class="text-decoration-none text-dark">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                {{ $guide->title }}
                                <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('-', ' ', $guide->category)) }}</span>
                            </a>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                {{ $guide->updated_at->diffForHumans() }}
                            </small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="text-center text-muted small mt-4">
        <p class="mb-0">
            <i class="fas fa-microscope me-1"></i>
            KPLC Meter Central Laboratory | Version 1.0 | Last Updated: {{ now()->format('F d, Y') }}
        </p>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-primary:hover {
    color: #0d6efd !important;
}
.shadow-hover {
    transition: all 0.3s ease;
    cursor: default;
}
.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
.card {
    border-radius: 12px;
    overflow: hidden;
}
</style>
@endpush --}}

@extends('layouts.app')

@section('title', 'User Guide - KPLC Meter Lab')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="card shadow-lg" style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);">
        <div class="card-body text-white p-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">
                        <i class="fas fa-book-open me-3"></i>User Guide
                    </h1>
                    <p class="lead opacity-75">
                        Comprehensive guide for using the Meter Central Laboratory System
                    </p>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('user-guide.search') }}" method="GET" class="d-flex">
                        <input type="text" name="q" placeholder="Search the guide..."
                               class="form-control form-control-lg border-0"
                               style="border-radius: 50px 0 0 50px;">
                        <button type="submit" class="btn btn-light btn-lg"
                                style="border-radius: 0 50px 50px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Cards -->
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                    <h6>Getting Started</h6>
                    <small class="text-muted">Login, navigation, basics</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-flask fa-3x text-success mb-3"></i>
                    <h6>Test Management</h6>
                    <small class="text-muted">Add results, uncertainty</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-clipboard-check fa-3x text-warning mb-3"></i>
                    <h6>Quality Control</h6>
                    <small class="text-muted">Review, approve, reject</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-hover text-center h-100">
                <div class="card-body">
                    <i class="fas fa-certificate fa-3x text-danger mb-3"></i>
                    <h6>Certificates</h6>
                    <small class="text-muted">Generate, sign, send</small>
                </div>
            </div>
        </div>
    </div>

    <!-- QUALITY CONTROL SECTION -->
    <div class="card shadow mt-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>Quality Control Module
                </h4>
                <a href="{{ route('user-guide.category', 'quality-control') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-book-open me-1"></i> View Full Guide
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Quality Control:</strong> The Quality Controller role ensures all calibrations meet required standards before certificate issuance. Acts as the final checkpoint in the calibration workflow.
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('user-guide.category', 'quality-control') }}" class="btn btn-warning">
                        <i class="fas fa-arrow-right me-1"></i> Learn More
                    </a>
                </div>
            </div>

            <!-- Quality Control Stats -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                            <h6 class="mt-1 mb-0">Pending Checks</h6>
                            <small class="text-muted">Calibrations waiting review</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                            <h6 class="mt-1 mb-0">Quality Approval</h6>
                            <small class="text-muted">Pass quality checks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                            <h6 class="mt-1 mb-0">Quality Rejection</h6>
                            <small class="text-muted">Fail quality checks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-chart-bar fa-2x text-primary"></i>
                            <h6 class="mt-1 mb-0">Quality Reports</h6>
                            <small class="text-muted">Track quality trends</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quality Control Quick Links -->
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-list-ul me-1"></i> Quality Control Guides:
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('user-guide.show', 'quality-controller-dashboard') }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard Overview
                        </a>
                        <a href="{{ route('user-guide.show', 'quality-check-process') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-clipboard-check me-1"></i> Quality Check Process
                        </a>
                        <a href="{{ route('user-guide.show', 'quality-decision-guide') }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-gavel me-1"></i> Decision Guide
                        </a>
                        <a href="{{ route('user-guide.show', 'quality-standards') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-book me-1"></i> Quality Standards
                        </a>
                        <a href="{{ route('user-guide.category', 'quality-control') }}" class="btn btn-sm btn-dark">
                            <i class="fas fa-book-open me-1"></i> View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TEST MANAGEMENT SUMMARY CARD -->
    <div class="card shadow mt-4 border-success">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-flask me-2"></i>Test Management Module
                </h4>
                <a href="{{ route('user-guide.category', 'test-management') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-book-open me-1"></i> View Full Guide
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>New Feature:</strong> The Test Management module allows technicians to record test results with automated uncertainty calculations for calibration certificates.
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('user-guide.category', 'test-management') }}" class="btn btn-success">
                        <i class="fas fa-arrow-right me-1"></i> Learn More
                    </a>
                </div>
            </div>

            <!-- Quick Overview Stats -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-plus-circle fa-2x text-primary"></i>
                            <h6 class="mt-1 mb-0">Add Test Results</h6>
                            <small class="text-muted">Record 10 trial readings</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-calculator fa-2x text-success"></i>
                            <h6 class="mt-1 mb-0">Auto Uncertainty</h6>
                            <small class="text-muted">Calculate u₁, u₂, u₃, U</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-check-circle fa-2x text-warning"></i>
                            <h6 class="mt-1 mb-0">Auto PASS/FAIL</h6>
                            <small class="text-muted">Based on tolerance</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                            <h6 class="mt-1 mb-0">Uncertainty Tracking</h6>
                            <small class="text-muted">Full audit trail</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links to Test Management Guides -->
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-list-ul me-1"></i> Quick Links:
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('user-guide.show', 'adding-test-results') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-flask me-1"></i> Adding Test Results
                        </a>
                        <a href="{{ route('user-guide.show', 'uncertainty-calculation') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-calculator me-1"></i> Uncertainty Calculation
                        </a>
                        <a href="{{ route('user-guide.show', 'tolerance-pass-fail') }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-balance-scale me-1"></i> Tolerance & PASS/FAIL
                        </a>
                        <a href="{{ route('user-guide.show', 'managing-test-results') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-list-ul me-1"></i> Managing Test Results
                        </a>
                        <a href="{{ route('user-guide.category', 'test-management') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-book-open me-1"></i> View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="row mt-4">
        @forelse($categories as $category)
            <div class="col-md-4 mb-4">
                <div class="card shadow-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-{{ $category->icon ?? 'book' }} fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('user-guide.category', $category->slug) }}"
                                       class="text-decoration-none text-dark">
                                        {{ $category->name }}
                                    </a>
                                </h5>
                                <small class="text-muted">{{ $category->guides->count() }} guides</small>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            @foreach($category->guides->take(5) as $guide)
                                <li class="py-1">
                                    <a href="{{ route('user-guide.show', $guide->slug) }}"
                                       class="text-decoration-none text-secondary hover-primary">
                                        <i class="fas fa-chevron-right text-primary me-2" style="font-size: 0.6rem;"></i>
                                        {{ $guide->title }}
                                    </a>
                                </li>
                            @endforeach
                            @if($category->guides->count() > 5)
                                <li class="py-1">
                                    <a href="{{ route('user-guide.category', $category->slug) }}"
                                       class="text-decoration-none text-primary small">
                                        + {{ $category->guides->count() - 5 }} more...
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No categories available yet.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Recent Guides -->
    @if(isset($recentGuides) && $recentGuides->isNotEmpty())
        <div class="card shadow mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-clock text-primary me-2"></i>Recently Updated Guides
                </h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($recentGuides as $guide)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('user-guide.show', $guide->slug) }}"
                               class="text-decoration-none text-dark">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                {{ $guide->title }}
                                <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('-', ' ', $guide->category)) }}</span>
                            </a>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                {{ $guide->updated_at->diffForHumans() }}
                            </small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="text-center text-muted small mt-4">
        <p class="mb-0">
            <i class="fas fa-microscope me-1"></i>
            KPLC Meter Central Laboratory | Version 1.0 | Last Updated: {{ now()->format('F d, Y') }}
        </p>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-primary:hover {
    color: #0d6efd !important;
}
.shadow-hover {
    transition: all 0.3s ease;
    cursor: default;
}
.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
.card {
    border-radius: 12px;
    overflow: hidden;
}
</style>
@endpush
