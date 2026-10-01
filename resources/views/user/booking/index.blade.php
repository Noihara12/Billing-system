<x-layouts.app title="My Booking - PS Rental">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">My Booking</h2>
        <a href="{{ route('booking.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Booking Baru
        </a>
    </div>

    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Kode</th>
                    <th class="px-5 py-3 font-medium">Unit</th>
                    <th class="px-5 py-3 font-medium">Tanggal</th>
                    <th class="px-5 py-3 font-medium">Jam</th>
                    <th class="px-5 py-3 font-medium">Durasi</th>
                    <th class="px-5 py-3 font-medium">Harga</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="px-5 py-3 text-slate-200 font-mono text-xs">{{ $booking->booking_code }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ $booking->unit->name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $booking->booking_date->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ round($booking->duration_minutes / 60, 1) }} Jam</td>
                        <td class="px-5 py-3 text-slate-200">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3"><x-status-badge :status="$booking->status" /></td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('booking.show', $booking) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-6 text-center text-slate-500">Anda belum memiliki booking.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</x-layouts.app>
