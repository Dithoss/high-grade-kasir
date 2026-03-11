@extends('layouts.app')

@section('title', 'Manajemen Buku')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg:    #f8fafc;
        --panel: #ffffff;
        --border:#e2e8f0;
        --muted: #94a3b8;
        --text:  #0f172a;
        --text2: #334155;
        --green: #16a34a;
        --red:   #dc2626;
        --blue:  #2563eb;
        --yellow:#d97706;
        --orange:#ea580c;
        --purple:#7c3aed;
        --accent:#2563eb;
    }

    .admin-books { font-family: 'IBM Plex Sans', sans-serif; }

    /* ── Top bar ── */
    .top-bar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px;
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 12px;
        gap: 16px; flex-wrap: wrap;
    }
    .top-bar-title {
        font-size: 1.1rem; font-weight: 700; color: var(--text);
        display: flex; align-items: center; gap: 10px;
    }
    .top-bar-title .badge {
        font-family: 'IBM Plex Mono', monospace;
        font-size: .72rem; font-weight: 600;
        padding: 2px 8px; border-radius: 6px;
        background: #eff6ff; color: var(--blue);
        border: 1px solid #bfdbfe;
    }

    /* ── Stat cards ── */
    .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    @media(max-width:900px) { .stat-row { grid-template-columns: repeat(2,1fr); } }
    @media(max-width:560px) { .stat-row { grid-template-columns: 1fr 1fr; } }

    .stat-card {
        background: var(--panel); border: 1px solid var(--border);
        border-radius: 10px; padding: 16px 18px;
        position: relative; overflow: hidden;
        transition: border-color .2s;
    }
    .stat-card:hover { border-color: #93c5fd; }
    .stat-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 2px;
    }
    .stat-card.blue::before   { background: var(--blue); }
    .stat-card.green::before  { background: var(--green); }
    .stat-card.red::before    { background: var(--red); }
    .stat-card.yellow::before { background: var(--yellow); }

    .stat-label { font-size: .75rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
    .stat-value { font-family: 'IBM Plex Mono', monospace; font-size: 1.8rem; font-weight: 700; color: var(--text); line-height: 1; }
    .stat-sub   { font-size: .75rem; color: var(--muted); margin-top: 4px; }

    /* ── Filter toolbar ── */
    .filter-bar {
        background: var(--panel); border: 1px solid var(--border);
        border-radius: 10px; padding: 12px 16px;
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .search-input-wrap {
        display: flex; align-items: center; gap: 8px;
        background: #f8fafc; border: 1px solid var(--border);
        border-radius: 8px; padding: 7px 12px; flex: 1; min-width: 200px;
        transition: border-color .2s;
    }
    .search-input-wrap:focus-within { border-color: var(--accent); }
    .search-input-wrap input {
        background: transparent; border: none; outline: none;
        color: var(--text); font-family: 'IBM Plex Sans', sans-serif;
        font-size: .875rem; flex: 1;
    }
    .search-input-wrap input::placeholder { color: var(--muted); }
    .admin-select {
        background: #f8fafc; border: 1px solid var(--border);
        border-radius: 8px; padding: 7px 12px; color: var(--text2);
        font-family: 'IBM Plex Sans', sans-serif; font-size: .875rem;
        outline: none; cursor: pointer; transition: border-color .2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238b949e' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
        padding-right: 28px;
    }
    .admin-select:focus { border-color: var(--accent); }

    .btn-admin {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 8px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-weight: 600; font-size: .85rem; cursor: pointer;
        border: 1px solid; transition: all .15s; text-decoration: none;
        white-space: nowrap;
    }
    .btn-green  { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .btn-green:hover  { background: #dcfce7; color: #15803d; }
    .btn-blue   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .btn-blue:hover   { background: #dbeafe; color: #1d4ed8; }
    .btn-red    { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .btn-red:hover    { background: #fee2e2; color: #b91c1c; }
    .btn-yellow { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .btn-yellow:hover { background: #fef3c7; color: #b45309; }
    .btn-muted  { background: #f8fafc; color: var(--muted); border-color: var(--border); }
    .btn-muted:hover  { background: #f1f5f9; color: var(--text2); }
    .btn-accent { background: var(--accent); color: white; border-color: var(--accent); }
    .btn-accent:hover { background: #1a5ed4; color: white; }

    /* ── Table ── */
    .table-wrap {
        background: var(--panel); border: 1px solid var(--border);
        border-radius: 12px; overflow: hidden;
    }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table thead tr {
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
    }
    .admin-table th {
        padding: 10px 14px; text-align: left;
        font-family: 'IBM Plex Mono', monospace;
        font-size: .72rem; font-weight: 600; color: var(--muted);
        text-transform: uppercase; letter-spacing: .07em;
        white-space: nowrap;
    }
    .admin-table th.sortable { cursor: pointer; user-select: none; }
    .admin-table th.sortable:hover { color: var(--text2); }
    .admin-table td {
        padding: 11px 14px; border-bottom: 1px solid var(--border);
        vertical-align: middle; font-size: .875rem; color: var(--text2);
    }
    .admin-table tbody tr:last-child td { border-bottom: none; }
    .admin-table tbody tr { transition: background .12s; }
    .admin-table tbody tr:hover { background: #f8fafc; }
    .admin-table tbody tr.selected { background: #eff6ff; }

    /* Book thumb */
    .book-thumb {
        width: 36px; height: 48px; border-radius: 5px;
        object-fit: cover; border: 1px solid var(--border);
        flex-shrink: 0;
    }
    .book-thumb-placeholder {
        width: 36px; height: 48px; border-radius: 5px;
        background: #f1f5f9; border: 1px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        color: var(--muted); font-size: 14px; flex-shrink: 0;
    }

    /* Book title cell */
    .book-title-cell { display: flex; align-items: center; gap: 12px; }
    .book-name {
        font-weight: 600; color: var(--text); font-size: .875rem;
        display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
        max-width: 240px;
    }
    .book-name a { color: var(--text); text-decoration: none; }
    .book-name a:hover { color: var(--blue); }
    .book-author { font-size: .75rem; color: var(--muted); margin-top: 2px; }

    /* Stock indicator */
    .stock-indicator {
        display: flex; align-items: center; gap: 6px;
        font-family: 'IBM Plex Mono', monospace; font-size: .82rem; font-weight: 600;
    }
    .stock-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    /* Category pill */
    .cat-pill {
        display: inline-block; padding: 2px 8px; border-radius: 5px;
        font-size: .72rem; font-weight: 600;
        background: #f5f3ff; color: #7c3aed;
        border: 1px solid #ddd6fe;
        font-family: 'IBM Plex Mono', monospace;
    }

    /* Status badge */
    .status-active { color: var(--green); }
    .status-low    { color: var(--yellow); }
    .status-empty  { color: var(--red); }

    /* Row checkbox */
    .row-check {
        width: 15px; height: 15px; cursor: pointer;
        accent-color: var(--accent);
    }

    /* Action buttons in row */
    .row-action {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 6px;
        border: 1px solid var(--border); background: transparent;
        color: var(--muted); cursor: pointer; transition: all .15s;
        text-decoration: none; font-size: .8rem;
    }
    .row-action:hover.edit   { border-color: #bfdbfe; color: #1d4ed8; background: #eff6ff; }
    .row-action:hover.delete { border-color: #fecaca; color: #b91c1c; background: #fef2f2; }
    .row-action:hover.view   { border-color: #bbf7d0; color: #15803d; background: #f0fdf4; }

    /* ── Pagination ── */
    .pag-wrap { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; flex-wrap: wrap; gap: 8px; }
    .pag-info { font-size: .8rem; color: var(--muted); font-family: 'IBM Plex Mono', monospace; }
    .pag-btns { display: flex; gap: 4px; }
    .pag-btn {
        min-width: 32px; height: 32px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid var(--border); background: transparent;
        font-size: .8rem; font-weight: 500; color: var(--muted);
        text-decoration: none; transition: all .15s; cursor: pointer;
        font-family: 'IBM Plex Mono', monospace;
    }
    .pag-btn:hover { border-color: var(--accent); color: var(--blue); }
    .pag-btn.active { background: var(--accent); border-color: var(--accent); color: white; }
    .pag-btn.disabled { opacity: .35; pointer-events: none; }

    /* ── Bulk action bar ── */
    .bulk-bar {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 10px; padding: 10px 16px;
        display: flex; align-items: center; gap: 12px;
        display: none;
    }
    .bulk-bar.visible { display: flex; }
    .bulk-count { font-weight: 700; color: var(--blue); font-size: .875rem; }

    /* ── Empty ── */
    .empty-row td { text-align: center; padding: 60px 20px; color: var(--muted); }
    .empty-row .empty-icon { font-size: 2.5rem; margin-bottom: 10px; opacity: .4; }

    /* ── Scrollbar ── */
    .table-scroll { overflow-x: auto; }
    .table-scroll::-webkit-scrollbar { height: 4px; }
    .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }

    /* ── Stock bar ── */
    .stock-bar-wrap { display: flex; align-items: center; gap: 8px; }
    .stock-bar-bg { flex: 1; height: 4px; background: var(--border); border-radius: 2px; max-width: 60px; }
    .stock-bar-fill { height: 100%; border-radius: 2px; }

    /* Dark panel overrides since we're inside a potentially light layout */
    .admin-books * { box-sizing: border-box; }
</style>
@endpush

@section('content')
@php
    $totalBooks    = $book->total();
    $allBooks      = \App\Models\Book::withTrashed();
    $availCount    = \App\Models\Book::where('stock', '>', 0)->count();
    $emptyCount    = \App\Models\Book::where('stock', 0)->count();
    $lowCount      = \App\Models\Book::whereBetween('stock', [1, 3])->count();
    $trashedCount  = \App\Models\Book::onlyTrashed()->count();
    $maxStock      = \App\Models\Book::max('stock') ?: 1;
@endphp

<div class="admin-books space-y-4">

    {{-- ── Top Bar ── --}}
    <div class="top-bar">
        <div class="top-bar-title">
            <i class="fas fa-database" style="color:var(--blue);"></i>
            Manajemen Buku
            <span class="badge">{{ number_format($totalBooks) }} records</span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('books.trash') }}" class="btn-admin btn-yellow">
                <i class="fas fa-trash-restore text-xs"></i>
                Trash <span style="font-family:'IBM Plex Mono',monospace;font-size:.75rem;">({{ $trashedCount }})</span>
            </a>
            <a href="{{ route('books.create') }}" class="btn-admin btn-accent">
                <i class="fas fa-plus text-xs"></i> Tambah Buku
            </a>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="stat-row">
        <div class="stat-card blue">
            <div class="stat-label">Total Buku</div>
            <div class="stat-value">{{ number_format(\App\Models\Book::count()) }}</div>
            <div class="stat-sub">{{ \App\Models\Category::count() }} kategori</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Tersedia</div>
            <div class="stat-value" style="color:var(--green);">{{ number_format($availCount) }}</div>
            <div class="stat-sub">Stok &gt; 0</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-label">Stok Rendah</div>
            <div class="stat-value" style="color:var(--yellow);">{{ $lowCount }}</div>
            <div class="stat-sub">Stok 1–3</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Habis</div>
            <div class="stat-value" style="color:var(--red);">{{ $emptyCount }}</div>
            <div class="stat-sub">Stok = 0</div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form method="GET" action="{{ route('books.index') }}" id="adminFilterForm">
        <div class="filter-bar">
            {{-- Search --}}
            <div class="search-input-wrap">
                <i class="fas fa-search" style="color:var(--muted);font-size:.8rem;"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul, penulis, ISBN...">
            </div>

            {{-- Category --}}
            <select name="category_id" class="admin-select" onchange="document.getElementById('adminFilterForm').submit()">
                <option value="">Semua Kategori</option>
                @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>

            {{-- Stock filter --}}
            <select name="stock_status" class="admin-select" onchange="document.getElementById('adminFilterForm').submit()">
                <option value="">Semua Stok</option>
                <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="low"       {{ request('stock_status') === 'low'       ? 'selected' : '' }}>Rendah (1–3)</option>
                <option value="empty"     {{ request('stock_status') === 'empty'     ? 'selected' : '' }}>Habis</option>
            </select>

            {{-- Sort --}}
            <select name="sort_by" class="admin-select" onchange="document.getElementById('adminFilterForm').submit()">
                <option value="created_at" {{ request('sort_by','created_at') === 'created_at' ? 'selected' : '' }}>Terbaru</option>
                <option value="name"       {{ request('sort_by') === 'name'   ? 'selected' : '' }}>Nama A–Z</option>
                <option value="stock"      {{ request('sort_by') === 'stock'  ? 'selected' : '' }}>Stok ↓</option>
            </select>

            <button type="submit" class="btn-admin btn-blue">
                <i class="fas fa-search text-xs"></i> Filter
            </button>

            @if(request()->hasAny(['search','category_id','stock_status','sort_by']))
            <a href="{{ route('books.index') }}" class="btn-admin btn-muted">
                <i class="fas fa-times text-xs"></i> Reset
            </a>
            @endif
        </div>
    </form>

    {{-- ── Bulk action bar ── --}}
    <div class="bulk-bar" id="bulkBar">
        <span class="bulk-count" id="bulkCount">0</span>
        <span style="color:var(--muted);font-size:.875rem;">item dipilih</span>
        <div style="height:16px;width:1px;background:var(--border);"></div>
        <form method="POST" action="{{ route('books.mass-delete') }}" id="massDeleteForm"
              onsubmit="return confirmMassDelete()">
            @csrf @method('DELETE')
            <div id="massDeleteInputs"></div>
            <button type="submit" class="btn-admin btn-red">
                <i class="fas fa-trash text-xs"></i> Hapus Dipilih
            </button>
        </form>
        <button onclick="clearSelection()" class="btn-admin btn-muted" style="margin-left:auto;">
            <i class="fas fa-times text-xs"></i> Batal
        </button>
    </div>

    {{-- ── Table ── --}}
    <div class="table-wrap">
        <div class="table-scroll">
            <table class="admin-table" id="booksTable">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" class="row-check" id="checkAll" onchange="toggleAll(this)">
                        </th>
                        <th style="min-width:280px;" class="sortable" onclick="sortBy('name')">
                            BUKU
                            @if(request('sort_by')==='name') <i class="fas fa-sort-up ml-1"></i>
                            @else <i class="fas fa-sort ml-1" style="opacity:.3;"></i> @endif
                        </th>
                        <th>KATEGORI</th>
                        <th class="sortable" onclick="sortBy('stock')">
                            STOK
                            @if(request('sort_by')==='stock') <i class="fas fa-sort-up ml-1"></i>
                            @else <i class="fas fa-sort ml-1" style="opacity:.3;"></i> @endif
                        </th>
                        <th>STATUS</th>
                        <th>PREORDER</th>
                        <th class="sortable" onclick="sortBy('created_at')">
                            DITAMBAH
                            @if(request('sort_by','created_at')==='created_at') <i class="fas fa-sort-up ml-1"></i>
                            @else <i class="fas fa-sort ml-1" style="opacity:.3;"></i> @endif
                        </th>
                        <th style="width:110px; text-align:right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($book as $b)
                    @php
                        $poCount = \App\Models\Preorder::where('book_id', $b->id)
                                    ->whereIn('status', ['pending','ready'])->count();
                        $stockPct = $maxStock > 0 ? min(100, ($b->stock / $maxStock) * 100) : 0;
                        $stockColor = $b->stock === 0 ? 'var(--red)' : ($b->stock <= 3 ? 'var(--yellow)' : 'var(--green)');
                    @endphp
                    <tr class="book-row" data-id="{{ $b->id }}">
                        <td>
                            <input type="checkbox" class="row-check book-check" value="{{ $b->id }}" onchange="updateBulk()">
                        </td>
                        <td>
                            <div class="book-title-cell">
                                @if($b->image)
                                    <img src="{{ asset('storage/'.$b->image) }}" class="book-thumb" alt="">
                                @else
                                    <div class="book-thumb-placeholder"><i class="fas fa-book"></i></div>
                                @endif
                                <div>
                                    <div class="book-name">
                                        <a href="{{ route('books.show', $b->slug) }}" target="_blank">{{ $b->name }}</a>
                                    </div>
                                    <div class="book-author">{{ $b->writer ?? '—' }}</div>
                                    @if($b->barcode)
                                    <div style="font-family:'IBM Plex Mono',monospace;font-size:.7rem;color:var(--muted);margin-top:2px;">
                                        {{ $b->barcode }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($b->category)
                                <span class="cat-pill">{{ $b->category->name }}</span>
                            @else
                                <span style="color:var(--muted);font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="stock-bar-wrap">
                                <div class="stock-indicator">
                                    <span class="stock-dot" style="background:{{ $stockColor }};"></span>
                                    <span style="color:{{ $stockColor }};">{{ $b->stock }}</span>
                                </div>
                                <div class="stock-bar-bg">
                                    <div class="stock-bar-fill" style="width:{{ $stockPct }}%;background:{{ $stockColor }};"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($b->stock === 0)
                                <span style="font-size:.75rem;font-weight:700;color:var(--red);">
                                    <i class="fas fa-times-circle mr-1"></i>Habis
                                </span>
                            @elseif($b->stock <= 3)
                                <span style="font-size:.75rem;font-weight:700;color:var(--yellow);">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Rendah
                                </span>
                            @else
                                <span style="font-size:.75rem;font-weight:700;color:var(--green);">
                                    <i class="fas fa-check-circle mr-1"></i>Tersedia
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($poCount > 0)
                                <span style="font-family:'IBM Plex Mono',monospace;font-size:.8rem;
                                             background:#fff7ed;color:#c2410c;
                                             border:1px solid #fed7aa;
                                             padding:2px 8px;border-radius:5px;font-weight:600;">
                                    {{ $poCount }} antrian
                                </span>
                            @else
                                <span style="color:var(--muted);font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-family:'IBM Plex Mono',monospace;font-size:.75rem;color:var(--muted);">
                                {{ $b->created_at->format('d M y') }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;justify-content:flex-end;gap:4px;">
                                <a href="{{ route('books.show', $b->slug) }}" target="_blank"
                                   class="row-action view" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('books.edit', $b->id) }}"
                                   class="row-action edit" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('books.destroy', $b->id) }}"
                                      onsubmit="return confirm('Hapus buku \'{{ addslashes($b->name) }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="row-action delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="8">
                            <div class="empty-icon">📭</div>
                            <p style="font-weight:600;color:var(--text2);margin-bottom:4px;">Tidak Ada Buku</p>
                            <p style="font-size:.85rem;">Coba ubah filter atau tambah buku baru</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ── --}}
        @if($book->hasPages())
        <div style="border-top:1px solid var(--border);">
            <div class="pag-wrap">
                <span class="pag-info">
                    {{ $book->firstItem() }}–{{ $book->lastItem() }} / {{ number_format($book->total()) }}
                </span>
                <div class="pag-btns">
                    @if($book->onFirstPage())
                        <span class="pag-btn disabled"><i class="fas fa-chevron-left" style="font-size:.7rem;"></i></span>
                    @else
                        <a href="{{ $book->previousPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-left" style="font-size:.7rem;"></i></a>
                    @endif

                    @foreach($book->getUrlRange(1, $book->lastPage()) as $page => $url)
                        @if($page == $book->currentPage())
                            <span class="pag-btn active">{{ $page }}</span>
                        @elseif($page == 1 || $page == $book->lastPage() || abs($page - $book->currentPage()) <= 1)
                            <a href="{{ $url }}" class="pag-btn">{{ $page }}</a>
                        @elseif(abs($page - $book->currentPage()) == 2)
                            <span class="pag-btn disabled">…</span>
                        @endif
                    @endforeach

                    @if($book->hasMorePages())
                        <a href="{{ $book->nextPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></a>
                    @else
                        <span class="pag-btn disabled"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Lihat semua (redirect ke catalog untuk admin reference) --}}
    <div class="flex justify-end">
        <a href="{{ route('books.catalog') }}" target="_blank"
           class="btn-admin btn-muted" style="font-size:.8rem;">
            <i class="fas fa-external-link-alt text-xs"></i> Lihat Tampilan User
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Checkbox logic ──
function toggleAll(el) {
    document.querySelectorAll('.book-check').forEach(c => {
        c.checked = el.checked;
        c.closest('tr').classList.toggle('selected', el.checked);
    });
    updateBulk();
}
function updateBulk() {
    const checked = document.querySelectorAll('.book-check:checked');
    const bar = document.getElementById('bulkBar');
    document.getElementById('bulkCount').textContent = checked.length;
    bar.classList.toggle('visible', checked.length > 0);

    // Update mass delete hidden inputs
    const container = document.getElementById('massDeleteInputs');
    container.innerHTML = '';
    checked.forEach(c => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = c.value;
        container.appendChild(inp);
    });

    // Sync check-all state
    const all = document.querySelectorAll('.book-check');
    document.getElementById('checkAll').indeterminate = checked.length > 0 && checked.length < all.length;
    document.getElementById('checkAll').checked = checked.length === all.length && all.length > 0;

    // Highlight rows
    document.querySelectorAll('.book-check').forEach(c => {
        c.closest('tr').classList.toggle('selected', c.checked);
    });
}
function clearSelection() {
    document.querySelectorAll('.book-check').forEach(c => { c.checked = false; c.closest('tr').classList.remove('selected'); });
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkBar').classList.remove('visible');
}
function confirmMassDelete() {
    const n = document.querySelectorAll('.book-check:checked').length;
    return confirm(`Hapus ${n} buku yang dipilih? Tindakan ini tidak dapat dibatalkan.`);
}

// ── Sort shortcut ──
function sortBy(col) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort_by', col);
    window.location = url.toString();
}

// ── Keyboard shortcut: N → new book ──
document.addEventListener('keydown', e => {
    if (e.key === 'n' && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT') {
        window.location = '{{ route("books.create") }}';
    }
});
</script>
@endpush