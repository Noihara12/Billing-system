<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(BookingService $bookingService): View
    {
        return view('admin.settings.edit', [
            'openTime' => (string) Setting::get('booking_open_time', '09:00'),
            // Input time HTML tidak menerima "24:00"; tengah malam ditampilkan sebagai 00:00.
            'closeTime' => str_replace('24:00', '00:00', (string) Setting::get('booking_close_time', '24:00')),
            'operatingHours' => $bookingService->describeOperatingHours(),
        ]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        Setting::set('booking_open_time', $request->validated('booking_open_time'), 'Jam buka booking (HH:MM)');
        Setting::set('booking_close_time', $request->validated('booking_close_time'), 'Jam tutup booking (HH:MM); lebih awal/sama dengan jam buka berarti hari berikutnya');

        return redirect()->route('admin.settings.edit')->with('status', 'Jam operasional berhasil diperbarui.');
    }
}
