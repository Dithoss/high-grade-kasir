@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

@php
    use App\Models\Book;
    use App\Models\Category;
    use App\Models\Fine;
    use App\Models\Transaction;
    use App\Models\User;
    use App\Models\Preorder;
    use App\Models\Algorithm;
    use Carbon\Carbon;

    // === CORE STATS ===
    $totalBooks        = Book::count();
    $totalCategories   = Category::count();
    $totalStock        = Book::sum('stock');
    $totalUsers        = User::count();

    // === TRANSACTION STATS ===
    $activeTransactions  = Transaction::whereNull('returned_at')->where('status', 'borrowed')->count();
    $returnedThisMonth   = Transaction::whereNotNull('returned_at')
        ->whereMonth('returned_at', now()->month)
        ->whereYear('returned_at', now()->year)->count();
    $totalTransactions   = Transaction::count();
    $overdueTransactions = Transaction::where('status', 'borrowed')
        ->whereNull('returned_at')->where('due_at', '<', now())->count();

    // === FINE / REVENUE STATS ===
    $unpaidFines       = Fine::where('status', 'unpaid')->sum('amount');
    $totalRevenue      = Fine::where('status', 'paid')->sum('amount');
    $revenueFromLost   = Fine::where('status', 'paid')->where('type', 'lost')->sum('amount');
    $revenueFromLate   = Fine::where('status', 'paid')->where('type', 'late')->sum('amount');
    $revenueFromBroken = Fine::where('status', 'paid')->where('type', 'broken')->sum('amount');
    $revenueThisMonth  = Fine::where('status', 'paid')
        ->whereMonth('paid_at', now()->month)
        ->whereYear('paid_at', now()->year)->sum('amount');
    $pendingConfirmation = Fine::where('status', 'pending_confirmation')->count();

    // === CHART: Revenue + Transactions per 6 months ===
    $revenueLabels     = [];
    $revenueValues     = [];
    $transactionCounts = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $revenueLabels[]     = $month->translatedFormat('M Y');
        $revenueValues[]     = (int) Fine::where('status', 'paid')
            ->whereMonth('paid_at', $month->month)
            ->whereYear('paid_at', $month->year)->sum('amount');
        $transactionCounts[] = (int) Transaction::whereMonth('borrowed_at', $month->month)
            ->whereYear('borrowed_at', $month->year)->count();
    }

    // === ALGORITHM: Action distribution ===
    $actionStats = Algorithm::selectRaw('action, count(*) as total')
        ->groupBy('action')->orderByDesc('total')->get()
        ->mapWithKeys(fn($r) => [$r->action => (int)$r->total]);

    // === ALGORITHM: Hot books by total activity ===
    $hotBooks = Algorithm::selectRaw('book_id, count(*) as total')
        ->groupBy('book_id')->orderByDesc('total')->take(8)->get();
    $hotBookIds = $hotBooks->pluck('book_id');
    $hotBooksInfo = Book::whereIn('id', $hotBookIds)->get()->keyBy('id');

    // === ALGORITHM: Hourly activity (last 30 days) ===
    $hourlyRaw = Algorithm::selectRaw('HOUR(created_at) as hour, count(*) as total')
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('hour')->pluck('total', 'hour')->toArray();
    $hourlyLabels = [];
    $hourlyValues = [];
    for ($h = 0; $h < 24; $h++) {
        $hourlyLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        $hourlyValues[] = (int)($hourlyRaw[$h] ?? 0);
    }

    // === ALGORITHM: Top categories by interest ===
    $topCategoriesData = \DB::table('algorithms')
        ->join('books', 'algorithms.book_id', '=', 'books.id')
        ->join('categories', 'books.category_id', '=', 'categories.id')
        ->selectRaw('categories.name as cat_name, count(*) as total')
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('total')->take(6)->get();

    // === ALGORITHM: Daily activity last 14 days ===
    $dailyLabels = [];
    $dailyValues = [];
    for ($i = 13; $i >= 0; $i--) {
        $day = now()->subDays($i);
        $dailyLabels[] = $day->format('d M');
        $dailyValues[] = (int) Algorithm::whereDate('created_at', $day->toDateString())->count();
    }

    // === RECENT TRANSACTIONS ===
    $recentTransactions = Transaction::with(['user', 'items.book'])
        ->latest()->take(6)->get();

    // === TOP BORROWED BOOKS (via TransactionItem) ===
    $topBooks = Book::select('books.*')
        ->selectSub(function ($q) {
            $q->from('transaction_items')->selectRaw('count(*)')
              ->whereColumn('transaction_items.book_id', 'books.id');
        }, 'transactions_count')
        ->orderByDesc('transactions_count')->take(5)->get();

    $pendingPreorders = class_exists(\App\Models\Preorder::class)
        ? Preorder::whereIn('status', ['waiting'])->count() : 0;

    $actionMeta = [
        'view'     => ['label' => 'Lihat',    'color' => '#2563eb', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        'borrow'   => ['label' => 'Pinjam',   'color' => '#059669', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        'wishlist' => ['label' => 'Wishlist', 'color' => '#db2777', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        'search'   => ['label' => 'Cari',     'color' => '#d97706', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
        'return'   => ['label' => 'Kembali',  'color' => '#0891b2', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
        'review'   => ['label' => 'Ulasan',   'color' => '#7c3aed', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ];
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap');

:root {
    --bg:      #f0f2f7;
    --surface: #ffffff;
    --border:  #e4e7ef;
    --ink:     #111827;
    --ink-2:   #374151;
    --muted:   #9ca3af;
    --blue:    #2563eb;
    --violet:  #7c3aed;
    --green:   #059669;
    --amber:   #d97706;
    --red:     #dc2626;
    --teal:    #0891b2;
    --pink:    #db2777;
}
* { box-sizing: border-box; }

.db {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    padding: 1.75rem 1.5rem;
    color: var(--ink);
}

/* HEADER */
.db-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.db-header h1 { font-family: 'Syne', sans-serif; font-size: clamp(1.5rem, 3vw, 2.1rem); font-weight: 800; margin: 0 0 .2rem; line-height: 1.1; }
.db-header p { color: var(--muted); font-size: .875rem; margin: 0; }
.db-date { font-family: 'Syne', sans-serif; font-size: .71rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; background: var(--ink); color: #fff; padding: .4rem 1rem; border-radius: 999px; white-space: nowrap; }

/* STAT CARDS */
.stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: .8rem; margin-bottom: .8rem; }
.sc { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem 1.3rem; position: relative; overflow: hidden; transition: transform .17s, box-shadow .17s; }
.sc:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.07); }
.sc::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--c, var(--blue)); border-radius: 14px 14px 0 0; }
.sc-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; margin-bottom: .8rem; background: color-mix(in srgb, var(--c,var(--blue)) 12%, white); }
.sc-icon svg { width: 17px; height: 17px; color: var(--c, var(--blue)); }
.sc-label { font-size: .68rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .07em; margin-bottom: .22rem; }
.sc-value { font-family: 'Syne', sans-serif; font-size: 1.65rem; font-weight: 800; line-height: 1; }
.sc-value.md { font-size: 1.15rem; }
.sc-sub { font-size: .68rem; color: var(--muted); margin-top: .28rem; }

/* REVENUE PANEL */
.rev-panel { background: var(--ink); border-radius: 16px; padding: 1.6rem 1.8rem; color: #fff; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: .8rem; position: relative; overflow: hidden; }
.rev-panel::before { content: ''; position: absolute; width: 320px; height: 320px; background: radial-gradient(circle, rgba(99,102,241,.3) 0%, transparent 70%); top: -120px; right: -80px; pointer-events: none; }
.rev-panel::after  { content: ''; position: absolute; width: 200px; height: 200px; background: radial-gradient(circle, rgba(219,39,119,.2) 0%, transparent 70%); bottom: -60px; left: 35%; pointer-events: none; }
.rev-main { position: relative; z-index: 1; }
.rev-lbl  { font-size: .67rem; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.42); margin-bottom: .28rem; }
.rev-big  { font-family: 'Syne', sans-serif; font-size: clamp(1.5rem,3vw,2.2rem); font-weight: 800; line-height: 1; }
.rev-sm   { font-family: 'Syne', sans-serif; font-size: 1.35rem; font-weight: 700; }
.rev-dim  { font-size: .73rem; color: rgba(255,255,255,.38); margin-top: .2rem; }
.rev-sep  { border: none; border-top: 1px solid rgba(255,255,255,.1); margin: 1rem 0; }
.rev-breakdown { position: relative; z-index: 1; display: flex; flex-direction: column; gap: .5rem; justify-content: center; }
.rev-item { display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,.07); border-radius: 9px; padding: .5rem .85rem; }
.rev-item-l { display: flex; align-items: center; gap: .45rem; font-size: .79rem; color: rgba(255,255,255,.68); }
.rev-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.rev-amt { font-family: 'Syne', sans-serif; font-size: .82rem; font-weight: 700; }

