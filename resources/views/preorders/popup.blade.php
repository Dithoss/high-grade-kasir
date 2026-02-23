{{--
    ═══════════════════════════════════════════════════════════════════
    PREORDER POPUP + QUEUE INFO — include in books.show
    Di-include setelah tombol "Stok Habis" dengan:
    @include('preorders._preorder-popup', ['book' => $book])
    ═══════════════════════════════════════════════════════════════════
--}}

@auth
@php
    $existingPreorder = auth()->user()
        ? \App\Models\Preorder::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->whereIn('status', ['waiting', 'ready'])
            ->first()
        : null;

    $totalQueue = \App\Models\Preorder::where('book_id', $book->id)
        ->whereIn('status', ['waiting', 'ready'])
        ->count();
@endphp

@role('user')
@if($book->stock <= 0)

    {{-- ── Queue info strip ── --}}
    @if($totalQueue > 0)
        <div class="mt-3 px-4 py-3 bg-amber-50 border-2 border-amber-200 rounded-xl flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-900">{{ $totalQueue }} orang</p>
                <p class="text-xs text-amber-700">sedang dalam antrian preorder</p>
            </div>
        </div>
    @endif

    {{-- ── Sudah punya preorder ── --}}
    @if($existingPreorder)
        <div class="mt-3 px-4 py-4 bg-violet-50 border-2 border-violet-200 rounded-xl">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-bold text-violet-800 text-sm">Anda sudah dalam antrian!</p>
            </div>
            <p class="text-xs text-violet-700 mb-3">
                Posisi: <strong>#{{ $existingPreorder->queue_position }}</strong> •
                Status: <strong>{{ $existingPreorder->status_label }}</strong>
            </p>

            @if($existingPreorder->isReady())
                <a href="{{ route('preorders.confirm', $existingPreorder->id) }}"
                   onclick="return confirm('Apakah Anda jadi meminjam buku ini? Konfirmasi akan membawa Anda ke halaman peminjaman.')"
                   class="block w-full px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-sm font-bold rounded-lg text-center shadow transition-all">
                    ✅ Pinjam Sekarang
                </a>
            @else
                <a href="{{ route('preorders.index') }}"
                   class="block w-full px-4 py-2.5 bg-violet-100 hover:bg-violet-200 text-violet-800 text-sm font-semibold rounded-lg text-center transition-all">
                    Lihat Preorder Saya
                </a>
            @endif
        </div>

    {{-- ── Tombol Preorder baru ── --}}
    @else
        <button type="button"
            onclick="openPreorderModal()"
            class="mt-3 w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-700 hover:from-violet-700 hover:to-purple-800 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Preorder Buku Ini</span>
        </button>
    @endif
@endif
@endrole
@endauth


