@php $selectedUnitIds = old('unit_ids', $game->units->pluck('id')->all()); @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form.input name="name" label="Nama Game" :value="$game->name" required placeholder="Tekken 8" />
    <x-form.checkbox name="is_active" label="Game Aktif" :checked="$game->is_active" />
</div>

<div class="mt-5">
    <x-form.textarea name="description" label="Deskripsi" :value="$game->description" rows="3" />
</div>

<div class="mt-5">
    <p class="block text-sm font-medium text-slate-300 mb-2">Tersedia di Unit</p>
    @if ($units->isEmpty())
        <p class="text-sm text-slate-500">Belum ada data unit. Tambahkan lewat menu Unit Management.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach ($units as $unit)
                <label class="flex items-center gap-2 text-sm text-slate-300">
                    <input
                        type="checkbox" name="unit_ids[]" value="{{ $unit->id }}"
                        @checked(in_array($unit->id, $selectedUnitIds))
                        class="rounded border-slate-700 bg-slate-800 text-brand-500 focus:ring-brand-500"
                    >
                    {{ $unit->name }}
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="mt-8 flex gap-3">
    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 transition">
        Simpan
    </button>
    <a href="{{ route('admin.games.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-5 py-2.5 transition">
        Batal
    </a>
</div>
