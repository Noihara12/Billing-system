<x-layouts.admin title="Game Management">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Manage Games</h2>
        <a href="{{ route('admin.games.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Tambah Game
        </a>
    </div>

    <form method="GET" action="{{ route('admin.games.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input
            type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama game..."
            class="rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 w-full sm:w-64"
        >
        <select name="status" class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
            Filter
        </button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.games.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-sm font-medium px-4 py-2 transition">
                Reset
            </a>
        @endif
    </form>

    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Game</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Jumlah Unit</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($games as $game)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="text-slate-200 font-medium">{{ $game->name }}</p>
                            @if ($game->description)
                                <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($game->description, 60) }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs {{ $game->is_active ? 'text-emerald-400' : 'text-slate-500' }}">
                                {{ $game->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $game->units_count }} unit</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.games.edit', $game) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.games.destroy', $game) }}" onsubmit="return confirm('Hapus game {{ $game->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-slate-500">Belum ada game. Tambahkan game pertama Anda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $games->links() }}
    </div>
</x-layouts.admin>
