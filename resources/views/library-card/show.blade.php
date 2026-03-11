@extends('layouts.app')

@section('title', 'Detail Kartu - ' . $card->user->name)
@section('header', 'Kartu Perpustakaan')
@section('subtitle', 'Kartu identitas anggota')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Mono:wght@400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --card-grad: linear-gradient(135deg, #667eea 0%, #f093fb 50%, #f5576c 100%);
        --card-grad-2: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --card-grad-3: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --accent: #7c3aed;
        --pop: #f59e0b;
    }

    body { font-family: 'Space Grotesk', sans-serif; }

    /* ── Card Flip ── */
    .card-scene { perspective: 1200px; }
    .card-flip {
        width: 480px; height: 290px;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.7s cubic-bezier(.4,0,.2,1);
    }
    .card-flip.flipped { transform: rotateY(180deg); }
    .card-face {
        position: absolute; inset: 0;
        backface-visibility: hidden;
        border-radius: 24px;
        overflow: hidden;
    }
    .card-back { transform: rotateY(180deg); }

    /* ── Card Front Design ── */
    .card-front-bg {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 70%, #533483 100%);
        width: 100%; height: 100%;
        position: relative;
    }
    .card-front-bg::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 60% 50% at 80% 20%, rgba(124,58,237,0.45) 0%, transparent 60%),
            radial-gradient(ellipse 50% 60% at 10% 80%, rgba(6,182,212,0.35) 0%, transparent 60%);
    }
    .card-front-bg::after {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        border-radius: 50%;
        border: 40px solid rgba(255,255,255,0.04);
    }
    .noise {
        position: absolute; inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
        pointer-events: none;
    }

    /* ── Card Back Design ── */
    .card-back-bg {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        width: 100%; height: 100%;
        position: relative;
        display: flex; flex-direction: column;
    }
    .mag-stripe {
        width: 100%; height: 48px;
        background: linear-gradient(180deg, #111 0%, #222 50%, #111 100%);
        margin-top: 32px;
    }

    /* ── Status Pills ── */
    .pill-active  { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
    .pill-expired { background: rgba(239,68,68,.15);  color: #f87171; border: 1px solid rgba(248,113,113,.3); }
    .pill-lost    { background: rgba(245,158,11,.15); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }

    /* ── Download Card (printable) ── */
    #downloadCard {
        width: 480px; height: 290px;
        border-radius: 24px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 70%, #533483 100%);
        font-family: 'Space Grotesk', sans-serif;
    }

    /* ── Action Button ── */
    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 14px; font-weight: 600; font-size: 14px;
        transition: all .2s; cursor: pointer; border: none;
    }
    .btn-violet { background: #7c3aed; color: white; }
    .btn-violet:hover { background: #6d28d9; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(124,58,237,.4); }
    .btn-emerald { background: #059669; color: white; }
    .btn-emerald:hover { background: #047857; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(5,150,105,.4); }
    .btn-rose { background: #e11d48; color: white; }
    .btn-rose:hover { background: #be123c; transform: translateY(-1px); }
    .btn-amber { background: #d97706; color: white; }
    .btn-amber:hover { background: #b45309; transform: translateY(-1px); }
    .btn-outline {
        background: transparent; color: #6b7280;
        border: 1.5px solid #e5e7eb;
    }
    .btn-outline:hover { border-color: #7c3aed; color: #7c3aed; background: rgba(124,58,237,.05); }
    .btn-cyan { background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: white; }
    .btn-cyan:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(6,182,212,.4); }

    /* ── Info Cards ── */
    .info-card {
        background: white; border-radius: 20px;
        border: 1.5px solid #f3f4f6;
        padding: 20px;
        transition: all .2s;
    }
    .info-card:hover { border-color: #e0e7ff; box-shadow: 0 4px 20px rgba(124,58,237,.08); transform: translateY(-2px); }

    /* ── Status Form Button ── */
    .status-btn {
        width: 100%; display: flex; align-items: center; gap: 10px;
        padding: 11px 16px; border-radius: 14px; font-size: 14px; font-weight: 600;
        border: 1.5px solid; cursor: pointer; transition: all .2s; background: transparent;
    }
    .status-active  { color: #059669; border-color: #a7f3d0; }
    .status-active:hover  { background: #ecfdf5; }
    .status-expired { color: #dc2626; border-color: #fecaca; }
    .status-expired:hover { background: #fef2f2; }
    .status-lost    { color: #d97706; border-color: #fde68a; }
    .status-lost:hover    { background: #fffbeb; }

    /* ── Holographic shimmer on card ── */
    @keyframes shimmer {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    .holo {
        position: absolute; inset: 0; border-radius: 24px; opacity: 0; pointer-events: none;
        background: linear-gradient(105deg, transparent 30%, rgba(255,255,255,.12) 50%, transparent 70%);
        background-size: 200% 100%;
        transition: opacity .3s;
    }
    .card-scene:hover .holo { opacity: 1; animation: shimmer 1.5s linear infinite; }

    /* ── Chip ── */
    .chip {
        width: 42px; height: 32px; border-radius: 6px;
        background: linear-gradient(135deg, #f0c040, #c8912a);
        position: relative; overflow: hidden;
    }
    .chip::before {
        content: '';
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%,-50%);
        width: 80%; height: 60%;
        border: 1.5px solid rgba(0,0,0,.25);
        border-radius: 4px;
    }
    .chip::after {
        content: '';
        position: absolute; top: 50%; left: 0; right: 0;
        height: 1px; background: rgba(0,0,0,.2);
        transform: translateY(-50%);
    }

    /* ── Barcode stripes ── */
    .barcode {
        display: flex; gap: 2px; align-items: flex-end;
        height: 32px;
    }
    .barcode span {
        display: block; width: 2px;
        background: rgba(255,255,255,0.6);
    }

    /* ── Flip hint ── */
    @keyframes bounce-x {
        0%,100% { transform: translateX(0); }
        50% { transform: translateX(4px); }
    }
    .flip-hint { animation: bounce-x 1.5s ease-in-out infinite; }

    /* ── Print layout ── */
    @media print {
        body * { visibility: hidden !important; }
        #printArea, #printArea * { visibility: visible !important; }
        #printArea { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background: white; }
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-8 px-4 py-2" style="font-family: 'Space Grotesk', sans-serif;">

    {{-- ── Top Nav ── --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.library-cards.index') }}"
           class="inline-flex items-center gap-2 text-gray-500 hover:text-violet-600 font-medium text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 font-medium uppercase tracking-widest">Kartu Anggota</span>
            <div class="w-1.5 h-1.5 rounded-full bg-violet-400"></div>
            <span class="text-xs font-bold text-gray-700">{{ $card->card_number }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- ══════════ LEFT: Card Visual + Details ══════════ --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- ── 3D Flip Card ── --}}
            <div class="flex flex-col items-center gap-4">
                <div class="card-scene" onclick="document.getElementById('cardFlip').classList.toggle('flipped')">
                    <div class="card-flip" id="cardFlip">

                        {{-- FRONT --}}
                        <div class="card-face card-front shadow-2xl cursor-pointer">
                            <div class="card-front-bg">
                                <div class="noise"></div>
                                <div class="holo"></div>

                                {{-- Decorative ring --}}
                                <div class="absolute -bottom-16 -right-16 w-56 h-56 rounded-full"
                                     style="border: 32px solid rgba(255,255,255,0.03);"></div>
                                <div class="absolute -top-8 -left-8 w-40 h-40 rounded-full"
                                     style="border: 24px solid rgba(255,255,255,0.03);"></div>

                                <div class="relative z-10 p-7 flex flex-col h-full" style="min-height:290px;">

                                    {{-- Header Row --}}
                                    <div class="flex items-center justify-between mb-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                                                 style="background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                                                <i class="fas fa-book-open text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-white font-bold text-xs tracking-widest uppercase" style="font-family:'Syne',sans-serif;">Perpustakaan</p>
                                                <p class="text-xs" style="color: rgba(196,181,253,0.8);">Digital Library</p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold px-3 py-1 rounded-full
                                            @if($card->status === 'active') pill-active
                                            @elseif($card->status === 'expired') pill-expired
                                            @else pill-lost
                                            @endif"
                                            style="font-family:'DM Mono',monospace; letter-spacing:.05em;">
                                            {{ strtoupper($card->status) }}
                                        </span>
                                    </div>

                                    {{-- Chip + Photo row --}}
                                    <div class="flex items-center gap-4 mb-5">
                                        <div class="chip"></div>
                                        <div class="w-px h-8" style="background: rgba(255,255,255,0.15);"></div>
                                        <div class="w-14 h-14 rounded-2xl overflow-hidden ring-2" style="ring-color: rgba(255,255,255,.2);">
                                            @if($card->photo_path)
                                                <img src="{{ asset('storage/' . $card->photo_path) }}" class="w-full h-full object-cover">
                                            @elseif($card->user->image)
                                                <img src="{{ asset('storage/' . $card->user->image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-white font-bold text-2xl"
                                                     style="background: linear-gradient(135deg, rgba(124,58,237,.6), rgba(6,182,212,.6));">
                                                    {{ strtoupper(substr($card->user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white font-bold text-lg truncate" style="font-family:'Syne',sans-serif; letter-spacing:-.01em;">
                                                {{ $card->user->name }}
                                            </p>
                                            <p class="text-xs truncate" style="color: rgba(196,181,253,.8);">
                                                {{ $card->user->email }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Bottom Row --}}
                                    <div class="mt-auto pt-4 flex items-end justify-between"
                                         style="border-top: 1px solid rgba(255,255,255,.12);">
                                        <div>
                                            <p class="text-xs uppercase tracking-widest mb-1.5" style="color:rgba(196,181,253,.6);">Nomor Kartu</p>
                                            <p class="text-white font-bold text-sm tracking-widest" style="font-family:'DM Mono',monospace;">
                                                {{ $card->card_number }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase tracking-widest mb-1.5" style="color:rgba(196,181,253,.6);">Berlaku s/d</p>
                                            <p class="text-white font-bold text-sm" style="font-family:'DM Mono',monospace;">
                                                {{ $card->expired_at->format('m / Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BACK --}}
                        <div class="card-face card-back shadow-2xl cursor-pointer">
                            <div class="card-back-bg">
                                <div class="noise"></div>
                                <div class="mag-stripe"></div>
                                <div class="px-7 py-5 flex flex-col gap-3">
                                    <div class="flex items-center gap-2">
                                        <div class="barcode">
                                            @for($i=0;$i<28;$i++)
                                            <span style="height:{{ rand(60,100) }}%; opacity:{{ 0.4 + ($i%3)*0.2 }};"></span>
                                            @endfor
                                        </div>
                                        <p class="text-xs ml-2" style="font-family:'DM Mono',monospace; color:rgba(196,181,253,.6);">
                                            {{ $card->card_number }}
                                        </p>
                                    </div>
                                    <div class="mt-1" style="background:rgba(255,255,255,.06); border-radius:10px; padding:12px 14px;">
                                        <p class="text-xs mb-0.5" style="color:rgba(196,181,253,.5);">Tanda Tangan</p>
                                        <p class="text-white font-medium text-sm" style="font-family:'Syne',sans-serif;">{{ $card->user->name }}</p>
                                    </div>
                                    <div class="flex justify-between items-end mt-auto">
                                        <div>
                                            <p class="text-xs" style="color:rgba(196,181,253,.5);">Diterbitkan</p>
                                            <p class="text-white text-xs font-medium" style="font-family:'DM Mono',monospace;">{{ $card->created_at->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs" style="color:rgba(196,181,253,.5);">Perpustakaan Digital</p>
                                            <p class="text-white text-xs font-bold">Hubungi admin jika hilang</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Flip hint --}}
                <p class="text-xs text-gray-400 flex items-center gap-1.5">
                    <svg class="w-3 h-3 flip-hint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    Klik kartu untuk balik
                </p>
            </div>

            {{-- ── Detail Grid ── --}}
            <div class="grid grid-cols-2 gap-3">
                @php
                    $details = [
                        ['label' => 'Nama Anggota', 'value' => $card->user->name, 'icon' => 'fa-user', 'color' => 'violet'],
                        ['label' => 'Email', 'value' => $card->user->email, 'icon' => 'fa-envelope', 'color' => 'sky'],
                        ['label' => 'Nomor Kartu', 'value' => $card->card_number, 'icon' => 'fa-id-card', 'color' => 'indigo', 'mono' => true],
                        ['label' => 'Berlaku Hingga', 'value' => $card->expired_at->format('d M Y'), 'icon' => 'fa-calendar-check', 'color' => 'emerald'],
                        ['label' => 'Diterbitkan', 'value' => $card->created_at->format('d M Y'), 'icon' => 'fa-calendar-plus', 'color' => 'amber'],
                        ['label' => 'Terakhir Update', 'value' => $card->updated_at->format('d M Y'), 'icon' => 'fa-clock', 'color' => 'rose'],
                    ];
                    $colorMap = [
                        'violet' => ['bg' => 'bg-violet-50', 'icon' => 'text-violet-600', 'label' => 'text-violet-500'],
                        'sky'    => ['bg' => 'bg-sky-50',    'icon' => 'text-sky-600',    'label' => 'text-sky-500'],
                        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'label' => 'text-indigo-500'],
                        'emerald'=> ['bg' => 'bg-emerald-50','icon' => 'text-emerald-600','label' => 'text-emerald-500'],
                        'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600',  'label' => 'text-amber-500'],
                        'rose'   => ['bg' => 'bg-rose-50',   'icon' => 'text-rose-600',   'label' => 'text-rose-500'],
                    ];
                @endphp
                @foreach($details as $d)
                @php $c = $colorMap[$d['color']]; @endphp
                <div class="info-card">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $c['bg'] }}">
                            <i class="fas {{ $d['icon'] }} text-xs {{ $c['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-semibold {{ $c['label'] }} uppercase tracking-wider">{{ $d['label'] }}</span>
                    </div>
                    <p class="font-semibold text-gray-800 text-sm truncate {{ isset($d['mono']) ? 'font-mono' : '' }}">
                        {{ $d['value'] }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══════════ RIGHT: Actions ══════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Status Badge --}}
            <div class="info-card">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Status Aktif</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center
                        @if($card->status === 'active') bg-emerald-100
                        @elseif($card->status === 'expired') bg-rose-100
                        @else bg-amber-100
                        @endif">
                        <i class="fas
                            @if($card->status === 'active') fa-check text-emerald-600
                            @elseif($card->status === 'expired') fa-times text-rose-600
                            @else fa-exclamation text-amber-600
                            @endif"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-base capitalize">{{ $card->status }}</p>
                        @php $daysLeft = now()->diffInDays($card->expired_at, false); @endphp
                        @if($daysLeft > 0)
                            <p class="text-xs text-gray-400">Tersisa {{ $daysLeft }} hari</p>
                        @else
                            <p class="text-xs text-rose-500">Sudah kedaluwarsa</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Download Card (Admin Only) ── --}}
            @role('admin')
            <div class="info-card" style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-color: #c4b5fd;">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-download text-violet-600 text-xs"></i>
                    </div>
                    <p class="font-semibold text-violet-800 text-sm">Download Kartu</p>
                </div>
                <p class="text-xs text-violet-500 mb-4">Unduh kartu sebagai gambar untuk dicetak atau ditempel.</p>
                <div class="flex flex-col gap-2">
                    <button onclick="downloadCard()"  class="btn-primary btn-violet w-full justify-center">
                        <i class="fas fa-layer-group"></i> Download Depan + Belakang
                    </button>
                    <button onclick="downloadFront()" class="btn-primary btn-outline w-full justify-center" style="font-size:13px;">
                        <i class="fas fa-image"></i> Depan Saja
                    </button>
                    <button onclick="downloadBack()"  class="btn-primary btn-outline w-full justify-center" style="font-size:13px;">
                        <i class="fas fa-image"></i> Belakang Saja
                    </button>
                    <button onclick="window.print()"  class="btn-primary btn-outline w-full justify-center" style="font-size:13px;">
                        <i class="fas fa-print"></i> Print Kartu
                    </button>
                </div>
            </div>
            @endrole

            {{-- ── Change Status ── --}}
            <div class="info-card">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-toggle-on text-gray-600 text-xs"></i>
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">Ubah Status</p>
                </div>
                <div class="space-y-2">
                    @foreach([
                        'active'  => ['fa-check-circle', 'Set Aktif',       'status-active'],
                        'expired' => ['fa-times-circle', 'Set Kedaluwarsa', 'status-expired'],
                        'lost'    => ['fa-exclamation-circle','Set Hilang',  'status-lost'],
                    ] as $status => [$icon, $label, $cls])
                    @if($card->status !== $status)
                    <form method="POST" action="{{ route('admin.library-cards.update-status', $card->id) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button type="submit" class="status-btn {{ $cls }}">
                            <i class="fas {{ $icon }} text-sm"></i> {{ $label }}
                        </button>
                    </form>
                    @endif
                    @endforeach
                </div>
            </div>

            {{-- ── Regenerate ── --}}
            <div class="info-card" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0;">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sync-alt text-emerald-600 text-xs"></i>
                    </div>
                    <p class="font-semibold text-emerald-800 text-sm">Terbitkan Ulang</p>
                </div>
                <p class="text-xs text-emerald-600 mb-4">Buat nomor baru & reset masa berlaku 3 tahun ke depan.</p>
                <form method="POST" action="{{ route('admin.library-cards.regenerate', $card->id) }}"
                      onsubmit="return confirm('Yakin ingin menerbitkan ulang? Nomor kartu lama akan diganti.')">
                    @csrf
                    <button type="submit" class="btn-primary btn-emerald w-full justify-center">
                        <i class="fas fa-sync-alt"></i> Terbitkan Ulang
                    </button>
                </form>
            </div>

            {{-- ── Update Photo ── --}}
            <div class="info-card">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-camera text-sky-600 text-xs"></i>
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">Foto Kartu</p>
                </div>
                <form method="POST" action="{{ route('library-card.update-photo') }}" enctype="multipart/form-data" id="photoForm">
                    @csrf
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl overflow-hidden ring-2 ring-gray-200 flex-shrink-0">
                            @if($card->photo_path)
                                <img id="photoPreview" src="{{ asset('storage/' . $card->photo_path) }}" class="w-full h-full object-cover">
                            @else
                                <div id="photoPreview" class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,#7c3aed,#0ea5e9);">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg" class="hidden">
                            <label for="photoInput" class="btn-primary btn-cyan w-full justify-center cursor-pointer">
                                <i class="fas fa-upload"></i> Pilih Foto
                            </label>
                            <button type="submit" id="photoSubmitBtn"
                                    class="btn-primary btn-emerald w-full justify-center hidden">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </div>
                    <p id="photoFileName" class="mt-2 text-xs text-emerald-600 font-medium hidden">
                        <i class="fas fa-check-circle mr-1"></i><span></span>
                    </p>
                    @error('photo')
                        <p class="text-rose-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-2">JPG/PNG · maks. 2 MB</p>
                </form>
            </div>

            {{-- Lost warning --}}
            @if($card->status === 'lost')
            <div class="rounded-2xl p-4 flex items-start gap-3" style="background:#fffbeb; border:1.5px solid #fde68a;">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-amber-600 text-xs"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm mb-0.5">Kartu Dilaporkan Hilang</p>
                    <p class="text-xs text-gray-500">Hubungi petugas perpustakaan untuk penerbitan kartu baru.</p>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ═══ Hidden printable card ═══ --}}
{{-- ═══ Hidden printable area ═══ --}}
<div id="printArea" style="display:none; position:fixed; inset:0; background:white; align-items:center; justify-content:center; z-index:9999;">
    <div style="display:flex; flex-direction:column; align-items:center; gap:20px;">
        <p style="text-align:center; font-family:'Space Grotesk',sans-serif; font-size:11px; color:#9ca3af; letter-spacing:.1em; text-transform:uppercase;">
            Kartu Perpustakaan Digital · Cetak & Gunting
        </p>

        {{-- Kartu Depan --}}
        <div id="downloadCardFront"
             style="width:480px;height:290px;border-radius:24px;position:relative;overflow:hidden;
                    background:linear-gradient(135deg,#1a1a2e 0%,#16213e 40%,#0f3460 70%,#533483 100%);
                    font-family:'Space Grotesk',sans-serif;">
            <div style="position:absolute;inset:0;
                background:radial-gradient(ellipse 60% 50% at 80% 20%, rgba(124,58,237,.45) 0%, transparent 60%),
                           radial-gradient(ellipse 50% 60% at 10% 80%, rgba(6,182,212,.35) 0%, transparent 60%);"></div>
            <div style="position:relative;z-index:10;padding:28px;display:flex;flex-direction:column;height:100%;box-sizing:border-box;">
                {{-- Header --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-book-open" style="color:white;font-size:13px;"></i>
                        </div>
                        <div>
                            <p style="color:white;font-weight:800;font-size:11px;letter-spacing:.15em;margin:0;font-family:'Syne',sans-serif;">PERPUSTAKAAN</p>
                            <p style="color:rgba(196,181,253,.8);font-size:9px;margin:0;">Digital Library</p>
                        </div>
                    </div>
                    <span style="font-size:9px;font-weight:700;padding:4px 10px;border-radius:20px;font-family:'DM Mono',monospace;letter-spacing:.08em;
                        @if($card->status==='active') background:rgba(16,185,129,.2);color:#34d399;border:1px solid rgba(52,211,153,.3);
                        @elseif($card->status==='expired') background:rgba(239,68,68,.2);color:#f87171;border:1px solid rgba(248,113,113,.3);
                        @else background:rgba(245,158,11,.2);color:#fbbf24;border:1px solid rgba(251,191,36,.3);
                        @endif">
                        {{ strtoupper($card->status) }}
                    </span>
                </div>
                {{-- Chip + Member --}}
                <div style="display:flex;align-items:center;gap:14px;flex:1;">
                    <div style="width:40px;height:30px;border-radius:6px;background:linear-gradient(135deg,#f0c040,#c8912a);flex-shrink:0;"></div>
                    <div style="width:52px;height:52px;border-radius:14px;overflow:hidden;border:2px solid rgba(255,255,255,.2);flex-shrink:0;">
                        @if($card->photo_path)
                            <img src="{{ asset('storage/' . $card->photo_path) }}" style="width:100%;height:100%;object-fit:cover;" crossorigin="anonymous">
                        @elseif($card->user->image)
                            <img src="{{ asset('storage/' . $card->user->image) }}" style="width:100%;height:100%;object-fit:cover;" crossorigin="anonymous">
                        @else
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,rgba(124,58,237,.6),rgba(6,182,212,.6));display:flex;align-items:center;justify-content:center;">
                                <span style="color:white;font-weight:800;font-size:22px;">{{ strtoupper(substr($card->user->name,0,1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <p style="color:white;font-weight:800;font-size:16px;margin:0 0 2px;font-family:'Syne',sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $card->user->name }}</p>
                        <p style="color:rgba(196,181,253,.8);font-size:10px;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $card->user->email }}</p>
                    </div>
                </div>
                {{-- Bottom --}}
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,.12);display:flex;justify-content:space-between;align-items:flex-end;">
                    <div>
                        <p style="color:rgba(196,181,253,.6);font-size:8px;text-transform:uppercase;letter-spacing:.15em;margin:0 0 4px;">Nomor Kartu</p>
                        <p style="color:white;font-weight:700;font-size:12px;letter-spacing:.15em;margin:0;font-family:'DM Mono',monospace;">{{ $card->card_number }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="color:rgba(196,181,253,.6);font-size:8px;text-transform:uppercase;letter-spacing:.15em;margin:0 0 4px;">Berlaku s/d</p>
                        <p style="color:white;font-weight:700;font-size:12px;margin:0;font-family:'DM Mono',monospace;">{{ $card->expired_at->format('m / Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Belakang --}}
        <div id="downloadCardBack"
             style="width:480px;height:290px;border-radius:24px;position:relative;overflow:hidden;
                    background:linear-gradient(135deg,#0f2027 0%,#203a43 50%,#2c5364 100%);
                    font-family:'Space Grotesk',sans-serif;">
            {{-- Magnetic stripe --}}
            <div style="width:100%;height:48px;background:linear-gradient(180deg,#111 0%,#222 50%,#111 100%);margin-top:32px;"></div>
            {{-- Content --}}
            <div style="padding:16px 28px;display:flex;flex-direction:column;gap:12px;">
                {{-- Barcode row --}}
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="display:flex;gap:2px;align-items:flex-end;height:32px;">
                        @for($i=0;$i<28;$i++)
                        <span style="display:block;width:2px;height:{{ rand(60,100) }}%;background:rgba(255,255,255,0.6);opacity:{{ 0.4 + ($i%3)*0.2 }};"></span>
                        @endfor
                    </div>
                    <p style="font-family:'DM Mono',monospace;font-size:10px;color:rgba(196,181,253,.6);margin:0;">{{ $card->card_number }}</p>
                </div>
                {{-- Signature box --}}
                <div style="background:rgba(255,255,255,.06);border-radius:10px;padding:10px 14px;">
                    <p style="color:rgba(196,181,253,.5);font-size:9px;margin:0 0 4px;">Tanda Tangan</p>
                    <p style="color:white;font-weight:500;font-size:13px;margin:0;font-family:'Syne',sans-serif;">{{ $card->user->name }}</p>
                </div>
                {{-- Footer --}}
                <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                    <div>
                        <p style="color:rgba(196,181,253,.5);font-size:9px;margin:0 0 2px;">Diterbitkan</p>
                        <p style="color:white;font-size:11px;font-weight:500;margin:0;font-family:'DM Mono',monospace;">{{ $card->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="color:rgba(196,181,253,.5);font-size:9px;margin:0 0 2px;">Perpustakaan Digital</p>
                        <p style="color:white;font-size:11px;font-weight:700;margin:0;">Hubungi admin jika hilang</p>
                    </div>
                </div>
            </div>
        </div>

        <p style="text-align:center;font-family:'Space Grotesk',sans-serif;font-size:10px;color:#d1d5db;margin-top:4px;">
            Dicetak: {{ now()->format('d M Y') }}
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    // ── Photo preview ──
    const photoInput     = document.getElementById('photoInput');
    const photoFileName  = document.getElementById('photoFileName');
    const photoSubmitBtn = document.getElementById('photoSubmitBtn');
    let   photoPreview   = document.getElementById('photoPreview');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { alert('Ukuran file melebihi 2 MB.'); photoInput.value = ''; return; }
        if (!['image/jpeg','image/jpg','image/png'].includes(file.type)) { alert('Gunakan JPG atau PNG.'); photoInput.value = ''; return; }

        photoFileName.classList.remove('hidden');
        photoFileName.querySelector('span').textContent = file.name;
        photoSubmitBtn.classList.remove('hidden');

        const reader = new FileReader();
        reader.onload = function(e) {
            if (photoPreview.tagName === 'IMG') {
                photoPreview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.id        = 'photoPreview';
                img.src       = e.target.result;
                img.className = 'w-full h-full object-cover';
                photoPreview.parentNode.replaceChild(img, photoPreview);
                photoPreview = img;
            }
        };
        reader.readAsDataURL(file);
    });

    // ── Shared render helper ──
    async function renderCardToCanvas(elId) {
        return await html2canvas(document.getElementById(elId), {
            scale          : 3,
            useCORS        : true,
            allowTaint     : true,
            backgroundColor: null,
            logging        : false,
        });
    }

    // ── Download: gabung depan + belakang jadi 1 PNG vertikal ──
    async function downloadCard() {
        const btn = event.currentTarget;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuat...';
        btn.disabled  = true;

        const printArea = document.getElementById('printArea');
        printArea.style.display = 'flex';
        await new Promise(r => setTimeout(r, 150)); // beri waktu render font

        try {
            const [canvasFront, canvasBack] = await Promise.all([
                renderCardToCanvas('downloadCardFront'),
                renderCardToCanvas('downloadCardBack'),
            ]);

            // Gabung dua canvas jadi satu (atas = depan, bawah = belakang)
            const gap    = 24 * 3; // 24px gap, scale 3×
            const merged = document.createElement('canvas');
            merged.width  = canvasFront.width;
            merged.height = canvasFront.height + gap + canvasBack.height;

            const ctx = merged.getContext('2d');
            // Background putih
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, merged.width, merged.height);
            // Gambar depan
            ctx.drawImage(canvasFront, 0, 0);
            // Label pemisah
            ctx.fillStyle = '#9ca3af';
            ctx.font      = `${11 * 3}px 'Space Grotesk', sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText('▲ Depan  ·  Belakang ▼', merged.width / 2, canvasFront.height + gap / 2);
            // Gambar belakang
            ctx.drawImage(canvasBack, 0, canvasFront.height + gap);

            printArea.style.display = 'none';

            const link     = document.createElement('a');
            link.download  = 'kartu-{{ Str::slug($card->user->name) }}-{{ $card->card_number }}.png';
            link.href      = merged.toDataURL('image/png');
            link.click();

        } catch (err) {
            printArea.style.display = 'none';
            console.error(err);
            alert('Gagal membuat gambar. Coba lagi.');
        }

        btn.innerHTML = original;
        btn.disabled  = false;
    }

    // ── Download hanya depan ──
    async function downloadFront() {
        const btn = event.currentTarget;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuat...';
        btn.disabled  = true;

        const printArea = document.getElementById('printArea');
        printArea.style.display = 'flex';
        await new Promise(r => setTimeout(r, 150));

        try {
            const canvas  = await renderCardToCanvas('downloadCardFront');
            printArea.style.display = 'none';
            const link    = document.createElement('a');
            link.download = 'kartu-depan-{{ Str::slug($card->user->name) }}.png';
            link.href     = canvas.toDataURL('image/png');
            link.click();
        } catch (err) {
            printArea.style.display = 'none';
            alert('Gagal membuat gambar.');
        }

        btn.innerHTML = original;
        btn.disabled  = false;
    }

    // ── Download hanya belakang ──
    async function downloadBack() {
        const btn = event.currentTarget;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuat...';
        btn.disabled  = true;

        const printArea = document.getElementById('printArea');
        printArea.style.display = 'flex';
        await new Promise(r => setTimeout(r, 150));

        try {
            const canvas  = await renderCardToCanvas('downloadCardBack');
            printArea.style.display = 'none';
            const link    = document.createElement('a');
            link.download = 'kartu-belakang-{{ Str::slug($card->user->name) }}.png';
            link.href     = canvas.toDataURL('image/png');
            link.click();
        } catch (err) {
            printArea.style.display = 'none';
            alert('Gagal membuat gambar.');
        }

        btn.innerHTML = original;
        btn.disabled  = false;
    }
</script>
@endpush