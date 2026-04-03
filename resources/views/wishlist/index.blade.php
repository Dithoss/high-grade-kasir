@extends('layouts.app')

@section('title', 'Wishlist Saya')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- ── Header ── --}}
    <div class="bg-gradient-to-r from-pink-600 to-rose-600 rounded-t-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Wishlist Saya</h2>
                    <p class="text-pink-100 text-sm">Koleksi buku favorit yang ingin saya baca</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold" id="wishlist-count">{{ $wishlists->count() }}</p>
                <p class="text-pink-100 text-sm">Buku tersimpan</p>
            </div>
        </div>
    </div>

    {{-- ── Content ── --}}
    <div class="bg-white rounded-b-xl shadow-lg p-6">
        @if($wishlists->count() > 0)

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6" id="wishlist-grid">
                @foreach($wishlists as $wishlist)
                    @php $book = $wishlist->book; @endphp

                    <div class="group relative" id="wishlist-card-{{ $book->id }}">

                        {{-- Book link --}}
                        <a href="{{ route('books.show', $book->slug) }}" class="block">
                            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1">

                                {{-- Cover --}}
                                <div class="aspect-[3/4] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden relative">
                                    @if($book->image)
                                        <img src="{{ asset('storage/' . $book->image) }}"
                                             alt="{{ $book->name }}"
                                             class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    @endif

                                    {{-- Stock badge --}}
                                    <div class="absolute top-2 right-2">
                                        <span class="text-xs {{ $book->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-1 rounded-full font-medium shadow-lg">
                                            {{ $book->stock > 0 ? 'Tersedia' : 'Habis' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 line-clamp-2 mb-2 group-hover:text-pink-600 transition-colors">
                                        {{ $book->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-1">{{ $book->writer }}</p>
                                    <p class="text-xs text-gray-400 mb-3">{{ $book->category?->name ?? 'Uncategorized' }}</p>
                                    <div class="flex items-center text-xs text-gray-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Ditambahkan {{ $wishlist->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </a>

                        {{-- ── Remove button ──
                             data-toggle-url pakai route helper Laravel → dijamin pakai ID, bukan slug --}}
                        <button
                            type="button"
                            data-book-id="{{ $book->id }}"
                            data-toggle-url="{{ route('wishlist.toggle', $book->id) }}"
                            onclick="removeFromWishlist(this, event)"
                            class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center z-10 group-hover:scale-110"
                            title="Hapus dari wishlist"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- ── Pinjam button — pakai <a> biasa, ini GET ke transactions.create ── --}}
                        @if($book->stock > 0)
                            <a
                                href="{{ route('transactions.create', ['book_id' => $book->id]) }}"
                                onclick="event.stopPropagation()"
                                class="absolute bottom-4 left-4 right-4 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 opacity-0 group-hover:opacity-100 flex items-center justify-center space-x-1 z-10"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span>Pinjam</span>
                            </a>
                        @endif

                    </div>
                @endforeach
            </div>

            @if(method_exists($wishlists, 'links'))
                <div class="mt-8">{{ $wishlists->links() }}</div>
            @endif

        @else
            {{-- ── Empty state ── --}}
            <div class="text-center py-16">
                <div class="w-32 h-32 bg-gradient-to-br from-pink-100 to-rose-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-16 h-16 text-pink-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Wishlist Masih Kosong</h3>
                <p class="text-gray-500 mb-6">Anda belum menambahkan buku ke wishlist</p>
                <a href="{{ route('books.index') }}"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-600 to-rose-600 text-white rounded-lg font-semibold hover:from-pink-700 hover:to-rose-700 shadow-lg hover:shadow-xl transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Jelajahi Koleksi Buku
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
/**
 * @param {HTMLButtonElement} btn  — tombol yang diklik (berisi data-book-id & data-toggle-url)
 * @param {MouseEvent}        e    — event dari onclick
 */
function removeFromWishlist(btn, e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    if (!confirm('Hapus buku ini dari wishlist?')) return;

    const bookId    = btn.dataset.bookId;
    const toggleUrl = btn.dataset.toggleUrl;   // URL sudah benar dari route helper Laravel
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken) { alert('CSRF token tidak ditemukan'); return; }

    btn.disabled = true;

    fetch(toggleUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept'      : 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        if (data.status === 'removed') {
            const card = document.getElementById('wishlist-card-' + bookId);
            if (card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(0.85)';
                setTimeout(() => {
                    card.remove();
                    // Update counter di header
                    const counter = document.getElementById('wishlist-count');
                    if (counter) counter.textContent = Math.max(0, parseInt(counter.textContent) - 1);
                    // Tampilkan empty state jika grid sudah kosong
                    const grid = document.getElementById('wishlist-grid');
                    if (grid && grid.children.length === 0) window.location.reload();
                }, 300);
            } else {
                window.location.reload();
            }
        } else {
            alert('Terjadi kesalahan saat menghapus dari wishlist');
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan: ' + err.message);
        btn.disabled = false;
    });
}
</script>
@endpush
@endsection