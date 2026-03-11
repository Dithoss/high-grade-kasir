@extends('layouts.app')

@section('title', 'Katalog Buku')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    :root {
        --ink: #1a1a2e;
        --rust: #e85d26;
        --sand: #f5f0e8;
        --sage: #4a7c59;
        --sky: #2d6a9f;
        --cream: #fdfaf4;
    }

    .books-page { font-family: 'DM Sans', sans-serif; background: var(--cream); min-height: 100vh; }

    /* ── Hero Banner ── */
    .hero-banner {
        background: var(--ink);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
        padding: 40px 48px;
    }
    .hero-banner::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 80% at 90% 50%, rgba(232,93,38,.25) 0%, transparent 60%),
            radial-gradient(ellipse 50% 60% at 10% 80%, rgba(45,106,159,.3) 0%, transparent 60%);
    }
    .hero-banner::after {
        content: '📚';
        position: absolute; right: 48px; top: 50%;
        transform: translateY(-50%);
        font-size: 96px;
        opacity: .12;
        pointer-events: none;
    }
    .hero-title {
        font-family: 'Fraunces', serif;
        font-size: 2.4rem; font-weight: 700;
        color: white; line-height: 1.15;
        position: relative; z-index: 1;
    }
    .hero-title span { color: #f5a623; }
    .hero-sub { color: rgba(255,255,255,.65); font-size: .95rem; margin-top: 8px; position: relative; z-index: 1; }

    /* ── Stats row ── */
    .stat-pill {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 14px; border-radius: 20px;
        background: rgba(255,255,255,.1); color: rgba(255,255,255,.85);
        font-size: .82rem; font-weight: 600;
        backdrop-filter: blur(8px);
    }
    .stat-pill .dot { width: 7px; height: 7px; border-radius: 50%; }

    /* ── Search bar ── */
    .search-wrap {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
        padding: 6px 6px 6px 20px;
        display: flex; align-items: center; gap: 8px;
        border: 2px solid transparent;
        transition: border-color .2s, box-shadow .2s;
    }
    .search-wrap:focus-within {
        border-color: var(--rust);
        box-shadow: 0 4px 24px rgba(232,93,38,.15);
    }
    .search-wrap input {
        flex: 1; border: none; outline: none;
        font-size: 1rem; font-family: 'DM Sans', sans-serif;
        background: transparent; color: var(--ink);
    }
    .search-wrap input::placeholder { color: #aaa; }
    .search-btn {
        background: var(--rust); color: white;
        border: none; border-radius: 12px;
        padding: 10px 24px; font-weight: 600;
        cursor: pointer; transition: all .2s; white-space: nowrap;
        font-family: 'DM Sans', sans-serif;
    }
    .search-btn:hover { background: #d44f1c; transform: translateY(-1px); }

    /* ── Filter chips ── */
    .filter-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 16px; border-radius: 20px;
        border: 1.5px solid #e5e7eb; background: white;
        font-size: .85rem; font-weight: 500; color: #6b7280;
        cursor: pointer; transition: all .18s; text-decoration: none;
        white-space: nowrap;
    }
    .filter-chip:hover, .filter-chip.active {
        border-color: var(--rust); color: var(--rust);
        background: rgba(232,93,38,.06);
    }
    .filter-chip.active { font-weight: 700; }

    /* ── Sort select ── */
    .sort-select {
        padding: 8px 16px; border-radius: 12px;
        border: 1.5px solid #e5e7eb; background: white;
        font-family: 'DM Sans', sans-serif; font-size: .85rem;
        color: var(--ink); cursor: pointer; outline: none;
        transition: border-color .2s;
    }
    .sort-select:focus { border-color: var(--rust); }

    /* ── Book Card ── */
    .book-card {
        background: white; border-radius: 16px;
        border: 1.5px solid #f0ece4;
        overflow: hidden; transition: all .25s;
        display: flex; flex-direction: column;
        position: relative;
        animation: fadeUp .3s ease-out both;
    }
    .book-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(26,26,46,.12);
        border-color: #e0d8cc;
    }
    .book-cover {
        aspect-ratio: 3/4;
        background: linear-gradient(135deg, #f5f0e8, #e8e0d0);
        overflow: hidden; position: relative;
    }
    .book-cover img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
    .book-card:hover .book-cover img { transform: scale(1.05); }
    .book-cover-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #f5f0e8, #ddd5c5);
    }
    .stock-badge {
        position: absolute; top: 10px; right: 10px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .72rem; font-weight: 700;
        backdrop-filter: blur(6px);
    }
    .stock-badge.available { background: rgba(74,124,89,.9); color: white; }
    .stock-badge.empty { background: rgba(185,28,28,.85); color: white; }
    .cat-badge {
        position: absolute; top: 10px; left: 10px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .72rem; font-weight: 600;
        background: rgba(26,26,46,.75); color: white;
        backdrop-filter: blur(6px);
    }
    .book-info { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; }
    .book-title {
        font-family: 'Fraunces', serif;
        font-weight: 600; font-size: .95rem;
        color: var(--ink); line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
        margin-bottom: 4px;
    }
    .book-author { font-size: .8rem; color: #9c9288; margin-bottom: 12px; }
    .book-actions { margin-top: auto; display: flex; gap: 8px; }
    .btn-borrow {
        flex: 1; padding: 8px 0; border-radius: 10px;
        background: var(--rust); color: white; border: none;
        font-weight: 600; font-size: .82rem; cursor: pointer;
        transition: all .2s; text-align: center;
        display: flex; align-items: center; justify-content: center; gap: 5px;
        text-decoration: none;
    }
    .btn-borrow:hover { background: #d44f1c; color: white; }
    .btn-borrow.disabled {
        background: #e5e7eb; color: #9ca3af; cursor: not-allowed;
        pointer-events: none;
    }
    .btn-borrow.preorder { background: var(--sky); }
    .btn-borrow.preorder:hover { background: #245a8a; color: white; }
    .btn-wishlist {
        width: 36px; height: 36px; border-radius: 10px;
        border: 1.5px solid #f0ece4; background: white;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .2s; color: #d1c4b8;
        text-decoration: none; flex-shrink: 0;
    }
    .btn-wishlist:hover, .btn-wishlist.active { border-color: #fca5a5; color: #ef4444; background: #fff5f5; }

    /* ── Preorder section ── */
    .preorder-card {
        background: white; border-radius: 16px;
        border: 1.5px solid #f0ece4; padding: 16px;
        display: flex; align-items: center; gap: 14px;
        transition: all .2s;
    }
    .preorder-card:hover { border-color: #ddd5c5; box-shadow: 0 4px 16px rgba(26,26,46,.07); }
    .preorder-cover {
        width: 52px; height: 70px; border-radius: 8px;
        overflow: hidden; flex-shrink: 0;
        background: linear-gradient(135deg, #f5f0e8, #e8e0d0);
    }
    .preorder-cover img { width: 100%; height: 100%; object-fit: cover; }
    .po-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .72rem; font-weight: 700;
    }
    .po-badge.pending   { background: #fef3c7; color: #92400e; }
    .po-badge.ready     { background: #d1fae5; color: #065f46; }
    .po-badge.cancelled { background: #fee2e2; color: #991b1b; }
    .po-badge.confirmed { background: #dbeafe; color: #1e40af; }

    /* ── Pagination ── */
    .pagination-wrap { display: flex; justify-content: center; gap: 4px; flex-wrap: wrap; }
    .page-btn {
        min-width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        border: 1.5px solid #e5e7eb; background: white;
        font-size: .85rem; font-weight: 500; color: #6b7280;
        text-decoration: none; transition: all .18s;
    }
    .page-btn:hover { border-color: var(--rust); color: var(--rust); }
    .page-btn.active { background: var(--rust); border-color: var(--rust); color: white; font-weight: 700; }
    .page-btn.disabled { opacity: .4; pointer-events: none; }

    /* ── Empty state ── */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 64px; margin-bottom: 16px; opacity: .4; }
    .empty-title { font-family: 'Fraunces', serif; font-size: 1.4rem; color: var(--ink); margin-bottom: 8px; }
    .empty-sub { color: #9c9288; font-size: .9rem; }

    /* ── Notification toast ── */
    .po-notify {
        background: linear-gradient(135deg, #065f46, #047857);
        color: white; border-radius: 16px; padding: 16px 20px;
        display: flex; align-items: center; gap: 14px;
        animation: slideInDown .4s ease-out;
    }
    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .po-notify-icon {
        width: 44px; height: 44px; background: rgba(255,255,255,.2);
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 20px;
    }

    /* ── Tab toggle ── */
    .tab-btn {
        padding: 8px 20px; border-radius: 12px; font-weight: 600;
        font-size: .9rem; cursor: pointer; transition: all .2s;
        border: 1.5px solid transparent; background: transparent;
        font-family: 'DM Sans', sans-serif;
    }
    .tab-btn.active { background: var(--ink); color: white; }
    .tab-btn:not(.active) { color: #6b7280; border-color: #e5e7eb; }
    .tab-btn:not(.active):hover { border-color: var(--rust); color: var(--rust); }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .book-card:nth-child(1)  { animation-delay: .03s; }
    .book-card:nth-child(2)  { animation-delay: .06s; }
    .book-card:nth-child(3)  { animation-delay: .09s; }
    .book-card:nth-child(4)  { animation-delay: .12s; }
    .book-card:nth-child(5)  { animation-delay: .15s; }
    .book-card:nth-child(6)  { animation-delay: .18s; }
    .book-card:nth-child(7)  { animation-delay: .21s; }
    .book-card:nth-child(8)  { animation-delay: .24s; }
</style>
@endpush

@section('content')
@php
    $user = Auth::user();
    $hasActiveFine = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
        ->whereIn('status', ['unpaid', 'pending_confirmation'])->exists();

    $readyPreorders = \App\Models\Preorder::where('user_id', $user->id)
        ->where('status', 'ready')
        ->with('book')
        ->get();

    $myPreorders = \App\Models\Preorder::where('user_id', $user->id)
        ->whereIn('status', ['pending', 'ready'])
        ->with('book')
        ->latest()
        ->get();

    $wishlistIds = $user->wishlists()->pluck('book_id')->toArray();
@endphp

<div class="books-page space-y-6">

    {{-- ── Notifikasi Preorder Siap ── --}}
    @foreach($readyPreorders as $rpo)
    <div class="po-notify">
        <div class="po-notify-icon">🎉</div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-base">Preorder Siap Diambil!</p>
            <p class="text-sm text-green-100 truncate">
                <strong>{{ $rpo->book->name }}</strong> sudah tersedia — segera konfirmasi sebelum kedaluwarsa.
            </p>
        </div>
        <a href="{{ route('preorders.confirm', $rpo->id) }}"
           class="flex-shrink-0 bg-white text-green-700 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 transition-all whitespace-nowrap">
            Ambil Sekarang →
        </a>
    </div>
    @endforeach

    {{-- ── Hero ── --}}
    <div class="hero-banner">
        <div class="relative z-10">
            <p class="text-xs font-bold tracking-widest text-orange-400 uppercase mb-2">Perpustakaan Digital</p>
            <h1 class="hero-title">Temukan Buku<br><span>Impianmu</span> di Sini</h1>
            <div class="flex items-center gap-3 mt-4 flex-wrap">
                <span class="stat-pill"><span class="dot bg-orange-400"></span>{{ $totalBooks }} Koleksi</span>
                <span class="stat-pill"><span class="dot bg-green-400"></span>{{ $availableBooks }} Tersedia</span>
                <span class="stat-pill"><span class="dot bg-blue-400"></span>{{ $totalCategories }} Kategori</span>
            </div>
        </div>
    </div>

    {{-- ── Tab: Buku / Preorder Saya ── --}}
    <div class="flex items-center gap-3 flex-wrap">
        <button class="tab-btn active" id="tabBooks" onclick="switchTab('books')">
            <i class="fas fa-book mr-1.5"></i>Katalog Buku
        </button>
        <button class="tab-btn {{ $readyPreorders->count() > 0 ? 'ring-2 ring-green-400' : '' }}"
                id="tabPreorder" onclick="switchTab('preorder')">
            <i class="fas fa-clock mr-1.5"></i>Preorder Saya
            @if($myPreorders->count() > 0)
                <span class="ml-1.5 bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $myPreorders->count() }}
                </span>
            @endif
        </button>
    </div>

    {{-- ════════════ TAB: BUKU ════════════ --}}
    <div id="panelBooks">
        <form action="{{ route('books.catalog') }}" method="GET" id="filterForm">
            <div class="space-y-4">
                {{-- Search --}}
                <div class="search-wrap">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari judul, penulis...">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                </div>

                {{-- Filters row --}}
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-semibold text-gray-500">Kategori:</span>
                    <a href="{{ route('books.catalog', array_merge(request()->except('category','page'), [])) }}"
                       class="filter-chip {{ !request('category') ? 'active' : '' }}">Semua</a>
                    @foreach($categories as $cat)
                    <a href="{{ route('books.catalog', array_merge(request()->except('category','page'), ['category' => $cat->id])) }}"
                       class="filter-chip {{ request('category') == $cat->id ? 'active' : '' }}">
                        {{ $cat->name }}
                    </a>
                    @endforeach

                    <div class="ml-auto flex items-center gap-2 flex-wrap">
                        {{-- Availability --}}
                        <select name="available" class="sort-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Stok</option>
                            <option value="1" {{ request('available') == '1' ? 'selected' : '' }}>Tersedia</option>
                            <option value="0" {{ request('available') == '0' ? 'selected' : '' }}>Habis</option>
                        </select>

                        {{-- Sort --}}
                        <select name="sort" class="sort-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="latest"    {{ request('sort','latest') === 'latest'    ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest"    {{ request('sort') === 'oldest'    ? 'selected' : '' }}>Terlama</option>
                            <option value="name_asc"  {{ request('sort') === 'name_asc'  ? 'selected' : '' }}>Nama A–Z</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nama Z–A</option>
                        </select>

                        @if(request()->hasAny(['search','category','available','sort']))
                        <a href="{{ route('books.catalog') }}" class="filter-chip text-rose-500 border-rose-200">
                            <i class="fas fa-times text-xs"></i> Reset
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Result info --}}
        <div class="flex items-center justify-between mt-3">
            <p class="text-sm text-gray-500">
                Menampilkan <strong class="text-gray-800">{{ $books->total() }}</strong> buku
                @if(request('search')) untuk "<strong>{{ request('search') }}</strong>" @endif
            </p>
        </div>

        {{-- Book Grid --}}
        @if($books->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mt-4">
            @foreach($books as $b)
            @php
                $inWishlist  = in_array($b->id, $wishlistIds);
                $canBorrow   = $b->stock > 0 && !$hasActiveFine;
                $alreadyPO   = \App\Models\Preorder::where('user_id', $user->id)
                                  ->where('book_id', $b->id)
                                  ->whereIn('status', ['pending','ready'])
                                  ->exists();
            @endphp
            <div class="book-card">
                <div class="book-cover">
                    @if($b->image)
                        <img src="{{ asset('storage/'.$b->image) }}" alt="{{ $b->name }}" loading="lazy">
                    @else
                        <div class="book-cover-placeholder">
                            <i class="fas fa-book text-4xl text-gray-300"></i>
                        </div>
                    @endif
                    <span class="stock-badge {{ $b->stock > 0 ? 'available' : 'empty' }}">
                        {{ $b->stock > 0 ? 'Tersedia' : 'Habis' }}
                    </span>
                    @if($b->category)
                        <span class="cat-badge">{{ Str::limit($b->category->name, 10) }}</span>
                    @endif
                </div>
                <div class="book-info">
                    <a href="{{ route('books.show', $b->slug) }}">
                        <p class="book-title">{{ $b->name }}</p>
                    </a>
                    <p class="book-author">{{ $b->writer ?? '—' }}</p>
                    <div class="book-actions">
                        @if($canBorrow)
                            <a href="{{ route('transactions.create', ['book_id' => $b->id]) }}"
                               class="btn-borrow">
                                <i class="fas fa-plus text-xs"></i> Pinjam
                            </a>
                        @elseif($b->stock <= 0 && !$hasActiveFine && !$alreadyPO)
                            <button onclick="openPreorderModal('{{ $b->id }}','{{ addslashes($b->name) }}')"
                                    class="btn-borrow preorder">
                                <i class="fas fa-clock text-xs"></i> Preorder
                            </button>
                        @elseif($alreadyPO)
                            <span class="btn-borrow disabled" style="font-size:.75rem;">
                                <i class="fas fa-check text-xs"></i> Sudah PO
                            </span>
                        @else
                            <span class="btn-borrow disabled">
                                {{ $hasActiveFine ? 'Ada Denda' : 'Habis' }}
                            </span>
                        @endif
                        <a href="{{ route('wishlist.toggle', $b->slug) }}"
                           class="btn-wishlist {{ $inWishlist ? 'active' : '' }}"
                           title="{{ $inWishlist ? 'Hapus dari wishlist' : 'Tambah ke wishlist' }}">
                            <i class="fas fa-heart text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($books->hasPages())
        <div class="pagination-wrap mt-8">
            @if($books->onFirstPage())
                <span class="page-btn disabled"><i class="fas fa-chevron-left text-xs"></i></span>
            @else
                <a href="{{ $books->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left text-xs"></i></a>
            @endif

            @foreach($books->getUrlRange(1, $books->lastPage()) as $page => $url)
                @if($page == $books->currentPage())
                    <span class="page-btn active">{{ $page }}</span>
                @elseif($page == 1 || $page == $books->lastPage() || abs($page - $books->currentPage()) <= 2)
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @elseif(abs($page - $books->currentPage()) == 3)
                    <span class="page-btn disabled">…</span>
                @endif
            @endforeach

            @if($books->hasMorePages())
                <a href="{{ $books->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right text-xs"></i></a>
            @else
                <span class="page-btn disabled"><i class="fas fa-chevron-right text-xs"></i></span>
            @endif
        </div>
        @endif

        @else
        <div class="empty-state mt-4">
            <div class="empty-icon">🔍</div>
            <p class="empty-title">Buku Tidak Ditemukan</p>
            <p class="empty-sub">Coba kata kunci lain atau hapus filter</p>
            <a href="{{ route('books.catalog') }}" class="inline-block mt-4 px-6 py-2.5 bg-orange-500 text-white rounded-xl font-semibold text-sm hover:bg-orange-600 transition-all">
                Lihat Semua Buku
            </a>
        </div>
        @endif
    </div>

    {{-- ════════════ TAB: PREORDER ════════════ --}}
    <div id="panelPreorder" class="hidden">
        <div class="flex items-center justify-between mb-5">
            <div style="font-family:'Fraunces',serif; font-size:1.4rem; font-weight:700; color:var(--ink); display:flex; align-items:center; gap:10px;">
                <span style="width:10px;height:10px;border-radius:50%;background:var(--rust);display:inline-block;"></span>
                Preorder Aktif Saya
            </div>
            <span class="text-sm text-gray-400">{{ $myPreorders->count() }} antrian</span>
        </div>

        @if($myPreorders->count() > 0)
        <div class="space-y-3">
            @foreach($myPreorders as $po)
            @php
                $statusLabel = ['pending'=>'Menunggu','ready'=>'Siap Diambil','cancelled'=>'Dibatalkan','confirmed'=>'Dikonfirmasi'];
                $statusClass = ['pending'=>'pending','ready'=>'ready','cancelled'=>'cancelled','confirmed'=>'confirmed'];
            @endphp
            <div class="preorder-card">
                <div class="preorder-cover">
                    @if($po->book?->image)
                        <img src="{{ asset('storage/'.$po->book->image) }}" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-50 to-orange-100">
                            <i class="fas fa-book text-orange-300"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $po->book?->name ?? 'Buku tidak tersedia' }}</p>
                        <span class="po-badge {{ $statusClass[$po->status] ?? 'pending' }} flex-shrink-0">
                            @if($po->status==='ready') 🎉
                            @elseif($po->status==='pending') ⏳
                            @else ✓ @endif
                            {{ $statusLabel[$po->status] ?? $po->status }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mb-2">{{ $po->book?->writer ?? '—' }}</p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span><i class="fas fa-calendar-alt mr-1 text-gray-300"></i>
                            Tgl Harap: {{ \Carbon\Carbon::parse($po->expected_borrow_date)->format('d M Y') }}
                        </span>
                        @if(isset($po->queue_position) && $po->status === 'pending')
                        <span><i class="fas fa-list-ol mr-1 text-gray-300"></i>
                            Antrian ke-{{ $po->queue_position }}
                        </span>
                        @endif
                    </div>
                    @if($po->notes)
                    <p class="text-xs text-gray-400 mt-1 italic truncate">
                        <i class="fas fa-sticky-note mr-1"></i>{{ $po->notes }}
                    </p>
                    @endif
                </div>
                <div class="flex flex-col gap-2 flex-shrink-0">
                    @if($po->status === 'ready')
                    <a href="{{ route('preorders.confirm', $po->id) }}"
                       class="px-4 py-2 bg-green-600 text-white text-xs font-bold rounded-xl hover:bg-green-700 transition-all whitespace-nowrap text-center">
                        <i class="fas fa-check mr-1"></i>Ambil
                    </a>
                    @elseif($po->status === 'pending')
                    <button onclick="openEditPreorder('{{ $po->id }}','{{ $po->expected_borrow_date }}','{{ addslashes($po->notes ?? '') }}')"
                            class="px-4 py-2 bg-blue-50 text-blue-700 text-xs font-bold rounded-xl hover:bg-blue-100 transition-all border border-blue-200 whitespace-nowrap">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    @endif
                    <form method="POST" action="{{ route('preorders.cancel', $po->id) }}"
                          onsubmit="return confirm('Batalkan preorder ini?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-xl hover:bg-red-100 transition-all border border-red-100">
                            <i class="fas fa-times mr-1"></i>Batal
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p class="empty-title">Belum Ada Preorder</p>
            <p class="empty-sub">Preorder buku yang sedang habis stok</p>
            <button onclick="switchTab('books')"
                    class="inline-block mt-4 px-6 py-2.5 bg-orange-500 text-white rounded-xl font-semibold text-sm hover:bg-orange-600 transition-all">
                Lihat Katalog
            </button>
        </div>
        @endif
    </div>
</div>

{{-- ════════ MODAL PREORDER BARU ════════ --}}
<div id="preorderModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-lg text-gray-900" style="font-family:'Fraunces',serif;">Daftar Preorder</h3>
                <p id="poBookName" class="text-sm text-gray-500 mt-0.5"></p>
            </div>
            <button onclick="closePreorderModal()" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                <i class="fas fa-times text-gray-500 text-sm"></i>
            </button>
        </div>
        <form id="preorderForm" method="POST" action="{{ route('preorders.store') }}">
            @csrf
            <input type="hidden" name="book_id" id="poBookId">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Harap Pinjam</label>
                    <input type="date" name="expected_borrow_date"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           value="{{ now()->addDays(7)->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm font-medium focus:border-orange-400 focus:outline-none transition-all" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan <span class="font-normal text-gray-400">(opsional)</span></label>
                    <textarea name="notes" rows="2" placeholder="Catatan untuk petugas..."
                              class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm resize-none focus:border-orange-400 focus:outline-none transition-all"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="closePreorderModal()"
                        class="flex-1 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700">
                    <i class="fas fa-clock mr-1.5"></i>Daftar Preorder
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════ MODAL EDIT PREORDER ════════ --}}
<div id="editPreorderModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-lg text-gray-900" style="font-family:'Fraunces',serif;">Edit Preorder</h3>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                <i class="fas fa-times text-gray-500 text-sm"></i>
            </button>
        </div>
        <form id="editPreorderForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Harap Pinjam</label>
                    <input type="date" name="expected_borrow_date" id="editPoDate"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm font-medium focus:border-blue-400 focus:outline-none transition-all" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan</label>
                    <textarea name="notes" id="editPoNotes" rows="2"
                              class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm resize-none focus:border-blue-400 focus:outline-none transition-all"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="closeEditModal()"
                        class="flex-1 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700">
                    <i class="fas fa-save mr-1.5"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(tab) {
    const isBooks = tab === 'books';
    document.getElementById('panelBooks').classList.toggle('hidden', !isBooks);
    document.getElementById('panelPreorder').classList.toggle('hidden', isBooks);
    document.getElementById('tabBooks').classList.toggle('active', isBooks);
    document.getElementById('tabPreorder').classList.toggle('active', !isBooks);
}
function openPreorderModal(bookId, bookName) {
    document.getElementById('poBookId').value = bookId;
    document.getElementById('poBookName').textContent = bookName;
    const m = document.getElementById('preorderModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closePreorderModal() {
    const m = document.getElementById('preorderModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function openEditPreorder(id, date, notes) {
    document.getElementById('editPreorderForm').action = `/preorders/${id}`;
    document.getElementById('editPoDate').value  = date;
    document.getElementById('editPoNotes').value = notes;
    const m = document.getElementById('editPreorderModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeEditModal() {
    const m = document.getElementById('editPreorderModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
['preorderModal','editPreorderModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) { this.classList.add('hidden'); this.classList.remove('flex'); }
    });
});
if (window.location.hash === '#preorder') switchTab('preorder');
</script>
@endpush