@extends('layouts.app')

@section('title', 'Preorder Saya')

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     DATA PREPARATION
══════════════════════════════════════════════════════════════ --}}
@php
    use App\Models\Preorder;

    $userId = auth()->id();

    $statCounts = [
        'all'       => Preorder::where('user_id', $userId)->count(),
        'waiting'   => Preorder::where('user_id', $userId)->where('status', 'waiting')->count(),
        'ready'     => Preorder::where('user_id', $userId)->where('status', 'ready')->count(),
        'confirmed' => Preorder::where('user_id', $userId)->where('status', 'confirmed')->count(),
        'cancelled' => Preorder::where('user_id', $userId)->where('status', 'cancelled')->count(),
        'expired'   => Preorder::where('user_id', $userId)->where('status', 'expired')->count(),
    ];

    $readyPreorders = Preorder::with('book')
        ->where('user_id', $userId)
        ->where('status', 'ready')
        ->orderBy('expired_at')
        ->get();

    $activeCount = $statCounts['waiting'] + $statCounts['ready'];

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

<div class="max-w-5xl mx-auto space-y-5">

    {{-- ╔═══════════════════════════════════════════════════════╗
         ║  HEADER BANNER                                       ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-7 text-white shadow-xl">
        {{-- Decorative blobs --}}
        <div class="pointer-events-none absolute -right-12 -top-12 h-52 w-52 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

            {{-- Title --}}
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-white/20 shadow-inner backdrop-blur-sm">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight">Preorder Saya</h1>
                    <p class="mt-0.5 text-sm text-violet-200">Kelola antrian peminjaman buku Anda</p>
                </div>
            </div>

            {{-- Stats chips --}}
            <div class="flex flex-wrap gap-2">
                <div class="rounded-xl bg-white/15 px-4 py-2.5 text-center backdrop-blur-sm">
                    <p class="text-xl font-extrabold">{{ $statCounts['waiting'] }}</p>
                    <p class="text-xs text-violet-200">Menunggu</p>
                </div>
                @if($statCounts['ready'] > 0)
                    <div class="rounded-xl bg-green-500/40 px-4 py-2.5 text-center ring-2 ring-green-400/60 backdrop-blur-sm">
                        <p class="text-xl font-extrabold">{{ $statCounts['ready'] }}</p>
                        <p class="text-xs text-green-100">Siap! 🎉</p>
                    </div>
                @endif
                <div class="rounded-xl bg-white/15 px-4 py-2.5 text-center backdrop-blur-sm">
                    <p class="text-xl font-extrabold">{{ $statCounts['all'] }}</p>
                    <p class="text-xs text-violet-200">Total</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ╔═══════════════════════════════════════════════════════╗
         ║  ALERT: BUKU SIAP DIPINJAM                          ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    @if($readyPreorders->isNotEmpty())
        <div class="rounded-2xl border-2 border-green-400 bg-green-50 p-5 shadow-md">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-green-500 shadow-md">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-extrabold text-green-900">🎉 Buku Siap Dipinjam!</p>
                    <p class="mt-0.5 text-sm text-green-700">
                        {{ $readyPreorders->count() }} buku sudah tersedia untuk Anda. Konfirmasi sebelum kedaluwarsa!
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($readyPreorders as $rp)
                            <div class="flex items-center gap-2 rounded-xl border border-green-300 bg-white px-3 py-2 shadow-sm">
                                @if($rp->book?->image)
                                    <img src="{{ asset('storage/' . $rp->book->image) }}"
                                         class="h-8 w-6 flex-shrink-0 rounded object-cover">
                                @endif
                                <div class="min-w-0">
                                    <p class="max-w-[9rem] truncate text-sm font-semibold text-gray-800">{{ $rp->book?->name }}</p>
                                    @if($rp->expired_at)
                                        <p class="text-xs font-bold text-red-600">⏰ {{ $rp->expired_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('preorders.confirm', $rp->id) }}"
                                   onclick="return confirm('Apakah Anda jadi meminjam buku ini? Konfirmasi akan membawa Anda ke halaman peminjaman.')"
                                   class="flex-shrink-0 rounded-lg bg-green-600 px-3 py-1 text-xs font-bold text-white shadow transition-all hover:bg-green-700">
                                    Pinjam
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- EC-4: Warning batas preorder aktif --}}
    @if($activeCount >= 3)
        <div class="flex items-start gap-3 rounded-xl border-2 border-orange-300 bg-orange-50 px-5 py-4">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>
                <p class="font-bold text-orange-800">Batas preorder aktif tercapai ({{ $activeCount }}/3)</p>
                <p class="text-sm text-orange-700">Batalkan salah satu preorder di bawah untuk dapat mendaftar preorder baru.</p>
            </div>
        </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-5 py-3.5 shadow-sm">
            <svg class="h-5 w-5 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-semibold text-green-800">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3.5 shadow-sm">
            <svg class="h-5 w-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
        </div>
    @endif

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
                @endphp
                <a href="{{ route('preorders.index', $key ? ['status' => $key] : []) }}"
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
         ║  PREORDER LIST                                       ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    @if($preorders->count() > 0)
        <div class="space-y-4">
            @foreach($preorders as $preorder)
                @php
                    $isReady    = $preorder->status === 'ready';
                    $isWaiting  = $preorder->status === 'waiting';
                    $isActive   = $preorder->isActive();
                    $isExpired  = $preorder->status === 'expired';
                    $isCancelled= $preorder->status === 'cancelled';

                    // EC-2: Posisi dihitung DINAMIS dari created_at, bukan stored queue_position
                    $dynPos   = \App\Models\Preorder::where('book_id', $preorder->book_id)
                        ->whereIn('status', ['waiting', 'ready'])
                        ->where('created_at', '<=', $preorder->created_at)
                        ->count();
                    $dynTotal = \App\Models\Preorder::where('book_id', $preorder->book_id)
                        ->whereIn('status', ['waiting', 'ready'])
                        ->count();

                    $cardBorderClass = match($preorder->status) {
                        'ready'     => 'border-green-300',
                        'waiting'   => 'border-violet-200',
                        'confirmed' => 'border-blue-200',
                        'cancelled' => 'border-gray-200',
                        'expired'   => 'border-red-200',
                        default     => 'border-gray-200',
                    };
                    $badgeClass = match($preorder->status) {
                        'waiting'   => 'bg-amber-100 text-amber-800 ring-1 ring-amber-300',
                        'ready'     => 'bg-green-100 text-green-800 ring-1 ring-green-400',
                        'confirmed' => 'bg-blue-100 text-blue-800 ring-1 ring-blue-300',
                        'cancelled' => 'bg-gray-100 text-gray-600 ring-1 ring-gray-300',
                        'expired'   => 'bg-red-100 text-red-800 ring-1 ring-red-300',
                        default     => 'bg-gray-100 text-gray-700',
                    };
                @endphp

                <div class="overflow-hidden rounded-2xl border-2 bg-white shadow-md transition-shadow hover:shadow-lg {{ $cardBorderClass }}">
                    {{-- Ready: top bar animasi --}}
                    @if($isReady)
                        <div class="h-1 animate-pulse bg-gradient-to-r from-green-400 via-emerald-400 to-teal-400"></div>
                    @endif

                    <div class="p-5">
                        <div class="flex flex-col gap-4 sm:flex-row">

                            {{-- Cover --}}
                            <a href="{{ route('books.show', $preorder->book?->slug ?? '#') }}"
                               class="flex-shrink-0">
                                <div class="h-24 w-16 overflow-hidden rounded-xl border border-gray-100 shadow-md">
                                    @if($preorder->book?->image)
                                        <img src="{{ asset('storage/' . $preorder->book->image) }}"
                                             alt="{{ $preorder->book->name }}"
                                             class="h-full w-full object-cover transition-transform hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-violet-100 to-purple-200">
                                            <svg class="h-8 w-8 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <div class="mb-1.5 flex flex-wrap items-start justify-between gap-2">
                                    <a href="{{ route('books.show', $preorder->book?->slug ?? '#') }}"
                                       class="text-lg font-extrabold text-gray-900 transition-colors hover:text-violet-700">
                                        {{ $preorder->book?->name ?? 'Buku tidak tersedia' }}
                                    </a>
                                    <span class="flex-shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                        {{ $preorder->status_label }}
                                    </span>
                                </div>

                                <p class="mb-3 text-sm text-gray-500">{{ $preorder->book?->writer ?? '—' }}</p>

                                {{-- Meta chips --}}
                                <div class="flex flex-wrap gap-2 text-xs">
                                    @if($isActive)
                                        <span class="flex items-center gap-1 rounded-lg bg-violet-100 px-2.5 py-1 font-bold text-violet-800">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Antrian #{{ $dynPos }}{{ $dynTotal > 0 ? ' / ' . $dynTotal : '' }}
                                        </span>
                                    @endif

                                    <span class="flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-gray-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Rencana: {{ $preorder->expected_borrow_date->format('d M Y') }}
                                    </span>

                                    <span class="flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-gray-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $preorder->created_at->format('d M Y') }}
                                    </span>

                                    @if($isReady && $preorder->expired_at)
                                        <span class="flex animate-pulse items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1 font-bold text-red-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                            </svg>
                                            Exp: {{ $preorder->expired_at->format('d M, H:i') }}
                                        </span>
                                    @endif
                                </div>

                                @if($preorder->notes)
                                    <p class="mt-2.5 rounded-lg bg-gray-50 px-3 py-2 text-xs italic text-gray-500">
                                        "{{ $preorder->notes }}"
                                    </p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-shrink-0 flex-col gap-2 sm:items-end sm:justify-start">
                                @if($isReady)
                                    <a href="{{ route('preorders.confirm', $preorder->id) }}"
                                       onclick="return confirm('Apakah Anda jadi meminjam buku ini?\nKonfirmasi akan membawa Anda ke halaman peminjaman.')"
                                       class="flex items-center gap-2 whitespace-nowrap rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:from-green-600 hover:to-emerald-700 hover:shadow-lg">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Pinjam Sekarang
                                    </a>
                                @endif

                                @if($isActive)
                                    <button type="button"
                                        onclick="openEditModal('{{ $preorder->id }}', '{{ $preorder->expected_borrow_date->format('Y-m-d') }}', @js($preorder->notes ?? ''))"
                                        class="flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition-all hover:bg-amber-100">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    <form action="{{ route('preorders.cancel', $preorder->id) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin membatalkan preorder ini?\nAntrian di bawah Anda akan naik posisi.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex w-full items-center gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition-all hover:bg-red-100">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- EC-2: Queue progress bar (hitung dinamis) --}}
                        @if($isWaiting && $dynTotal > 0)
                            @php $pct = max(5, (int) round(($dynTotal - $dynPos + 1) / $dynTotal * 100)); @endphp
                            <div class="mt-4 border-t border-gray-100 pt-4">
                                <div class="mb-1.5 flex justify-between text-xs font-medium text-gray-500">
                                    <span>Progres antrian</span>
                                    <span>Posisi {{ $dynPos }} dari {{ $dynTotal }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-purple-600 transition-all duration-1000"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                                @if($dynPos === 1)
                                    <p class="mt-1.5 text-xs font-semibold text-violet-700">🎯 Anda berikutnya saat buku dikembalikan!</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div>{{ $preorders->appends(request()->query())->links() }}</div>

    @else
        {{-- Empty State --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-16 text-center shadow-md">
            <div class="mx-auto mb-6 flex h-28 w-28 items-center justify-center rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 shadow-inner">
                <svg class="h-14 w-14 text-violet-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="mb-2 text-2xl font-extrabold text-gray-900">Tidak Ada Preorder</h3>
            <p class="mx-auto mb-8 max-w-sm text-gray-500">
                @if($currentStatus)
                    Belum ada preorder dengan status "{{ $tabs[$currentStatus]['label'] ?? $currentStatus }}".
                @else
                    Anda belum mendaftar antrian untuk buku apapun.
                @endif
            </p>
            <div class="flex items-center justify-center gap-3">
                @if($currentStatus)
                    <a href="{{ route('preorders.index') }}"
                       class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-200">
                        Lihat Semua
                    </a>
                @endif
                <a href="{{ route('books.catalog') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-700 px-6 py-3 font-bold text-white shadow-md transition-all hover:from-violet-700 hover:to-purple-800 hover:shadow-lg">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Jelajahi Buku
                </a>
            </div>
        </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════
     EDIT MODAL
══════════════════════════════════════════════════════════════ --}}
<div id="editModal" style="display:none;position:fixed;inset:0;z-index:9999;">
    <div id="editBackdrop"
         style="position:absolute;inset:0;background:rgba(17,24,39,0.6);backdrop-filter:blur(4px)"></div>

    <div style="position:relative;display:flex;align-items:center;justify-content:center;min-height:100%;padding:1rem;pointer-events:none;">
        <div id="editPanel"
             style="pointer-events:all;background:#fff;border-radius:1.25rem;box-shadow:0 25px 60px rgba(0,0,0,0.25);width:100%;max-width:28rem;transform:scale(0.92) translateY(12px);opacity:0;transition:transform .28s cubic-bezier(.34,1.56,.64,1),opacity .22s ease;">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9);padding:1.25rem 1.5rem;border-radius:1.25rem 1.25rem 0 0;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.625rem;">
                    <div style="background:rgba(255,255,255,.2);border-radius:.625rem;padding:.5rem;">
                        <svg style="width:1.2rem;height:1.2rem;stroke:#fff;fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <p style="color:#fff;font-weight:700;font-size:1.05rem;margin:0;">Edit Preorder</p>
                </div>
                <button onclick="closeEditModal()" type="button"
                    style="background:rgba(255,255,255,.15);border:none;border-radius:.5rem;padding:.4rem;cursor:pointer;display:flex;"
                    onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
                    <svg style="width:1.2rem;height:1.2rem;stroke:#fff;fill:none;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding:1.5rem;">
                <input type="hidden" id="editPreorderId">

                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:.875rem;font-weight:700;color:#374151;margin-bottom:.5rem;">
                        Rencana Tanggal Pinjam <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="date" id="editDate"
                        min="{{ date('Y-m-d') }}"
                        style="width:100%;padding:.75rem 1rem;border:2px solid #e5e7eb;border-radius:.75rem;font-size:.9rem;outline:none;box-sizing:border-box;font-family:inherit;"
                        onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;font-size:.875rem;font-weight:700;color:#374151;margin-bottom:.5rem;">
                        Catatan <span style="font-weight:400;color:#9ca3af;">(opsional)</span>
                    </label>
                    <textarea id="editNotes" rows="3" maxlength="500" placeholder="Tambahkan catatan..."
                        style="width:100%;padding:.75rem 1rem;border:2px solid #e5e7eb;border-radius:.75rem;font-size:.875rem;outline:none;resize:vertical;box-sizing:border-box;font-family:inherit;"
                        onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                </div>

                <div id="editError"
                     style="display:none;background:#fef2f2;color:#b91c1c;padding:.75rem 1rem;border-radius:.625rem;font-size:.875rem;margin-bottom:1rem;border:1px solid #fecaca;"></div>
                <div id="editSuccess"
                     style="display:none;background:#f0fdf4;color:#15803d;padding:.75rem 1rem;border-radius:.625rem;font-size:.875rem;margin-bottom:1rem;border:1px solid #bbf7d0;"></div>

                <div style="display:flex;gap:.75rem;">
                    <button type="button" onclick="closeEditModal()"
                        style="flex:1;padding:.75rem;background:#f3f4f6;color:#374151;border:none;border-radius:.875rem;font-weight:600;cursor:pointer;font-size:.9rem;font-family:inherit;"
                        onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        Batal
                    </button>
                    <button type="button" onclick="submitEdit()" id="editSubmitBtn"
                        style="flex:1.5;padding:.75rem;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border:none;border-radius:.875rem;font-weight:700;cursor:pointer;font-size:.9rem;font-family:inherit;box-shadow:0 4px 12px rgba(124,58,237,.3);"
                        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ── Edit Modal ──────────────────────────────────────────────── */
