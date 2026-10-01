<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form.input name="name" label="Nama Kategori" :value="$category->name" required placeholder="PS 5" />
    <x-form.input name="sort_order" label="Urutan Tampil" type="number" :value="$category->sort_order" placeholder="0" />
</div>

<div class="mt-5">
    <x-form.textarea name="description" label="Deskripsi" :value="$category->description" rows="3" />
</div>

<div class="mt-8 flex gap-3">
    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 transition">
        Simpan
    </button>
    <a href="{{ route('admin.categories.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-5 py-2.5 transition">
        Batal
    </a>
</div>