/* SECTION CARD */
.sec { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 1.3rem 1.4rem; margin-bottom: .8rem; }
.sec-title { font-family: 'Syne', sans-serif; font-size: .92rem; font-weight: 700; color: var(--ink); margin: 0 0 1.1rem; display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; }
.sec-title .pip { width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.sec-title .sub-note { font-size: .68rem; color: var(--muted); font-weight: 400; font-family: 'Inter', sans-serif; }

/* GRID LAYOUTS */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; margin-bottom: .8rem; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .8rem; margin-bottom: .8rem; }
@media (max-width: 960px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } .rev-panel { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .stat-row { grid-template-columns: 1fr 1fr; } }

/* RANK LIST */
.rank-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .65rem; }
.rank-item { display: flex; align-items: center; gap: .7rem; }
.rank-n { font-family: 'Syne', sans-serif; font-size: .7rem; font-weight: 800; color: var(--muted); width: 18px; text-align: center; flex-shrink: 0; }
.rank-n.gold { color: #f59e0b; }
.rank-bar-w { flex: 1; }
.rank-bar-top { display: flex; justify-content: space-between; font-size: .76rem; margin-bottom: 3px; color: var(--ink-2); }
.rank-bar-top .cnt { font-family: 'Syne', sans-serif; font-weight: 700; font-size: .76rem; }
.rank-track { height: 5px; background: #f0f0f0; border-radius: 999px; overflow: hidden; }
.rank-fill { height: 100%; border-radius: 999px; }

/* TABLE */
.dt-wrap { overflow-x: auto; }
table.dt { width: 100%; border-collapse: collapse; font-size: .8rem; }
table.dt th { text-align: left; padding: .55rem .9rem; background: #f9fafb; font-size: .67rem; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); font-weight: 600; border-bottom: 1px solid var(--border); }
table.dt td { padding: .65rem .9rem; border-bottom: 1px solid #f3f4f6; color: var(--ink-2); vertical-align: middle; }
table.dt tr:last-child td { border-bottom: none; }
table.dt tr:hover td { background: #fafbfd; }
.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: .67rem; font-weight: 600; white-space: nowrap; }
.b-blue  { background: #dbeafe; color: #1d4ed8; }
.b-green { background: #d1fae5; color: #065f46; }
.b-red   { background: #fee2e2; color: #991b1b; }

/* QUICK ACTIONS */
.qa-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: .6rem; }
.qa { display: flex; flex-direction: column; align-items: center; gap: .45rem; padding: 1rem; background: #f9fafb; border: 1.5px solid var(--border); border-radius: 11px; text-decoration: none; color: var(--ink); font-size: .78rem; font-weight: 500; transition: all .16s; text-align: center; }
.qa:hover { border-color: var(--blue); background: #eff6ff; color: var(--blue); transform: translateY(-2px); box-shadow: 0 4px 14px rgba(37,99,235,.1); }
.qa svg { width: 19px; height: 19px; }

/* ALERT */
.alert { display: flex; align-items: center; gap: .6rem; padding: .75rem 1rem; border-radius: 9px; margin-bottom: 1.2rem; font-size: .84rem; }
.alert-ok { background: #f0fdf4; border-left: 4px solid #22c55e; color: #15803d; }

/* ══════════════════════════════════════════════════════
   ACTION DISTRIBUTION — NEW DESIGN
   Horizontal segmented bar + animated cards
══════════════════════════════════════════════════════ */
.act-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.3rem 1.4rem;
    overflow: hidden;
}

/* Segmented bar */
.act-bar-wrap { margin: .85rem 0 1.2rem; }
.act-bar {
    display: flex;
    height: 10px;
    border-radius: 999px;
    overflow: hidden;
    gap: 2px;
    background: #f3f4f6;
}
.act-bar-seg {
    height: 100%;
    border-radius: 999px;
    transition: width 1.1s cubic-bezier(0.22,1,0.36,1);
    width: 0;
    position: relative;
    overflow: hidden;
}
.act-bar-seg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.35) 50%, transparent 100%);
    transform: translateX(-100%);
    animation: shimmer 2.5s ease-in-out infinite;
}
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    60%  { transform: translateX(100%); }
    100% { transform: translateX(100%); }
}

/* Action cards grid */
.act-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    gap: .55rem;
}
.act-card {
    position: relative;
    border-radius: 11px;
    padding: .85rem 1rem;
    border: 1.5px solid transparent;
    cursor: default;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.act-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,.09);
}
.act-card::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: .07;
    background: var(--ac);
    transition: opacity .18s;
}
.act-card:hover::before { opacity: .13; }

/* Decorative circle */
.act-card-circle {
    position: absolute;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: var(--ac);
    opacity: .06;
    bottom: -24px; right: -18px;
    transition: transform .18s;
}
.act-card:hover .act-card-circle { transform: scale(1.2); }

.act-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .55rem;
    position: relative;
    z-index: 1;
}
.act-card-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: color-mix(in srgb, var(--ac) 14%, white);
}
.act-card-icon svg {
    width: 14px; height: 14px;
    color: var(--ac);
}
.act-card-pct {
    font-family: 'Syne', sans-serif;
    font-size: .7rem;
    font-weight: 700;
    color: var(--ac);
    background: color-mix(in srgb, var(--ac) 12%, white);
    padding: 2px 7px;
    border-radius: 999px;
}
.act-card-count {
    font-family: 'Syne', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--ink);
    line-height: 1;
    margin-bottom: .18rem;
    position: relative;
    z-index: 1;
    display: block;
}
.act-card-label {
    font-size: .72rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    position: relative;
    z-index: 1;
}
/* Mini spark line */
.act-card-bar {
    margin-top: .55rem;
    height: 3px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--ac) 15%, white);
    position: relative;
    z-index: 1;
    overflow: hidden;
}
.act-card-bar-fill {
    height: 100%;
    border-radius: 999px;
    background: var(--ac);
    width: 0;
    transition: width 1.3s cubic-bezier(0.22,1,0.36,1);
}

