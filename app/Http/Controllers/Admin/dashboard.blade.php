@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Dashboard</h1>
            <p>Welcome, {{ Auth::user()->name }}!</p>
            <p>Role: {{ Auth::user()->role_display }}</p>

            @can('view_dashboard')
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Customers</h5>
                                <h2>{{ $stats['total_customers'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Calibrations</h5>
                                <h2>{{ $stats['total_calibrations'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h2>{{ $stats['pending_calibrations'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Certificates</h5>
                                <h2>{{ $stats['total_certificates'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
