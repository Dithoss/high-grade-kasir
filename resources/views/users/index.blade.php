@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')

@section('content')
{{-- Flash Message --}}
@if (session('success'))
<div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-sm animate-fade-in">
    <div class="flex items-center">
        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
</div>
@endif

{{-- Header Section --}}
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 mb-6 text-white">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-2">Daftar User</h2>
            <p class="text-indigo-100">Kelola pengguna sistem perpustakaan</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 shadow-lg hover:shadow-xl transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah User
            </a>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total User</p>
                <p class="text-3xl font-bold text-gray-900">{{ $users->total() }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Halaman</p>
                <p class="text-3xl font-bold text-gray-900">{{ $users->currentPage() }}/{{ $users->lastPage() }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Data Per Halaman</p>
                <p class="text-3xl font-bold text-gray-900">{{ $users->count() }}</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Users Table - Desktop --}}
<div class="hidden md:block bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Bergabung</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($users as $user)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $loop->iteration + $users->firstItem() - 1 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                @if($user->image)
                                    <img class="h-12 w-12 rounded-full object-cover ring-2 ring-indigo-100"
                                         src="{{ asset('storage/' . $user->image) }}"
                                         alt="{{ $user->name }}"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg ring-2 ring-indigo-100\'>{{ strtoupper(substr($user->name, 0, 1)) }}</div>';">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg ring-2 ring-indigo-100">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">ID: {{ substr($user->id, 0, 8) }}...</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $user->email }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $role = $user->roles->first();
                            $roleColors = [
                                'admin' => 'bg-red-100 text-red-800 ring-1 ring-red-200',
                                'user'  => 'bg-blue-100 text-blue-800 ring-1 ring-blue-200',
                            ];
                            $colorClass = $roleColors[$role?->name] ?? 'bg-gray-100 text-gray-800 ring-1 ring-gray-200';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            {{ ucfirst($role?->name ?? 'No Role') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $user->created_at->format('d M Y') }}</span>
                            <span class="text-xs text-gray-500">{{ $user->created_at->format('H:i') }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center space-x-2">
                            {{-- Edit --}}
                            <a href="{{ route('users.edit', $user->id) }}"
                               class="inline-flex items-center px-3 py-2 bg-blue-500 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 shadow hover:shadow-md transition-all duration-200"
                               title="Edit User">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>

                            {{-- Kartu Perpustakaan --}}
                            <button type="button"
                                    onclick="openCardModal('{{ $user->id }}')"
                                    class="inline-flex items-center px-3 py-2 bg-cyan-500 text-white text-xs font-semibold rounded-lg hover:bg-cyan-600 shadow hover:shadow-md transition-all duration-200"
                                    title="Lihat Kartu Perpustakaan">
                                <i class="fas fa-id-card mr-1"></i>
                                Kartu
                            </button>

                            {{-- Hapus --}}
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-2 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 shadow hover:shadow-md transition-all duration-200"
                                        title="Hapus User">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-500 font-semibold mb-2">Belum ada user</p>
                            <p class="text-gray-400 text-sm mb-4">Tambahkan user pertama Anda</p>
                            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Tambah User
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Users Cards - Mobile --}}
<div class="md:hidden space-y-4">
    @forelse ($users as $user)
    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
        <div class="p-5">
            <div class="flex items-center space-x-4 mb-4">
                @if($user->image)
                    <img class="h-14 w-14 rounded-full object-cover ring-2 ring-indigo-100"
                         src="{{ asset('storage/' . $user->image) }}"
                         alt="{{ $user->name }}">
                @else
                    <div class="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl ring-2 ring-indigo-100">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900 text-lg">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                @php
                    $role = $user->roles->first();
                    $roleColors = [
                        'admin' => 'bg-red-100 text-red-800 ring-1 ring-red-200',
                        'user'  => 'bg-blue-100 text-blue-800 ring-1 ring-blue-200',
                    ];
                    $colorClass = $roleColors[$role?->name] ?? 'bg-gray-100 text-gray-800 ring-1 ring-gray-200';
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    {{ ucfirst($role?->name ?? 'No Role') }}
                </span>
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $user->created_at->format('d M Y, H:i') }}
                </div>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('users.edit', $user->id) }}"
                   class="flex-1 inline-flex items-center justify-center px-3 py-2.5 bg-blue-500 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 shadow hover:shadow-md transition-all duration-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <button type="button"
                        onclick="openCardModal('{{ $user->id }}')"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2.5 bg-cyan-500 text-white text-sm font-semibold rounded-lg hover:bg-cyan-600 shadow hover:shadow-md transition-all duration-200">
                    <i class="fas fa-id-card mr-1.5"></i>
                    Kartu
                </button>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="flex-1"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-3 py-2.5 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600 shadow hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <svg class="w-20 h-20 text-gray-300 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-gray-500 font-semibold text-lg mb-2">Belum ada user</p>
        <a href="{{ route('users.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 shadow-lg transition-all duration-200 mt-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Tambah User
        </a>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($users->hasPages())
<div class="mt-6">
    {{ $users->links() }}
</div>
@endif


{{-- ============================================================
     LIBRARY CARD MODAL
     ============================================================ --}}
<div id="cardModal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true" aria-labelledby="cardModalTitle">

    {{-- Backdrop --}}
    <div id="cardModalBackdrop"
         class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0"
         onclick="closeCardModal()"></div>

    {{-- Panel --}}
    <div id="cardModalPanel"
         class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden
                transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto">

        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-cyan-600 to-blue-700 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-id-card text-white text-lg"></i>
                </div>
                <div>
                    <h3 id="cardModalTitle" class="text-white font-bold text-lg leading-tight">Kartu Perpustakaan</h3>
                    <p class="text-cyan-100 text-xs" id="cardModalSubtitle">Memuat data...</p>
                </div>
            </div>
            <button onclick="closeCardModal()"
                    class="text-white/70 hover:text-white hover:bg-white/20 p-2 rounded-xl transition-all">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div id="cardModalBody" class="p-6">

            {{-- Loading State --}}
            <div id="cardModalLoading" class="flex flex-col items-center justify-center py-12">
                <div class="w-12 h-12 border-4 border-cyan-200 border-t-cyan-600 rounded-full animate-spin mb-4"></div>
                <p class="text-gray-500 text-sm">Memuat kartu perpustakaan...</p>
            </div>

            {{-- Error State --}}
            <div id="cardModalError" class="hidden flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <p class="text-gray-700 font-semibold mb-1">Gagal memuat data</p>
                <p class="text-gray-500 text-sm" id="cardModalErrorMsg"></p>
            </div>

            {{-- Card Content (hidden until loaded) --}}
            <div id="cardModalContent" class="hidden space-y-5">

                {{-- Visual Card --}}
                <div id="cardVisual"
                     class="rounded-2xl overflow-hidden shadow-xl relative"
                     style="background: linear-gradient(135deg, #1e3a5f 0%, #1a56db 55%, #0e9f6e 100%); min-height: 220px;">
                    {{-- Decorative circles --}}
                    <div class="absolute top-0 right-0 w-48 h-48 rounded-full opacity-10 pointer-events-none"
                         style="background: radial-gradient(circle, white, transparent); transform: translate(30%,-30%);"></div>
                    <div class="absolute bottom-0 left-0 w-36 h-36 rounded-full opacity-10 pointer-events-none"
                         style="background: radial-gradient(circle, white, transparent); transform: translate(-30%,30%);"></div>

                    <div class="relative z-10 p-6 flex flex-col" style="min-height:220px;">
                        {{-- Card header --}}
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-book-open text-white text-sm"></i>
                                </div>
                                <span class="text-white font-bold text-xs tracking-widest uppercase">Perpustakaan Digital</span>
                            </div>
                            <span id="cardStatusBadge"
                                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border"></span>
                        </div>

                        {{-- Member info --}}
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="w-16 h-16 rounded-xl overflow-hidden ring-2 ring-white/30 flex-shrink-0 bg-white/20">
                                <img id="cardPhoto" src="" alt="" class="w-full h-full object-cover hidden">
                                <div id="cardPhotoFallback"
                                     class="w-full h-full flex items-center justify-center">
                                    <span id="cardPhotoInitial" class="text-white font-bold text-2xl"></span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p id="cardUserName" class="text-white font-bold text-lg truncate"></p>
                                <p id="cardUserEmail" class="text-blue-200 text-sm truncate"></p>
                                <span id="cardUserRole" class="inline-block mt-1.5 px-2 py-0.5 bg-white/20 rounded-lg text-xs text-white font-medium"></span>
                            </div>
                        </div>

                        {{-- Card number + expiry --}}
                        <div class="mt-5 pt-4 border-t border-white/20 flex items-end justify-between">
                            <div>
                                <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Nomor Kartu</p>
                                <p id="cardNumber" class="text-white font-mono font-bold text-sm tracking-widest"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Berlaku Hingga</p>
                                <p id="cardExpiry" class="text-white font-bold text-sm"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info row --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        <p id="cardInfoStatus" class="text-sm font-bold capitalize"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Berlaku Hingga</p>
                        <p id="cardInfoExpiry" class="text-sm font-bold"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Diterbitkan</p>
                        <p id="cardInfoIssued" class="text-sm font-bold"></p>
                    </div>
                </div>

                {{-- Warning if expiring soon / expired / lost --}}
                <div id="cardWarning" class="hidden rounded-xl p-4 flex items-start space-x-3">
                    <i id="cardWarningIcon" class="text-lg mt-0.5 flex-shrink-0"></i>
                    <p id="cardWarningText" class="text-sm font-medium"></p>
                </div>

                {{-- Actions --}}
                <div class="border-t border-gray-200 pt-4 flex flex-col sm:flex-row gap-3">
                    {{-- Change status dropdown --}}
                    <div class="relative flex-1" id="statusDropdownWrapper">
                        <button type="button"
                                onclick="toggleStatusDropdown()"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-all">
                            <i class="fas fa-toggle-on mr-2"></i>Ubah Status
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="statusDropdown"
                             class="hidden absolute bottom-full mb-2 left-0 w-full bg-white rounded-xl shadow-xl border border-gray-100 z-10 overflow-hidden">
                            <button onclick="changeCardStatus('active')"
                                    class="w-full text-left px-4 py-3 text-sm text-green-700 hover:bg-green-50 flex items-center transition-colors">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Set Aktif
                            </button>
                            <button onclick="changeCardStatus('expired')"
                                    class="w-full text-left px-4 py-3 text-sm text-red-700 hover:bg-red-50 flex items-center transition-colors border-t border-gray-50">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Set Kadaluarsa
                            </button>
                            <button onclick="changeCardStatus('lost')"
                                    class="w-full text-left px-4 py-3 text-sm text-yellow-700 hover:bg-yellow-50 flex items-center transition-colors border-t border-gray-50">
                                <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>Laporkan Hilang
                            </button>
                        </div>
                    </div>

                    {{-- Regenerate --}}
                    <button type="button"
                            onclick="regenerateCard()"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-md">
                        <i class="fas fa-sync-alt mr-2"></i>Terbitkan Ulang
                    </button>

                    {{-- Detail page --}}
                    <a id="cardDetailLink" href="#"
                       class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-cyan-600 text-white text-sm font-semibold rounded-xl hover:bg-cyan-700 transition-all shadow-md">
                        <i class="fas fa-external-link-alt mr-2"></i>Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fade-in 0.3s ease-out; }
</style>
@endpush

@push('scripts')
<script>
/*
 |--------------------------------------------------------------------------
 | Library Card Modal — vanilla JS, no dependencies
 |--------------------------------------------------------------------------
 */

let currentCardId  = null;
let currentUserId  = null;

// ── Open ────────────────────────────────────────────────────────────────────
function openCardModal(userId) {
    currentUserId = userId;
    currentCardId = null;

    const modal    = document.getElementById('cardModal');
    const backdrop = document.getElementById('cardModalBackdrop');
    const panel    = document.getElementById('cardModalPanel');

    // Show skeleton states
    showLoading();
    modal.classList.remove('hidden');

    // Animate in
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        panel.classList.remove('scale-95','opacity-0');
        panel.classList.add('scale-100','opacity-100');
    });

    document.body.style.overflow = 'hidden';

    // Fetch card data
    fetch(`/admin/library-cards/by-user/${userId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat data kartu.');
        return res.json();
    })
    .then(data => renderCard(data))
    .catch(err => showError(err.message));
}

// ── Close ───────────────────────────────────────────────────────────────────
function closeCardModal() {
    const modal    = document.getElementById('cardModal');
    const backdrop = document.getElementById('cardModalBackdrop');
    const panel    = document.getElementById('cardModalPanel');

    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100','opacity-100');
    panel.classList.add('scale-95','opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    closeStatusDropdown();
}

// ── Loading / Error helpers ─────────────────────────────────────────────────
function showLoading() {
    document.getElementById('cardModalLoading').classList.remove('hidden');
    document.getElementById('cardModalLoading').classList.add('flex');
    document.getElementById('cardModalError').classList.add('hidden');
    document.getElementById('cardModalError').classList.remove('flex');
    document.getElementById('cardModalContent').classList.add('hidden');
    document.getElementById('cardModalSubtitle').textContent = 'Memuat data...';
}

function showError(msg) {
    document.getElementById('cardModalLoading').classList.add('hidden');
    document.getElementById('cardModalLoading').classList.remove('flex');
    document.getElementById('cardModalError').classList.remove('hidden');
    document.getElementById('cardModalError').classList.add('flex');
    document.getElementById('cardModalContent').classList.add('hidden');
    document.getElementById('cardModalErrorMsg').textContent = msg;
    document.getElementById('cardModalSubtitle').textContent = 'Terjadi kesalahan';
}

// ── Render card data into modal ─────────────────────────────────────────────
function renderCard(data) {
    currentCardId = data.id;

    // Subtitle
    document.getElementById('cardModalSubtitle').textContent = data.user_name;

    // Photo
    const photo    = document.getElementById('cardPhoto');
    const fallback = document.getElementById('cardPhotoFallback');
    if (data.photo_url) {
        photo.src = data.photo_url;
        photo.classList.remove('hidden');
        fallback.classList.add('hidden');
    } else {
        document.getElementById('cardPhotoInitial').textContent =
            (data.user_name || 'U').charAt(0).toUpperCase();
        photo.classList.add('hidden');
        fallback.classList.remove('hidden');
    }

    // Text fields
    document.getElementById('cardUserName').textContent  = data.user_name;
    document.getElementById('cardUserEmail').textContent = data.user_email;
    document.getElementById('cardUserRole').textContent  = data.user_role;
    document.getElementById('cardNumber').textContent    = data.card_number;
    document.getElementById('cardExpiry').textContent    = data.expired_at_formatted_short;
    document.getElementById('cardInfoExpiry').textContent = data.expired_at_formatted;
    document.getElementById('cardInfoIssued').textContent = data.created_at_formatted;
    document.getElementById('cardInfoStatus').textContent = ucfirst(data.status);

    // Detail link
    document.getElementById('cardDetailLink').href =
        `/admin/library-cards/${data.id}`;

    // Status badge on card visual
    const badge = document.getElementById('cardStatusBadge');
    const statusStyles = {
        active:  'bg-green-400/20  text-green-200  border-green-400/40',
        expired: 'bg-red-400/20    text-red-200    border-red-400/40',
        lost:    'bg-yellow-400/20 text-yellow-200 border-yellow-400/40',
    };
    badge.className = `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border ${statusStyles[data.status] ?? statusStyles.active}`;
    badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full mr-1.5 ${data.status === 'active' ? 'bg-green-400' : data.status === 'expired' ? 'bg-red-400' : 'bg-yellow-400'}"></span>${data.status.toUpperCase()}`;

    // Info status text color
    const statusTextColors = { active: 'text-green-600', expired: 'text-red-600', lost: 'text-yellow-600' };
    document.getElementById('cardInfoStatus').className =
        `text-sm font-bold capitalize ${statusTextColors[data.status] ?? 'text-gray-700'}`;

    // Warning banner
    const warning    = document.getElementById('cardWarning');
    const warningIcon = document.getElementById('cardWarningIcon');
    const warningText = document.getElementById('cardWarningText');

    if (data.warning) {
        warning.classList.remove('hidden');
        const warnStyles = {
            expiring: { box: 'bg-amber-50 text-amber-800',  icon: 'fas fa-clock text-amber-500' },
            expired:  { box: 'bg-red-50 text-red-800',      icon: 'fas fa-times-circle text-red-500' },
            lost:     { box: 'bg-yellow-50 text-yellow-800', icon: 'fas fa-exclamation-circle text-yellow-500' },
        };
        const style = warnStyles[data.warning_type] ?? warnStyles.lost;
        warning.className = `rounded-xl p-4 flex items-start space-x-3 ${style.box}`;
        warningIcon.className = `text-lg mt-0.5 flex-shrink-0 ${style.icon}`;
        warningText.textContent = data.warning;
    } else {
        warning.classList.add('hidden');
    }

    // Show content
    document.getElementById('cardModalLoading').classList.add('hidden');
    document.getElementById('cardModalLoading').classList.remove('flex');
    document.getElementById('cardModalContent').classList.remove('hidden');
}

