<x-layouts.admin title="Tambah Perangkat Tasmota">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.tasmota.index') }}" class="text-slate-400 hover:text-white transition text-sm">
            ← Kembali
        </a>
        <h2 class="text-lg font-semibold text-white">Tambah Perangkat Tasmota</h2>
    </div>

    <div class="max-w-2xl">
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <form method="POST" action="{{ route('admin.tasmota.store') }}" class="space-y-5">
                @csrf

                {{-- Device Name --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nama Perangkat <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="device_name"
                        value="{{ old('device_name', $device->device_name) }}"
                        placeholder="Contoh: Sonoff PS 01"
                        class="w-full rounded-lg bg-slate-800 border @error('device_name') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
                    >
                    @error('device_name')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- IP Address --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        IP Address <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="ip_address"
                        value="{{ old('ip_address', $device->ip_address) }}"
                        placeholder="Contoh: 192.168.1.101"
                        class="w-full rounded-lg bg-slate-800 border @error('ip_address') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500 font-mono"
                    >
                    @error('ip_address')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">
                        Akan digunakan sebagai: <code class="font-mono">http://&lt;IP&gt;/cm?cmnd=Power%20ON</code>
                    </p>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Deskripsi</label>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="Keterangan tambahan tentang perangkat ini..."
                        class="w-full rounded-lg bg-slate-800 border @error('description') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500 resize-none"
                    >{{ old('description', $device->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Authentication (opsional) --}}
                <div class="rounded-lg border border-slate-700 p-4 space-y-4">
                    <p class="text-sm font-medium text-slate-300">🔒 Autentikasi HTTP (Opsional)</p>
                    <p class="text-xs text-slate-500">
                        Jika perangkat Tasmota Anda dikonfigurasi dengan username/password,
                        isi form di bawah. Kosongkan jika tidak menggunakan autentikasi.
                        Password disimpan terenkripsi.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1.5">Username</label>
                            <input
                                type="text"
                                name="username"
                                value="{{ old('username', $device->username) }}"
                                placeholder="admin"
                                autocomplete="off"
                                class="w-full rounded-lg bg-slate-800 border @error('username') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
                            >
                            @error('username')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-400 mb-1.5">Password</label>
                            <input
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                autocomplete="new-password"
                                class="w-full rounded-lg bg-slate-800 border @error('password') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
                            >
                            @error('password')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Status Awal <span class="text-rose-400">*</span>
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-lg bg-slate-800 border @error('status') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                        <option value="UNKNOWN" @selected(old('status', $device->status) === 'UNKNOWN')>UNKNOWN</option>
                        <option value="ONLINE" @selected(old('status', $device->status) === 'ONLINE')>ONLINE</option>
                        <option value="OFFLINE" @selected(old('status', $device->status) === 'OFFLINE')>OFFLINE</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">
                        Status akan diperbarui otomatis saat Anda menekan tombol "Ping" di halaman daftar.
                    </p>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-5 py-2 transition"
                    >
                        Simpan Perangkat
                    </button>
                    <a href="{{ route('admin.tasmota.index') }}" class="text-slate-400 hover:text-slate-300 text-sm transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Help --}}
        <div class="mt-4 rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-3 text-xs text-slate-500 space-y-1">
            <p class="font-medium text-slate-400">💡 Cara mendapatkan IP Address Tasmota:</p>
            <ol class="list-decimal list-inside space-y-1 ml-1">
                <li>Buka Tasmota Web Interface di browser</li>
                <li>Pergi ke <strong>Main Menu → Information</strong></li>
                <li>Catat IP Address yang tertera</li>
                <li>Pastikan perangkat berada di jaringan WiFi yang sama dengan server</li>
            </ol>
        </div>
    </div>
</x-layouts.admin>

