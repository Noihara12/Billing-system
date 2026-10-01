<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan formulir profil pengguna.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load('role');

        $bookingStats = [
            'total' => $user->bookings()->count(),
            'pending' => $user->bookings()->where('status', 'PENDING')->count(),
            'confirmed' => $user->bookings()->where('status', 'CONFIRMED')->count(),
            'completed' => $user->bookings()->where('status', 'COMPLETED')->count(),
        ];

        return view('user.profile.edit', compact('user', 'bookingStats'));
    }

    /**
     * Perbarui data profil pengguna (nama, email, nomor whatsapp).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun pengguna.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('profile.edit')->with('password_status', 'Kata sandi berhasil diubah.');
    }
}

