@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">

    <div class="card-friendly p-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-edit text-white"></i>
            </div>
            Edit Kategori
        </h1>
        <p class="text-gray-600 mt-1 ml-13">Perbarui informasi dan gambar banner kategori</p>
    </div>

    <div class="card-friendly p-8">
        <form action="{{ route('categories.update', $category->id) }}" method="POST"
              enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            {{-- Nama --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition
                    @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea name="description" rows="3"
                    placeholder="Deskripsi singkat kategori ini..."
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition resize-none">{{ old('description', $category->description) }}</textarea>
            </div>

            {{-- Gambar Sekarang --}}
            @if($category->image)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar Saat Ini</label>
                    <div class="relative w-full h-48 rounded-xl overflow-hidden">
                        <img src="{{ asset('storage/' . $category->image) }}"
                             alt="{{ $category->name }}"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-end p-4">
                            <span class="text-white font-semibold text-lg">{{ $category->name }}</span>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 mt-3 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 text-red-600 rounded">
                        <span class="text-sm text-red-600 font-medium">Hapus gambar ini</span>
                    </label>
                </div>
            @endif

            {{-- Upload Gambar Baru --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ $category->image ? 'Ganti Gambar' : 'Upload Gambar Banner' }}
                    <span class="text-gray-400 font-normal">(opsional, maks. 2MB)</span>
                </label>

                <div id="imagePreviewEdit"
                     class="relative w-full h-40 rounded-xl overflow-hidden mb-3 border-2 border-dashed border-gray-300 bg-gray-50 hidden">
                    <img id="previewImgEdit" src="" alt="Preview"
                         class="w-full h-full object-cover">
                    <button type="button" onclick="clearImageEdit()"
                        class="absolute top-2 right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full text-xs flex items-center justify-center shadow">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <label for="imageInputEdit"
                    class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-gray-300 hover:border-purple-400 hover:bg-purple-50 cursor-pointer transition-all"
                    id="dropZoneEdit">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-500">Klik atau drag gambar ke sini</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP · Maks 2MB</p>
                    <input id="imageInputEdit" type="file" name="image"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           class="hidden" onchange="previewImageEdit(this)">
                </label>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('categories.index') }}"
                    class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition text-center">
                    Batal
                </a>
                <button type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-xl shadow-md transition">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewImageEdit(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImgEdit').src = e.target.result;
            document.getElementById('imagePreviewEdit').classList.remove('hidden');
            document.getElementById('dropZoneEdit').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearImageEdit() {
    document.getElementById('imageInputEdit').value = '';
    document.getElementById('imagePreviewEdit').classList.add('hidden');
    document.getElementById('dropZoneEdit').classList.remove('hidden');
}

document.querySelectorAll('.color-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.color-swatch').forEach(s => {
            s.classList.remove('ring-2', 'ring-offset-2', 'ring-gray-800');
        });
        this.nextElementSibling.classList.add('ring-2', 'ring-offset-2', 'ring-gray-800');
    });
});
</script>
@endpush
@endsection