<?php

use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CarouselController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\PriceListController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TasmotaController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\BookingLookupController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController;
use App\Services\BookingService;
use Illuminate\Support\Facades\Route;

// Beranda: daftar unit + banner promo. Terbuka untuk pengunjung tanpa akun.
Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');
Route::get('beranda-data', [UserDashboardController::class, 'data'])->name('dashboard.data');

Route::get('tentang-kami', function (BookingService $bookingService) {
    return view('user.about', ['operatingHours' => $bookingService->describeOperatingHours()]);
})->name('about');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
});

// Booking tamu: bisa diakses tanpa login, customer memakai kode booking untuk mengecek pesanannya.
Route::get('booking', [UserBookingController::class, 'create'])->name('booking.create');
Route::post('booking', [UserBookingController::class, 'store'])->middleware('throttle:10,60')->name('booking.store');
Route::get('booking/availability', [UserBookingController::class, 'availability'])->name('booking.availability');

Route::get('cek-booking', [BookingLookupController::class, 'create'])->name('booking.lookup');
Route::post('cek-booking', [BookingLookupController::class, 'store'])->middleware('throttle:10,60')->name('booking.lookup.store');
Route::get('cek-booking/{booking:booking_code}', [BookingLookupController::class, 'show'])->name('booking.lookup.show');
Route::patch('cek-booking/{booking:booking_code}/cancel', [BookingLookupController::class, 'cancel'])->name('booking.lookup.cancel');

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('my-booking', [UserBookingController::class, 'index'])->name('booking.index');
    Route::get('my-booking/{booking}', [UserBookingController::class, 'show'])->name('booking.show');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard-data', [AdminDashboardController::class, 'data'])->name('dashboard.data');
    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('games', GameController::class)->except(['show']);
    Route::patch('price-lists/{priceList}/toggle', [PriceListController::class, 'toggle'])->name('price-lists.toggle');
    Route::resource('price-lists', PriceListController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [AdminBookingController::class, 'index'])->name('index');
        Route::get('{booking}', [AdminBookingController::class, 'show'])->name('show');
        Route::patch('{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('confirm');
        Route::patch('{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('cancel');
        Route::patch('{booking}/complete', [AdminBookingController::class, 'complete'])->name('complete');
        Route::delete('{booking}', [AdminBookingController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'create'])->name('create');
        Route::post('from-booking/{booking}', [BillingController::class, 'startFromBooking'])->name('start-from-booking');
        Route::post('walk-in', [BillingController::class, 'storeWalkIn'])->name('start-walkin');
    });

    Route::prefix('rentals')->name('rentals.')->group(function () {
        Route::get('/', [RentalController::class, 'index'])->name('index');
        Route::get('data', [RentalController::class, 'data'])->name('data');
        Route::patch('{rental}/complete', [RentalController::class, 'complete'])->name('complete');
        Route::patch('{rental}/cancel', [RentalController::class, 'cancel'])->name('cancel');
    });

    Route::patch('carousels/{carousel}/toggle', [CarouselController::class, 'toggle'])->name('carousels.toggle');
    Route::resource('carousels', CarouselController::class)->except(['show']);

    // Tasmota Management
    Route::post('tasmota/{tasmota_device}/ping', [TasmotaController::class, 'ping'])->name('tasmota.ping');
    Route::post('tasmota/{tasmota_device}/power-on', [TasmotaController::class, 'powerOn'])->name('tasmota.power-on');
    Route::post('tasmota/{tasmota_device}/power-off', [TasmotaController::class, 'powerOff'])->name('tasmota.power-off');
    Route::post('tasmota/{tasmota_device}/status', [TasmotaController::class, 'powerStatus'])->name('tasmota.power-status');
    Route::resource('tasmota', TasmotaController::class)->except(['show'])->parameters(['tasmota' => 'tasmota_device']);

    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});
