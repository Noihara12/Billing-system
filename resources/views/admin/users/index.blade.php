<x-layouts.admin title="User Management">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Manage Users</h2>
            <p class="text-xs text-slate-400 mt-1">Kelola data akun pengguna, role, dan hak akses sistem.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition shadow-lg shadow-brand-600/20">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Pengguna
        </a>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Pengguna</p>
                <p class="text-xl font-bold text-white mt-0.5">{{ $counts['total'] }}</p>
            </div>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Admin</p>
                <p class="text-xl font-bold text-white mt-0.5">{{ $counts['admin'] }}</p>
            </div>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Customer</p>
                <p class="text-xl font-bold text-white mt-0.5">{{ $counts['customer'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 mb-5">
        <div class="relative flex-1 min-w-[240px] max-w-md">
            <input
                type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, email, atau no. WA..."
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <select name="role_id" class="rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>
                    {{ ucfirst($role->name) }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
            Filter
        </button>

        @if (request()->hasAny(['search', 'role_id']))
            <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-sm font-medium px-4 py-2 transition">
                Reset
            </a>
        @endif
    </form>

    {{-- Tabel Pengguna --}}
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Pengguna</th>
                    <th class="px-5 py-3 font-medium">Role</th>
                    <th class="px-5 py-3 font-medium">WhatsApp</th>
                    <th class="px-5 py-3 font-medium">Booking</th>
                    <th class="px-5 py-3 font-medium">Terdaftar</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-brand-400">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-slate-200 font-medium flex items-center gap-1.5">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-300 font-normal">Anda</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if ($user->isAdmin())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/15 text-purple-300 border border-purple-500/20">
                                    Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/20">
                                    Customer
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($user->whatsapp_number)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->whatsapp_number) }}" target="_blank" class="font-mono text-xs text-slate-300 hover:text-emerald-400 transition">
                                    {{ $user->whatsapp_number }}
                                </a>
                            @else
                                <span class="text-slate-500 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-300">
                            {{ $user->bookings_count }} sesi
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">
                                    Edit
                                </a>

                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                            Tidak ada data pengguna yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-layouts.admin>

