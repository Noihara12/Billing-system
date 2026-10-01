@php $selectedGameIds = old('game_ids', $unit->games->pluck('id')->all()); @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form.input name="name" label="Nama Unit" :value="$unit->name" required placeholder="PS 01" />
    <x-form.input name="unit_code" label="Kode Unit" :value="$unit->unit_code" required placeholder="PS-01" />

    <x-form.select
        name="category_id"
        label="Kategori"
        :options="$categories->pluck('name', 'id')"
        :value="$unit->category_id"
        placeholder="Pilih kategori (PS 4 / PS 5)"
        required
    />

    <x-form.select name="status" label="Status" :options="array_combine($statuses, $statuses)" :value="$unit->status" required />

    <x-form.select
        name="tasmota_device_id"
        label="Perangkat Tasmota"
        :options="$tasmotaDevices->pluck('device_name', 'id')"
        :value="$unit->tasmota_device_id"
        placeholder="Tidak terhubung"
    />
</div>

<div class="mt-5">
    <x-form.textarea name="description" label="Deskripsi" :value="$unit->description" rows="3" />
</div>

<div class="mt-5 flex flex-wrap gap-6">
    <x-form.checkbox name="is_active" label="Unit Aktif" :checked="$unit->is_active" />
    <x-form.checkbox name="auto_power_on" label="Automatic Power ON" :checked="$unit->auto_power_on" />
    <x-form.checkbox name="auto_power_off" label="Automatic Power OFF" :checked="$unit->auto_power_off" />
</div>

<div class="mt-5">
    <label for="game_ids" class="block text-sm font-medium text-slate-300 mb-1.5">Game Tersedia</label>
    @if ($games->isEmpty())
        <p class="text-sm text-slate-500">Belum ada data game. Tambahkan lewat menu Game Management.</p>
    @else
        <select
            id="game_ids" name="game_ids[]" multiple
            data-multiselect data-placeholder="Pilih game..." data-search-placeholder="Cari game..."
            class="w-full h-40 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
        >
            @foreach ($games as $game)
                <option value="{{ $game->id }}" @selected(in_array($game->id, $selectedGameIds))>{{ $game->name }}</option>
            @endforeach
        </select>
    @endif
    @error('game_ids')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    @error('game_ids.*')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
</div>

<div class="mt-8 flex gap-3">
    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 transition">
        Simpan
    </button>
    <a href="{{ route('admin.units.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-5 py-2.5 transition">
        Batal
    </a>
</div>
