<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Unit;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['unit', 'user'])
            ->when($request->filled('date'), fn ($q) => $q->whereDate('booking_date', $request->date('date')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search');
                $q->where(function ($q2) use ($term) {
                    $q2->where('customer_name', 'like', "%{$term}%")
                        ->orWhere('booking_code', 'like', "%{$term}%")
                        ->orWhere('whatsapp_number', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'units' => Unit::query()->orderBy('name')->get(),
            'statuses' => [
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_COMPLETED,
            ],
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['unit', 'user', 'priceList', 'createdBy']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $bookingService->confirm($booking);

        return back()->with('status', "Booking {$booking->booking_code} dikonfirmasi.");
    }

    public function cancel(Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $bookingService->cancel($booking);

        return back()->with('status', "Booking {$booking->booking_code} dibatalkan.");
    }

    public function complete(Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $bookingService->complete($booking);

        return back()->with('status', "Booking {$booking->booking_code} diselesaikan.");
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', "Booking {$booking->booking_code} dihapus.");
    }
}
