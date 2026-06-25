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

        .sidebar-toggle {
            border: 0;
            background: transparent;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
        }

        .sidebar-toggle:hover,
        .sidebar-toggle:focus {
            color: #ffc107;
        }

        .notification-button {
            border: 0;
            background: transparent;
            color: #fff;
            position: relative;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .notification-button:hover,
        .notification-button:focus {
            color: #ffc107;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 17px;
            height: 17px;
            border-radius: 999px;
            background: #dc3545;
            color: #fff;
            font-size: 10px;
            line-height: 17px;
            text-align: center;
            font-weight: 700;
        }

        .notification-menu {
            width: min(360px, 92vw);
            max-height: 430px;
            overflow-y: auto;
            border-radius: 2px;
        }

        .notification-item {
            white-space: normal;
            border-bottom: 1px solid var(--line);
        }

        .notification-item.unread {
            background: #eef7fb;
        }

        .content-wrap {
            margin-left: 230px;
            padding: 86px 15px 28px;
        }

        .sidebar,
        .topbar,
        .content-wrap {
            transition: margin-left .2s ease, left .2s ease, transform .2s ease;
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-230px);
        }

        body.sidebar-collapsed .topbar {
            left: 0;
        }

        body.sidebar-collapsed .content-wrap {
            margin-left: 0;
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

            body.sidebar-collapsed .sidebar {
                display: none;
                transform: none;
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
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">MyADZU</div>
        <div class="profile-block">
            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div class="fw-semibold small">{{ auth()->user()->name ?? 'User' }}</div>
            <div class="text-info small">{{ ucfirst(auth()->user()->role ?? 'respondent') }}</div>
        </div>

        <div class="nav-section">Main Navigation</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
        </nav>

        <div class="nav-section">Data Modules</div>
        <nav class="nav flex-column">
            @if (auth()->user()?->canAccessReportType('fuel-prices'))
                <a class="nav-link {{ request()->routeIs('fuel-prices.*') ? 'active' : '' }}" href="{{ route('fuel-prices.index') }}"><i class="bi bi-fuel-pump"></i> Weekly Fuel Prices</a>
            @endif
            @if (auth()->user()?->canAccessReportType('electricity-consumptions'))
                <a class="nav-link {{ request()->routeIs('electricity-consumptions.*') ? 'active' : '' }}" href="{{ route('electricity-consumptions.index') }}"><i class="bi bi-lightning-charge"></i> Electricity Consumption</a>
            @endif
            @if (auth()->user()?->canAccessReportType('fuel-vehicle-uses'))
                <a class="nav-link {{ request()->routeIs('fuel-vehicle-uses.*') ? 'active' : '' }}" href="{{ route('fuel-vehicle-uses.index') }}"><i class="bi bi-truck"></i> Fuel and Vehicle Use</a>
            @endif
            @if (auth()->user()?->canAccessReportType('solar-performances'))
                <a class="nav-link {{ request()->routeIs('solar-performances.*') ? 'active' : '' }}" href="{{ route('solar-performances.index') }}"><i class="bi bi-sun"></i> Solar Savings</a>
            @endif
            @if (auth()->user()?->canAccessReportType('student-service-volumes'))
                <a class="nav-link {{ request()->routeIs('student-service-volumes.*') ? 'active' : '' }}" href="{{ route('student-service-volumes.index') }}"><i class="bi bi-people"></i> Student Service Volume</a>
            @endif
            @if (auth()->user()?->canAccessReportType('estimated-savings'))
                <a class="nav-link {{ request()->routeIs('estimated-savings.*') ? 'active' : '' }}" href="{{ route('estimated-savings.index') }}"><i class="bi bi-cash-coin"></i> Estimated Savings</a>
            @endif
        </nav>

        <div class="nav-section">Common Menu</div>
        <nav class="nav flex-column mb-4">
            @if (auth()->user()?->isAdmin())
                <a class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}"><i class="bi bi-people-fill"></i> Account Management</a>
                <a class="nav-link {{ request()->routeIs('responder-approvals.*') ? 'active' : '' }}" href="{{ route('responder-approvals.index') }}"><i class="bi bi-person-check"></i> Responder Approvals</a>
            @endif
            <a class="nav-link {{ request()->routeIs('account.*') ? 'active' : '' }}" href="{{ route('account.edit') }}"><i class="bi bi-person-gear"></i> Account Settings</a>
            <form method="POST" action="{{ route('logout') }}" class="px-2 mt-2">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
        </nav>
    </aside>

    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" type="button" id="sidebarToggle" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="true" title="Toggle navigation">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-semibold">University Energy Monitoring Dashboard</span>
        </div>
        <div class="d-flex align-items-center gap-3 small">
            <div class="dropdown">
                <button class="notification-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="bi bi-bell-fill fs-5"></i>
                    @if (($navUnreadCount ?? 0) > 0)
                        <span class="notification-badge">{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <span class="fw-semibold">Notifications</span>
                        @if (($navUnreadCount ?? 0) > 0)
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button class="btn btn-sm btn-link p-0" type="submit">Mark all read</button>
                            </form>
                        @endif
                    </div>
                    @forelse (($navNotifications ?? collect()) as $notification)
                        <a class="dropdown-item notification-item py-2 {{ $notification->isUnread() ? 'unread' : '' }}" href="{{ route('notifications.open', $notification) }}">
                            <div>{{ $notification->message }}</div>
                            <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                        </a>
                    @empty
                        <div class="px-3 py-3 text-muted">No notifications.</div>
                    @endforelse
                </div>
            </div>
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
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        function setSidebarState(collapsed) {
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            sidebarToggle?.setAttribute('aria-expanded', String(! collapsed));
            localStorage.setItem('sidebarCollapsed', String(collapsed));
        }

        setSidebarState(sidebarCollapsed);
        sidebarToggle?.addEventListener('click', () => {
            setSidebarState(! document.body.classList.contains('sidebar-collapsed'));
        });
    </script>
    @stack('scripts')
</body>
</html>
