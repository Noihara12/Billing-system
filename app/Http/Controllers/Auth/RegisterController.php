<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $customerRoleId = Role::query()->where('name', Role::CUSTOMER)->value('id');

        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'whatsapp_number' => $request->string('whatsapp_number'),
            'password' => $request->string('password'),
            'role_id' => $customerRoleId,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
