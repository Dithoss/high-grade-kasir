@extends('layouts.app')
@section('title', 'Daftar Transaksi')
@section('header')
    @role('admin')
        Daftar Transaksi
        @if(isset($pendingCount) && $pendingCount > 0)
            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 bg-orange-500 text-white text-xs font-bold rounded-full animate-pulse">
                {{ $pendingCount }} menunggu
            </span>
        @endif
    @else
        Transaksi Saya
    @endrole
@endsection
@section('subtitle')
    @role('admin')
        Kelola semua peminjaman buku
    @else
        Riwayat peminjaman buku Anda
    @endrole
@endsection

@section('content')
{{-- Header Actions --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-wrap gap-2">
        @role('admin')
            <a href="{{ route('transactions.trash') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-trash mr-2"></i> Lihat Dihapus
            </a>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2"></i> Pinjam Buku
            </a>
        @else
            @php
                $hasActiveFine = auth()->user()->transactions()
                    ->whereHas('fine', function ($q) {
                        $q->whereIn('status', ['unpaid', 'pending_confirmation']);
                    })
                    ->exists();
            @endphp

            @if($hasActiveFine)
                <button type="button" onclick="showFineWarning()"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed opacity-75 shadow-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Pinjam Buku
                </button>
            @else
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2"></i> Pinjam Buku
                </a>
            @endif
        @endrole
    </div>
</div>

{{-- Fine Warning Alert --}}
@role('user')
    @if(isset($hasActiveFine) && $hasActiveFine)
    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6 shadow-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-red-800">Peminjaman Ditangguhkan</h3>
                <p class="mt-1 text-sm text-red-700">Anda tidak dapat meminjam buku karena masih memiliki denda yang belum dibayar.</p>
                <div class="mt-3">
                    <a href="{{ route('fines.index') }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all">
                        <i class="fas fa-money-bill-wave mr-2"></i> Bayar Denda Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endrole

{{-- Pending Approval Banner (Admin) --}}
@role('admin')
    @if(isset($pendingCount) && $pendingCount > 0)
    <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 mb-6 shadow-md">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="h-6 w-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-orange-800">
                        {{ $pendingCount }} Peminjaman Menunggu Persetujuan
                    </h3>
                    <p class="mt-0.5 text-sm text-orange-700">Terdapat permintaan peminjaman yang perlu Anda tinjau dan setujui.</p>
                </div>
            </div>
            {{-- Quick filter ke pending_approval --}}
            <a href="{{ route('transactions.index', ['status' => 'pending_approval']) }}"
               class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm">
                <i class="fas fa-eye mr-2"></i> Lihat Semua
            </a>
        </div>
    </div>
    @endif
@endrole

{{-- Filter Section --}}
<div class="bg-white rounded-xl shadow-md mb-6 overflow-hidden">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
        <h5 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            Filter & Pencarian
        </h5>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('transactions.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="Cari resi/peminjam..."
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Semua Status</option>
                        @role('admin')
                            <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>
                                ⏳ Menunggu Approval
                            </option>
                        @endrole
                        <option value="borrowed"         {{ request('status') == 'borrowed'         ? 'selected' : '' }}>Dipinjam</option>
                        <option value="return_requested" {{ request('status') == 'return_requested' ? 'selected' : '' }}>Pengajuan Kembali</option>
                        <option value="returned"         {{ request('status') == 'returned'         ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="damaged"          {{ request('status') == 'damaged'          ? 'selected' : '' }}>Rusak</option>
                        <option value="lost"             {{ request('status') == 'lost'             ? 'selected' : '' }}>Hilang</option>
                        @role('admin')
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        @endrole
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" name="date_from"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" name="date_to"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           value="{{ request('date_to') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan</label>
                    <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Default</option>
                        <option value="borrowed_at" {{ request('sort') == 'borrowed_at' ? 'selected' : '' }}>Tanggal Pinjam</option>
                        <option value="due_at"      {{ request('sort') == 'due_at'      ? 'selected' : '' }}>Tanggal Kembali</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 mt-4">
                <button type="submit" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-search mr-2"></i> Terapkan Filter
                </button>
                <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">

        {{-- Desktop Table --}}
        <div class="hidden md:block">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No Resi</th>
                        @role('admin')
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Peminjam</th>
                        @endrole
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Buku</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal Pinjam</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($transactions as $item)
                    {{-- Highlight baris pending_approval --}}
                    <tr class="hover:bg-gray-50 transition-colors {{ $item->status === 'pending_approval' ? 'bg-orange-50/40' : '' }}">

                        {{-- No Resi --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                                #{{ substr($item->receipt_number, 0, 8) }}
                            </span>
                        </td>

                        {{-- Peminjam (admin only) --}}
                        @role('admin')
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative flex-shrink-0">
                                    @if($item->user->image ?? $item->user->profile_photo_path ?? false)
                                        <img src="{{ asset('storage/' . ($item->user->image ?? $item->user->profile_photo_path)) }}"
                                             alt="{{ $item->user->name }}"
                                             class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-md">
                                    @else
                                        @php
                                            $colors     = ['from-blue-500 to-blue-700','from-purple-500 to-purple-700','from-green-500 to-green-700','from-pink-500 to-pink-700','from-orange-500 to-orange-700','from-teal-500 to-teal-700'];
                                            $colorIndex = crc32($item->user->name) % count($colors);
                                            $avatarColor = $colors[abs($colorIndex)];
                                        @endphp
                                        <div class="w-11 h-11 bg-gradient-to-br {{ $avatarColor }} rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md ring-2 ring-white">
                                            {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 truncate">{{ $item->user->name }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $item->user->email }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($item->user->getRoleNames() as $role)
                                            @php
                                                $roleStyle = match($role) {
                                                    'admin'     => 'bg-red-100 text-red-700 border border-red-200',
                                                    'user'      => 'bg-blue-100 text-blue-700 border border-blue-200',
                                                    'librarian' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                                    default     => 'bg-gray-100 text-gray-600 border border-gray-200',
                                                };
                                                $roleIcon = match($role) {
                                                    'admin'     => 'fas fa-shield-alt',
                                                    'user'      => 'fas fa-user',
                                                    'librarian' => 'fas fa-book',
                                                    default     => 'fas fa-circle',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $roleStyle }}">
                                                <i class="{{ $roleIcon }} text-[9px]"></i> {{ ucfirst($role) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endrole

                        {{-- Buku --}}
                        <td class="px-6 py-4">
                            @php $firstItem = $item->items->first(); $totalItems = $item->items->count(); @endphp
                            @if($firstItem)
                            <div>
                                <div class="font-semibold text-gray-900">{{ $firstItem->book->name }}</div>
                                <div class="text-sm text-gray-500">Qty: {{ $firstItem->quantity }}</div>
                                @if($totalItems > 1)
                                    <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full mt-1">
                                        +{{ $totalItems - 1 }} buku lain
                                    </span>
                                @endif
                            </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        {{-- Tanggal Pinjam --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item->borrowed_at->format('d M Y') }}
                        </td>

                        {{-- Jatuh Tempo --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->due_at)
                                <div class="text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($item->due_at)->format('d M Y') }}
                                    @if($item->is_extended)
                                        <span class="block text-xs text-purple-600 font-medium mt-1">
                                            <i class="fas fa-clock"></i> Diperpanjang
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($item->status)
                                @case('pending_approval')
                                    <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                                        <i class="fas fa-hourglass-half mr-1"></i> Menunggu Approval
                                    </span>
                                    @break
                                @case('borrowed')
                                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                        <i class="fas fa-book-open mr-1"></i> Dipinjam
                                    </span>
                                    @break
                                @case('return_requested')
                                    <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                                        <i class="fas fa-clock mr-1"></i> Menunggu Konfirmasi
                                    </span>
                                    @break
                                @case('returned')
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Dikembalikan
                                    </span>
                                    @break
                                @case('damaged')
                                    <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Rusak
                                    </span>
                                    @break
                                @case('lost')
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i> Hilang
                                    </span>
                                    @break
                                @case('rejected')
                                    <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                        <i class="fas fa-ban mr-1"></i> Ditolak
                                    </span>
                                    @break
                            @endswitch
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2 flex-wrap">
                                <a href="{{ route('transactions.show', $item->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg transition-all">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>

                                {{-- ── USER ACTIONS ── --}}
                                @role('user')
                                    @if($item->status === 'pending_approval')
                                        <span class="inline-flex items-center px-3 py-1.5 bg-orange-100 text-orange-700 text-xs font-medium rounded-lg">
                                            <i class="fas fa-hourglass-half mr-1"></i> Menunggu Admin
                                        </span>
                                    @elseif($item->canBeExtended())
                                        <form action="{{ route('transactions.request-extend', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-lg transition-all"
                                                    onclick="return confirm('Ajukan perpanjangan peminjaman?')">
                                                <i class="fas fa-clock mr-1"></i> Perpanjang
                                            </button>
                                        </form>
                                    @elseif($item->hasPendingExtension())
                                        <span class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg">
                                            <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                                        </span>
                                    @elseif($item->alreadyExtended())
                                        <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-lg">
                                            <i class="fas fa-check mr-1"></i> Sudah Diperpanjang
                                        </span>
                                    @endif
                                @endrole

                                {{-- ── ADMIN ACTIONS ── --}}
                                @role('admin')
                                    {{-- Approve / Reject untuk pending_approval --}}
                                    @if($item->status === 'pending_approval')
                                        <form action="{{ route('transactions.approve', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-lg transition-all"
                                                    onclick="return confirm('Setujui peminjaman ini?')">
                                                <i class="fas fa-check-circle mr-1"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('transactions.reject', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-all"
                                                    onclick="return confirm('Tolak peminjaman ini? Tindakan ini tidak dapat dibatalkan.')">
                                                <i class="fas fa-times-circle mr-1"></i> Tolak
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Setujui perpanjangan --}}
                                    @if($item->hasPendingExtension() && $item->status === 'borrowed')
                                        <form action="{{ route('transactions.approve-extend', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-lg transition-all"
                                                    onclick="return confirm('Setujui perpanjangan peminjaman?')">
                                                <i class="fas fa-check mr-1"></i> Setujui Perpanjang
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Konfirmasi pengembalian --}}
                                    @if($item->status === 'return_requested')
                                        <form action="{{ route('confirm-return', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-lg transition-all"
                                                    onclick="return confirm('Konfirmasi pengembalian buku ini?')">
                                                <i class="fas fa-check-circle mr-1"></i> Konfirmasi
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Inspeksi --}}
                                    @if($item->status === 'returned')
                                        <a href="{{ route('transactions.inspect', $item->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium rounded-lg transition-all">
                                            <i class="fas fa-search mr-1"></i> Inspeksi
                                        </a>
                                    @endif

                                    {{-- Update status manual — tidak untuk status final atau pending --}}
                                    @if(!in_array($item->status, ['damaged', 'lost', 'rejected', 'pending_approval']))
                                        <a href="{{ route('transactions.edit', $item->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition-all">
                                            <i class="fas fa-edit mr-1"></i> Update
                                        </a>
                                    @endif

                                    {{-- Hapus --}}
                                    <form action="{{ route('transactions.destroy', $item->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-all"
                                                onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasRole('admin') ? '7' : '6' }}" class="px-6 py-12">
                            <div class="text-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium mb-2">
                                    @role('admin') Tidak ada transaksi @else Anda belum memiliki transaksi peminjaman @endrole
                                </p>
                                @role('user')
                                    @if(!isset($hasActiveFine) || !$hasActiveFine)
                                        <a href="{{ route('transactions.create') }}"
                                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg mt-2">
                                            Mulai pinjam buku sekarang
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('transactions.create') }}"
                                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg mt-2">
                                        Buat transaksi peminjaman baru
                                    </a>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse ($transactions as $item)
            <div class="p-4 hover:bg-gray-50 transition-colors {{ $item->status === 'pending_approval' ? 'bg-orange-50/40' : '' }}">
                <div class="flex justify-between items-start mb-3">
                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                        #{{ substr($item->receipt_number, 0, 8) }}
                    </span>
                    @switch($item->status)
                        @case('pending_approval')
                            <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu Approval
                            </span>
                            @break
                        @case('borrowed')
                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                <i class="fas fa-book-open mr-1"></i> Dipinjam
                            </span>
                            @break
                        @case('return_requested')
                            <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                                <i class="fas fa-clock mr-1"></i> Menunggu
                            </span>
                            @break
                        @case('returned')
                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                <i class="fas fa-check-circle mr-1"></i> Dikembalikan
                            </span>
                            @break
                        @case('damaged')
                            <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Rusak
                            </span>
                            @break
                        @case('lost')
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                                <i class="fas fa-times-circle mr-1"></i> Hilang
                            </span>
                            @break
                        @case('rejected')
                            <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                <i class="fas fa-ban mr-1"></i> Ditolak
                            </span>
                            @break
                    @endswitch
                </div>

                {{-- Peminjam Mobile (admin only) --}}
                @role('admin')
                <div class="flex items-center gap-3 mb-3">
                    @if($item->user->image ?? $item->user->profile_photo_path ?? false)
                        <img src="{{ asset('storage/' . ($item->user->image ?? $item->user->profile_photo_path)) }}"
                             alt="{{ $item->user->name }}"
                             class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow">
                    @else
                        @php
                            $colors      = ['from-blue-500 to-blue-700','from-purple-500 to-purple-700','from-green-500 to-green-700','from-pink-500 to-pink-700','from-orange-500 to-orange-700','from-teal-500 to-teal-700'];
                            $colorIndex  = crc32($item->user->name) % count($colors);
                            $avatarColor = $colors[abs($colorIndex)];
                        @endphp
                        <div class="w-10 h-10 bg-gradient-to-br {{ $avatarColor }} rounded-full flex items-center justify-center text-white font-bold text-sm shadow flex-shrink-0">
                            {{ strtoupper(substr($item->user->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">{{ $item->user->name }}</div>
                        <div class="flex flex-wrap gap-1 mt-0.5">
                            @foreach($item->user->getRoleNames() as $role)
                                @php
                                    $roleStyle = match($role) { 'admin' => 'bg-red-100 text-red-700 border border-red-200', 'user' => 'bg-blue-100 text-blue-700 border border-blue-200', default => 'bg-gray-100 text-gray-600 border border-gray-200' };
                                    $roleIcon  = match($role) { 'admin' => 'fas fa-shield-alt', 'user' => 'fas fa-user', default => 'fas fa-circle' };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $roleStyle }}">
                                    <i class="{{ $roleIcon }} text-[9px]"></i> {{ ucfirst($role) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endrole

                @php $firstItem = $item->items->first(); @endphp
                @if($firstItem)
                <div class="mb-3">
                    <div class="font-semibold text-gray-900">{{ $firstItem->book->name }}</div>
                </div>
                @endif

                <div class="text-sm text-gray-500 mb-4 space-y-1">
                    <div><i class="fas fa-calendar-alt mr-2 text-gray-400"></i>Pinjam: {{ $item->borrowed_at->format('d M Y') }}</div>
                    @if($item->due_at)
                        <div>
                            <i class="fas fa-calendar-check mr-2 text-gray-400"></i>Jatuh Tempo: {{ \Carbon\Carbon::parse($item->due_at)->format('d M Y') }}
                            @if($item->is_extended)
                                <span class="text-xs text-purple-600 font-medium ml-1">(Diperpanjang)</span>
                            @endif
                        </div>
                    @endif
                    <div>
                        <i class="fas fa-boxes mr-2 text-gray-400"></i>Qty: {{ $item->items->sum('quantity') }}
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('transactions.show', $item->id) }}"
                       class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg transition-all">
                        <i class="fas fa-eye mr-1"></i> Detail
                    </a>

                    @role('user')
                        @if($item->status === 'pending_approval')
                            <span class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-orange-100 text-orange-700 text-xs font-medium rounded-lg">
                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu Admin
                            </span>
                        @elseif($item->canBeExtended())
                            <form action="{{ route('transactions.request-extend', $item->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-lg transition-all"
                                        onclick="return confirm('Ajukan perpanjangan?')">
                                    <i class="fas fa-clock mr-1"></i> Perpanjang
                                </button>
                            </form>
                        @endif
                    @endrole

                    @role('admin')
                        @if($item->status === 'pending_approval')
                            <form action="{{ route('transactions.approve', $item->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-lg transition-all"
                                        onclick="return confirm('Setujui peminjaman?')">
                                    <i class="fas fa-check-circle mr-1"></i> Setujui
                                </button>
                            </form>
                            <form action="{{ route('transactions.reject', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-all"
                                        onclick="return confirm('Tolak peminjaman?')">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </form>
                        @endif

                        @if($item->hasPendingExtension() && $item->status === 'borrowed')
                            <form action="{{ route('transactions.approve-extend', $item->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-lg transition-all"
                                        onclick="return confirm('Setujui perpanjangan?')">
                                    <i class="fas fa-check mr-1"></i> Setujui
                                </button>
                            </form>
                        @endif

                        @if($item->status === 'return_requested')
                            <form action="{{ route('confirm-return', $item->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-lg transition-all"
                                        onclick="return confirm('Konfirmasi pengembalian?')">
                                    <i class="fas fa-check-circle mr-1"></i> Konfirmasi
                                </button>
                            </form>
                        @elseif(!in_array($item->status, ['damaged', 'lost', 'rejected', 'pending_approval']))
                            <a href="{{ route('transactions.edit', $item->id) }}"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition-all">
                                <i class="fas fa-edit mr-1"></i> Update
                            </a>
                        @endif

                        <form action="{{ route('transactions.destroy', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-all"
                                    onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endrole
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 mb-3">Tidak ada transaksi</p>
                @role('user')
                    @if(!isset($hasActiveFine) || !$hasActiveFine)
                        <a href="{{ route('transactions.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                            Mulai pinjam buku sekarang
                        </a>
                    @endif
                @else
                    <a href="{{ route('transactions.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                        Buat transaksi peminjaman baru
                    </a>
                @endrole
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function showFineWarning() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Peminjaman Ditangguhkan',
            html: '<p class="text-gray-700">Anda tidak dapat meminjam buku karena masih memiliki denda yang belum dibayar atau sedang menunggu konfirmasi pembayaran.</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-money-bill-wave mr-2"></i>Bayar Denda',
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("fines.index") }}';
            }
        });
    } else {
        if (confirm('Anda tidak dapat meminjam buku karena masih memiliki denda yang belum dibayar.\n\nKlik OK untuk membayar denda sekarang.')) {
            window.location.href = '{{ route("fines.index") }}';
        }
    }
}
</script>
@endpush