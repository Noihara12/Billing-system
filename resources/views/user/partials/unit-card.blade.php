@php($booking = $unit->upcomingBooking)
<div class="rounded-xl border border-slate-800 bg-slate-900 p-5" data-unit-id="{{ $unit->id }}">
    <div class="flex items-start justify-between mb-3">
        <div>
            <h3 class="font-semibold text-white">{{ $unit->name }}</h3>
            <p class="text-xs text-slate-500">{{ $unit->unit_code }}</p>
        </div>
        <x-status-badge :status="$unit->status" />
    </div>

    @if ($unit->status === \App\Models\Unit::STATUS_PLAYING && $unit->activeRentalSession)
        <p class="text-sm text-slate-400 mb-1">
            Sisa waktu:
            <span class="text-slate-200 font-mono" data-remaining-end="{{ $unit->activeRentalSession->rental_end_time->toIso8601String() }}">-</span>
        </p>
    @elseif ($unit->status === \App\Models\Unit::STATUS_BOOKED && $booking)
        <p class="text-sm text-slate-400">Mulai: <span class="text-slate-200">{{ $booking->start_time->format('H:i') }}</span></p>
        <p class="text-sm text-slate-400">Durasi: <span class="text-slate-200">{{ round($booking->duration_minutes / 60, 1) }} Jam</span></p>
    @else
        <p class="text-sm text-slate-400 mb-1">Sisa waktu: <span class="text-slate-200 font-mono">-</span></p>
    @endif

    @if ($unit->games->isNotEmpty())
        <p class="text-xs text-slate-500 mt-3">
            Game: {{ $unit->games->pluck('name')->join(', ') }}
        </p>
    @endif

    @php($isBookable = ! in_array($unit->status, [\App\Models\Unit::STATUS_MAINTENANCE, \App\Models\Unit::STATUS_OFFLINE], true))

    @if ($isBookable)
        {{-- Unit yang sedang dipakai tetap bisa dibooking untuk jam lain. --}}
        <a
            href="{{ route('booking.create', ['unit_id' => $unit->id]) }}"
            class="mt-4 inline-block w-full text-center rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium py-2 transition"
        >
            {{ $unit->status === \App\Models\Unit::STATUS_AVAILABLE ? 'Booking' : 'Booking untuk Jam Lain' }}
        </a>
    @else
        <span class="mt-4 block w-full text-center rounded-lg bg-slate-800 text-slate-500 text-sm font-medium py-2 cursor-not-allowed">
            Sedang Tidak Tersedia
        </span>
    @endif
</div>
