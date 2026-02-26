@extends('layouts.app')

@section('title', 'Server Error - 500')

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-code">
                    <h1 class="display-1 fw-bold text-danger">500</h1>
                </div>
                <div class="error-content">
                    <h2 class="h3 mb-3">Internal Server Error</h2>
                    <p class="lead text-muted mb-4">
                        Something went wrong on our end. We're working to fix this issue.
                        Please try again in a few minutes.
                    </p>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle"></i>
                        If this problem persists, please contact your system administrator.
                    </div>
                    <div class="error-actions">
                        <button onclick="location.reload()" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-arrow-clockwise"></i> Try Again
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-house"></i> Go to Dashboard
                        </a>
                    </div>

                    @auth
                    <hr class="my-4">
                    <div class="contact-info">
                        <p class="text-muted mb-2">Need immediate help?</p>
                        <small class="text-muted">
                            Contact your system administrator or check the system logs for more details.
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
