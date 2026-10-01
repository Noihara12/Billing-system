<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreBookingRequest;
use App\Models\Booking;
use App\Models\PriceList;
use App\Models\Unit;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        return view('user.booking.create', [
            'units' => Unit::query()->with('category')->where('is_active', true)->orderBy('name')->get(),
            'priceLists' => PriceList::query()->where('is_active', true)->orderBy('sort_order')->orderBy('duration_minutes')->get(),
            'selectedUnitId' => $request->integer('unit_id') ?: null,
        ]);
    }

    public function availability(Request $request, BookingService $bookingService): JsonResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'price_list_id' => ['nullable', 'integer', 'exists:price_lists,id'],
        ]);

        $unit = Unit::query()->where('is_active', true)->findOrFail($validated['unit_id']);
        $durationMinutes = isset($validated['price_list_id'])
            ? (int) PriceList::query()->whereKey($validated['price_list_id'])->value('duration_minutes')
            : BookingService::SLOT_MINUTES;

        return response()->json($bookingService->availability($unit, Carbon::parse($validated['date']), $durationMinutes));
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService): RedirectResponse
    {
        $booking = $bookingService->createBooking($request->validated(), $request->user());
        $status = "Booking berhasil dibuat dengan kode {$booking->booking_code}.";

        if (! $request->user()) {
            // Tamu boleh langsung melihat booking yang baru saja dibuatnya.
            BookingLookupController::remember($request, $booking);

            return redirect()->route('booking.lookup.show', $booking)->with('status', $status);
        }

        return redirect()->route('booking.show', $booking)->with('status', $status);
    }

    public function index(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['unit'])
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('user.booking.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load(['unit', 'priceList']);

        return view('user.booking.show', compact('booking'));
    }
}
