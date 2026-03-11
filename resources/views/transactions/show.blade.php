@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-xl p-6 text-white">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Detail Transaksi</h2>
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                Kembali
            </a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="bg-white rounded-b-xl shadow-lg p-8 space-y-8">

        <!-- RESI & STATUS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="text-center bg-indigo-50 border border-indigo-200 rounded-xl p-6">
                <p class="text-sm text-gray-600 mb-1">Nomor Transaksi</p>
                <p class="text-2xl font-mono font-bold text-indigo-600">#{{ $transaction->receipt_number }}</p>
            </div>

            <div class="text-center rounded-xl p-6 {{ 
                $transaction->status === 'returned' ? 'bg-green-50 border border-green-200' :
                ($transaction->status === 'return_requested' ? 'bg-blue-50 border border-blue-200' :
                ($transaction->status === 'damaged' ? 'bg-orange-50 border border-orange-200' :
                ($transaction->status === 'lost' ? 'bg-red-50 border border-red-200' :
                'bg-yellow-50 border border-yellow-200')))
            }}">
                <p class="text-sm text-gray-600 mb-2">Status</p>
                @switch($transaction->status)
                    @case('borrowed')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Dipinjam
                        </span>
                        @break
                    @case('return_requested')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Menunggu Konfirmasi
                        </span>
                        @break
                    @case('returned')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Dikembalikan
                        </span>
                        @break
                    @case('damaged')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-orange-100 text-orange-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Rusak
                        </span>
                        @break
                    @case('lost')
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Hilang
                        </span>
                        @break
                @endswitch
            </div>
        </div>

        <!-- USER -->
        <div>
            <h3 class="font-bold text-lg mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informasi Peminjam
            </h3>

            <div class="flex items-center bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="h-14 w-14 bg-blue-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                    {{ strtoupper(substr($transaction->user->name, 0, 1)) }}
                </div>
                <div class="ml-4">
                    <p class="font-bold text-gray-900">{{ $transaction->user->name }}</p>
                    <p class="text-sm text-gray-600">{{ $transaction->user->email }}</p>
                </div>
            </div>
        </div>

        <!-- BOOK LIST -->
        <div>
            <h3 class="font-bold text-lg mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Buku Dipinjam
            </h3>

            <div class="space-y-4">
                @foreach ($transaction->items as $item)
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                        <div class="bg-green-50 p-4 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-lg">{{ $item->book->name }}</p>
                                <p class="text-sm text-gray-600">oleh {{ $item->book->writer }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Jumlah</p>
                                <p class="text-2xl font-bold text-green-600">{{ $item->quantity }}</p>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Barcode</p>
                                <p class="font-mono font-semibold">{{ $item->book->barcode }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Kategori</p>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">
                                    {{ $item->book->category?->name ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($transaction->items->isEmpty())
                    <p class="text-sm text-gray-500 italic text-center py-4">
                        Tidak ada buku pada transaksi ini.
                    </p>
                @endif
            </div>
        </div>

        <!-- TIMELINE -->
        <div>
            <h3 class="font-bold text-lg mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Timeline
            </h3>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="font-semibold text-gray-900">Dipinjam</p>
                        <p class="text-sm text-gray-600">{{ $transaction->borrowed_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                @if($transaction->due_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Jatuh Tempo</p>
                            <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($transaction->due_at)->format('d M Y') }}</p>
                            @if($transaction->is_extended)
                                <span class="inline-block mt-1 px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full font-medium">
                                    <i class="fas fa-clock"></i> Sudah Diperpanjang
                                </span>
                            @endif
                            @if($transaction->status === 'borrowed' && now()->gt($transaction->due_at))
                                <span class="inline-block mt-1 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                    Terlambat {{ now()->diffInDays($transaction->due_at) }} hari
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($transaction->returned_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Dikembalikan</p>
                            <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($transaction->returned_at)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start opacity-50">
                        <div class="flex-shrink-0 w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-600">Belum Dikembalikan</p>
                            <p class="text-sm text-gray-500">-</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- EXTENSION HISTORY -->
        @if($transaction->extension_requested_at || $transaction->extension_approved_at || $transaction->is_extended)
        <div>
            <h3 class="font-bold text-lg mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat Perpanjangan
            </h3>

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 space-y-4">
                @if($transaction->extension_requested_at && !$transaction->extension_approved_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Perpanjangan Diajukan</p>
                            <p class="text-sm text-gray-600">{{ $transaction->extension_requested_at->format('d M Y, H:i') }}</p>
                            <span class="inline-block mt-2 px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-semibold">
                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu Persetujuan Admin
                            </span>
                        </div>
                    </div>
                @endif

                @if($transaction->extension_approved_at && $transaction->is_extended)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Perpanjangan Disetujui</p>
                            <p class="text-sm text-gray-600">{{ $transaction->extension_approved_at->format('d M Y, H:i') }}</p>
                            <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Jatuh tempo diperpanjang 7 hari
                            </span>
                            @if($transaction->extended_due_at)
                                <p class="text-xs text-gray-500 mt-1">
                                    Jatuh tempo baru: {{ \Carbon\Carbon::parse($transaction->extended_due_at)->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- FINES (if any) -->
        @if($transaction->fines && $transaction->fines->count() > 0)
        <div>
            <h3 class="font-bold text-lg mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Denda
            </h3>

            <div class="space-y-3">
                @foreach($transaction->fines as $fine)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    @switch($fine->type)
                                        @case('late') Denda Keterlambatan @break
                                        @case('broken') Denda Kerusakan @break
                                        @case('lost') Denda Kehilangan @break
                                        @default Denda @break
                                    @endswitch
                                </p>
                                @if($fine->note)
                                    <p class="text-sm text-gray-600 mt-1">{{ $fine->note }}</p>
                                @endif
                            </div>
                            <span class="text-xl font-bold text-red-600">
                                Rp {{ number_format($fine->amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-red-200">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ 
                                $fine->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' 
                            }}">
                                {{ $fine->status === 'paid' ? 'Lunas' : 'Belum Dibayar' }}
                            </span>
                            @if($fine->paid_at)
                                <span class="text-xs text-gray-600">
                                    Dibayar: {{ \Carbon\Carbon::parse($fine->paid_at)->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- ACTION BUTTONS -->
    @if(auth()->user()->hasRole('user'))
        @if($transaction->status === 'borrowed')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-6">
                {{-- Request Return Button --}}
                <form action="{{ route('request-return', $transaction->id) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all"
                            onclick="return confirm('Ajukan pengembalian buku?')">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Ajukan Pengembalian
                    </button>
                </form>

                {{-- Request Extend Button --}}
                @if($transaction->canBeExtended())
                    <form action="{{ route('transactions.request-extend', $transaction->id) }}" method="POST">
                        @csrf
                        @if($settings->isPreorderEnabled())
                        <button type="submit" 
                                class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all"
                                onclick="return confirm('Ajukan perpanjangan peminjaman?')">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ajukan Perpanjangan
                        </button>
                        @endif
                    </form>
                @elseif($transaction->hasPendingExtension())
                    <div class="w-full py-3 bg-yellow-100 text-yellow-700 text-center rounded-lg font-semibold border-2 border-yellow-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu Persetujuan Perpanjangan
                    </div>
                @elseif($transaction->alreadyExtended())
                    <div class="w-full py-3 bg-gray-100 text-gray-600 text-center rounded-lg font-semibold border-2 border-gray-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Sudah Diperpanjang
                    </div>
                @endif
            </div>
        @endif
    @endif

    @role('admin')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-6">
            @if($transaction->status === 'return_requested')
                <form action="{{ route('confirm-return', $transaction->id) }}" method="POST" class="md:col-span-2">
                    @csrf
                    <button type="submit" 
                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all"
                            onclick="return confirm('Konfirmasi pengembalian buku?')">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Konfirmasi Pengembalian
                    </button>
                </form>
            @endif

            @if($transaction->hasPendingExtension() && $transaction->status === 'borrowed')
                <form action="{{ route('transactions.approve-extend', $transaction->id) }}" method="POST" class="{{ $transaction->status !== 'return_requested' ? 'md:col-span-2' : '' }}">
                    @csrf
                    <button type="submit" 
                            class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all"
                            onclick="return confirm('Setujui perpanjangan peminjaman?')">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Setujui Perpanjangan
                    </button>
                </form>
            @endif

            @if($transaction->status === 'returned')
                <a href="{{ route('transactions.inspect', $transaction->id) }}" 
                   class="md:col-span-2 block w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-center rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Lakukan Inspeksi Buku
                </a>
            @endif
        </div>
    @endrole

</div>
@endsection