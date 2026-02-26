<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dakoss Global POS') | Dakoss Global Nigeria Limited</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/icons/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --dakoss-primary: #1a472a;
            --dakoss-secondary: #f39200;
            --dakoss-accent: #e74c3c;
            --light-bg: #f8f9fa;
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #333;
        }

        /* Navbar Branding */
        .navbar {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 0.8rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white !important;
        }

        .navbar-brand img {
            max-height: 40px;
            width: auto;
        }

        .navbar-brand span {
            background: linear-gradient(135deg, #fff 0%, var(--dakoss-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            margin: 0 5px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--dakoss-secondary) !important;
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background-color: var(--dakoss-secondary);
            color: white !important;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            min-height: 100vh;
            padding: 2rem 0;
            position: sticky;
            top: 0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin: 0.5rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-left: 4px solid transparent;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            color: var(--dakoss-secondary);
            background-color: rgba(255,255,255,0.1);
            border-left-color: var(--dakoss-secondary);
        }

        .sidebar-menu a.active {
            color: var(--dakoss-secondary);
            background-color: rgba(255,255,255,0.1);
            border-left-color: var(--dakoss-secondary);
        }

        .sidebar-menu i {
            font-size: 1.3rem;
            min-width: 20px;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1.2rem;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--dakoss-primary);
            border-color: var(--dakoss-primary);
            font-weight: 600;
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
        }

        .btn-primary:hover {
            background-color: #0f3a1f;
            border-color: #0f3a1f;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 71, 42, 0.3);
        }

        .btn-secondary {
            background-color: var(--dakoss-secondary);
            border-color: var(--dakoss-secondary);
            font-weight: 600;
            border-radius: 8px;
            color: white;
            padding: 0.6rem 1.2rem;
        }

        .btn-secondary:hover {
            background-color: #d67a00;
            border-color: #d67a00;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(243, 146, 0, 0.3);
        }

        .btn-outline-primary {
            color: var(--dakoss-primary);
            border-color: var(--dakoss-primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--dakoss-primary);
            border-color: var(--dakoss-primary);
            color: white;
        }

        /* Alerts */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 8px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: 8px;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
            border-radius: 8px;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            border-radius: 8px;
        }

        /* Dashboard Stats */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid var(--dakoss-primary);
        }

        .stat-card .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--dakoss-secondary);
        }

        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dakoss-primary);
            margin: 0.5rem 0;
        }

        .stat-card .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Tables */
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: var(--dakoss-primary);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }

        .table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-color: #e0e0e0;
        }

        .table tbody tr:hover {
            background-color: var(--light-bg);
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--dakoss-primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 71, 42, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--dakoss-primary);
            margin-bottom: 0.5rem;
        }

        /* Badge */
        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            color: rgba(255,255,255,0.9);
            padding: 2rem;
            text-align: center;
            margin-top: 3rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .footer a {
            color: var(--dakoss-secondary);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 1rem 0;
            }

            .main-content {
                padding: 1rem;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }
        }

        /* Loading Animation */
        .spinner-border {
            color: var(--dakoss-primary);
        }

        /* Modal */
        .modal-header {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            color: white;
            border: none;
        }

        .modal-title {
            font-weight: 700;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }
    </style>

    @yield('extra_css')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="Dakoss Global Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> Settings</a></li>
                                <li><a class="dropdown-item" href="{{ route('password.change') }}"><i class="bi bi-key"></i> Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            @auth
            <!-- Sidebar -->
            <nav class="col-lg-2 d-none d-lg-block sidebar">
                <ul class="sidebar-menu">
                    <li class="mb-3 px-3">
                        <small class="text-white-50 text-uppercase fw-bold">Menu</small>
                    </li>

                    @if(Auth::user()->role->name === 'sales_staff')
                        <li>
                            <a href="/dashboard" class="@if(request()->path() === 'dashboard') active @endif">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="/sales" class="@if(request()->path() === 'sales') active @endif">
                                <i class="bi bi-cash-register"></i> Point of Sale
                            </a>
                        </li>
                        <li>
                            <a href="/sales-history" class="@if(request()->path() === 'sales-history') active @endif">
                                <i class="bi bi-receipt"></i> Sales History
                            </a>
                        </li>
                    @elseif(Auth::user()->role->name === 'subadmin' || Auth::user()->role->name === 'admin')
                        <li>
                            <a href="/dashboard" class="@if(request()->path() === 'dashboard') active @endif">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="/items" class="@if(strpos(request()->path(), 'items') === 0) active @endif">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        <li>
                            <a href="/categories" class="@if(strpos(request()->path(), 'categories') === 0) active @endif">
                                <i class="bi bi-tag"></i> Categories
                            </a>
                        </li>
                        <li>
                            <a href="/suppliers" class="@if(strpos(request()->path(), 'suppliers') === 0) active @endif">
                                <i class="bi bi-truck"></i> Suppliers
                            </a>
                        </li>
                        <li>
                            <a href="/stock" class="@if(strpos(request()->path(), 'stock') === 0) active @endif">
                                <i class="bi bi-graph-up"></i> Stock Management
                            </a>
                        </li>
                        <li>
                            <a href="/stock-movements" class="@if(strpos(request()->path(), 'stock-movements') === 0) active @endif">
                                <i class="bi bi-arrow-left-right"></i> Stock Movements
                            </a>
                        </li>
                        <li>
                            <a href="/sales" class="@if(strpos(request()->path(), 'sales') === 0 && request()->path() !== 'sales-history') active @endif">
                                <i class="bi bi-cart"></i> Sales
                            </a>
                        </li>
                        <li>
                            <a href="/sales-history" class="@if(request()->path() === 'sales-history') active @endif">
                                <i class="bi bi-receipt"></i> Sales History
                            </a>
                        </li>
                        <li>
                            <a href="/reports" class="@if(strpos(request()->path(), 'reports') === 0) active @endif">
                                <i class="bi bi-graph-up-arrow"></i> Reports
                            </a>
                        </li>
                        @if(Auth::user()->role->name === 'admin')
                            <li class="mb-3 mt-4 px-3">
                                <small class="text-white-50 text-uppercase fw-bold">Administration</small>
                            </li>
                            <li>
                                <a href="/users" class="@if(strpos(request()->path(), 'users') === 0) active @endif">
                                    <i class="bi bi-people"></i> Users
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings.printer') }}" class="@if(strpos(request()->path(), 'settings/printer') !== false) active @endif">
                                    <i class="bi bi-printer"></i> Printer Settings
                                </a>
                            </li>
                            <li>
                                <a href="/settings" class="@if(strpos(request()->path(), 'settings') === 0 && strpos(request()->path(), 'settings/printer') === false) active @endif">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>
            </nav>
            @endauth

            <!-- Main Content -->
            <main class="col-lg-10 main-content @if(!Auth::check()) col-12 @endif">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-2">© {{ date('Y') }} Dakoss Global Nigeria Limited. All rights reserved.</p>
            <p class="mb-0"><small>Dakoss Global POS - Inventory & Point of Sale System | Built with <i class="bi bi-heart-fill"></i> in Nigeria</small></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @yield('extra_js')
</body>
</html>
