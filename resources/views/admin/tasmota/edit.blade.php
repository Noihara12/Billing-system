<x-layouts.admin title="Edit Perangkat Tasmota">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.tasmota.index') }}" class="text-slate-400 hover:text-white transition text-sm">
            ← Kembali
        </a>
        <h2 class="text-lg font-semibold text-white">Edit: {{ $device->device_name }}</h2>
    </div>

    <div class="max-w-2xl space-y-6">
        {{-- Form Edit --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <form method="POST" action="{{ route('admin.tasmota.update', $device) }}" class="space-y-5">
                @csrf
                @method('PUT')

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
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Deskripsi</label>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="Keterangan tambahan..."
                        class="w-full rounded-lg bg-slate-800 border @error('description') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500 resize-none"
                    >{{ old('description', $device->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Authentication --}}
                <div class="rounded-lg border border-slate-700 p-4 space-y-4">
                    <p class="text-sm font-medium text-slate-300">🔒 Autentikasi HTTP (Opsional)</p>
                    @if ($device->username)
                        <div class="flex items-center gap-2 text-xs text-amber-400">
                            <span>⚠️</span>
                            <span>Username sudah dikonfigurasi: <strong>{{ $device->username }}</strong>. Kosongkan field password jika tidak ingin mengubahnya.</span>
                        </div>
                    @else
                        <p class="text-xs text-slate-500">
                            Kosongkan jika tidak menggunakan autentikasi. Password disimpan terenkripsi.
                        </p>
                    @endif

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
                            <label class="block text-sm text-slate-400 mb-1.5">
                                Password Baru
                            </label>
                            <input
                                type="password"
                                name="password"
                                placeholder="{{ $device->password_encrypted ? '(tidak diubah)' : '••••••••' }}"
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
                        Status <span class="text-rose-400">*</span>
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
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-5 py-2 transition"
                    >
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.tasmota.index') }}" class="text-slate-400 hover:text-slate-300 text-sm transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Info Unit Terhubung --}}
        @if ($device->unit)
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4">
                <p class="text-sm font-medium text-emerald-400 mb-2">🎮 Unit Terhubung</p>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white font-medium">{{ $device->unit->name }}</p>
                        <p class="text-sm text-slate-400">{{ $device->unit->unit_code }} — Status: <span class="font-medium">{{ $device->unit->status }}</span></p>
                        @if ($device->unit->auto_power_on || $device->unit->auto_power_off)
                            <div class="flex gap-3 mt-1">
                                @if ($device->unit->auto_power_on)
                                    <span class="text-xs text-emerald-400">✅ Auto Power ON aktif</span>
                                @endif
                                @if ($device->unit->auto_power_off)
                                    <span class="text-xs text-rose-400">✅ Auto Power OFF aktif</span>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-slate-500 mt-1">Auto Power: tidak aktif</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.units.edit', $device->unit) }}" class="text-brand-400 hover:text-brand-300 text-sm transition">
                        Edit Unit →
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4">
                <p class="text-sm text-slate-400">
                    ℹ️ Perangkat ini belum terhubung ke unit manapun.
                    Hubungkan melalui halaman
                    <a href="{{ route('admin.units.index') }}" class="text-brand-400 hover:underline">Manage Units</a>.
                </p>
            </div>
        @endif

        {{-- Quick Test Panel --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
            <p class="text-sm font-medium text-slate-300 mb-3">⚡ Test Perangkat Langsung</p>
            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    onclick="pingDevice('{{ route('admin.tasmota.ping', $device) }}')"
                    class="text-sm bg-slate-700 hover:bg-slate-600 text-slate-200 px-4 py-2 rounded-lg transition"
                >
                    🔗 Test Koneksi (Ping)
                </button>
                <button
                    type="button"
                    onclick="sendPower('{{ route('admin.tasmota.power-on', $device) }}', 'ON')"
                    class="text-sm bg-emerald-700/30 hover:bg-emerald-700/50 text-emerald-400 px-4 py-2 rounded-lg transition"
                >
                    ⚡ Power ON
                </button>
                <button
                    type="button"
                    onclick="sendPower('{{ route('admin.tasmota.power-off', $device) }}', 'OFF')"
                    class="text-sm bg-rose-700/30 hover:bg-rose-700/50 text-rose-400 px-4 py-2 rounded-lg transition"
                >
                    🔴 Power OFF
                </button>
            </div>
            <div id="test-result" class="mt-3 hidden rounded-lg px-3 py-2 text-sm"></div>
        </div>
    </div>

    <script>
        function showResult(message, success) {
            const el = document.getElementById('test-result');
            el.className = 'mt-3 rounded-lg px-3 py-2 text-sm ' + (success
                ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300'
                : 'bg-rose-500/15 border border-rose-500/30 text-rose-300');
            el.textContent = message;
            el.classList.remove('hidden');
        }

        async function pingDevice(url) {
            showResult('Menghubungi perangkat...', true);
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                const latency = data.latency_ms !== null ? ` (${data.latency_ms}ms)` : '';
                showResult(
                    data.online
                        ? `✅ ONLINE${latency} — Terakhir dicek: ${data.last_checked}`
                        : `❌ OFFLINE: ${data.error ?? 'Tidak dapat dijangkau'}`,
                    data.online
                );
            } catch (e) {
                showResult('❌ Gagal menghubungi server.', false);
            }
        }

        async function sendPower(url, command) {
            if (!confirm(`Kirim Power ${command} sekarang?`)) return;
            showResult(`Mengirim Power ${command}...`, true);
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                showResult(data.message, data.success);
            } catch (e) {
                showResult('❌ Gagal menghubungi server.', false);
            }
        }
    </script>
</x-layouts.admin>

