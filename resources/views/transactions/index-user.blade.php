@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Success Message --}}
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-md animate-fade-in">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-12 mb-6 rounded-xl shadow-xl">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Halo, {{ auth()->user()->name }}! 👋</h1>
                    <p class="text-blue-100 text-lg">Selamat datang di Perpustakaan Digital</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $user = auth()->user();

        $activeLoansCount = $user->transactions()
            ->whereIn('status', ['borrowed', 'return_requested'])
            ->count();

        $wishlistCount = $user->wishlists()->count();

        $pendingFines = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'unpaid')
            ->count();

        $unpaidFinesAmount = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'unpaid')
            ->sum('amount');

        $totalBorrowed = $user->transactions()->count();

        $hasActiveFine = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['unpaid', 'pending_confirmation'])
            ->exists();

        // Transaksi aktif (max 3 untuk preview)
        $activeTransactions = $user->transactions()
            ->with(['items.book.category'])
            ->whereIn('status', ['borrowed', 'return_requested'])
            ->latest()
            ->take(3)
            ->get();

        // Wishlist (max 4 untuk preview)
        $wishlists          = $user->wishlists()->with('book.category')->latest()->take(4)->get();
        $totalWishlists     = $user->wishlists()->count();
        $availableWishlists = $wishlists->filter(fn($w) => ($w->book->stock ?? 0) > 0)->count();
        $uniqueCategories   = $wishlists->pluck('book.category_id')->filter()->unique()->count();

        // Personalized recommendations
        $personalizedBooks = app(\App\Contracts\Repositories\AlgorithmRepository::class)
            ->personalized($user->id, 6);

        // Buku terbaru
        $recentBooks = \App\Models\Book::with('category')->latest()->take(8)->get();
    @endphp

    {{-- Fine Warning Banner --}}
    @if($hasActiveFine)
    <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-xl p-5 mb-6 shadow-md">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-800">Peminjaman Ditangguhkan</p>
                    <p class="text-sm text-red-600">Anda memiliki denda yang belum dibayar. Lunasi untuk bisa meminjam buku baru.</p>
                </div>
            </div>
            <a href="{{ route('fines.index') }}" class="flex-shrink-0 ml-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all">
                Bayar Denda
            </a>
        </div>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <a href="{{ route('transactions.history') }}" class="block bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">Sedang Dipinjam</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $activeLoansCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Buku aktif</p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </a>

        <a href="{{ route('wishlist.index') }}" class="block bg-white rounded-xl shadow-lg p-6 border-l-4 border-pink-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">Wishlist</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $wishlistCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Buku favorit</p>
                </div>
                <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
            </div>
        </a>

        <a href="{{ route('fines.index') }}" class="block bg-white rounded-xl shadow-lg p-6 border-l-4 border-amber-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">Denda Belum Terbayar</p>
                    <p class="text-3xl font-bold {{ $pendingFines > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $pendingFines }}</p>
                    <p class="text-xs {{ $pendingFines > 0 ? 'text-red-400' : 'text-gray-500' }} mt-1">
                        {{ $pendingFines > 0 ? 'Rp ' . number_format($unpaidFinesAmount, 0, ',', '.') : 'Tidak ada denda' }}
                    </p>
                </div>
                <div class="w-14 h-14 {{ $pendingFines > 0 ? 'bg-amber-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 {{ $pendingFines > 0 ? 'text-amber-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </a>

        <a href="{{ route('transactions.history') }}" class="block bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">Total Peminjaman</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalBorrowed }}</p>
                    <p class="text-xs text-gray-500 mt-1">Sepanjang waktu</p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Left Column (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Peminjaman Aktif --}}
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Peminjaman Aktif</h3>
                            <p class="text-sm text-gray-500">Buku yang sedang Anda pinjam</p>
                        </div>
                    </div>
                    <a href="{{ route('transactions.history') }}"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all flex items-center space-x-2">
                        <span>Lihat Semua</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                @if($activeTransactions->count() > 0)
                    <div class="space-y-4">
                        @foreach($activeTransactions as $transaction)
                            @php
                                $items     = $transaction->items;
                                $isOverdue = $transaction->due_at && \Carbon\Carbon::parse($transaction->due_at)->isPast();
                            @endphp
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border-2 {{ $isOverdue ? 'border-red-300' : 'border-blue-200' }} hover:shadow-md transition-all">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-mono text-gray-500">{{ $transaction->receipt_number }}</span>
                                    <div class="flex items-center gap-2">
                                        @if($isOverdue)
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">Terlambat</span>
                                        @endif
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full
                                            {{ $transaction->status === 'borrowed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $transaction->status === 'borrowed' ? 'Dipinjam' : 'Menunggu Konfirmasi' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    @foreach($items as $item)
                                        @if($item->book)
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-14 bg-gray-200 rounded flex-shrink-0 overflow-hidden">
                                                @if($item->book->image)
                                                    <img src="{{ asset('storage/' . $item->book->image) }}" alt="{{ $item->book->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $item->book->name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $item->book->writer ?? '-' }}</p>
                                                @if($item->quantity > 1)
                                                    <p class="text-xs text-blue-600">x{{ $item->quantity }} eksemplar</p>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-blue-200 text-xs text-gray-500">
                                    <span>Pinjam: {{ \Carbon\Carbon::parse($transaction->borrowed_at)->format('d M Y') }}</span>
                                    <span class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-blue-600' }}">
                                        Kembali: {{ $transaction->due_at ? \Carbon\Carbon::parse($transaction->due_at)->format('d M Y') : '-' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Tidak Ada Peminjaman Aktif</h4>
                        <p class="text-gray-500 mb-4">Mulai pinjam buku dari koleksi kami</p>
                        @if(!$hasActiveFine)
                            <a href="{{ route('books.index') }}"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 shadow-md transition-all">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Jelajahi Buku
                            </a>
                        @else
                            <a href="{{ route('fines.index') }}"
                                class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 shadow-md transition-all">
                                Bayar Denda Dahulu
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Wishlist --}}
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Wishlist Saya</h3>
                            <p class="text-sm text-gray-500">Buku yang ingin saya baca</p>
                        </div>
                    </div>
                    <a href="{{ route('wishlist.index') }}"
                        class="px-4 py-2 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white rounded-lg font-semibold transition-all flex items-center space-x-2 shadow-md">
                        <span>Lihat Semua</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                @if($wishlists->count() > 0)
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-lg p-4 border-2 border-pink-200 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $totalWishlists }}</p>
                            <p class="text-xs font-semibold text-gray-500 mt-1">Total Buku</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border-2 border-green-200 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $availableWishlists }}</p>
                            <p class="text-xs font-semibold text-gray-500 mt-1">Tersedia</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border-2 border-blue-200 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $uniqueCategories }}</p>
                            <p class="text-xs font-semibold text-gray-500 mt-1">Kategori</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($wishlists as $wishlistItem)
                            @php $book = $wishlistItem->book; @endphp
                            @if($book)
                                <div class="group relative">
                                    <a href="{{ route('books.show', $book->slug) }}" class="block">
                                        <div class="rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 border-2 border-pink-100 hover:border-pink-300">
                                            <div class="aspect-[3/4] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden relative">
                                                @if($book->image)
                                                    <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                                                @else
                                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                @endif
                                                <div class="absolute top-2 right-2">
                                                    <span class="text-xs {{ $book->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-0.5 rounded-full font-medium">
                                                        {{ $book->stock > 0 ? 'Ada' : 'Habis' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="p-3 bg-white">
                                                <h4 class="font-semibold text-gray-900 line-clamp-2 text-sm group-hover:text-pink-600 transition-colors">{{ $book->name }}</h4>
                                                <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $book->writer ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </a>
                                    @if($book->stock > 0 && !$hasActiveFine)
                                        <a href="{{ route('transactions.create', ['book_id' => $book->id]) }}"
                                            class="absolute bottom-3 left-3 right-3 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-2 rounded-lg shadow opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            Pinjam
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if($totalWishlists > 4)
                        <div class="mt-4 text-center">
                            <a href="{{ route('wishlist.index') }}" class="text-pink-600 hover:text-pink-700 font-semibold text-sm">
                                Lihat {{ $totalWishlists - 4 }} buku lainnya →
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-pink-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Wishlist Masih Kosong</h4>
                        <p class="text-gray-500 mb-4">Tambahkan buku favorit Anda ke wishlist</p>
                        <a href="{{ route('books.index') }}"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-600 to-rose-600 text-white rounded-lg font-semibold shadow-md transition-all">
                            Jelajahi Koleksi Buku
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column (1/3) --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <a href="{{ route('books.index') }}" class="flex items-center space-x-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <div><p class="font-semibold text-gray-900">Cari Buku</p><p class="text-xs text-gray-500">Jelajahi koleksi</p></div>
                    </a>

                    @if($hasActiveFine)
                        <a href="{{ route('fines.index') }}" class="flex items-center space-x-3 p-3 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group border border-red-100">
                            <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div><p class="font-semibold text-red-700">Bayar Denda</p><p class="text-xs text-red-400">Peminjaman ditangguhkan</p></div>
                        </a>
                    @else
                        <a href="{{ route('transactions.create') }}" class="flex items-center space-x-3 p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                            <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <div><p class="font-semibold text-gray-900">Pinjam Buku</p><p class="text-xs text-gray-500">Buat peminjaman baru</p></div>
                        </a>
                    @endif

                    <a href="{{ route('wishlist.index') }}" class="flex items-center space-x-3 p-3 bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors group">
                        <div class="w-10 h-10 bg-pink-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </div>
                        <div><p class="font-semibold text-gray-900">Wishlist</p><p class="text-xs text-gray-500">Buku favorit</p></div>
                    </a>

                    <a href="{{ route('transactions.history') }}" class="flex items-center space-x-3 p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div><p class="font-semibold text-gray-900">Riwayat</p><p class="text-xs text-gray-500">Lihat peminjaman</p></div>
                    </a>

                    <a href="{{ route('fines.index') }}" class="flex items-center space-x-3 p-3 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors group">
                        <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div><p class="font-semibold text-gray-900">Denda</p><p class="text-xs text-gray-500">Kelola pembayaran</p></div>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold">Tips Membaca</h3>
                </div>
                <ul class="space-y-2 text-sm text-indigo-100">
                    <li class="flex items-start gap-2"><span>📚</span><span>Baca minimal 20 menit setiap hari</span></li>
                    <li class="flex items-start gap-2"><span>⏰</span><span>Kembalikan buku tepat waktu</span></li>
                    <li class="flex items-start gap-2"><span>💡</span><span>Catat hal menarik dari buku</span></li>
                    <li class="flex items-start gap-2"><span>🎯</span><span>Tetapkan target membaca bulanan</span></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Personalized Recommendations --}}
    @if($personalizedBooks->isNotEmpty())
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Rekomendasi Untuk Anda</h2>
                    <p class="text-sm text-gray-500">Berdasarkan riwayat peminjaman</p>
                </div>
            </div>
            <a href="{{ route('books.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">Lihat Semua →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($personalizedBooks as $recBook)
            <div class="group relative">
                <a href="{{ route('books.show', $recBook->slug) }}" class="block">
                    <div class="rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 border-2 border-gray-100 hover:border-purple-300">
                        <div class="aspect-[3/4] bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center overflow-hidden relative">
                            @if($recBook->image)
                                <img src="{{ asset('storage/' . $recBook->image) }}" alt="{{ $recBook->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                            @else
                                <svg class="w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                            <div class="absolute top-2 right-2">
                                <span class="text-xs {{ $recBook->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-0.5 rounded-full font-medium">
                                    {{ $recBook->stock > 0 ? 'Ada' : 'Habis' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3 bg-white">
                            <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-1 group-hover:text-purple-600">{{ $recBook->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $recBook->category?->name ?? 'Uncategorized' }}</p>
                        </div>
                    </div>
                </a>
                @if($recBook->stock > 0 && !$hasActiveFine)
                    <a href="{{ route('transactions.create', ['book_id' => $recBook->id]) }}"
                        class="absolute bottom-3 left-3 right-3 bg-purple-500 hover:bg-purple-600 text-white text-xs font-semibold py-2 rounded-lg shadow opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Pinjam
                    </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Buku Terbaru --}}
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Buku Terbaru</h2>
                    <p class="text-sm text-gray-500">Koleksi terbaru perpustakaan</p>
                </div>
            </div>
            <a href="{{ route('books.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all flex items-center space-x-2">
                <span>Lihat Semua</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if($recentBooks->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($recentBooks as $book)
                    <div class="group relative">
                        <a href="{{ route('books.show', $book->slug) }}" class="block">
                            <div class="rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 border-2 border-gray-100 hover:border-blue-300">
                                <div class="aspect-[3/4] bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden relative">
                                    @if($book->image)
                                        <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-2 right-2">
                                        <span class="text-xs {{ $book->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-0.5 rounded-full font-medium shadow">
                                            {{ $book->stock > 0 ? 'Tersedia' : 'Habis' }}
                                        </span>
                                    </div>
                                    @if($book->category)
                                        <div class="absolute top-2 left-2">
                                            <span class="text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full font-medium shadow">
                                                {{ $book->category->name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-3 bg-white">
                                    <h3 class="font-semibold text-gray-900 line-clamp-2 text-sm group-hover:text-blue-600 transition-colors">{{ $book->name }}</h3>
                                    <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $book->writer ?? '-' }}</p>
                                    @if($book->stock > 0)
                                        <p class="text-xs text-gray-400 mt-1">Stok: {{ $book->stock }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @if($book->stock > 0 && !$hasActiveFine)
                            <a href="{{ route('transactions.create', ['book_id' => $book->id]) }}"
                                class="absolute bottom-3 left-3 right-3 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-2 rounded-lg shadow opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Pinjam Sekarang
                            </a>
                        @elseif(!$book->stock)
                            <div class="absolute bottom-3 left-3 right-3 bg-gray-400 text-white text-xs font-semibold py-2 rounded-lg text-center opacity-0 group-hover:opacity-100 transition-all">
                                Stok Habis
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="text-gray-500">Belum ada buku tersedia</p>
            </div>
        @endif
    </div>

</div>
@endsection