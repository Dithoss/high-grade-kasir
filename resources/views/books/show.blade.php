@extends('layouts.app')

@section('title', 'Detail Buku — ' . $book->name)

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     EDGE CASE CHECKS (computed di blade, logika berat di controller)
══════════════════════════════════════════════════════════════ --}}
@php
    $authUser = auth()->user();

    // ── EC-5: Sudah meminjam buku yang sama? ───────────────────
    $alreadyBorrowing = false;
    if ($authUser) {
        $alreadyBorrowing = \App\Models\Transaction::whereHas('items', fn($q) => $q->where('book_id', $book->id))
            ->where('user_id', $authUser->id)
            ->whereIn('status', ['borrowed', 'return_requested'])
            ->exists();
    }

    // ── EC-6: Ada denda aktif? ─────────────────────────────────
    $hasActiveFine = false;
    if ($authUser) {
        $hasActiveFine = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $authUser->id))
            ->whereIn('status', ['unpaid', 'pending_confirmation'])
            ->exists();
    }

    // ── EC-4: Batas maks preorder aktif (3) ────────────────────
    $activePreorderCount = 0;
    if ($authUser) {
        $activePreorderCount = \App\Models\Preorder::where('user_id', $authUser->id)
            ->whereIn('status', ['waiting', 'ready'])
            ->count();
    }
    $maxPreorders    = 3;
    $preorderBlocked = $activePreorderCount >= $maxPreorders;

    // ── EC-5 lanjut: sudah punya preorder aktif untuk buku ini? ─
    $existingPreorder = null;
    if ($authUser) {
        $existingPreorder = \App\Models\Preorder::where('user_id', $authUser->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['waiting', 'ready'])
            ->first();
    }

    // ── EC-2: Hitung posisi antrian DINAMIS (by created_at, bukan stored) ─
    $totalQueue = \App\Models\Preorder::where('book_id', $book->id)
        ->whereIn('status', ['waiting', 'ready'])
        ->count();

    $myQueuePosition = null;
    if ($existingPreorder) {
        $myQueuePosition = \App\Models\Preorder::where('book_id', $book->id)
            ->whereIn('status', ['waiting', 'ready'])
            ->where('created_at', '<=', $existingPreorder->created_at)
            ->count();
    }

    // ── EC-10/17: Buku dalam kondisi normal? (admin bisa flag book) ─
    // Asumsi ada field `is_available` atau cek via status
    // Minimal cek soft-delete sudah di-handle Eloquent
    $bookIsNormal = !isset($book->is_damaged) || !$book->is_damaged;
@endphp

