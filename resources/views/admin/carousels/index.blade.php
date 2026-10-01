<x-layouts.admin title="Carousel Management">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Manage Carousel</h2>
        <a href="{{ route('admin.carousels.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Tambah Carousel
        </a>
    </div>

    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Gambar</th>
                    <th class="px-5 py-3 font-medium">Judul</th>
                    <th class="px-5 py-3 font-medium">Deskripsi</th>
                    <th class="px-5 py-3 font-medium">Urutan</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($carousels as $carousel)
                    <tr>
                        <td class="px-5 py-3">
                            @if ($carousel->image_path)
                                <img src="{{ Storage::url($carousel->image_path) }}" alt="{{ $carousel->title }}" class="rounded w-12 h-12 object-cover">
                            @else
                                <div class="rounded w-12 h-12 bg-slate-800 flex items-center justify-center text-xs text-slate-500">No Image</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-200 font-medium">{{ $carousel->title }}</td>
                        <td class="px-5 py-3 text-slate-400 truncate max-w-xs">{{ $carousel->description }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $carousel->sort_order }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.carousels.toggle', $carousel) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs {{ $carousel->is_active ? 'text-emerald-400' : 'text-slate-500' }} hover:underline">
                                    {{ $carousel->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.carousels.edit', $carousel) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.carousels.destroy', $carousel) }}" onsubmit="return confirm('Hapus carousel {{ $carousel->title }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-6 text-center text-slate-500">Belum ada carousel. Tambahkan carousel pertama Anda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $carousels->links() }}
    </div>
</x-layouts.admin>