/* Total row */
.act-total-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: .9rem;
    border-top: 1px solid var(--border);
    font-size: .8rem;
    color: var(--muted);
}
.act-total-row strong {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 800;
    color: var(--ink);
}
</style>

<div class="db">

@if(session('success'))
<div class="alert alert-ok">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- HEADER --}}
<div class="db-header">
    <div>
        <h1>Halo, {{ auth()->user()->name }} 👋</h1>
        <p>Ringkasan aktivitas perpustakaan hari ini</p>
    </div>
    <span class="db-date">{{ now()->translatedFormat('l, d F Y') }}</span>
</div>

{{-- PRIMARY STATS --}}
<div class="stat-row">
    <div class="sc" style="--c:var(--blue)">
        <div class="sc-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>
        <div class="sc-label">Total Buku</div>
        <div class="sc-value">{{ number_format($totalBooks) }}</div>
        <div class="sc-sub">{{ number_format($totalStock) }} unit stok</div>
    </div>
    <div class="sc" style="--c:var(--amber)">
        <div class="sc-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="sc-label">Sedang Dipinjam</div>
        <div class="sc-value">{{ number_format($activeTransactions) }}</div>
        <div class="sc-sub">{{ $overdueTransactions }} terlambat</div>
    </div>
    <div class="sc" style="--c:var(--green)">
        <div class="sc-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="sc-label">Kembali Bulan Ini</div>
        <div class="sc-value">{{ number_format($returnedThisMonth) }}</div>
        <div class="sc-sub">{{ number_format($totalTransactions) }} total transaksi</div>
    </div>
    <div class="sc" style="--c:var(--violet)">
        <div class="sc-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        <div class="sc-label">Total Pengguna</div>
        <div class="sc-value">{{ number_format($totalUsers) }}</div>
        <div class="sc-sub">{{ $totalCategories }} kategori</div>
    </div>
    <div class="sc" style="--c:var(--red)">
        <div class="sc-label">Denda Belum Dibayar</div>
        <div class="sc-value md">Rp {{ number_format($unpaidFines, 0, ',', '.') }}</div>
        <div class="sc-sub">menunggu pembayaran</div>
    </div>
    <div class="sc" style="--c:var(--amber)">
        <div class="sc-label">Menunggu Konfirmasi</div>
        <div class="sc-value">{{ $pendingConfirmation }}</div>
        <div class="sc-sub">pembayaran denda</div>
    </div>
    <div class="sc" style="--c:var(--red)">
        <div class="sc-label">Transaksi Terlambat</div>
        <div class="sc-value" style="color:var(--red)">{{ $overdueTransactions }}</div>
        <div class="sc-sub">perlu tindak lanjut</div>
    </div>
    @if($pendingPreorders > 0)
    <div class="sc" style="--c:var(--teal)">
        <div class="sc-label">Antrean Preorder</div>
        <div class="sc-value">{{ $pendingPreorders }}</div>
        <div class="sc-sub">menunggu buku tersedia</div>
    </div>
    @endif
