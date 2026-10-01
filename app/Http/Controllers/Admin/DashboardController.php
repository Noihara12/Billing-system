<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RentalSession;
use App\Models\Unit;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RentalService $rentalService): View
    {
        $rentalService->completeExpired();

        return view('admin.dashboard', $this->buildDashboardData());
    }

    public function data(RentalService $rentalService): JsonResponse
    {
        $rentalService->completeExpired();

        $data = $this->buildDashboardData();

        return response()->json([
            'stats' => $data['stats'],
            'active_rentals' => $data['activeRentals']->map(fn (RentalSession $rental) => [
                'id' => $rental->id,
                'unit_name' => $rental->unit->name,
                'customer_name' => $rental->customer_name ?? $rental->user->name ?? '-',
                'start_time' => $rental->rental_start_time->format('H:i'),
                'end_time' => $rental->rental_end_time->format('H:i'),
                'end_time_iso' => $rental->rental_end_time->toIso8601String(),
                'remaining_seconds' => $rental->remainingSeconds(),
                'status' => $rental->status,
            ])->values(),
        ]);
    }

    /**
     * @return array{stats: array<string, int|float>, activeRentals: \Illuminate\Support\Collection<int, RentalSession>}
     */
    private function buildDashboardData(): array
    {
        $stats = [
            'total_units' => Unit::query()->count(),
            'units_available' => Unit::query()->where('status', Unit::STATUS_AVAILABLE)->count(),
            'units_playing' => Unit::query()->where('status', Unit::STATUS_PLAYING)->count(),
            'units_booked' => Unit::query()->where('status', Unit::STATUS_BOOKED)->count(),
            'units_maintenance' => Unit::query()->where('status', Unit::STATUS_MAINTENANCE)->count(),
            'bookings_today' => Booking::query()->whereDate('booking_date', today())->count(),
            'active_rentals' => RentalSession::query()->where('status', RentalSession::STATUS_ACTIVE)->count(),
            'revenue_today' => (float) RentalSession::query()
                ->where('status', RentalSession::STATUS_COMPLETED)
                ->whereDate('actual_end_time', today())
                ->sum('total_price'),
            'revenue_month' => (float) RentalSession::query()
                ->where('status', RentalSession::STATUS_COMPLETED)
                ->whereMonth('actual_end_time', now()->month)
                ->whereYear('actual_end_time', now()->year)
                ->sum('total_price'),
        ];

        $activeRentals = RentalSession::query()
            ->with(['unit', 'user', 'booking'])
            ->where('status', RentalSession::STATUS_ACTIVE)
            ->orderBy('rental_end_time')
            ->get();

        return compact('stats', 'activeRentals');
    }
}

