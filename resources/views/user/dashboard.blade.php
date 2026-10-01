<x-layouts.app title="Beranda - Bagoes Cafe">
    <div data-dashboard-root data-endpoint="{{ route('dashboard.data') }}">
        <div class="mb-8">
            <div class="relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-800" data-carousel>
                @if ($carousels->isEmpty())
                    <div class="h-40 flex items-center justify-center text-slate-500 text-sm">
                        Belum ada banner promosi aktif
                    </div>
                @else
                    @foreach ($carousels as $carousel)
                        <div class="{{ $loop->first ? '' : 'hidden' }}" data-carousel-slide>
                            @if ($carousel->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($carousel->image_path) }}" alt="{{ $carousel->title }}" class="w-full h-56 object-cover">
                            @else
                                <div class="w-full h-56 bg-slate-800 flex items-center justify-center text-sm text-slate-500">Tanpa gambar</div>
                            @endif
                            <div class="p-4">
                                <h3 class="font-semibold text-white">{{ $carousel->title }}</h3>
                                @if ($carousel->description)
                                    <p class="text-sm text-slate-400 mt-1">{{ $carousel->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if ($carousels->count() > 1)
                        <button type="button" data-carousel-prev class="absolute left-2 top-1/4 -translate-y-1/2 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center">‹</button>
                        <button type="button" data-carousel-next class="absolute right-2 top-1/4 -translate-y-1/2 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center">›</button>
                        <div class="absolute bottom-20 left-1/2 -translate-x-1/2 flex gap-1.5">
                            @foreach ($carousels as $carousel)
                                <button type="button" data-carousel-dot class="w-2 h-2 rounded-full {{ $loop->first ? 'bg-white' : 'bg-white/40' }}"></button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Daftar Unit PlayStation</h2>
            <span class="text-xs text-slate-500">Update otomatis setiap 10 detik</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" data-units-grid>
            @forelse ($units as $unit)
                @include('user.partials.unit-card', ['unit' => $unit])
            @empty
                <p class="text-slate-500 text-sm col-span-full" data-empty-row>Belum ada unit yang tersedia.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