</div>

{{-- REVENUE PANEL --}}
<div class="rev-panel">
    <div class="rev-main">
        <div class="rev-lbl">Total Pendapatan</div>
        <div class="rev-big">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="rev-dim">semua waktu · dari denda terbayar</div>
        <hr class="rev-sep">
        <div class="rev-lbl">Bulan Ini</div>
        <div class="rev-sm">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</div>
    </div>
    <div class="rev-breakdown">
        <div class="rev-item">
            <div class="rev-item-l"><span class="rev-dot" style="background:#f87171"></span> Keterlambatan</div>
            <div class="rev-amt">Rp {{ number_format($revenueFromLate, 0, ',', '.') }}</div>
        </div>
        <div class="rev-item">
            <div class="rev-item-l"><span class="rev-dot" style="background:#fb923c"></span> Buku Rusak</div>
            <div class="rev-amt">Rp {{ number_format($revenueFromBroken, 0, ',', '.') }}</div>
        </div>
        <div class="rev-item">
            <div class="rev-item-l"><span class="rev-dot" style="background:#a78bfa"></span> Buku Hilang</div>
            <div class="rev-amt">Rp {{ number_format($revenueFromLost, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- CHART ROW 1: Revenue + Transaction Volume --}}
<div class="grid-2">
    <div class="sec" style="margin-bottom:0">
        <div class="sec-title"><span class="pip" style="background:var(--blue)"></span> Pendapatan 6 Bulan Terakhir</div>
        <div style="position:relative;height:210px"><canvas id="cRevenue"></canvas></div>
    </div>
    <div class="sec" style="margin-bottom:0">
        <div class="sec-title"><span class="pip" style="background:var(--amber)"></span> Volume Transaksi 6 Bulan</div>
        <div style="position:relative;height:210px"><canvas id="cTransactions"></canvas></div>
    </div>
</div>
<div style="margin-bottom:.8rem"></div>

{{-- CHART ROW 2: Hourly Activity + Category Interest --}}
<div class="grid-2">
    <div class="sec" style="margin-bottom:0">
        <div class="sec-title">
            <span class="pip" style="background:var(--teal)"></span> Jam Aktif Pengguna
            <span class="sub-note">(30 hari terakhir)</span>
        </div>
        <div style="position:relative;height:200px"><canvas id="cHourly"></canvas></div>
    </div>
    <div class="sec" style="margin-bottom:0">
        <div class="sec-title"><span class="pip" style="background:var(--violet)"></span> Minat Kategori Buku</div>
        @if($topCategoriesData->isEmpty())
            <div style="text-align:center;color:var(--muted);font-size:.82rem;padding:3rem 0">Belum ada data aktivitas Algorithm</div>
        @else
            <div style="position:relative;height:200px"><canvas id="cCategories"></canvas></div>
        @endif
    </div>
</div>
<div style="margin-bottom:.8rem"></div>

{{-- CHART ROW 3: Daily Activity + ACTION DISTRIBUTION (NEW) --}}
<div class="grid-2">
    {{-- Daily Activity --}}
    <div class="sec" style="margin-bottom:0">
        <div class="sec-title">
            <span class="pip" style="background:var(--green)"></span> Aktivitas Harian
            <span class="sub-note">(14 hari terakhir)</span>
        </div>
        <div style="position:relative;height:185px"><canvas id="cDaily"></canvas></div>
    </div>

    {{-- ══ ACTION DISTRIBUTION — REDESIGNED ══ --}}
    <div class="act-panel" style="margin-bottom:0">
        <div class="sec-title" style="margin-bottom:0">
            <span class="pip" style="background:var(--pink)"></span>
            Distribusi Aktivitas Pengguna
            <span class="sub-note">semua waktu</span>
        </div>

        @if($actionStats->isEmpty())
            <div style="text-align:center;color:var(--muted);font-size:.82rem;padding:3rem 0;margin-top:.5rem">
                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;color:#d1d5db"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Belum ada data aksi tercatat
            </div>
        @else
            @php
                $actTotal = $actionStats->sum();
                $actMax   = $actionStats->max() ?: 1;
            @endphp

            {{-- Segmented bar --}}
            <div class="act-bar-wrap">
                <div class="act-bar" id="actSegBar">
                    @foreach($actionStats as $action => $count)
                        @php
                            $meta = $actionMeta[$action] ?? ['label' => ucfirst($action), 'color' => '#6b7280', 'icon' => ''];
                            $pct  = $actTotal > 0 ? round(($count / $actTotal) * 100, 1) : 0;
                        @endphp
                        <div class="act-bar-seg"
                             data-w="{{ $pct }}"
                             style="background:{{ $meta['color'] }}; min-width:{{ $pct > 0 ? '4px' : '0' }};"
                             title="{{ $meta['label'] }}: {{ $count }} ({{ $pct }}%)">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Action cards --}}
            <div class="act-cards">
                @foreach($actionStats as $action => $count)
                    @php
                        $meta = $actionMeta[$action] ?? ['label' => ucfirst($action), 'color' => '#6b7280', 'icon' => ''];
                        $pct  = $actTotal > 0 ? round(($count / $actTotal) * 100, 1) : 0;
                        $barW = $actMax  > 0 ? round(($count / $actMax) * 100) : 0;
                    @endphp
                    <div class="act-card" style="--ac:{{ $meta['color'] }}; background:color-mix(in srgb,{{ $meta['color'] }} 4%,white); border-color:color-mix(in srgb,{{ $meta['color'] }} 16%,white);">
                        <div class="act-card-circle"></div>

                        <div class="act-card-top">
                            <div class="act-card-icon">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                                </svg>
                            </div>
                            <span class="act-card-pct">{{ $pct }}%</span>
                        </div>

                        <span class="act-card-count" data-target="{{ $count }}">0</span>
                        <div class="act-card-label">{{ $meta['label'] }}</div>

                        <div class="act-card-bar">
                            <div class="act-card-bar-fill" data-w="{{ $barW }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total footer --}}
            <div class="act-total-row">
                <span>Total aktivitas tercatat</span>
                <strong>{{ number_format($actTotal) }}</strong>
            </div>
        @endif
    </div>
