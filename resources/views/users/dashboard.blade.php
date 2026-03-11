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
    <div class="bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 text-white px-6 py-12 mb-6 rounded-xl shadow-xl">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Halo, {{ auth()->user()->name }}! 👋</h1>
                    <p class="text-blue-100 text-lg">Selamat datang di Perpustakaan Digital</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-32 h-32 text-white opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @php
        $totalBooks      = \App\Models\Book::count();
        $totalCategories = \App\Models\Category::count();

        $activeBorrowed = \App\Models\Transaction::where('user_id', auth()->id())
            ->whereIn('status', ['borrowed', 'return_requested'])
            ->count();

        $unpaidFines = \App\Models\Fine::whereHas('transaction', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'unpaid')
            ->sum('amount');

        $hasActiveFine = \App\Models\Fine::whereHas('transaction', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereIn('status', ['unpaid', 'pending_confirmation'])
            ->exists();

        // ── KATEGORI BANNER ──────────────────────────────────────────
        $bannerCategories = \App\Models\Category::withCount('books')
            ->orderByDesc('books_count')
            ->take(8)
            ->get();

        $activeCategoryId = request('category_id');

        // Buku terbaru — ikut filter kategori jika ada
        $recentBooks = \App\Models\Book::latest()
            ->when($activeCategoryId, fn($q) => $q->where('category_id', $activeCategoryId))
            ->take(6)
            ->get();
    @endphp

    <div class="">
        {{-- Quick Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('books.index') }}" class="block bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Total Buku</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalBooks }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-blue-500 mt-2">Lihat semua buku →</p>
            </a>

            <a href="{{ route('categories.index') }}" class="block bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md hover:border-purple-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Kategori</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalCategories }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-purple-500 mt-2">Lihat semua kategori →</p>
            </a>

            <a href="{{ route('transactions.index') }}" class="block bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Sedang Dipinjam</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeBorrowed }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-blue-500 mt-2">Lihat transaksi →</p>
            </a>

            <a href="{{ route('fines.index') }}" class="block bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md {{ $unpaidFines > 0 ? 'hover:border-red-200' : 'hover:border-green-200' }} transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Denda Belum Bayar</p>
                        <p class="text-lg font-bold {{ $unpaidFines > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            Rp {{ number_format($unpaidFines, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 {{ $unpaidFines > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $unpaidFines > 0 ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs {{ $unpaidFines > 0 ? 'text-red-500' : 'text-gray-400' }} mt-2">
                    {{ $unpaidFines > 0 ? 'Bayar sekarang →' : 'Tidak ada denda' }}
                </p>
            </a>
        </div>

        {{-- Fine Warning Banner --}}
        @if($hasActiveFine)
        <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-xl p-5 mb-8 shadow-md">
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

        {{-- ════════════════════════════════════════════════════════════
            CATEGORY BANNER — Sisip di sini, sebelum Rekomendasi
        ════════════════════════════════════════════════════════════ --}}
        @if($bannerCategories->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mb-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Telusuri Kategori</h2>
                        <p class="text-xs text-gray-400">Klik banner untuk filter buku di bawah</p>
                    </div>
                </div>
                @if($activeCategoryId)
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset Filter
                    </a>
                @else
                    <a href="{{ route('categories.index') }}" class="text-xs text-purple-600 hover:underline font-medium">
                        Semua Kategori →
                    </a>
                @endif
            </div>

            {{-- Banner Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
                @foreach($bannerCategories as $cat)
                    @php
                        $color    = $cat->color ?: 'from-blue-500 to-indigo-600';
                        $isActive = (string)$activeCategoryId === (string)$cat->id;
                    @endphp
                    <a href="{{ $isActive ? route('dashboard') : route('dashboard', ['category_id' => $cat->id]) }}"
                       class="group relative rounded-xl overflow-hidden shadow-sm transition-all duration-300
                           {{ $isActive
                               ? 'ring-[3px] ring-purple-500 ring-offset-1 shadow-md scale-[1.04]'
                               : 'hover:shadow-md hover:-translate-y-0.5 hover:scale-[1.02]' }}">

                        <div class="relative h-20 sm:h-24">
                            {{-- Gambar atau gradient fallback --}}
                            @if($cat->image)
                                <img src="{{ asset('storage/' . $cat->image) }}"
                                     alt="{{ $cat->name }}"
                                     class="w-full h-full object-cover transition-transform duration-500
                                         {{ $isActive ? 'scale-110' : 'group-hover:scale-110' }}">
                                <div class="absolute inset-0 bg-gradient-to-t
                                    {{ $isActive ? 'from-purple-900/75 via-black/20' : 'from-black/65 via-black/10' }} to-transparent">
                                </div>
                            @else
                                <div class="w-full h-full bg-gradient-to-br {{ $color }} flex items-center justify-center">
                                    <span class="text-4xl font-black text-white/15 select-none leading-none">
                                        {{ strtoupper(substr($cat->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t
                                    {{ $isActive ? 'from-purple-900/60' : 'from-black/50' }} to-transparent">
                                </div>
                            @endif

                            {{-- Centang aktif --}}
                            @if($isActive)
                                <div class="absolute top-1.5 right-1.5">
                                    <div class="w-4 h-4 bg-white rounded-full flex items-center justify-center shadow">
                                        <svg class="w-2.5 h-2.5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif

                            {{-- Label bawah --}}
                            <div class="absolute bottom-0 left-0 right-0 px-2 pb-2">
                                <p class="text-white font-bold text-xs leading-tight line-clamp-1 drop-shadow">
                                    {{ $cat->name }}
                                </p>
                                <p class="text-white/60 text-xs">{{ $cat->books_count }} buku</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Info bar filter aktif --}}
            @if($activeCategoryId)
                @php $activecat = $bannerCategories->firstWhere('id', $activeCategoryId); @endphp
                @if($activecat)
                <div class="mt-3 flex items-center gap-2 px-3 py-2 bg-purple-50 border border-purple-200 rounded-lg text-sm">
                    <svg class="w-3.5 h-3.5 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    <span class="text-purple-700 text-xs">
                        Menampilkan buku kategori <strong>{{ $activecat->name }}</strong>
                    </span>
                    <span class="ml-auto text-xs text-purple-400">{{ $recentBooks->count() }} buku</span>
                </div>
                @endif
            @endif
        </div>
        @endif
        {{-- ════════════════════════════════════════════════════════════ --}}

        {{-- Personalized Recommendations --}}
        @php
            $personalizedBooks = app(\App\Contracts\Repositories\AlgorithmRepository::class)
                ->personalized(auth()->id(), 6);
        @endphp

        @if($personalizedBooks->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Rekomendasi Untuk Anda
                </h2>
                <a href="{{ route('books.catalog') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                    Lihat Semua →
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($personalizedBooks as $recBook)
                <a href="{{ route('books.show', $recBook->slug) }}" class="group">
                    <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                        <div class="aspect-[3/4] bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center overflow-hidden">
                            @if($recBook->image)
                                <img src="{{ asset('storage/' . $recBook->image) }}" alt="{{ $recBook->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                            @else
                                <svg class="w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-1 group-hover:text-purple-600">
                                {{ $recBook->name }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ $recBook->category?->name ?? 'Uncategorized' }}</p>
                            <div class="flex items-center mt-1">
                                <span class="text-xs {{ $recBook->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $recBook->stock > 0 ? 'Tersedia' : 'Habis' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Menu Cepat
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('books.index') }}"
                   class="group flex flex-col items-center p-4 rounded-lg hover:bg-blue-50 transition-colors border border-gray-100">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Cari Buku</span>
                </a>

                @if($hasActiveFine)
                    <a href="{{ route('fines.index') }}"
                       class="group flex flex-col items-center p-4 rounded-lg hover:bg-red-50 transition-colors border border-red-100 bg-red-50/50">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-red-200 transition-colors">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-red-600">Bayar Denda Dulu</span>
                        <span class="text-xs text-red-400 mt-1">Peminjaman ditangguhkan</span>
                    </a>
                @else
                    <a href="{{ route('transactions.create') }}"
                       class="group flex flex-col items-center p-4 rounded-lg hover:bg-blue-50 transition-colors border border-gray-100">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Pinjam Buku</span>
                    </a>
                @endif

                <a href="{{ route('transactions.index') }}"
                   class="group flex flex-col items-center p-4 rounded-lg hover:bg-blue-50 transition-colors border border-gray-100">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Riwayat Pinjam</span>
                </a>

                <a href="{{ route('categories.index') }}"
                   class="group flex flex-col items-center p-4 rounded-lg hover:bg-purple-50 transition-colors border border-gray-100">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">Kategori</span>
                </a>
            </div>
        </div>

        {{-- Recent Books Section --}}
        {{-- Judul berubah sesuai filter --}}
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    @if($activeCategoryId)
                        @php $activecat = $bannerCategories->firstWhere('id', $activeCategoryId); @endphp
                        Buku &mdash; <span class="text-purple-600">{{ $activecat?->name }}</span>
                    @else
                        Buku Terbaru
                    @endif
                </h2>
                <a href="{{ route('books.catalog', $activeCategoryId ? ['category_id' => $activeCategoryId] : []) }}"
                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Lihat Semua →
                </a>
            </div>

            @if($recentBooks->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($recentBooks as $book)
                    <a href="{{ route('books.show', $book->slug) }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                            <div class="aspect-[3/4] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                                @if($book->image)
                                    <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-1 group-hover:text-blue-600">
                                    {{ $book->name }}
                                </h3>
                                <p class="text-xs text-gray-500">{{ $book->category?->name ?? 'Uncategorized' }}</p>
                                <div class="flex items-center mt-1">
                                    <span class="text-xs {{ $book->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $book->stock > 0 ? 'Tersedia' : 'Habis' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p class="text-gray-500 mb-2">Belum ada buku di kategori ini</p>
                    <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline">← Tampilkan semua</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection