@extends('layouts.app')

@section('title', 'Sampah Buku')

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

    .admin-trash { font-family: 'IBM Plex Sans', sans-serif; }

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
        background: #fff7ed; color: var(--orange);
        border: 1px solid #fed7aa;
    }

    /* ── Stat cards ── */
    .stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    @media(max-width:700px) { .stat-row { grid-template-columns: 1fr 1fr; } }

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
    .stat-card.orange::before { background: var(--orange); }
    .stat-card.red::before    { background: var(--red); }
    .stat-card.green::before  { background: var(--green); }

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
    .btn-orange { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .btn-orange:hover { background: #ffedd5; color: #c2410c; }
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
        background: #fff7ed;
        border-bottom: 1px solid var(--border);
    }
    .admin-table th {
        padding: 10px 14px; text-align: left;
        font-family: 'IBM Plex Mono', monospace;
        font-size: .72rem; font-weight: 600; color: var(--muted);
        text-transform: uppercase; letter-spacing: .07em;
        white-space: nowrap;
    }
    .admin-table td {
        padding: 11px 14px; border-bottom: 1px solid var(--border);
        vertical-align: middle; font-size: .875rem; color: var(--text2);
    }
    .admin-table tbody tr:last-child td { border-bottom: none; }
    .admin-table tbody tr { transition: background .12s; }
    .admin-table tbody tr:hover { background: #fff7ed; }
    .admin-table tbody tr.selected { background: #fff7ed; }

    /* Book thumb */
    .book-thumb {
        width: 36px; height: 48px; border-radius: 5px;
        object-fit: cover; border: 1px solid var(--border);
        flex-shrink: 0; opacity: .75;
        filter: grayscale(30%);
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
    .book-author { font-size: .75rem; color: var(--muted); margin-top: 2px; }

    /* Category pill */
    .cat-pill {
        display: inline-block; padding: 2px 8px; border-radius: 5px;
        font-size: .72rem; font-weight: 600;
        background: #f5f3ff; color: #7c3aed;
        border: 1px solid #ddd6fe;
        font-family: 'IBM Plex Mono', monospace;
    }

    /* Deleted-at badge */
    .deleted-badge {
        font-family: 'IBM Plex Mono', monospace;
        font-size: .72rem; font-weight: 600;
        background: #fff1f2; color: var(--red);
        border: 1px solid #fecdd3;
        padding: 2px 8px; border-radius: 5px;
        white-space: nowrap;
    }
    .deleted-ago {
        font-size: .72rem; color: var(--muted); margin-top: 3px;
    }

    /* Row checkbox */
    .row-check {
        width: 15px; height: 15px; cursor: pointer;
        accent-color: var(--orange);
    }

    /* Action buttons in row */
    .row-action {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 6px;
        border: 1px solid var(--border); background: transparent;
        color: var(--muted); cursor: pointer; transition: all .15s;
        text-decoration: none; font-size: .8rem;
    }
    .row-action:hover.restore  { border-color: #bbf7d0; color: #15803d; background: #f0fdf4; }
    .row-action:hover.perm-del { border-color: #fecaca; color: #b91c1c; background: #fef2f2; }

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
    .pag-btn:hover { border-color: var(--orange); color: var(--orange); }
    .pag-btn.active { background: var(--orange); border-color: var(--orange); color: white; }
    .pag-btn.disabled { opacity: .35; pointer-events: none; }

    /* ── Bulk action bar ── */
    .bulk-bar {
        background: #fff7ed; border: 1px solid #fed7aa;
        border-radius: 10px; padding: 10px 16px;
        display: none; align-items: center; gap: 12px;
    }
    .bulk-bar.visible { display: flex; }
    .bulk-count { font-weight: 700; color: var(--orange); font-size: .875rem; }

    /* ── Empty ── */
    .empty-row td { text-align: center; padding: 60px 20px; color: var(--muted); }
    .empty-icon { font-size: 2.5rem; margin-bottom: 10px; opacity: .4; }

    /* ── Notice banner ── */
    .trash-notice {
        display: flex; align-items: flex-start; gap: 12px;
        background: #fff7ed; border: 1px solid #fed7aa;
        border-radius: 10px; padding: 14px 18px;
        font-size: .85rem; color: #92400e;
    }
    .trash-notice i { color: var(--orange); margin-top: 2px; flex-shrink: 0; }

    /* ── Scrollbar ── */
    .table-scroll { overflow-x: auto; }
    .table-scroll::-webkit-scrollbar { height: 4px; }
    .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }

    .admin-trash * { box-sizing: border-box; }
</style>
@endpush

@section('content')
@php
    $totalTrashed  = $book->total();
    $trashedToday  = \App\Models\Book::onlyTrashed()->whereDate('deleted_at', today())->count();
    $trashedThisWk = \App\Models\Book::onlyTrashed()->whereBetween('deleted_at', [now()->startOfWeek(), now()])->count();
@endphp

<div class="admin-trash space-y-4">

    {{-- ── Top Bar ── --}}
    <div class="top-bar">
        <div class="top-bar-title">
            <i class="fas fa-trash-alt" style="color:var(--orange);"></i>
            Sampah Buku
            <span class="badge">{{ number_format($totalTrashed) }} records</span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form method="POST" action="{{ route('books.empty-trash') }}"
                  onsubmit="return confirm('Hapus SEMUA buku di sampah secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-admin btn-red" {{ $totalTrashed === 0 ? 'disabled' : '' }}
                    style="{{ $totalTrashed === 0 ? 'opacity:.4;cursor:not-allowed;' : '' }}">
                    <i class="fas fa-fire text-xs"></i> Kosongkan Sampah
                </button>
            </form>
            <a href="{{ route('books.index') }}" class="btn-admin btn-muted">
                <i class="fas fa-arrow-left text-xs"></i> Kembali ke Buku
            </a>
        </div>
    </div>

    {{-- ── Warning Notice ── --}}
    <div class="trash-notice">
        <i class="fas fa-exclamation-triangle"></i>
        <span>
            Buku di sampah <strong>belum benar-benar dihapus</strong>. Anda bisa memulihkannya kapan saja.
            Hapus permanen hanya jika Anda yakin tidak akan membutuhkannya lagi.
        </span>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="stat-row">
        <div class="stat-card orange">
            <div class="stat-label">Total Sampah</div>
            <div class="stat-value" style="color:var(--orange);">{{ number_format($totalTrashed) }}</div>
            <div class="stat-sub">Buku dihapus</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Hari Ini</div>
            <div class="stat-value" style="color:var(--red);">{{ $trashedToday }}</div>
            <div class="stat-sub">Dihapus hari ini</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Minggu Ini</div>
            <div class="stat-value" style="color:var(--green);">{{ $trashedThisWk }}</div>
            <div class="stat-sub">7 hari terakhir</div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form method="GET" action="{{ route('books.trash') }}" id="trashFilterForm">
        <div class="filter-bar">
            <div class="search-input-wrap">
                <i class="fas fa-search" style="color:var(--muted);font-size:.8rem;"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul, penulis...">
            </div>

            <select name="sort_by" class="admin-select" onchange="document.getElementById('trashFilterForm').submit()">
                <option value="deleted_at" {{ request('sort_by','deleted_at') === 'deleted_at' ? 'selected' : '' }}>Terbaru Dihapus</option>
                <option value="name"       {{ request('sort_by') === 'name'       ? 'selected' : '' }}>Nama A–Z</option>
                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
            </select>

            <button type="submit" class="btn-admin btn-orange">
                <i class="fas fa-search text-xs"></i> Filter
            </button>

            @if(request()->hasAny(['search','sort_by']))
            <a href="{{ route('books.trash') }}" class="btn-admin btn-muted">
                <i class="fas fa-times text-xs"></i> Reset
            </a>
            @endif
        </div>
    </form>

    {{-- ── Bulk Action Bar ── --}}
    <div class="bulk-bar" id="bulkBar">
        <span class="bulk-count" id="bulkCount">0</span>
        <span style="color:var(--muted);font-size:.875rem;">item dipilih</span>
        <div style="height:16px;width:1px;background:var(--border);"></div>

        {{-- Bulk Restore --}}
        <form method="POST" action="{{ route('books.mass-restore') }}" id="massRestoreForm"
              onsubmit="return confirmMassRestore()">
            @csrf
            <div id="massRestoreInputs"></div>
            <button type="submit" class="btn-admin btn-green">
                <i class="fas fa-trash-restore text-xs"></i> Pulihkan Dipilih
            </button>
        </form>

        {{-- Bulk Permanent Delete --}}
        <form method="POST" action="{{ route('books.mass-force-delete') }}" id="massForceDeleteForm"
              onsubmit="return confirmMassPermanent()">
            @csrf
            @method('DELETE')
            <div id="massForceDeleteInputs"></div>
            <button type="submit" class="btn-admin btn-red">
                <i class="fas fa-trash text-xs"></i> Hapus Permanen
            </button>
        </form>

        <button type="button" onclick="clearSelection()" class="btn-admin btn-muted" style="margin-left:auto;">
            <i class="fas fa-times text-xs"></i> Batal
        </button>
    </div>

    {{-- ── Table ── --}}
    <div class="table-wrap">
        <div class="table-scroll">
            <table class="admin-table" id="trashTable">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" class="row-check" id="checkAll" onchange="toggleAll(this)">
                        </th>
                        <th style="min-width:280px;">BUKU</th>
                        <th>KATEGORI</th>
                        <th>STOK TERAKHIR</th>
                        <th>DIHAPUS PADA</th>
                        <th style="width:110px; text-align:right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($book as $b)
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
                                    <div class="book-name">{{ $b->name }}</div>
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
                            <span style="font-family:'IBM Plex Mono',monospace;font-size:.82rem;font-weight:600;color:var(--text2);">
                                {{ $b->stock }}
                            </span>
                            <span style="font-size:.72rem;color:var(--muted);margin-left:2px;">unit</span>
                        </td>
                        <td>
                            <div class="deleted-badge">
                                <i class="fas fa-clock mr-1" style="font-size:.65rem;"></i>
                                {{ $b->deleted_at->format('d M Y') }}
                            </div>
                            <div class="deleted-ago">{{ $b->deleted_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div style="display:flex;justify-content:flex-end;gap:4px;">

                                {{-- ✅ Restore — method PUT --}}
                                <form method="POST" action="{{ route('books.restore', $b->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="row-action restore" title="Pulihkan">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                </form>

                                {{-- ✅ Permanent Delete — method DELETE --}}
                                <form method="POST" action="{{ route('books.force-delete', $b->id) }}"
                                      onsubmit="return confirm('Hapus \'{{ addslashes($b->name) }}\' secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="row-action perm-del" title="Hapus Permanen">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6">
                            <div class="empty-icon">🗑️</div>
                            <p style="font-weight:600;color:var(--text2);margin-bottom:4px;">Sampah Kosong</p>
                            <p style="font-size:.85rem;">Tidak ada buku yang dihapus saat ini</p>
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

</div>
@endsection

@push('scripts')
<script>
function toggleAll(el) {
    document.querySelectorAll('.book-check').forEach(c => {
        c.checked = el.checked;
        c.closest('tr').classList.toggle('selected', el.checked);
    });
    updateBulk();
}

function updateBulk() {
    const checked = document.querySelectorAll('.book-check:checked');
    const bar     = document.getElementById('bulkBar');

    document.getElementById('bulkCount').textContent = checked.length;
    bar.classList.toggle('visible', checked.length > 0);

    // Sync hidden inputs untuk kedua form bulk
    ['massRestoreInputs', 'massForceDeleteInputs'].forEach(containerId => {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        checked.forEach(c => {
            const inp   = document.createElement('input');
            inp.type    = 'hidden';
            inp.name    = 'ids[]';
            inp.value   = c.value;
            container.appendChild(inp);
        });
    });

    const all = document.querySelectorAll('.book-check');
    const checkAll = document.getElementById('checkAll');
    checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
    checkAll.checked       = all.length > 0 && checked.length === all.length;

    document.querySelectorAll('.book-check').forEach(c => {
        c.closest('tr').classList.toggle('selected', c.checked);
    });
}

function clearSelection() {
    document.querySelectorAll('.book-check').forEach(c => {
        c.checked = false;
        c.closest('tr').classList.remove('selected');
    });
    const checkAll = document.getElementById('checkAll');
    checkAll.checked       = false;
    checkAll.indeterminate = false;
    document.getElementById('bulkBar').classList.remove('visible');
}

function confirmMassRestore() {
    const n = document.querySelectorAll('.book-check:checked').length;
    if (n === 0) { alert('Pilih minimal satu buku.'); return false; }
    return confirm(`Pulihkan ${n} buku yang dipilih?`);
}

function confirmMassPermanent() {
    const n = document.querySelectorAll('.book-check:checked').length;
    if (n === 0) { alert('Pilih minimal satu buku.'); return false; }
    return confirm(`Hapus ${n} buku secara PERMANEN? Tindakan ini tidak dapat dibatalkan.`);
}
</script>
@endpush