</div>
<div style="margin-bottom:.8rem"></div>

{{-- BOTTOM GRID: Hot Books + Top Borrowed --}}
<div class="grid-2">
    <div class="sec" style="margin-bottom:0">
        <div class="sec-title">
            <span class="pip" style="background:var(--pink)"></span> Buku Paling Diminati
            <span class="sub-note">(berdasarkan aktivitas)</span>
        </div>
        @if($hotBooks->isEmpty())
            <div style="text-align:center;color:var(--muted);font-size:.82rem;padding:2rem 0">Belum ada data</div>
        @else
        @php $maxHot = $hotBooks->max('total') ?: 1; @endphp
        <ul class="rank-list">
            @foreach($hotBooks as $i => $row)
            @php $bk = $hotBooksInfo[$row->book_id] ?? null; @endphp
            <li class="rank-item">
                <div class="rank-n {{ $i===0?'gold':'' }}">#{{ $i+1 }}</div>
                <div class="rank-bar-w">
                    <div class="rank-bar-top">
                        <span>{{ Str::limit($bk->name ?? 'Unknown', 28) }}</span>
                        <span class="cnt" style="color:var(--pink)">{{ $row->total }}×</span>
                    </div>
                    <div class="rank-track">
                        <div class="rank-fill" style="width:{{ ($row->total/$maxHot)*100 }}%;background:linear-gradient(90deg,var(--pink),var(--violet))"></div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    <div class="sec" style="margin-bottom:0">
        <div class="sec-title"><span class="pip" style="background:var(--violet)"></span> Buku Terbanyak Dipinjam</div>
        @php $maxTrx = $topBooks->max('transactions_count') ?: 1; @endphp
        <ul class="rank-list">
            @forelse($topBooks as $i => $book)
            <li class="rank-item">
                <div class="rank-n {{ $i===0?'gold':'' }}">#{{ $i+1 }}</div>
                <div class="rank-bar-w">
                    <div class="rank-bar-top">
                        <span>{{ Str::limit($book->name, 28) }}</span>
                        <span class="cnt" style="color:var(--violet)">{{ $book->transactions_count }}×</span>
                    </div>
                    <div class="rank-track">
                        <div class="rank-fill" style="width:{{ ($book->transactions_count/$maxTrx)*100 }}%;background:linear-gradient(90deg,var(--blue),var(--violet))"></div>
                    </div>
                </div>
            </li>
            @empty
            <li style="text-align:center;color:var(--muted);padding:1rem">Belum ada data</li>
            @endforelse
        </ul>
    </div>
