@extends('layouts.app')

@section('title', 'Pinjam Buku Baru')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg p-8 text-white mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold mb-1">Pinjam Buku Baru 📚</h1>
                <p class="text-blue-100">Pilih buku via barcode scanner atau pilih manual dari koleksi</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('transactions.store') }}" id="transactionForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">

                @role('admin')
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Pilih Peminjam
                    </h3>
                    <select name="user_id"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none @error('user_id') border-red-500 @enderror"
                        required>
                        <option value="">-- Pilih Member --</option>
                        @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'user'))->get() as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                @endrole

                <!-- Tanggal -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tanggal Peminjaman
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pinjam <span class="text-red-500">*</span></label>
                            <input type="date" name="borrowed_at"
                                value="{{ old('borrowed_at', date('Y-m-d')) }}"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all outline-none @error('borrowed_at') border-red-500 @enderror"
                                required>
                            @error('borrowed_at')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kembali <span class="text-red-500">*</span></label>
                            <input type="date" name="due_at"
                                value="{{ old('due_at', date('Y-m-d', strtotime('+7 days'))) }}"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all outline-none @error('due_at') border-red-500 @enderror"
                                required>
                            @error('due_at')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            <p class="mt-2 text-xs text-gray-500">Biasanya 7–14 hari dari tanggal pinjam</p>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════
                     BARCODE SCANNER SECTION (BARU)
                ═══════════════════════════════════════════════════════ -->
                <div class="bg-white rounded-xl shadow-md p-6 border-2 border-dashed border-indigo-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Scan / Input Barcode
                        <span class="ml-1 text-xs font-medium bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">Cepat</span>
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">Scan barcode buku menggunakan scanner atau ketik manual lalu tekan Enter / klik tombol Tambah.</p>

                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="barcodeInput"
                                placeholder="Scan atau ketik barcode buku..."
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-3 border-2 border-indigo-200 rounded-lg focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none font-mono text-sm"
                            >
                        </div>
                        <button
                            type="button"
                            id="btnScanBarcode"
                            class="inline-flex items-center px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all gap-2 whitespace-nowrap"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah
                        </button>
                    </div>

                    <!-- Feedback barcode -->
                    <div id="barcodeFeedback" class="hidden mt-3 p-3 rounded-lg text-sm font-medium flex items-center gap-2"></div>

                    <!-- Barcode preview card (muncul saat buku ditemukan) -->
                    <div id="barcodePreview" class="hidden mt-3 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div id="barcodePreviewCover" class="w-12 h-16 rounded overflow-hidden flex-shrink-0 bg-indigo-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="barcodePreviewName" class="font-bold text-gray-900 truncate"></p>
                                <p id="barcodePreviewWriter" class="text-sm text-gray-500 truncate"></p>
                                <p id="barcodePreviewStock" class="text-xs font-semibold mt-0.5"></p>
                            </div>
                            <div class="flex-shrink-0">
                                <span id="barcodePreviewBadge" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Buku Terpilih -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Buku yang Dipilih
                            <span class="ml-1 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-full" id="bookCount">0</span>
                        </h3>
                        <button type="button" id="btnOpenModal"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-semibold transition-all shadow-md hover:shadow-lg gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Pilih dari Katalog
                        </button>
                    </div>
                    <div id="selectedBooksList" class="space-y-3">
                        <div id="emptySelectedState" class="text-center py-10 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="font-semibold">Belum ada buku dipilih</p>
                            <p class="text-sm mt-1">Scan barcode atau klik "Pilih dari Katalog"</p>
                        </div>
                    </div>
                    @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-md p-6 border-2 border-blue-200 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ringkasan Peminjaman
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-blue-200">
                            <span class="text-sm text-gray-600">Total Buku</span>
                            <span class="text-lg font-bold text-gray-900" id="summaryBookCount">0</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-blue-200">
                            <span class="text-sm text-gray-600">Total Item</span>
                            <span class="text-lg font-bold text-gray-900" id="summaryTotalItems">0</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-600">Durasi Pinjam</span>
                            <span class="text-lg font-bold text-gray-900" id="summaryDuration">-</span>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-blue-200 space-y-3">
                        <button type="submit" id="submitBtn" disabled
                            class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg font-bold shadow-lg transition-all flex items-center justify-center opacity-50 cursor-not-allowed">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Konfirmasi Peminjaman
                        </button>
                        <a href="{{ route('transactions.index') }}"
                            class="w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batal
                        </a>
                    </div>
                </div>

                <!-- Cara Input Buku -->
                <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cara Menambah Buku
                    </h4>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-indigo-50 rounded-lg">
                            <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5">1</div>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">Via Barcode Scanner</p>
                                <p class="text-xs text-indigo-600 mt-0.5">Arahkan scanner ke barcode buku, buku langsung ditambahkan otomatis</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg">
                            <div class="w-7 h-7 bg-purple-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5">2</div>
                            <div>
                                <p class="text-sm font-semibold text-purple-800">Via Pilih Katalog</p>
                                <p class="text-xs text-purple-600 mt-0.5">Klik "Pilih dari Katalog" untuk browse dan pilih buku secara manual</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl shadow-md p-6 border-2 border-amber-200">
                    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tips Peminjaman
                    </h4>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Maksimal peminjaman 7–14 hari</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Kembalikan buku tepat waktu</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Jaga kondisi buku dengan baik</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Denda keterlambatan: Rp 1.000/hari</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ============================================================ --}}
{{--   MODAL KATALOG BUKU                                         --}}
{{-- ============================================================ --}}
<div id="bookModal" style="display:none; position:fixed; inset:0; z-index:9999; overflow:hidden;">
    <div id="modalBackdrop" style="position:absolute; inset:0; background:rgba(17,24,39,0.65); backdrop-filter:blur(3px);"></div>
    <div style="position:relative; display:flex; align-items:center; justify-content:center; min-height:100%; padding:1rem; pointer-events:none;">
        <div id="modalPanel"
             style="pointer-events:all; background:white; border-radius:1rem; box-shadow:0 25px 60px rgba(0,0,0,0.3); width:100%; max-width:72rem; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; transform:scale(0.93); opacity:0; transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease;">

            <div style="background:linear-gradient(135deg,#7c3aed,#4f46e5); padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:0.75rem; padding:0.625rem;">
                        <svg style="width:1.5rem; height:1.5rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <h2 style="color:white; font-size:1.2rem; font-weight:700; margin:0;">Pilih Buku dari Katalog</h2>
                        <p style="color:rgba(221,214,254,0.9); font-size:0.75rem; margin:2px 0 0;">Klik buku untuk memilih • klik lagi untuk batal</p>
                    </div>
                </div>
                <button id="btnCloseModal" type="button"
                    style="background:rgba(255,255,255,0.15); border:none; border-radius:0.625rem; padding:0.5rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg style="width:1.5rem; height:1.5rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div style="padding:1rem 1.5rem; border-bottom:1px solid #f0f0f0; background:#fafafa; flex-shrink:0;">
                <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                    <div style="position:relative; flex:1; min-width:200px;">
                        <svg style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); width:1rem; height:1rem; stroke:#9ca3af; fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="bookSearchInput" placeholder="Cari judul, penulis, barcode, kategori..."
                            style="width:100%; padding:0.625rem 1rem 0.625rem 2.5rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s; box-sizing:border-box;"
                            onfocus="this.style.borderColor='#a78bfa'" onblur="this.style.borderColor='#e5e7eb'">
                    </div>
                    <select id="modalCategoryFilter"
                        style="padding:0.625rem 1rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; outline:none; background:white; color:#374151; min-width:150px; cursor:pointer;"
                        onfocus="this.style.borderColor='#a78bfa'" onblur="this.style.borderColor='#e5e7eb'">
                        <option value="">Semua Kategori</option>
                    </select>
                    <select id="modalStockFilter"
                        style="padding:0.625rem 1rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; outline:none; background:white; color:#374151; min-width:130px; cursor:pointer;"
                        onfocus="this.style.borderColor='#a78bfa'" onblur="this.style.borderColor='#e5e7eb'">
                        <option value="available" selected>Tersedia</option>
                        <option value="">Semua Stok</option>
                        <option value="empty">Stok Habis</option>
                    </select>
                </div>
                <p id="modalResultCount" style="font-size:0.75rem; color:#9ca3af; margin:0.5rem 0 0 0.25rem;"></p>
            </div>

            <div style="flex:1; overflow-y:auto; padding:1.5rem;">
                <div id="modalBookGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:1rem;"></div>
                <div id="modalEmptyState" style="display:none; flex-direction:column; align-items:center; justify-content:center; padding:5rem 0; text-align:center;">
                    <div style="background:#f3f4f6; border-radius:50%; padding:1.5rem; margin-bottom:1rem;">
                        <svg style="width:2.5rem; height:2.5rem; stroke:#d1d5db; fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p style="font-weight:600; color:#6b7280; margin:0;">Buku tidak ditemukan</p>
                    <p style="font-size:0.875rem; color:#9ca3af; margin:4px 0 0;">Coba ubah kata kunci pencarian</p>
                </div>
            </div>

            <div id="modalFooter" style="display:none; border-top:1px solid #e5e7eb; background:#f9fafb; padding:1rem 1.5rem; flex-shrink:0;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; flex:1; min-width:0;">
                        <span style="font-size:0.875rem; font-weight:600; color:#4b5563; white-space:nowrap;">
                            <span id="modalSelectedCount" style="color:#7c3aed; font-weight:700;">0</span> buku dipilih
                        </span>
                        <div id="modalSelectedTags" style="display:flex; gap:0.5rem; flex-wrap:wrap;"></div>
                    </div>
                    <button id="btnConfirmModal" type="button"
                        style="flex-shrink:0; padding:0.625rem 1.25rem; background:linear-gradient(135deg,#7c3aed,#4f46e5); color:white; font-size:0.875rem; font-weight:600; border:none; border-radius:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.5rem; box-shadow:0 4px 12px rgba(124,58,237,0.3); transition:opacity 0.15s;"
                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <svg style="width:1rem; height:1rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tambahkan ke Daftar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /* ── DATA ── */
    var ALL_BOOKS     = {!! json_encode($books ?? []) !!};
    var BARCODE_LOOKUP_URL = '{{ route('books.barcode-lookup') }}';
    var selectedBooks = [];
    var modalPending  = {};
    var itemIndex     = 0;

    /* ══════════════════════════════════════════
       BARCODE SCANNER LOGIC
    ══════════════════════════════════════════ */
    var barcodeDebounce = null;
    var lastScannedBarcode = '';

    document.addEventListener('DOMContentLoaded', function () {
        var barcodeInput = document.getElementById('barcodeInput');
        var btnScan      = document.getElementById('btnScanBarcode');

        // Enter key pada input barcode
        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processBarcode(barcodeInput.value.trim());
            }
        });

        // Debounce untuk hardware scanner (scanner biasanya input cepat)
        barcodeInput.addEventListener('input', function () {
            clearTimeout(barcodeDebounce);
            var val = barcodeInput.value.trim();
            if (val.length >= 6) {
                // Hardware scanner biasanya selesai < 100ms
                barcodeDebounce = setTimeout(function () {
                    // Cek apakah tidak ada aktivitas selama 150ms (ciri scanner)
                    processBarcode(val);
                }, 150);
            }
        });

        // Tombol Tambah
        btnScan.addEventListener('click', function () {
            processBarcode(barcodeInput.value.trim());
        });

        /* Modal */
        document.getElementById('btnOpenModal').addEventListener('click', openModal);
        document.getElementById('btnCloseModal').addEventListener('click', closeModal);
        document.getElementById('modalBackdrop').addEventListener('click', closeModal);
        document.getElementById('btnConfirmModal').addEventListener('click', confirmSelection);
        document.getElementById('bookSearchInput').addEventListener('input', filterBooks);
        document.getElementById('modalCategoryFilter').addEventListener('change', filterBooks);
        document.getElementById('modalStockFilter').addEventListener('change', filterBooks);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

        /* Tanggal */
        var borrow = document.querySelector('[name="borrowed_at"]');
        var due    = document.querySelector('[name="due_at"]');
        if (borrow) borrow.addEventListener('change', updateSummary);
        if (due)    due.addEventListener('change', updateSummary);
        updateSummary();

        /* Auto-select dari URL ?book_id= */
        @if(request('book_id'))
            @php $preBook = \App\Models\Book::with('category')->find(request('book_id')); @endphp
            @if($preBook)
            var preId = '{{ $preBook->id }}';
            var pre   = ALL_BOOKS.find(function(b){ return String(b.id) === preId; });
            if (pre) addToList(pre);
            @endif
        @endif

        // Focus barcode input saat halaman load
        barcodeInput.focus();
    });

    /* ── PROCESS BARCODE ── */
    function processBarcode(barcode) {
        if (!barcode) return;
        if (barcode === lastScannedBarcode) {
            // Buku sudah di-scan, reset & beri feedback
            showBarcodeFeedback('info', '⚠️ Buku dengan barcode ini sudah ada di daftar.');
            return;
        }

        showBarcodeFeedback('loading', '⏳ Mencari buku...');
        hideBarcodePreview();

        // Cari di ALL_BOOKS dulu (client-side, lebih cepat)
        var found = ALL_BOOKS.find(function(b){
            return b.barcode && b.barcode.toLowerCase() === barcode.toLowerCase();
        });

        if (found) {
            handleFoundBook(found);
            return;
        }

        // Jika tidak ketemu di client, fetch ke server
        fetch(BARCODE_LOOKUP_URL + '?barcode=' + encodeURIComponent(barcode), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json().then(function(data){ return { ok: res.ok, data: data }; }); })
        .then(function(result) {
            if (result.ok) {
                handleFoundBook(result.data);
            } else {
                showBarcodeFeedback('error', '❌ ' + (result.data.error || 'Buku tidak ditemukan'));
                hideBarcodePreview();
            }
        })
        .catch(function() {
            showBarcodeFeedback('error', '❌ Gagal menghubungi server. Coba lagi.');
        });
    }

    function handleFoundBook(book) {
        // Cek sudah ada di list
        var alreadyIn = selectedBooks.find(function(b){ return String(b.id) === String(book.id); });
        if (alreadyIn) {
            showBarcodeFeedback('warning', '⚠️ "' + book.name + '" sudah ada di daftar peminjaman.');
            showBarcodePreview(book, false);
            return;
        }

        if (book.stock <= 0) {
            showBarcodeFeedback('error', '❌ Stok "' + book.name + '" habis, tidak dapat dipinjam.');
            showBarcodePreview(book, false);
            return;
        }

        showBarcodeFeedback('success', '✅ Buku ditemukan dan ditambahkan!');
        showBarcodePreview(book, true);
        addToList(book);

        lastScannedBarcode = document.getElementById('barcodeInput').value.trim();
        document.getElementById('barcodeInput').value = '';
        lastScannedBarcode = '';

        // Focus kembali ke barcode input untuk scan berikutnya
        setTimeout(function() {
            document.getElementById('barcodeInput').focus();
        }, 300);
    }

    function showBarcodeFeedback(type, msg) {
        var el = document.getElementById('barcodeFeedback');
        var colors = {
            success: 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
            error:   'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;',
            warning: 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;',
            info:    'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;',
            loading: 'background:#f5f3ff; color:#7c3aed; border:1px solid #ddd6fe;',
        };
        el.style.cssText = (colors[type] || colors.info) + ' display:flex; align-items:center; gap:0.5rem; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; margin-top:0.75rem;';
        el.textContent = msg;
        el.classList.remove('hidden');
        if (type === 'success') {
            setTimeout(function(){ el.classList.add('hidden'); }, 3000);
        }
    }

    function showBarcodePreview(book, isSuccess) {
        var preview = document.getElementById('barcodePreview');
        var nameEl  = document.getElementById('barcodePreviewName');
        var writerEl= document.getElementById('barcodePreviewWriter');
        var stockEl = document.getElementById('barcodePreviewStock');
        var badge   = document.getElementById('barcodePreviewBadge');
        var cover   = document.getElementById('barcodePreviewCover');

        nameEl.textContent   = book.name || '-';
        writerEl.textContent = book.writer || '-';

        if (book.stock > 0) {
            stockEl.style.color = '#16a34a';
            stockEl.textContent = 'Stok: ' + book.stock;
            badge.style.cssText = 'background:#dcfce7; color:#16a34a; padding:2px 8px; border-radius:9999px; font-size:11px; font-weight:700;';
            badge.textContent   = 'Tersedia';
        } else {
            stockEl.style.color = '#dc2626';
            stockEl.textContent = 'Stok habis';
            badge.style.cssText = 'background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:9999px; font-size:11px; font-weight:700;';
            badge.textContent   = 'Habis';
        }

        if (book.image) {
            cover.innerHTML = '<img src="/storage/' + book.image + '" alt="' + x(book.name) + '" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            cover.innerHTML = '<svg style="width:1.5rem;height:1.5rem;stroke:#a5b4fc;fill:none;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>';
        }

        preview.style.borderColor = isSuccess ? '#6ee7b7' : '#fca5a5';
        preview.style.background  = isSuccess ? '#f0fdf4' : '#fff1f2';
        preview.classList.remove('hidden');
    }

    function hideBarcodePreview() {
        document.getElementById('barcodePreview').classList.add('hidden');
    }

    /* ══════════════════════════════════════════
       OPEN / CLOSE MODAL
    ══════════════════════════════════════════ */
    function openModal() {
        Object.keys(modalPending).forEach(function(k){ delete modalPending[k]; });
        var modal = document.getElementById('bookModal');
        var panel = document.getElementById('modalPanel');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        fillCategoryFilter();
        document.getElementById('modalStockFilter').value = 'available';
        document.getElementById('bookSearchInput').value  = '';
        filterBooks();
        setTimeout(function () { panel.style.transform = 'scale(1)'; panel.style.opacity = '1'; }, 10);
        setTimeout(function () { document.getElementById('bookSearchInput').focus(); }, 300);
    }

    function closeModal() {
        var panel = document.getElementById('modalPanel');
        var modal = document.getElementById('bookModal');
        panel.style.transform = 'scale(0.93)';
        panel.style.opacity   = '0';
        setTimeout(function () {
            modal.style.display          = 'none';
            document.body.style.overflow = '';
            Object.keys(modalPending).forEach(function(k){ delete modalPending[k]; });
            updateFooter();
        }, 230);
    }

    /* ══════════════════════════════════════════
       CATEGORY FILTER
    ══════════════════════════════════════════ */
    function fillCategoryFilter() {
        var sel  = document.getElementById('modalCategoryFilter');
        while (sel.options.length > 1) sel.remove(1);
        var seen = {};
        ALL_BOOKS.forEach(function (b) {
            if (b.category && !seen[b.category.id]) {
                seen[b.category.id] = true;
                var o = document.createElement('option');
                o.value = b.category.id; o.textContent = b.category.name; sel.appendChild(o);
            }
        });
    }

    /* ══════════════════════════════════════════
       FILTER
    ══════════════════════════════════════════ */
    function filterBooks() {
        var q     = document.getElementById('bookSearchInput').value.toLowerCase().trim();
        var catId = document.getElementById('modalCategoryFilter').value;
        var stock = document.getElementById('modalStockFilter').value;
        var usedIds = selectedBooks.map(function (b) { return String(b.id); });
        var list = ALL_BOOKS.filter(function (b) {
            if (usedIds.indexOf(String(b.id)) !== -1) return false;
            var mSearch =
                (b.name    && b.name.toLowerCase().indexOf(q)    !== -1) ||
                (b.writer  && b.writer.toLowerCase().indexOf(q)  !== -1) ||
                (b.barcode && b.barcode.toLowerCase().indexOf(q) !== -1) ||
                (b.category && b.category.name.toLowerCase().indexOf(q) !== -1);
            var mCat   = !catId || (b.category && String(b.category.id) === catId);
            var mStock = (stock === '')         ? true
                       : (stock === 'available') ? b.stock > 0
                       :                           b.stock <= 0;
            return mSearch && mCat && mStock;
        });
        renderGrid(list);
    }

    /* ══════════════════════════════════════════
       RENDER GRID
    ══════════════════════════════════════════ */
    function renderGrid(books) {
        var grid  = document.getElementById('modalBookGrid');
        var empty = document.getElementById('modalEmptyState');
        var cnt   = document.getElementById('modalResultCount');
        var avail = ALL_BOOKS.filter(function(b){
            return selectedBooks.map(function(x){ return String(x.id); }).indexOf(String(b.id)) === -1;
        }).length;
        cnt.textContent = 'Menampilkan ' + books.length + ' dari ' + avail + ' buku';
        if (books.length === 0) { grid.innerHTML = ''; empty.style.display = 'flex'; return; }
        empty.style.display = 'none';
        grid.innerHTML = books.map(function (b) {
            var pend  = !!modalPending[String(b.id)];
            var avail = b.stock > 0;
            var img   = b.image ? '/storage/' + b.image : null;
            var border = pend ? '2px solid #7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.2); background:#faf5ff;' : '2px solid #e5e7eb; background:white;';
            var checkBg = pend ? 'background:#7c3aed; border:2px solid #7c3aed;' : 'background:rgba(255,255,255,0.85); border:2px solid #d1d5db;';
            var cover = img
                ? '<img src="' + img + '" alt="' + x(b.name) + '" style="width:100%;height:100%;object-fit:cover;display:block;">'
                : '<div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:0.5rem;background:linear-gradient(135deg,#f5f3ff,#ede9fe);"><svg style="width:2rem;height:2rem;stroke:#c4b5fd;fill:none;margin-bottom:4px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg><span style="font-size:9px;color:#a78bfa;text-align:center;line-height:1.3;">' + x(b.name) + '</span></div>';
            var habis = !avail ? '<div style="position:absolute;top:6px;left:6px;background:#ef4444;color:white;font-size:9px;font-weight:700;padding:2px 5px;border-radius:3px;z-index:2;">HABIS</div>' : '';
            var catBadge = b.category ? '<span style="font-size:10px;background:#dbeafe;color:#2563eb;font-weight:600;padding:1px 5px;border-radius:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:60%;">' + x(b.category.name) + '</span>' : '<span></span>';
            var stockColor = avail ? '#16a34a' : '#ef4444';
            var stockLbl   = avail ? b.stock + ' stok' : 'Habis';
            var cursor = avail ? 'cursor:pointer;' : 'cursor:not-allowed;opacity:0.5;';
            return '<div data-id="' + b.id + '" onclick="' + (avail ? 'window._toggleBook(\'' + b.id + '\')' : '') + '" style="border-radius:0.75rem; overflow:hidden; border:' + border + ' transition:all 0.18s; ' + cursor + '">'
                + '<div style="position:relative;">' + habis
                + '<div style="position:absolute;top:6px;right:6px;z-index:2;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all 0.15s;' + checkBg + '"><svg style="width:11px;height:11px;stroke:white;fill:none;opacity:' + (pend?1:0) + ';" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>'
                + '<div style="aspect-ratio:3/4;overflow:hidden;">' + cover + '</div></div>'
                + '<div style="padding:0.625rem;">'
                + '<p style="font-size:11.5px;font-weight:700;color:#111827;line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin:0 0 3px;">' + x(b.name) + '</p>'
                + (b.writer ? '<p style="font-size:10.5px;color:#6b7280;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;margin:0 0 5px;">' + x(b.writer) + '</p>' : '')
                + '<div style="display:flex;align-items:center;justify-content:space-between;gap:3px;">' + catBadge
                + '<span style="font-size:10.5px;font-weight:700;color:' + stockColor + ';flex-shrink:0;">' + stockLbl + '</span></div></div></div>';
        }).join('');
    }

    /* ══════════════════════════════════════════
       TOGGLE PENDING
    ══════════════════════════════════════════ */
    window._toggleBook = function (id) {
        var book = ALL_BOOKS.find(function (b) { return String(b.id) === String(id); });
        if (!book || book.stock <= 0) return;
        if (modalPending[id]) delete modalPending[id];
        else                   modalPending[id] = book;
        filterBooks(); updateFooter();
    };

    /* ══════════════════════════════════════════
       FOOTER MODAL
    ══════════════════════════════════════════ */
    function updateFooter() {
        var footer  = document.getElementById('modalFooter');
        var tags    = document.getElementById('modalSelectedTags');
        var cntEl   = document.getElementById('modalSelectedCount');
        var pending = Object.values(modalPending);
        cntEl.textContent = pending.length;
        if (pending.length === 0) { footer.style.display = 'none'; return; }
        footer.style.display = 'flex';
        tags.innerHTML = pending.map(function (b) {
            return '<span style="display:inline-flex;align-items:center;gap:5px;background:#ede9fe;color:#6d28d9;font-size:11.5px;font-weight:600;padding:3px 9px;border-radius:6px;">'
                 + '<span style="max-width:90px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">' + x(b.name) + '</span>'
                 + '<button type="button" onclick="window._toggleBook(\'' + b.id + '\')" style="background:none;border:none;cursor:pointer;color:#a78bfa;font-size:15px;line-height:1;padding:0;margin:0;">&times;</button>'
                 + '</span>';
        }).join('');
    }

    /* ══════════════════════════════════════════
       KONFIRMASI MODAL
    ══════════════════════════════════════════ */
    function confirmSelection() {
        var pending = Object.values(modalPending);
        if (pending.length === 0) return;
        pending.forEach(function (b) { addToList(b); });
        Object.keys(modalPending).forEach(function(k){ delete modalPending[k]; });
        closeModal();
    }

    /* ══════════════════════════════════════════
       TAMBAH KE DAFTAR PINJAM
    ══════════════════════════════════════════ */
    function addToList(book) {
        if (selectedBooks.find(function (b) { return String(b.id) === String(book.id); })) return;
        selectedBooks.push({ id: book.id, name: book.name, writer: book.writer,
                             stock: book.stock, image: book.image, category: book.category,
                             quantity: 1, index: itemIndex++ });
        renderList(); updateSummary();
    }

    /* ══════════════════════════════════════════
       RENDER DAFTAR PINJAM
    ══════════════════════════════════════════ */
    function renderList() {
        var wrap  = document.getElementById('selectedBooksList');
        var empty = document.getElementById('emptySelectedState');
        Array.from(wrap.children).forEach(function(child) {
            if (child.id !== 'emptySelectedState') child.remove();
        });
        if (selectedBooks.length === 0) { if (empty) empty.style.display = 'block'; return; }
        if (empty) empty.style.display = 'none';
        selectedBooks.forEach(function (book, i) {
            var el = document.createElement('div');
            el.style.cssText = 'display:flex;align-items:center;gap:1rem;padding:1rem;border-radius:0.75rem;border:2px solid #ddd6fe;background:linear-gradient(135deg,#faf5ff,#eef2ff);';
            var cover = book.image
                ? '<img src="/storage/' + book.image + '" alt="' + x(book.name) + '" style="width:100%;height:100%;object-fit:cover;">'
                : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f5f3ff,#ede9fe);"><svg style="width:1.5rem;height:1.5rem;stroke:#c4b5fd;fill:none;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>';
            var catBadge = book.category
                ? '<span style="font-size:11px;background:#dbeafe;color:#2563eb;font-weight:500;padding:1px 8px;border-radius:9999px;display:inline-block;margin-top:3px;">' + x(book.category.name) + '</span>'
                : '';

            // Barcode badge
            var barcodeBadge = book.barcode
                ? '<span style="font-size:10px;background:#f3f4f6;color:#6b7280;padding:1px 6px;border-radius:4px;font-family:monospace;display:inline-block;margin-top:2px;">' + x(book.barcode) + '</span>'
                : '';

            el.innerHTML =
                '<div style="width:3.25rem;height:4.25rem;border-radius:6px;overflow:hidden;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,0.1);">' + cover + '</div>'
              + '<div style="flex:1;min-width:0;">'
              + '<p style="font-weight:700;color:#111827;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;margin:0 0 2px;">' + x(book.name) + '</p>'
              + '<p style="font-size:13px;color:#6b7280;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;margin:0;">' + x(book.writer || '-') + '</p>'
              + catBadge + barcodeBadge
              + '<p style="font-size:11.5px;color:#9ca3af;margin:4px 0 0;">Stok tersisa: <strong style="color:#16a34a;">' + book.stock + '</strong></p>'
              + '</div>'
              + '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">'
              + btn('-', 'window._dec(' + i + ')')
              + '<span style="width:1.75rem;text-align:center;font-weight:700;color:#111827;">' + book.quantity + '</span>'
              + btn('+', 'window._inc(' + i + ')')
              + '</div>'
              + '<button type="button" onclick="window._remove(' + i + ')" style="padding:0.4rem;background:#fee2e2;color:#ef4444;border:none;border-radius:6px;cursor:pointer;flex-shrink:0;" onmouseover="this.style.background=\'#fecaca\'" onmouseout="this.style.background=\'#fee2e2\'">'
              + '<svg style="width:1.2rem;height:1.2rem;stroke:currentColor;fill:none;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
              + '</button>'
              + '<input type="hidden" name="items[' + book.index + '][book_id]" value="' + book.id + '">'
              + '<input type="hidden" name="items[' + book.index + '][quantity]" value="' + book.quantity + '">';
            wrap.appendChild(el);
        });
    }

    function btn(label, onclick) {
        return '<button type="button" onclick="' + onclick + '"'
            + ' style="width:2rem;height:2rem;border:2px solid #e5e7eb;background:white;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#374151;"'
            + ' onmouseover="this.style.borderColor=\'#a78bfa\'" onmouseout="this.style.borderColor=\'#e5e7eb\'">'
            + label + '</button>';
    }

    /* ══════════════════════════════════════════
       CONTROLS
    ══════════════════════════════════════════ */
    window._inc = function (i) {
        if (selectedBooks[i].quantity < selectedBooks[i].stock) {
            selectedBooks[i].quantity++; renderList(); updateSummary();
        }
    };
    window._dec = function (i) {
        if (selectedBooks[i].quantity > 1) {
            selectedBooks[i].quantity--; renderList(); updateSummary();
        }
    };
    window._remove = function (i) {
        selectedBooks.splice(i, 1); renderList(); updateSummary();
    };

    /* ══════════════════════════════════════════
       SUMMARY
    ══════════════════════════════════════════ */
    function updateSummary() {
        var n = selectedBooks.length;
        var t = selectedBooks.reduce(function (s, b) { return s + b.quantity; }, 0);
        document.getElementById('bookCount').textContent         = n;
        document.getElementById('summaryBookCount').textContent  = n;
        document.getElementById('summaryTotalItems').textContent = t;
        var bEl = document.querySelector('[name="borrowed_at"]');
        var dEl = document.querySelector('[name="due_at"]');
        if (bEl && dEl && bEl.value && dEl.value) {
            var d = Math.ceil((new Date(dEl.value) - new Date(bEl.value)) / 86400000);
            document.getElementById('summaryDuration').textContent = d > 0 ? d + ' hari' : '-';
        }
        var btn = document.getElementById('submitBtn');
        if (n > 0) { btn.disabled = false; btn.classList.remove('opacity-50','cursor-not-allowed'); }
        else        { btn.disabled = true;  btn.classList.add('opacity-50','cursor-not-allowed'); }
    }

    /* ══════════════════════════════════════════
       ESCAPE HTML
    ══════════════════════════════════════════ */
    function x(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
</script>

@endsection