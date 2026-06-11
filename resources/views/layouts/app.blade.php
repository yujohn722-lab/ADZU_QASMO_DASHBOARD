<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Energy Crisis Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --portal-blue: #073f8f;
            --portal-blue-dark: #052f69;
            --sidebar: #223438;
            --sidebar-dark: #18272b;
            --sidebar-link: #a9c6ce;
            --accent: #19bceb;
            --workspace: #e8edf3;
            --line: #d7dde4;
        }

        body {
            background: var(--workspace);
            color: #263238;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 230px;
            background: var(--sidebar);
            color: #fff;
            overflow-y: auto;
            z-index: 1030;
        }

        .sidebar-brand {
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #062a57;
            font-size: 21px;
            font-weight: 700;
        }

        .profile-block {
            padding: 14px 10px 12px;
            text-align: center;
            background: #223438;
        }

        .avatar-circle {
            width: 112px;
            height: 112px;
            margin: 0 auto 12px;
            border: 4px solid #dce7ec;
            border-radius: 50%;
            background: #f5f9fb;
            color: #073f8f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 700;
        }

        .menu-search {
            margin: 8px 10px 12px;
            position: relative;
        }

        .menu-search input {
            background: #314b52;
            border: 0;
            border-radius: 2px;
            color: #d7e8ed;
            height: 37px;
            font-size: 13px;
            padding-right: 34px;
        }

        .menu-search i {
            position: absolute;
            right: 11px;
            top: 10px;
            color: #a8c2c9;
        }

        .nav-section {
            padding: 10px 14px;
            background: var(--sidebar-dark);
            color: #7899a2;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 7px 14px;
            color: var(--sidebar-link);
            font-size: 13px;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: #2d464d;
            border-left-color: #ffc107;
        }

        .sidebar .nav-link i {
            color: #ffc107;
            width: 14px;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 230px;
            right: 0;
            height: 56px;
            background: var(--portal-blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            z-index: 1020;
        }

        .content-wrap {
            margin-left: 230px;
            padding: 86px 15px 28px;
        }

        .portal-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-top: 3px solid var(--accent);
            border-radius: 2px;
            box-shadow: none;
            margin-bottom: 19px;
        }

        .portal-panel-header {
            min-height: 43px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 12px;
            border-bottom: 1px solid var(--line);
            font-size: 18px;
            font-weight: 400;
            color: #263238;
        }

        .portal-panel-header .title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .portal-panel-body {
            padding: 13px;
        }

        .metric-card {
            background: #fff;
            border: 1px solid var(--line);
            border-top: 3px solid var(--accent);
            border-radius: 2px;
            padding: 12px;
            min-height: 94px;
        }

        .metric-card .label {
            color: #607d8b;
            font-size: 12px;
            text-transform: uppercase;
        }

        .metric-card .value {
            color: #17324d;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-size: 12px;
            text-transform: uppercase;
            color: #607d8b;
            background: #f5f8fa;
            white-space: nowrap;
        }

        .btn {
            border-radius: 2px;
        }

        .form-control,
        .form-select {
            border-radius: 2px;
            font-size: 14px;
        }

        .shortcut-list .list-group-item {
            border-radius: 2px;
            margin-bottom: 10px;
            color: #2089c3;
            font-weight: 600;
        }

        .chart-box {
            min-height: 280px;
        }

        @media (max-width: 991px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
            }

            .topbar {
                position: static;
                left: 0;
            }

            .content-wrap {
                margin-left: 0;
                padding-top: 18px;
            }
        }

        @media print {
            .sidebar,
            .topbar,
            .no-print,
            .btn,
            form {
                display: none !important;
            }

            .content-wrap {
                margin: 0;
                padding: 0;
            }

            body {
                background: #fff;
            }

            .portal-panel,
            .metric-card {
                break-inside: avoid;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">MyADZU</div>
        <div class="profile-block">
            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div class="fw-semibold small">{{ auth()->user()->name ?? 'User' }}</div>
            <div class="text-info small">{{ ucfirst(auth()->user()->role ?? 'respondent') }}</div>
        </div>
        <div class="menu-search">
            <input class="form-control" type="text" placeholder="Search menu...">
            <i class="bi bi-search"></i>
        </div>

        <div class="nav-section">Main Navigation</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a class="nav-link {{ request()->routeIs('fuel-prices.*') ? 'active' : '' }}" href="{{ route('fuel-prices.index') }}"><i class="bi bi-fuel-pump"></i> Weekly Fuel Prices</a>
            <a class="nav-link {{ request()->routeIs('electricity-consumptions.*') ? 'active' : '' }}" href="{{ route('electricity-consumptions.index') }}"><i class="bi bi-lightning-charge"></i> Electricity Consumption</a>
            <a class="nav-link {{ request()->routeIs('fuel-vehicle-uses.*') ? 'active' : '' }}" href="{{ route('fuel-vehicle-uses.index') }}"><i class="bi bi-truck"></i> Fuel and Vehicle Use</a>
            <a class="nav-link {{ request()->routeIs('solar-performances.*') ? 'active' : '' }}" href="{{ route('solar-performances.index') }}"><i class="bi bi-sun"></i> Solar Savings</a>
            <a class="nav-link {{ request()->routeIs('student-service-volumes.*') ? 'active' : '' }}" href="{{ route('student-service-volumes.index') }}"><i class="bi bi-people"></i> Student Service Volume</a>
            <a class="nav-link {{ request()->routeIs('estimated-savings.*') ? 'active' : '' }}" href="{{ route('estimated-savings.index') }}"><i class="bi bi-cash-coin"></i> Estimated Savings</a>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
        </nav>

        <div class="nav-section">Common Menu</div>
        <nav class="nav flex-column mb-4">
            <a class="nav-link {{ request()->routeIs('account.*') ? 'active' : '' }}" href="{{ route('account.edit') }}"><i class="bi bi-person-gear"></i> Account Settings</a>
            <form method="POST" action="{{ route('logout') }}" class="px-2 mt-2">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
        </nav>
    </aside>

    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-list fs-4"></i>
            <span class="fw-semibold">Energy Crisis Learning Continuity Dashboard</span>
        </div>
        <div class="d-flex align-items-center gap-4 small">
            <i class="bi bi-bell-fill"></i>
            <span><i class="bi bi-person-fill me-1"></i> Welcome, {{ strtoupper(auth()->user()->name ?? 'USER') }}!</span>
        </div>
    </header>

    <main class="content-wrap">
        @if (session('status'))
            <div class="alert alert-success no-print">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger no-print">
                <strong>Please review the form.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
