<x-layouts.admin title="Admin Dashboard">
    <div data-dashboard-root data-endpoint="{{ route('admin.dashboard.data') }}">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <x-stat-card label="Total Unit" data-stat="total_units" :value="$stats['total_units']" />
            <x-stat-card label="Unit Available" data-stat="units_available" :value="$stats['units_available']" />
            <x-stat-card label="Unit Playing" data-stat="units_playing" :value="$stats['units_playing']" />
            <x-stat-card label="Unit Booking" data-stat="units_booked" :value="$stats['units_booked']" />
            <x-stat-card label="Unit Maintenance" data-stat="units_maintenance" :value="$stats['units_maintenance']" />
            <x-stat-card label="Booking Hari Ini" data-stat="bookings_today" :value="$stats['bookings_today']" />
            <x-stat-card label="Rental Aktif" data-stat="active_rentals" :value="$stats['active_rentals']" />
            <x-stat-card label="Pendapatan Hari Ini" data-stat="revenue_today" data-format="currency" :value="'Rp '.number_format($stats['revenue_today'], 0, ',', '.')" />
            <x-stat-card label="Pendapatan Bulan Ini" data-stat="revenue_month" data-format="currency" :value="'Rp '.number_format($stats['revenue_month'], 0, ',', '.')" />
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="font-semibold text-white">Active Rentals</h2>
                <span class="text-xs text-slate-500">Update otomatis setiap 10 detik</span>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-slate-800/50 text-slate-400 text-left">
                    <tr>
                        <th class="px-5 py-3 font-medium">Unit</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Start</th>
                        <th class="px-5 py-3 font-medium">End</th>
                        <th class="px-5 py-3 font-medium">Sisa Waktu</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody data-active-rentals-body class="divide-y divide-slate-800">
                    @forelse ($activeRentals as $rental)
                        <tr>
                            <td class="px-5 py-3 text-slate-200">{{ $rental->unit->name }}</td>
                            <td class="px-5 py-3 text-slate-200">{{ $rental->customer_name ?? $rental->user->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $rental->rental_start_time->format('H:i') }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $rental->rental_end_time->format('H:i') }}</td>
                            <td class="px-5 py-3 font-mono text-slate-200" data-remaining-end="{{ $rental->rental_end_time->toIso8601String() }}">-</td>
                            <td class="px-5 py-3"><x-status-badge :status="$rental->status" /></td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500">Belum ada rental aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>
</x-layouts.admin>
