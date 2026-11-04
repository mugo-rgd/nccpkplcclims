@extends('layouts.app')

@section('title', $category->name . ' - User Guide')

@section('content')
<div class="container-fluid">
    <!-- Category Header -->
    <div class="card shadow">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('user-guide.index') }}">User Guide</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                </ol>
            </nav>

            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-4 rounded-circle me-4">
                    <i class="fas fa-{{ $category->icon ?? 'folder' }} fa-3x text-primary"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold">{{ $category->name }}</h1>
                    <p class="text-muted mb-0">{{ $guides->total() }} guides available</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Guides List -->
    <div class="row mt-4">
        @forelse($guides as $guide)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                <i class="fas fa-{{ $guide->icon ?? 'file-alt' }} text-primary"></i>
                            </div>
                            <div>
                                <h6 class="card-title mb-1">
                                    <a href="{{ route('user-guide.show', $guide->slug) }}"
                                       class="text-decoration-none text-dark fw-bold">
                                        {{ $guide->title }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $guide->updated_at->format('M d, Y') }}
                                    <span class="mx-1">•</span>
                                    <i class="far fa-eye me-1"></i>{{ $guide->views ?? 0 }}
                                </small>
                                <p class="card-text small text-secondary mt-2">
                                    {{ Str::limit(strip_tags($guide->content), 120) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="{{ route('user-guide.show', $guide->slug) }}"
                           class="btn btn-sm btn-outline-primary">
                            Read More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No guides found in this category.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $guides->links() }}
    </div>
</div>
@endsection
