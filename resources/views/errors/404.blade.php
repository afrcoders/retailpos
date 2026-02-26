@extends('layouts.app')

@section('title', 'Page Not Found - 404')

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-code">
                    <h1 class="display-1 fw-bold text-primary">404</h1>
                </div>
                <div class="error-content">
                    <h2 class="h3 mb-3">Oops! Page Not Found</h2>
                    <p class="lead text-muted mb-4">
                        The page you're looking for doesn't exist or has been moved.
                        Don't worry, it happens to the best of us!
                    </p>
                    <div class="error-actions">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-house"></i> Go to Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </button>
                    </div>

                    @auth
                    <hr class="my-4">
                    <div class="quick-links">
                        <p class="text-muted mb-3">Or try these popular sections:</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ route('sales.pos') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-cart"></i> Point of Sale
                            </a>
                            <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box"></i> Products
                            </a>
                            <a href="{{ route('sales.history') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-receipt"></i> Sales History
                            </a>
                            @if(Auth::user()->role->name === 'admin')
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-people"></i> Users
                            </a>
                            @endif
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    padding: 2rem;
}

.error-code h1 {
    font-size: 8rem;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .error-code h1 {
        font-size: 6rem;
    }

    .error-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>
@endsection
