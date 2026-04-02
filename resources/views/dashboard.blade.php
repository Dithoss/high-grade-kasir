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
        'view'     => ['label' => 'Lihat',    'color' => '#2563eb', 'tw_bg' => 'bg-blue-50', 'tw_text' => 'text-blue-600', 'tw_ring' => 'ring-blue-100', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        'borrow'   => ['label' => 'Pinjam',   'color' => '#059669', 'tw_bg' => 'bg-emerald-50', 'tw_text' => 'text-emerald-600', 'tw_ring' => 'ring-emerald-100', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        'wishlist' => ['label' => 'Wishlist', 'color' => '#db2777', 'tw_bg' => 'bg-pink-50', 'tw_text' => 'text-pink-600', 'tw_ring' => 'ring-pink-100', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        'search'   => ['label' => 'Cari',     'color' => '#d97706', 'tw_bg' => 'bg-amber-50', 'tw_text' => 'text-amber-600', 'tw_ring' => 'ring-amber-100', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
        'return'   => ['label' => 'Kembali',  'color' => '#0891b2', 'tw_bg' => 'bg-cyan-50', 'tw_text' => 'text-cyan-600', 'tw_ring' => 'ring-cyan-100', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
        'review'   => ['label' => 'Ulasan',   'color' => '#7c3aed', 'tw_bg' => 'bg-violet-50', 'tw_text' => 'text-violet-600', 'tw_ring' => 'ring-violet-100', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ];
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

* { box-sizing: border-box; }
body { font-family: 'Plus Jakarta Sans', sans-serif; }

/* Segmented bar animation */
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
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.4) 50%, transparent 100%);
    transform: translateX(-100%);
    animation: shimmer 2.5s ease-in-out infinite;
}
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    60%  { transform: translateX(100%); }
    100% { transform: translateX(100%); }
}

.act-card-bar-fill {
    height: 100%;
    border-radius: 999px;
    width: 0;
    transition: width 1.3s cubic-bezier(0.22,1,0.36,1);
}

/* Revenue panel gradient */
.rev-panel {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 40%, #3b82f6 70%, #60a5fa 100%);
    position: relative;
    overflow: hidden;
}
.rev-panel::before {
    content: '';
    position: absolute;
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
    top: -140px; right: -100px;
    pointer-events: none;
}
.rev-panel::after {
    content: '';
    position: absolute;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(96,165,250,.3) 0%, transparent 70%);
    bottom: -70px; left: 40%;
    pointer-events: none;
}
.rev-grid-dots {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
}

/* Stat card accent bar */
.sc-accent { height: 3px; border-radius: 999px 999px 0 0; }

/* Hover scale */
.card-hover { transition: transform .17s, box-shadow .17s; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.07); }

