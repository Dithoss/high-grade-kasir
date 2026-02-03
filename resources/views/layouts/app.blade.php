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
    @stack('styles')
    <style>
        /* [Previous styles remain exactly the same] */
        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Dark mode variables */
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

        /* Apply dark mode colors */
        .dark body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .dark .bg-white {
            background-color: var(--bg-secondary) !important;
        }

        .dark .bg-gray-50 {
            background-color: var(--bg-primary) !important;
        }

        .dark .bg-gray-100 {
            background-color: var(--bg-tertiary) !important;
        }

        .dark .text-gray-900 {
            color: var(--text-primary) !important;
        }

        .dark .text-gray-700 {
            color: #cbd5e1 !important;
        }

        .dark .text-gray-600 {
            color: var(--text-secondary) !important;
        }

        .dark .text-gray-500 {
            color: #64748b !important;
        }

        .dark .border-gray-200 {
            border-color: var(--border-color) !important;
        }

        .dark .border-gray-100 {
            border-color: #475569 !important;
        }

        /* Dark mode for cards */
        .dark .card-friendly {
            background-color: var(--bg-secondary) !important;
            box-shadow: 0 2px 8px var(--shadow) !important;
        }

        /* Dark mode for inputs */
        .dark input,
        .dark select,
        .dark textarea {
            background-color: var(--bg-tertiary) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary) !important;
        }

        .dark input::placeholder {
            color: var(--text-secondary) !important;
        }

        .dark .search-input {
            background-color: var(--bg-tertiary) !important;
        }

        .dark .search-input:focus {
            background-color: var(--bg-secondary) !important;
        }

        /* Dark mode for flash messages */
        .dark .flash-message {
            box-shadow: 0 4px 12px var(--shadow) !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        /* Dark Mode Toggle Button */
        .dark-mode-toggle {
            position: relative;
            width: 56px;
            height: 28px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dark .dark-mode-toggle {
            background: linear-gradient(135deg, #1e293b, #0f172a);
        }

        .dark-mode-toggle-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .dark .dark-mode-toggle-slider {
            transform: translateX(28px);
        }

        .dark-mode-toggle-slider i {
            font-size: 12px;
            color: #3b82f6;
        }

        .dark .dark-mode-toggle-slider i {
            color: #fbbf24;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            animation: pulse-badge 2s infinite;
        }

        .notification-badge.hidden {
            display: none;
        }

        @keyframes pulse-badge {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Notification Dropdown */
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            width: 380px;
            max-width: 90vw;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-height: 500px;
            overflow-y: auto;
        }

        .dark .notification-dropdown {
            background: var(--bg-secondary);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .notification-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Notification item styles */
        .notification-item {
            transition: background-color 0.2s;
        }

        .notification-item.unread {
            background-color: #eff6ff;
        }

        .dark .notification-item.unread {
            background-color: #1e3a5f;
        }

        /* Button yang lebih besar dan jelas */
        .btn-large {
            min-height: 44px;
            font-size: 16px;
            padding: 0 24px;
        }

        /* Active menu - lebih jelas */
        .menu-active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .menu-active i {
            color: white !important;
        }

        .dark .menu-active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        /* Card yang lebih friendly */
        .card-friendly {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.2s;
        }

        .card-friendly:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        /* Flash message animation */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .flash-message {
            animation: slideInRight 0.3s ease-out;
        }

        /* Icon yang lebih besar untuk kemudahan */
        .icon-large {
            font-size: 20px;
        }

        /* Search focus yang lebih jelas */
        .search-input:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }

        /* Tooltip sederhana */
        [data-tooltip] {
            position: relative;
        }

        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 6px 12px;
            background: #1f2937;
            color: white;
            font-size: 13px;
            border-radius: 6px;
            white-space: nowrap;
            margin-bottom: 8px;
            z-index: 1000;
        }

        .dark [data-tooltip]:hover::after {
            background: #0f172a;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar - [Keep existing sidebar code exactly the same] -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static flex flex-col shadow-lg border-r border-gray-200">
            
            <!-- Logo Header - Lebih Besar dan Jelas -->
            <div class="h-20 px-6 flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 shadow-md">
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

            <!-- User Info - Lebih Prominent -->
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <a href="{{ route('users.edit', Auth::id()) }}" class="block">
                    <div class="flex items-center space-x-3 p-3 hover:bg-white rounded-xl transition-all">
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center shadow-md overflow-hidden">
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
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ Auth::user()->name ?? 'User' }}
                            </p>
                            <div class="flex items-center mt-1">
                                @role('admin')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                        <i class="fas fa-crown mr-1"></i>
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        <i class="fas fa-user mr-1"></i>
                                        Member
                                    </span>
                                @endrole
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </div>
                </a>
            </div>
            
            <!-- Navigation Menu - Icon dan Text Lebih Besar -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto">
                <div class="space-y-2">
                    
                    <!-- Dashboard -->
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
                    
                    <!-- Books -->
                    <a href="{{ route('books.index') }}" 
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="books">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-green-50 group-hover:bg-green-100">
                            <i class="fas fa-book icon-large text-green-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">Koleksi Buku</span>
                    </a>
                    
                    <!-- Transactions -->
                    <a href="{{ route('transactions.index') }}" 
                       class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                       data-page="transactions">
                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-amber-50 group-hover:bg-amber-100">
                            <i class="fas fa-exchange-alt icon-large text-amber-600"></i>
                        </div>
                        <span class="ml-4 font-medium text-base">
                            @role('user')
                                Peminjaman Saya
                            @else
                                Peminjaman
                            @endrole
                        </span>
                    </a>

                    @role('admin')
                        <!-- Admin Section -->
                        <div class="pt-6 pb-2">
                            <div class="px-4 mb-3">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Menu Admin</span>
                            </div>
                        </div>

                        <!-- Categories -->
                        <a href="{{ route('categories.index') }}" 
                           class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                           data-page="categories">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-50 group-hover:bg-purple-100">
                                <i class="fas fa-tags icon-large text-purple-600"></i>
                            </div>
                            <span class="ml-4 font-medium text-base">Kategori</span>
                        </a>

                        <!-- Users -->
                        <a href="{{ route('users.index') }}" 
                           class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                           data-page="users">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-pink-50 group-hover:bg-pink-100">
                                <i class="fas fa-users icon-large text-pink-600"></i>
                            </div>
                            <span class="ml-4 font-medium text-base">Kelola Member</span>
                        </a>
                        
                        <!-- Audit Log -->
                        <a href="{{ route('audit.index') }}" 
                           class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                           data-page="audit">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-indigo-50 group-hover:bg-indigo-100">
                                <i class="fas fa-clipboard-list icon-large text-indigo-600"></i>
                            </div>
                            <span class="ml-4 font-medium text-base">Riwayat Aktivitas</span>
                        </a>

                        <!-- Fines -->
                        <a href="{{ route('admin.fines.index') }}" 
                           class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                           data-page="fines">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-50 group-hover:bg-red-100">
                                <i class="fas fa-money-bill-wave icon-large text-red-600"></i>
                            </div>
                            <span class="ml-4 font-medium text-base">Kelola Denda</span>
                        </a>
                    @else
                        <!-- User Fines -->
                        <a href="{{ route('fines.index') }}" 
                           class="nav-link flex items-center px-4 py-3.5 text-gray-700 hover:bg-blue-50 rounded-xl group"
                           data-page="fines">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-50 group-hover:bg-red-100">
                                <i class="fas fa-money-bill-wave icon-large text-red-600"></i>
                            </div>
                            <span class="ml-4 font-medium text-base">Denda Saya</span>
                        </a>
                    @endrole
                </div>
            </nav>
            
            <!-- Logout Button - Sangat Jelas -->
            <div class="border-t border-gray-200 p-4 bg-gray-50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center px-4 py-3.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-all font-medium text-base"
                            onclick="return confirm('Yakin ingin keluar dari sistem?')">
                        <i class="fas fa-sign-out-alt icon-large mr-3"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay untuk mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Header dengan Notifikasi Real-time -->
            <header class="bg-white sticky top-0 z-30 border-b border-gray-200 shadow-sm">
                <div class="px-4 lg:px-8 py-4">
                    <div class="flex items-center gap-4">
                        
                        <!-- Mobile Menu Button -->
                        <button id="openSidebar" class="lg:hidden text-gray-600 hover:text-gray-900 hover:bg-gray-100 p-3 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <!-- Logo Mobile -->
                        <div class="lg:hidden flex items-center">
                            <i class="fas fa-book-open text-blue-600 text-xl mr-2"></i>
                            <span class="font-bold text-gray-800">Perpustakaan</span>
                        </div>

                        <!-- Search Bar - Sangat Prominent -->
                        <div class="flex-1 max-w-3xl">
                            <form action="{{ route('books.index') }}" method="GET">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400 text-lg"></i>
                                    </div>
                                    <input 
                                        type="text" 
                                        name="search"
                                        value="{{ request('search') }}"
                                        placeholder="Ketik judul buku, nama penulis, atau kategori yang Anda cari..."
                                        class="search-input w-full pl-12 pr-4 py-3.5 text-base bg-gray-50 border-2 border-gray-200 rounded-xl focus:bg-white focus:outline-none"
                                    >
                                </div>
                            </form>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3">
                            
                            <!-- Dark Mode Toggle -->
                            <button 
                                id="darkModeToggle"
                                class="btn-large flex items-center justify-center w-12 h-12 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 relative"
                                data-tooltip="Toggle Dark Mode"
                            >
                                <div class="dark-mode-toggle">
                                    <div class="dark-mode-toggle-slider">
                                        <i class="fas fa-sun"></i>
                                    </div>
                                </div>
                            </button>

                            <!-- Filter Button - Jelas dengan Text -->
                            <button 
                                id="toggleFilter" 
                                class="btn-large flex items-center gap-2 px-6 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-md hover:shadow-lg font-medium"
                                data-tooltip="Atur Filter Pencarian"
                            >
                                <i class="fas fa-filter text-lg"></i>
                                <span class="hidden sm:inline">Filter</span>
                            </button>

                            <!-- Notification Button with Real Data -->
                            <div class="relative">
                                <button 
                                    id="notificationBtn"
                                    class="btn-large flex items-center justify-center w-12 h-12 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 relative"
                                    data-tooltip="Notifikasi"
                                >
                                    <i class="fas fa-bell text-lg"></i>
                                    @if(Auth::user()->unreadNotifications->count() > 0)
                                        <span class="notification-badge" id="notificationBadge">
                                            {{ Auth::user()->unreadNotifications->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Notification Dropdown with Real Notifications -->
                                <div id="notificationDropdown" class="notification-dropdown">
                                    <div class="p-4 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <h3 class="font-bold text-gray-900 text-lg">Notifikasi</h3>
                                            @if(Auth::user()->unreadNotifications->count() > 0)
                                                <button 
                                                    id="markAllRead"
                                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                                                >
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
                                                    'amber' => 'bg-amber-100 text-amber-600',
                                                    'blue' => 'bg-blue-100 text-blue-600',
                                                    'green' => 'bg-green-100 text-green-600',
                                                    'red' => 'bg-red-100 text-red-600',
                                                    'orange' => 'bg-orange-100 text-orange-600',
                                                ];
                                                $iconColor = $iconColors[$data['icon_color'] ?? 'blue'] ?? 'bg-blue-100 text-blue-600';
                                            @endphp
                                            <div class="notification-item p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer {{ $notification->read_at ? '' : 'unread' }}"
                                                 data-notification-id="{{ $notification->id }}">
                                                <div class="flex gap-3">
                                                    <div class="w-10 h-10 {{ $iconColor }} rounded-full flex items-center justify-center flex-shrink-0">
                                                        <i class="fas {{ $data['icon'] ?? 'fa-bell' }}"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-gray-900 mb-1">
                                                            {{ $data['title'] ?? 'Notifikasi' }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            {{ $data['message'] ?? '' }}
                                                        </p>
                                                        <p class="text-xs text-gray-400 mt-1">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </p>
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
                                            <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                                Lihat Semua Notifikasi
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- User Avatar (Mobile) -->
                            <a href="{{ route('users.edit', Auth::id()) }}" class="lg:hidden">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center shadow overflow-hidden">
                                    @if(Auth::user()->image)
                                        <img src="{{ asset('storage/' . Auth::user()->image) }}" 
                                             alt="{{ Auth::user()->name }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- [Keep Filter Panel and rest of the layout exactly the same] -->
            <!-- Filter Panel (Slide dari kanan) -->
            <div id="filterPanel" class="fixed inset-y-0 right-0 z-50 w-full sm:w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto">
                
                <!-- Header Panel -->
                <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold flex items-center">
                                <i class="fas fa-sliders-h mr-3 text-2xl"></i>
                                Filter Pencarian
                            </h3>
                            <p class="text-blue-100 text-sm mt-1">Sesuaikan hasil pencarian buku Anda</p>
                        </div>
                        <button id="closeFilter" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('books.index') }}" method="GET" class="p-6 space-y-6">
                    
                    <!-- Preserve search query -->
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <!-- Category Filter -->
                    <div class="card-friendly p-4">
                        <label class="block text-base font-bold text-gray-700 mb-3">
                            <i class="fas fa-tags text-blue-600 mr-2"></i>
                            Pilih Kategori Buku
                        </label>
                        <select name="category_id" class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none bg-white">
                            <option value="">Semua Kategori</option>
                            @foreach(\App\Models\Category::all() as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stock Filter -->
                    <div class="card-friendly p-4">
                        <label class="block text-base font-bold text-gray-700 mb-3">
                            <i class="fas fa-boxes text-blue-600 mr-2"></i>
                            Ketersediaan Buku
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <input 
                                type="number" 
                                name="stock_min" 
                                placeholder="Stok Minimal"
                                value="{{ request('stock_min') }}"
                                class="px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                            <input 
                                type="number" 
                                name="stock_max" 
                                placeholder="Stok Maksimal"
                                value="{{ request('stock_max') }}"
                                class="px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Kosongkan untuk menampilkan semua
                        </p>
                    </div>

                    <!-- Sort Options -->
                    <div class="card-friendly p-4">
                        <label class="block text-base font-bold text-gray-700 mb-3">
                            <i class="fas fa-sort text-blue-600 mr-2"></i>
                            Urutkan Hasil
                        </label>
                        
                        <select name="sort_by" class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none bg-white mb-3">
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Buku Terbaru</option>
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama Buku</option>
                            <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Jumlah Stok</option>
                        </select>

                        <select name="sort_dir" class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none bg-white">
                            <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Tertinggi ke Terendah</option>
                            <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Terendah ke Tertinggi</option>
                        </select>
                    </div>

                    <!-- Action Buttons - Lebih Besar dan Jelas -->
                    <div class="flex gap-3 pt-4">
                        <button 
                            type="submit"
                            class="flex-1 btn-large bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 shadow-lg hover:shadow-xl"
                        >
                            <i class="fas fa-search mr-2"></i>
                            Terapkan Filter
                        </button>
                        <a 
                            href="{{ route('books.index') }}"
                            class="flex-1 btn-large bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 text-center flex items-center justify-center"
                        >
                            <i class="fas fa-redo mr-2"></i>
                            Hapus Filter
                        </a>
                    </div>
                </form>
            </div>

            <!-- Filter Panel Overlay -->
            <div id="filterOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                
                <!-- Flash Messages - Lebih Friendly -->
                @if (session('success'))
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

                @if (session('error'))
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

                @if (session('warning'))
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

                @if (session('info'))
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

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 shadow-inner">
                <div class="px-4 lg:px-8">
                    <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
                        <div class="flex items-center space-x-2 mb-2 md:mb-0">
                            <i class="fas fa-copyright text-gray-400"></i>
                            <span>2024 Perpustakaan Digital. Hak Cipta Dilindungi.</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-400">Versi 1.0.0</span>
                            <span class="text-gray-300">|</span>
                            <span>Dibuat dengan <i class="fas fa-heart text-red-500"></i></span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Back to Top Button - Lebih Besar -->
    <button id="backToTop" 
            class="fixed bottom-8 right-8 w-14 h-14 bg-blue-600 text-white rounded-full shadow-2xl hover:bg-blue-700 transition-all transform hover:scale-110 hidden z-50">
        <i class="fas fa-arrow-up text-xl"></i>
    </button>

    <script>
        // Dark Mode Handler
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        
        // Check for saved dark mode preference or default to light mode
        const currentMode = localStorage.getItem('darkMode') || 'light';
        if (currentMode === 'dark') {
            html.classList.add('dark');
        }

        darkModeToggle?.addEventListener('click', () => {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('darkMode', isDark ? 'dark' : 'light');
            
            // Update icon
            const icon = darkModeToggle.querySelector('.dark-mode-toggle-slider i');
            if (isDark) {
                icon.className = 'fas fa-moon';
            } else {
                icon.className = 'fas fa-sun';
            }
        });

        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Filter panel toggle
        const filterPanel = document.getElementById('filterPanel');
        const filterOverlay = document.getElementById('filterOverlay');
        const toggleFilterBtn = document.getElementById('toggleFilter');
        const closeFilterBtn = document.getElementById('closeFilter');

        function openFilter() {
            filterPanel.classList.remove('translate-x-full');
            filterOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeFilter() {
            filterPanel.classList.add('translate-x-full');
            filterOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        toggleFilterBtn?.addEventListener('click', openFilter);
        closeFilterBtn?.addEventListener('click', closeFilter);
        filterOverlay?.addEventListener('click', closeFilter);

        // Notification dropdown toggle
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');

        notificationBtn?.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });

        // Close notification when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationBtn?.contains(e.target) && !notificationDropdown?.contains(e.target)) {
                notificationDropdown?.classList.remove('show');
            }
        });

        // Mark notification as read when clicked
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.notificationId;
                
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        this.querySelector('.bg-blue-500')?.remove();
                        
                        // Update badge count
                        updateNotificationBadge();
                    }
                });
            });
        });

        // Mark all as read
        document.getElementById('markAllRead')?.addEventListener('click', function() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                    });
                    document.querySelectorAll('.bg-blue-500').forEach(dot => dot.remove());
                    this.style.display = 'none';
                    
                    // Update badge
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        badge.classList.add('hidden');
                    }
                }
            });
        });

        // Function to update notification badge
        function updateNotificationBadge() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                            badge.classList.remove('hidden');
                        }
                    } else {
                        if (badge) {
                            badge.classList.add('hidden');
                        }
                    }
                });
        }

        // Close all panels on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeSidebar();
                closeFilter();
                notificationDropdown?.classList.remove('show');
            }
        });

        // Highlight active menu
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
                link.classList.add('menu-active');
            }
        });

        // Back to top button
        const backToTopBtn = document.getElementById('backToTop');
        const mainContent = document.querySelector('main');
        
        if (mainContent && backToTopBtn) {
            mainContent.addEventListener('scroll', () => {
                if (mainContent.scrollTop > 300) {
                    backToTopBtn.classList.remove('hidden');
                } else {
                    backToTopBtn.classList.add('hidden');
                }
            });

            backToTopBtn.addEventListener('click', () => {
                mainContent.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Auto-hide flash messages after 7 seconds
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(message => {
                message.style.transition = 'opacity 0.5s, transform 0.5s';
                message.style.opacity = '0';
                message.style.transform = 'translateX(100%)';
                setTimeout(() => message.remove(), 500);
            });
        }, 7000);
    </script>

    @stack('scripts')
</body>
</html>