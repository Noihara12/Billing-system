<x-layouts.app title="Profil Saya - PS Rental">
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header Profil --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 backdrop-blur p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-400 flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-brand-500/20">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">{{ $user->name }}</h1>
                    <p class="text-sm text-slate-400">{{ $user->email }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-500/10 text-brand-400 border border-brand-500/20">
                            {{ ucfirst($user->role?->name ?? 'Customer') }}
                        </span>
                        <span class="text-xs text-slate-500">
                            Bergabung sejak {{ $user->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-slate-800 pt-4 sm:pt-0 sm:pl-6 text-center">
                <div class="px-3 py-2 rounded-xl bg-slate-800/40">
                    <p class="text-xs text-slate-400 font-medium">Total Booking</p>
                    <p class="text-lg font-bold text-white mt-0.5">{{ $bookingStats['total'] }}</p>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-800/40">
                    <p class="text-xs text-amber-400 font-medium">Pending</p>
                    <p class="text-lg font-bold text-white mt-0.5">{{ $bookingStats['pending'] }}</p>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-800/40">
                    <p class="text-xs text-emerald-400 font-medium">Selesai</p>
                    <p class="text-lg font-bold text-white mt-0.5">{{ $bookingStats['completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Form Informasi Akun --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-white">Informasi Profil</h2>
                    <p class="text-xs text-slate-400 mt-1">Perbarui informasi kontak dan data akun Anda.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">Nama Lengkap <span class="text-rose-400">*</span></label>
                        <input
                            id="name" type="text" name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full rounded-lg bg-slate-800 border @error('name') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Alamat Email <span class="text-rose-400">*</span></label>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full rounded-lg bg-slate-800 border @error('email') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-slate-300 mb-1.5">Nomor WhatsApp <span class="text-rose-400">*</span></label>
                        <input
                            id="whatsapp_number" type="tel" name="whatsapp_number"
                            value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                            required
                            inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" data-phone
                            placeholder="08xxxxxxxxxx"
                            class="w-full rounded-lg bg-slate-800 border @error('whatsapp_number') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono"
                        >
                        @error('whatsapp_number')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">Nomor ini digunakan untuk konfirmasi rental PlayStation.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2.5 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Ubah Password --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-white">Ubah Kata Sandi</h2>
                    <p class="text-xs text-slate-400 mt-1">Pastikan akun Anda menggunakan kata sandi yang aman.</p>
                </div>

                @if (session('password_status'))
                    <div class="mb-5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('password_status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-300 mb-1.5">Kata Sandi Saat Ini <span class="text-rose-400">*</span></label>
                        <input
                            id="current_password" type="password" name="current_password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg bg-slate-800 border @error('current_password') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                        @error('current_password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Kata Sandi Baru <span class="text-rose-400">*</span></label>
                        <input
                            id="password" type="password" name="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-lg bg-slate-800 border @error('password') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-rose-400">*</span></label>
                        <input
                            id="password_confirmation" type="password" name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-medium px-4 py-2.5 transition">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

