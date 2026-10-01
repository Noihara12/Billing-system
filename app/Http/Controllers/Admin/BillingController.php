<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StartWalkInRentalRequest;
use App\Models\Booking;
use App\Models\PriceList;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use App\Services\RentalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function create(Request $request, RentalService $rentalService, BookingService $bookingService): View
    {
        $rentalService->completeExpired();

        $availableUnits = Unit::query()->with('category')->where('status', Unit::STATUS_AVAILABLE)->where('is_active', true)->orderBy('name')->get();

        $walkInLimits = $availableUnits->mapWithKeys(function (Unit $unit) use ($bookingService) {
            $nextBooking = $bookingService->nextBooking($unit->id);

            return [$unit->id => [
                'next_booking_start' => $nextBooking?->start_time,
                'minutes_available' => $bookingService->walkInMinutesAvailable($nextBooking),
            ]];
        });

        $pendingBookings = Booking::query()
            ->with(['unit', 'user'])
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->whereDoesntHave('rentalSession')
            ->orderBy('start_time')
            ->get();

        return view('admin.billing.create', [
            'pendingBookings' => $pendingBookings,
            'availableUnits' => $availableUnits,
            'walkInLimits' => $walkInLimits,
            'priceLists' => PriceList::query()->with('category')->where('is_active', true)->orderBy('sort_order')->orderBy('duration_minutes')->get(),
            'selectedUnitId' => $request->integer('unit_id') ?: null,
            'customers' => User::query()->whereHas('role', fn ($q) => $q->where('name', Role::CUSTOMER))->orderBy('name')->get(),
        ]);
    }

    public function startFromBooking(Request $request, Booking $booking, RentalService $rentalService): RedirectResponse
    {
        $session = $rentalService->startFromBooking($booking, $request->user());

        return redirect()->route('admin.rentals.index')->with('status', "Rental untuk {$session->unit->name} dimulai.");
    }

    public function storeWalkIn(StartWalkInRentalRequest $request, RentalService $rentalService): RedirectResponse
    {
        $session = $rentalService->startWalkIn($request->validated(), $request->user());

        return redirect()->route('admin.rentals.index')->with('status', "Rental untuk {$session->unit->name} dimulai.");
    }
}
