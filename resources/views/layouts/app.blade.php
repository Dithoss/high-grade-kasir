<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Perpustakaan Digital')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Mono:wght@400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        * { font-family: 'Poppins', sans-serif; }

        :root {
            --bg-primary: #f9fafb;
            --bg-secondary: #ffffff;
            --bg-tertiary: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        .dark {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --shadow: rgba(0, 0, 0, 0.3);
        }

        .dark body { background-color: var(--bg-primary); color: var(--text-primary); }
        .dark .bg-white { background-color: var(--bg-secondary) !important; }
        .dark .bg-gray-50 { background-color: var(--bg-primary) !important; }
        .dark .bg-gray-100 { background-color: var(--bg-tertiary) !important; }
        .dark .text-gray-900 { color: var(--text-primary) !important; }
        .dark .text-gray-700 { color: #cbd5e1 !important; }
        .dark .text-gray-600 { color: var(--text-secondary) !important; }
        .dark .text-gray-500 { color: #64748b !important; }
        .dark .border-gray-200 { border-color: var(--border-color) !important; }
        .dark .border-gray-100 { border-color: #475569 !important; }
        .dark .card-friendly { background-color: var(--bg-secondary) !important; box-shadow: 0 2px 8px var(--shadow) !important; }
        .dark input, .dark select, .dark textarea { background-color: var(--bg-tertiary) !important; border-color: var(--border-color) !important; color: var(--text-primary) !important; }
        .dark input::placeholder { color: var(--text-secondary) !important; }
        .dark .search-input { background-color: var(--bg-tertiary) !important; }
        .dark .search-input:focus { background-color: var(--bg-secondary) !important; }
        .dark .flash-message { box-shadow: 0 4px 12px var(--shadow) !important; }

        /* ── Profile dropdown (mobile) ── */
        .profile-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            border: 1px solid #e5e7eb;
            z-index: 100;
            overflow: hidden;
        }
        .dark .profile-dropdown {
            background: var(--bg-secondary);
            border-color: var(--border-color);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .profile-dropdown.open {
            display: block;
            animation: dropdownIn 0.18s ease-out;
        }
        @keyframes dropdownIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .profile-dropdown-item {
            display: flex;
            align-items: center;
            padding: 11px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            transition: background 0.15s;
            cursor: pointer;
            text-decoration: none;
        }
        .dark .profile-dropdown-item { color: #cbd5e1; }
        .profile-dropdown-item:hover { background: #f0f9ff; }
        .dark .profile-dropdown-item:hover { background: #1e3a5f; }
        .profile-dropdown-item .item-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 10px;
            flex-shrink: 0;
            font-size: 13px;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        .dark ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        * { transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }

        /* ── Dark mode toggle ── */
        .dark-mode-toggle {
            position: relative; width: 56px; height: 28px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 14px; cursor: pointer; transition: all 0.3s ease;
        }
        .dark .dark-mode-toggle { background: linear-gradient(135deg, #1e293b, #0f172a); }
        .dark-mode-toggle-slider {
            position: absolute; top: 3px; left: 3px;
            width: 22px; height: 22px;
            background: white; border-radius: 50%;
            transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .dark .dark-mode-toggle-slider { transform: translateX(28px); }
        .dark-mode-toggle-slider i { font-size: 12px; color: #3b82f6; }
        .dark .dark-mode-toggle-slider i { color: #fbbf24; }

        /* ── Notification badge ── */
        .notification-badge {
            position: absolute; top: -4px; right: -4px;
            min-width: 20px; height: 20px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white; font-size: 11px; font-weight: 600;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 6px;
            box-shadow: 0 2px 8px rgba(239,68,68,0.4);
            animation: pulse-badge 2s infinite;
        }
        .notification-badge.hidden { display: none; }
        @keyframes pulse-badge {
            0%,100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* ── Notification dropdown ── */
        .notification-dropdown {
            display: none;
            position: absolute; top: 100%; right: 0; margin-top: 8px;
            width: 380px; max-width: 90vw;
            background: white; border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            z-index: 1000; max-height: 500px; overflow-y: auto;
        }
        .dark .notification-dropdown {
            background: var(--bg-secondary);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .notification-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .notification-item { transition: background-color 0.2s; }
        .notification-item.unread { background-color: #eff6ff; }
        .dark .notification-item.unread { background-color: #1e3a5f; }

        /* ── Misc ── */
        .btn-large { min-height: 44px; font-size: 16px; padding: 0 24px; }

        .menu-active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white; font-weight: 600;
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        .menu-active i { color: white !important; }
        .dark .menu-active { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }

        .card-friendly {
            background: white; border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        .card-friendly:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        .flash-message { animation: slideInRight 0.3s ease-out; }

        .icon-large { font-size: 20px; }
        .search-input:focus {
            box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
            border-color: #3b82f6;
        }

        [data-tooltip] { position: relative; }
        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%);
            padding: 6px 12px;
            background: #1f2937; color: white;
            font-size: 13px; border-radius: 6px;
            white-space: nowrap; margin-bottom: 8px; z-index: 1000;
        }
        .dark [data-tooltip]:hover::after { background: #0f172a; }
    </style>
</head>
<body class="bg-gray-50">
<div class="flex h-screen overflow-hidden">

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR                                                              --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-white transform -translate-x-full
                  transition-transform duration-300 ease-in-out
                  lg:translate-x-0 lg:static flex flex-col shadow-lg border-r border-gray-200">

        {{-- Logo --}}
        <div class="h-20 px-6 flex items-center justify-between
                    bg-gradient-to-r from-blue-600 to-blue-700 shadow-md">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-book-open text-white text-2xl"></i>
                </div>
                <div>
                    <span class="text-xl font-bold text-white block">Perpustakaan</span>
                    <span class="text-xs text-blue-100">Digital Library</span>
                </div>
            </div>
            <button id="closeSidebar" class="lg:hidden text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- ── Profile Section with Slide-down Mini Card ── --}}
        <div class="px-4 pt-4 pb-3 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 relative">

            {{-- Trigger Button --}}
            <button id="profileToggle"
                    onclick="toggleProfileDropdown()"
                    class="w-full text-left group">
                <div class="flex items-center space-x-3 p-3 hover:bg-white rounded-xl transition-all">
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700
                                    rounded-full flex items-center justify-center shadow-md overflow-hidden">
                            @if(Auth::user()->image)
                                <img src="{{ asset('storage/' . Auth::user()->image) }}"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <span class="text-white font-bold text-lg">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name ?? 'User' }}</p>
                        <div class="flex items-center mt-1">
                            @role('admin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    <i class="fas fa-crown mr-1"></i>Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <i class="fas fa-user mr-1"></i>Member
                                </span>
                            @endrole
                        </div>
                    </div>
                    <i class="fas fa-id-card text-gray-400 text-sm group-hover:text-cyan-500 transition-colors"></i>
                </div>
            </button>

            {{-- Slide-down card panel --}}
            <div id="profileCardDropdown"
                 class="overflow-hidden transition-all duration-300 ease-in-out"
                 style="max-height:0; opacity:0;">

                <div class="px-1 pb-2 pt-1 space-y-2.5">

                    {{-- Loading skeleton --}}
                    <div id="pcSkeleton"
                         class="rounded-2xl overflow-hidden"
                         style="background:linear-gradient(135deg,#1e3a5f,#1a56db,#0e9f6e);min-height:158px;">
                        <div class="p-4 flex flex-col justify-between" style="min-height:158px;">
                            <div class="flex items-center justify-between mb-3">
                                <div class="h-2.5 w-28 bg-white/20 rounded-full animate-pulse"></div>
                                <div class="h-4 w-14 bg-white/20 rounded-full animate-pulse"></div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl bg-white/20 animate-pulse flex-shrink-0"></div>
                                <div class="space-y-2 flex-1">
                                    <div class="h-2.5 w-24 bg-white/20 rounded-full animate-pulse"></div>
                                    <div class="h-2 w-32 bg-white/20 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-white/20 flex justify-between">
                                <div class="space-y-1.5">
                                    <div class="h-2 w-16 bg-white/20 rounded-full animate-pulse"></div>
                                    <div class="h-2.5 w-24 bg-white/20 rounded-full animate-pulse"></div>
                                </div>
                                <div class="space-y-1.5 text-right">
                                    <div class="h-2 w-16 bg-white/20 rounded-full animate-pulse ml-auto"></div>
                                    <div class="h-2.5 w-12 bg-white/20 rounded-full animate-pulse ml-auto"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actual mini card --}}
                    <div id="pcCard"
                         class="hidden rounded-2xl overflow-hidden shadow-lg relative"
                         style="background:linear-gradient(135deg,#1e3a5f 0%,#1a56db 55%,#0e9f6e 100%);min-height:158px;">
                        <div class="absolute top-0 right-0 w-36 h-36 rounded-full opacity-10 pointer-events-none"
                             style="background:radial-gradient(circle,white,transparent);transform:translate(35%,-35%);"></div>
                        <div class="relative z-10 p-4 flex flex-col" style="min-height:158px;">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-1.5">
                                    <i class="fas fa-book-open text-white opacity-80" style="font-size:10px;"></i>
                                    <span class="text-white font-bold tracking-wider opacity-90" style="font-size:10px;">PERPUSTAKAAN DIGITAL</span>
                                </div>
                                <span id="pcStatusBadge"
                                      class="inline-flex items-center px-2 py-0.5 rounded-full font-bold border"
                                      style="font-size:10px;"></span>
                            </div>
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="w-12 h-12 rounded-xl overflow-hidden ring-2 ring-white/30 flex-shrink-0 bg-white/20">
                                    <img id="pcPhoto" src="" alt="" class="w-full h-full object-cover hidden">
                                    <div id="pcFallback" class="w-full h-full flex items-center justify-center">
                                        <span id="pcInitial" class="text-white font-bold text-xl"></span>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p id="pcName"  class="text-white font-bold text-sm truncate"></p>
                                    <p id="pcEmail" class="text-blue-200 truncate" style="font-size:11px;"></p>
                                    <span id="pcRole"
                                          class="inline-block mt-1 px-1.5 py-0.5 bg-white/20 rounded text-white/90"
                                          style="font-size:10px;"></span>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-white/20 flex items-end justify-between">
                                <div>
                                    <p class="text-blue-300 uppercase tracking-wider mb-0.5" style="font-size:8px;">Nomor Kartu</p>
                                    <p id="pcNumber" class="text-white font-mono font-bold tracking-widest" style="font-size:11px;"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-blue-300 uppercase tracking-wider mb-0.5" style="font-size:8px;">Berlaku Hingga</p>
                                    <p id="pcExpiry" class="text-white font-bold" style="font-size:11px;"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Error state --}}
                    <div id="pcError" class="hidden rounded-2xl bg-gray-100 p-4 text-center">
                        <i class="fas fa-exclamation-circle text-gray-400 text-xl mb-1"></i>
                        <p class="text-gray-500 text-xs">Kartu tidak ditemukan</p>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-2">
                        <a href="{{ route('users.edit', Auth::id()) }}"
                           class="flex-1 inline-flex items-center justify-center px-3 py-2
                                  bg-white border border-gray-200 text-gray-700 text-xs font-semibold
                                  rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                            <i class="fas fa-user-edit mr-1.5 text-blue-500"></i>Edit Profil
                        </a>
                        @role('admin')
                            <a href="{{ route('admin.library-cards.index') }}"
                               class="flex-1 inline-flex items-center justify-center px-3 py-2
                                      bg-cyan-600 text-white text-xs font-semibold
                                      rounded-xl hover:bg-cyan-700 transition-all shadow-sm">
                                <i class="fas fa-id-card mr-1.5"></i>Semua Kartu
                            </a>
                        @else
                            <a href="{{ route('library-card.show') }}"
                               class="flex-1 inline-flex items-center justify-center px-3 py-2
                                      bg-cyan-600 text-white text-xs font-semibold
                                      rounded-xl hover:bg-cyan-700 transition-all shadow-sm">
                                <i class="fas fa-id-card mr-1.5"></i>Lihat Kartu
                            </a>
                        @endrole
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Navigation ── --}}
        <nav class="flex-1 px-4 py-6 overflow-y-auto">
            <div class="space-y-2">

                @role('admin')
                    <a href="{{ route('dashboard') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="dashboard">
                @else
                    <a href="{{ route('users.dashboard') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="dashboard">
                @endrole
                    <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-50 group-hover:bg-blue-100">
                        <i class="fas fa-home icon-large text-blue-600"></i>
                    </div>
                    <span class="ml-4 font-medium text-base">Beranda</span>
                </a>

                <a href="{{ route('books.index') }}"
                   class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                   data-page="books">
                    <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-green-50 group-hover:bg-green-100">
                        <i class="fas fa-book icon-large text-green-600"></i>
                    </div>
                    <span class="ml-4 font-medium text-base">Koleksi Buku</span>
                </a>

                @role('admin')
                    <a href="{{ route('transactions.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="transactions">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-amber-50 group-hover:bg-amber-100">
                            <i class="fas fa-exchange-alt icon-large text-amber-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Peminjaman</span>
                    </a>
                @endrole

                @role('admin')
                    <div class="pt-6 pb-2">
                        <div class="px-4 mb-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Menu Admin</span>
                        </div>
                    </div>

                    <a href="{{ route('categories.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="categories">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-50 group-hover:bg-purple-100">
                            <i class="fas fa-tags icon-large text-purple-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Kategori</span>
                    </a>

                    <a href="{{ route('users.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="users">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-pink-50 group-hover:bg-pink-100">
                            <i class="fas fa-users icon-large text-pink-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Kelola Member</span>
                    </a>

                    <a href="{{ route('audit.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="audit">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-indigo-50 group-hover:bg-indigo-100">
                            <i class="fas fa-clipboard-list icon-large text-indigo-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Riwayat Aktivitas</span>
                    </a>

                    <a href="{{ route('admin.fines.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="fines">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-50 group-hover:bg-red-100">
                            <i class="fas fa-money-bill-wave icon-large text-red-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Kelola Denda</span>
                    </a>

                    <a href="{{ route('settings.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="settings">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-teal-50 group-hover:bg-teal-100">
                            <i class="fas fa-sliders-h icon-large text-teal-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Aturan Sistem</span>
                    </a>
                @else
                    <a href="{{ route('library-card.show') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="library-card">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-cyan-50 group-hover:bg-cyan-100">
                            <i class="fas fa-id-card icon-large text-cyan-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Kartu Saya</span>
                    </a>

                    <a href="{{ route('fines.index') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="fines">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-50 group-hover:bg-red-100">
                            <i class="fas fa-money-bill-wave icon-large text-red-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Denda Saya</span>
                    </a>

                    <a href="{{ route('transactions.history') }}"
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="history">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-50 group-hover:bg-purple-100">
                            <i class="fas fa-history icon-large text-purple-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Riwayat Transaksi</span>
                    </a>
                @endrole

            </div>
        </nav>

        {{-- Logout --}}
        <div class="border-t border-gray-200 p-4 bg-gray-50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        onclick="return confirm('Yakin ingin keluar dari sistem?')"
                        class="w-full flex items-center justify-center px-4 py-3.5
                               text-red-600 bg-red-50 hover:bg-red-100
                               rounded-xl transition-all font-medium text-base">
                    <i class="fas fa-sign-out-alt icon-large mr-3"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- MAIN CONTENT                                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- ── Header ── --}}
        <header class="bg-white sticky top-0 z-30 border-b border-gray-200 shadow-sm">
            <div class="px-4 lg:px-8 py-4">
                <div class="flex items-center gap-4">

                    {{-- Mobile hamburger --}}
                    <button id="openSidebar"
                            class="lg:hidden text-gray-600 hover:text-gray-900 hover:bg-gray-100 p-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Mobile logo --}}
                    <div class="lg:hidden flex items-center">
                        <i class="fas fa-book-open text-blue-600 text-xl mr-2"></i>
                        <span class="font-bold text-gray-800">Perpustakaan</span>
                    </div>

                    {{-- Search --}}
                    <div class="flex-1 max-w-3xl">
                        <form action="{{ route('books.catalog') }}" method="GET">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Ketik judul buku, nama penulis, atau kategori..."
                                       class="search-input w-full pl-12 pr-4 py-3.5 text-base
                                              bg-gray-50 border-2 border-gray-200 rounded-xl
                                              focus:bg-white focus:outline-none">
                            </div>
                        </form>
                    </div>

                    {{-- Right actions --}}
                    <div class="flex items-center gap-3">

                        {{-- Dark mode --}}
                        <button id="darkModeToggle"
                                class="btn-large flex items-center justify-center w-12 h-12
                                       bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 relative"
                                data-tooltip="Toggle Dark Mode">
                            <div class="dark-mode-toggle">
                                <div class="dark-mode-toggle-slider">
                                    <i class="fas fa-sun"></i>
                                </div>
                            </div>
                        </button>

                        {{-- Filter --}}
                        <button id="toggleFilter"
                                class="btn-large flex items-center gap-2 px-6
                                       bg-blue-600 text-white rounded-xl hover:bg-blue-700
                                       shadow-md hover:shadow-lg font-medium"
                                data-tooltip="Atur Filter Pencarian">
                            <i class="fas fa-filter text-lg"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>

                        {{-- Notifications --}}
                        <div class="relative">
                            <button id="notificationBtn"
                                    class="btn-large flex items-center justify-center w-12 h-12
                                           bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 relative"
                                    data-tooltip="Notifikasi">
                                <i class="fas fa-bell text-lg"></i>
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="notification-badge" id="notificationBadge">
                                        {{ Auth::user()->unreadNotifications->count() }}
                                    </span>
                                @else
                                    <span class="notification-badge hidden" id="notificationBadge"></span>
                                @endif
                            </button>

                            <div id="notificationDropdown" class="notification-dropdown">
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-bold text-gray-900 text-lg">Notifikasi</h3>
                                        @if(Auth::user()->unreadNotifications->count() > 0)
                                            <button id="markAllRead"
                                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                                Tandai Semua Dibaca
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="max-h-96 overflow-y-auto" id="notificationList">
                                    @forelse(Auth::user()->notifications()->limit(5)->get() as $notification)
                                        @php
                                            $data = $notification->data;
                                            $iconColors = [
                                                'amber'  => 'bg-amber-100 text-amber-600',
                                                'blue'   => 'bg-blue-100 text-blue-600',
                                                'green'  => 'bg-green-100 text-green-600',
                                                'red'    => 'bg-red-100 text-red-600',
                                                'orange' => 'bg-orange-100 text-orange-600',
                                            ];
                                            $iconColor = $iconColors[$data['icon_color'] ?? 'blue'] ?? 'bg-blue-100 text-blue-600';
                                        @endphp
                                        <div class="notification-item p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer
                                                    {{ $notification->read_at ? '' : 'unread' }}"
                                             data-notification-id="{{ $notification->id }}">
                                            <div class="flex gap-3">
                                                <div class="w-10 h-10 {{ $iconColor }} rounded-full flex items-center justify-center flex-shrink-0">
                                                    <i class="fas {{ $data['icon'] ?? 'fa-bell' }}"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900 mb-1">{{ $data['title'] ?? 'Notifikasi' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $data['message'] ?? '' }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                                @if(!$notification->read_at)
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center">
                                            <i class="fas fa-bell-slash text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-500 text-sm">Tidak ada notifikasi</p>
                                        </div>
                                    @endforelse
                                </div>
                                @if(Auth::user()->notifications->count() > 0)
                                    <div class="p-3 border-t border-gray-200 text-center">
                                        <a href="{{ route('notifications.index') }}"
                                           class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            Lihat Semua Notifikasi
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Mobile avatar --}}
                        <div class="relative lg:hidden" id="mobileProfileWrapper">
                            <button onclick="toggleMobileProfileDropdown()"
                                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700
                                           rounded-full flex items-center justify-center shadow overflow-hidden
                                           ring-2 ring-transparent hover:ring-blue-300 transition-all">
                                @if(Auth::user()->image)
                                    <img src="{{ asset('storage/' . Auth::user()->image) }}"
                                         alt="{{ Auth::user()->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="text-white font-bold text-sm">
                                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                    </span>
                                @endif
                            </button>
                            <div id="mobileProfileDropdown"
                                 class="profile-dropdown right-0 left-auto w-52"
                                 style="position:absolute; top: calc(100% + 8px);">
                                <a href="{{ route('users.edit', Auth::id()) }}" class="profile-dropdown-item">
                                    <div class="item-icon bg-blue-100">
                                        <i class="fas fa-user-edit text-blue-600"></i>
                                    </div>
                                    <span class="text-sm font-medium">Edit Profil</span>
                                </a>
                                <div class="border-t border-gray-100 mx-3"></div>
                                @role('admin')
                                    <a href="{{ route('admin.library-cards.index') }}" class="profile-dropdown-item">
                                        <div class="item-icon bg-cyan-100">
                                            <i class="fas fa-id-card text-cyan-600"></i>
                                        </div>
                                        <span class="text-sm font-medium">Kartu Anggota</span>
                                    </a>
                                @else
                                    <a href="{{ route('library-card.show') }}" class="profile-dropdown-item">
                                        <div class="item-icon bg-cyan-100">
                                            <i class="fas fa-id-card text-cyan-600"></i>
                                        </div>
                                        <span class="text-sm font-medium">Kartu Saya</span>
                                    </a>
                                @endrole
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        {{-- ── Filter Panel (slide-in) ── --}}
        <div id="filterPanel"
             class="fixed inset-y-0 right-0 z-50 w-full sm:w-96 bg-white shadow-2xl
                    transform translate-x-full transition-transform duration-300 overflow-y-auto">
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold flex items-center">
                            <i class="fas fa-sliders-h mr-3 text-2xl"></i>Filter Pencarian
                        </h3>
                        <p class="text-blue-100 text-sm mt-1">Sesuaikan hasil pencarian buku Anda</p>
                    </div>
                    <button id="closeFilter" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <form action="{{ route('books.index') }}" method="GET" class="p-6 space-y-6">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div class="card-friendly p-4">
                    <label class="block text-base font-bold text-gray-700 mb-3">
                        <i class="fas fa-tags text-blue-600 mr-2"></i>Pilih Kategori Buku
                    </label>
                    <select name="category_id"
                            class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl
                                   focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none bg-white">
                        <option value="">Semua Kategori</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-friendly p-4">
                    <label class="block text-base font-bold text-gray-700 mb-3">
                        <i class="fas fa-boxes text-blue-600 mr-2"></i>Ketersediaan Buku
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" name="stock_min" placeholder="Stok Minimal"
                               value="{{ request('stock_min') }}"
                               class="px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl
                                      focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">
                        <input type="number" name="stock_max" placeholder="Stok Maksimal"
                               value="{{ request('stock_max') }}"
                               class="px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl
                                      focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>Kosongkan untuk menampilkan semua
                    </p>
                </div>
                <div class="card-friendly p-4">
                    <label class="block text-base font-bold text-gray-700 mb-3">
                        <i class="fas fa-sort text-blue-600 mr-2"></i>Urutkan Hasil
                    </label>
                    <select name="sort_by"
                            class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl
                                   focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none bg-white mb-3">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Buku Terbaru</option>
                        <option value="name"       {{ request('sort_by') == 'name'       ? 'selected' : '' }}>Nama Buku</option>
                        <option value="stock"      {{ request('sort_by') == 'stock'      ? 'selected' : '' }}>Jumlah Stok</option>
                    </select>
                    <select name="sort_dir"
                            class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl
                                   focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none bg-white">
                        <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Tertinggi ke Terendah</option>
                        <option value="asc"  {{ request('sort_dir') == 'asc'  ? 'selected' : '' }}>Terendah ke Tertinggi</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                            class="flex-1 btn-large bg-blue-600 text-white rounded-xl font-semibold
                                   hover:bg-blue-700 shadow-lg hover:shadow-xl">
                        <i class="fas fa-search mr-2"></i>Terapkan Filter
                    </button>
                    <a href="{{ route('books.index') }}"
                       class="flex-1 btn-large bg-gray-100 text-gray-700 rounded-xl font-semibold
                              hover:bg-gray-200 text-center flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i>Hapus Filter
                    </a>
                </div>
            </form>
        </div>
        <div id="filterOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

        {{-- ── Page Content ── --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">

            @if(session('success'))
                <div class="flash-message card-friendly bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-5 mb-6">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-green-800 text-base mb-1">Berhasil!</p>
                            <p class="text-green-700 text-sm">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="flash-message card-friendly bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 p-5 mb-6">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-red-800 text-base mb-1">Terjadi Kesalahan!</p>
                            <p class="text-red-700 text-sm">{{ session('error') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="flash-message card-friendly bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-500 p-5 mb-6">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-yellow-800 text-base mb-1">Perhatian!</p>
                            <p class="text-yellow-700 text-sm">{{ session('warning') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-yellow-600 hover:text-yellow-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="flash-message card-friendly bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-5 mb-6">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-info-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-blue-800 text-base mb-1">Informasi</p>
                            <p class="text-blue-700 text-sm">{{ session('info') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- ── Footer ── --}}
        <footer class="bg-white border-t border-gray-200 py-4 shadow-inner">
            <div class="px-4 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
                    <div class="flex items-center space-x-2 mb-2 md:mb-0">
                        <i class="fas fa-copyright text-gray-400"></i>
                        <span>Perpustakaan Digital.</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-400">Github</span>
                        <span class="text-gray-300">|</span>
                        <span>Dithoss</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

{{-- Back to top --}}
<button id="backToTop"
        class="fixed bottom-8 right-8 w-14 h-14 bg-blue-600 text-white rounded-full
               shadow-2xl hover:bg-blue-700 transition-all transform hover:scale-110 hidden z-50">
    <i class="fas fa-arrow-up text-xl"></i>
</button>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- SCRIPTS                                                                  --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<script>
    // ── Card fetch URL (role-aware) ───────────────────────────────────────────
    const CARD_FETCH_URL = '{{ Auth::user()->hasRole("admin")
        ? route("admin.library-cards.by-user", Auth::id())
        : route("library-card.json") }}';

    // ── Sidebar Mini Card ─────────────────────────────────────────────────────
    (function () {
        let _cardLoaded = false;
        let _dropOpen   = false;

        window.toggleProfileDropdown = function () {
            const panel = document.getElementById('profileCardDropdown');
            _dropOpen = !_dropOpen;

            if (_dropOpen) {
                panel.style.maxHeight = '420px';
                panel.style.opacity   = '1';
                if (!_cardLoaded) _loadCard();
            } else {
                panel.style.maxHeight = '0';
                panel.style.opacity   = '0';
            }
        };

        function _loadCard() {
            fetch(CARD_FETCH_URL, {
                headers: {
                    'Accept'           : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(d  => { _renderCard(d); _cardLoaded = true; })
            .catch(() => {
                document.getElementById('pcSkeleton').classList.add('hidden');
                document.getElementById('pcError').classList.remove('hidden');
            });
        }

        function _renderCard(d) {
            // Photo / fallback
            const photo    = document.getElementById('pcPhoto');
            const fallback = document.getElementById('pcFallback');
            if (d.photo_url) {
                photo.src = d.photo_url;
                photo.classList.remove('hidden');
                fallback.classList.add('hidden');
            } else {
                document.getElementById('pcInitial').textContent =
                    (d.user_name || 'U').charAt(0).toUpperCase();
                photo.classList.add('hidden');
                fallback.classList.remove('hidden');
            }

            // Text fields
            document.getElementById('pcName').textContent   = d.user_name  ?? '-';
            document.getElementById('pcEmail').textContent  = d.user_email ?? '-';
            document.getElementById('pcRole').textContent   = d.user_role  ?? '-';
            document.getElementById('pcNumber').textContent = d.card_number ?? '-';
            document.getElementById('pcExpiry').textContent = d.expired_at_formatted_short ?? '-';

            // Status badge
            const badge  = document.getElementById('pcStatusBadge');
            const bStyle = {
                active : 'bg-green-400/20 text-green-200 border-green-400/40',
                expired: 'bg-red-400/20 text-red-200 border-red-400/40',
                lost   : 'bg-yellow-400/20 text-yellow-200 border-yellow-400/40',
            };
            const bDot = {
                active : 'bg-green-400',
                expired: 'bg-red-400',
                lost   : 'bg-yellow-400',
            };
            const status = d.status ?? 'active';
            badge.className = `inline-flex items-center px-2 py-0.5 rounded-full font-bold border ${bStyle[status] ?? bStyle.active}`;
            badge.style.fontSize = '10px';
            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full mr-1 ${bDot[status] ?? bDot.active}"></span>${status.toUpperCase()}`;

            // Swap skeleton → card
            document.getElementById('pcSkeleton').classList.add('hidden');
            document.getElementById('pcCard').classList.remove('hidden');
        }

        // Close panel when clicking outside
        document.addEventListener('click', (e) => {
            const toggle = document.getElementById('profileToggle');
            const panel  = document.getElementById('profileCardDropdown');
            if (!toggle || !panel) return;
            if (!toggle.contains(e.target) && !panel.contains(e.target) && _dropOpen) {
                _dropOpen = false;
                panel.style.maxHeight = '0';
                panel.style.opacity   = '0';
            }
        });
    })();

    // ── Dark Mode ─────────────────────────────────────────────────────────────
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;

    // Apply saved preference immediately
    if ((localStorage.getItem('darkMode') || 'light') === 'dark') {
        html.classList.add('dark');
        const icon = darkModeToggle?.querySelector('.dark-mode-toggle-slider i');
        if (icon) icon.className = 'fas fa-moon';
    }

    darkModeToggle?.addEventListener('click', () => {
        html.classList.toggle('dark');
        const isDark = html.classList.contains('dark');
        localStorage.setItem('darkMode', isDark ? 'dark' : 'light');
        const icon = darkModeToggle.querySelector('.dark-mode-toggle-slider i');
        if (icon) icon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
    });

    // ── Sidebar ───────────────────────────────────────────────────────────────
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const openBtn  = document.getElementById('openSidebar');
    const closeBtn = document.getElementById('closeSidebar');

    function openSidebar() {
        sidebar?.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    openBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // ── Filter Panel ──────────────────────────────────────────────────────────
    const filterPanel   = document.getElementById('filterPanel');
    const filterOverlay = document.getElementById('filterOverlay');

    function openFilter() {
        filterPanel?.classList.remove('translate-x-full');
        filterOverlay?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeFilter() {
        filterPanel?.classList.add('translate-x-full');
        filterOverlay?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('toggleFilter')?.addEventListener('click', openFilter);
    document.getElementById('closeFilter')?.addEventListener('click', closeFilter);
    filterOverlay?.addEventListener('click', closeFilter);

    // ── Mobile Profile Dropdown ───────────────────────────────────────────────
    function toggleMobileProfileDropdown() {
        document.getElementById('mobileProfileDropdown')?.classList.toggle('open');
    }

    // ── Close dropdowns on outside click ─────────────────────────────────────
    document.addEventListener('click', (e) => {
        // Mobile profile dropdown
        const mobileWrapper = document.getElementById('mobileProfileWrapper');
        const mobileDd      = document.getElementById('mobileProfileDropdown');
        if (mobileDd && mobileWrapper && !mobileWrapper.contains(e.target)) {
            mobileDd.classList.remove('open');
        }

        // Notification dropdown
        const notifBtn = document.getElementById('notificationBtn');
        const notifDd  = document.getElementById('notificationDropdown');
        if (notifDd && notifBtn &&
            !notifBtn.contains(e.target) && !notifDd.contains(e.target)) {
            notifDd.classList.remove('show');
        }
    });

    // ── Notifications ─────────────────────────────────────────────────────────
    document.getElementById('notificationBtn')?.addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('notificationDropdown')?.classList.toggle('show');
    });

    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function () {
            const id   = this.dataset.notificationId;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrf) return;

            fetch('/notifications/' + id + '/read', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN' : csrf,
                    'Accept'       : 'application/json',
                    'Content-Type' : 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.classList.remove('unread');
                    this.querySelector('.bg-blue-500')?.remove();
                    updateNotificationBadge();
                }
            })
            .catch(console.error);
        });
    });

    document.getElementById('markAllRead')?.addEventListener('click', function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf) return;

        fetch('/notifications/mark-all-read', {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN' : csrf,
                'Accept'       : 'application/json',
                'Content-Type' : 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item').forEach(i => i.classList.remove('unread'));
                document.querySelectorAll('.notification-item .bg-blue-500').forEach(d => d.remove());
                this.style.display = 'none';
                const badge = document.getElementById('notificationBadge');
                if (badge) badge.classList.add('hidden');
            }
        })
        .catch(console.error);
    });

    function updateNotificationBadge() {
        fetch('/notifications/unread-count')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (!badge) return;
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            })
            .catch(console.error);
    }

    // ── Escape key ────────────────────────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeSidebar();
        closeFilter();
        document.getElementById('notificationDropdown')?.classList.remove('show');
        document.getElementById('mobileProfileDropdown')?.classList.remove('open');
    });

    // ── Active nav link ───────────────────────────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
            link.classList.add('menu-active');
        }
    });

    // ── Back to top ───────────────────────────────────────────────────────────
    const backToTopBtn = document.getElementById('backToTop');
    const mainContent  = document.querySelector('main');
    if (mainContent && backToTopBtn) {
        mainContent.addEventListener('scroll', () => {
            backToTopBtn.classList.toggle('hidden', mainContent.scrollTop <= 300);
        });
        backToTopBtn.addEventListener('click', () =>
            mainContent.scrollTo({ top: 0, behavior: 'smooth' })
        );
    }

    // ── Auto-dismiss flash messages (7s) ─────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.flash-message').forEach(msg => {
            msg.style.transition = 'opacity 0.5s, transform 0.5s';
            msg.style.opacity    = '0';
            msg.style.transform  = 'translateX(100%)';
            setTimeout(() => msg.remove(), 500);
        });
    }, 7000);
</script>

@stack('scripts')
</body>
</html>