<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna (admin & customer).
     */
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('role')
            ->withCount('bookings')
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->integer('role_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search');
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('whatsapp_number', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::all();

        $counts = [
            'total' => User::count(),
            'admin' => User::whereHas('role', fn ($q) => $q->where('name', Role::ADMIN))->count(),
            'customer' => User::whereHas('role', fn ($q) => $q->where('name', Role::CUSTOMER))->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'counts'));
    }

    /**
     * Tampilkan formulir pembuatan pengguna baru.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    /**
     * Tampilkan formulir edit pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('role'),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek jika ini admin terakhir
        if ($user->isAdmin()) {
            $adminCount = User::whereHas('role', fn ($q) => $q->where('name', Role::ADMIN))->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus admin terakhir pada sistem.');
            }
        }

        try {
            $userName = $user->name;
            $user->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('status', "Pengguna {$userName} berhasil dihapus.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat transaksi/rental.');
        }
    }
}

