@extends('layouts.app')
@section('title', 'Daftar Buku')
@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

    :root {
        --ink:       #0f1117;
        --ink-2:     #3a3f51;
        --ink-3:     #7b8099;
        --surface:   #f6f7fb;
        --card:      #ffffff;
        --border:    #e8eaf2;
        --accent:    #2d5be3;
        --accent-2:  #1a3fa8;
        --accent-bg: #eef2fd;
        --danger:    #e03f3f;
        --warn:      #e07d1a;
        --success:   #1a9e5c;
        --radius:    14px;
        --shadow-sm: 0 1px 4px rgba(15,17,23,0.06), 0 4px 16px rgba(15,17,23,0.04);
        --shadow-md: 0 4px 12px rgba(15,17,23,0.08), 0 12px 32px rgba(15,17,23,0.06);
        --shadow-lg: 0 8px 24px rgba(15,17,23,0.12), 0 24px 64px rgba(15,17,23,0.08);
    }

    * { box-sizing: border-box; }

    body, .page-wrapper { font-family: 'DM Sans', sans-serif; }

    .page-wrapper {
        min-height: 100vh;
        background: var(--surface);
        padding: 2rem 1.5rem 4rem;
    }

    /* ── HEADER ─────────────────────────────────────────── */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .header-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 4px;
    }

    .header-title {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 700;
        color: var(--ink);
        line-height: 1.15;
        margin: 0 0 6px;
    }

    .header-sub {
        font-size: 14px;
        color: var(--ink-3);
        font-weight: 400;
    }

    .header-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

    /* ── STAT PILLS ──────────────────────────────────────── */
    .stats-row {
        display: flex;
        gap: 12px;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }

    .stat-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 50px;
        padding: 8px 18px 8px 10px;
        box-shadow: var(--shadow-sm);
    }

    .stat-pill-icon {
        width: 34px; height: 34px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
    }

    .stat-pill-body { line-height: 1.2; }
    .stat-pill-val  { font-size: 15px; font-weight: 600; color: var(--ink); }
    .stat-pill-lbl  { font-size: 11px; color: var(--ink-3); }

    /* ── TOOLBAR ─────────────────────────────────────────── */
    .toolbar {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .toolbar-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .toolbar-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--ink-3);
    }

    .view-toggle {
        display: flex;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 3px;
        gap: 2px;
    }

    .view-toggle button {
        border: none;
        background: transparent;
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        color: var(--ink-3);
        transition: all .18s;
        display: flex; align-items: center;
    }

    .view-toggle button.active {
        background: var(--card);
        color: var(--accent);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
        gap: 10px;
        align-items: end;
    }

    @media (max-width: 900px) {
        .filter-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 560px) {
        .filter-grid { grid-template-columns: 1fr; }
    }

    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--ink-3);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-size: 13px;
        font-family: 'DM Sans', sans-serif;
        color: var(--ink);
        background: var(--card);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(45,91,227,0.12);
    }

    .search-wrap { position: relative; }
    .search-wrap .search-icon {
        position: absolute; left: 11px; top: 50%;
        transform: translateY(-50%);
        color: var(--ink-3); pointer-events: none;
    }
    .search-wrap .form-control { padding-left: 36px; }

    .filter-actions { display: flex; gap: 8px; }

    /* ── BUTTONS ─────────────────────────────────────────── */
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 18px;
        border-radius: 9px;
        font-size: 13px; font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        border: none; cursor: pointer;
        text-decoration: none;
        transition: all .18s;
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--accent);
        color: #fff;
        box-shadow: 0 2px 8px rgba(45,91,227,0.3);
    }
    .btn-primary:hover { background: var(--accent-2); box-shadow: 0 4px 16px rgba(45,91,227,0.4); transform: translateY(-1px); }

    .btn-danger {
        background: var(--danger);
        color: #fff;
        box-shadow: 0 2px 8px rgba(224,63,63,0.25);
    }
    .btn-danger:hover { background: #c23333; transform: translateY(-1px); }

    .btn-ghost {
        background: var(--surface);
        color: var(--ink-2);
        border: 1.5px solid var(--border);
    }
    .btn-ghost:hover { background: var(--border); }

    .btn-scan {
        background: #fff;
        color: #1a3fa8;
        border: 1.5px solid #c5d0f5;
        box-shadow: 0 2px 8px rgba(45,91,227,0.10);
        position: relative;
        overflow: hidden;
    }
    .btn-scan::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(45,91,227,0.06) 50%, transparent 100%);
        transform: translateX(-100%);
        transition: transform 0.5s ease;
    }
    .btn-scan:hover::before { transform: translateX(100%); }
    .btn-scan:hover {
        border-color: var(--accent);
        color: var(--accent);
        box-shadow: 0 4px 14px rgba(45,91,227,0.18);
        transform: translateY(-1px);
    }

    .btn-icon {
        padding: 8px; border-radius: 8px;
        background: var(--surface); border: 1.5px solid var(--border);
        color: var(--ink-2); cursor: pointer; font-size: 14px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all .15s; text-decoration: none;
    }

    .btn-icon:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-bg); }
    .btn-icon.danger:hover { border-color: var(--danger); color: var(--danger); background: #fdf2f2; }

    /* ── MASS DELETE BAR ─────────────────────────────────── */
    .mass-bar {
        display: none;
        align-items: center;
        justify-content: space-between;
        background: #fff8e6;
        border: 1.5px solid #f0c84a;
        border-radius: var(--radius);
        padding: 12px 18px;
        margin-bottom: 1.25rem;
        gap: 12px;
        flex-wrap: wrap;
        animation: slideDown .2s ease;
    }

    .mass-bar.visible { display: flex; }

    @keyframes slideDown {
        from { opacity:0; transform: translateY(-8px); }
        to   { opacity:1; transform: translateY(0); }
    }

    /* ── GRID VIEW ───────────────────────────────────────── */
    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.25rem;
    }

    @media (max-width: 480px) {
        .books-grid { grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    }

    .book-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: box-shadow .22s, transform .22s;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .book-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .book-card-select {
        position: absolute; top: 10px; left: 10px; z-index: 3;
    }

    .book-card-select input[type=checkbox] {
        width: 16px; height: 16px;
        accent-color: var(--accent);
        cursor: pointer;
        border-radius: 4px;
    }

    /* Book cover */
    .book-cover {
        position: relative;
        aspect-ratio: 3/4;
        overflow: hidden;
        background: linear-gradient(135deg, #e8eaf2 0%, #d4d8ec 100%);
        flex-shrink: 0;
    }

    .book-cover img {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .35s ease;
    }

    .book-card:hover .book-cover img { transform: scale(1.05); }

    .book-cover-placeholder {
        width: 100%; height: 100%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #eef2fd 0%, #e0e7ff 100%);
    }

    .book-cover-placeholder .spine {
        width: 3px; height: 50px;
        background: linear-gradient(to bottom, var(--accent), var(--accent-2));
        border-radius: 2px;
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        opacity: .35;
    }

    .book-cover-placeholder svg {
        width: 36px; height: 36px;
        stroke: #a0abcc; fill: none;
    }

    .book-cover-placeholder span {
        font-size: 10px; color: #a0abcc;
        text-align: center; padding: 0 12px;
        line-height: 1.4;
        font-weight: 500;
    }

    /* Stock badge on cover */
    .stock-badge {
        position: absolute; bottom: 8px; right: 8px;
        padding: 3px 9px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .stock-badge.good  { background: rgba(26,158,92,.85);  color: #fff; }
    .stock-badge.low   { background: rgba(224,125,26,.85); color: #fff; }
    .stock-badge.empty { background: rgba(224,63,63,.85);  color: #fff; }

    /* Category chip on cover */
    .cat-chip-cover {
        position: absolute; top: 8px; right: 8px;
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(6px);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px; font-weight: 600;
        color: var(--accent);
        max-width: 80px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        border: 1px solid rgba(45,91,227,.15);
    }

    /* Card body */
    .book-card-body {
        padding: 12px 14px 10px;
        flex: 1;
        display: flex; flex-direction: column; gap: 4px;
    }

    .book-card-title {
        font-size: 13.5px; font-weight: 600; color: var(--ink);
        line-height: 1.35;
        overflow: hidden; display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        margin: 0;
    }

    .book-card-writer {
        font-size: 11.5px; color: var(--ink-3);
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
        font-weight: 400;
    }

    .book-card-barcode {
        font-size: 10.5px; color: #b0b5c8;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.04em;
    }

    /* Card footer actions */
    .book-card-footer {
        display: flex;
        gap: 6px;
        padding: 0 10px 10px;
        border-top: 1px solid var(--border);
        margin-top: 6px;
        padding-top: 8px;
    }

    .card-action {
        flex: 1;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 4px;
        padding: 6px 4px;
        border-radius: 7px;
        font-size: 11px; font-weight: 600;
        border: none; cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }

    .card-action.view  { background: var(--accent-bg); color: var(--accent); }
    .card-action.edit  { background: #fff8e6; color: #b86a00; }
    .card-action.del   { background: #fdf2f2; color: var(--danger); }

    .card-action:hover { filter: brightness(.92); transform: translateY(-1px); }

    /* ── TABLE VIEW ──────────────────────────────────────── */
    .table-wrap {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .data-table {
        width: 100%; border-collapse: collapse;
    }

    .data-table thead th {
        background: #f0f2fa;
        padding: 11px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--ink-3);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .data-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        font-size: 13.5px;
        color: var(--ink-2);
        vertical-align: middle;
    }

    .data-table tbody tr:last-child td { border-bottom: none; }

    .data-table tbody tr:hover td { background: #f9fafd; }

    .table-cover {
        width: 40px; height: 52px;
        border-radius: 5px; overflow: hidden;
        background: linear-gradient(135deg, #eef2fd, #e0e7ff);
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }

    .table-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .book-info-cell { display: flex; align-items: center; gap: 12px; }

    .book-info-text .title {
        font-size: 13.5px; font-weight: 600; color: var(--ink);
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
        max-width: 200px;
    }

    .book-info-text .writer {
        font-size: 12px; color: var(--ink-3); margin-top: 2px;
    }

    .badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11.5px; font-weight: 600;
    }

    .badge-blue   { background: var(--accent-bg); color: var(--accent); }
    .badge-green  { background: #e6f9f0; color: var(--success); }
    .badge-yellow { background: #fff5e6; color: var(--warn); }
    .badge-red    { background: #fdf2f2; color: var(--danger); }

    .mono { font-family: 'Courier New', monospace; font-size: 12px; color: #8090b0; }

    /* ── EMPTY STATE ─────────────────────────────────────── */
    .empty-state {
        padding: 5rem 2rem; text-align: center;
    }
    .empty-icon {
        width: 72px; height: 72px;
        background: var(--surface); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
        border: 1.5px solid var(--border);
    }

    /* ── FLASH ───────────────────────────────────────────── */
    .flash {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 18px; border-radius: var(--radius);
        font-size: 13.5px; font-weight: 500;
        margin-bottom: 1.25rem;
        animation: slideDown .25s ease;
    }
    .flash-success { background: #e6f9f0; border: 1.5px solid #a7f3d0; color: #166534; }
    .flash-error   { background: #fdf2f2; border: 1.5px solid #fecaca; color: #991b1b; }

    /* ── QUICK ACTIONS BAR ───────────────────────────────── */
    .quick-actions-bar {
        margin-bottom: 1.5rem;
    }

    .qa-label {
        display: flex; align-items: center; gap: 6px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: var(--ink-3);
        margin-bottom: 10px;
    }

    .qa-links {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    @media (max-width: 900px) { .qa-links { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .qa-links { grid-template-columns: 1fr 1fr; } }

    .qa-link {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px;
        border-radius: var(--radius);
        text-decoration: none;
        border: 1.5px solid var(--border);
        background: var(--card);
        box-shadow: var(--shadow-sm);
        transition: all .2s;
        position: relative;
        overflow: hidden;
    }

    .qa-link::after {
        content: '';
        position: absolute; inset: 0;
        opacity: 0;
        transition: opacity .2s;
    }

    .qa-link:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .qa-link:hover::after { opacity: 1; }

    .qa-link-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .qa-link-text {
        display: flex; flex-direction: column; gap: 2px;
        flex: 1; min-width: 0;
    }

    .qa-link-title {
        font-size: 13px; font-weight: 700; color: var(--ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .qa-link-sub {
        font-size: 11px; color: var(--ink-3);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .qa-arrow {
        color: var(--ink-3); flex-shrink: 0;
        transition: transform .2s;
    }

    .qa-link:hover .qa-arrow { transform: translateX(3px); color: inherit; }

    /* Scan */
    .qa-link--scan .qa-link-icon { background: #eef2fd; color: var(--accent); }
    .qa-link--scan:hover { border-color: var(--accent); }
    .qa-link--scan:hover .qa-link-title { color: var(--accent); }

    /* Add */
    .qa-link--add .qa-link-icon { background: #e6f9f0; color: var(--success); }
    .qa-link--add:hover { border-color: var(--success); }
    .qa-link--add:hover .qa-link-title { color: var(--success); }

    /* Transactions */
    .qa-link--trans .qa-link-icon { background: #fff5e6; color: var(--warn); }
    .qa-link--trans:hover { border-color: var(--warn); }
    .qa-link--trans:hover .qa-link-title { color: var(--warn); }

    /* Trash */
    .qa-link--trash .qa-link-icon { background: #fdf2f2; color: var(--danger); }
    .qa-link--trash:hover { border-color: var(--danger); }
    .qa-link--trash:hover .qa-link-title { color: var(--danger); }

    /* ── PAGINATION OVERRIDE ─────────────────────────────── */
    .pagination-wrap { margin-top: 1.5rem; }
</style>

<div class="page-wrapper">

    {{-- Flash --}}
    @if(session('success'))
    <div class="flash flash-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flash flash-error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <p class="header-eyebrow">Perpustakaan Digital</p>
            <h1 class="header-title">Koleksi Buku</h1>
            <p class="header-sub">Kelola seluruh koleksi buku perpustakaan Anda</p>
        </div>
        <div class="header-actions">
            @role('admin')
            <button id="massDeleteBtn" onclick="massDeleteSelected()"
                class="btn btn-danger" style="display:none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus (<span id="selectedCount">0</span>)
            </button>

            {{-- Quick Scan / Transaksi Baru --}}
            <a href="{{ route('transactions.create') }}" class="btn btn-scan">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Scan &amp; Pinjam
            </a>

            <a href="{{ route('books.create') }}" class="btn btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Buku
            </a>
            @endrole
        </div>
    </div>

    {{-- Stat Pills --}}
    <div class="stats-row">
        <div class="stat-pill">
            <div class="stat-pill-icon" style="background:#eef2fd;">
                <svg width="16" height="16" fill="none" stroke="#2d5be3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div class="stat-pill-body">
                <div class="stat-pill-val">{{ $book->total() }}</div>
                <div class="stat-pill-lbl">Total Buku</div>
            </div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-icon" style="background:#e6f9f0;">
                <svg width="16" height="16" fill="none" stroke="#1a9e5c" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-pill-body">
                <div class="stat-pill-val">{{ $book->where('stock', '>', 0)->count() }}</div>
                <div class="stat-pill-lbl">Tersedia</div>
            </div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-icon" style="background:#fdf2f2;">
                <svg width="16" height="16" fill="none" stroke="#e03f3f" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </div>
            <div class="stat-pill-body">
                <div class="stat-pill-val">{{ $book->where('stock', 0)->count() }}</div>
                <div class="stat-pill-lbl">Stok Habis</div>
            </div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-icon" style="background:#fff5e6;">
                <svg width="16" height="16" fill="none" stroke="#e07d1a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
            </div>
            <div class="stat-pill-body">
                <div class="stat-pill-val">{{ $book->currentPage() }} / {{ $book->lastPage() }}</div>
                <div class="stat-pill-lbl">Halaman</div>
            </div>
        </div>
    </div>

    {{-- Quick Actions (Admin only) --}}
    @role('admin')
    <div class="quick-actions-bar">
        <div class="qa-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Aksi Cepat
        </div>
        <div class="qa-links">
            <a href="{{ route('transactions.create') }}" class="qa-link qa-link--scan">
                <div class="qa-link-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div class="qa-link-text">
                    <span class="qa-link-title">Scan &amp; Pinjam</span>
                    <span class="qa-link-sub">Input barcode → buat transaksi</span>
                </div>
                <svg class="qa-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('books.create') }}" class="qa-link qa-link--add">
                <div class="qa-link-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div class="qa-link-text">
                    <span class="qa-link-title">Tambah Buku</span>
                    <span class="qa-link-sub">Input koleksi buku baru</span>
                </div>
                <svg class="qa-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('transactions.index') }}" class="qa-link qa-link--trans">
                <div class="qa-link-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div class="qa-link-text">
                    <span class="qa-link-title">Daftar Transaksi</span>
                    <span class="qa-link-sub">Lihat semua peminjaman</span>
                </div>
                <svg class="qa-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('books.trash') }}" class="qa-link qa-link--trash">
                <div class="qa-link-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="qa-link-text">
                    <span class="qa-link-title">Sampah Buku</span>
                    <span class="qa-link-sub">Pulihkan buku terhapus</span>
                </div>
                <svg class="qa-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
    @endrole

    {{-- Toolbar --}}
    <div class="toolbar">
        <div class="toolbar-top">
            <span class="toolbar-label">Filter & Pencarian</span>
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:12px; color:var(--ink-3);">Tampilan:</span>
                <div class="view-toggle">
                    <button id="viewGrid" class="active" onclick="setView('grid')" title="Grid">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button id="viewTable" onclick="setView('table')" title="Tabel">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <form method="GET">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Cari Buku</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" class="form-control" placeholder="Nama, penulis, barcode..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Stok Min</label>
                    <input type="number" name="stock_min" class="form-control" placeholder="0" value="{{ request('stock_min') }}">
                </div>
                <div class="form-group">
                    <label>Stok Max</label>
                    <input type="number" name="stock_max" class="form-control" placeholder="∞" value="{{ request('stock_max') }}">
                </div>
                <div class="form-group">
                    <label>Urutkan</label>
                    <select name="sort_by" class="form-control">
                        <option value="">Default</option>
                        <option value="name"  @selected(request('sort_by')==='name')>Nama</option>
                        <option value="stock" @selected(request('sort_by')==='stock')>Stok</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Arah</label>
                    <select name="sort_dir" class="form-control">
                        <option value="asc"  @selected(request('sort_dir')==='asc')>A → Z</option>
                        <option value="desc" @selected(request('sort_dir')==='desc')>Z → A</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions" style="margin-top:12px;">
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Terapkan
                </button>
                <a href="{{ route('books.index') }}" class="btn btn-ghost">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Mass delete bar --}}
    <div class="mass-bar" id="massBar">
        <div style="display:flex; align-items:center; gap:10px;">
            <svg width="18" height="18" fill="none" stroke="#b86a00" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span style="font-size:13.5px; font-weight:600; color:#7c4900;">
                <span id="massBarCount">0</span> buku dipilih
            </span>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="clearAllSelection()" class="btn btn-ghost" style="font-size:12px; padding:6px 14px;">Batal Pilih</button>
            <button onclick="massDeleteSelected()" class="btn btn-danger" style="font-size:12px; padding:6px 14px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Terpilih
            </button>
        </div>
    </div>

    {{-- ═══ GRID VIEW ═══ --}}
    <div id="viewGridContainer">
        @forelse ($book as $item)
            @if($loop->first)
            <div class="books-grid">
            @endif

            <div class="book-card">
                @role('admin')
                <div class="book-card-select">
                    <input type="checkbox" class="book-checkbox" value="{{ $item->id }}" onclick="updateSelectedCount()">
                </div>
                @endrole

                {{-- Cover --}}
                <div class="book-cover">
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" loading="lazy">
                    @else
                        <div class="book-cover-placeholder">
                            <div class="spine"></div>
                            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>{{ Str::limit($item->name, 30) }}</span>
                        </div>
                    @endif

                    {{-- Category chip --}}
                    @if($item->category)
                    <span class="cat-chip-cover">{{ $item->category->name }}</span>
                    @endif

                    {{-- Stock badge --}}
                    <span class="stock-badge {{ $item->stock > 5 ? 'good' : ($item->stock > 0 ? 'low' : 'empty') }}">
                        {{ $item->stock > 0 ? $item->stock . ' stok' : 'Habis' }}
                    </span>
                </div>

                {{-- Body --}}
                <div class="book-card-body">
                    <p class="book-card-title">{{ $item->name }}</p>
                    <p class="book-card-writer">{{ $item->writer ?? '—' }}</p>
                    <p class="book-card-barcode">{{ $item->barcode }}</p>
                </div>

                {{-- Footer actions --}}
                <div class="book-card-footer">
                    <a href="{{ route('books.show', $item->slug) }}" class="card-action view">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Detail
                    </a>
                    @role('admin')
                    <a href="{{ route('books.edit', $item->id) }}" class="card-action edit">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form action="{{ route('books.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus buku ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="card-action del" style="width:100%; border:none;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                    @endrole
                </div>
            </div>

            @if($loop->last)
            </div>
            @endif

        @empty
            <div class="empty-state" style="background:var(--card); border:1px solid var(--border); border-radius:var(--radius);">
                <div class="empty-icon">
                    <svg width="30" height="30" fill="none" stroke="#a0abcc" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <p style="font-size:16px; font-weight:600; color:var(--ink-2); margin:0 0 6px;">Tidak ada buku ditemukan</p>
                <p style="font-size:13px; color:var(--ink-3); margin:0;">Coba ubah filter atau tambah buku baru</p>
            </div>
        @endforelse
    </div>

    {{-- ═══ TABLE VIEW ═══ --}}
    <div id="viewTableContainer" style="display:none;">
        <div class="table-wrap">
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            @role('admin')
                            <th style="width:40px;">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" style="width:15px;height:15px;accent-color:var(--accent);cursor:pointer;">
                            </th>
                            @endrole
                            <th>No</th>
                            <th>Buku</th>
                            <th>Barcode</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($book as $item)
                        <tr>
                            @role('admin')
                            <td>
                                <input type="checkbox" class="book-checkbox" value="{{ $item->id }}" onclick="updateSelectedCount()" style="width:15px;height:15px;accent-color:var(--accent);cursor:pointer;">
                            </td>
                            @endrole
                            <td style="color:var(--ink-3); font-size:12px;">{{ $loop->iteration + $book->firstItem() - 1 }}</td>
                            <td>
                                <div class="book-info-cell">
                                    <div class="table-cover">
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" loading="lazy">
                                        @else
                                            <svg width="16" height="16" fill="none" stroke="#a0abcc" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        @endif
                                    </div>
                                    <div class="book-info-text">
                                        <div class="title">{{ $item->name }}</div>
                                        <div class="writer">{{ $item->writer ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="mono">{{ $item->barcode }}</span></td>
                            <td>
                                @if($item->category)
                                <span class="badge badge-blue">{{ $item->category->name }}</span>
                                @else
                                <span style="color:var(--ink-3); font-size:12px;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $item->stock > 5 ? 'badge-green' : ($item->stock > 0 ? 'badge-yellow' : 'badge-red') }}">
                                    {{ $item->stock }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <a href="{{ route('books.show', $item->slug) }}" class="btn-icon" title="Detail">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @role('admin')
                                    <a href="{{ route('books.edit', $item->id) }}" class="btn-icon" title="Edit">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('books.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus buku ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon danger" title="Hapus">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('admin') ? '7' : '6' }}" class="empty-state">
                                <div class="empty-icon" style="margin:0 auto 1rem;">
                                    <svg width="28" height="28" fill="none" stroke="#a0abcc" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p style="font-size:15px; font-weight:600; color:var(--ink-2); margin:0 0 4px;">Tidak ada buku</p>
                                <p style="font-size:13px; color:var(--ink-3); margin:0;">Coba ubah filter pencarian</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap">
        {{ $book->links() }}
    </div>

</div>

{{-- Mass Delete Form --}}
<form id="massDeleteForm" action="{{ route('books.mass-delete') }}" method="POST" style="display:none;">
    @csrf @method('DELETE')
    <input type="hidden" name="ids" id="selectedIds">
</form>

@push('scripts')
<script>
    /* ── VIEW TOGGLE ── */
    var currentView = localStorage.getItem('bookView') || 'grid';

    function setView(v) {
        currentView = v;
        localStorage.setItem('bookView', v);
        document.getElementById('viewGridContainer').style.display  = v === 'grid'  ? 'block' : 'none';
        document.getElementById('viewTableContainer').style.display = v === 'table' ? 'block' : 'none';
        document.getElementById('viewGrid').classList.toggle('active',  v === 'grid');
        document.getElementById('viewTable').classList.toggle('active', v === 'table');
    }

    // init
    setView(currentView);

    /* ── CHECKBOX ── */
    function toggleSelectAll() {
        var all = document.getElementById('selectAll');
        document.querySelectorAll('.book-checkbox').forEach(function(c){ c.checked = all ? all.checked : false; });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        var checked = document.querySelectorAll('.book-checkbox:checked');
        var n = checked.length;
        var massBtn = document.getElementById('massDeleteBtn');
        var massBar = document.getElementById('massBar');

        document.getElementById('selectedCount').textContent = n;
        document.querySelectorAll('#massBarCount').forEach(function(el){ el.textContent = n; });

        if (n > 0) {
            massBtn.style.display = 'inline-flex';
            massBar.classList.add('visible');
        } else {
            massBtn.style.display = 'none';
            massBar.classList.remove('visible');
        }

        var all = document.getElementById('selectAll');
        var total = document.querySelectorAll('.book-checkbox').length;
        if (all) all.checked = n === total && n > 0;
    }

    function clearAllSelection() {
        document.querySelectorAll('.book-checkbox').forEach(function(c){ c.checked = false; });
        updateSelectedCount();
    }

    function massDeleteSelected() {
        var ids = Array.from(document.querySelectorAll('.book-checkbox:checked')).map(function(c){ return c.value; });
        if (!ids.length) { alert('Pilih minimal 1 buku.'); return; }
        if (!confirm('Yakin ingin menghapus ' + ids.length + ' buku?')) return;
        document.getElementById('selectedIds').value = JSON.stringify(ids);
        document.getElementById('massDeleteForm').submit();
    }
</script>
@endpush

@endsection