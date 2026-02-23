@extends('layouts.app')
@section('title', 'Tambah Buku')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

    :root {
        --ink:        #111827;
        --ink-2:      #374151;
        --ink-3:      #9ca3af;
        --surface:    #f3f4f8;
        --card:       #ffffff;
        --border:     #e5e7eb;
        --border-2:   #d1d5db;
        --accent:     #2563eb;
        --accent-lt:  #eff6ff;
        --accent-dk:  #1d4ed8;
        --success:    #059669;
        --success-lt: #ecfdf5;
        --warn:       #d97706;
        --warn-lt:    #fffbeb;
        --danger:     #dc2626;
        --danger-lt:  #fef2f2;
        --radius:     12px;
        --radius-sm:  8px;
        --shadow:     0 1px 3px rgba(0,0,0,0.07), 0 8px 24px rgba(0,0,0,0.05);
        --shadow-lg:  0 4px 12px rgba(0,0,0,0.1), 0 20px 48px rgba(0,0,0,0.08);
    }

    * { box-sizing: border-box; }

    .page-wrap {
        min-height: 100vh;
        background: var(--surface);
        padding: 2rem 1rem 5rem;
        font-family: 'Sora', sans-serif;
    }

    .page-inner { max-width: 900px; margin: 0 auto; }

    /* ── HEADER ── */
    .page-hd {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
    }

    .page-hd-left { display: flex; align-items: center; gap: 14px; }

    .page-hd-icon {
        width: 52px; height: 52px; border-radius: 14px;
        background: var(--accent); color: #fff;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 12px rgba(37,99,235,.35);
    }

    .page-hd h1 {
        font-size: 1.5rem; font-weight: 700;
        color: var(--ink); margin: 0 0 2px;
        line-height: 1.2;
    }

    .page-hd p { font-size: 13px; color: var(--ink-3); margin: 0; }

    .back-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: var(--radius-sm);
        background: var(--card); border: 1.5px solid var(--border-2);
        font-size: 13px; font-weight: 600; color: var(--ink-2);
        text-decoration: none; transition: all .15s;
    }
    .back-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-lt); }

    /* ── GRID LAYOUT ── */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }

    @media (max-width: 720px) {
        .form-grid { grid-template-columns: 1fr; }
    }

    /* ── CARD ── */
    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .card-hd {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        background: #fafbfc;
        display: flex; align-items: center; gap: 8px;
    }

    .card-hd-icon {
        width: 28px; height: 28px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
    }

    .card-hd h3 {
        font-size: 13px; font-weight: 700; color: var(--ink);
        margin: 0; text-transform: uppercase; letter-spacing: .05em;
    }

    .card-body { padding: 20px; }

    /* ── FORM ELEMENTS ── */
    .field { margin-bottom: 18px; }
    .field:last-child { margin-bottom: 0; }

    .field label {
        display: block;
        font-size: 12px; font-weight: 700; color: var(--ink-2);
        text-transform: uppercase; letter-spacing: .05em;
        margin-bottom: 6px;
    }

    .field label .req { color: var(--danger); margin-left: 2px; }

    .field-hint {
        font-size: 11px; color: var(--ink-3); margin-top: 4px; font-weight: 400;
        text-transform: none; letter-spacing: 0;
    }

    .input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--border-2);
        border-radius: var(--radius-sm);
        font-size: 14px; font-family: 'Sora', sans-serif;
        color: var(--ink); background: #fff;
        outline: none; transition: border-color .15s, box-shadow .15s;
    }

    .input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }

    .input.is-error { border-color: var(--danger); }
    .input.is-success { border-color: var(--success); }

    .input-mono { font-family: 'JetBrains Mono', monospace; font-size: 13px; }

    textarea.input { resize: vertical; min-height: 90px; }

    .err-msg { font-size: 12px; color: var(--danger); margin-top: 4px; }

    /* ── BARCODE FIELD ── */
    .barcode-outer {
        position: relative;
    }

    .barcode-scanner-indicator {
        position: absolute;
        right: 12px; top: 50%; transform: translateY(-50%);
        display: flex; align-items: center; gap: 6px;
        font-size: 11px; font-weight: 600; color: var(--ink-3);
        pointer-events: none;
        transition: opacity .2s;
    }

    .scanner-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #d1d5db;
        transition: background .2s;
    }

    /* Saat field focused — indikator jadi hijau (scanner ready) */
    #barcodeInput:focus ~ .barcode-scanner-indicator .scanner-dot {
        background: var(--success);
        animation: dot-pulse 1.4s ease-in-out infinite;
    }

    #barcodeInput:focus ~ .barcode-scanner-indicator { color: var(--success); }

    @keyframes dot-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(5,150,105,0); }
        50%       { box-shadow: 0 0 0 4px rgba(5,150,105,.25); }
    }

    /* Input styling for barcode */
    #barcodeInput { padding-right: 110px; letter-spacing: .04em; }

    /* Flash success animation on scan */
    @keyframes flash-success {
        0%   { border-color: var(--success); box-shadow: 0 0 0 4px rgba(5,150,105,.25); }
        100% { border-color: var(--border-2); box-shadow: none; }
    }

    .barcode-flash { animation: flash-success 1.2s ease forwards; }

    /* ── BARCODE STATUS ── */
    .barcode-status {
        display: none;
        margin-top: 8px;
        padding: 10px 14px;
        border-radius: var(--radius-sm);
        font-size: 13px; font-weight: 500;
        align-items: center; gap: 8px;
    }

    .barcode-status.checking { display: flex; background: #f0f4ff; color: var(--accent); border: 1px solid #c7d7fd; }
    .barcode-status.available { display: flex; background: var(--success-lt); color: var(--success); border: 1px solid #a7f3d0; }
    .barcode-status.duplicate { display: flex; background: var(--warn-lt); color: var(--warn); border: 1px solid #fde68a; }

    /* ── DUPLICATE BOOK CARD ── */
    .dup-card {
        display: none;
        margin-top: 10px;
        border: 2px solid #fde68a;
        border-radius: var(--radius-sm);
        overflow: hidden;
        background: var(--warn-lt);
    }

    .dup-card-head {
        background: #fef3c7;
        padding: 8px 14px;
        font-size: 11px; font-weight: 700;
        color: #92400e; letter-spacing: .05em; text-transform: uppercase;
        display: flex; align-items: center; gap: 6px;
    }

    .dup-card-body {
        padding: 12px 14px;
        display: flex; align-items: center; gap: 12px;
    }

    .dup-cover {
        width: 44px; height: 58px; border-radius: 5px; overflow: hidden;
        background: #fde68a; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }

    .dup-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .dup-info { flex: 1; min-width: 0; }
    .dup-name { font-size: 13.5px; font-weight: 700; color: #78350f; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .dup-writer { font-size: 12px; color: #92400e; margin-top: 1px; }
    .dup-stock { font-size: 12px; color: #b45309; margin-top: 4px; }

    .dup-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }

    .dup-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 14px; border-radius: 7px;
        font-size: 12px; font-weight: 700; font-family: 'Sora', sans-serif;
        border: none; cursor: pointer; text-decoration: none;
        transition: all .15s;
    }

    .dup-btn-add { background: var(--warn); color: #fff; }
    .dup-btn-add:hover { background: #b45309; }

    .dup-btn-view { background: #fef3c7; color: #92400e; border: 1.5px solid #fde68a; }
    .dup-btn-view:hover { background: #fde68a; }

    /* ── COVER UPLOAD ── */
    .cover-upload {
        border: 2px dashed var(--border-2);
        border-radius: var(--radius-sm);
        text-align: center;
        cursor: pointer;
        transition: all .18s;
        overflow: hidden;
        position: relative;
        background: #fafbfc;
        aspect-ratio: 3/4;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
    }

    .cover-upload:hover { border-color: var(--accent); background: var(--accent-lt); }

    .cover-upload input[type=file] { display: none; }

    .cover-upload img {
        position: absolute; inset: 0;
        width: 100%; height: 100%; object-fit: cover;
        border-radius: var(--radius-sm);
    }

    .cover-upload-placeholder {
        display: flex; flex-direction: column; align-items: center; gap: 8px;
        pointer-events: none;
    }

    .cover-upload-placeholder svg { width: 36px; height: 36px; stroke: var(--ink-3); fill: none; }
    .cover-upload-placeholder span { font-size: 12px; font-weight: 600; color: var(--ink-3); }
    .cover-upload-placeholder small { font-size: 11px; color: #d1d5db; }

    .cover-change-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.5);
        display: none; align-items: center; justify-content: center;
        border-radius: var(--radius-sm);
    }

    .cover-upload:hover .cover-change-overlay { display: flex; }

    .cover-change-overlay span {
        font-size: 12px; font-weight: 700; color: #fff;
    }

    /* ── STOCK QUICK-SET ── */
    .stock-btns {
        display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;
    }

    .stock-preset {
        padding: 4px 12px;
        border: 1.5px solid var(--border-2);
        border-radius: 20px;
        font-size: 11px; font-weight: 700; font-family: 'Sora', sans-serif;
        color: var(--ink-2); background: #fff;
        cursor: pointer; transition: all .12s;
    }

    .stock-preset:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-lt); }

    /* ── SUBMIT ── */
    .submit-row {
        display: flex; gap: 10px; justify-content: flex-end;
        margin-top: 1.25rem; flex-wrap: wrap;
    }

    .btn-submit {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 28px;
        border-radius: var(--radius-sm);
        background: var(--accent);
        color: #fff; border: none; cursor: pointer;
        font-size: 14px; font-weight: 700; font-family: 'Sora', sans-serif;
        box-shadow: 0 4px 12px rgba(37,99,235,.35);
        transition: all .18s;
    }

    .btn-submit:hover { background: var(--accent-dk); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.4); }

    .btn-cancel {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px;
        border-radius: var(--radius-sm);
        background: #fff; color: var(--ink-2);
        border: 1.5px solid var(--border-2);
        font-size: 14px; font-weight: 600; font-family: 'Sora', sans-serif;
        text-decoration: none; cursor: pointer;
        transition: all .15s;
    }

    .btn-cancel:hover { border-color: var(--ink-2); background: var(--surface); }

    /* ── QUICK ADD STOCK MODAL ── */
    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,.5); backdrop-filter: blur(4px);
        align-items: center; justify-content: center; padding: 1rem;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
        background: var(--card); border-radius: 16px;
        box-shadow: var(--shadow-lg); width: 100%; max-width: 420px;
        overflow: hidden; animation: modal-in .22s cubic-bezier(.34,1.56,.64,1);
    }

    @keyframes modal-in {
        from { transform: scale(.9); opacity: 0; }
        to   { transform: scale(1);  opacity: 1; }
    }

    .modal-hd {
        padding: 20px 24px 16px;
        border-bottom: 1px solid var(--border);
        background: var(--warn-lt);
    }

    .modal-hd h3 { font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px; }
    .modal-hd p  { font-size: 13px; color: var(--ink-3); margin: 0; }

    .modal-body { padding: 20px 24px; }

    .modal-book-preview {
        display: flex; align-items: center; gap: 12px;
        padding: 12px; background: var(--surface); border-radius: var(--radius-sm);
        margin-bottom: 16px;
    }

    .modal-cover {
        width: 40px; height: 52px; border-radius: 5px; overflow: hidden;
        background: var(--border); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-cover img { width: 100%; height: 100%; object-fit: cover; }

    .modal-book-name { font-size: 13.5px; font-weight: 700; color: var(--ink); }
    .modal-book-stock { font-size: 12px; color: var(--ink-3); margin-top: 2px; }

    .modal-stock-input {
        display: flex; align-items: center; gap: 10px;
    }

    .modal-stock-input input {
        flex: 1; padding: 10px 14px;
        border: 1.5px solid var(--border-2); border-radius: var(--radius-sm);
        font-size: 18px; font-weight: 700; font-family: 'Sora', sans-serif;
        color: var(--ink); text-align: center; outline: none;
    }

    .modal-stock-input input:focus { border-color: var(--warn); box-shadow: 0 0 0 3px rgba(217,119,6,.15); }

    .modal-stock-btn {
        width: 40px; height: 40px; border-radius: 8px;
        border: 1.5px solid var(--border-2); background: #fff;
        font-size: 20px; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: var(--ink-2); transition: all .12s;
    }

    .modal-stock-btn:hover { border-color: var(--accent); color: var(--accent); }

    .modal-ft {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex; gap: 10px; justify-content: flex-end;
    }

    .modal-save {
        padding: 10px 22px; border-radius: var(--radius-sm);
        background: var(--warn); color: #fff;
        border: none; cursor: pointer;
        font-size: 13px; font-weight: 700; font-family: 'Sora', sans-serif;
        transition: all .15s;
    }
    .modal-save:hover { background: #b45309; }

    .modal-cancel {
        padding: 10px 16px; border-radius: var(--radius-sm);
        background: #fff; color: var(--ink-2);
        border: 1.5px solid var(--border-2); cursor: pointer;
        font-size: 13px; font-weight: 600; font-family: 'Sora', sans-serif;
        transition: all .15s;
    }
    .modal-cancel:hover { background: var(--surface); }

    /* spinner */
    .spin { animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

{{-- No external deps needed — uses native getUserMedia + BarcodeDetector --}}

<div class="page-wrap">
<div class="page-inner">

    {{-- Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <div class="page-hd-icon">
                <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h1>Tambah Buku Baru</h1>
                <p>Input detail buku atau scan barcode untuk mengisi data otomatis</p>
            </div>
        </div>
        <a href="{{ route('books.index') }}" class="back-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" id="bookForm">
        @csrf
        <div class="form-grid">

            {{-- ─── LEFT COLUMN ─── --}}
            <div>

                {{-- BARCODE CARD ─────────────────────────────── --}}
                <div class="card" style="margin-bottom:1.25rem; border-color:#c7d7fd;">
                    <div class="card-hd">
                        <div class="card-hd-icon" style="background:#eff6ff;">
                            <svg width="16" height="16" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <h3>Barcode Buku</h3>
                    </div>
                    <div class="card-body">
                        <div class="field" style="margin-bottom:10px;">
                            <label>Nomor Barcode <span class="req">*</span></label>

                            <div class="barcode-outer">
                                <input
                                    type="text"
                                    name="barcode"
                                    id="barcodeInput"
                                    class="input input-mono @error('barcode') is-error @enderror"
                                    placeholder="Klik di sini lalu scan barcode..."
                                    value="{{ old('barcode') }}"
                                    autocomplete="off"
                                    autofocus
                                >
                                <div class="barcode-scanner-indicator">
                                    <div class="scanner-dot"></div>
                                    <span id="scannerLabel">Siap scan</span>
                                </div>
                            </div>

                            {{-- Status cek duplikat --}}
                            <div class="barcode-status" id="barcodeStatus">
                                <svg id="statusIcon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="spin"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0110 10" stroke-linecap="round"/></svg>
                                <span id="statusMsg">Memeriksa barcode...</span>
                            </div>

                            {{-- Duplicate book card --}}
                            <div class="dup-card" id="dupCard">
                                <div class="dup-card-head">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Buku dengan barcode ini sudah ada!
                                </div>
                                <div class="dup-card-body">
                                    <div class="dup-cover" id="dupCover">
                                        <svg width="18" height="18" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                                    </div>
                                    <div class="dup-info">
                                        <div class="dup-name" id="dupName">—</div>
                                        <div class="dup-writer" id="dupWriter">—</div>
                                        <div class="dup-stock">Stok saat ini: <strong id="dupStock">—</strong></div>
                                        <div class="dup-actions">
                                            <button type="button" class="dup-btn dup-btn-add" id="dupAddStockBtn">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                Tambah Stok
                                            </button>
                                            <a href="#" class="dup-btn dup-btn-view" id="dupEditLink">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit Buku
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @error('barcode')
                            <p class="err-msg">{{ $message }}</p>
                            @enderror

                            <p class="field-hint" style="margin-top:6px;">
                                Gunakan <strong>hardware barcode scanner</strong> (USB/Bluetooth) — klik field lalu scan. Atau ketik nomor barcode secara manual.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- INFO CARD ─────────────────────────────────────── --}}
                <div class="card" style="margin-bottom:1.25rem;">
                    <div class="card-hd">
                        <div class="card-hd-icon" style="background:#f3f4f6;">
                            <svg width="15" height="15" fill="none" stroke="#374151" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3>Informasi Buku</h3>
                    </div>
                    <div class="card-body">

                        <div class="field">
                            <label>Nama Buku <span class="req">*</span></label>
                            <input type="text" name="name" id="nameInput"
                                class="input @error('name') is-error @enderror"
                                placeholder="Judul lengkap buku"
                                value="{{ old('name') }}" required>
                            @error('name')<p class="err-msg">{{ $message }}</p>@enderror
                        </div>

                        <div class="field">
                            <label>Penulis</label>
                            <input type="text" name="writer" id="writerInput"
                                class="input @error('writer') is-error @enderror"
                                placeholder="Nama penulis / pengarang"
                                value="{{ old('writer') }}">
                            @error('writer')<p class="err-msg">{{ $message }}</p>@enderror
                        </div>

                        <div class="field">
                            <label>Kategori <span class="req">*</span></label>
                            <select name="category_id" id="categoryInput"
                                class="input @error('category_id') is-error @enderror" required>
                                <option value="">— Pilih kategori —</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="err-msg">{{ $message }}</p>@enderror
                        </div>

                        <div class="field">
                            <label>Stok <span class="req">*</span></label>
                            <input type="number" name="stock" id="stockInput" min="0"
                                class="input @error('stock') is-error @enderror"
                                placeholder="0"
                                value="{{ old('stock', 1) }}" required>
                            <div class="stock-btns">
                                <button type="button" class="stock-preset" onclick="setStock(1)">1</button>
                                <button type="button" class="stock-preset" onclick="setStock(3)">3</button>
                                <button type="button" class="stock-preset" onclick="setStock(5)">5</button>
                                <button type="button" class="stock-preset" onclick="setStock(10)">10</button>
                                <button type="button" class="stock-preset" onclick="setStock(20)">20</button>
                            </div>
                            @error('stock')<p class="err-msg">{{ $message }}</p>@enderror
                        </div>

                        <div class="field" style="margin-bottom:0;">
                            <label>Sinopsis</label>
                            <textarea name="sypnosis" id="synopsisInput"
                                class="input @error('sypnosis') is-error @enderror"
                                placeholder="Ringkasan cerita atau deskripsi buku..."
                                rows="4">{{ old('sypnosis') }}</textarea>
                            @error('sypnosis')<p class="err-msg">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>

                {{-- SUBMIT ROW --}}
                <div class="submit-row">
                    <a href="{{ route('books.index') }}" class="btn-cancel">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batal
                    </a>
                    <button type="submit" class="btn-submit">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Simpan Buku
                    </button>
                </div>

            </div>{{-- /left --}}

            {{-- ─── RIGHT COLUMN ─── --}}
            <div>

                {{-- COVER UPLOAD ──────────────────────────────────── --}}
                <div class="card" style="margin-bottom:1.25rem;">
                    <div class="card-hd">
                        <div class="card-hd-icon" style="background:#fdf4ff;">
                            <svg width="15" height="15" fill="none" stroke="#9333ea" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3>Cover Buku</h3>
                    </div>
                    <div class="card-body">
                        <label class="cover-upload" id="coverLabel">
                            <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewCover(event)">
                            <div class="cover-upload-placeholder" id="coverPlaceholder">
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Upload Cover</span>
                                <small>PNG, JPG (maks. 2MB)</small>
                            </div>
                            <img id="coverPreview" src="" alt="" style="display:none;">
                            <div class="cover-change-overlay">
                                <span>🔄 Ganti Foto</span>
                            </div>
                        </label>
                        @error('image')<p class="err-msg" style="margin-top:6px;">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- TIPS CARD ──────────────────────────────────────── --}}
                <div class="card" style="border-color:#c7d7fd;">
                    <div class="card-hd" style="background:#eff6ff;">
                        <div class="card-hd-icon" style="background:#dbeafe;">
                            <svg width="14" height="14" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <h3 style="color:#1d4ed8;">Panduan Input Barcode</h3>
                    </div>
                    <div class="card-body" style="padding:14px 16px;">
                        <div style="display:flex; flex-direction:column; gap:12px;">

                            <div style="display:flex; gap:10px; align-items:flex-start;">
                                <div style="width:28px; height:28px; border-radius:7px; background:#2563eb; color:#fff; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">1</div>
                                <div>
                                    <p style="font-size:12.5px; font-weight:700; color:#1e40af; margin:0 0 2px;">Klik field barcode</p>
                                    <p style="font-size:11.5px; color:#3b82f6; margin:0; line-height:1.4;">Pastikan kursor ada di field barcode (indikator <span style="color:var(--success);font-weight:700;">hijau</span> = siap)</p>
                                </div>
                            </div>

                            <div style="display:flex; gap:10px; align-items:flex-start;">
                                <div style="width:28px; height:28px; border-radius:7px; background:#2563eb; color:#fff; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">2</div>
                                <div>
                                    <p style="font-size:12.5px; font-weight:700; color:#1e40af; margin:0 0 2px;">Scan dengan scanner fisik</p>
                                    <p style="font-size:11.5px; color:#3b82f6; margin:0; line-height:1.4;">Scanner USB/Bluetooth otomatis mengisi field & cek duplikat</p>
                                </div>
                            </div>

                            <div style="display:flex; gap:10px; align-items:flex-start;">
                                <div style="width:28px; height:28px; border-radius:7px; background:#059669; color:#fff; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">✓</div>
                                <div>
                                    <p style="font-size:12.5px; font-weight:700; color:#065f46; margin:0 0 2px;">Duplikat terdeteksi otomatis</p>
                                    <p style="font-size:11.5px; color:#059669; margin:0; line-height:1.4;">Jika barcode sudah ada, bisa langsung <strong>Tambah Stok</strong> tanpa form baru</p>
                                </div>
                            </div>

                            <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:10px 12px; margin-top:2px;">
                                <p style="font-size:11px; color:#0369a1; margin:0; font-weight:500; line-height:1.5;">
                                    💡 <strong>Tidak punya scanner?</strong> Ketik nomor barcode manual. Format bebas — bisa ISBN, kode internal, atau nomor urut.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>{{-- /right --}}

        </div>{{-- /form-grid --}}
    </form>

</div>
</div>

{{-- ══════════════════════════════════════════════════
     QUICK ADD STOCK MODAL
══════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="stockModal">
    <div class="modal-box">
        <div class="modal-hd">
            <h3>Tambah Stok Buku</h3>
            <p>Masukkan jumlah stok yang ingin ditambahkan</p>
        </div>
        <form method="POST" id="stockForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="modal-book-preview">
                    <div class="modal-cover" id="modalCover">
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                    </div>
                    <div>
                        <div class="modal-book-name" id="modalBookName">—</div>
                        <div class="modal-book-stock" id="modalBookStock">Stok saat ini: —</div>
                    </div>
                </div>

                <label style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:8px;">
                    Tambah Stok Sebanyak
                </label>
                <div class="modal-stock-input">
                    <button type="button" class="modal-stock-btn" onclick="changeModalStock(-1)">−</button>
                    <input type="number" name="stock_add" id="modalStockAdd" value="1" min="1" max="999">
                    <button type="button" class="modal-stock-btn" onclick="changeModalStock(1)">+</button>
                </div>
                {{-- Hidden fields to send full update --}}
                <input type="hidden" name="_stock_add_mode" value="1">
                <input type="hidden" name="stock_current" id="stockCurrentHidden" value="0">
            </div>
            <div class="modal-ft">
                <button type="button" class="modal-cancel" onclick="closeStockModal()">Batal</button>
                <button type="submit" class="modal-save">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Simpan Stok
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════════════════
   BARCODE — Hardware scanner
═══════════════════════════════════════════════════════════ */
var LOOKUP_URL  = '{{ route('books.barcode-lookup') }}';
var BOOKS_INDEX = '{{ route('books.index') }}';
var barcodeDebounce = null;
var lastChecked = '';
var dupBookData  = null;

var barcodeInput = document.getElementById('barcodeInput');

// Hardware scanner sends chars very fast then stops
barcodeInput.addEventListener('input', function () {
    clearTimeout(barcodeDebounce);
    hideDup(); hideStatus();
    var val = barcodeInput.value.trim();
    if (val.length < 3) return;
    barcodeDebounce = setTimeout(function () {
        if (val !== lastChecked) checkBarcode(val);
    }, 180); // 180ms — long enough for keyboard typing, short enough for scanner
});

barcodeInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(barcodeDebounce);
        var val = barcodeInput.value.trim();
        if (val && val !== lastChecked) checkBarcode(val);
    }
});

function checkBarcode(barcode) {
    lastChecked = barcode;
    showStatus('checking', 'Memeriksa barcode...');

    fetch(LOOKUP_URL + '?barcode=' + encodeURIComponent(barcode), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
    .then(function (res) {
        if (res.ok) {
            // Found! — duplicate
            dupBookData = res.d;
            showStatus('duplicate', '⚠️ Barcode sudah terdaftar — lihat info di bawah');
            showDup(res.d);
        } else {
            // Not found — ok to use
            showStatus('available', '✅ Barcode belum digunakan, siap dipakai');
            hideDup();
            dupBookData = null;
        }
    })
    .catch(function () {
        hideStatus(); hideDup(); dupBookData = null;
    });
}

/* ═══════════════════════════════════════════════════════════
   STATUS / DUP CARD
═══════════════════════════════════════════════════════════ */
function showStatus(type, msg) {
    var el = document.getElementById('barcodeStatus');
    el.className = 'barcode-status ' + type;
    document.getElementById('statusMsg').textContent = msg;
    var icon = document.getElementById('statusIcon');
    if (type === 'checking') {
        icon.innerHTML = '<circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0110 10" stroke-linecap="round"/>';
        icon.classList.add('spin');
    } else if (type === 'available') {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        icon.classList.remove('spin');
    } else {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
        icon.classList.remove('spin');
    }
}

function hideStatus() { document.getElementById('barcodeStatus').className = 'barcode-status'; }

function showDup(book) {
    document.getElementById('dupCard').style.display = 'block';
    document.getElementById('dupName').textContent   = book.name   || '—';
    document.getElementById('dupWriter').textContent = book.writer  || '—';
    document.getElementById('dupStock').textContent  = book.stock;

    var cover = document.getElementById('dupCover');
    if (book.image) {
        cover.innerHTML = '<img src="/storage/' + book.image + '" alt="' + esc(book.name) + '">';
    } else {
        cover.innerHTML = '<svg width="18" height="18" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>';
    }

    // Edit link
    document.getElementById('dupEditLink').href = '{{ url('books') }}/' + book.id + '/edit';
}

function hideDup() { document.getElementById('dupCard').style.display = 'none'; }

// Tambah stok button
document.getElementById('dupAddStockBtn').addEventListener('click', function () {
    if (!dupBookData) return;
    openStockModal(dupBookData);
});

/* ═══════════════════════════════════════════════════════════
   QUICK ADD STOCK MODAL
═══════════════════════════════════════════════════════════ */
function openStockModal(book) {
    document.getElementById('modalBookName').textContent  = book.name || '—';
    document.getElementById('modalBookStock').textContent = 'Stok saat ini: ' + book.stock;
    document.getElementById('modalStockAdd').value = 1;
    document.getElementById('stockCurrentHidden').value = book.stock;

    // Set form action to book update route
    var form = document.getElementById('stockForm');
    form.action = '{{ url('books') }}/' + book.id;

    var cover = document.getElementById('modalCover');
    if (book.image) {
        cover.innerHTML = '<img src="/storage/' + book.image + '" alt="" style="width:100%;height:100%;object-fit:cover;">';
    } else {
        cover.innerHTML = '<svg width="16" height="16" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13"/></svg>';
    }

    document.getElementById('stockModal').classList.add('open');
}

function closeStockModal() {
    document.getElementById('stockModal').classList.remove('open');
}

document.getElementById('stockModal').addEventListener('click', function (e) {
    if (e.target === this) closeStockModal();
});

function changeModalStock(delta) {
    var input = document.getElementById('modalStockAdd');
    var val = parseInt(input.value) || 1;
    val = Math.max(1, val + delta);
    input.value = val;
}

// On stock form submit: calculate new total stock and send as 'stock'
document.getElementById('stockForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var add     = parseInt(document.getElementById('modalStockAdd').value) || 0;
    var current = parseInt(document.getElementById('stockCurrentHidden').value) || 0;
    var newStock = current + add;

    // We'll POST to the books update endpoint with just the stock change
    var form = this;
    var url  = form.action;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ?
                document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            _method: 'PUT',
            stock: newStock,
            _stock_add_mode: 1
        })
    })
    .then(function (r) {
        if (r.ok || r.status === 302 || r.status === 200) {
            closeStockModal();
            // Update the dup card stock display
            if (dupBookData) {
                dupBookData.stock = newStock;
                document.getElementById('dupStock').textContent = newStock;
                document.getElementById('modalBookStock').textContent = 'Stok saat ini: ' + newStock;
            }
            // Show success toast
            showToast('✅ Stok berhasil ditambahkan! Stok baru: ' + newStock, 'success');
        } else {
            showToast('❌ Gagal memperbarui stok, coba via tombol Edit Buku.', 'error');
        }
    })
    .catch(function () {
        // Fallback: submit form normally
        var hiddenStock = document.createElement('input');
        hiddenStock.type = 'hidden';
        hiddenStock.name = 'stock';
        hiddenStock.value = newStock;
        form.appendChild(hiddenStock);
        form.submit();
    });
});

