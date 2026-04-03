@extends('layouts.app')

@section('title', 'Manajemen Preorder')

@section('content')

@php
    use App\Models\Preorder;

    $statCounts = [
        'all'       => Preorder::count(),
        'waiting'   => Preorder::where('status', 'waiting')->count(),
        'ready'     => Preorder::where('status', 'ready')->count(),
        'confirmed' => Preorder::where('status', 'confirmed')->count(),
        'cancelled' => Preorder::where('status', 'cancelled')->count(),
        'expired'   => Preorder::where('status', 'expired')->count(),
    ];

    $tabs = [
        ''          => ['label' => 'Semua',        'emoji' => '📋', 'color' => 'gray'],
        'waiting'   => ['label' => 'Menunggu',     'emoji' => '⏳', 'color' => 'amber'],
        'ready'     => ['label' => 'Siap Pinjam',  'emoji' => '✅', 'color' => 'green'],
        'confirmed' => ['label' => 'Dipinjam',     'emoji' => '📌', 'color' => 'blue'],
        'cancelled' => ['label' => 'Dibatalkan',   'emoji' => '❌', 'color' => 'red'],
        'expired'   => ['label' => 'Kedaluwarsa',  'emoji' => '🕐', 'color' => 'slate'],
    ];
    $currentStatus = request('status', '');
@endphp

