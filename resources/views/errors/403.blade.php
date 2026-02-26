@extends('layouts.app')

@section('title', 'Access Forbidden - 403')

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-code">
                    <h1 class="display-1 fw-bold text-warning">403</h1>
                </div>
                <div class="error-content">
                    <h2 class="h3 mb-3">Access Forbidden</h2>
                    <p class="lead text-muted mb-4">
                        You don't have permission to access this resource.
                        @if(isset($exception) && $exception->getMessage())
                            {{ $exception->getMessage() }}
                        @endif
                    </p>

                    @auth
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle"></i>
                        You are logged in as <strong>{{ Auth::user()->name }}</strong> with
                        <strong>{{ Auth::user()->role->display_name }}</strong> role.
                    </div>
                    @else
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle"></i>
                        You may need to log in to access this resource.
                    </div>
                    @endauth

                    <div class="error-actions">
                        @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-house"></i> Go to Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </button>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </button>
                        @endauth
                    </div>

                    @auth
                    <hr class="my-4">
                    <div class="role-info">
                        <p class="text-muted mb-2">Available sections for your role:</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            @if(Auth::user()->role->name === 'sales_staff')
                            <a href="{{ route('sales.pos') }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-cart"></i> Point of Sale
                            </a>
                            <a href="{{ route('sales.history') }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-receipt"></i> Sales History
                            </a>
                            @elseif(in_array(Auth::user()->role->name, ['subadmin', 'admin']))
                            <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-box"></i> Products
                            </a>
                            <a href="{{ route('sales.pos') }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-cart"></i> Point of Sale
                            </a>
                            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-graph-up"></i> Reports
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
