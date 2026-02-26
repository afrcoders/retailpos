@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="fade-in">
    <h1 class="mb-4"><i class="bi bi-gear"></i> Settings</h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Account Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="{{ Auth::user()->email }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->role->display_name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->phone }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->created_at->format('M d, Y') }}" disabled>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">System Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Application</label>
                            <p>Dakoss Global POS v1.0</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Laravel Version</label>
                            <p>{{ app()::VERSION }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
