<x-layouts.app title="Detail Booking - PS Rental">
    <div class="max-w-xl mx-auto">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Kode Booking</p>
                    <p class="text-2xl font-mono font-semibold text-white">{{ $booking->booking_code }}</p>
                </div>
                <x-status-badge :status="$booking->status" />
            </div>

            <p class="mt-3 text-sm text-slate-400">Simpan kode ini untuk mengecek booking Anda kapan saja di halaman Cek Booking.</p>

            <dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Unit</dt>
                    <dd class="text-slate-200">{{ $booking->unit->name }} ({{ $booking->unit->unit_code }})</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Kategori</dt>
                    <dd class="text-slate-200">{{ $booking->unit->category?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Tanggal</dt>
                    <dd class="text-slate-200">{{ $booking->booking_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Jam</dt>
                    <dd class="text-slate-200">{{ $booking->start_time->format('H:i') }} – {{ $booking->end_time->format('H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Durasi</dt>
                    <dd class="text-slate-200">{{ round($booking->duration_minutes / 60, 1) }} Jam</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Total Harga</dt>
                    <dd class="text-slate-200 font-semibold">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Nama</dt>
                    <dd class="text-slate-200">{{ $booking->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">WhatsApp</dt>
                    <dd class="text-slate-200">{{ $booking->whatsapp_number }}</dd>
                </div>
                @if ($booking->notes)
                    <div class="col-span-2">
                        <dt class="text-xs text-slate-500">Catatan</dt>
                        <dd class="text-slate-200">{{ $booking->notes }}</dd>
                    </div>
                @endif
            </dl>

            @if (in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_CONFIRMED], true))
                <div class="mt-6 pt-5 border-t border-slate-800">
                    <p class="text-sm text-slate-400">
                        {{ $booking->status === \App\Models\Booking::STATUS_PENDING
                            ? 'Booking Anda menunggu konfirmasi admin. Datang 15 menit sebelum jam mulai.'
                            : 'Booking Anda sudah dikonfirmasi. Datang 15 menit sebelum jam mulai.' }}
                    </p>
                    <form method="POST" action="{{ route('booking.lookup.cancel', $booking) }}" class="mt-4" onsubmit="return confirm('Batalkan booking {{ $booking->booking_code }}?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg border border-rose-500/40 text-rose-400 hover:bg-rose-500/10 text-sm font-medium px-4 py-2 transition">
                            Batalkan Booking
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="mt-4 flex justify-between text-sm">
            <a href="{{ route('booking.create') }}" class="text-brand-400 hover:text-brand-300">Buat booking lagi</a>
            <a href="{{ route('booking.lookup') }}" class="text-slate-400 hover:text-slate-200">Cek booking lain</a>
        </div>
    </div>
</x-layouts.app>
