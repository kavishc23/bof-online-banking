@extends('layouts.app')

@php
    $pageTitle = $pageTitle ?? 'BoF Admin Module';
@endphp

@push('styles')
<style>
    .admin-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        position: relative;
        z-index: 1;
    }

    .admin-heading h2 {
        margin: 0 0 10px;
        color: var(--primary-mid);
        font-size: 2rem;
        line-height: 1.1;
        font-weight: 800;
    }

    .admin-heading p {
        margin: 0;
        color: var(--text-soft);
        font-size: 1rem;
        line-height: 1.6;
    }

    .admin-card {
        background: var(--card-bg);
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        padding: 22px;
        margin-bottom: 22px;
        backdrop-filter: blur(8px);
    }

    body.dark-mode .admin-card {
        background: rgba(17,24,39,0.92);
        border-color: rgba(51,65,85,0.95);
    }

    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
    }

    .admin-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        color: var(--text-main);
        line-height: 1.6;
    }

    .admin-detail-wide {
        grid-column: 1 / -1;
    }

    .admin-stat span {
        display: block;
        color: var(--text-soft);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .admin-stat strong {
        color: var(--primary-mid);
        font-size: 28px;
        font-weight: 900;
    }

    .admin-table-wrap {
        overflow-x: auto;
    }

    .admin-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(29, 78, 216, 0.18);
    }

    .admin-btn.secondary {
        background: rgba(37, 99, 235, 0.08);
        color: var(--primary-mid);
        box-shadow: none;
    }

    .admin-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .admin-field {
        display: grid;
        gap: 7px;
    }

    .admin-field label {
        color: var(--primary-mid);
        font-weight: 800;
        font-size: 13px;
    }

    .admin-field input,
    .admin-field select,
    .admin-field textarea {
        border: 1px solid var(--border-soft);
        border-radius: 12px;
        padding: 11px 12px;
        background: var(--bg-soft);
        color: var(--text-main);
    }

    body.dark-mode .admin-field input,
    body.dark-mode .admin-field select,
    body.dark-mode .admin-field textarea {
        background: rgba(15,23,42,0.85);
        border-color: rgba(51,65,85,0.95);
    }

    .admin-field.full {
        grid-column: 1 / -1;
    }

    .admin-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 1px solid var(--border-soft);
        padding: 16px 0;
    }

    .admin-toggle-row:last-child {
        border-bottom: none;
    }

    .admin-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .admin-badge-blue { background: #dbeafe; color: #1d4ed8; }
    .admin-badge-green { background: #dcfce7; color: #166534; }
    .admin-badge-yellow { background: #fef3c7; color: #92400e; }
    .admin-badge-red { background: #fee2e2; color: #991b1b; }
    .admin-badge-slate { background: #e2e8f0; color: #334155; }

    .admin-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .switch input {
        width: 20px;
        height: 20px;
    }

    @media (max-width: 900px) {
        .admin-heading {
            flex-direction: column;
        }

        .admin-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('topcard')
    <div class="admin-heading">
        <div>
            <h2>@yield('heading', 'Admin Module')</h2>
            <p>@yield('subheading', 'Manage Bank of Fiji administration tasks.')</p>
        </div>
        @yield('actions')
    </div>
@endsection

@section('content')
    @yield('admin-content')
@endsection
