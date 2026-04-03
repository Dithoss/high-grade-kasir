@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $user = Auth::user();

    $activeTransactions = $user->transactions()
        ->with(['items.book'])
        ->whereIn('status', ['borrowed', 'return_requested'])
        ->latest()->take(3)->get();

    $activeLoansCount = $user->transactions()
        ->whereIn('status', ['borrowed', 'return_requested'])->count();

    $wishlistCount = $user->wishlists()->count();

    $pendingFines = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
        ->where('status', 'unpaid')->count();

    $totalBorrowed = $user->transactions()->count();

    $hasActiveFine = \App\Models\Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
        ->whereIn('status', ['unpaid', 'pending_confirmation'])->exists();

    $preorders = $user->preorders()
        ->with('book')
        ->whereIn('status', ['waiting', 'ready'])
        ->latest()->take(3)->get();

    $totalPreorders = $user->preorders()
        ->whereIn('status', ['waiting', 'ready'])->count();

    $recentBooks = \App\Models\Book::with('category')->latest()->take(8)->get();
@endphp

<div class="space-y-6">

    {{-- ── Hero ── --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-7 text-white flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-1">Halo, {{ Auth::user()->name }}! 👋</h1>
            <p class="text-blue-100 text-sm">Selamat datang kembali di Perpustakaan Digital</p>
        </div>
        <div class="hidden md:flex w-20 h-20 bg-white/20 rounded-full items-center justify-center">
            <i class="fas fa-book-open text-3xl"></i>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('transactions.history') }}" class="bg-white rounded-xl p-5 border-l-4 border-blue-500 shadow hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 mb-1">Dipinjam</p>
            <p class="text-3xl font-bold text-gray-900">{{ $activeLoansCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Buku aktif</p>
        </a>
        <a href="{{ route('wishlist.index') }}" class="bg-white rounded-xl p-5 border-l-4 border-pink-500 shadow hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 mb-1">Wishlist</p>
            <p class="text-3xl font-bold text-gray-900">{{ $wishlistCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Buku favorit</p>
        </a>
        <a href="{{ route('fines.index') }}" class="bg-white rounded-xl p-5 border-l-4 border-amber-500 shadow hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 mb-1">Denda</p>
            <p class="text-3xl font-bold {{ $pendingFines > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $pendingFines }}</p>
            <p class="text-xs text-gray-400 mt-1">Belum dibayar</p>
        </a>
        <a href="{{ route('preorders.index') }}" class="bg-white rounded-xl p-5 border-l-4 border-purple-500 shadow hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 mb-1">Pre Order</p>
            <p class="text-3xl font-bold text-gray-900">{{ $totalPreorders }}</p>
            <p class="text-xs text-gray-400 mt-1">Dalam antrian</p>
        </a>
    </div>

    {{-- ── Main Grid ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Col ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Peminjaman Aktif --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book-open text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Peminjaman Aktif</h3>
                            <p class="text-xs text-gray-500">Buku yang sedang dipinjam</p>
                        </div>
                    </div>
                    <a href="{{ route('transactions.history') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                        Lihat Semua <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                </div>

                @forelse($activeTransactions as $transaction)
                    @php $isOverdue = $transaction->due_at && \Carbon\Carbon::parse($transaction->due_at)->isPast(); @endphp
                    <div class="mb-3 p-4 rounded-xl border-2 {{ $isOverdue ? 'border-red-200 bg-red-50' : 'border-blue-100 bg-blue-50' }} hover:shadow-sm transition-all">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-mono text-gray-400">{{ substr($transaction->receipt_number, 0, 12) }}...</span>
                            <div class="flex gap-2">
                                @if($isOverdue)
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">Terlambat</span>
                                @endif
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $transaction->status === 'borrowed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $transaction->status === 'borrowed' ? 'Dipinjam' : 'Menunggu Konfirmasi' }}
                                </span>
                            </div>
                        </div>
                        @foreach($transaction->items as $item)
                            @if($item->book)
                            <div class="flex items-center gap-3 mt-2">
                                <div class="w-9 h-12 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                    @if($item->book->image)
                                        <img src="{{ asset('storage/' . $item->book->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                            <i class="fas fa-book text-blue-400 text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $item->book->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $item->book->writer ?? '-' }}</p>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        <div class="flex justify-between mt-3 pt-2 border-t border-blue-200 text-xs text-gray-500">
                            <span>Pinjam: {{ \Carbon\Carbon::parse($transaction->borrowed_at)->format('d M Y') }}</span>
                            <span class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-blue-600' }}">
                                Kembali: {{ $transaction->due_at ? \Carbon\Carbon::parse($transaction->due_at)->format('d M Y') : '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <i class="fas fa-book text-gray-300 text-4xl mb-3"></i>
                        <p class="font-semibold text-gray-700 mb-1">Tidak Ada Peminjaman Aktif</p>
                        <p class="text-sm text-gray-500 mb-4">Mulai pinjam buku dari koleksi kami</p>
                        <a href="{{ route('books.catalog') }}" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i> Jelajahi Buku
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pre Order --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bookmark text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Pre Order Saya</h3>
                            <p class="text-xs text-gray-500">Buku yang sedang dalam antrian</p>
                        </div>
                    </div>
                    @if($totalPreorders > 0)
                    <a href="{{ route('preorders.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-semibold flex items-center gap-1">
                        Lihat Semua <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    @endif
                </div>

                @forelse($preorders as $preorder)
                    @php
                        $isReady   = $preorder->status === 'ready';
                        $isExpired = $isReady && $preorder->expired_at && \Carbon\Carbon::parse($preorder->expired_at)->isPast();
                    @endphp
                    <div class="mb-3 p-4 rounded-xl border-2 {{ $isReady ? 'border-green-200 bg-green-50' : 'border-purple-100 bg-purple-50' }} hover:shadow-sm transition-all">
                        <div class="flex items-center gap-3">
                            {{-- Cover --}}
                            <div class="w-9 h-12 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                @if($preorder->book?->image)
                                    <img src="{{ asset('storage/' . $preorder->book->image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-purple-100">
                                        <i class="fas fa-bookmark text-purple-400 text-xs"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $preorder->book?->name ?? 'Buku tidak tersedia' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $preorder->book?->writer ?? '-' }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($isReady)
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                            <i class="fas fa-check-circle mr-1"></i>Siap Dipinjam
                                        </span>
                                        @if($preorder->expired_at)
                                            <span class="text-xs text-{{ $isExpired ? 'red' : 'orange' }}-600 font-medium">
                                                Konfirmasi sebelum {{ \Carbon\Carbon::parse($preorder->expired_at)->format('d M Y') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">
                                            <i class="fas fa-hourglass-half mr-1"></i>Antrian #{{ $preorder->queue_position }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- CTA --}}
                            @if($isReady && !$isExpired)
                                <a href="{{ route('preorders.confirm', $preorder->id) }}"
                                   class="flex-shrink-0 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg transition-all">
                                    Konfirmasi
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <i class="fas fa-bookmark text-gray-300 text-4xl mb-3"></i>
                        <p class="font-semibold text-gray-700 mb-1">Belum Ada Pre Order</p>
                        <p class="text-sm text-gray-500 mb-4">Pre order buku yang sedang tidak tersedia</p>
                        <a href="{{ route('books.catalog') }}" class="inline-flex items-center px-5 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700">
                            <i class="fas fa-search mr-2"></i> Cari Buku
                        </a>
                    </div>
                @endforelse

                @if($totalPreorders > 3)
                    <div class="mt-2 text-center">
                        <a href="{{ route('preorders.index') }}" class="text-purple-600 hover:text-purple-700 font-semibold text-sm">
                            Lihat {{ $totalPreorders - 3 }} pre order lainnya →
                        </a>
                    </div>
                @endif
            </div>

        </div>

        {{-- Right Col ── --}}
        <div class="space-y-6">

            {{-- Aksi Cepat --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-bold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="{{ route('books.catalog') }}" class="flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <div class="w-9 h-9 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-search text-white text-sm"></i>
                        </div>
                        <div><p class="font-semibold text-gray-900 text-sm">Cari Buku</p><p class="text-xs text-gray-500">Jelajahi koleksi</p></div>
                    </a>
                    <a href="{{ route('preorders.index') }}" class="flex items-center gap-3 p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <div class="w-9 h-9 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bookmark text-white text-sm"></i>
                        </div>
                        <div><p class="font-semibold text-gray-900 text-sm">Pre Order</p><p class="text-xs text-gray-500">Antrian buku</p></div>
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 p-3 bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors">
                        <div class="w-9 h-9 bg-pink-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-heart text-white text-sm"></i>
                        </div>
                        <div><p class="font-semibold text-gray-900 text-sm">Wishlist</p><p class="text-xs text-gray-500">Buku favorit</p></div>
                    </a>
                    <a href="{{ route('transactions.history') }}" class="flex items-center gap-3 p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <div class="w-9 h-9 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-history text-white text-sm"></i>
                        </div>
                        <div><p class="font-semibold text-gray-900 text-sm">Riwayat</p><p class="text-xs text-gray-500">Semua peminjaman</p></div>
                    </a>
                    <a href="{{ route('fines.index') }}" class="flex items-center gap-3 p-3 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                        <div class="w-9 h-9 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-money-bill-wave text-white text-sm"></i>
                        </div>
                        <div><p class="font-semibold text-gray-900 text-sm">Denda</p><p class="text-xs text-gray-500">Kelola pembayaran</p></div>
                    </a>
                </div>
            </div>

            {{-- Tips --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="font-bold">Tips Membaca</h3>
                </div>
                <ul class="space-y-2 text-sm text-indigo-100">
                    <li class="flex gap-2"><span>📚</span><span>Baca minimal 20 menit setiap hari</span></li>
                    <li class="flex gap-2"><span>⏰</span><span>Kembalikan buku tepat waktu</span></li>
                    <li class="flex gap-2"><span>💡</span><span>Catat hal menarik dari buku</span></li>
                    <li class="flex gap-2"><span>🎯</span><span>Tetapkan target membaca bulanan</span></li>
                </ul>
            </div>

        </div>
    </div>

    {{-- ── Buku Terbaru ── --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-blue-600"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900">Buku Terbaru</h2>
                    <p class="text-xs text-gray-500">Koleksi terbaru perpustakaan</p>
                </div>
            </div>
            <a href="{{ route('books.catalog') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                Lihat Semua <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </div>

        @if($recentBooks->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($recentBooks as $book)
            <div class="group relative">
                <a href="{{ route('books.show', $book->slug) }}" class="block">
                    <div class="rounded-xl overflow-hidden border-2 border-gray-100 hover:border-blue-300 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                        <div class="aspect-[3/4] bg-gray-100 overflow-hidden relative">
                            @if($book->image)
                                <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-book text-gray-300 text-3xl"></i>
                                </div>
                            @endif
                            <span class="absolute top-2 right-2 text-xs {{ $book->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-0.5 rounded-full font-medium shadow">
                                {{ $book->stock > 0 ? 'Tersedia' : 'Habis' }}
                            </span>
                            @if($book->category)
                                <span class="absolute top-2 left-2 text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full font-medium shadow">
                                    {{ $book->category->name }}
                                </span>
                            @endif
                        </div>
                        <div class="p-3 bg-white">
                            <h3 class="font-semibold text-gray-900 line-clamp-2 text-sm group-hover:text-blue-600 transition-colors">{{ $book->name }}</h3>
                            <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $book->writer ?? '-' }}</p>
                        </div>
                    </div>
                </a>
                @if($book->stock > 0 && !$hasActiveFine)
                    <a href="{{ route('transactions.create', ['book_id' => $book->id]) }}"
                       class="absolute bottom-3 left-3 right-3 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-2 rounded-lg shadow opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-1">
                        <i class="fas fa-plus text-xs"></i> Pinjam
                    </a>
                @elseif(!$book->stock)
                    <div class="absolute bottom-3 left-3 right-3 bg-gray-400 text-white text-xs font-bold py-2 rounded-lg text-center opacity-0 group-hover:opacity-100 transition-all">
                        Stok Habis
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
            <div class="text-center py-10">
                <i class="fas fa-book text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">Belum ada buku tersedia</p>
            </div>
        @endif
    </div>

</div>
@endsection