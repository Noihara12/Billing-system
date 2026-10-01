<x-layouts.admin title="Detail Booking">
    <div class="max-w-2xl">
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <p class="text-xs text-slate-500">Booking Code</p>
                    <p class="text-lg font-mono font-semibold text-white">{{ $booking->booking_code }}</p>
                </div>
                <x-status-badge :status="$booking->status" />
            </div>

            <dl class="grid grid-cols-2 gap-y-4 text-sm">
                <dt class="text-slate-500">Customer</dt>
                <dd class="text-slate-200">{{ $booking->customer_name }} ({{ $booking->user->email ?? '-' }})</dd>

                <dt class="text-slate-500">WhatsApp</dt>
                <dd class="text-slate-200">{{ $booking->whatsapp_number }}</dd>

                <dt class="text-slate-500">Unit</dt>
                <dd class="text-slate-200">{{ $booking->unit->name }} ({{ $booking->unit->unit_code }})</dd>

                <dt class="text-slate-500">Tanggal</dt>
                <dd class="text-slate-200">{{ $booking->booking_date->format('d M Y') }}</dd>

                <dt class="text-slate-500">Waktu</dt>
                <dd class="text-slate-200">{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</dd>

                <dt class="text-slate-500">Durasi</dt>
                <dd class="text-slate-200">{{ round($booking->duration_minutes / 60, 1) }} Jam</dd>

                <dt class="text-slate-500">Harga</dt>
                <dd class="text-slate-200">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>

                <dt class="text-slate-500">Price List</dt>
                <dd class="text-slate-200">{{ $booking->priceList->label ?? '-' }}</dd>

                @if ($booking->notes)
                    <dt class="text-slate-500">Catatan</dt>
                    <dd class="text-slate-200">{{ $booking->notes }}</dd>
                @endif

                <dt class="text-slate-500">Dibuat</dt>
                <dd class="text-slate-200">{{ $booking->created_at->format('d M Y H:i') }}</dd>
            </dl>

            <div class="mt-6 flex flex-wrap gap-2">
                @if ($booking->status === \App\Models\Booking::STATUS_PENDING)
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">Confirm</button>
                    </form>
                @endif
                @if (in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_CONFIRMED]))
                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium px-4 py-2 transition">Complete</button>
                    </form>
                    <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking ini?');">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium px-4 py-2 transition">Cancel</button>
                    </form>
                @endif
                <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</x-layouts.admin>