/* Rank bar track */
.rank-track { height: 5px; background: #f0f0f0; border-radius: 999px; overflow: hidden; }
.rank-fill  { height: 100%; border-radius: 999px; }

/* Table hover */
.dt-row:hover td { background: #f8faff; }

/* Quick action hover */
.qa-btn { transition: all .16s; }
.qa-btn:hover { border-color: #2563eb; background: #eff6ff; color: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 14px rgba(37,99,235,.1); }

/* Chart canvas */
canvas { display: block; }

/* Scrollbar */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
</style>

<div class="min-h-screen bg-slate-50 p-6 font-['Plus_Jakarta_Sans']">

    {{-- SUCCESS ALERT --}}
    @if(session('success'))
    <div class="flex items-center gap-2 px-4 py-3 mb-5 rounded-xl bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── HEADER ── --}}
    <div class="flex items-center justify-between flex-wrap gap-4 mb-7">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 leading-tight tracking-tight">
                Halo, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">Ringkasan aktivitas perpustakaan hari ini</p>
        </div>
        <span class="text-xs font-bold uppercase tracking-widest bg-blue-600 text-white px-4 py-2 rounded-full whitespace-nowrap shadow-sm shadow-blue-200">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    {{-- ── PRIMARY STAT CARDS ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-3 mb-3">

        {{-- Total Buku --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#2563eb"></div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3 bg-blue-50">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Buku</div>
            <div class="text-3xl font-extrabold text-slate-900 leading-none">{{ number_format($totalBooks) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ number_format($totalStock) }} unit stok</div>
        </div>

        {{-- Sedang Dipinjam --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#d97706"></div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3 bg-amber-50">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Sedang Dipinjam</div>
            <div class="text-3xl font-extrabold text-slate-900 leading-none">{{ number_format($activeTransactions) }}</div>
            <div class="text-[11px] text-slate-400 mt-1"><span class="text-red-500 font-semibold">{{ $overdueTransactions }}</span> terlambat</div>
        </div>

        {{-- Kembali Bulan Ini --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#059669"></div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3 bg-emerald-50">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Kembali Bulan Ini</div>
            <div class="text-3xl font-extrabold text-slate-900 leading-none">{{ number_format($returnedThisMonth) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ number_format($totalTransactions) }} total transaksi</div>
        </div>

        {{-- Total Pengguna --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#7c3aed"></div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3 bg-violet-50">
                <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Pengguna</div>
            <div class="text-3xl font-extrabold text-slate-900 leading-none">{{ number_format($totalUsers) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $totalCategories }} kategori</div>
        </div>

        {{-- Denda Belum Dibayar --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#dc2626"></div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Denda Belum Dibayar</div>
            <div class="text-xl font-extrabold text-red-600 leading-tight">Rp {{ number_format($unpaidFines, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">menunggu pembayaran</div>
        </div>

        {{-- Menunggu Konfirmasi --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#d97706"></div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Menunggu Konfirmasi</div>
            <div class="text-3xl font-extrabold text-slate-900 leading-none">{{ $pendingConfirmation }}</div>
            <div class="text-[11px] text-slate-400 mt-1">pembayaran denda</div>
        </div>

        {{-- Transaksi Terlambat --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#dc2626"></div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Transaksi Terlambat</div>
            <div class="text-3xl font-extrabold text-red-600 leading-none">{{ $overdueTransactions }}</div>
            <div class="text-[11px] text-slate-400 mt-1">perlu tindak lanjut</div>
        </div>

        @if($pendingPreorders > 0)
        {{-- Antrean Preorder --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 card-hover overflow-hidden relative">
            <div class="sc-accent absolute top-0 left-0 right-0" style="background:#0891b2"></div>
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Antrean Preorder</div>
            <div class="text-3xl font-extrabold text-slate-900 leading-none">{{ $pendingPreorders }}</div>
            <div class="text-[11px] text-slate-400 mt-1">menunggu buku tersedia</div>
        </div>
        @endif

    </div>

    {{-- ── REVENUE PANEL (Blue Gradient) ── --}}
    <div class="rev-panel rounded-2xl p-6 mb-3 grid grid-cols-1 md:grid-cols-2 gap-6 shadow-lg shadow-blue-200/50">
        <div class="rev-grid-dots rounded-2xl"></div>

        {{-- Left: totals --}}
        <div class="relative z-10">
            <div class="text-[10px] uppercase tracking-widest text-blue-200 font-semibold mb-1">Total Pendapatan</div>
            <div class="text-3xl lg:text-4xl font-extrabold text-white leading-none mb-1">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="text-blue-200/60 text-xs mb-5">semua waktu · dari denda terbayar</div>

            <div class="h-px bg-white/10 mb-4"></div>

            <div class="text-[10px] uppercase tracking-widest text-blue-200 font-semibold mb-1">Bulan Ini</div>
            <div class="text-2xl font-extrabold text-white leading-none">
                Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}
            </div>

            {{-- Mini badge --}}
            <div class="mt-4 inline-flex items-center gap-1.5 bg-white/10 backdrop-blur rounded-full px-3 py-1 text-xs text-blue-100 font-medium border border-white/15">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Rekap semua denda terbayar
            </div>
        </div>

        {{-- Right: breakdown --}}
        <div class="relative z-10 flex flex-col gap-3 justify-center">
            <div class="flex items-center justify-between bg-white/10 backdrop-blur border border-white/15 rounded-xl px-4 py-3">
                <div class="flex items-center gap-2 text-sm text-blue-100">
                    <span class="w-2 h-2 rounded-full bg-red-300 shrink-0"></span>
                    Keterlambatan
                </div>
                <div class="font-extrabold text-white text-sm font-['DM_Mono']">Rp {{ number_format($revenueFromLate, 0, ',', '.') }}</div>
            </div>
            <div class="flex items-center justify-between bg-white/10 backdrop-blur border border-white/15 rounded-xl px-4 py-3">
                <div class="flex items-center gap-2 text-sm text-blue-100">
                    <span class="w-2 h-2 rounded-full bg-orange-300 shrink-0"></span>
                    Buku Rusak
                </div>
                <div class="font-extrabold text-white text-sm font-['DM_Mono']">Rp {{ number_format($revenueFromBroken, 0, ',', '.') }}</div>
            </div>
            <div class="flex items-center justify-between bg-white/10 backdrop-blur border border-white/15 rounded-xl px-4 py-3">
                <div class="flex items-center gap-2 text-sm text-blue-100">
                    <span class="w-2 h-2 rounded-full bg-violet-300 shrink-0"></span>
                    Buku Hilang
                </div>
                <div class="font-extrabold text-white text-sm font-['DM_Mono']">Rp {{ number_format($revenueFromLost, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- ── CHARTS ROW 1: Revenue + Transactions ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Pendapatan 6 Bulan Terakhir</span>
            </div>
            <div class="relative h-52"><canvas id="cRevenue"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Volume Transaksi 6 Bulan</span>
            </div>
            <div class="relative h-52"><canvas id="cTransactions"></canvas></div>
        </div>
    </div>

    {{-- ── CHARTS ROW 2: Hourly + Category ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-cyan-500 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Jam Aktif Pengguna</span>
                <span class="text-[10px] text-slate-400 font-normal">(30 hari terakhir)</span>
            </div>
            <div class="relative h-48"><canvas id="cHourly"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-violet-600 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Minat Kategori Buku</span>
            </div>
            @if($topCategoriesData->isEmpty())
                <div class="flex items-center justify-center h-48 text-slate-400 text-sm">Belum ada data aktivitas Algorithm</div>
            @else
                <div class="relative h-48"><canvas id="cCategories"></canvas></div>
            @endif
        </div>
    </div>

    {{-- ── CHARTS ROW 3: Daily Activity + Action Distribution ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">

        {{-- Daily Activity --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Aktivitas Harian</span>
                <span class="text-[10px] text-slate-400 font-normal">(14 hari terakhir)</span>
            </div>
            <div class="relative h-44"><canvas id="cDaily"></canvas></div>
        </div>

        {{-- Action Distribution --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-pink-500 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Distribusi Aktivitas Pengguna</span>
                <span class="text-[10px] text-slate-400 font-normal">semua waktu</span>
            </div>

            @if($actionStats->isEmpty())
                <div class="flex flex-col items-center justify-center h-44 text-slate-400 text-sm gap-2">
                    <svg class="w-9 h-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Belum ada data aksi tercatat
                </div>
            @else
                @php
                    $actTotal = $actionStats->sum();
                    $actMax   = $actionStats->max() ?: 1;
                @endphp

                {{-- Segmented bar --}}
                <div class="flex h-2.5 rounded-full overflow-hidden gap-0.5 bg-slate-100 mb-4" id="actSegBar">
                    @foreach($actionStats as $action => $count)
                        @php
                            $meta = $actionMeta[$action] ?? ['label' => ucfirst($action), 'color' => '#6b7280'];
                            $pct  = $actTotal > 0 ? round(($count / $actTotal) * 100, 1) : 0;
                        @endphp
                        <div class="act-bar-seg"
                             data-w="{{ $pct }}"
                             style="background:{{ $meta['color'] }}; min-width:{{ $pct > 0 ? '4px' : '0' }};"
                             title="{{ $meta['label'] }}: {{ $count }} ({{ $pct }}%)">
                        </div>
                    @endforeach
                </div>

                {{-- Action cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($actionStats as $action => $count)
                        @php
                            $meta = $actionMeta[$action] ?? ['label' => ucfirst($action), 'color' => '#6b7280', 'tw_bg' => 'bg-slate-50', 'tw_text' => 'text-slate-600', 'tw_ring' => 'ring-slate-100'];
                            $pct  = $actTotal > 0 ? round(($count / $actTotal) * 100, 1) : 0;
                            $barW = $actMax  > 0 ? round(($count / $actMax) * 100) : 0;
                        @endphp
                        <div class="rounded-xl p-3 border border-slate-100 bg-slate-50/60 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 relative overflow-hidden">
                            {{-- Decorative circle --}}
                            <div class="absolute -bottom-4 -right-4 w-16 h-16 rounded-full opacity-[0.07]" style="background:{{ $meta['color'] }}"></div>

                            <div class="flex items-center justify-between mb-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:{{ $meta['color'] }}18">
                                    <svg class="w-3.5 h-3.5" style="color:{{ $meta['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="color:{{ $meta['color'] }};background:{{ $meta['color'] }}15">{{ $pct }}%</span>
                            </div>

                            <span class="block text-xl font-extrabold text-slate-900 leading-none mb-0.5 act-card-count" data-target="{{ $count }}">0</span>
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $meta['label'] }}</div>

                            <div class="mt-2 h-1 rounded-full bg-slate-100 overflow-hidden">
                                <div class="act-card-bar-fill h-full rounded-full" data-w="{{ $barW }}" style="background:{{ $meta['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Total row --}}
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
                    <span>Total aktivitas tercatat</span>
                    <strong class="text-base font-extrabold text-slate-900">{{ number_format($actTotal) }}</strong>
                </div>
            @endif
        </div>
    </div>

    {{-- ── BOTTOM: Hot Books + Top Borrowed ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">

        {{-- Hot Books --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-pink-500 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Buku Paling Diminati</span>
                <span class="text-[10px] text-slate-400 font-normal">(berdasarkan aktivitas)</span>
            </div>
            @if($hotBooks->isEmpty())
                <div class="text-center text-slate-400 text-sm py-8">Belum ada data</div>
            @else
                @php $maxHot = $hotBooks->max('total') ?: 1; @endphp
                <ul class="space-y-3">
                    @foreach($hotBooks as $i => $row)
                        @php $bk = $hotBooksInfo[$row->book_id] ?? null; @endphp
                        <li class="flex items-center gap-3">
                            <div class="text-[11px] font-extrabold w-5 text-center shrink-0 {{ $i===0 ? 'text-amber-500' : 'text-slate-400' }}">#{{ $i+1 }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-slate-700 truncate text-xs">{{ Str::limit($bk->name ?? 'Unknown', 28) }}</span>
                                    <span class="font-bold text-pink-500 text-xs ml-2 shrink-0">{{ $row->total }}×</span>
                                </div>
                                <div class="rank-track">
                                    <div class="rank-fill" style="width:{{ ($row->total/$maxHot)*100 }}%;background:linear-gradient(90deg,#db2777,#7c3aed)"></div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Top Borrowed --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full bg-violet-600 shrink-0"></span>
                <span class="font-bold text-slate-800 text-sm">Buku Terbanyak Dipinjam</span>
            </div>
            @php $maxTrx = $topBooks->max('transactions_count') ?: 1; @endphp
            <ul class="space-y-3">
                @forelse($topBooks as $i => $book)
                    <li class="flex items-center gap-3">
                        <div class="text-[11px] font-extrabold w-5 text-center shrink-0 {{ $i===0 ? 'text-amber-500' : 'text-slate-400' }}">#{{ $i+1 }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-700 truncate text-xs">{{ Str::limit($book->name, 28) }}</span>
                                <span class="font-bold text-violet-600 text-xs ml-2 shrink-0">{{ $book->transactions_count }}×</span>
                            </div>
                            <div class="rank-track">
                                <div class="rank-fill" style="width:{{ ($book->transactions_count/$maxTrx)*100 }}%;background:linear-gradient(90deg,#2563eb,#7c3aed)"></div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="text-center text-slate-400 text-sm py-8">Belum ada data</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ── RECENT TRANSACTIONS ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5 mb-3">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
            <span class="font-bold text-slate-800 text-sm">Transaksi Terbaru</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-2.5 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-50 rounded-l-lg">Pengguna</th>
                        <th class="text-left py-2.5 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-50">No. Kwitansi</th>
                        <th class="text-left py-2.5 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-50">Status</th>
                        <th class="text-left py-2.5 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-50">Jatuh Tempo</th>
                        <th class="text-left py-2.5 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-50 rounded-r-lg">Tgl Pinjam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $trx)
                        @php
                            $isOverdue = $trx->status === 'borrowed' && $trx->due_at && $trx->due_at->isPast();
                            $badgeClass = $trx->returned_at
                                ? 'bg-emerald-50 text-emerald-700'
                                : ($isOverdue ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700');
                            $badgeLabel = $trx->returned_at ? 'Kembali' : ($isOverdue ? 'Terlambat' : 'Dipinjam');
                        @endphp
                        <tr class="dt-row border-b border-slate-50 last:border-0">
                            <td class="py-3 px-3 font-semibold text-slate-800 text-xs">{{ $trx->user->name ?? '-' }}</td>
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-400">{{ $trx->receipt_number ?? '#'.substr($trx->id,0,8) }}</td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </td>
                            <td class="py-3 px-3 text-xs {{ $isOverdue ? 'text-red-500 font-semibold' : 'text-slate-400' }}">
                                {{ $trx->due_at ? \Carbon\Carbon::parse($trx->due_at)->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-xs text-slate-400">{{ \Carbon\Carbon::parse($trx->borrowed_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-8">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── QUICK ACTIONS ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
            <span class="font-bold text-slate-800 text-sm">Akses Cepat</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            <a href="{{ route('books.index') }}" class="qa-btn flex flex-col items-center gap-2 p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-600 text-xs font-semibold text-center no-underline">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Kelola Buku
            </a>
            <a href="{{ route('categories.index') }}" class="qa-btn flex flex-col items-center gap-2 p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-600 text-xs font-semibold text-center no-underline">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Kategori
            </a>
            <a href="{{ route('users.index') }}" class="qa-btn flex flex-col items-center gap-2 p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-600 text-xs font-semibold text-center no-underline">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengguna
            </a>
            <a href="{{ route('transactions.index') }}" class="qa-btn flex flex-col items-center gap-2 p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-600 text-xs font-semibold text-center no-underline">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Transaksi
            </a>
            <a href="{{ route('admin.fines.index') }}" class="qa-btn flex flex-col items-center gap-2 p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-600 text-xs font-semibold text-center no-underline">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Denda
            </a>
            <a href="{{ route('audit.index') }}" class="qa-btn flex flex-col items-center gap-2 p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-600 text-xs font-semibold text-center no-underline">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Audit Log
            </a>
        </div>
    </div>

</div>{{-- end wrapper --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#94a3b8';
Chart.defaults.plugins.legend.display = false;

const gridC = 'rgba(0,0,0,0.04)';
const tickC = '#94a3b8';

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
                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                g.addColorStop(0, 'rgba(37,99,235,.85)');
                g.addColorStop(1, 'rgba(37,99,235,.15)');
                return g;
            },
            borderRadius: 6, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { tooltip: { callbacks: { label: c => 'Rp ' + c.raw.toLocaleString('id-ID') } } },
        scales: baseScales(v => v >= 1000 ? 'Rp ' + (v / 1000) + 'k' : 'Rp ' + v)
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
        plugins: { tooltip: { callbacks: { label: c => c.raw + ' transaksi' } } },
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
        plugins: { tooltip: { callbacks: { label: c => c.raw + ' aktivitas' } } },
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
        plugins: { tooltip: { callbacks: { label: c => c.raw + ' aktivitas' } } },
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
            backgroundColor: ['#7c3aed', '#2563eb', '#059669', '#d97706', '#dc2626', '#0891b2'],
            hoverOffset: 8, borderWidth: 2.5, borderColor: '#fff',
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: {
            legend: {
                display: true, position: 'right',
                labels: { font: { size: 10 }, boxWidth: 10, padding: 8, color: '#475569' }
            },
            tooltip: { callbacks: { label: c => c.label + ': ' + c.raw + ' aksi' } }
        }
    }
});
@endif

/* ══ ACTION Distribution Animations ══ */
(function () {
    // Animate segmented bar
    const segs = document.querySelectorAll('.act-bar-seg');
    setTimeout(() => {
        segs.forEach(seg => { seg.style.width = seg.dataset.w + '%'; });
    }, 120);

    // Animate card bars
    const fills = document.querySelectorAll('.act-card-bar-fill');
    setTimeout(() => {
        fills.forEach((fill, i) => {
            setTimeout(() => { fill.style.width = fill.dataset.w + '%'; }, i * 80);
        });
    }, 200);

    // Count-up animation
    function countUp(el, target, duration) {
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased).toLocaleString('id-ID');
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    const counters = document.querySelectorAll('.act-card-count[data-target]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                countUp(el, parseInt(el.dataset.target, 10), 900 + Math.random() * 300);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.3 });
    counters.forEach(el => observer.observe(el));
})();
</script>

@endsection