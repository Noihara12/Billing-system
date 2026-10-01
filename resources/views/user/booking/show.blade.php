<x-layouts.app title="Detail Booking - PS Rental">
    <div class="max-w-2xl mx-auto">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <p class="text-xs text-slate-500">Booking Code</p>
                    <p class="text-lg font-mono font-semibold text-white">{{ $booking->booking_code }}</p>
                </div>
                <x-status-badge :status="$booking->status" />
            </div>

            <dl class="grid grid-cols-2 gap-y-4 text-sm">
                <dt class="text-slate-500">Unit</dt>
                <dd class="text-slate-200">{{ $booking->unit->name }} ({{ $booking->unit->unit_code }})</dd>

                <dt class="text-slate-500">Tanggal</dt>
                <dd class="text-slate-200">{{ $booking->booking_date->format('d M Y') }}</dd>

                <dt class="text-slate-500">Jam Mulai</dt>
                <dd class="text-slate-200">{{ $booking->start_time->format('H:i') }}</dd>

                <dt class="text-slate-500">Jam Selesai</dt>
                <dd class="text-slate-200">{{ $booking->end_time->format('H:i') }}</dd>

                <dt class="text-slate-500">Durasi</dt>
                <dd class="text-slate-200">{{ round($booking->duration_minutes / 60, 1) }} Jam</dd>

                <dt class="text-slate-500">Harga</dt>
                <dd class="text-slate-200">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>

                <dt class="text-slate-500">Nama</dt>
                <dd class="text-slate-200">{{ $booking->customer_name }}</dd>

                <dt class="text-slate-500">WhatsApp</dt>
                <dd class="text-slate-200">{{ $booking->whatsapp_number }}</dd>

                @if ($booking->notes)
                    <dt class="text-slate-500">Catatan</dt>
                    <dd class="text-slate-200">{{ $booking->notes }}</dd>
                @endif
            </dl>

            <a href="{{ route('booking.index') }}" class="mt-6 inline-block rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
                Kembali ke My Booking
            </a>
        </div>
    </div>
</x-layouts.app>