{{-- ═══════════════════════════════════════════════════════
     PREORDER MODAL
═══════════════════════════════════════════════════════ --}}
<div id="preorderModal" style="display:none; position:fixed; inset:0; z-index:9999; overflow:hidden;">
    <div id="preorderBackdrop"
         style="position:absolute; inset:0; background:rgba(17,24,39,0.65); backdrop-filter:blur(3px);"></div>

    <div style="position:relative; display:flex; align-items:center; justify-content:center; min-height:100%; padding:1rem; pointer-events:none;">
        <div id="preorderPanel"
             style="pointer-events:all; background:white; border-radius:1rem; box-shadow:0 25px 60px rgba(0,0,0,0.3); width:100%; max-width:30rem; transform:scale(0.93); opacity:0; transition:transform 0.28s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease;">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9); padding:1.25rem 1.5rem; border-radius:1rem 1rem 0 0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:0.75rem; padding:0.625rem;">
                        <svg style="width:1.4rem; height:1.4rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p style="color:white; font-weight:700; font-size:1.1rem; margin:0;">Preorder Buku</p>
                        <p style="color:rgba(221,214,254,0.85); font-size:0.72rem; margin:2px 0 0;">Daftarkan antrian peminjaman Anda</p>
                    </div>
                </div>
                <button onclick="closePreorderModal()" type="button"
                    style="background:rgba(255,255,255,0.15); border:none; border-radius:0.5rem; padding:0.4rem 0.5rem; cursor:pointer; display:flex;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg style="width:1.25rem; height:1.25rem; stroke:white; fill:none;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding:1.5rem;">

                {{-- Buku info --}}
                <div style="display:flex; gap:0.875rem; padding:0.875rem; background:#f5f3ff; border-radius:0.75rem; margin-bottom:1.25rem; border:1.5px solid #ede9fe;">
                    @if($book->image)
                        <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}"
                             style="width:3rem; height:4rem; object-fit:cover; border-radius:0.5rem; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.12);">
                    @else
                        <div style="width:3rem; height:4rem; background:linear-gradient(135deg,#ede9fe,#ddd6fe); border-radius:0.5rem; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            <svg style="width:1.5rem; height:1.5rem; stroke:#a78bfa; fill:none;" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                    <div style="flex:1; min-width:0;">
                        <p style="font-weight:700; color:#111827; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; margin:0 0 2px;">{{ $book->name }}</p>
                        <p style="font-size:12.5px; color:#6b7280; margin:0 0 6px;">{{ $book->writer }}</p>
                        @if($totalQueue > 0)
                            <div style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; color:#92400e; font-size:11px; font-weight:600; padding:2px 8px; border-radius:999px;">
                                <svg style="width:10px; height:10px; stroke:currentColor; fill:none;" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $totalQueue }} antrian • Anda akan ke-{{ $totalQueue + 1 }}
                            </div>
                        @else
                            <div style="display:inline-flex; align-items:center; gap:4px; background:#d1fae5; color:#065f46; font-size:11px; font-weight:600; padding:2px 8px; border-radius:999px;">
                                ✓ Jadilah yang pertama dalam antrian!
                            </div>
                        @endif
                    </div>
                </div>

                <form id="preorderForm"
                      action="{{ route('preorders.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">

                    {{-- Tanggal --}}
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                            Rencana Tanggal Pinjam <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="date" name="expected_borrow_date"
                            id="preorderDate"
                            min="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                            required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.9rem; outline:none; box-sizing:border-box; font-family:inherit;"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'">
                        <p style="font-size:11px; color:#9ca3af; margin-top:4px;">Kapan Anda berencana meminjam buku ini?</p>
                    </div>

                    {{-- Catatan --}}
                    <div style="margin-bottom:1.5rem;">
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                            Catatan (opsional)
                        </label>
                        <textarea name="notes" rows="2" maxlength="500"
                            placeholder="Tambahkan catatan, misalnya: untuk keperluan studi..."
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; outline:none; resize:vertical; box-sizing:border-box; font-family:inherit;"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                    </div>

                    {{-- Info box --}}
                    <div style="background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:0.75rem; padding:0.875rem 1rem; margin-bottom:1.25rem;">
                        <p style="font-size:12px; color:#1e40af; font-weight:600; margin:0 0 4px;">ℹ️ Informasi Preorder</p>
                        <ul style="font-size:11.5px; color:#3b82f6; margin:0; padding:0; list-style:none; line-height:1.8;">
                            <li>• Anda akan diberi tahu saat buku tersedia</li>
                            <li>• Konfirmasi dalam 2 hari setelah notifikasi</li>
                            <li>• Preorder dapat dibatalkan kapan saja</li>
                        </ul>
                    </div>

                    <div id="preorderError" style="display:none; background:#fef2f2; color:#b91c1c; padding:0.75rem 1rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;"></div>

                    <div style="display:flex; gap:0.75rem;">
                        <button type="button" onclick="closePreorderModal()"
                            style="flex:1; padding:0.75rem; background:#f3f4f6; color:#374151; border:none; border-radius:0.75rem; font-weight:600; cursor:pointer; font-size:0.9rem;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            Batal
                        </button>
                        <button type="submit" id="preorderSubmitBtn"
                            style="flex:2; padding:0.75rem; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:white; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer; font-size:0.9rem; box-shadow:0 4px 12px rgba(124,58,237,0.3);"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            🕐 Daftar Preorder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openPreorderModal() {
    var modal = document.getElementById('preorderModal');
    var panel = document.getElementById('preorderPanel');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
        panel.style.transform = 'scale(1)';
        panel.style.opacity   = '1';
    }, 10);
}

function closePreorderModal() {
    var panel = document.getElementById('preorderPanel');
    var modal = document.getElementById('preorderModal');
    panel.style.transform = 'scale(0.93)';
    panel.style.opacity   = '0';
    setTimeout(function() {
        modal.style.display          = 'none';
        document.body.style.overflow = '';
    }, 230);
}

// Backdrop close
document.getElementById('preorderBackdrop').addEventListener('click', closePreorderModal);
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePreorderModal();
});
</script>