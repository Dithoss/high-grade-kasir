@extends('layouts.app')

@section('title', 'Detail Preorder #' . str_pad($preorder->id, 3, '0', STR_PAD_LEFT))

@section('content')

@php
    $dynPos = \App\Models\Preorder::where('book_id', $preorder->book_id)
        ->whereIn('status', ['waiting', 'ready'])
        ->where('created_at', '<=', $preorder->created_at)
        ->count();

    $dynTotal = \App\Models\Preorder::where('book_id', $preorder->book_id)
        ->whereIn('status', ['waiting', 'ready'])
        ->count();

    $queueAhead = \App\Models\Preorder::where('book_id', $preorder->book_id)
        ->whereIn('status', ['waiting', 'ready'])
        ->where('created_at', '<', $preorder->created_at)
        ->get();

    $isReady    = $preorder->status === 'ready';
    $isWaiting  = $preorder->status === 'waiting';
    $isActive   = $preorder->isActive();
    $isExpired  = $preorder->status === 'expired';
    $isCancelled = $preorder->status === 'cancelled';
    $isConfirmed = $preorder->status === 'confirmed';

    $statusConfig = match($preorder->status) {
        'waiting'   => ['label' => 'Menunggu',    'bg' => 'bg-amber-100',  'text' => 'text-amber-800',  'ring' => 'ring-amber-300',  'icon_bg' => 'bg-amber-500',  'bar' => 'from-amber-400 to-yellow-400'],
        'ready'     => ['label' => 'Siap Pinjam', 'bg' => 'bg-green-100',  'text' => 'text-green-800',  'ring' => 'ring-green-400',  'icon_bg' => 'bg-green-500',  'bar' => 'from-green-400 to-emerald-500'],
        'confirmed' => ['label' => 'Dipinjam',    'bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'ring' => 'ring-blue-300',   'icon_bg' => 'bg-blue-500',   'bar' => 'from-blue-400 to-indigo-500'],
        'cancelled' => ['label' => 'Dibatalkan',  'bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'ring' => 'ring-gray-300',   'icon_bg' => 'bg-gray-400',   'bar' => 'from-gray-300 to-gray-400'],
        'expired'   => ['label' => 'Kedaluwarsa', 'bg' => 'bg-red-100',    'text' => 'text-red-800',    'ring' => 'ring-red-300',    'icon_bg' => 'bg-red-500',    'bar' => 'from-red-400 to-rose-500'],
        default     => ['label' => $preorder->status, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'ring' => 'ring-gray-200',   'icon_bg' => 'bg-gray-400',   'bar' => 'from-gray-300 to-gray-400'],
    };
@endphp

<div class="max-w-5xl mx-auto space-y-6">

    {{-- ── Breadcrumb ── --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.preorders.index') }}" class="hover:text-blue-600 transition-colors">
            Manajemen Preorder
        </a>
        <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="font-semibold text-gray-800">
            PO-{{ str_pad($preorder->id, 3, '0', STR_PAD_LEFT) }}
        </span>
    </div>

    {{-- Flash --}}
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
         ║  STATUS HEADER CARD                                  ║
         ╚═══════════════════════════════════════════════════════╝ --}}
    <div class="overflow-hidden rounded-2xl border-2 bg-white shadow-md
        {{ $isReady ? 'border-green-300' : ($isWaiting ? 'border-violet-200' : ($isConfirmed ? 'border-blue-200' : 'border-gray-200')) }}">

        {{-- animated top bar for ready --}}
        @if($isReady)
            <div class="h-1.5 animate-pulse bg-gradient-to-r from-green-400 via-emerald-400 to-teal-400"></div>
        @else
            <div class="h-1.5 bg-gradient-to-r {{ $statusConfig['bar'] }}"></div>
        @endif

        <div class="p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                {{-- Kiri: ID + Status --}}
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-2xl font-extrabold text-gray-900">
                            PO-{{ str_pad($preorder->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="rounded-full px-3 py-1 text-sm font-bold ring-1
                            {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['ring'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">
                        Dibuat {{ $preorder->created_at->format('d M Y, H:i') }}
                        <span class="text-gray-300 mx-1">·</span>
                        {{ $preorder->created_at->diffForHumans() }}
                    </p>
                    @if($preorder->notes)
                        <div class="mt-3 flex items-start gap-2 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600 max-w-md">
                            <svg class="h-4 w-4 mt-0.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            <span class="italic">"{{ $preorder->notes }}"</span>
                        </div>
                    @endif
                </div>

                {{-- Kanan: Action buttons --}}
                <div class="flex flex-wrap gap-2">
                    @if($isWaiting)
                        <form action="{{ route('admin.preorders.markReady', $preorder->id) }}"
                              method="POST"
                              onsubmit="return confirm('Tandai preorder ini sebagai siap dipinjam?\nAnggota akan mendapat notifikasi.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600
                                       px-5 py-2.5 text-sm font-bold text-white shadow-md hover:from-green-600 hover:to-emerald-700 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Tandai Siap Dipinjam
                            </button>
                        </form>
                    @endif

                    @if($isReady)
                        <form action="{{ route('admin.preorders.notify', $preorder->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-amber-600 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                Kirim Ulang Notifikasi
                            </button>
                        </form>
                    @endif

                    @if($isActive)
                        <form action="{{ route('admin.preorders.cancel', $preorder->id) }}"
                              method="POST"
                              onsubmit="return confirm('Batalkan preorder ini?\nAntrian anggota lain akan bergeser naik.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="flex items-center gap-2 rounded-xl border border-red-300 bg-red-50 px-5 py-2.5
                                       text-sm font-bold text-red-700 hover:bg-red-100 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Batalkan Preorder
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.preorders.index') }}"
                       class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-5 py-2.5
                              text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ╔═══════════════════════════════════════════════════════╗
             ║  KOLOM KIRI (2/3): Buku + Antrian                    ║
             ╚═══════════════════════════════════════════════════════╝ --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Informasi Buku --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md">
                <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-gray-800">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100">
                        <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    Informasi Buku
                </h2>

                <div class="flex gap-5">
                    {{-- Cover --}}
                    <a href="{{ route('books.show', $preorder->book?->slug ?? '#') }}"
                       class="flex-shrink-0">
                        <div class="h-32 w-24 overflow-hidden rounded-xl border border-gray-100 shadow-md">
                            @if($preorder->book?->image)
                                <img src="{{ asset('storage/' . $preorder->book->image) }}"
                                     alt="{{ $preorder->book->name }}"
                                     class="h-full w-full object-cover transition-transform hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-violet-100 to-purple-200">
                                    <svg class="h-10 w-10 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </a>

                    {{-- Detail buku --}}
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('books.show', $preorder->book?->slug ?? '#') }}"
                           class="text-xl font-extrabold text-gray-900 hover:text-violet-700 transition-colors line-clamp-2">
                            {{ $preorder->book?->name ?? '—' }}
                        </a>
                        <p class="mt-1 text-sm text-gray-500">{{ $preorder->book?->writer ?? '—' }}</p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            {{-- Stok --}}
                            <span class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold
                                {{ ($preorder->book?->stock ?? 0) > 0
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-700' }}">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Stok: {{ $preorder->book?->stock ?? 0 }}
                            </span>

                            {{-- Kategori --}}
                            @if($preorder->book?->category)
                                <span class="flex items-center gap-1.5 rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-semibold text-blue-800">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ $preorder->book->category->name }}
                                </span>
                            @endif

                            {{-- Total antri buku ini --}}
                            <span class="flex items-center gap-1.5 rounded-lg bg-violet-100 px-3 py-1.5 text-xs font-semibold text-violet-800">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $dynTotal }} orang mengantri
                            </span>
                        </div>

                        {{-- Tanggal --}}
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-gray-50 px-4 py-3">
                                <p class="text-xs text-gray-400 mb-0.5">Rencana Pinjam</p>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ $preorder->expected_borrow_date->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $preorder->expected_borrow_date->diffForHumans() }}</p>
                            </div>
                            @if($isReady && $preorder->expired_at)
                                <div class="rounded-xl bg-red-50 px-4 py-3 ring-1 ring-red-200">
                                    <p class="text-xs text-red-400 mb-0.5">Kedaluwarsa</p>
                                    <p class="text-sm font-bold text-red-700">
                                        {{ $preorder->expired_at->format('d M Y, H:i') }}
                                    </p>
                                    <p class="text-xs font-semibold text-red-500 animate-pulse">
                                        {{ $preorder->expired_at->diffForHumans() }}
                                    </p>
                                </div>
                            @elseif($preorder->expired_at)
                                <div class="rounded-xl bg-gray-50 px-4 py-3">
                                    <p class="text-xs text-gray-400 mb-0.5">Kedaluwarsa</p>
                                    <p class="text-sm font-bold text-gray-500">
                                        {{ $preorder->expired_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Posisi & Antrian Buku --}}
            @if($isActive && $dynTotal > 0)
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md">
                    <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-gray-800">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        Antrian Buku "{{ Str::limit($preorder->book?->name, 30) }}"
                    </h2>

                    {{-- Progress bar --}}
                    @php $pct = max(5, (int) round(($dynTotal - $dynPos + 1) / $dynTotal * 100)); @endphp
                    <div class="mb-4">
                        <div class="mb-1.5 flex justify-between text-xs font-medium text-gray-500">
                            <span>Posisi dalam antrian</span>
                            <span class="font-bold text-violet-700">{{ $dynPos }} / {{ $dynTotal }}</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-purple-600 transition-all duration-1000"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Daftar anggota mengantri --}}
                    <div class="space-y-2">
                        @foreach(\App\Models\Preorder::with('user')
                            ->where('book_id', $preorder->book_id)
                            ->whereIn('status', ['waiting','ready'])
                            ->orderBy('created_at')
                            ->get() as $i => $qItem)
                            <div class="flex items-center gap-3 rounded-xl px-4 py-3
                                {{ $qItem->id === $preorder->id
                                    ? 'bg-violet-50 ring-2 ring-violet-300'
                                    : 'bg-gray-50' }}">
                                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-xs font-extrabold
                                    {{ $i === 0 ? 'bg-amber-400 text-white' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $i + 1 }}
                                </span>
                                <div class="h-7 w-7 flex-shrink-0 overflow-hidden rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    @if($qItem->user?->image)
                                        <img src="{{ asset('storage/' . $qItem->user->image) }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-white">
                                            {{ strtoupper(substr($qItem->user?->name ?? 'U', 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">
                                        {{ $qItem->user?->name ?? '—' }}
                                        @if($qItem->id === $preorder->id)
                                            <span class="ml-1 text-xs font-bold text-violet-600">(ini)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">Daftar {{ $qItem->created_at->format('d M Y') }}</p>
                                </div>
                                <span class="flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-bold
                                    {{ $qItem->status === 'ready' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $qItem->status === 'ready' ? 'Siap' : 'Menunggu' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ╔═══════════════════════════════════════════════════════╗
             ║  KOLOM KANAN (1/3): Anggota + Timeline               ║
             ╚═══════════════════════════════════════════════════════╝ --}}
        <div class="space-y-6">

            {{-- Informasi Anggota --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md">
                <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-gray-800">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    Anggota
                </h2>

                <div class="flex flex-col items-center text-center">
                    <div class="h-16 w-16 overflow-hidden rounded-full bg-gradient-to-br from-blue-500 to-blue-700
                                flex items-center justify-center shadow-md ring-4 ring-blue-100 mb-3">
                        @if($preorder->user?->image)
                            <img src="{{ asset('storage/' . $preorder->user->image) }}"
                                 class="h-full w-full object-cover">
                        @else
                            <span class="text-2xl font-extrabold text-white">
                                {{ strtoupper(substr($preorder->user?->name ?? 'U', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <p class="text-lg font-extrabold text-gray-900">{{ $preorder->user?->name ?? '—' }}</p>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $preorder->user?->email ?? '—' }}</p>

                    {{-- Preorder aktif user ini --}}
                    @php
                        $userActiveCount = \App\Models\Preorder::where('user_id', $preorder->user_id)
                            ->whereIn('status', ['waiting','ready'])
                            ->count();
                        $userTotalCount  = \App\Models\Preorder::where('user_id', $preorder->user_id)->count();
                    @endphp
                    <div class="mt-4 w-full grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-violet-50 px-3 py-2.5 text-center">
                            <p class="text-lg font-extrabold text-violet-700">{{ $userActiveCount }}</p>
                            <p class="text-xs text-violet-500">Aktif</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-3 py-2.5 text-center">
                            <p class="text-lg font-extrabold text-gray-700">{{ $userTotalCount }}</p>
                            <p class="text-xs text-gray-500">Total</p>
                        </div>
                    </div>

                    <div class="mt-4 w-full flex gap-2">
                        <a href="{{ route('users.show', $preorder->user_id) }}"
                           class="flex-1 rounded-xl border border-blue-200 bg-blue-50 py-2 text-xs font-semibold
                                  text-blue-700 hover:bg-blue-100 transition-colors text-center">
                            Profil
                        </a>
                        <a href="{{ route('admin.preorders.index', ['search' => $preorder->user?->email]) }}"
                           class="flex-1 rounded-xl border border-gray-200 bg-gray-50 py-2 text-xs font-semibold
                                  text-gray-600 hover:bg-gray-100 transition-colors text-center">
                            Preorder Lain
                        </a>
                    </div>
                </div>
            </div>

            {{-- Timeline Status --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md">
                <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-gray-800">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-100">
                        <svg class="h-4 w-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    Timeline
                </h2>

                @php
                    $timeline = [
                        [
                            'label'  => 'Preorder Dibuat',
                            'time'   => $preorder->created_at,
                            'done'   => true,
                            'color'  => 'bg-green-500',
                            'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        ],
                        [
                            'label'  => 'Ditandai Siap',
                            'time'   => in_array($preorder->status, ['ready','confirmed','expired']) ? ($preorder->updated_at) : null,
                            'done'   => in_array($preorder->status, ['ready','confirmed','expired']),
                            'color'  => 'bg-emerald-500',
                            'icon'   => 'M5 13l4 4L19 7',
                        ],
                        [
                            'label'  => 'Dikonfirmasi Anggota',
                            'time'   => $isConfirmed ? $preorder->updated_at : null,
                            'done'   => $isConfirmed,
                            'color'  => 'bg-blue-500',
                            'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                        [
                            'label'  => $isCancelled ? 'Dibatalkan' : ($isExpired ? 'Kedaluwarsa' : 'Selesai'),
                            'time'   => ($isCancelled || $isExpired) ? $preorder->updated_at : null,
                            'done'   => $isCancelled || $isExpired || $isConfirmed,
                            'color'  => $isCancelled ? 'bg-red-400' : ($isExpired ? 'bg-orange-400' : 'bg-teal-500'),
                            'icon'   => $isCancelled
                                ? 'M6 18L18 6M6 6l12 12'
                                : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                    ];
                @endphp

                <div class="space-y-0">
                    @foreach($timeline as $i => $step)
                        <div class="flex gap-3">
                            {{-- Icon + line --}}
                            <div class="flex flex-col items-center">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full
                                    {{ $step['done'] ? $step['color'] : 'bg-gray-200' }} shadow-sm">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                                    </svg>
                                </div>
                                @if(!$loop->last)
                                    <div class="w-px flex-1 my-1 {{ $step['done'] ? 'bg-gray-300' : 'bg-gray-100' }}"></div>
                                @endif
                            </div>
                            {{-- Label --}}
                            <div class="pb-5 pt-1 min-w-0">
                                <p class="text-sm font-semibold {{ $step['done'] ? 'text-gray-800' : 'text-gray-400' }}">
                                    {{ $step['label'] }}
                                </p>
                                @if($step['time'])
                                    <p class="text-xs text-gray-400">{{ $step['time']->format('d M Y, H:i') }}</p>
                                @elseif(!$step['done'])
                                    <p class="text-xs text-gray-300">Belum terjadi</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@endsection