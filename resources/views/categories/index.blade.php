@extends('layouts.app')

@section('title', 'Daftar Kategori')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="card-friendly p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tags text-white text-xl"></i>
                    </div>
                    Daftar Kategori
                </h1>
                <p class="text-gray-600 mt-2">Kelola kategori dan gambar banner perpustakaan</p>
            </div>

            @role('admin')
            <div class="flex gap-3">
                <button id="massDeleteBtn" onclick="massDeleteSelected()"
                    class="hidden items-center gap-2 px-5 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 font-semibold shadow-md transition-all">
                    <i class="fas fa-trash"></i>
                    Hapus (<span id="selectedCount">0</span>)
                </button>
                <button onclick="openCreateModal()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-plus"></i>
                    Tambah Kategori
                </button>
            </div>
            @endrole
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="card-friendly p-5 text-center">
            <div class="text-3xl font-bold text-purple-600">{{ $categories->total() }}</div>
            <div class="text-sm text-gray-600 mt-1">Total Kategori</div>
        </div>
        <div class="card-friendly p-5 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ $categories->currentPage() }}/{{ $categories->lastPage() }}</div>
            <div class="text-sm text-gray-600 mt-1">Halaman</div>
        </div>
        <div class="card-friendly p-5 text-center">
            <div class="text-3xl font-bold text-green-600">{{ $categories->count() }}</div>
            <div class="text-sm text-gray-600 mt-1">Di Halaman Ini</div>
        </div>
    </div>

    {{-- Category Grid (Banner style) --}}
    @if($categories->count() > 0)
        @role('admin')
        <div class="flex items-center gap-3 px-1">
            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="w-4 h-4 rounded text-purple-600">
            <label for="selectAll" class="text-sm text-gray-600 cursor-pointer">Pilih Semua</label>
        </div>
        @endrole

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($categories as $category)
                @php
                    $color = $category->color ?: 'from-blue-500 to-indigo-600';
                @endphp

                <div class="group relative card-friendly overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">

                    {{-- Checkbox (admin only) --}}
                    @role('admin')
                    <div class="absolute top-3 left-3 z-20">
                        <input type="checkbox"
                            class="category-checkbox w-5 h-5 rounded-md text-purple-600 shadow-md border-2 border-white"
                            value="{{ $category->id }}"
                            onclick="updateSelectedCount()">
                    </div>
                    @endrole

                    {{-- Banner Image / Gradient --}}
                    <div class="relative h-40 overflow-hidden">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}"
                                 alt="{{ $category->name }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                        @else
                            <div class="w-full h-full bg-gradient-to-br {{ $color }} flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-5xl font-black text-white/20 select-none">
                                        {{ strtoupper(substr($category->name, 0, 2)) }}
                                    </div>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                        @endif

                        {{-- Category Name overlay --}}
                        <div class="absolute bottom-3 left-4 right-4">
                            <h3 class="text-white font-bold text-lg leading-tight drop-shadow-lg">
                                {{ $category->name }}
                            </h3>
                            @if($category->books_count ?? null)
                                <span class="text-white/80 text-xs">{{ $category->books_count }} buku</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-4">
                        @if($category->description)
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $category->description }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic mb-3">Tidak ada deskripsi</p>
                        @endif

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span><i class="fas fa-calendar mr-1"></i>{{ $category->created_at->format('d M Y') }}</span>
                            @if(!$category->image)
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">Tanpa Gambar</span>
                            @else
                                <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded-full"><i class="fas fa-image mr-1"></i>Bergambar</span>
                            @endif
                        </div>

                        @role('admin')
                        <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('categories.edit', $category->id) }}"
                               class="flex-1 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg text-center transition-all">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus kategori {{ $category->name }}?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition-all">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        @endrole
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="card-friendly p-5">
            {{ $categories->links() }}
        </div>
    @else
        <div class="card-friendly p-16 text-center">
            <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-tags text-4xl text-purple-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Kategori</h3>
            <p class="text-gray-600 mb-6">Buat kategori pertama dengan gambar banner menarik!</p>
            @role('admin')
            <button onclick="openCreateModal()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-semibold shadow-md">
                <i class="fas fa-plus"></i> Tambah Kategori
            </button>
            @endrole
        </div>
    @endif

</div>

{{-- Mass Delete Form --}}
<form id="massDeleteForm" action="{{ route('categories.mass-delete') }}" method="POST" class="hidden">
    @csrf @method('DELETE')
    <input type="hidden" name="ids" id="selectedIds">
</form>

