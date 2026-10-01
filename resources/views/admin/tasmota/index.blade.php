<x-layouts.admin title="Tasmota Management">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Manage Tasmota Devices</h2>
        <a href="{{ route('admin.tasmota.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Tambah Perangkat
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.tasmota.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input
            type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau IP..."
            class="rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 w-full sm:w-64"
        >
        <select name="status" class="rounded-lg bg-slate-800 w-full sm:w-auto border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Semua Status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium px-4 py-2 transition">
            Filter
        </button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.tasmota.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-sm font-medium px-4 py-2 transition">
                Reset
            </a>
        @endif
    </form>

    {{-- Info Banner --}}
    <div class="mb-5 rounded-lg border border-blue-500/30 bg-blue-500/10 px-4 py-3 text-sm text-blue-300">
        <strong>Tasmota HTTP API:</strong> Sistem berkomunikasi dengan perangkat via
        <code class="font-mono text-blue-200">http://&lt;IP&gt;/cm?cmnd=Power%20ON</code>
        dan
        <code class="font-mono text-blue-200">Power%20OFF</code>.
        Pastikan perangkat berada dalam jaringan yang sama dengan server.
    </div>

    {{-- Tabel --}}
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-800/50 text-slate-400 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium">Nama Perangkat</th>
                    <th class="px-5 py-3 font-medium">IP Address</th>
                    <th class="px-5 py-3 font-medium">Unit Terhubung</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Terakhir Dicek</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($devices as $device)
                    <tr id="device-row-{{ $device->id }}">
                        <td class="px-5 py-3">
                            <p class="text-slate-200 font-medium">{{ $device->device_name }}</p>
                            @if ($device->description)
                                <p class="text-xs text-slate-500 mt-0.5">{{ $device->description }}</p>
                            @endif
                            @if ($device->username)
                                <p class="text-xs text-slate-600 mt-0.5">🔒 Autentikasi aktif</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <code class="font-mono text-slate-300 text-xs bg-slate-800 px-2 py-1 rounded">{{ $device->ip_address }}</code>
                        </td>
                        <td class="px-5 py-3 text-slate-400">
                            @if ($device->unit)
                                <span class="text-emerald-400 font-medium">{{ $device->unit->name }}</span>
                                <p class="text-xs text-slate-500">{{ $device->unit->unit_code }}</p>
                            @else
                                <span class="text-slate-600 italic">Tidak terhubung</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span id="status-badge-{{ $device->id }}" class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                                @if ($device->status === 'ONLINE') bg-emerald-500/15 text-emerald-400
                                @elseif ($device->status === 'OFFLINE') bg-rose-500/15 text-rose-400
                                @else bg-slate-500/15 text-slate-400 @endif">
                                <span class="w-1.5 h-1.5 rounded-full inline-block
                                    @if ($device->status === 'ONLINE') bg-emerald-400
                                    @elseif ($device->status === 'OFFLINE') bg-rose-400
                                    @else bg-slate-400 @endif"></span>
                                {{ $device->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">
                            <span id="last-checked-{{ $device->id }}">
                                {{ $device->last_checked_at ? $device->last_checked_at->diffForHumans() : 'Belum pernah' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end flex-wrap gap-2">
                                {{-- Ping --}}
                                <button
                                    type="button"
                                    onclick="pingDevice({{ $device->id }}, '{{ route('admin.tasmota.ping', $device) }}')"
                                    class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 px-2.5 py-1 rounded-lg transition"
                                    title="Test koneksi"
                                >
                                    🔗 Ping
                                </button>

                                {{-- Power ON --}}
                                <button
                                    type="button"
                                    onclick="sendPower({{ $device->id }}, '{{ route('admin.tasmota.power-on', $device) }}', 'ON')"
                                    class="text-xs bg-emerald-700/30 hover:bg-emerald-700/50 text-emerald-400 px-2.5 py-1 rounded-lg transition"
                                    title="Kirim Power ON"
                                >
                                    ⚡ ON
                                </button>

                                {{-- Power OFF --}}
                                <button
                                    type="button"
                                    onclick="sendPower({{ $device->id }}, '{{ route('admin.tasmota.power-off', $device) }}', 'OFF')"
                                    class="text-xs bg-rose-700/30 hover:bg-rose-700/50 text-rose-400 px-2.5 py-1 rounded-lg transition"
                                    title="Kirim Power OFF"
                                >
                                    🔴 OFF
                                </button>

                                <a href="{{ route('admin.tasmota.edit', $device) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">Edit</a>

                                <form method="POST" action="{{ route('admin.tasmota.destroy', $device) }}" onsubmit="return confirm('Hapus perangkat {{ $device->device_name }}? Unit yang terhubung akan terputus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                            Belum ada perangkat Tasmota. Tambahkan perangkat pertama Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $devices->links() }}
    </div>

    {{-- Modal feedback --}}
    <div id="tasmota-toast" class="fixed bottom-6 right-6 z-50 hidden max-w-sm"></div>

    <script>
        /**
         * Tampilkan toast notifikasi
         */
        function showToast(message, success) {
            const toast = document.getElementById('tasmota-toast');
            toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm shadow-2xl transition-all';
            toast.className += success
                ? ' border-emerald-500/40 bg-emerald-500/15 text-emerald-300'
                : ' border-rose-500/40 bg-rose-500/15 text-rose-300';
            toast.textContent = message;
            toast.classList.remove('hidden');
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        /**
         * Update status badge di baris tabel
         */
        function updateStatusBadge(deviceId, status, lastChecked) {
            const badge = document.getElementById(`status-badge-${deviceId}`);
            const lcEl = document.getElementById(`last-checked-${deviceId}`);

            if (!badge) return;

            const colors = {
                ONLINE:  { badge: 'bg-emerald-500/15 text-emerald-400', dot: 'bg-emerald-400' },
                OFFLINE: { badge: 'bg-rose-500/15 text-rose-400', dot: 'bg-rose-400' },
                UNKNOWN: { badge: 'bg-slate-500/15 text-slate-400', dot: 'bg-slate-400' },
            };

            const c = colors[status] ?? colors.UNKNOWN;
            badge.className = `inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full ${c.badge}`;
            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full inline-block ${c.dot}"></span>${status}`;

            if (lcEl && lastChecked) {
                lcEl.textContent = lastChecked;
            }
        }

        /**
         * Ping device untuk test koneksi
         */
        async function pingDevice(deviceId, url) {
            showToast('Menghubungi perangkat...', true);
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            ?? '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                updateStatusBadge(deviceId, data.status, data.last_checked);
                const latency = data.latency_ms !== null ? ` (${data.latency_ms}ms)` : '';
                showToast(
                    data.online
                        ? `✅ Perangkat ONLINE${latency}`
                        : `❌ Perangkat OFFLINE: ${data.error ?? 'Tidak dapat dijangkau'}`,
                    data.online
                );
            } catch (e) {
                showToast('❌ Gagal menghubungi server.', false);
            }
        }

        /**
         * Kirim perintah Power ON atau OFF
         */
        async function sendPower(deviceId, url, command) {
            if (!confirm(`Kirim Power ${command} ke perangkat ini?`)) return;
            showToast(`Mengirim Power ${command}...`, true);
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            ?? '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                updateStatusBadge(deviceId, data.status, null);
                showToast(data.message, data.success);
            } catch (e) {
                showToast('❌ Gagal menghubungi server.', false);
            }
        }
    </script>
</x-layouts.admin>

