<x-layouts.admin title="Category Management">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Manage Kategori</h2>
        <a href="{{ route('admin.categories.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Tambah Kategori
        </a>
    </div>

    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Nama</th>
                    <th class="px-5 py-3 font-medium">Deskripsi</th>
                    <th class="px-5 py-3 font-medium">Unit</th>
                    <th class="px-5 py-3 font-medium">Price List</th>
                    <th class="px-5 py-3 font-medium">Urutan</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-5 py-3 text-slate-200 font-medium">{{ $category->name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $category->description ?: '-' }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $category->units_count }} unit</td>
                        <td class="px-5 py-3 text-slate-400">{{ $category->price_lists_count }} harga</td>
                        <td class="px-5 py-3 text-slate-400">{{ $category->sort_order }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ $category->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-6 text-center text-slate-500">Belum ada kategori. Tambahkan kategori pertama Anda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
</x-layouts.admin>