<div class="max-w-7xl mx-auto">

    {{-- ── BANNER PERINGATAN GLOBAL ─────────────────────────────── --}}
    @if($hasActiveFine)
        <div class="mb-5 flex items-start gap-3 px-5 py-4 bg-red-50 border-2 border-red-300 rounded-xl shadow-sm">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-bold text-red-800 text-sm">Akun Anda memiliki denda belum terbayar</p>
                <p class="text-red-700 text-xs mt-0.5">Lunasi denda terlebih dahulu untuk dapat meminjam atau preorder buku.</p>
            </div>
            <a href="{{ route('fines.index') }}"
               class="flex-shrink-0 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition-all">
                Bayar Denda
            </a>
        </div>
    @endif

    @if($alreadyBorrowing)
        <div class="mb-5 flex items-start gap-3 px-5 py-4 bg-blue-50 border-2 border-blue-300 rounded-xl shadow-sm">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-blue-800 text-sm">Anda sedang meminjam buku ini</p>
                <p class="text-blue-700 text-xs mt-0.5">Kembalikan buku yang sedang dipinjam sebelum meminjam atau preorder lagi.</p>
            </div>
        </div>
    @endif

    {{-- ── HEADER CARD ──────────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Detail Buku</h2>
                    <p class="text-blue-100 text-sm">Informasi lengkap buku</p>
                </div>
            </div>
            <a href="{{ route('books.index') }}"
               class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- ── CONTENT CARD ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-b-xl shadow-lg p-8 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- ════ LEFT: IMAGE + BARCODE ════ --}}
            <div class="space-y-6">

                {{-- Book Image --}}
                <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl p-8 flex items-center justify-center shadow-inner relative" style="min-height:400px;">
                    @if($book->image)
                        <img src="{{ asset('storage/' . $book->image) }}"
                             alt="{{ $book->name }}"
                             class="max-w-full max-h-full object-contain rounded-lg">
                    @else
                        <div class="text-center text-gray-400">
                            <svg class="w-32 h-32 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="font-medium text-lg">Tidak ada gambar</p>
                        </div>
                    @endif

                    {{-- EC-16/17: Badge buku rusak/hilang --}}
                    @if(isset($book->is_damaged) && $book->is_damaged)
                        <div class="absolute top-3 left-3 px-3 py-1.5 bg-orange-500 text-white text-xs font-bold rounded-lg shadow-lg flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            Kondisi Rusak
                        </div>
                    @endif

                    {{-- Wishlist button (Large — top right) --}}
                    @role('user')
                    <button
                        id="wishlistBtnLarge"
                        onclick="toggleWishlistLarge('{{ $book->slug }}')"
                        class="absolute top-4 right-4 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center space-x-2
                            {{ $authUser && $authUser->wishlists()->where('book_id', $book->id)->exists()
                                ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-6 h-6 {{ $authUser && $authUser->wishlists()->where('book_id', $book->id)->exists() ? 'fill-current' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span id="wishlistTextLarge">
                            {{ $authUser && $authUser->wishlists()->where('book_id', $book->id)->exists() ? 'Tersimpan' : 'Simpan' }}
                        </span>
                    </button>
                    @endrole
                </div>

                {{-- Barcode --}}
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-base font-bold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            Barcode Buku
                        </p>
                        <button onclick="copyBarcode()"
                                class="p-2 hover:bg-indigo-100 rounded-lg transition-colors group"
                                title="Copy barcode">
                            <svg class="w-5 h-5 text-indigo-600 group-hover:text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-inner">
                        <div class="w-full overflow-x-auto flex justify-center mb-3">
                            <svg id="barcode" class="max-w-full"></svg>
                        </div>
                        <p class="text-center font-mono text-base font-semibold text-gray-900">{{ $book->barcode }}</p>
                    </div>
                </div>
            </div>

            {{-- ════ RIGHT: DETAIL + ACTIONS ════ --}}
            <div class="space-y-6">

                {{-- Title & Author --}}
                <div class="pb-6 border-b-2 border-gray-100">
                    <h1 class="text-4xl font-bold text-gray-900 mb-3 leading-tight">{{ $book->name }}</h1>
                    <div class="flex items-center space-x-2 text-gray-600">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span class="text-xl font-medium">{{ $book->writer }}</span>
                    </div>
                </div>

                {{-- Synopsis --}}
                @if($book->sypnosis)
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-blue-200 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Sinopsis
                    </h3>
                    <div class="text-gray-700 leading-relaxed whitespace-pre-line text-sm">{{ $book->sypnosis }}</div>
                </div>
                @endif

                {{-- Details Grid --}}
                <div class="space-y-4">

                    {{-- Stock --}}
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border-2 border-green-200 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 bg-green-100 rounded-xl">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Stok Tersedia</p>
                                    <p class="text-3xl font-bold text-gray-900">
                                        {{ $book->stock }}
                                        <span class="text-lg font-normal text-gray-600">unit</span>
                                    </p>
                                </div>
                            </div>
                            <span class="px-5 py-3 rounded-xl text-base font-bold shadow-md
                                {{ $book->stock > 5 ? 'bg-green-500 text-white' : ($book->stock > 0 ? 'bg-yellow-500 text-white' : 'bg-red-500 text-white') }}">
                                @if($book->stock > 5)   Stok Banyak
                                @elseif($book->stock > 0) Stok Terbatas
                                @else                    Habis
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Queue info (hanya jika habis) --}}
                    @if($book->stock <= 0 && $totalQueue > 0)
                        <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-amber-900">{{ $totalQueue }} orang dalam antrian</p>
                                <p class="text-xs text-amber-700">Preorder sekarang untuk masuk antrian ke-{{ $totalQueue + 1 }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Category & Writer --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-5 border-2 border-purple-200">
                            <div class="flex items-start space-x-3">
                                <div class="p-2 bg-purple-100 rounded-lg mt-1">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 mb-1">Kategori</p>
                                    <p class="text-lg font-bold text-gray-900 break-words">{{ $book->category?->name ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-5 border-2 border-amber-200">
                            <div class="flex items-start space-x-3">
                                <div class="p-2 bg-amber-100 rounded-lg mt-1">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 mb-1">Penulis</p>
                                    <p class="text-lg font-bold text-gray-900 break-words">{{ $book->writer }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Barcode Quick View --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-5 border-2 border-indigo-200">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">Kode Barcode</p>
                                <p class="text-lg font-bold text-gray-900 font-mono">{{ $book->barcode }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Timestamps --}}
                    <div class="bg-gray-50 rounded-xl p-5 border-2 border-gray-200">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Ditambahkan
                                </p>
                                <p class="font-bold text-gray-900">{{ $book->created_at->format('d M Y') }}</p>
                                <p class="text-sm text-gray-600">{{ $book->created_at->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Terakhir Update
                                </p>
                                <p class="font-bold text-gray-900">{{ $book->updated_at->format('d M Y') }}</p>
                                <p class="text-sm text-gray-600">{{ $book->updated_at->format('H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════
                     ACTION BUTTONS — dengan semua EC di sini
                ══════════════════════════════════════════════════ --}}
                <div class="flex flex-col gap-3 pt-6 border-t-2 border-gray-100">

                    {{-- ── USER ROLE ── --}}
                    @role('user')

                        {{-- Wishlist Button --}}
                        <button
                            id="wishlistBtn"
                            onclick="toggleWishlist('{{ $book->slug }}')"
                            class="w-full px-6 py-4 rounded-xl font-bold transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5
                                {{ $authUser && $authUser->wishlists()->where('book_id', $book->id)->exists()
                                    ? 'bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white'
                                    : 'bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700' }}">
                            <svg class="w-6 h-6 {{ $authUser && $authUser->wishlists()->where('book_id', $book->id)->exists() ? 'fill-current' : '' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span id="wishlistText">
                                {{ $authUser && $authUser->wishlists()->where('book_id', $book->id)->exists() ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}
                            </span>
                        </button>

                        {{-- ── EC-5: Sudah meminjam buku ini ── --}}
                        @if($alreadyBorrowing)
                            <div class="w-full px-6 py-4 bg-blue-50 border-2 border-blue-200 text-blue-700 rounded-xl font-bold flex items-center justify-center space-x-2 cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Sedang Dipinjam</span>
                            </div>

                        {{-- ── EC-6: Ada denda aktif ── --}}
                        @elseif($hasActiveFine)
                            <div class="w-full px-6 py-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl font-bold flex items-center justify-center space-x-2 cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <span>Lunasi Denda Dulu</span>
                            </div>

                        {{-- ── EC-16: Buku rusak ── --}}
                        @elseif(isset($book->is_damaged) && $book->is_damaged)
                            <div class="w-full px-6 py-4 bg-orange-50 border-2 border-orange-200 text-orange-700 rounded-xl font-bold flex items-center justify-center space-x-2 cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <span>Buku Sedang Dalam Perbaikan</span>
                            </div>

                        {{-- ── STOK ADA: Tombol Pinjam Normal ── --}}
                        @elseif($book->stock > 0)
                            <a href="{{ route('transactions.create', ['book_id' => $book->id]) }}"
                               class="w-full px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span>Pinjam Buku Ini</span>
                            </a>

                        {{-- ── STOK HABIS: Preorder Section ── --}}
                        @else
                            {{-- Tombol Stok Habis (disabled) --}}
                            <div class="w-full px-6 py-4 bg-gray-300 text-gray-600 rounded-xl font-bold flex items-center justify-center space-x-2 cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span>Stok Habis</span>
                            </div>

                            {{-- ──────────────────────────────────────────────
                                 PREORDER BLOCK
                            ─────────────────────────────────────────────── --}}

                            {{-- EC-5: Sudah preorder buku ini --}}
                            @if($existingPreorder)
                                <div class="w-full px-5 py-4 bg-violet-50 border-2 border-violet-200 rounded-xl">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-violet-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="font-bold text-violet-800 text-sm">Anda sudah dalam antrian!</p>
                                    </div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-xs text-violet-700">Posisi:</span>
                                        <span class="inline-flex items-center justify-center w-7 h-7 bg-violet-600 text-white font-bold text-sm rounded-full">
                                            {{ $myQueuePosition }}
                                        </span>
                                        <span class="text-xs text-violet-700">dari {{ $totalQueue }} antrian</span>
                                        @php
                                            $badge = $existingPreorder->status === 'ready'
                                                ? ['bg-green-100 text-green-800', 'Siap Dipinjam']
                                                : ['bg-amber-100 text-amber-800', 'Menunggu'];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badge[0] }}">{{ $badge[1] }}</span>
                                    </div>

                                    {{-- Progress bar --}}
                                    @if($totalQueue > 0)
                                        @php $pct = max(5, round(($totalQueue - $myQueuePosition + 1) / $totalQueue * 100)); @endphp
                                        <div class="w-full bg-violet-100 rounded-full h-1.5 mb-3">
                                            <div class="bg-gradient-to-r from-violet-500 to-purple-600 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                                        </div>
                                    @endif

                                    {{-- EC: Konfirmasi jika ready --}}
                                    @if($existingPreorder->isReady())
                                        <a href="{{ route('preorders.confirm', $existingPreorder->id) }}"
                                           onclick="return confirm('Apakah Anda jadi meminjam buku ini? Konfirmasi akan membawa Anda ke halaman peminjaman.')"
                                           class="block w-full px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-sm font-bold rounded-lg text-center shadow transition-all mb-2">
                                            ✅ Pinjam Sekarang — Konfirmasi Preorder
                                        </a>
                                    @endif

                                    <a href="{{ route('preorders.index') }}"
                                       class="block w-full px-4 py-2 bg-violet-100 hover:bg-violet-200 text-violet-800 text-xs font-semibold rounded-lg text-center transition-all">
                                        Kelola Preorder Saya →
                                    </a>
                                </div>

                            {{-- EC-4: Sudah maks preorder aktif --}}
                            @elseif($preorderBlocked)
                                <div class="w-full px-5 py-4 bg-orange-50 border-2 border-orange-200 rounded-xl">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        <div>
                                            <p class="font-bold text-orange-800 text-sm">Batas Preorder Tercapai</p>
                                            <p class="text-orange-700 text-xs mt-0.5">Anda sudah memiliki {{ $maxPreorders }} preorder aktif. Batalkan salah satu untuk mendaftar di sini.</p>
                                            <a href="{{ route('preorders.index') }}"
                                               class="inline-block mt-2 px-4 py-1.5 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs font-semibold rounded-lg transition-all">
                                                Kelola Preorder →
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            {{-- ✅ Normal: Tombol Preorder --}}
                            @else
                            @if($settings->isPreorderEnabled())
                                <button type="button"
                                    onclick="openPreorderModal()"
                                    class="w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-700 hover:from-violet-700 hover:to-purple-800 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Preorder Buku Ini</span>
                                    @if($totalQueue > 0)
                                        <span class="ml-1 px-2 py-0.5 bg-white/25 rounded-full text-xs font-bold">Antrian ke-{{ $totalQueue + 1 }}</span>
                                    @else
                                        <span class="ml-1 px-2 py-0.5 bg-white/25 rounded-full text-xs font-bold">Pertama!</span>
                                    @endif
                                </button>
                            @endif
                            @endif
                        @endif
                        {{-- ── END STOK LOGIC ── --}}

                    @endrole
                    {{-- END @role('user') --}}

                    {{-- ── ADMIN ROLE ── --}}
                    @role('admin')
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('books.edit', $book->id) }}"
                           class="flex-1 px-6 py-4 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Edit Buku</span>
                        </a>
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" class="flex-1"
                              onsubmit="return confirmDelete({{ \App\Models\Preorder::where('book_id',$book->id)->whereIn('status',['waiting','ready'])->count() }})">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full px-6 py-4 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </div>

                    {{-- Admin: Lihat semua antrian buku ini --}}
                    @if($totalQueue > 0)
                        <a href="{{ route('admin.preorders.index', ['book_id' => $book->id]) }}"
                           class="w-full px-5 py-3 bg-violet-50 hover:bg-violet-100 border-2 border-violet-200 text-violet-800 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Lihat {{ $totalQueue }} Antrian Preorder
                        </a>
                    @endif
                    @endrole

                </div>
                {{-- END ACTION BUTTONS --}}

            </div>
            {{-- END RIGHT COL --}}
        </div>
    </div>
    {{-- END CONTENT CARD --}}

    {{-- ── RELATED BOOKS ─────────────────────────────────────────── --}}
    @if($relatedBooks->count() > 0)
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <svg class="w-7 h-7 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Buku Serupa
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($relatedBooks as $related)
            <a href="{{ route('books.show', $related->slug) }}" class="group">
                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                    <div class="aspect-[3/4] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                        @if($related->image)
                            <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}"
                                 class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                        @else
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-900 line-clamp-2 mb-1 text-sm group-hover:text-indigo-600 transition-colors">{{ $related->name }}</h3>
                        <p class="text-xs text-gray-500 mb-2">{{ $related->category?->name ?? '-' }}</p>
                        <span class="text-xs {{ $related->stock > 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }} px-2 py-1 rounded-full font-medium">
                            {{ $related->stock > 0 ? 'Tersedia' : 'Habis' }}
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
{{-- END .max-w-7xl --}}


{{-- ══════════════════════════════════════════════════════════════
     PREORDER MODAL
     EC-1 (Race Condition): Ditangani di PreorderRepository via DB::transaction + lockForUpdate
     EC-19 (Manipulasi Request): book_id dari hidden input, validasi server-side di controller
══════════════════════════════════════════════════════════════ --}}
@role('user')
@if($book->stock <= 0 && !$existingPreorder && !$preorderBlocked && !$hasActiveFine && !$alreadyBorrowing)
<div id="preorderModal" style="display:none; position:fixed; inset:0; z-index:9999; overflow:hidden;">
    <div id="preorderBackdrop" style="position:absolute; inset:0; background:rgba(17,24,39,0.65); backdrop-filter:blur(3px);"></div>

    <div style="position:relative; display:flex; align-items:center; justify-content:center; min-height:100%; padding:1rem; pointer-events:none;">
        <div id="preorderPanel"
             style="pointer-events:all; background:white; border-radius:1rem; box-shadow:0 25px 60px rgba(0,0,0,0.3); width:100%; max-width:30rem; transform:scale(0.93); opacity:0; transition:transform 0.28s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease;">

            {{-- Modal Header --}}
            <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9); padding:1.25rem 1.5rem; border-radius:1rem 1rem 0 0; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:0.75rem; padding:0.625rem;">
                        <svg style="width:1.4rem; height:1.4rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p style="color:white; font-weight:700; font-size:1.1rem; margin:0;">Preorder Buku</p>
                        <p style="color:rgba(221,214,254,0.85); font-size:0.72rem; margin:2px 0 0;">Daftarkan antrian peminjaman Anda</p>
                    </div>
                </div>
                <button onclick="closePreorderModal()" type="button"
                    style="background:rgba(255,255,255,0.15); border:none; border-radius:0.5rem; padding:0.4rem 0.5rem; cursor:pointer; display:flex;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg style="width:1.25rem; height:1.25rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding:1.5rem;">
                {{-- Buku Preview --}}
                <div style="display:flex; gap:0.875rem; padding:0.875rem; background:#f5f3ff; border-radius:0.75rem; margin-bottom:1.25rem; border:1.5px solid #ede9fe;">
                    @if($book->image)
                        <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}"
                             style="width:3rem; height:4rem; object-fit:cover; border-radius:0.5rem; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.12);">
                    @else
                        <div style="width:3rem; height:4rem; background:linear-gradient(135deg,#ede9fe,#ddd6fe); border-radius:0.5rem; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            <svg style="width:1.5rem; height:1.5rem; stroke:#a78bfa; fill:none;" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                    <div style="flex:1; min-width:0;">
                        <p style="font-weight:700; color:#111827; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; margin:0 0 3px;">{{ $book->name }}</p>
                        <p style="font-size:12px; color:#6b7280; margin:0 0 6px;">{{ $book->writer }}</p>
                        @if($totalQueue > 0)
                            <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; color:#92400e; font-size:11px; font-weight:600; padding:2px 8px; border-radius:999px;">
                                👥 {{ $totalQueue }} antrian — Anda akan ke-{{ $totalQueue + 1 }}
                            </span>
                        @else
                            <span style="display:inline-flex; align-items:center; background:#d1fae5; color:#065f46; font-size:11px; font-weight:600; padding:2px 8px; border-radius:999px;">
                                ✓ Jadilah yang pertama dalam antrian!
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Form --}}
                {{-- EC-19: book_id dari server, tidak dimanipulasi user --}}
                <form id="preorderForm" action="{{ route('preorders.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">

                    <div style="margin-bottom:1rem;">
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                            Rencana Tanggal Pinjam <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="date" name="expected_borrow_date"
                            id="preorderDate"
                            min="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                            required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.9rem; outline:none; box-sizing:border-box; font-family:inherit;"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'">
                        <p style="font-size:11px; color:#9ca3af; margin-top:4px;">Kapan Anda berencana meminjam buku ini?</p>
                    </div>

                    <div style="margin-bottom:1.5rem;">
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                            Catatan (opsional)
                        </label>
                        <textarea name="notes" rows="2" maxlength="500"
                            placeholder="Misalnya: untuk keperluan studi..."
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; outline:none; resize:vertical; box-sizing:border-box; font-family:inherit;"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                    </div>

                    {{-- Info Box --}}
                    <div style="background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:0.75rem; padding:0.875rem 1rem; margin-bottom:1.25rem;">
                        <p style="font-size:12px; color:#1e40af; font-weight:700; margin:0 0 5px;">ℹ️ Info Preorder</p>
                        <ul style="font-size:11.5px; color:#3b82f6; margin:0; padding:0; list-style:none; line-height:1.9;">
                            <li>• Notifikasi dikirim saat buku tersedia</li>
                            <li>• Konfirmasi dalam <strong>2 hari</strong> setelah notifikasi</li>
                            <li>• Maks <strong>{{ $maxPreorders }} preorder aktif</strong> per akun</li>
                            <li>• Dapat dibatalkan kapan saja</li>
                        </ul>
                    </div>

                    <div id="preorderError" style="display:none; background:#fef2f2; color:#b91c1c; padding:0.75rem 1rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem; border:1px solid #fecaca;"></div>

                    <div style="display:flex; gap:0.75rem;">
                        <button type="button" onclick="closePreorderModal()"
                            style="flex:1; padding:0.75rem; background:#f3f4f6; color:#374151; border:none; border-radius:0.75rem; font-weight:600; cursor:pointer; font-size:0.9rem; font-family:inherit;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            Batal
                        </button>
                        <button type="submit" id="preorderSubmitBtn"
                            style="flex:2; padding:0.75rem; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:white; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer; font-size:0.9rem; font-family:inherit; box-shadow:0 4px 12px rgba(124,58,237,0.3);"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            🕐 Daftar Preorder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endrole


