<x-layouts.admin title="Unit Management">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Manage Units</h2>
        <a href="{{ route('admin.units.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Tambah Unit
        </a>
    </div>

    <form method="GET" action="{{ route('admin.units.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input
            type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode unit..."
            class="rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 w-full sm:w-64"
        >
        <select name="status" class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <select name="category_id" class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
            Filter
        </button>
        @if (request()->hasAny(['search', 'status', 'category_id']))
            <a href="{{ route('admin.units.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-sm font-medium px-4 py-2 transition">
                Reset
            </a>
        @endif
    </form>

    @if ($units->isEmpty())
        <div class="rounded-xl border border-slate-800 bg-slate-900 px-5 py-10 text-center text-slate-500">
            Belum ada unit. Tambahkan unit pertama Anda.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
            @foreach ($units as $unit)
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-5 flex flex-col {{ $unit->is_active ? '' : 'opacity-60' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-white truncate">{{ $unit->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $unit->unit_code }}</p>
                        </div>
                        <x-status-badge :status="$unit->status" />
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="rounded-md bg-brand-500/15 text-brand-300 text-xs font-medium px-2 py-0.5">{{ $unit->category?->name ?? 'Tanpa kategori' }}</span>
                        <span class="rounded-md text-xs font-medium px-2 py-0.5 {{ $unit->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                            {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Tasmota</dt>
                            <dd class="text-slate-200 truncate">{{ $unit->tasmotaDevice?->device_name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Auto Power</dt>
                            <dd class="text-slate-200">
                                ON {{ $unit->auto_power_on ? '✓' : '✗' }} · OFF {{ $unit->auto_power_off ? '✓' : '✗' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Game ({{ $unit->games_count }})</dt>
                            <dd class="mt-1.5 flex flex-wrap gap-1.5">
                                @forelse ($unit->games->take(6) as $game)
                                    <span class="rounded-full border border-slate-700 bg-slate-800 px-2 py-0.5 text-xs text-slate-300">{{ $game->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-500">Belum ada game</span>
                                @endforelse
                                @if ($unit->games->count() > 6)
                                    <span class="rounded-full bg-brand-500/15 px-2 py-0.5 text-xs text-brand-300">+{{ $unit->games->count() - 6 }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-auto pt-4">
                        <div class="pt-4 border-t border-slate-800 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.units.edit', $unit) }}" class="text-center rounded-lg bg-slate-800 hover:bg-brand-600 text-slate-200 hover:text-white text-sm font-medium py-2 transition">Edit</a>
                            <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" onsubmit="return confirm('Hapus unit {{ $unit->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg border border-rose-500/40 text-rose-400 hover:bg-rose-500/10 text-sm font-medium py-2 transition">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        {{ $units->links() }}
    </div>
</x-layouts.admin>
