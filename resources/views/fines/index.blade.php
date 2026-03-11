    @extends('layouts.app')

    @section('title', 'Denda Saya')

    @section('content')
    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="card-friendly p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                        </div>
                        Denda Saya
                    </h1>
                    <p class="text-gray-600 mt-2">Kelola dan pantau status denda peminjaman buku Anda</p>
                </div>

                {{-- Stats Summary --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-yellow-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $fines->total() }}</div>
                        <div class="text-xs text-yellow-700 mt-1">Total Denda</div>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-lg font-bold text-red-600">
                            Rp {{ number_format($unpaidFinesAmount / 1000, 0, ',', '.') }}rb
                        </div>
                        <div class="text-xs text-red-700 mt-1">Belum Dibayar</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-lg font-bold text-green-600">
                            Rp {{ number_format($paidTotal / 1000, 0, ',', '.') }}rb
                        </div>
                        <div class="text-xs text-green-700 mt-1">Sudah Dibayar</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-5 flex items-center gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-5 flex items-center gap-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                </div>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Unpaid Alert Banner --}}
        @if($unpaidTotal > 0)
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-5 flex items-center gap-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-red-800">Anda memiliki denda yang belum dibayar!</h4>
                    <p class="text-sm text-red-700 mt-0.5">
                        Total tagihan: <strong>Rp {{ number_format($unpaidTotal, 0, ',', '.') }}</strong>. Segera selesaikan agar peminjaman berikutnya tidak terganggu.
                    </p>
                </div>
            </div>
        @endif

        {{-- Fine Cards --}}
        @if($fines->count() > 0)
            <div class="space-y-4">
                @foreach($fines as $fine)
                    <div class="card-friendly p-6 hover:shadow-lg transition-shadow
                        @if($fine->status === 'unpaid') border-l-4 border-red-400
                        @elseif($fine->status === 'pending_confirmation') border-l-4 border-yellow-400
                        @else border-l-4 border-green-400
                        @endif
                    ">
                        <div class="flex flex-col lg:flex-row gap-6">

                            {{-- Left: Info --}}
                            <div class="flex-1">
                                {{-- Top Row: Receipt + Badges --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <div class="flex items-center flex-wrap gap-2 mb-2">
                                            <h3 class="text-lg font-bold text-gray-900">
                                                #{{ $fine->transaction->receipt_number }}
                                            </h3>

                                            {{-- Fine Type Badge --}}
                                            @switch($fine->type)
                                                @case('late')
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-clock"></i> Keterlambatan
                                                    </span>
                                                    @break
                                                @case('broken')
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-tools"></i> Kerusakan
                                                    </span>
                                                    @break
                                                @case('lost')
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-question-circle"></i> Kehilangan
                                                    </span>
                                                    @break
                                                @case('manual')
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-edit"></i> Manual
                                                    </span>
                                                    @break
                                            @endswitch

                                            {{-- Status Badge --}}
                                            @if($fine->status === 'paid')
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-check-circle"></i> Lunas
                                                </span>
                                            @elseif($fine->status === 'pending_confirmation')
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-hourglass-half"></i> Menunggu Konfirmasi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-times-circle"></i> Belum Dibayar
                                                </span>
                                            @endif

                                            {{-- Payment Method Badge (if set) --}}
                                            @if($fine->payment_method === 'stripe' && $fine->status !== 'unpaid')
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-credit-card"></i> Stripe
                                                </span>
                                            @elseif($fine->payment_method === 'cash' && $fine->status !== 'unpaid')
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-money-bill-alt"></i> Tunai
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Denda dibuat: {{ $fine->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Book Info --}}
                                @if($fine->transaction->items->isNotEmpty())
                                    <div class="space-y-2 mb-4">
                                        @foreach($fine->transaction->items as $item)
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
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900">{{ $item->book->name }}</h4>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-user mr-1"></i>{{ $item->book->author }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Detail Info Row --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    {{-- Fine Amount --}}
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-money-bill-wave text-red-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 text-xs">Jumlah Denda</div>
                                            <div class="font-bold text-red-700 text-base">
                                                Rp {{ number_format($fine->amount, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Late Days --}}
                                    @if($fine->late_days > 0)
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-calendar-times text-orange-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-gray-500 text-xs">Hari Terlambat</div>
                                                <div class="font-semibold text-gray-900">{{ $fine->late_days }} hari</div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Paid At / Requested At --}}
                                    @if($fine->paid_at)
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-gray-500 text-xs">Tanggal Bayar</div>
                                                <div class="font-semibold text-gray-900">
                                                    {{ \Carbon\Carbon::parse($fine->paid_at)->format('d M Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($fine->status === 'pending_confirmation' && $fine->payment_requested_at)
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-hourglass-half text-yellow-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-gray-500 text-xs">Diminta Bayar</div>
                                                <div class="font-semibold text-gray-900">
                                                    {{ \Carbon\Carbon::parse($fine->payment_requested_at)->format('d M Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Right: Actions --}}
                            <div class="flex lg:flex-col gap-3 lg:w-52 lg:justify-start">
                                @if($fine->status === 'unpaid')

                                    {{-- Online Payment via Stripe --}}
                                    <!-- <form action="{{ route('fines.pay', $fine->id) }}" method="POST" class="flex-1 lg:flex-none">
                                        @csrf
                                        <input type="hidden" name="payment_method" value="stripe">
                                        @if($settings->isOnlinePaymentEnabled())
                                        <button type="submit"
                                            class="w-full px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold text-center transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                            <i class="fas fa-credit-card"></i>
                                            <span>Bayar Online</span>
                                        </button>
                                        @endif
                                    </form> -->

                                    {{-- Cash Payment --}}
                                    <form action="{{ route('fines.pay', $fine->id) }}" method="POST" class="flex-1 lg:flex-none">
                                        @csrf
                                        <input type="hidden" name="payment_method" value="cash">
                                        @if($settings->isCashPaymentEnabled())
                                        <button type="submit"
                                            class="w-full px-5 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-semibold text-center transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                            <i class="fas fa-money-bill-alt"></i>
                                            <span>Bayar Tunai</span>
                                        </button>
                                        @endif
                                    </form>

                                @elseif($fine->status === 'pending_confirmation')
                                    <div class="flex-1 lg:flex-none px-5 py-3 bg-yellow-50 border-2 border-yellow-200 text-yellow-700 rounded-xl font-semibold text-center flex items-center justify-center gap-2">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span>Menunggu Konfirmasi Admin</span>
                                    </div>

                                @else
                                    <div class="flex-1 lg:flex-none px-5 py-3 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl font-semibold text-center flex items-center justify-center gap-2">
                                        <i class="fas fa-check-double"></i>
                                        <span>Sudah Lunas</span>
                                    </div>
                                @endif

                                {{-- Link to Transaction Detail --}}
                                <a
                                    href="{{ route('transactions.show', $fine->transaction->id) }}"
                                    class="flex-1 lg:flex-none px-5 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold text-center transition-all duration-200 flex items-center justify-center gap-2"
                                >
                                    <i class="fas fa-eye"></i>
                                    <span>Lihat Transaksi</span>
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="card-friendly p-6">
                {{ $fines->links() }}
            </div>

        @else
            {{-- Empty State --}}
            <div class="card-friendly p-12 text-center">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-double text-4xl text-green-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Denda!</h3>
                <p class="text-gray-600 mb-6">Anda tidak memiliki denda apapun. Terus jaga rekam jejak peminjaman Anda!</p>
                <a
                    href="{{ route('transactions.history') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200"
                >
                    <i class="fas fa-history"></i>
                    <span>Lihat Riwayat Transaksi</span>
                </a>
            </div>
        @endif

    </div>
    @endsection