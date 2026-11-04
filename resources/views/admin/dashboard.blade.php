@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dashboard</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Users</span>
                                    <span class="info-box-number">{{ $stats['total_users'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-building"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Customers</span>
                                    <span class="info-box-number">{{ $stats['total_customers'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-microscope"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Calibrations</span>
                                    <span class="info-box-number">{{ $stats['total_calibrations'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-pdf"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Certificates</span>
                                    <span class="info-box-number">{{ $stats['total_certificates'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Welcome to KPLC Meter Laboratory System!</h5>
                            <p>You are logged in as <strong>{{ auth()->user()->name }}</strong> 
                               ({{ auth()->user()->getRoleDisplayAttribute() }})</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection