@extends('layouts.app')

@section('title', 'Kelola Kartu Perpustakaan')
@section('header', 'Kartu Perpustakaan')
@section('subtitle', 'Kelola seluruh kartu anggota perpustakaan')

@section('content')
<div class="space-y-6">

    {{-- ====================== STATS ====================== --}}
    @php
        $allCards     = \App\Models\LibraryCard::count();
        $activeCards  = \App\Models\LibraryCard::where('status', 'active')->count();
        $expiredCards = \App\Models\LibraryCard::where('status', 'expired')->count();
        $lostCards    = \App\Models\LibraryCard::where('status', 'lost')->count();
        $expiringSoon = \App\Models\LibraryCard::where('status', 'active')
                            ->where('expired_at', '<=', now()->addDays(30))
                            ->where('expired_at', '>=', now())
                            ->count();
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-id-card text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $allCards }}</p>
                    <p class="text-xs text-gray-500 font-medium">Total Kartu</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeCards }}</p>
                    <p class="text-xs text-gray-500 font-medium">Kartu Aktif</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $expiringSoon }}</p>
                    <p class="text-xs text-gray-500 font-medium">Segera Habis</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $expiredCards + $lostCards }}</p>
                    <p class="text-xs text-gray-500 font-medium">Tidak Aktif</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== FILTER BAR ====================== --}}
    <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
        <form method="GET" action="{{ route('admin.library-cards.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Cari nama anggota atau nomor kartu..."
                       class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>
            <select name="status" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="">Semua Status</option>
                <option value="active"  {{ ($filters['status'] ?? '') === 'active'  ? 'selected' : '' }}>Aktif</option>
                <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                <option value="lost"    {{ ($filters['status'] ?? '') === 'lost'    ? 'selected' : '' }}>Hilang</option>
            </select>
            <label class="flex items-center gap-2 px-4 py-3 border border-gray-300 rounded-xl cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="expiring_soon" value="1"
                       {{ !empty($filters['expiring_soon']) ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 rounded">
                <span class="text-sm text-gray-700 whitespace-nowrap">Segera Habis</span>
            </label>
            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-all shadow-md whitespace-nowrap">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <a href="{{ route('admin.library-cards.index') }}"
               class="px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-all text-center">
                <i class="fas fa-redo"></i>
            </a>
        </form>
    </div>

    {{-- ====================== TABLE ====================== --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-id-card text-blue-600 mr-2"></i>
                Daftar Kartu Perpustakaan
                <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                    {{ $cards->total() }}
                </span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Anggota</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nomor Kartu</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Berlaku Hingga</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cards as $card)
                    @php
                        $daysLeft       = now()->diffInDays($card->expired_at, false);
                        $isExpiringSoon = $daysLeft <= 30 && $daysLeft > 0 && $card->status === 'active';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        {{-- Member --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                    @if($card->photo_path)
                                        <img src="{{ asset('storage/' . $card->photo_path) }}" alt="" class="w-full h-full object-cover">
                                    @elseif($card->user->image)
                                        <img src="{{ asset('storage/' . $card->user->image) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($card->user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $card->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $card->user->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Card Number --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-medium text-gray-800 bg-gray-100 px-3 py-1 rounded-lg">
                                {{ $card->card_number }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                @if($card->status === 'active') bg-green-100 text-green-700
                                @elseif($card->status === 'expired') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700
                                @endif">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                    @if($card->status === 'active') bg-green-500
                                    @elseif($card->status === 'expired') bg-red-500
                                    @else bg-yellow-500
                                    @endif"></span>
                                {{ ucfirst($card->status) }}
                            </span>
                        </td>

                        {{-- Expiry --}}
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-800">{{ $card->expired_at->format('d M Y') }}</p>
                            @if($isExpiringSoon)
                                <p class="text-xs text-amber-600 font-medium mt-0.5">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $daysLeft }} hari lagi
                                </p>
                            @elseif($daysLeft <= 0 && $card->status !== 'expired')
                                <p class="text-xs text-red-500 font-medium mt-0.5">Kedaluwarsa</p>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.library-cards.detail', $card->id) }}"
                                   class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- Status + Regenerate dropdown --}}
                                <div class="relative card-action-wrapper">
                                    <button type="button"
                                            onclick="toggleCardActionDropdown(this)"
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="card-action-dropdown hidden absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-20">
                                        <form method="POST" action="{{ route('admin.library-cards.update-status', $card->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-green-700 hover:bg-green-50 flex items-center transition-colors">
                                                <i class="fas fa-check-circle mr-2"></i>Set Aktif
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.library-cards.update-status', $card->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="expired">
                                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-700 hover:bg-red-50 flex items-center transition-colors border-t border-gray-50">
                                                <i class="fas fa-times-circle mr-2"></i>Set Kadaluarsa
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.library-cards.update-status', $card->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="lost">
                                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-yellow-700 hover:bg-yellow-50 flex items-center transition-colors border-t border-gray-50">
                                                <i class="fas fa-exclamation-circle mr-2"></i>Laporkan Hilang
                                            </button>
                                        </form>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <form method="POST" action="{{ route('admin.library-cards.regenerate', $card->id) }}"
                                              onsubmit="return confirm('Terbitkan ulang kartu ini? Nomor kartu akan berubah.')">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-50 flex items-center transition-colors">
                                                <i class="fas fa-sync-alt mr-2"></i>Terbitkan Ulang
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-id-card text-gray-400 text-3xl"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Tidak ada kartu ditemukan</p>
                                <p class="text-gray-400 text-sm mt-1">Coba ubah filter pencarian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cards->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $cards->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function toggleCardActionDropdown(btn) {
    const wrapper  = btn.closest('.card-action-wrapper');
    const dropdown = wrapper.querySelector('.card-action-dropdown');
    const isHidden = dropdown.classList.contains('hidden');

    // Close all other open dropdowns first
    document.querySelectorAll('.card-action-dropdown').forEach(d => d.classList.add('hidden'));

    if (isHidden) dropdown.classList.remove('hidden');
}

// Close when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.card-action-wrapper')) {
        document.querySelectorAll('.card-action-dropdown').forEach(d => d.classList.add('hidden'));
    }
});
</script>
@endpush