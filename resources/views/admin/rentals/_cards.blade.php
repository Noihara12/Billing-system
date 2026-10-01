@php
    $playingCount = $units->filter(fn ($unit) => $unit->activeRentalSession)->count();
@endphp

<p class="text-sm text-slate-400 mb-4">
    <span class="text-brand-300 font-semibold">{{ $playingCount }}</span> unit sedang bermain dari
    <span class="text-slate-200 font-semibold">{{ $units->count() }}</span> unit.
</p>

@if ($units->isEmpty())
    <div class="rounded-xl border border-slate-800 bg-slate-900 px-5 py-10 text-center text-slate-500" data-empty-row>
        Belum ada unit aktif.
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
        @foreach ($units as $unit)
            @php
                $rental = $unit->activeRentalSession;
                $totalSeconds = $rental ? max(1, $rental->rental_start_time->diffInSeconds($rental->rental_end_time)) : 1;
                $progress = $rental ? min(100, max(0, $rental->rental_start_time->diffInSeconds(now()) / $totalSeconds * 100)) : 0;
            @endphp

            <div class="rounded-xl border {{ $rental ? 'border-brand-500/40 bg-brand-500/5' : 'border-slate-800 bg-slate-900' }} p-5 flex flex-col" data-unit-id="{{ $unit->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-white">{{ $unit->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $unit->unit_code }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <x-status-badge :status="$rental ? 'PLAYING' : $unit->status" />
                        @if ($unit->category)
                            <span class="rounded-md bg-slate-800 text-slate-300 text-xs font-medium px-2 py-0.5">{{ $unit->category->name }}</span>
                        @endif
                    </div>
                </div>

                @if ($rental)
                    <div class="mt-4 text-white">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Sisa Waktu</p>
                        <p class="text-3xl font-mono font-semibold" data-remaining-end="{{ $rental->rental_end_time->toIso8601String() }}">-</p>
                    </div>

                    <div class="mt-3 h-1.5 rounded-full bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ round($progress, 1) }}%"></div>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div class="col-span-2">
                            <dt class="text-xs text-slate-500">Customer</dt>
                            <dd class="text-slate-200 truncate">{{ $rental->customer_name ?? $rental->user?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Mulai</dt>
                            <dd class="text-slate-200">{{ $rental->rental_start_time->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Selesai</dt>
                            <dd class="text-slate-200">{{ $rental->rental_end_time->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Durasi</dt>
                            <dd class="text-slate-200">{{ round($rental->duration_minutes / 60, 1) }} Jam</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Harga</dt>
                            <dd class="text-slate-200">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 pt-4 border-t border-slate-800">
                        <form method="POST" action="{{ route('admin.rentals.complete', $rental) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium py-2.5 transition">
                                Stop Rental
                            </button>
                        </form>
                        {{-- Pembatalan jarang dipakai (hanya untuk salah input), jadi dibuat sebagai aksi kecil. --}}
                        <form method="POST" action="{{ route('admin.rentals.cancel', $rental) }}" class="mt-2 text-center" onsubmit="return confirm('Batalkan rental unit {{ $unit->name }}? Rental ini tidak akan dihitung sebagai pendapatan.');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-slate-500 hover:text-rose-400 transition">Batalkan rental</button>
                        </form>
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center py-8">
                        <p class="text-sm text-slate-500">Tidak ada rental berjalan</p>
                    </div>

                    @if ($unit->status === \App\Models\Unit::STATUS_AVAILABLE)
                        <a href="{{ route('admin.billing.create', ['unit_id' => $unit->id]) }}" class="block w-full text-center rounded-lg bg-slate-800 hover:bg-brand-600 text-slate-200 hover:text-white text-sm font-medium py-2 transition">
                            Start Rental
                        </a>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
@endif
