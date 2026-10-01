<x-layouts.admin title="Price List Management">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Manage Price List</h2>
        <a href="{{ route('admin.price-lists.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Tambah Price List
        </a>
    </div>

    @php
        $groups = $categories->map(fn ($category) => [
            'category' => $category,
            'title' => $category->name,
            'subtitle' => $category->description,
            'priceLists' => $category->priceLists,
        ]);

        if ($uncategorizedPriceLists->isNotEmpty()) {
            $groups->push([
                'category' => null,
                'title' => 'Tanpa Kategori',
                'subtitle' => 'Price list ini tidak muncul di billing sampai diberi kategori.',
                'priceLists' => $uncategorizedPriceLists,
            ]);
        }
    @endphp

    @if ($groups->isEmpty())
        <div class="rounded-xl border border-slate-800 bg-slate-900 px-5 py-10 text-center text-slate-500">
            Belum ada kategori. <a href="{{ route('admin.categories.create') }}" class="text-brand-400 hover:text-brand-300">Tambahkan kategori</a> terlebih dahulu.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach ($groups as $group)
                <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden flex flex-col">
                    <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-800 bg-slate-800/40">
                        <div>
                            <h3 class="text-base font-semibold text-white">{{ $group['title'] }}</h3>
                            <p class="text-xs text-slate-500">
                                {{ $group['priceLists']->count() }} paket harga
                                @if ($group['subtitle'])
                                    · {{ $group['subtitle'] }}
                                @endif
                            </p>
                        </div>
                        @if ($group['category'])
                            <a href="{{ route('admin.price-lists.create', ['category_id' => $group['category']->id]) }}" class="shrink-0 rounded-lg bg-slate-800 hover:bg-brand-600 text-slate-200 hover:text-white text-xs font-medium px-3 py-1.5 transition">
                                + Tambah Harga
                            </a>
                        @endif
                    </div>

                    @if ($group['priceLists']->isEmpty())
                        <div class="flex-1 px-5 py-8 text-center text-sm text-slate-500">
                            Belum ada harga untuk kategori ini.
                        </div>
                    @else
                        <ul class="divide-y divide-slate-800">
                            @foreach ($group['priceLists'] as $priceList)
                                <li class="flex items-center gap-4 px-5 py-3 {{ $priceList->is_active ? '' : 'opacity-60' }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-slate-200 font-medium">{{ $priceList->label }}</p>
                                        <p class="text-xs text-slate-500">{{ $priceList->duration_minutes }} menit · urutan {{ $priceList->sort_order }}</p>
                                    </div>

                                    <p class="text-slate-100 font-semibold whitespace-nowrap">Rp {{ number_format($priceList->price, 0, ',', '.') }}</p>

                                    <form method="POST" action="{{ route('admin.price-lists.toggle', $priceList) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $priceList->is_active ? 'border-emerald-500/30 bg-emerald-500/15 text-emerald-400' : 'border-slate-600 text-slate-400' }}">
                                            {{ $priceList->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.price-lists.edit', $priceList) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">Edit</a>
                                        <form method="POST" action="{{ route('admin.price-lists.destroy', $priceList) }}" onsubmit="return confirm('Hapus price list {{ $priceList->label }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
