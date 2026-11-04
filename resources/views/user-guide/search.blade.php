@extends('layouts.app')

@section('title', 'Search Results - User Guide')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold">
                        <i class="fas fa-search text-primary me-2"></i>Search Results
                    </h1>
                    @if(isset($query) && $query)
                        <p class="text-muted mb-0">
                            Showing results for: <strong>"{{ $query }}"</strong>
                            <span class="ms-2">({{ $guides->total() }} results)</span>
                        </p>
                    @endif
                </div>
                <a href="{{ route('user-guide.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Guide
                </a>
            </div>
        </div>
    </div>

    <!-- Results -->
    @if(isset($guides) && $guides->isNotEmpty())
        <div class="mt-4">
            @foreach($guides as $guide)
                <div class="card shadow-hover mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                <i class="fas fa-{{ $guide->icon ?? 'file-alt' }} text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">
                                    <a href="{{ route('user-guide.show', $guide->slug) }}"
                                       class="text-decoration-none text-dark">
                                        {{ $guide->title }}
                                    </a>
                                    <span class="badge bg-secondary ms-2">{{ $guide->category }}</span>
                                </h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $guide->updated_at->format('F d, Y') }}
                                    <span class="mx-1">•</span>
                                    <i class="far fa-eye me-1"></i>{{ $guide->views ?? 0 }} views
                                </small>
                                <p class="card-text text-secondary mt-2">
                                    {{ Str::limit(strip_tags($guide->content), 200) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $guides->appends(['q' => $query ?? ''])->links() }}
            </div>
        </div>
    @else
        <div class="card shadow mt-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h3>No results found</h3>
                <p class="text-muted">Try different keywords or browse the categories</p>
                <a href="{{ route('user-guide.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i>Browse User Guide
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
