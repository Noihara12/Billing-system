<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LookupBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman "Cek Booking" untuk customer tanpa akun: verifikasi dengan kode booking + nomor WhatsApp,
 * lalu id booking disimpan di session sehingga halaman detailnya bisa dibuka ulang.
 */
class BookingLookupController extends Controller
{
    private const SESSION_KEY = 'verified_booking_ids';

    public static function remember(Request $request, Booking $booking): void
    {
        $ids = array_unique([...$request->session()->get(self::SESSION_KEY, []), $booking->id]);

        $request->session()->put(self::SESSION_KEY, $ids);
    }

    public function create(): View
    {
        return view('user.booking.lookup');
    }

    public function store(LookupBookingRequest $request): RedirectResponse
    {
        $booking = Booking::query()->where('booking_code', $request->validated('booking_code'))->first();

        if (! $booking || Booking::normalizeWhatsapp($booking->whatsapp_number) !== Booking::normalizeWhatsapp($request->validated('whatsapp_number'))) {
            return back()->withInput()->withErrors(['booking_code' => 'Kode booking dan nomor WhatsApp tidak cocok.']);
        }

        self::remember($request, $booking);

        return redirect()->route('booking.lookup.show', $booking);
    }

    public function show(Request $request, Booking $booking): View|RedirectResponse
    {
        if (! $this->isVerified($request, $booking)) {
            return redirect()->route('booking.lookup')->withErrors(['booking_code' => 'Masukkan kode booking dan nomor WhatsApp untuk melihat detail booking.']);
        }

        $booking->load(['unit.category', 'priceList']);

        return view('user.booking.lookup-show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking, BookingService $bookingService): RedirectResponse
    {
        if (! $this->isVerified($request, $booking)) {
            return redirect()->route('booking.lookup')->withErrors(['booking_code' => 'Masukkan kode booking dan nomor WhatsApp untuk membatalkan booking.']);
        }

        $bookingService->cancel($booking);

        return redirect()->route('booking.lookup.show', $booking)->with('status', "Booking {$booking->booking_code} telah dibatalkan.");
    }

    private function isVerified(Request $request, Booking $booking): bool
    {
        if ($request->user() && $booking->user_id === $request->user()->id) {
            return true;
        }

        return in_array($booking->id, $request->session()->get(self::SESSION_KEY, []), true);
    }
}
