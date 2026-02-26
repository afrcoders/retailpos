@extends('layouts.app')

@section('title', 'Session Expired - 419')

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-code">
                    <h1 class="display-1 fw-bold text-info">419</h1>
                </div>
                <div class="error-content">
                    <h2 class="h3 mb-3">Session Expired</h2>
                    <p class="lead text-muted mb-4">
                        Your session has expired for security reasons.
                        Please refresh the page and try again.
                    </p>

                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle"></i>
                        This usually happens when you've been inactive for too long or when the page has been open for an extended period.
                    </div>

                    <div class="error-actions">
                        <button onclick="location.reload()" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Page
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-house"></i> Go to Dashboard
                        </a>
                    </div>

                    <hr class="my-4">
                    <div class="security-info">
                        <p class="text-muted mb-2">Why did this happen?</p>
                        <small class="text-muted">
                            Session tokens expire automatically to protect your account from unauthorized access.
                            Simply refresh the page to get a new session token.
                        </small>
                    </div>
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