<div class="max-w-7xl mx-auto space-y-5">

    {{-- ╔═══════════════════════════════════════════════════════╗
         ║  HEADER BANNER                                       ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-700 via-indigo-700 to-violet-700 p-7 text-white shadow-xl">
        <div class="pointer-events-none absolute -right-12 -top-12 h-52 w-52 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-white/20 shadow-inner backdrop-blur-sm">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight">Manajemen Preorder</h1>
                    <p class="mt-0.5 text-sm text-blue-200">Kelola semua antrian peminjaman anggota</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <div class="rounded-xl bg-amber-400/30 px-4 py-2.5 text-center ring-2 ring-amber-400/50 backdrop-blur-sm">
                    <p class="text-xl font-extrabold">{{ $statCounts['waiting'] }}</p>
                    <p class="text-xs text-amber-100">Menunggu</p>
                </div>
                @if($statCounts['ready'] > 0)
                    <div class="rounded-xl bg-green-500/30 px-4 py-2.5 text-center ring-2 ring-green-400/50 backdrop-blur-sm">
                        <p class="text-xl font-extrabold">{{ $statCounts['ready'] }}</p>
                        <p class="text-xs text-green-100">Siap Dipinjam</p>
                    </div>
                @endif
                <div class="rounded-xl bg-white/15 px-4 py-2.5 text-center backdrop-blur-sm">
                    <p class="text-xl font-extrabold">{{ $statCounts['all'] }}</p>
                    <p class="text-xs text-blue-200">Total</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-5 py-3.5 shadow-sm">
            <svg class="h-5 w-5 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm font-semibold text-green-800">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3.5 shadow-sm">
            <svg class="h-5 w-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    {{-- ╔═══════════════════════════════════════════════════════╗
         ║  FILTER & SEARCH                                     ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-md">
        <form method="GET" action="{{ route('admin.preorders.index') }}"
              class="flex flex-wrap items-center gap-3">

            {{-- Preserve status tab --}}
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama anggota, email, atau judul buku..."
                       class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 py-2.5 pl-9 pr-4 text-sm
                              focus:border-blue-500 focus:bg-white focus:outline-none">
            </div>

            {{-- Filter buku --}}
            <select name="book_id"
                    class="rounded-xl border-2 border-gray-200 bg-gray-50 py-2.5 px-3 text-sm
                           focus:border-blue-500 focus:outline-none">
                <option value="">Semua Buku</option>
                @foreach($books as $book)
                    <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                        {{ Str::limit($book->name, 35) }}
                    </option>
                @endforeach
            </select>

            {{-- Sort --}}
            <select name="sort_by"
                    class="rounded-xl border-2 border-gray-200 bg-gray-50 py-2.5 px-3 text-sm
                           focus:border-blue-500 focus:outline-none">
                <option value="created_at"           {{ request('sort_by','created_at') === 'created_at' ? 'selected' : '' }}>Terbaru</option>
                <option value="expected_borrow_date" {{ request('sort_by') === 'expected_borrow_date'    ? 'selected' : '' }}>Rencana Pinjam</option>
                <option value="expired_at"           {{ request('sort_by') === 'expired_at'             ? 'selected' : '' }}>Kedaluwarsa</option>
            </select>

            <button type="submit"
                    class="flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold
                           text-white shadow-sm hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                Cari
            </button>

            @if(request()->hasAny(['search', 'book_id', 'sort_by']))
                <a href="{{ route('admin.preorders.index', $currentStatus ? ['status' => $currentStatus] : []) }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ╔═══════════════════════════════════════════════════════╗
         ║  FILTER TABS                                         ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white p-2 shadow-md">
        <div class="flex min-w-max gap-1.5">
            @foreach($tabs as $key => $tab)
                @php
                    $isActive = $currentStatus === $key;
                    $count    = $statCounts[$key === '' ? 'all' : $key] ?? 0;
                    $activeClass = match($tab['color']) {
                        'amber' => 'bg-amber-500 text-white shadow-md shadow-amber-200',
                        'green' => 'bg-green-600 text-white shadow-md shadow-green-200',
                        'blue'  => 'bg-blue-600 text-white shadow-md shadow-blue-200',
                        'red'   => 'bg-red-500 text-white shadow-md shadow-red-200',
                        'slate' => 'bg-slate-600 text-white shadow-md',
                        default => 'bg-gray-700 text-white shadow-md',
                    };
                    $inactiveClass = match($tab['color']) {
                        'amber' => 'text-amber-700 hover:bg-amber-50',
                        'green' => 'text-green-700 hover:bg-green-50',
                        'blue'  => 'text-blue-700 hover:bg-blue-50',
                        'red'   => 'text-red-700 hover:bg-red-50',
                        'slate' => 'text-slate-600 hover:bg-slate-50',
                        default => 'text-gray-600 hover:bg-gray-100',
                    };
                    $query = array_merge(
                        request()->only(['search', 'book_id', 'sort_by']),
                        $key ? ['status' => $key] : []
                    );
                @endphp
                <a href="{{ route('admin.preorders.index', $query) }}"
                   class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold transition-all
                          {{ $isActive ? $activeClass : $inactiveClass }}">
                    <span>{{ $tab['emoji'] }}</span>
                    <span>{{ $tab['label'] }}</span>
                    <span class="rounded-full px-1.5 py-0.5 text-xs font-extrabold
                        {{ $isActive ? 'bg-white/25' : 'bg-gray-100 text-gray-500' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ╔═══════════════════════════════════════════════════════╗
         ║  TABLE                                               ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    @if($preorders->count() > 0)
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-4 py-3.5 border-b border-gray-100">#</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Anggota</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Buku</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Status</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Antrian</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Rencana Pinjam</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Kedaluwarsa</th>
                            <th class="px-4 py-3.5 border-b border-gray-100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($preorders as $preorder)
                            @php
                                $isReady    = $preorder->status === 'ready';
                                $isWaiting  = $preorder->status === 'waiting';
                                $isActive   = $preorder->isActive();

                                $dynPos = \App\Models\Preorder::where('book_id', $preorder->book_id)
                                    ->whereIn('status', ['waiting', 'ready'])
                                    ->where('created_at', '<=', $preorder->created_at)
                                    ->count();

                                $badgeClass = match($preorder->status) {
                                    'waiting'   => 'bg-amber-100 text-amber-800 ring-1 ring-amber-300',
                                    'ready'     => 'bg-green-100 text-green-800 ring-1 ring-green-400',
                                    'confirmed' => 'bg-blue-100 text-blue-800 ring-1 ring-blue-300',
                                    'cancelled' => 'bg-gray-100 text-gray-600 ring-1 ring-gray-300',
                                    'expired'   => 'bg-red-100 text-red-800 ring-1 ring-red-300',
                                    default     => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $isReady ? 'bg-green-50/40' : '' }}">

                                {{-- ID --}}
                                <td class="px-4 py-3.5 text-xs text-gray-400 font-mono whitespace-nowrap">
                                    PO-{{ str_pad($preorder->id, 3, '0', STR_PAD_LEFT) }}
                                </td>

                                {{-- Anggota --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-8 w-8 flex-shrink-0 overflow-hidden rounded-full
                                                    bg-gradient-to-br from-blue-500 to-blue-700
                                                    flex items-center justify-center">
                                            @if($preorder->user?->image)
                                                <img src="{{ asset('storage/' . $preorder->user->image) }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <span class="text-xs font-bold text-white">
                                                    {{ strtoupper(substr($preorder->user?->name ?? 'U', 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 truncate max-w-[130px]">
                                                {{ $preorder->user?->name ?? '—' }}
                                            </p>
                                            <p class="text-xs text-gray-400 truncate max-w-[130px]">
                                                {{ $preorder->user?->email ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Buku --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-10 w-7 flex-shrink-0 overflow-hidden rounded-md border border-gray-100 shadow-sm">
                                            @if($preorder->book?->image)
                                                <img src="{{ asset('storage/' . $preorder->book->image) }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-violet-100 to-purple-200">
                                                    <svg class="h-4 w-4 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 truncate max-w-[150px]">
                                                {{ $preorder->book?->name ?? '—' }}
                                            </p>
                                            <p class="text-xs text-gray-400 truncate max-w-[150px]">
                                                {{ $preorder->book?->writer ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                        {{ $preorder->status_label }}
                                    </span>
                                </td>

                                {{-- Posisi antrian --}}
                                <td class="px-4 py-3.5 text-center">
                                    @if($isActive)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold
                                            {{ $dynPos === 1 ? 'bg-amber-100 text-amber-800' : 'bg-violet-100 text-violet-800' }}">
                                            {{ $dynPos }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-sm">—</span>
                                    @endif
                                </td>

                                {{-- Rencana pinjam --}}
                                <td class="px-4 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $preorder->expected_borrow_date->format('d M Y') }}
                                </td>

                                {{-- Kedaluwarsa --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($isReady && $preorder->expired_at)
                                        <span class="flex items-center gap-1 text-xs font-bold text-red-600 animate-pulse">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                            </svg>
                                            {{ $preorder->expired_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5 flex-wrap">

                                        {{-- Detail --}}
                                        <a href="{{ route('admin.preorders.show', $preorder->id) }}"
                                           class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50
                                                  px-2.5 py-1.5 text-xs font-semibold text-gray-600
                                                  hover:bg-gray-100 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>

                                        {{-- Tandai Siap — hanya jika waiting --}}
                                        @if($isWaiting)
                                            <form action="{{ route('admin.preorders.markReady', $preorder->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Tandai preorder ini sebagai siap dipinjam?\nAnggota akan mendapat notifikasi.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="flex items-center gap-1 rounded-lg bg-green-100
                                                           px-2.5 py-1.5 text-xs font-semibold text-green-800
                                                           hover:bg-green-200 transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Tandai Siap
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Kirim Notif — hanya jika ready --}}
                                        @if($isReady)
                                            <form action="{{ route('admin.preorders.notify', $preorder->id) }}"
                                                  method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-1 rounded-lg bg-amber-100
                                                           px-2.5 py-1.5 text-xs font-semibold text-amber-800
                                                           hover:bg-amber-200 transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                    </svg>
                                                    Kirim Notif
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Batalkan — hanya jika masih aktif --}}
                                        @if($isActive)
                                            <form action="{{ route('admin.preorders.cancel', $preorder->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Batalkan preorder ini?\nAntrian anggota lain akan bergeser naik.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center gap-1 rounded-lg bg-red-100
                                                           px-2.5 py-1.5 text-xs font-semibold text-red-700
                                                           hover:bg-red-200 transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                {{ $preorders->appends(request()->query())->links() }}
            </div>
        </div>

    @else
        <div class="rounded-2xl border border-gray-100 bg-white p-16 text-center shadow-md">
            <div class="mx-auto mb-6 flex h-28 w-28 items-center justify-center rounded-3xl
                        bg-gradient-to-br from-blue-100 to-indigo-100 shadow-inner">
                <svg class="h-14 w-14 text-blue-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="mb-2 text-2xl font-extrabold text-gray-900">Tidak Ada Preorder</h3>
            <p class="mx-auto mb-8 max-w-sm text-gray-500">
                @if($currentStatus)
                    Belum ada preorder dengan status "{{ $tabs[$currentStatus]['label'] ?? $currentStatus }}".
                @elseif(request()->hasAny(['search','book_id']))
                    Tidak ada hasil untuk pencarian tersebut.
                @else
                    Belum ada data preorder dari anggota.
                @endif
            </p>
            @if($currentStatus || request()->hasAny(['search','book_id']))
                <a href="{{ route('admin.preorders.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-700
                          px-6 py-3 font-bold text-white shadow-md hover:from-blue-700 hover:to-indigo-800">
                    Lihat Semua Preorder
                </a>
            @endif
        </div>
    @endif

</div>

@endsection