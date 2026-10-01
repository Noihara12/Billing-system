<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use App\Models\Unit;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, RentalService $rentalService): View|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $rentalService->completeExpired();

        $carousels = Carousel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $units = $this->activeUnits();

        return view('user.dashboard', compact('carousels', 'units'));
    }

    public function data(RentalService $rentalService): JsonResponse
    {
        $rentalService->completeExpired();

        $units = $this->activeUnits()->map(fn (Unit $unit) => [
            'id' => $unit->id,
            'name' => $unit->name,
            'unit_code' => $unit->unit_code,
            'status' => $unit->status,
            'remaining_end_iso' => $unit->activeRentalSession?->rental_end_time->toIso8601String(),
            'booking_start' => $unit->upcomingBooking?->start_time->format('H:i'),
            'booking_duration_minutes' => $unit->upcomingBooking?->duration_minutes,
            'games' => $unit->games->pluck('name')->values(),
        ])->values();

        return response()->json(['units' => $units]);
    }

    /**
     * @return Collection<int, Unit>
     */
    private function activeUnits()
    {
        return Unit::query()
            ->where('is_active', true)
            ->with(['games', 'activeRentalSession', 'upcomingBooking'])
            ->orderBy('name')
            ->get();
    }
}
