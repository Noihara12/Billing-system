<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form.select
        name="category_id"
        label="Kategori"
        :options="$categories->pluck('name', 'id')"
        :value="$priceList->category_id"
        placeholder="Pilih kategori"
        required
    />
    <x-form.input name="label" label="Label" :value="$priceList->label" required placeholder="1 Jam" />
    <x-form.input name="duration_minutes" label="Durasi (menit)" type="number" :value="$priceList->duration_minutes" required placeholder="60" />
    <x-form.input name="price" label="Harga (Rp)" type="number" :value="$priceList->price" required placeholder="10000" />
    <x-form.input name="sort_order" label="Urutan Tampil" type="number" :value="$priceList->sort_order" placeholder="0" />
</div>

<div class="mt-5">
    <x-form.checkbox name="is_active" label="Price List Aktif" :checked="$priceList->is_active" />
</div>

<div class="mt-8 flex gap-3">
    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 transition">
        Simpan
    </button>
    <a href="{{ route('admin.price-lists.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-5 py-2.5 transition">
        Batal
    </a>
</div>
