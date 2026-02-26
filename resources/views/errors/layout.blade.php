@extends('layouts.app')

@section('title', "Error @yield('code') - @yield('title')")

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-code">
                    <h1 class="display-1 fw-bold text-secondary">@yield('code')</h1>
                </div>
                <div class="error-content">
                    <h2 class="h3 mb-3">@yield('title')</h2>
                    <p class="lead text-muted mb-4">
                        @yield('message')
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
                    <div class="help-info">
                        <p class="text-muted mb-2">Need help?</p>
                        <small class="text-muted">
                            If this problem continues, please contact your system administrator.
                        </small>
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
