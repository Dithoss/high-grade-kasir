@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="card-friendly p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    Riwayat Transaksi
                </h1>
                <p class="text-gray-600 mt-2">Lihat semua riwayat peminjaman buku Anda</p>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</div>
                    <div class="text-xs text-blue-700 mt-1">Total Transaksi</div>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['returned'] ?? 0 }}</div>
                    <div class="text-xs text-green-700 mt-1">Dikembalikan</div>
                </div>
                <div class="bg-amber-50 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-amber-600">{{ $stats['borrowed'] ?? 0 }}</div>
                    <div class="text-xs text-amber-700 mt-1">Dipinjam</div>
                </div>
                <div class="bg-red-50 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['overdue'] ?? 0 }}</div>
                    <div class="text-xs text-red-700 mt-1">Terlambat</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card-friendly p-6">
        <form method="GET" action="{{ route('transactions.history') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i> Cari Buku
                    </label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Judul buku atau kode transaksi..."
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter mr-1"></i> Status
                    </label>
                    <select
                        name="status"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none bg-white"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>⏳ Menunggu Persetujuan</option>
                        <option value="borrowed"         {{ request('status') == 'borrowed'         ? 'selected' : '' }}>Dipinjam</option>
                        <option value="returned"         {{ request('status') == 'returned'         ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="overdue"          {{ request('status') == 'overdue'          ? 'selected' : '' }}>Terlambat</option>
                        <option value="damaged"          {{ request('status') == 'damaged'          ? 'selected' : '' }}>Rusak</option>
                        <option value="lost"             {{ request('status') == 'lost'             ? 'selected' : '' }}>Hilang</option>
                        <option value="rejected"         {{ request('status') == 'rejected'         ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-1"></i> Dari Tanggal
                    </label>
                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-1"></i> Sampai Tanggal
                    </label>
                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none"
                    >
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="px-6 py-3 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2"
                >
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <a
                    href="{{ route('transactions.history') }}"
                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all duration-200 flex items-center gap-2"
                >
                    <i class="fas fa-redo"></i> Reset
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 ml-auto"
                >
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </form>
    </div>

    <!-- Transaction List -->
    @if($transactions->count() > 0)
        <div class="space-y-4">
            @foreach($transactions as $transaction)
            <div class="card-friendly p-6 hover:shadow-lg transition-shadow
                {{ $transaction->status === 'pending_approval' ? 'border-l-4 border-orange-400' : '' }}
                {{ $transaction->status === 'rejected'         ? 'border-l-4 border-red-400 opacity-75' : '' }}">

                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Transaction Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center flex-wrap gap-2 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $transaction->receipt_number }}
                                    </h3>

                                    {{-- ── STATUS BADGE ── --}}
                                    @switch($transaction->status)

                                        @case('pending_approval')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-hourglass-half"></i>
                                                <span>Menunggu Persetujuan Admin</span>
                                            </span>
                                            @break

                                        @case('rejected')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-ban"></i>
                                                <span>Ditolak Admin</span>
                                            </span>
                                            @break

                                        @case('borrowed')
                                            @php
                                                $daysLeft = \Carbon\Carbon::parse($transaction->due_at)->diffInDays(now(), false);
                                                $isOverdue = $daysLeft > 0;
                                            @endphp
                                            @if($isOverdue)
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <span>Terlambat</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-book-reader"></i>
                                                    <span>Dipinjam</span>
                                                </span>
                                            @endif
                                            @break

                                        @case('return_requested')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-clock"></i>
                                                <span>Pengajuan Pengembalian</span>
                                            </span>
                                            @break

                                        @case('returned')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Dikembalikan</span>
                                            </span>
                                            @break

                                        @case('damaged')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-tools"></i>
                                                <span>Rusak</span>
                                            </span>
                                            @break

                                        @case('lost')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-question-circle"></i>
                                                <span>Hilang</span>
                                            </span>
                                            @break

                                    @endswitch

                                    @if($transaction->is_extended)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                            <i class="fas fa-clock"></i>
                                            <span>Diperpanjang</span>
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Diajukan: {{ \Carbon\Carbon::parse($transaction->borrowed_at)->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        {{-- Info khusus pending_approval --}}
                        @if($transaction->status === 'pending_approval')
                            <div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg flex items-start gap-3">
                                <i class="fas fa-info-circle text-orange-500 mt-0.5 flex-shrink-0"></i>
                                <div class="text-sm text-orange-800">
                                    <span class="font-semibold">Menunggu persetujuan admin.</span>
                                    Permintaan peminjaman Anda sedang ditinjau. Anda akan mendapat notifikasi setelah disetujui atau ditolak.
                                </div>
                            </div>
                        @endif

                        {{-- Info khusus rejected --}}
                        @if($transaction->status === 'rejected')
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                                <i class="fas fa-times-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                                <div class="text-sm text-red-800">
                                    <span class="font-semibold">Permintaan ditolak oleh admin.</span>
                                    Hubungi petugas perpustakaan untuk informasi lebih lanjut.
                                </div>
                            </div>
                        @endif

                        <!-- Books List -->
                        <div class="space-y-2 mb-4">
                            @foreach($transaction->items as $item)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    @if($item->book->image)
                                        <img
                                            src="{{ asset('storage/' . $item->book->image) }}"
                                            alt="{{ $item->book->name }}"
                                            class="w-12 h-16 object-cover rounded"
                                        >
                                    @else
                                        <div class="w-12 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-book text-white"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-gray-900 truncate">{{ $item->book->name }}</h4>
                                        <p class="text-sm text-gray-600">
                                            @if($item->book->writer ?? $item->book->author ?? null)
                                                <i class="fas fa-user mr-1"></i>{{ $item->book->writer ?? $item->book->author }} •
                                            @endif
                                            Jumlah: {{ $item->quantity }} buku
                                        </p>
                                        @if($item->book->category)
                                            <span class="inline-block mt-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                                {{ $item->book->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Timeline — hanya tampil jika bukan pending/rejected -->
                        @if(!in_array($transaction->status, ['pending_approval', 'rejected']))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar-check text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs">Tanggal Pinjam</div>
                                    <div class="font-semibold text-gray-900">
                                        {{ \Carbon\Carbon::parse($transaction->borrowed_at)->format('d M Y') }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar-alt text-amber-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs">Jatuh Tempo</div>
                                    <div class="font-semibold text-gray-900">
                                        {{ \Carbon\Carbon::parse($transaction->due_at)->format('d M Y') }}
                                    </div>
                                </div>
                            </div>

                            @if($transaction->returned_at)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-gray-500 text-xs">Dikembalikan</div>
                                        <div class="font-semibold text-gray-900">
                                            {{ \Carbon\Carbon::parse($transaction->returned_at)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-hourglass-half text-gray-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-gray-500 text-xs">Sisa Waktu</div>
                                        <div class="font-semibold text-gray-900">
                                            @php $daysLeft = \Carbon\Carbon::parse($transaction->due_at)->diffInDays(now(), false); @endphp
                                            @if($daysLeft < 0)
                                                <span class="text-green-600">{{ abs($daysLeft) }} hari lagi</span>
                                            @elseif($daysLeft == 0)
                                                <span class="text-amber-600">Hari ini</span>
                                            @else
                                                <span class="text-red-600">Terlambat {{ $daysLeft }} hari</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endif

                        <!-- Fines -->
                        @if($transaction->fine->isNotEmpty())
                            <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                                <h4 class="font-semibold text-red-800 mb-3 flex items-center gap-2">
                                    <i class="fas fa-exclamation-circle"></i> Denda
                                </h4>
                                @php $totalFine = 0; @endphp
                                @foreach($transaction->fine as $fine)
                                    @php $totalFine += $fine->amount; @endphp
                                    <div class="flex justify-between items-center text-sm mb-2">
                                        <span class="text-red-700">
                                            {{ $fine->type == 'late' ? 'Keterlambatan' : ($fine->type == 'lost' ? 'Hilang' : 'Rusak') }}
                                            @if(isset($fine->late_days) && $fine->late_days > 0)
                                                ({{ $fine->late_days }} hari)
                                            @endif
                                        </span>
                                        <span class="font-bold text-red-800">
                                            Rp {{ number_format($fine->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                                <div class="border-t border-red-200 mt-3 pt-3">
                                    <div class="flex justify-between items-center font-bold text-red-900">
                                        <span>Total Denda:</span>
                                        <span>Rp {{ number_format($totalFine, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex lg:flex-col gap-3 lg:w-48">
                        <a
                            href="{{ route('transactions.show', $transaction->id) }}"
                            class="flex-1 lg:flex-none px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold text-center transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-eye"></i> Detail
                        </a>

                        {{-- Receipt hanya untuk yang sudah aktif dipinjam --}}
                        @if($transaction->status === 'borrowed' && !$transaction->returned_at)
                            <a
                                href="{{ route('transactions.receipt', $transaction->id) }}"
                                class="flex-1 lg:flex-none px-5 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-semibold text-center transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                                target="_blank"
                            >
                                <i class="fas fa-receipt"></i> Bukti
                            </a>
                        @endif

                        @if($transaction->status === 'pending_approval')
                            <span class="flex-1 lg:flex-none px-5 py-3 bg-orange-100 text-orange-700 rounded-xl font-semibold text-center flex items-center justify-center gap-2 text-sm">
                                <i class="fas fa-hourglass-half"></i> Menunggu
                            </span>
                        @endif

                        @if(in_array($transaction->status, ['returned', 'damaged', 'lost']))
                            <button
                                class="flex-1 lg:flex-none px-5 py-3 bg-gray-400 text-white rounded-xl cursor-not-allowed font-semibold flex items-center justify-center gap-2"
                                disabled
                            >
                                <i class="fas fa-check"></i> Selesai
                            </button>
                        @endif

                        @if($transaction->status === 'rejected')
                            <span class="flex-1 lg:flex-none px-5 py-3 bg-red-100 text-red-700 rounded-xl font-semibold text-center flex items-center justify-center gap-2 text-sm">
                                <i class="fas fa-ban"></i> Ditolak
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="card-friendly p-6">
            {{ $transactions->links() }}
        </div>
    @else
        <div class="card-friendly p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-history text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Riwayat Transaksi</h3>
            <p class="text-gray-600 mb-6">Anda belum memiliki riwayat peminjaman buku</p>
            <a
                href="{{ route('books.index') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200"
            >
                <i class="fas fa-book"></i> Jelajahi Koleksi Buku
            </a>
        </div>
    @endif
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        .card-friendly, .card-friendly * { visibility: visible; }
        .card-friendly { position: absolute; left: 0; top: 0; width: 100%; }
        button, a[href*="receipt"] { display: none !important; }
    }
</style>
@endsection