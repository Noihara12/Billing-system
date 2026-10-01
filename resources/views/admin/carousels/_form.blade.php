<div>
    <x-form.input name="title" label="Judul" :value="$carousel->title" required />
    <x-form.textarea name="description" label="Deskripsi" :value="$carousel->description" rows="3" />

    @if ($carousel->exists && $carousel->image_path)
        <div class="mt-4">
            <p class="text-sm font-medium text-slate-300 mb-2">Gambar Saat Ini</p>
            <img src="{{ Storage::url($carousel->image_path) }}" alt="{{ $carousel->title }}" class="rounded-lg w-full max-w-md h-48 object-cover">
        </div>
    @endif

    <div class="mt-4">
        <label for="image" class="block text-sm font-medium text-slate-300 mb-1.5">
            Gambar
        </label>
        <div class="mb-2 rounded-lg border border-slate-800 bg-slate-950/40 px-3 py-2.5 text-xs text-slate-400 space-y-1">
            <p><span class="text-slate-300 font-medium">Ukuran ideal:</span> 1600 × 320 piksel (rasio 5:1), minimal 1200 × 240 piksel.</p>
            <p><span class="text-slate-300 font-medium">Format:</span> JPG, PNG, atau WebP. Maksimal 2 MB.</p>
            <p>Banner ditampilkan selebar layar dengan tinggi tetap. Gambar yang rasionya berbeda akan dipotong otomatis di bagian atas dan bawah, jadi letakkan tulisan atau objek penting di bagian tengah.</p>
        </div>
        <input
            type="file" id="image" name="image" accept="image/*"
            class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-500"
        >
        @error('image')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>

    <x-form.input name="sort_order" label="Urutan Tampil" type="number" :value="$carousel->sort_order" placeholder="0" />

    <div class="mt-5">
        <x-form.checkbox name="is_active" label="Carousel Aktif" :checked="$carousel->is_active" />
    </div>

    <div class="mt-8 flex gap-3">
        <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 transition">
            Simpan
        </button>
        <a href="{{ route('admin.carousels.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-5 py-2.5 transition">
            Batal
        </a>
    </div>
</div>
