@extends('layouts.app')

@section('title', 'Katalog Buku')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Katalog Buku 📚</h1>
                <p class="text-blue-100">Jelajahi seluruh koleksi perpustakaan digital kami</p>
            </div>
            <div class="hidden md:block">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    @php
        $hasActiveFine = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', auth()->id()))
            ->whereIn('status', ['unpaid', 'pending_confirmation'])
            ->exists();
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $totalBooks }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Buku</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $availableBooks }}</p>
            <p class="text-xs text-gray-500 mt-1">Tersedia</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $totalCategories }}</p>
            <p class="text-xs text-gray-500 mt-1">Kategori</p>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <form method="GET" action="{{ route('books.catalog') }}">
            <div class="flex flex-col md:flex-row gap-3">
                {{-- Search --}}
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari judul, penulis..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                {{-- Category --}}
                <select name="category" class="w-full md:w-48 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Availability --}}
                <select name="available" class="w-full md:w-44 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Stok</option>
                    <option value="1" {{ request('available') === '1' ? 'selected' : '' }}>Tersedia</option>
                    <option value="0" {{ request('available') === '0' ? 'selected' : '' }}>Habis</option>
                </select>

                {{-- Sort --}}
                <select name="sort" class="w-full md:w-44 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>A - Z</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Z - A</option>
                </select>

                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all">
                    Cari
                </button>

                @if(request()->hasAny(['search', 'category', 'available', 'sort']))
                    <a href="{{ route('books.catalog') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Active Filters --}}
    @if(request()->hasAny(['search', 'category', 'available']))
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-500">Filter aktif:</span>
            @if(request('search'))
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    Kata kunci: "{{ request('search') }}"
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-2 hover:text-blue-900">×</a>
                </span>
            @endif
            @if(request('category'))
                @php $selectedCat = $categories->find(request('category')); @endphp
                <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 text-sm rounded-full">
                    Kategori: {{ $selectedCat?->name }}
                    <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="ml-2 hover:text-purple-900">×</a>
                </span>
            @endif
            @if(request('available') !== null && request('available') !== '')
                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">
                    Stok: {{ request('available') === '1' ? 'Tersedia' : 'Habis' }}
                    <a href="{{ request()->fullUrlWithQuery(['available' => null]) }}" class="ml-2 hover:text-green-900">×</a>
                </span>
            @endif
            <span class="text-sm text-gray-500">— {{ $books->total() }} hasil ditemukan</span>
        </div>
    @endif

    {{-- Book Grid --}}
    @if($books->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($books as $book)
                <div class="group relative">
                    <a href="{{ route('books.show', $book->slug) }}" class="block">
                        <div class="rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 border-2 border-gray-100 hover:border-blue-300 bg-white">
                            <div class="aspect-[3/4] bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden relative">
                                @if($book->image)
                                    <img src="{{ asset('storage/' . $book->image) }}"
                                         alt="{{ $book->name }}"
                                         class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                @endif

                                {{-- Stock badge --}}
                                <div class="absolute top-2 right-2">
                                    <span class="text-xs {{ $book->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-0.5 rounded-full font-medium shadow">
                                        {{ $book->stock > 0 ? 'Ada' : 'Habis' }}
                                    </span>
                                </div>

                                {{-- Category badge --}}
                                @if($book->category)
                                    <div class="absolute top-2 left-2">
                                        <span class="text-xs bg-blue-500/80 text-white px-2 py-0.5 rounded-full font-medium">
                                            {{ Str::limit($book->category->name, 10) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-3">
                                <h3 class="font-semibold text-gray-900 line-clamp-2 text-sm group-hover:text-blue-600 transition-colors leading-tight">
                                    {{ $book->name }}
                                </h3>
                                <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $book->writer ?? '-' }}</p>
                                @if($book->stock > 0)
                                    <p class="text-xs text-gray-400 mt-1">Stok: {{ $book->stock }}</p>
                                @endif
                            </div>
                        </div>
                    </a>

                    {{-- Pinjam hover button --}}
                    @if($book->stock > 0 && !$hasActiveFine)
                        <a href="{{ route('transactions.create', ['book_id' => $book->id]) }}"
                            class="absolute bottom-3 left-3 right-3 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-2 rounded-lg shadow opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Pinjam
                        </a>
                    @elseif(!$book->stock)
                        <div class="absolute bottom-3 left-3 right-3 bg-gray-400 text-white text-xs font-semibold py-2 rounded-lg text-center opacity-0 group-hover:opacity-100 transition-all">
                            Stok Habis
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($books->hasPages())
            <div class="flex justify-center">
                {{ $books->withQueryString()->links() }}
            </div>
        @endif

    @else
        <div class="bg-white rounded-xl shadow-sm p-16 text-center border border-gray-100">
            <svg class="mx-auto h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Buku Tidak Ditemukan</h3>
            <p class="text-gray-500 mb-6">Coba ubah kata kunci atau filter pencarian</p>
            <a href="{{ route('books.catalog') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all">
                Lihat Semua Buku
            </a>
        </div>
    @endif

</div>
@endsection