/* ═══════════════════════════════════════════════════════════
   SCANNER FOCUS INDICATOR
   Tunjukkan label "Aktif" saat field di-focus (scanner ready),
   dan "Siap scan" saat blur. Plus flash animation saat barcode masuk.
═══════════════════════════════════════════════════════════ */
var barcodeInputEl = document.getElementById('barcodeInput');
var scannerLabel   = document.getElementById('scannerLabel');

barcodeInputEl.addEventListener('focus', function () {
    scannerLabel.textContent = 'Aktif — scan sekarang';
});

barcodeInputEl.addEventListener('blur', function () {
    scannerLabel.textContent = 'Siap scan';
});

// Saat scanner masuk (input event setelah debounce selesai = scan sukses),
// tambahkan flash class singkat
var prevLen = 0;
barcodeInputEl.addEventListener('input', function () {
    var cur = this.value.length;
    // Scanner biasanya langsung input banyak karakter sekaligus
    if (cur > prevLen + 3) {
        this.classList.add('barcode-flash');
        setTimeout(function () {
            barcodeInputEl.classList.remove('barcode-flash');
        }, 1200);
    }
    prevLen = cur;
});

/* ═══════════════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════════════ */
function previewCover(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (ev) {
        var img = document.getElementById('coverPreview');
        var ph  = document.getElementById('coverPlaceholder');
        img.src = ev.target.result;
        img.style.display = 'block';
        ph.style.display  = 'none';
    };
    reader.readAsDataURL(file);
}

function setStock(n) {
    document.getElementById('stockInput').value = n;
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Toast
function showToast(msg, type) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = [
        'position:fixed; bottom:24px; right:24px; z-index:99999;',
        'padding:12px 20px; border-radius:10px; font-size:13.5px; font-weight:600;',
        'font-family:Sora,sans-serif; box-shadow:0 8px 24px rgba(0,0,0,.15);',
        'animation:toast-in .25s ease;',
        type === 'success'
            ? 'background:#059669; color:#fff;'
            : 'background:#dc2626; color:#fff;'
    ].join('');
    document.body.appendChild(t);
    setTimeout(function () { t.remove(); }, 4000);
}

// Toast keyframe (inject once)
if (!document.getElementById('toast-style')) {
    var s = document.createElement('style');
    s.id = 'toast-style';
    s.textContent = '@keyframes toast-in { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }';
    document.head.appendChild(s);
}
</script>

@endsection