// ── Status dropdown ─────────────────────────────────────────────────────────
function toggleStatusDropdown() {
    document.getElementById('statusDropdown').classList.toggle('hidden');
}
function closeStatusDropdown() {
    document.getElementById('statusDropdown').classList.add('hidden');
}

// ── Change card status (AJAX) ───────────────────────────────────────────────
function changeCardStatus(status) {
    if (!currentCardId) return;
    closeStatusDropdown();

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/admin/library-cards/${currentCardId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ status })
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal mengubah status.');
        return res.json();
    })
    .then(data => {
        if (data.success) renderCard(data.card);
    })
    .catch(err => alert(err.message));
}

// ── Regenerate card (AJAX) ──────────────────────────────────────────────────
function regenerateCard() {
    if (!currentCardId) return;
    if (!confirm('Terbitkan ulang kartu ini? Nomor kartu lama akan diganti dan masa berlaku direset 3 tahun.')) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/admin/library-cards/${currentCardId}/regenerate`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal menerbitkan ulang kartu.');
        return res.json();
    })
    .then(data => {
        if (data.success) renderCard(data.card);
    })
    .catch(err => alert(err.message));
}

// ── Helpers ─────────────────────────────────────────────────────────────────
function ucfirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

// Close modal on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCardModal();
});

// Close status dropdown when clicking outside
document.addEventListener('click', e => {
    const wrapper = document.getElementById('statusDropdownWrapper');
    if (wrapper && !wrapper.contains(e.target)) closeStatusDropdown();
});
</script>
@endpush

@endsection