function openEditModal(id, date, notes) {
    document.getElementById('editPreorderId').value    = id;
    document.getElementById('editDate').value          = date;
    document.getElementById('editNotes').value         = notes || '';
    document.getElementById('editError').style.display = 'none';
    document.getElementById('editSuccess').style.display = 'none';

    var modal = document.getElementById('editModal');
    var panel = document.getElementById('editPanel');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
        panel.style.transform = 'scale(1) translateY(0)';
        panel.style.opacity   = '1';
        document.getElementById('editDate').focus();
    }, 15);
}

function closeEditModal() {
    var panel = document.getElementById('editPanel');
    panel.style.transform = 'scale(0.92) translateY(12px)';
    panel.style.opacity   = '0';
    setTimeout(function() {
        document.getElementById('editModal').style.display = 'none';
        document.body.style.overflow = '';
    }, 230);
}

function submitEdit() {
    var id    = document.getElementById('editPreorderId').value;
    var date  = document.getElementById('editDate').value;
    var notes = document.getElementById('editNotes').value;
    var errEl = document.getElementById('editError');
    var sucEl = document.getElementById('editSuccess');
    var btn   = document.getElementById('editSubmitBtn');

    errEl.style.display = 'none';
    sucEl.style.display = 'none';

    if (!date) {
        errEl.textContent   = 'Tanggal pinjam wajib diisi.';
        errEl.style.display = 'block';
        return;
    }

    btn.textContent = 'Menyimpan…';
    btn.disabled    = true;

    fetch('/preorders/' + id, {
        method : 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept'       : 'application/json',
        },
        body: JSON.stringify({ expected_borrow_date: date, notes: notes })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) {
            sucEl.textContent   = data.message || 'Berhasil disimpan!';
            sucEl.style.display = 'block';
            setTimeout(function() { closeEditModal(); window.location.reload(); }, 800);
        } else {
            errEl.textContent   = data.message || 'Terjadi kesalahan.';
            errEl.style.display = 'block';
        }
    })
    .catch(function() {
        errEl.textContent   = 'Gagal menghubungi server.';
        errEl.style.display = 'block';
    })
    .finally(function() {
        btn.textContent = 'Simpan Perubahan';
        btn.disabled    = false;
    });
}

document.getElementById('editBackdrop').addEventListener('click', closeEditModal);
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeEditModal(); });
</script>
@endpush

@endsection