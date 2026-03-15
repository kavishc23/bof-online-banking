<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'BoF Online Banking' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --sidebar-width: 285px;
            --sidebar-collapsed-width: 96px;

            --primary-dark: #0b2147;
            --primary-mid: #163d7a;
            --primary-light: #2563eb;
            --primary-soft: #dbeafe;

            --bg-main: #eef3f8;
            --bg-soft: #f8fbff;
            --card-bg: rgba(255,255,255,0.92);

            --text-main: #1f2937;
            --text-soft: #6b7280;
            --text-muted: #94a3b8;

            --border-soft: rgba(226, 232, 240, 0.95);

            --success-bg: #dcfce7;
            --success-text: #166534;

            --error-bg: #fee2e2;
            --error-text: #991b1b;

            --warn-bg: #fff7ed;
            --warn-text: #9a3412;

            --shadow-soft: 0 10px 24px rgba(15, 43, 91, 0.07);
            --shadow-card: 0 16px 36px rgba(15, 43, 91, 0.08);
            --shadow-sidebar: 6px 0 28px rgba(8, 25, 56, 0.14);

            --radius-xl: 24px;
            --radius-lg: 20px;
            --radius-md: 14px;
        }

        body.dark-mode {
            --primary-dark: #091a33;
            --primary-mid: #8fb8ff;
            --primary-light: #60a5fa;
            --primary-soft: #1e3a5f;

            --bg-main: #0b1220;
            --bg-soft: #111827;
            --card-bg: rgba(17,24,39,0.92);

            --text-main: #f3f4f6;
            --text-soft: #cbd5e1;
            --text-muted: #94a3b8;

            --border-soft: rgba(51, 65, 85, 0.95);

            --success-bg: rgba(22, 101, 52, 0.18);
            --success-text: #86efac;

            --error-bg: rgba(127, 29, 29, 0.22);
            --error-text: #fca5a5;

            --warn-bg: rgba(154, 52, 18, 0.2);
            --warn-text: #fdba74;

            --shadow-soft: 0 10px 24px rgba(0, 0, 0, 0.28);
            --shadow-card: 0 16px 36px rgba(0, 0, 0, 0.3);
            --shadow-sidebar: 6px 0 28px rgba(0, 0, 0, 0.32);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.06), transparent 28%),
                linear-gradient(180deg, #f7faff 0%, #eef3f8 100%);
            color: var(--text-main);
            transition: background 0.25s ease, color 0.25s ease;
        }

        body.dark-mode {
            background:
                radial-gradient(circle at top left, rgba(96, 165, 250, 0.08), transparent 28%),
                linear-gradient(180deg, #0b1220 0%, #111827 100%);
        }

        a {
            color: inherit;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #091b39 0%, #103263 48%, #184691 100%);
            color: white;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: width 0.25s ease, padding 0.25s ease;
            box-shadow: var(--shadow-sidebar);
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 50;
            overflow: hidden;
        }

        body.dark-mode .sidebar {
            background: linear-gradient(180deg, #050b16 0%, #0b1f3e 48%, #11305e 100%);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
            padding-left: 10px;
            padding-right: 10px;
        }

        .sidebar-top {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .brand-block {
            padding: 14px;
            border-radius: 20px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            min-height: 118px;
        }

        .brand-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .brand h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: white;
            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
            flex-shrink: 0;
        }

        .brand-name-text {
            white-space: nowrap;
        }

        .brand p {
            margin: 10px 0 0 46px;
            color: #dbeafe;
            font-size: 13px;
            line-height: 1.5;
            max-width: 175px;
        }

        .toggle-btn {
            border: none;
            background: rgba(255,255,255,0.12);
            color: white;
            border-radius: 12px;
            width: 42px;
            height: 42px;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.2s ease, transform 0.2s ease;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-btn:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-1px);
        }

        .sidebar-shortcuts {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .top-shortcut {
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(255,255,255,0.08);
            color: #e0ecff;
            font-size: 12px;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.06);
            transition: 0.2s ease;
        }

        .top-shortcut:hover {
            background: rgba(255,255,255,0.14);
        }

        .sidebar-section-title {
            margin: 6px 8px 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #bfdbfe;
            opacity: 0.9;
        }

        .menu {
            margin-top: 2px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
            padding: 13px 14px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            background: rgba(255,255,255,0.05);
            transition: 0.2s ease;
            min-height: 48px;
            border: 1px solid transparent;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.14);
            transform: translateX(2px);
            border-color: rgba(255,255,255,0.06);
        }

        .menu a.active {
            background: linear-gradient(90deg, rgba(255,255,255,0.22), rgba(255,255,255,0.12));
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
        }

        .menu-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            font-size: 17px;
            flex-shrink: 0;
        }

        .sidebar-bottom {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .sidebar-footer-note {
            color: #d6e5ff;
            font-size: 12px;
            line-height: 1.5;
            padding: 0 4px;
            opacity: 0.92;
        }

        .logout-form {
            margin: 0;
        }

        .logout-btn {
            width: 100%;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            padding: 13px 14px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 18px rgba(185, 28, 28, 0.22);
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(185, 28, 28, 0.28);
        }

        .main {
            flex: 1;
            padding: 24px 28px 32px;
            transition: padding 0.25s ease;
        }

        .page-shell {
            max-width: 1450px;
            margin: 0 auto;
        }

        .top-utility {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .top-utility-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .top-utility-left .eyebrow {
            font-size: 12px;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
        }

        .top-utility-left .date-line {
            font-size: 14px;
            color: var(--text-soft);
        }

        .top-utility-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .top-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 14px;
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(219, 234, 254, 0.95);
            box-shadow: 0 8px 20px rgba(15, 43, 91, 0.04);
            color: var(--text-soft);
            font-size: 13px;
            font-weight: 600;
        }

        .chip-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
        }

        .profile-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #ffffff, #f8fbff);
            border: 1px solid rgba(219, 234, 254, 0.95);
            box-shadow: 0 10px 20px rgba(15, 43, 91, 0.05);
        }

        body.dark-mode .top-chip,
        body.dark-mode .profile-pill {
            background: rgba(17,24,39,0.88);
            border-color: rgba(51,65,85,0.95);
        }

        .profile-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa, #1d4ed8);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
        }

        .profile-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .profile-meta strong {
            font-size: 13px;
            color: var(--text-main);
        }

        .profile-meta span {
            font-size: 12px;
            color: var(--text-soft);
        }

        .theme-toggle {
            border: none;
            background: linear-gradient(135deg, var(--primary-light), #1e40af);
            color: white;
            padding: 10px 14px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(29, 78, 216, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .theme-toggle:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(29, 78, 216, 0.24);
        }

        .top-card {
            background: linear-gradient(135deg, #ffffff, #f9fbff);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(219, 234, 254, 0.95);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        body.dark-mode .top-card,
        body.dark-mode .section,
        body.dark-mode table {
            background: rgba(17,24,39,0.92);
            border-color: rgba(51,65,85,0.95);
        }

        body.dark-mode th {
            background: #172338;
            color: #bfdbfe;
        }

        body.dark-mode tr:hover {
            background: rgba(30, 41, 59, 0.65);
        }

        .top-card::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.05);
            top: -90px;
            right: -60px;
            pointer-events: none;
        }

        .top-card h2 {
            margin: 0 0 10px;
            color: var(--primary-mid);
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.3px;
            position: relative;
            z-index: 1;
        }

        .top-card p {
            margin: 0;
            color: var(--text-soft);
            font-size: 15px;
            line-height: 1.65;
            max-width: 780px;
            position: relative;
            z-index: 1;
        }

        .success-box,
        .error-box,
        .validation-box {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid transparent;
        }

        .success-box {
            background: var(--success-bg);
            color: var(--success-text);
            border-color: #bbf7d0;
        }

        .error-box {
            background: var(--error-bg);
            color: var(--error-text);
            border-color: #fecaca;
        }

        .validation-box {
            background: var(--warn-bg);
            color: var(--warn-text);
            border-color: #fed7aa;
        }

        .section {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(229, 231, 235, 0.9);
            margin-bottom: 24px;
        }

        .section h3 {
            margin-top: 0;
            margin-bottom: 18px;
            color: var(--primary-mid);
            font-size: 24px;
            font-weight: 800;
        }

        .quick-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 18px;
            position: relative;
            z-index: 1;
        }

        .action-btn {
            text-decoration: none;
            background: linear-gradient(135deg, var(--primary-light), #1e40af);
            color: white;
            padding: 11px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 8px 18px rgba(29, 78, 216, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(29, 78, 216, 0.24);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 14px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #eef5ff;
            color: var(--primary-mid);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        tr:hover {
            background: #f9fbff;
        }

        .badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-completed {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            color: var(--text-soft);
            font-size: 15px;
        }

        .section-note {
            color: var(--text-soft);
            font-size: 13px;
            margin-top: -8px;
            margin-bottom: 18px;
        }

        .sidebar.collapsed .brand p,
        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .logout-text,
        .sidebar.collapsed .sidebar-footer-note,
        .sidebar.collapsed .sidebar-section-title,
        .sidebar.collapsed .top-shortcut,
        .sidebar.collapsed .brand-name-text {
            display: none;
        }

        .sidebar.collapsed .brand-block {
            padding: 12px 8px 60px;
        }

        .sidebar.collapsed .brand-row {
            justify-content: center;
            align-items: center;
        }

        .sidebar.collapsed .brand {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .sidebar.collapsed .brand h1 {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-shortcuts {
            display: none;
        }

        .sidebar.collapsed .menu a {
            justify-content: center;
            padding: 13px 10px;
        }

        .sidebar.collapsed .toggle-btn {
            position: absolute;
            top: 58px;
            left: 50%;
            transform: translateX(-50%);
            width: 36px;
            height: 36px;
            font-size: 16px;
            border-radius: 10px;
            display: inline-flex;
        }

        .sidebar.collapsed .toggle-btn:hover {
            transform: translateX(-50%) translateY(-1px);
        }

        .sidebar.collapsed .logout-btn {
            padding-left: 10px;
            padding-right: 10px;
        }

        @media (max-width: 960px) {
            .layout {
                flex-direction: column;
            }

            .sidebar,
            .sidebar.collapsed {
                width: 100%;
                height: auto;
                position: relative;
                padding: 18px 14px;
            }

            .sidebar.collapsed .brand p,
            .sidebar.collapsed .menu-text,
            .sidebar.collapsed .logout-text,
            .sidebar.collapsed .sidebar-footer-note,
            .sidebar.collapsed .sidebar-section-title,
            .sidebar.collapsed .top-shortcut,
            .sidebar.collapsed .brand-name-text {
                display: inline;
            }

            .sidebar.collapsed .brand-block {
                padding: 14px;
            }

            .sidebar.collapsed .sidebar-shortcuts {
                display: flex;
            }

            .sidebar.collapsed .toggle-btn {
                position: static;
                transform: none;
                width: 42px;
                height: 42px;
            }

            .sidebar.collapsed .toggle-btn:hover {
                transform: translateY(-1px);
            }

            .sidebar.collapsed .menu a {
                justify-content: flex-start;
                padding: 13px 14px;
            }

            .main {
                padding: 20px;
            }

            .top-utility {
                flex-direction: column;
                align-items: flex-start;
            }

            .top-utility-right {
                width: 100%;
                justify-content: space-between;
            }

            .brand p {
                max-width: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <div class="brand-block">
                <div class="brand-row">
                    <div class="brand">
                        <h1>
                            <span class="brand-mark">BoF</span>
                            <span class="brand-name-text">BoF</span>
                        </h1>
                        <p>Online Banking Portal</p>
                    </div>
                    <button class="toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar">☰</button>
                </div>

                <div class="sidebar-shortcuts">
                    <a href="{{ route('transfer') }}" class="top-shortcut">Transfer</a>
                    <a href="{{ route('bill-payment') }}" class="top-shortcut">Pay Bill</a>
                </div>
            </div>

            <div class="sidebar-section-title">Main Menu</div>

            <nav class="menu">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="menu-icon">🏠</span>
                    <span class="menu-text">Dashboard</span>
                </a>

                <a href="{{ route('dashboard') }}#profile">
                    <span class="menu-icon">👤</span>
                    <span class="menu-text">Customer Profile</span>
                </a>

                <a href="{{ route('dashboard') }}#accounts">
                    <span class="menu-icon">💳</span>
                    <span class="menu-text">Accounts</span>
                </a>

                <a href="{{ route('transactions') }}" class="{{ request()->routeIs('transactions') ? 'active' : '' }}">
                    <span class="menu-icon">📄</span>
                    <span class="menu-text">Transactions</span>
                </a>

                <a href="{{ route('transfer') }}" class="{{ request()->routeIs('transfer') ? 'active' : '' }}">
                    <span class="menu-icon">🔁</span>
                    <span class="menu-text">Transfer Money</span>
                </a>

                <a href="{{ route('bill-payment') }}" class="{{ request()->routeIs('bill-payment') ? 'active' : '' }}">
                    <span class="menu-icon">💡</span>
                    <span class="menu-text">Bill Payment</span>
                </a>

                <a href="{{ route('beneficiaries') }}" class="{{ request()->routeIs('beneficiaries') ? 'active' : '' }}">
                    <span class="menu-icon">👥</span>
                    <span class="menu-text">Beneficiaries</span>
                </a>

                <a href="{{ route('scheduled-payments') }}" class="{{ request()->routeIs('scheduled-payments') ? 'active' : '' }}">
                    <span class="menu-icon">🗓️</span>
                    <span class="menu-text">Scheduled Payments</span>
                </a>
            </nav>
        </div>

        <div class="sidebar-bottom">
            <div class="sidebar-footer-note">
                Secure demo banking workspace for customer account access, payments, transfers, beneficiaries, scheduled payments, and transaction tracking.
            </div>

            <form class="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-btn" type="submit">
                    <span>⎋</span>
                    <span class="logout-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="page-shell">
            <div class="top-utility">
                <div class="top-utility-left">
                    <div class="eyebrow">Digital Banking Workspace</div>
                    <div class="date-line">
                        {{ now()->format('l, d M Y') }}
                    </div>
                </div>

                <div class="top-utility-right">
                    <button class="theme-toggle" id="themeToggle" type="button">🌙 Dark Mode</button>

                    <div class="top-chip">
                        <span class="chip-dot"></span>
                        <span>System Online</span>
                    </div>

                    <div class="profile-pill">
                        <div class="profile-avatar">
                            {{ strtoupper(substr(session('user.username', session('customer.firstName', 'U')), 0, 1)) }}
                        </div>
                        <div class="profile-meta">
                            <strong>{{ session('customer.firstName', session('user.username', 'User')) }}</strong>
                            <span>Logged in customer</span>
                        </div>
                    </div>
                </div>
            </div>

            @hasSection('topcard')
                <div class="top-card">
                    @yield('topcard')
                </div>
            @endif

            @if(session('success'))
                <div class="success-box">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-box">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="validation-box">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const themeToggle = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('bof-theme');
    const savedSidebarState = localStorage.getItem('bof-sidebar-collapsed');

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeToggle) {
            themeToggle.textContent = '☀️ Light Mode';
        }
    }

    if (sidebar && savedSidebarState === 'true' && window.innerWidth > 960) {
        sidebar.classList.add('collapsed');
    }

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            if (window.innerWidth > 960) {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem(
                    'bof-sidebar-collapsed',
                    sidebar.classList.contains('collapsed') ? 'true' : 'false'
                );
            }
        });
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');

            localStorage.setItem('bof-theme', isDark ? 'dark' : 'light');
            themeToggle.textContent = isDark ? '☀️ Light Mode' : '🌙 Dark Mode';
        });
    }
</script>

@stack('scripts')
</body>
</html>