{{-- ═══════════════════════════════════════════════════════════════
    CREATE MODAL
═══════════════════════════════════════════════════════════════ --}}
<div id="createCategoryModal"
     class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 py-8">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">

        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Tambah Kategori</h2>
                    <p class="text-purple-100 text-sm mt-1">Lengkapi dengan gambar banner agar menarik</p>
                </div>
                <button onclick="closeCreateModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('categories.store') }}" method="POST"
              enctype="multipart/form-data" class="px-8 py-6 space-y-5">
            @csrf

            {{-- Nama --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                    placeholder="Contoh: Teknologi, Sejarah, Fiksi"
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition">
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea name="description" rows="2"
                    placeholder="Deskripsi singkat kategori ini..."
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition resize-none"></textarea>
            </div>

            {{-- Image Upload --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Gambar Banner <span class="text-gray-400 font-normal">(opsional, maks. 2MB)</span>
                </label>

                {{-- Preview Area --}}
                <div id="imagePreviewCreate"
                     class="relative w-full h-40 rounded-xl overflow-hidden mb-3 border-2 border-dashed border-gray-300 bg-gray-50 hidden">
                    <img id="previewImgCreate" src="" alt="Preview"
                         class="w-full h-full object-cover">
                    <button type="button" onclick="clearImageCreate()"
                        class="absolute top-2 right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full text-xs flex items-center justify-center shadow">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <label for="imageInputCreate"
                    class="flex flex-col items-center justify-center w-full h-32 rounded-xl border-2 border-dashed border-gray-300 hover:border-purple-400 hover:bg-purple-50 cursor-pointer transition-all"
                    id="dropZoneCreate">
                    <div class="text-center">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Klik atau drag gambar ke sini</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP · Maks 2MB</p>
                    </div>
                    <input id="imageInputCreate" type="file" name="image"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           class="hidden" onchange="previewImageCreate(this)">
                </label>
            </div>

            {{-- Color Fallback --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Warna Gradient <span class="text-gray-400 font-normal">(dipakai jika tanpa gambar)</span>
                </label>
                <div class="grid grid-cols-4 gap-2" id="colorPickerCreate">
                    @php
                        $colors = [
                            'from-blue-500 to-indigo-600'   => ['bg-blue-500', 'Biru'],
                            'from-emerald-500 to-teal-600'  => ['bg-emerald-500', 'Hijau'],
                            'from-rose-500 to-pink-600'     => ['bg-rose-500', 'Merah Muda'],
                            'from-amber-500 to-orange-600'  => ['bg-amber-500', 'Oranye'],
                            'from-violet-500 to-purple-600' => ['bg-violet-500', 'Ungu'],
                            'from-cyan-500 to-sky-600'      => ['bg-cyan-500', 'Biru Muda'],
                            'from-red-500 to-rose-600'      => ['bg-red-500', 'Merah'],
                            'from-lime-500 to-green-600'    => ['bg-lime-500', 'Hijau Muda'],
                        ];
                    @endphp
                    @foreach($colors as $value => [$bg, $label])
                        <label class="cursor-pointer" title="{{ $label }}">
                            <input type="radio" name="color" value="{{ $value }}" class="sr-only color-radio"
                                   {{ $loop->first ? 'checked' : '' }}>
                            <div class="w-full h-10 rounded-lg bg-gradient-to-br {{ $value }} color-swatch
                                {{ $loop->first ? 'ring-2 ring-offset-2 ring-gray-800' : '' }}
                                hover:scale-105 transition-transform">
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-md transition">
                    <i class="fas fa-save mr-2"></i>Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ── Modal ──────────────────────────────────────────────────────────────────
function openCreateModal() {
    document.getElementById('createCategoryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeCreateModal() {
    document.getElementById('createCategoryModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// ── Image Preview ──────────────────────────────────────────────────────────
function previewImageCreate(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImgCreate').src = e.target.result;
            document.getElementById('imagePreviewCreate').classList.remove('hidden');
            document.getElementById('dropZoneCreate').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearImageCreate() {
    document.getElementById('imageInputCreate').value = '';
    document.getElementById('imagePreviewCreate').classList.add('hidden');
    document.getElementById('dropZoneCreate').classList.remove('hidden');
}

// ── Color Picker ───────────────────────────────────────────────────────────
document.querySelectorAll('.color-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.color-swatch').forEach(s => {
            s.classList.remove('ring-2', 'ring-offset-2', 'ring-gray-800');
        });
        this.nextElementSibling.classList.add('ring-2', 'ring-offset-2', 'ring-gray-800');
    });
});

// ── Mass Delete ────────────────────────────────────────────────────────────
function toggleSelectAll() {
    const all = document.getElementById('selectAll').checked;
    document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = all);
    updateSelectedCount();
}
function updateSelectedCount() {
    const checked = document.querySelectorAll('.category-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = count;
    const btn = document.getElementById('massDeleteBtn');
    if (count > 0) { btn.classList.remove('hidden'); btn.classList.add('inline-flex'); }
    else { btn.classList.add('hidden'); btn.classList.remove('inline-flex'); }
    const all = document.querySelectorAll('.category-checkbox');
    const selectAll = document.getElementById('selectAll');
    if (selectAll) selectAll.checked = count === all.length && count > 0;
}
function massDeleteSelected() {
    const ids = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return;
    if (confirm(`Yakin hapus ${ids.length} kategori?`)) {
        document.getElementById('selectedIds').value = JSON.stringify(ids);
        document.getElementById('massDeleteForm').submit();
    }
}

// Tutup modal saat klik backdrop
document.getElementById('createCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCreateModal(); });
</script>
@endpush
@endsection