</div>
<div style="margin-bottom:.8rem"></div>

{{-- RECENT TRANSACTIONS --}}
<div class="sec">
    <div class="sec-title"><span class="pip" style="background:var(--amber)"></span> Transaksi Terbaru</div>
    <div class="dt-wrap">
        <table class="dt">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>No. Kwitansi</th>
                    <th>Status</th>
                    <th>Jatuh Tempo</th>
                    <th>Tgl Pinjam</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $trx)
                @php
                    $isOverdue = $trx->status === 'borrowed' && $trx->due_at && $trx->due_at->isPast();
                    $bc = $trx->returned_at ? 'b-green' : ($isOverdue ? 'b-red' : 'b-blue');
                    $bl = $trx->returned_at ? 'Kembali'  : ($isOverdue ? 'Terlambat' : 'Dipinjam');
                @endphp
                <tr>
                    <td><span style="font-weight:500">{{ $trx->user->name ?? '-' }}</span></td>
                    <td style="font-family:monospace;font-size:.71rem;color:var(--muted)">{{ $trx->receipt_number ?? '#'.substr($trx->id,0,8) }}</td>
                    <td><span class="badge {{ $bc }}">{{ $bl }}</span></td>
                    <td style="font-size:.76rem;color:{{ $isOverdue?'var(--red)':'var(--muted)' }}">
                        {{ $trx->due_at ? \Carbon\Carbon::parse($trx->due_at)->format('d M Y') : '-' }}
                    </td>
                    <td style="font-size:.76rem;color:var(--muted)">{{ \Carbon\Carbon::parse($trx->borrowed_at)->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:2rem">Belum ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="sec">
    <div class="sec-title"><span class="pip" style="background:#6366f1"></span> Akses Cepat</div>
    <div class="qa-grid">
        <a href="{{ route('books.index') }}" class="qa"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>Kelola Buku</a>
        <a href="{{ route('categories.index') }}" class="qa"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>Kategori</a>
        <a href="{{ route('users.index') }}" class="qa"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Pengguna</a>
        <a href="{{ route('transactions.index') }}" class="qa"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Transaksi</a>
        <a href="{{ route('admin.fines.index') }}" class="qa"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Denda</a>
        <a href="{{ route('audit.index') }}" class="qa"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Audit Log</a>
    </div>
</div>

</div>{{-- .db --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#9ca3af';
Chart.defaults.plugins.legend.display = false;

const gridC = 'rgba(0,0,0,0.045)';
const tickC = '#9ca3af';
const baseScales = (yCallback) => ({
    x: { grid: { display: false }, ticks: { color: tickC, font: { size: 10 } } },
    y: { grid: { color: gridC }, ticks: { color: tickC, font: { size: 10 }, callback: yCallback }, beginAtZero: true }
});

// ── Revenue Bar ──
new Chart(document.getElementById('cRevenue'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($revenueLabels) !!},
        datasets: [{
            data: {!! json_encode($revenueValues) !!},
            backgroundColor: (ctx) => {
                const g = ctx.chart.ctx.createLinearGradient(0,0,0,200);
                g.addColorStop(0,'rgba(37,99,235,.85)');
                g.addColorStop(1,'rgba(37,99,235,.18)');
                return g;
            },
            borderRadius: 6, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { tooltip: { callbacks: { label: c => 'Rp '+c.raw.toLocaleString('id-ID') } } },
        scales: baseScales(v => v >= 1000 ? 'Rp '+(v/1000)+'k' : 'Rp '+v)
    }
});

// ── Transaction Line ──
new Chart(document.getElementById('cTransactions'), {
    type: 'line',
    data: {
        labels: {!! json_encode($revenueLabels) !!},
        datasets: [{
            data: {!! json_encode($transactionCounts) !!},
            borderColor: '#d97706', backgroundColor: 'rgba(217,119,6,.08)',
            fill: true, tension: 0.42,
            pointBackgroundColor: '#d97706', pointRadius: 4, pointHoverRadius: 6, borderWidth: 2.5,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { tooltip: { callbacks: { label: c => c.raw+' transaksi' } } },
        scales: baseScales(v => v)
    }
});

// ── Hourly Activity Bar ──
new Chart(document.getElementById('cHourly'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($hourlyLabels) !!},
        datasets: [{
            data: {!! json_encode($hourlyValues) !!},
            backgroundColor: (ctx) => {
                const vals = {!! json_encode($hourlyValues) !!};
                const max = Math.max(...vals) || 1;
                const alpha = 0.12 + (ctx.raw / max) * 0.78;
                return `rgba(8,145,178,${alpha})`;
            },
            borderRadius: 3, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { tooltip: { callbacks: { label: c => c.raw+' aktivitas' } } },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickC, font: { size: 9 }, maxRotation: 0 } },
            y: { grid: { color: gridC }, ticks: { color: tickC, font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
        }
    }
});

// ── Daily Activity Line ──
new Chart(document.getElementById('cDaily'), {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyLabels) !!},
        datasets: [{
            data: {!! json_encode($dailyValues) !!},
            borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.08)',
            fill: true, tension: 0.42,
            pointBackgroundColor: '#059669', pointRadius: 3, pointHoverRadius: 5, borderWidth: 2,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { tooltip: { callbacks: { label: c => c.raw+' aktivitas' } } },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickC, font: { size: 9 }, maxRotation: 40 } },
            y: { grid: { color: gridC }, ticks: { color: tickC, font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
        }
    }
});

@if(!$topCategoriesData->isEmpty())
new Chart(document.getElementById('cCategories'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($topCategoriesData->pluck('cat_name')->values()) !!},
        datasets: [{
            data: {!! json_encode($topCategoriesData->pluck('total')->values()) !!},
            backgroundColor: ['#7c3aed','#2563eb','#059669','#d97706','#dc2626','#0891b2'],
            hoverOffset: 8, borderWidth: 2.5, borderColor: '#fff',
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: {
            legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 8, color: '#374151' } },
            tooltip: { callbacks: { label: c => c.label+': '+c.raw+' aksi' } }
        }
    }
});
@endif

/* ══ ACTION DISTRIBUTION ANIMATIONS ══ */
(function() {
    // Animate segmented bar widths
    const segs = document.querySelectorAll('.act-bar-seg');
    setTimeout(() => {
        segs.forEach(seg => {
            seg.style.width = seg.dataset.w + '%';
        });
    }, 120);

    // Animate card bar fills
    const fills = document.querySelectorAll('.act-card-bar-fill');
    setTimeout(() => {
        fills.forEach((fill, i) => {
            setTimeout(() => {
                fill.style.width = fill.dataset.w + '%';
            }, i * 80);
        });
    }, 200);

    // Animated count-up for card numbers
    function countUp(el, target, duration) {
        const start    = performance.now();
        const startVal = 0;
        function step(now) {
            const elapsed  = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(startVal + (target - startVal) * eased).toLocaleString('id-ID');
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    const counters = document.querySelectorAll('.act-card-count[data-target]');
    // Use IntersectionObserver so animation fires when scrolled into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el     = entry.target;
                const target = parseInt(el.dataset.target, 10);
                countUp(el, target, 900 + Math.random() * 300);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.3 });

    counters.forEach(el => observer.observe(el));
})();
</script>
@endsection