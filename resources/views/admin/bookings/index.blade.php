<x-layouts.admin title="Booking Management">
    <h2 class="text-lg font-semibold text-white mb-5">Booking Management</h2>

    <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input
            type="text" name="search" value="{{ request('search') }}" placeholder="Nama, kode, atau WhatsApp..."
            class="rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 w-full sm:w-56"
        >
        <input
            type="date" name="date" value="{{ request('date') }}"
            class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
        >
        <select name="unit_id" class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Unit</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
            Filter
        </button>
        @if (request()->hasAny(['search', 'date', 'unit_id', 'status']))
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-sm font-medium px-4 py-2 transition">
                Reset
            </a>
        @endif
    </form>

    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Kode</th>
                    <th class="px-5 py-3 font-medium">Customer</th>
                    <th class="px-5 py-3 font-medium">Unit</th>
                    <th class="px-5 py-3 font-medium">Tanggal</th>
                    <th class="px-5 py-3 font-medium">Jam</th>
                    <th class="px-5 py-3 font-medium">Harga</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="hover:text-brand-400">{{ $booking->booking_code }}</a>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-slate-200">{{ $booking->customer_name }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->whatsapp_number }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $booking->unit->name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $booking->booking_date->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $booking->start_time->format('H:i') }}-{{ $booking->end_time->format('H:i') }}</td>
                        <td class="px-5 py-3 text-slate-200">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3"><x-status-badge :status="$booking->status" /></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($booking->status === \App\Models\Booking::STATUS_PENDING)
                                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-brand-400 hover:text-brand-300 text-xs font-medium">Confirm</button>
                                    </form>
                                @endif
                                @if (in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_CONFIRMED]))
                                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-emerald-400 hover:text-emerald-300 text-xs font-medium">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking {{ $booking->booking_code }}?');">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-amber-400 hover:text-amber-300 text-xs font-medium">Cancel</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Hapus booking {{ $booking->booking_code }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-6 text-center text-slate-500">Belum ada booking.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</x-layouts.admin>