@push('scripts')
{{-- JsBarcode --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
// ── Barcode ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    JsBarcode("#barcode", "{{ $book->barcode }}", {
        format: "CODE128", width: 2, height: 80, displayValue: false, margin: 0
    });
});

function copyBarcode() {
    navigator.clipboard.writeText("{{ $book->barcode }}").then(function() {
        var btn = event.currentTarget;
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        setTimeout(function(){ btn.innerHTML = orig; }, 2000);
    });
}

// ── CSRF Helper ──────────────────────────────────────────────────
function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// ── Wishlist (Main + Large Button) ───────────────────────────────
function _doWishlistToggle(bookSlug, onSuccess) {
    fetch('/wishlist/' + bookSlug + '/toggle', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(onSuccess)
    .catch(function(e) { showToast('Gagal: ' + e.message, 'error'); });
}

function toggleWishlist(slug) {
    var btn  = document.getElementById('wishlistBtn');
    var text = document.getElementById('wishlistText');
    var icon = btn.querySelector('svg');
    btn.disabled = true;

    _doWishlistToggle(slug, function(data) {
        var added = data.status === 'added';
        btn.className = 'w-full px-6 py-4 rounded-xl font-bold transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5 '
            + (added
                ? 'bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white'
                : 'bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700');
        text.textContent = added ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist';
        if (added) icon.classList.add('fill-current'); else icon.classList.remove('fill-current');
        _syncLargeWishlist(added);
        showToast(added ? '✓ Ditambahkan ke wishlist!' : 'Dihapus dari wishlist', added ? 'success' : 'info');
        btn.disabled = false;
    });
}

function toggleWishlistLarge(slug) {
    var btn  = document.getElementById('wishlistBtnLarge');
    var text = document.getElementById('wishlistTextLarge');
    var icon = btn.querySelector('svg');
    btn.disabled = true;

    _doWishlistToggle(slug, function(data) {
        var added = data.status === 'added';
        btn.className = 'absolute top-4 right-4 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center space-x-2 '
            + (added ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50');
        text.textContent = added ? 'Tersimpan' : 'Simpan';
        if (added) icon.classList.add('fill-current'); else icon.classList.remove('fill-current');
        _syncMainWishlist(added);
        showToast(added ? '✓ Ditambahkan ke wishlist!' : 'Dihapus dari wishlist', added ? 'success' : 'info');
        btn.disabled = false;
    });
}

function _syncMainWishlist(added) {
    var btn = document.getElementById('wishlistBtn');
    var txt = document.getElementById('wishlistText');
    var ico = btn ? btn.querySelector('svg') : null;
    if (!btn) return;
    btn.className = 'w-full px-6 py-4 rounded-xl font-bold transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5 '
        + (added ? 'bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white'
                 : 'bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700');
    if (txt) txt.textContent = added ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist';
    if (ico) { if (added) ico.classList.add('fill-current'); else ico.classList.remove('fill-current'); }
}

function _syncLargeWishlist(added) {
    var btn = document.getElementById('wishlistBtnLarge');
    var txt = document.getElementById('wishlistTextLarge');
    var ico = btn ? btn.querySelector('svg') : null;
    if (!btn) return;
    btn.className = 'absolute top-4 right-4 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center space-x-2 '
        + (added ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50');
    if (txt) txt.textContent = added ? 'Tersimpan' : 'Simpan';
    if (ico) { if (added) ico.classList.add('fill-current'); else ico.classList.remove('fill-current'); }
}

// ── Preorder Modal ────────────────────────────────────────────────
function openPreorderModal() {
    var modal = document.getElementById('preorderModal');
    var panel = document.getElementById('preorderPanel');
    if (!modal) return;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    setTimeout(function() { panel.style.transform = 'scale(1)'; panel.style.opacity = '1'; }, 10);
}

function closePreorderModal() {
    var panel = document.getElementById('preorderPanel');
    var modal = document.getElementById('preorderModal');
    if (!modal) return;
    panel.style.transform = 'scale(0.93)';
    panel.style.opacity   = '0';
    setTimeout(function() { modal.style.display = 'none'; document.body.style.overflow = ''; }, 230);
}

var _backdrop = document.getElementById('preorderBackdrop');
if (_backdrop) _backdrop.addEventListener('click', closePreorderModal);

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePreorderModal();
});

// EC-19: Submit form dengan tambahan guard JS (validasi utama tetap server-side)
var _pForm = document.getElementById('preorderForm');
if (_pForm) {
    _pForm.addEventListener('submit', function(e) {
        var date = document.getElementById('preorderDate').value;
        var errEl = document.getElementById('preorderError');
        if (!date) {
            e.preventDefault();
            errEl.textContent = 'Tanggal pinjam wajib diisi.';
            errEl.style.display = 'block';
            return;
        }
        // Disable submit untuk cegah double-submit (EC-1 frontend guard)
        var btn = document.getElementById('preorderSubmitBtn');
        btn.disabled    = true;
        btn.textContent = 'Memproses...';
        // Biarkan form submit normal (bukan AJAX) agar CSRF dan redirect berjalan baik
    });
}

// ── Admin: Konfirmasi hapus buku yang masih ada antrian (EC-10) ───
function confirmDelete(queueCount) {
    if (queueCount > 0) {
        return confirm(
            'Buku ini masih memiliki ' + queueCount + ' antrian preorder aktif.\n\n' +
            'Menghapus buku akan membatalkan SEMUA antrian dan mengirim notifikasi ke pengguna.\n\n' +
            'Yakin ingin menghapus?'
        );
    }
    return confirm('Yakin ingin menghapus buku ini?');
}

// ── Toast Notification ───────────────────────────────────────────
function showToast(message, type) {
    var colors = { success: '#22c55e', error: '#ef4444', info: '#3b82f6' };
    var toast  = document.createElement('div');
    toast.style.cssText = 'position:fixed; bottom:1.5rem; right:1.5rem; background:' + (colors[type]||colors.info)
        + '; color:white; padding:0.875rem 1.25rem; border-radius:0.75rem; font-size:0.9rem; font-weight:600; z-index:99999; box-shadow:0 8px 25px rgba(0,0,0,0.2); font-family:inherit;';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity    = '0';
        toast.style.transform  = 'translateY(10px)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}
</script>
@endpush

@endsection