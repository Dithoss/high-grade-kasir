@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="card-friendly p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-900 rounded-xl flex items-center justify-center">
                        <i class="fas fa-sliders-h text-white text-xl"></i>
                    </div>
                    Pengaturan Sistem
                </h1>
                <p class="text-gray-600 mt-2">Kelola aturan dan konfigurasi sistem perpustakaan secara dinamis.</p>
            </div>
            <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
                <i class="fas fa-info-circle text-amber-500"></i>
                <span>Perubahan langsung berlaku setelah disimpan.</span>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500 text-lg"></i>
            <span class="text-green-800 font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Sidebar: Group Navigation --}}
        <div class="lg:w-64 flex-shrink-0">
            <div class="card-friendly p-4 sticky top-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-2">Kategori</p>
                <nav class="space-y-1">
                    @php
                        $colorMap = [
                            'blue'   => 'bg-blue-100 text-blue-700 border-blue-300',
                            'red'    => 'bg-red-100 text-red-700 border-red-300',
                            'purple' => 'bg-purple-100 text-purple-700 border-purple-300',
                            'amber'  => 'bg-amber-100 text-amber-700 border-amber-300',
                            'green'  => 'bg-green-100 text-green-700 border-green-300',
                            'gray'   => 'bg-gray-100 text-gray-700 border-gray-300',
                        ];
                    @endphp

                    @foreach($groups as $groupKey => $group)
                        @php
                            $isActive = $activeGroup === $groupKey;
                            $colorClass = $colorMap[$group['color']];
                        @endphp
                        <a
                            href="{{ route('settings.index', ['group' => $groupKey]) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-sm font-medium
                                {{ $isActive
                                    ? $colorClass . ' border font-semibold shadow-sm'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                }}"
                        >
                            <i class="{{ $group['icon'] }} w-4 text-center"></i>
                            <span>{{ $group['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Main: Settings Form --}}
        <div class="flex-1">
            <div class="card-friendly overflow-hidden">

                {{-- Group Header --}}
                @php
                    $currentGroup = $groups[$activeGroup];
                    $headerColor = [
                        'blue'   => 'from-blue-500 to-blue-700',
                        'red'    => 'from-red-500 to-red-700',
                        'purple' => 'from-purple-500 to-purple-700',
                        'amber'  => 'from-amber-500 to-amber-600',
                        'green'  => 'from-green-500 to-green-700',
                        'gray'   => 'from-gray-600 to-gray-800',
                    ][$currentGroup['color']];
                @endphp

                <div class="bg-gradient-to-r {{ $headerColor }} px-6 py-5">
                    <h2 class="text-xl font-bold text-white flex items-center gap-3">
                        <i class="{{ $currentGroup['icon'] }}"></i>
                        {{ $currentGroup['label'] }}
                    </h2>
                    <p class="text-white/75 text-sm mt-1">
                        {{ $fields->count() }} pengaturan tersedia di kategori ini.
                    </p>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('settings.update') }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="group" value="{{ $activeGroup }}">

                    @foreach($fields as $field)
                        <div class="border border-gray-100 rounded-xl p-5 hover:border-gray-200 transition-colors bg-white">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                <div class="flex-1">
                                    <label
                                        for="{{ str_replace('.', '_', $field['key']) }}"
                                        class="block text-sm font-semibold text-gray-800 mb-1"
                                    >
                                        {{ $field['label'] }}
                                    </label>
                                    @if(!empty($field['description']))
                                        <p class="text-xs text-gray-500 mb-3">{{ $field['description'] }}</p>
                                    @endif

                                    {{-- INPUT: boolean --}}
                                    @if($field['type'] === 'boolean')
                                        <label class="inline-flex items-center cursor-pointer gap-3">
                                            <div class="relative">
                                                <input
                                                    type="checkbox"
                                                    id="{{ str_replace('.', '_', $field['key']) }}"
                                                    name="{{ $field['key'] }}"
                                                    class="sr-only peer"
                                                    {{ $field['value'] == '1' ? 'checked' : '' }}
                                                >
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </div>
                                            <span class="text-sm text-gray-700 peer-checked:text-blue-700 font-medium" id="toggle-label-{{ str_replace('.', '_', $field['key']) }}">
                                                {{ $field['value'] == '1' ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </label>

                                    {{-- INPUT: textarea --}}
                                    @elseif($field['type'] === 'textarea')
                                        <textarea
                                            id="{{ str_replace('.', '_', $field['key']) }}"
                                            name="{{ $field['key'] }}"
                                            rows="3"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none text-sm resize-none"
                                        >{{ $field['value'] }}</textarea>

                                    {{-- INPUT: number --}}
                                    @elseif($field['type'] === 'number')
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="number"
                                                id="{{ str_replace('.', '_', $field['key']) }}"
                                                name="{{ $field['key'] }}"
                                                value="{{ $field['value'] }}"
                                                min="{{ $field['min'] ?? 0 }}"
                                                @isset($field['max']) max="{{ $field['max'] }}" @endisset
                                                class="w-40 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none text-sm font-mono"
                                            >
                                            @if(!empty($field['min']) || isset($field['max']))
                                                <span class="text-xs text-gray-400">
                                                    {{ isset($field['min']) ? 'min: '.$field['min'] : '' }}
                                                    {{ isset($field['max']) ? ' – maks: '.$field['max'] : '' }}
                                                </span>
                                            @endif
                                        </div>

                                    {{-- INPUT: text / email / url --}}
                                    @else
                                        <input
                                            type="{{ $field['type'] }}"
                                            id="{{ str_replace('.', '_', $field['key']) }}"
                                            name="{{ $field['key'] }}"
                                            value="{{ $field['value'] }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none text-sm"
                                        >
                                    @endif
                                </div>

                                {{-- Key Badge --}}
                                <div class="flex-shrink-0">
                                    <code class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-lg font-mono">
                                        {{ $field['key'] }}
                                    </code>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Submit --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Perubahan akan di-log di Audit Log.
                        </p>
                        <button
                            type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2"
                        >
                            <i class="fas fa-save"></i>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Update toggle label text saat toggle berubah
    document.querySelectorAll('input[type="checkbox"].sr-only').forEach(checkbox => {
        const key = checkbox.id;
        const label = document.getElementById('toggle-label-' + key);
        if (!label) return;
        checkbox.addEventListener('change', () => {
            label.textContent = checkbox.checked ? 'Aktif' : 'Nonaktif';
        });
    });
</script>
@endsection